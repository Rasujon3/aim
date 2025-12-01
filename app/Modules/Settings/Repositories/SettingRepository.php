<?php

namespace App\Modules\Settings\Repositories;

use App\Modules\Products\Models\Product;
use App\Modules\Settings\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SettingRepository
{
    public function all()
    {
        $data = Setting::first();

        return $data;
    }
    public function update(array $data, $userId)
    {
        DB::beginTransaction();
        try {
            $data['updated_by'] = $userId;

            // Always work on the first (and only) row
            $setting = Setting::first();

            // If no setting exists → create new
            if (!$setting) {
                $data['created_by'] = $userId;
                $setting = Setting::create($data);
            } else {
                // Update existing
                $data['updated_by'] = $userId;
            }

            // Handle Login Logo
            if (isset($data['login_logo']) && $data['login_logo'] instanceof UploadedFile && $data['login_logo']->isValid()) {
                // Delete old file if exists
                if ($setting->login_logo) {
                    $this->deleteOldFile($setting->login_logo);
                }
                $data['login_logo'] = $this->storeFile($data['login_logo'], 'uploads/settings', 'login_logo_');
            }

            // Handle Invoice Logo
            if (isset($data['invoice_logo']) && $data['invoice_logo'] instanceof UploadedFile && $data['invoice_logo']->isValid()) {
                if ($setting->invoice_logo) {
                    $this->deleteOldFile($setting->invoice_logo);
                }
                $data['invoice_logo'] = $this->storeFile($data['invoice_logo'], 'uploads/settings', 'invoice_logo_');
            }

            // Update the record
            $setting->update($data);

            DB::commit();
            return $this->all();
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
