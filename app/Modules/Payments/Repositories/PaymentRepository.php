<?php

namespace App\Modules\Payments\Repositories;

use App\Models\User;
use App\Modules\Customers\Models\Customer;
use App\Modules\Invoices\Models\Invoice;
use App\Modules\Payments\Models\Payment;
use App\Modules\Settings\Models\Setting;
use App\Modules\Vendors\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentRepository
{
    public function all()
    {
        $data = Payment::with('invoice', 'company', 'customer')
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

            $data['payment_number'] = $this->generatePaymentNumber();

            $invoice = Invoice::find($data['invoice_id']);
            if ($invoice) {
                $data['company_id'] = $invoice->company_id;
                $data['customer_id'] = $invoice->customer_id;
                $customer = Customer::find($invoice->customer_id);
                if ($customer) {
                    $data['user_id'] = $customer->assigned_user;
                }
            }

            // Previously paid amount
            $previouslyPaid = Payment::where('invoice_id', $data['invoice_id'])->sum('amount');
            $totalPaid = $previouslyPaid + $data['amount'];

            if ($invoice->grand_total <= $totalPaid && $invoice->due_amount <= $totalPaid) {
                $invoice->status = 'Paid';
            }

            $invoice->due_amount = $invoice->grand_total < $totalPaid
                ? 0.00
                :  $invoice->due_amount - $data['amount'];

            $invoice->paid = $totalPaid;
            $invoice->save();

            // Create the record in the database
            $created = Payment::create($data);

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
    public function update(Payment $payment, array $data, $userId)
    {
        DB::beginTransaction();
        try {
            $data['updated_by'] = $userId;

            $invoice = Invoice::find($payment->invoice_id);

            // Previously paid amount
            $previouslyPaid = Payment::where('invoice_id', $payment->invoice_id)
                ->where('id', '!=', $payment->id)
                ->sum('amount');

            $totalPaid = $previouslyPaid + $data['amount'];

            if ($invoice->grand_total <= $totalPaid && $invoice->due_amount <= $totalPaid) {
                $invoice->status = 'Paid';
            } else {
                $invoice->status = 'Pending';
            }

            $dueAmount = 0.00;
            if ($invoice->grand_total >= $totalPaid) {
                $dueAmount = $invoice->grand_total - $totalPaid;
            }

            $invoice->paid = $totalPaid;
            $invoice->due_amount = $dueAmount;
            $invoice->save();

            $payment->update($data);

            DB::commit();
            return $this->find($payment->id);
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
    public function checkInvoiceStatus($invoiceId)
    {
        $status = Invoice::where('id', $invoiceId)->value('status');
        return $status;
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
    public function checkInvoiceId($oldInvoiceId, $newInvoiceId)
    {
        return $oldInvoiceId == $newInvoiceId;
    }
}
