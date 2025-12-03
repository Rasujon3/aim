<?php

namespace App\Modules\Profiles\Controllers;

use App\Http\Controllers\AppBaseController;
use App\Modules\Customers\Repositories\CustomerRepository;
use App\Modules\Customers\Requests\CustomerRequest;
use App\Modules\Payments\Repositories\PaymentRepository;
use App\Modules\Payments\Requests\PaymentRequest;
use App\Modules\Profiles\Repositories\ProfileRepository;
use App\Modules\Profiles\Requests\ProfileRequest;
use App\Modules\Vendors\Repositories\VendorRepository;
use App\Modules\Vendors\Requests\VendorRequest;
use Illuminate\Http\Request;

class ProfileController extends AppBaseController
{
    protected ProfileRepository $profileRepository;

    public function __construct(ProfileRepository $profileRepo)
    {
        $this->profileRepository = $profileRepo;
    }

    // Fetch all data
    public function index()
    {
        $data = $this->profileRepository->all();
        return $this->sendResponse($data, 'Data retrieved successfully.');
    }

    // Store data
    public function updateData(ProfileRequest $request)
    {
        $user = getUser();

        $email = $request->email;

        $checkEmail = $this->profileRepository->checkEmail($email, $user?->id);
        if ($checkEmail) {
            return $this->sendError('Email already exists. Please add different email.', 409);
        }

        $store = $this->profileRepository->updateData($request->all(), $user?->id);
        if (!$store) {
            return $this->sendError('Something went wrong!!! [PPC-01]', 500);
        }

        return $this->sendResponse($store, 'Data created successfully.');
    }

    // Update data
    public function changePassword(ProfileRequest $request)
    {
        $user = getUser();

        $checkCurrentPassword = $this->profileRepository->checkCurrentPassword($request->current_password, $user?->id);
        if (!$checkCurrentPassword) {
            return $this->sendError('Current password does not match.', 409);
        }

        $updated = $this->profileRepository->changePassword($request->all(), $user?->id);
        if (!$updated) {
            return $this->sendError('Something went wrong!!! [PPC-02]', 500);
        }

        return $this->sendResponse($updated, 'Data updated successfully!');
    }

    // Delete data
    public function destroy($id)
    {
        $data = $this->profileRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found');
        }

        $deleted = $this->profileRepository->delete($data);
        if (!$deleted) {
            return $this->sendError('Something went wrong!!! [PC-03]', 500);
        }

        return $this->sendSuccess('Data deleted successfully!');
    }
}
