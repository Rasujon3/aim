<?php

namespace App\Modules\Roles\Repositories;

use App\Modules\Roles\Models\Role;
use App\Services\S3Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class RoleRepository
{
    public function all()
    {
        $data = Role::latest()->get();

        return $data;
    }
    public function store(array $data, $userId)
    {
        DB::beginTransaction();
        try {
            $data['created_by'] = $userId;
            $data['updated_by'] = $userId;
            $data['is_editable'] = true;
            $data['is_deletable'] = true;
            // Create the record in the database
            $created = Role::create($data);

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
    public function update(Role $role, array $data, $userId)
    {
        DB::beginTransaction();
        try {
            $data['updated_by'] = $userId;

            // Perform the update
            $role->update($data);

            DB::commit();
            return $role;
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
    public function delete(Role $role)
    {
        DB::beginTransaction();
        try {
            // 5. Finally, delete the floor itself
            $role->delete();

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error deleting data: ' , [
                'id' => $role->id,
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
        return Role::find($id);
    }
    public function isEditable($id)
    {
        $data = Role::find($id);
        return $data && $data->is_editable ? $data->is_editable : null;
    }
    public function isDeletable($id)
    {
        $data = Role::find($id);
        return $data && $data->is_deletable ? $data->is_deletable : null;
    }
}
