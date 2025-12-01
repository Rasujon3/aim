<?php

namespace App\Modules\Quotations\Repositories;

use App\Modules\Invoices\Models\Invoice;
use App\Modules\Invoices\Models\InvoiceAttachment;
use App\Modules\Invoices\Models\InvoiceItem;
use App\Modules\Products\Models\Product;
use App\Modules\Quotations\Models\Quotation;
use App\Modules\Quotations\Models\QuotationAttachment;
use App\Modules\Quotations\Models\QuotationItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Str;

class QuotationRepository
{
    public function all()
    {
        $data = Quotation::with('items', 'attachments', 'taxRate', 'company', 'customer')
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

            $data['quotation_number'] = $this->generateQuotationNumber();
            $data['hash'] = Str::uuid();

            // Create Quotation
            $quotation = Quotation::create($data);

            // Create Items (Minimum 1 guaranteed by validation)
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    $itemData['quotation_id'] = $quotation->id;
                    QuotationItem::create($itemData);
                }
            }

            // Correct way to check file
            if (isset($data['attachments']) && is_array($data['attachments'])) {
                foreach ($data['attachments'] as $attachment) {
                    if ($attachment && $attachment instanceof UploadedFile && $attachment->isValid()) {
                        $filePath = $this->storeFile($attachment, 'uploads/quotations', 'quotation_');
                        QuotationAttachment::create([
                            'quotation_id' => $quotation->id,
                            'img' => $filePath
                        ]);
                    }
                }
            }

            DB::commit();

            return $this->find($quotation->id);
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
    public function update(Quotation $quotation, array $data, $userId)
    {
        DB::beginTransaction();
        try {
            $data['updated_by'] = $userId;

            // Correct way to check file
            if (isset($data['items']) && is_array($data['items'])) {
                // Delete existing items
                QuotationItem::where('quotation_id', $quotation->id)->delete();

                // Create new items
                foreach ($data['items'] as $itemData) {
                    $itemData['quotation_id'] = $quotation->id;
                    QuotationItem::create($itemData);
                }
            }

            if (isset($data['attachments']) && is_array($data['attachments'])) {
                // 1st fetch attachments ids for specific invoice
                $existingAttachments = QuotationAttachment::where('quotation_id', $quotation->id)->get();
                // 2nd delete existing attachments files from storage
                foreach ($existingAttachments as $existingAttachment) {
                    $this->deleteOldFile($existingAttachment->img);
                }
                // 3rd delete existing attachments records from database
                QuotationAttachment::where('quotation_id', $quotation->id)->delete();

                // 4th store new attachments files and records in database
                foreach ($data['attachments'] as $attachment) {
                    if ($attachment && $attachment instanceof UploadedFile && $attachment->isValid()) {
                        $filePath = $this->storeFile($attachment, 'uploads/quotations', 'quotation_');
                        QuotationAttachment::create([
                            'quotation_id' => $quotation->id,
                            'img' => $filePath
                        ]);
                    }
                }
            }

            // Perform the update
            $quotation->update($data);

            DB::commit();
            return $this->find($quotation->id);
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
    public function delete(Quotation $quotation)
    {
        DB::beginTransaction();
        try {
            // 1. Delete associated files

            // 1st fetch attachments ids for specific invoice
            $existingAttachments = QuotationAttachment::where('quotation_id', $quotation->id)->get();
            // 2nd delete existing attachments files from storage
            foreach ($existingAttachments as $existingAttachment) {
                $this->deleteOldFile($existingAttachment->img);
            }
            // 3rd delete existing attachments records from database
            QuotationAttachment::where('quotation_id', $quotation->id)->delete();

            // 2. Delete related InvoiceItems
            QuotationItem::where('quotation_id', $quotation->id)->delete();

            // 3. Finally, delete the data itself
            $quotation->delete();

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error deleting data: ' , [
                'id' => $quotation->id,
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
        return Quotation::with('items', 'attachments', 'taxRate', 'company', 'customer')->find($id);
    }
    private function generateQuotationNumber()
    {
        $year = now()->format('Y');
        $month = now()->format('m');

        // Count today's invoices
        $count = Quotation::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();

        $sequence = str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        return "QUO/{$year}/{$month}/{$sequence}";
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
