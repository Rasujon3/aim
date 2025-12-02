<?php

namespace App\Modules\Payments\Controllers;

use App\Http\Controllers\AppBaseController;
use App\Modules\Customers\Repositories\CustomerRepository;
use App\Modules\Customers\Requests\CustomerRequest;
use App\Modules\Payments\Repositories\PaymentRepository;
use App\Modules\Payments\Requests\PaymentRequest;
use App\Modules\Vendors\Repositories\VendorRepository;
use App\Modules\Vendors\Requests\VendorRequest;
use Illuminate\Http\Request;

class PaymentController extends AppBaseController
{
    protected PaymentRepository $paymentRepository;

    public function __construct(PaymentRepository $paymentRepo)
    {
        $this->paymentRepository = $paymentRepo;
    }

    // Fetch all data
    public function index()
    {
        $data = $this->paymentRepository->all();
        return $this->sendResponse($data, 'Data retrieved successfully.');
    }

    // Store data
    public function create(PaymentRequest $request)
    {
        $user = getUser();

        $invoiceId = $request->invoice_id;
        $amount = $request->amount;

        $checkInvoiceStatus = $this->paymentRepository->checkInvoiceStatus($invoiceId);
        if ($checkInvoiceStatus == 'Paid') {
            return $this->sendError('Invoice is already paid', 409);
        }

        $checkAmountExceed = $this->paymentRepository->checkAmountExceed($invoiceId, $amount);
        if (!$checkAmountExceed) {
            return $this->sendError('Payment amount exceeds the due amount', 409);
        }

        $store = $this->paymentRepository->store($request->all(), $user?->id);
        if (!$store) {
            return $this->sendError('Something went wrong!!! [PC-01]', 500);
        }

        return $this->sendResponse($store, 'Data created successfully.');
    }

    // Get single details data
    public function show($id)
    {
        $data = $this->paymentRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found');
        }

        return $this->sendResponse($data, 'Data retrieved successfully.');
    }

    // Update data
    public function update(PaymentRequest $request, $id)
    {
        $user = getUser();

        $data = $this->paymentRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found', 404);
        }

        $checkInvoiceId = $this->paymentRepository->checkInvoiceId($data->invoice_id, $request->invoice_id);
        if (!$checkInvoiceId) {
            return $this->sendError('Invoice ID cannot be changed', 409);
        }

        $updated = $this->paymentRepository->update($data, $request->all(), $user?->id);
        if (!$updated) {
            return $this->sendError('Something went wrong!!! [PC-02]', 500);
        }

        return $this->sendResponse($updated, 'Data updated successfully!');
    }

    // Delete data
    public function destroy($id)
    {
        $data = $this->paymentRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found');
        }

        $deleted = $this->paymentRepository->delete($data);
        if (!$deleted) {
            return $this->sendError('Something went wrong!!! [PC-03]', 500);
        }

        return $this->sendSuccess('Data deleted successfully!');
    }
}
