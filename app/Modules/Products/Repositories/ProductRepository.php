<?php

namespace App\Modules\Products\Repositories;

use App\Modules\Products\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ProductRepository
{
    public function all()
    {
        $data = Product::with('category', 'taxRate')
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

            // Correct way to check file
            if (isset($data['photo']) && $data['photo'] instanceof UploadedFile && $data['photo']->isValid()) {
                $filePath = $this->storeFile($data['photo'], 'uploads/products', 'product_');
                $data['photo'] = $filePath;
            } else {
                $data['photo'] = null;
            }

            // Create the record in the database
            $created = Product::create($data);

            DB::commit();

            return $created->load('category', 'taxRate');
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
    public function update(Product $product, array $data, $userId)
    {
        DB::beginTransaction();
        try {
            $data['updated_by'] = $userId;

            // Correct way to check file
            if (isset($data['photo']) && $data['photo'] instanceof UploadedFile && $data['photo']->isValid()) {
                $filePath = $this->updateFile($data['photo'], 'uploads/products', 'product_', $product->photo);
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
    public function delete(Product $product)
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
        return Product::with('category', 'taxRate')->find($id);
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
