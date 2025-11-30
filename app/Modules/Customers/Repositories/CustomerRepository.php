<?php

namespace App\Modules\Customers\Repositories;

use App\Models\User;
use App\Modules\Customers\Models\Customer;
use App\Modules\Vendors\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CustomerRepository
{
    public function all()
    {
        $data = Customer::latest()->get();

        return $data;
    }
    public function store(array $data, $userId)
    {
        DB::beginTransaction();
        try {
            $data['created_by'] = $userId;
            $data['updated_by'] = null;

            // Create the record in the database
            $created = Customer::create($data);

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
    public function update(Customer $customer, array $data, $userId)
    {
        DB::beginTransaction();
        try {
            $data['updated_by'] = $userId;

            // Perform the update
            $customer->update($data);

            DB::commit();
            return $customer;
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
    public function delete(Customer $customer)
    {
        DB::beginTransaction();
        try {
            // 5. Finally, delete the data itself
            $customer->delete();

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error deleting data: ' , [
                'id' => $customer->id,
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
        return Customer::find($id);
    }
    public function checkAlreadyAssigned($regUserId)
    {
        return Customer::where('assigned_user', $regUserId)->exists();
    }
    public function checkUserRole($regUserId)
    {
        $role = User::where('id', $regUserId)->value('role');
        if ($role && ($role === 'customer' || $role === 'Customer')) {
            return true;
        }
        return false;
    }
    public function assignUser($customerId, $regUserId, $userId)
    {
        DB::beginTransaction();
        try {
            // Perform the update
            $customer = Customer::find($customerId);
            $customer->assigned_user = $regUserId;
            $customer->updated_by = $userId;
            $customer->save();

            DB::commit();
            return $customer;
        } catch (Exception $e) {
            DB::rollBack();

            // Log the error
            Log::error('Error updating assignUser data: ' , [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }
}
