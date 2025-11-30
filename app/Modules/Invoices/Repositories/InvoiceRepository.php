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
        $data = Invoice::latest()->get();

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
                        $filePath = $this->storeFile($attachment, 'invoices', 'invoice_');
                        InvoiceAttachment::create([
                            'invoice_id' => $invoice->id,
                            'img' => $filePath
                        ]);
                    }
                }
            }

            // Create the record in the database
            $created = Invoice::create($data);

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
    public function update(Invoice $product, array $data, $userId)
    {
        DB::beginTransaction();
        try {
            $data['updated_by'] = $userId;

            // Correct way to check file
            if (isset($data['photo']) && $data['photo'] instanceof UploadedFile && $data['photo']->isValid()) {
                $filePath = $this->updateFile($data['photo'], 'products', 'product_', $product->photo);
                $data['photo'] = $filePath;
            }

            // Perform the update
            $product->update($data);

            DB::commit();
            return $this->find($product->id);
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
    public function delete(Invoice $product)
    {
        DB::beginTransaction();
        try {
            // 1. Delete associated files
            if (!empty($product->photo)) {
                $this->deleteOldFile($product->photo);
            }
            // 5. Finally, delete the data itself
            $product->delete();

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error deleting data: ' , [
                'id' => $product->id,
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
        return Invoice::with('category', 'taxRate')->find($id);
    }
    private function generateInvoiceNumber()
    {
        $year = now()->format('Y');
        $month = now()->format('m');

        // Count today's invoices
        $count = Invoice::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();

        $sequence = str_pad($count + 1, 4, '0', STR_PAD_LEFT); // 0001, 0002, 0003...

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
