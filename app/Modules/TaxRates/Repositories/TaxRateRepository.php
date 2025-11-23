<?php

namespace App\Modules\TaxRates\Repositories;

use App\Modules\Categories\Models\Category;
use App\Modules\Roles\Models\Role;
use App\Modules\TaxRates\Models\TaxRate;
use App\Services\S3Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class TaxRateRepository
{
    public function all()
    {
        $data = TaxRate::latest()->get();

        return $data;
    }
    public function store(array $data, $userId)
    {
        DB::beginTransaction();
        try {
            $data['created_by'] = $userId;
            $data['updated_by'] = null;

            // Create the record in the database
            $created = TaxRate::create($data);

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
    public function update(TaxRate $taxRate, array $data, $userId)
    {
        DB::beginTransaction();
        try {
            $data['updated_by'] = $userId;

            // Perform the update
            $taxRate->update($data);

            DB::commit();
            return $taxRate;
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
    public function delete(TaxRate $taxRate)
    {
        DB::beginTransaction();
        try {
            // 5. Finally, delete the data itself
            $taxRate->delete();

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error deleting data: ' , [
                'id' => $taxRate->id,
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
        return TaxRate::find($id);
    }
}
