<?php

namespace App\Modules\Invoices\Repositories;

use App\Modules\Invoices\Models\Invoice;
use App\Modules\Invoices\Models\InvoiceAttachment;
use App\Modules\Invoices\Models\InvoiceItem;
use App\Modules\Products\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Str;

class InvoiceRepository
{
    public function all()
    {
        $data = Invoice::with('items', 'attachments', 'taxRate', 'company', 'customer')
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

            $data['invoice_number'] = $this->generateInvoiceNumber();
            $data['hash'] = Str::uuid();

            // Create Invoice
            $invoice = Invoice::create($data);

            // Create Items (Minimum 1 guaranteed by validation)
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    $itemData['invoice_id'] = $invoice->id;
                    InvoiceItem::create($itemData);
                }
            }

            // Correct way to check file
            if (isset($data['attachments']) && is_array($data['attachments'])) {
                foreach ($data['attachments'] as $attachment) {
                    if ($attachment && $attachment instanceof UploadedFile && $attachment->isValid()) {
                        $filePath = $this->storeFile($attachment, 'uploads/invoices', 'invoice_');
                        InvoiceAttachment::create([
                            'invoice_id' => $invoice->id,
                            'img' => $filePath
                        ]);
                    }
                }
            }

            DB::commit();

            return $this->find($invoice->id);
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
    public function update(Invoice $invoice, array $data, $userId)
    {
        DB::beginTransaction();
        try {
            $data['updated_by'] = $userId;

            // Correct way to check file
            if (isset($data['items']) && is_array($data['items'])) {
                // Delete existing items
                InvoiceItem::where('invoice_id', $invoice->id)->delete();

                // Create new items
                foreach ($data['items'] as $itemData) {
                    $itemData['invoice_id'] = $invoice->id;
                    InvoiceItem::create($itemData);
                }
            }

            if (isset($data['attachments']) && is_array($data['attachments'])) {
                // 1st fetch attachments ids for specific invoice
                $existingAttachments = InvoiceAttachment::where('invoice_id', $invoice->id)->get();
                // 2nd delete existing attachments files from storage
                foreach ($existingAttachments as $existingAttachment) {
                    $this->deleteOldFile($existingAttachment->img);
                }
                // 3rd delete existing attachments records from database
                InvoiceAttachment::where('invoice_id', $invoice->id)->delete();

                // 4th store new attachments files and records in database
                foreach ($data['attachments'] as $attachment) {
                    if ($attachment && $attachment instanceof UploadedFile && $attachment->isValid()) {
                        $filePath = $this->storeFile($attachment, 'uploads/invoices', 'invoice_');
                        InvoiceAttachment::create([
                            'invoice_id' => $invoice->id,
                            'img' => $filePath
                        ]);
                    }
                }
            }

            // Perform the update
            $invoice->update($data);

            DB::commit();
            return $this->find($invoice->id);
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
    public function delete(Invoice $invoice)
    {
        DB::beginTransaction();
        try {
            // 1. Delete associated files

            // 1st fetch attachments ids for specific invoice
            $existingAttachments = InvoiceAttachment::where('invoice_id', $invoice->id)->get();
            // 2nd delete existing attachments files from storage
            foreach ($existingAttachments as $existingAttachment) {
                $this->deleteOldFile($existingAttachment->img);
            }
            // 3rd delete existing attachments records from database
            InvoiceAttachment::where('invoice_id', $invoice->id)->delete();

            // 2. Delete related InvoiceItems
            InvoiceItem::where('invoice_id', $invoice->id)->delete();

            // 3. Finally, delete the data itself
            $invoice->delete();

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error deleting data: ' , [
                'id' => $invoice->id,
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
        return Invoice::with('items', 'attachments', 'taxRate', 'company', 'customer')->find($id);
    }
    private function generateInvoiceNumber()
    {
        $year = now()->format('Y');
        $month = now()->format('m');

        // Count today's invoices
        $count = Invoice::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();

        $sequence = str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        return "INV/{$year}/{$month}/{$sequence}";
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
