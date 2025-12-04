<?php

namespace App\Modules\PurPayments\Controllers;

use App\Http\Controllers\AppBaseController;
use App\Modules\Customers\Repositories\CustomerRepository;
use App\Modules\Customers\Requests\CustomerRequest;
use App\Modules\Payments\Repositories\PaymentRepository;
use App\Modules\Payments\Requests\PaymentRequest;
use App\Modules\PurPayments\Repositories\PurPaymentRepository;
use App\Modules\PurPayments\Requests\PurPaymentRequest;
use App\Modules\Vendors\Repositories\VendorRepository;
use App\Modules\Vendors\Requests\VendorRequest;
use Illuminate\Http\Request;

class PurPaymentController extends AppBaseController
{
    protected PurPaymentRepository $purPaymentRepository;

    public function __construct(PurPaymentRepository $purPaymentRepo)
    {
        $this->purPaymentRepository = $purPaymentRepo;
    }

    // Fetch all data
    public function index()
    {
        $data = $this->purPaymentRepository->all();
        return $this->sendResponse($data, 'Data retrieved successfully.');
    }

    // Store data
    public function create(PurPaymentRequest $request)
    {
        $user = getUser();

        $purchaseId = $request->purchase_id;
        $amount = $request->amount;

        $checkPurchaseStatus = $this->purPaymentRepository->checkPurchaseStatus($purchaseId);
        if ($checkPurchaseStatus == 'Paid') {
            return $this->sendError('Invoice is already paid', 409);
        }

        $checkAmountExceed = $this->purPaymentRepository->checkAmountExceed($purchaseId, $amount);
        if (!$checkAmountExceed) {
            return $this->sendError('Payment amount exceeds the due amount', 409);
        }

        $store = $this->purPaymentRepository->store($request->all(), $user?->id);
        if (!$store) {
            return $this->sendError('Something went wrong!!! [PPC-01]', 500);
        }

        return $this->sendResponse($store, 'Data created successfully.');
    }

    // Get single details data
    public function show($id)
    {
        $data = $this->purPaymentRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found');
        }

        return $this->sendResponse($data, 'Data retrieved successfully.');
    }

    // Update data
    public function update(PurPaymentRequest $request, $id)
    {
        $user = getUser();

        $data = $this->purPaymentRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found', 404);
        }

        $checkInvoiceId = $this->purPaymentRepository->checkPurchaseId($data->invoice_id, $request->invoice_id);
        if (!$checkInvoiceId) {
            return $this->sendError('Purchase ID cannot be changed', 409);
        }

        $updated = $this->purPaymentRepository->update($data, $request->all(), $user?->id);
        if (!$updated) {
            return $this->sendError('Something went wrong!!! [PPC-02]', 500);
        }

        return $this->sendResponse($updated, 'Data updated successfully!');
    }

    // Delete data
    public function destroy($id)
    {
        $data = $this->purPaymentRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found');
        }

        $deleted = $this->purPaymentRepository->delete($data);
        if (!$deleted) {
            return $this->sendError('Something went wrong!!! [PC-03]', 500);
        }

        return $this->sendSuccess('Data deleted successfully!');
    }
}
