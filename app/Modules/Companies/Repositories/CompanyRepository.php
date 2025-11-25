<?php

namespace App\Modules\Companies\Repositories;

use App\Modules\Companies\Models\Company;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CompanyRepository
{
    public function all()
    {
        $data = Company::latest()->get();

        return $data;
    }
    public function store(array $data, $userId)
    {
        DB::beginTransaction();
        try {
            $data['created_by'] = $userId;
            $data['updated_by'] = null;

            // Correct way to check file
            if (isset($data['logo']) && $data['logo'] instanceof UploadedFile && $data['logo']->isValid()) {
                $filePath = $this->storeFile($data['logo'], 'company', 'logo_');
                $data['logo'] = $filePath;
            } else {
                $data['logo'] = null;
            }

            if (isset($data['logo_dark']) && $data['logo_dark'] instanceof UploadedFile && $data['logo_dark']->isValid()) {
                $filePath = $this->storeFile($data['logo_dark'], 'company', 'logoDark_');
                $data['logo_dark'] = $filePath;
            } else {
                $data['logo_dark'] = null;
            }

            if (isset($data['qrcode']) && $data['qrcode'] instanceof UploadedFile && $data['qrcode']->isValid()) {
                $filePath = $this->storeFile($data['qrcode'], 'company', 'qrcode_');
                $data['qrcode'] = $filePath;
            } else {
                $data['qrcode'] = null;
            }

            // Create the record in the database
            $created = Company::create($data);

            DB::commit();

            return $created;
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
    public function update(Company $company, array $data, $userId)
    {
        DB::beginTransaction();
        try {
            $data['updated_by'] = $userId;

            // Correct way to check file
            if (isset($data['logo']) && $data['logo'] instanceof UploadedFile && $data['logo']->isValid()) {
                $filePath = $this->updateFile($data['logo'], 'company', 'logo_', $company->logo);
                $data['logo'] = $filePath;
            }

            if (isset($data['logo_dark']) && $data['logo_dark'] instanceof UploadedFile && $data['logo_dark']->isValid()) {
                $filePath = $this->updateFile($data['logo_dark'], 'company', 'logoDark_', $company->logo_dark);
                $data['logo_dark'] = $filePath;
            }

            if (isset($data['qrcode']) && $data['qrcode'] instanceof UploadedFile && $data['qrcode']->isValid()) {
                $filePath = $this->updateFile($data['qrcode'], 'company', 'qrcode_', $company->qrcode);
                $data['qrcode'] = $filePath;
            }

            // Perform the update
            $company->update($data);

            DB::commit();
            return $this->find($company->id);
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
    public function delete(Company $company)
    {
        DB::beginTransaction();
        try {
            // 1. Delete associated files
            if (!empty($company->logo)) {
                $this->deleteOldFile($company->logo);
            }

            if (!empty($company->logo_dark)) {
                $this->deleteOldFile($company->logo_dark);
            }

            if (!empty($company->qrcode)) {
                $this->deleteOldFile($company->qrcode);
            }

            // 5. Finally, delete the data itself
            $company->delete();

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error deleting data: ' , [
                'id' => $company->id,
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
        return Company::find($id);
    }
    private function storeFile($file, $filePath, $prefix)
    {
        // Define the directory path
        $directory = public_path($filePath);

        // Ensure the directory exists
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        // Generate a unique file name
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
        if (!empty($oldFilePath)) {
            $oldFullFilePath = public_path($oldFilePath); // Use without prepending $filePath
            if (file_exists($oldFullFilePath)) {
                unlink($oldFullFilePath); // Delete the old file
                return true;
            }

            Log::warning('Old file not found for deletion', ['path' => $oldFullFilePath]);
            return false;
        }
    }
}
