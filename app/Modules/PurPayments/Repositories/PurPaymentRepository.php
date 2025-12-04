<?php

namespace App\Modules\PurPayments\Repositories;

use App\Models\User;
use App\Modules\Customers\Models\Customer;
use App\Modules\Invoices\Models\Invoice;
use App\Modules\Payments\Models\Payment;
use App\Modules\Purchases\Models\Purchase;
use App\Modules\PurPayments\Models\PurPayment;
use App\Modules\Settings\Models\Setting;
use App\Modules\Vendors\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PurPaymentRepository
{
    public function all()
    {
        $data = PurPayment::with('purchase', 'company', 'vendor')
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

            $data['pur_payment_number'] = $this->generatePaymentNumber();

            $purchase = Purchase::find($data['purchase_id']);
            if ($purchase) {
                $data['company_id'] = $purchase->company_id;
                $data['vendor_id'] = $purchase->vendor_id;
            }

            // Previously paid amount
            $previouslyPaid = PurPayment::where('purchase_id', $data['purchase_id'])->sum('amount');
            $totalPaid = $previouslyPaid + $data['amount'];

            if ($purchase->grand_total <= $totalPaid && $purchase->due_amount <= $totalPaid) {
                $purchase->status = 'Paid';
            }

            $purchase->due_amount = $purchase->grand_total < $totalPaid
                ? 0.00
                :  $purchase->due_amount - $data['amount'];

            $purchase->paid = $totalPaid;
            $purchase->save();

            // Create the record in the database
            $created = PurPayment::create($data);

            DB::commit();

            return $created;
        } catch (Exception $e) {
            DB::rollBack();

            // Log the error
            Log::error('Error in storing data: ' , [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'line' => $e->getLine()
            ]);

            return null;
        }
    }
    public function update(PurPayment $payment, array $data, $userId)
    {
        DB::beginTransaction();
        try {
            $data['updated_by'] = $userId;

            $purchase = Purchase::find($payment->purchase_id);

            // Previously paid amount
            $previouslyPaid = PurPayment::where('purchase_id', $payment->purchase_id)
                ->where('id', '!=', $payment->id)
                ->sum('amount');

            $totalPaid = $previouslyPaid + $data['amount'];

            if ($purchase->grand_total <= $totalPaid && $purchase->due_amount <= $totalPaid) {
                $purchase->status = 'Paid';
            } else {
                $purchase->status = 'Pending';
            }

            $dueAmount = 0.00;
            if ($purchase->grand_total >= $totalPaid) {
                $dueAmount = $purchase->grand_total - $totalPaid;
            }

            $purchase->paid = $totalPaid;
            $purchase->due_amount = $dueAmount;
            $purchase->save();

            $payment->update($data);

            DB::commit();
            return $this->find($payment->id);
        } catch (Exception $e) {
            DB::rollBack();

            // Log the error
            Log::error('Error updating data: ' , [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'line' => $e->getLine()
            ]);

            return null;
        }
    }
    // In FloorRepository.php
    public function delete(PurPayment $payment)
    {
        DB::beginTransaction();
        try {
            // 1. Adjust the invoice amounts
            $purchase = Purchase::find($payment->purchase_id);
            if ($purchase) {
                $purchase->paid = $purchase->paid - $payment->amount;
                $purchase->due_amount = $purchase->due_amount + $payment->amount;
                if ($purchase->paid < $purchase->grand_total) {
                    $purchase->status = 'Pending';
                }
                $purchase->save();
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
                'line' => $e->getLine()
            ]);

            return false;
        }
    }
    public function find($id)
    {
        return PurPayment::with('purchase', 'company', 'vendor')->find($id);
    }
    public function checkPurchaseStatus($purchaseId)
    {
        $status = Purchase::where('id', $purchaseId)->value('status');
        return $status;
    }
    public function checkAmountExceed($purchaseId, $amount)
    {
        $dueAmount = Purchase::where('id', $purchaseId)->value('due_amount');
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
        $count = PurPayment::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();

        $sequence = str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        $prefix = 'PUR/PAY/'; // You can set a prefix if needed
//        $paymentPrefix = Setting::value('payment_number_prefix');
//        if (!empty($paymentPrefix)) {
//            $prefix = $paymentPrefix;
//        }

        return "$prefix{$year}/{$month}/{$sequence}";
    }
    public function checkPurchaseId($oldPurchaseId, $newPurchaseId)
    {
        return $oldPurchaseId == $newPurchaseId;
    }
}
