<?php

namespace App\Modules\Companies\Controllers;

use App\Http\Controllers\AppBaseController;
use App\Modules\Companies\Repositories\CompanyRepository;
use App\Modules\Companies\Requests\CompanyRequest;
use App\Modules\Products\Repositories\ProductRepository;
use App\Modules\Products\Requests\ProductRequest;
use App\Modules\Vendors\Repositories\VendorRepository;
use App\Modules\Vendors\Requests\VendorRequest;
use Illuminate\Http\Request;

class CompanyController extends AppBaseController
{
    protected CompanyRepository $companyRepository;

    public function __construct(CompanyRepository $companyRepo)
    {
        $this->companyRepository = $companyRepo;
    }

    // Fetch all data
    public function index()
    {
        $data = $this->companyRepository->all();
        return $this->sendResponse($data, 'Data retrieved successfully.');
    }

    // Store data
    public function create(CompanyRequest $request)
    {
        $user = getUser();

        $store = $this->companyRepository->store($request->all(), $user?->id);
        if (!$store) {
            return $this->sendError('Something went wrong!!! [CYC-01]', 500);
        }

        return $this->sendResponse($store, 'Data created successfully.');
    }

    // Get single details data
    public function show($id)
    {
        $data = $this->companyRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found');
        }

        return $this->sendResponse($data, 'Data retrieved successfully.');
    }

    // Update data
    public function update(CompanyRequest $request, $id)
    {
        $user = getUser();

        $data = $this->companyRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found', 404);
        }

        $updated = $this->companyRepository->update($data, $request->all(), $user?->id);
        if (!$updated) {
            return $this->sendError('Something went wrong!!! [CYC-02]', 500);
        }

        return $this->sendResponse($updated, 'Data updated successfully!');
    }

    // Delete data
    public function destroy($id)
    {
        $data = $this->companyRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found');
        }

        $this->companyRepository->delete($data);
        return $this->sendSuccess('Data deleted successfully!');
    }
}
