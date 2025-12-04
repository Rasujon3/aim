<?php

namespace App\Modules\Purchases\Controllers;

use App\Http\Controllers\AppBaseController;
use App\Modules\Invoices\Repositories\InvoiceRepository;
use App\Modules\Invoices\Requests\InvoiceRequest;
use App\Modules\Products\Repositories\ProductRepository;
use App\Modules\Products\Requests\ProductRequest;
use App\Modules\Purchases\Repositories\PurchaseRepository;
use App\Modules\Purchases\Requests\PurchaseRequest;
use App\Modules\Vendors\Repositories\VendorRepository;
use App\Modules\Vendors\Requests\VendorRequest;
use Illuminate\Http\Request;

class PurchaseController extends AppBaseController
{
    protected PurchaseRepository $purchaseRepository;

    public function __construct(PurchaseRepository $purchaseRepo)
    {
        $this->purchaseRepository = $purchaseRepo;
    }

    // Fetch all data
    public function index()
    {
        $data = $this->purchaseRepository->all();
        return $this->sendResponse($data, 'Data retrieved successfully.');
    }

    // Store data
    public function create(PurchaseRequest $request)
    {
        $user = getUser();

        $store = $this->purchaseRepository->store($request->all(), $user?->id);
        if (!$store) {
            return $this->sendError('Something went wrong!!! [PPC-01]', 500);
        }

        return $this->sendResponse($store, 'Data created successfully.');
    }

    // Get single details data
    public function show($id)
    {
        $data = $this->purchaseRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found');
        }

        return $this->sendResponse($data, 'Data retrieved successfully.');
    }

    // Update data
    public function update(PurchaseRequest $request, $id)
    {
        $user = getUser();

        $data = $this->purchaseRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found', 404);
        }

        $updated = $this->purchaseRepository->update($data, $request->all(), $user?->id);
        if (!$updated) {
            return $this->sendError('Something went wrong!!! [PPC-02]', 500);
        }

        return $this->sendResponse($updated, 'Data updated successfully!');
    }

    // Delete data
    public function destroy($id)
    {
        $data = $this->purchaseRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found');
        }

        $deleted = $this->purchaseRepository->delete($data);
        if (!$deleted) {
            return $this->sendError('Something went wrong!!! [PPC-03]', 500);
        }

        return $this->sendSuccess('Data deleted successfully!');
    }
}
