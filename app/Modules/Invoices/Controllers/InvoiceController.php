<?php

namespace App\Modules\Invoices\Controllers;

use App\Http\Controllers\AppBaseController;
use App\Modules\Invoices\Repositories\InvoiceRepository;
use App\Modules\Invoices\Requests\InvoiceRequest;
use App\Modules\Products\Repositories\ProductRepository;
use App\Modules\Products\Requests\ProductRequest;
use App\Modules\Vendors\Repositories\VendorRepository;
use App\Modules\Vendors\Requests\VendorRequest;
use Illuminate\Http\Request;

class InvoiceController extends AppBaseController
{
    protected InvoiceRepository $invoiceRepository;

    public function __construct(InvoiceRepository $invoiceRepo)
    {
        $this->invoiceRepository = $invoiceRepo;
    }

    // Fetch all data
    public function index()
    {
        $data = $this->invoiceRepository->all();
        return $this->sendResponse($data, 'Data retrieved successfully.');
    }

    // Store data
    public function create(InvoiceRequest $request)
    {
        $user = getUser();

        $store = $this->invoiceRepository->store($request->all(), $user?->id);
        if (!$store) {
            return $this->sendError('Something went wrong!!! [IC-01]', 500);
        }

        return $this->sendResponse($store, 'Data created successfully.');
    }

    // Get single details data
    public function show($id)
    {
        $data = $this->invoiceRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found');
        }

        return $this->sendResponse($data, 'Data retrieved successfully.');
    }

    // Update data
    public function update(InvoiceRequest $request, $id)
    {
        $user = getUser();

        $data = $this->invoiceRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found', 404);
        }

        $updated = $this->invoiceRepository->update($data, $request->all(), $user?->id);
        if (!$updated) {
            return $this->sendError('Something went wrong!!! [RC-02]', 500);
        }

        return $this->sendResponse($updated, 'Data updated successfully!');
    }

    // Delete data
    public function destroy($id)
    {
        $data = $this->invoiceRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found');
        }

        $this->invoiceRepository->delete($data);
        return $this->sendSuccess('Data deleted successfully!');
    }
}
