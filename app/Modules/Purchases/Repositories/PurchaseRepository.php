<?php

namespace App\Modules\Purchases\Repositories;

use App\Modules\Invoices\Models\Invoice;
use App\Modules\Invoices\Models\InvoiceAttachment;
use App\Modules\Invoices\Models\InvoiceItem;
use App\Modules\Products\Models\Product;
use App\Modules\Purchases\Models\Purchase;
use App\Modules\Purchases\Models\PurchaseAttachment;
use App\Modules\Purchases\Models\PurchaseItem;
use App\Modules\Settings\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Str;

class PurchaseRepository
{
    public function all()
    {
        $data = Purchase::with('items', 'attachments', 'taxRate', 'company', 'vendor')
            ->latest()
            ->get();

        return $data;
    }
    public function store(array $data, $userId)
    {
        DB::beginTransaction();
        try {
            $data['created_by'] = $userId;
            $data['updated_by'] = null;

            $data['purchase_number'] = $this->generatePurchaseNumber();
            $data['hash'] = Str::uuid();
            $data['due_amount'] = $data['grand_total'];

            // Create Purchase
            $purchase = Purchase::create($data);

            // Create Items (Minimum 1 guaranteed by validation)
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    $itemData['purchase_id'] = $purchase->id;
                    PurchaseItem::create($itemData);
                }
            }

            // Correct way to check file
            if (isset($data['attachments']) && is_array($data['attachments'])) {
                foreach ($data['attachments'] as $attachment) {
                    if ($attachment && $attachment instanceof UploadedFile && $attachment->isValid()) {
                        $filePath = $this->storeFile($attachment, 'uploads/purchases', 'purchase_');
                        PurchaseAttachment::create([
                            'purchase_id' => $purchase->id,
                            'img' => $filePath
                        ]);
                    }
                }
            }

            DB::commit();

            return $this->find($purchase->id);
        } catch (Exception $e) {
            DB::rollBack();

            // Log the error
            Log::error('Error in storing data: ' , [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }
    public function update(Purchase $purchase, array $data, $userId)
    {
        DB::beginTransaction();
        try {
            $data['updated_by'] = $userId;

            // Correct way to check file
            if (isset($data['items']) && is_array($data['items'])) {
                // Delete existing items
                PurchaseItem::where('purchase_id', $purchase->id)->delete();

                // Create new items
                foreach ($data['items'] as $itemData) {
                    $itemData['purchase_id'] = $purchase->id;
                    PurchaseItem::create($itemData);
                }
            }

            if (isset($data['attachments']) && is_array($data['attachments'])) {
                // 1st fetch attachments ids for specific invoice
                $existingAttachments = PurchaseAttachment::where('purchase_id', $purchase->id)->get();
                // 2nd delete existing attachments files from storage
                foreach ($existingAttachments as $existingAttachment) {
                    $this->deleteOldFile($existingAttachment->img);
                }
                // 3rd delete existing attachments records from database
                PurchaseAttachment::where('purchase_id', $purchase->id)->delete();

                // 4th store new attachments files and records in database
                foreach ($data['attachments'] as $attachment) {
                    if ($attachment && $attachment instanceof UploadedFile && $attachment->isValid()) {
                        $filePath = $this->storeFile($attachment, 'uploads/purchases', 'purchase_');
                        PurchaseAttachment::create([
                            'purchase_id' => $purchase->id,
                            'img' => $filePath
                        ]);
                    }
                }
            }

            // Perform the update
            $purchase->update($data);

            DB::commit();
            return $this->find($purchase->id);
        } catch (Exception $e) {
            DB::rollBack();

            // Log the error
            Log::error('Error updating data: ' , [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }
    // In FloorRepository.php
    public function delete(Purchase $purchase)
    {
        DB::beginTransaction();
        try {
            // 1. Delete associated files

            // 1st fetch attachments ids for specific invoice
            $existingAttachments = PurchaseAttachment::where('purchase_id', $purchase->id)->get();
            // 2nd delete existing attachments files from storage
            foreach ($existingAttachments as $existingAttachment) {
                $this->deleteOldFile($existingAttachment->img);
            }
            // 3rd delete existing attachments records from database
            PurchaseAttachment::where('purchase_id', $purchase->id)->delete();

            // 2. Delete related InvoiceItems
            PurchaseItem::where('purchase_id', $purchase->id)->delete();

            // 3. Finally, delete the data itself
            $purchase->delete();

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error deleting data: ' , [
                'id' => $purchase->id,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }
    public function find($id)
    {
        return Purchase::with('items', 'attachments', 'taxRate', 'company', 'vendor')->find($id);
    }
    private function generatePurchaseNumber()
    {
        $year = now()->format('Y');
        $month = now()->format('m');

        // Count today's invoices
        $count = Purchase::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();

        $sequence = str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        $prefix = 'PUR/'; // You can set a prefix if needed
//        $invPrefix = Setting::value('invoice_number_prefix');
//        if (!empty($invPrefix)) {
//            $prefix = $invPrefix;
//        }

        return "$prefix{$year}/{$month}/{$sequence}";
    }
    private function storeFile($file, $filePath, $prefix)
    {
        // Define the directory path
        // TODO: Change path if needed
        # $filePath = 'files/images/country'; # change path if needed
        $directory = public_path($filePath);

        // Ensure the directory exists
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        // Generate a unique file name
        // TODO: Change path if needed
        # $fileName = uniqid('flag_', true) . '.' . $file->getClientOriginalExtension();
        $fileName = uniqid($prefix, true) . '.' . $file->getClientOriginalExtension();

        // Move the file to the destination directory
        $file->move($directory, $fileName);

        // path & file name in the database
        $path = $filePath . '/' . $fileName;
        return $path;
    }
    private function updateFile($file, $filePath, $prefix, $oldFilePath = null)
    {
        // Delete the old file if it exists
        $this->deleteOldFile($oldFilePath);

        // Store path & file name in the database
        $path = $this->storeFile($file, $filePath, $prefix);
        return $path;
    }
    private function deleteOldFile($oldFilePath)
    {
        // TODO: ensure from database
        if (!empty($oldFilePath)) { # ensure from database
            $oldFullFilePath = public_path($oldFilePath); // Use without prepending $filePath
            if (file_exists($oldFullFilePath)) {
                unlink($oldFullFilePath); // Delete the old file
                return true;
            } else {
                Log::warning('Old file not found for deletion', ['path' => $oldFullFilePath]);
                return false;
            }
        }
    }
}
