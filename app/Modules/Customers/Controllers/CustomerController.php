<?php

namespace App\Modules\Customers\Controllers;

use App\Http\Controllers\AppBaseController;
use App\Modules\Customers\Repositories\CustomerRepository;
use App\Modules\Customers\Requests\CustomerRequest;
use App\Modules\Vendors\Repositories\VendorRepository;
use App\Modules\Vendors\Requests\VendorRequest;
use Illuminate\Http\Request;

class CustomerController extends AppBaseController
{
    protected CustomerRepository $customerRepository;

    public function __construct(CustomerRepository $customerRepo)
    {
        $this->customerRepository = $customerRepo;
    }

    // Fetch all data
    public function index()
    {
        $data = $this->customerRepository->all();
        return $this->sendResponse($data, 'Data retrieved successfully.');
    }

    // Store data
    public function create(CustomerRequest $request)
    {
        $user = getUser();

        $store = $this->customerRepository->store($request->all(), $user?->id);
        if (!$store) {
            return $this->sendError('Something went wrong!!! [CC-01]', 500);
        }

        return $this->sendResponse($store, 'Data created successfully.');
    }

    // Get single details data
    public function show($id)
    {
        $data = $this->customerRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found');
        }

        return $this->sendResponse($data, 'Data retrieved successfully.');
    }

    // Update data
    public function update(CustomerRequest $request, $id)
    {
        $user = getUser();

        $data = $this->customerRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found', 404);
        }

        $updated = $this->customerRepository->update($data, $request->all(), $user?->id);
        if (!$updated) {
            return $this->sendError('Something went wrong!!! [RC-02]', 500);
        }

        return $this->sendResponse($updated, 'Data updated successfully!');
    }

    // Delete data
    public function destroy($id)
    {
        $data = $this->customerRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found');
        }

        $this->customerRepository->delete($data);
        return $this->sendSuccess('Data deleted successfully!');
    }
    public function assignUser(CustomerRequest $request)
    {
        $customerId = $request->input('customer_id');
        $regUserId = $request->input('user_id');

        // Check already assigned
        $checkAlreadyAssigned = $this->customerRepository->checkAlreadyAssigned($regUserId);
        if ($checkAlreadyAssigned) {
            return $this->sendError('User already assigned to this customer', 409);
        }

        // Check user role (only 'customer' role allowed)
        $checkUserRole = $this->customerRepository->checkUserRole($regUserId);
        if (!$checkUserRole) {
            return $this->sendError('Only customer role allowed', 409);
        }

        $user = getUser();
        $updated = $this->customerRepository->assignUser($customerId, $regUserId, $user?->id);

        if (!$updated) {
            return $this->sendError('Something went wrong!!! [CC-03]', 500);
        }

        return $this->sendSuccess('User assigned to customer successfully!');
    }
}
