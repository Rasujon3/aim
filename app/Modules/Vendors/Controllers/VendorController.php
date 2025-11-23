<?php

namespace App\Modules\Vendors\Controllers;

use App\Http\Controllers\AppBaseController;
use App\Modules\Vendors\Repositories\VendorRepository;
use App\Modules\Vendors\Requests\VendorRequest;
use Illuminate\Http\Request;

class VendorController extends AppBaseController
{
    protected VendorRepository $vendorRepository;

    public function __construct(VendorRepository $vendorRepo)
    {
        $this->vendorRepository = $vendorRepo;
    }

    // Fetch all data
    public function index()
    {
        $data = $this->vendorRepository->all();
        return $this->sendResponse($data, 'Data retrieved successfully.');
    }

    // Store data
    public function store(VendorRequest $request)
    {
        $user = getUser();

        $store = $this->vendorRepository->store($request->all(), $user?->id);
        if (!$store) {
            return $this->sendError('Something went wrong!!! [CC-01]', 500);
        }

        return $this->sendResponse($store, 'Data created successfully.');
    }

    // Get single details data
    public function show($id)
    {
        $data = $this->vendorRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found');
        }

        return $this->sendResponse($data, 'Data retrieved successfully.');
    }

    // Update data
    public function update(VendorRequest $request, $id)
    {
        $user = getUser();

        $data = $this->vendorRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found', 404);
        }

        $updated = $this->vendorRepository->update($data, $request->all(), $user?->id);
        if (!$updated) {
            return $this->sendError('Something went wrong!!! [RC-02]', 500);
        }

        return $this->sendResponse($updated, 'Data updated successfully!');
    }

    // Delete data
    public function destroy($id)
    {
        $data = $this->vendorRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found');
        }

        $this->vendorRepository->delete($data);
        return $this->sendSuccess('Data deleted successfully!');
    }
}
