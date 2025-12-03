<?php

namespace App\Modules\Profiles\Repositories;

use App\Models\User;
use App\Modules\Customers\Models\Customer;
use App\Modules\Invoices\Models\Invoice;
use App\Modules\Payments\Models\Payment;
use App\Modules\Settings\Models\Setting;
use App\Modules\Vendors\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class ProfileRepository
{
    public function all()
    {
        $data = Payment::with('invoice', 'company', 'customer')
            ->latest()
            ->get();

        return $data;
    }
    public function updateData(array $data, $userId)
    {
        DB::beginTransaction();
        try {
            $created = User::find($userId);
            $created->full_name = $data['full_name'] ?? $created->full_name;
            $created->email = $data['email'] ?? $created->email;
            $created->updated_by = $userId;
            $created->save();

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
    public function changePassword(array $data, $userId)
    {
        DB::beginTransaction();
        try {
            $user = User::find($userId);
            $user->password = Hash::make($data['new_password']);
            $user->updated_by = $userId;
            $user->save();

            DB::commit();
            return $user;
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
    public function delete(Payment $payment)
    {
        DB::beginTransaction();
        try {
            // 1. Adjust the invoice amounts
            $invoice = Invoice::find($payment->invoice_id);
            if ($invoice) {
                $invoice->paid = $invoice->paid - $payment->amount;
                $invoice->due_amount = $invoice->due_amount + $payment->amount;
                if ($invoice->paid < $invoice->grand_total) {
                    $invoice->status = 'Pending';
                }
                $invoice->save();
            }

            // 2. Finally, delete the data itself
            $payment->delete();

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error deleting data: ' , [
                'id' => $payment->id,
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
        return Payment::with('invoice', 'company', 'customer')->find($id);
    }
    public function checkEmail($email, $userid)
    {
        $exists = User::where('email', $email)
            ->where('id', '!=', $userid)
            ->exists();
        return $exists;
    }
    public function checkAmountExceed($invoiceId, $amount)
    {
        $dueAmount = Invoice::where('id', $invoiceId)->value('due_amount');
        if ($amount <= $dueAmount) {
            return true;
        }
        return false;
    }
    private function generatePaymentNumber()
    {
        $year = now()->format('Y');
        $month = now()->format('m');

        // Count today's invoices
        $count = Payment::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();

        $sequence = str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        $prefix = 'PAY/'; // You can set a prefix if needed
        $paymentPrefix = Setting::value('payment_number_prefix');
        if (!empty($paymentPrefix)) {
            $prefix = $paymentPrefix;
        }

        return "$prefix{$year}/{$month}/{$sequence}";
    }
    public function checkCurrentPassword($currentPassword, $userId)
    {
        $user = User::where('id', $userId)->first();
        if (!Hash::check($currentPassword, $user->password)) {
            return false;
        }
        return true;
    }
}
