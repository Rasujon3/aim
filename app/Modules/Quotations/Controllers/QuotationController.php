<?php

namespace App\Modules\Quotations\Controllers;

use App\Http\Controllers\AppBaseController;
use App\Modules\Invoices\Repositories\InvoiceRepository;
use App\Modules\Invoices\Requests\InvoiceRequest;
use App\Modules\Products\Repositories\ProductRepository;
use App\Modules\Products\Requests\ProductRequest;
use App\Modules\Quotations\Repositories\QuotationRepository;
use App\Modules\Quotations\Requests\QuotationRequest;
use App\Modules\Vendors\Repositories\VendorRepository;
use App\Modules\Vendors\Requests\VendorRequest;
use Illuminate\Http\Request;

class QuotationController extends AppBaseController
{
    protected QuotationRepository $quotationRepository;

    public function __construct(QuotationRepository $quotationRepo)
    {
        $this->quotationRepository = $quotationRepo;
    }

    // Fetch all data
    public function index()
    {
        $data = $this->quotationRepository->all();
        return $this->sendResponse($data, 'Data retrieved successfully.');
    }

    // Store data
    public function create(QuotationRequest $request)
    {
        $user = getUser();

        $store = $this->quotationRepository->store($request->all(), $user?->id);
        if (!$store) {
            return $this->sendError('Something went wrong!!! [IC-01]', 500);
        }

        return $this->sendResponse($store, 'Data created successfully.');
    }

    // Get single details data
    public function show($id)
    {
        $data = $this->quotationRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found');
        }

        return $this->sendResponse($data, 'Data retrieved successfully.');
    }

    // Update data
    public function update(QuotationRequest $request, $id)
    {
        $user = getUser();

        $data = $this->quotationRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found', 404);
        }

        $updated = $this->quotationRepository->update($data, $request->all(), $user?->id);
        if (!$updated) {
            return $this->sendError('Something went wrong!!! [IC-02]', 500);
        }

        return $this->sendResponse($updated, 'Data updated successfully!');
    }

    // Delete data
    public function destroy($id)
    {
        $data = $this->quotationRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found');
        }

        $deleted = $this->quotationRepository->delete($data);
        if (!$deleted) {
            return $this->sendError('Something went wrong!!! [IC-03]', 500);
        }

        return $this->sendSuccess('Data deleted successfully!');
    }
}
