<?php

namespace App\Modules\Settings\Controllers;

use App\Http\Controllers\AppBaseController;
use App\Modules\Products\Repositories\ProductRepository;
use App\Modules\Products\Requests\ProductRequest;
use App\Modules\Settings\Repositories\SettingRepository;
use App\Modules\Settings\Requests\SettingRequest;
use App\Modules\Vendors\Repositories\VendorRepository;
use App\Modules\Vendors\Requests\VendorRequest;
use Illuminate\Http\Request;

class SettingController extends AppBaseController
{
    protected SettingRepository $settingRepository;

    public function __construct(SettingRepository $settingRepo)
    {
        $this->settingRepository = $settingRepo;
    }
    // Fetch all data
    public function index()
    {
        $data = $this->settingRepository->all();
        return $this->sendResponse($data, 'Data retrieved successfully.');
    }
    // Update data
    public function update(SettingRequest $request)
    {
        $user = getUser();

        $updated = $this->settingRepository->update($request->all(), $user?->id);
        if (!$updated) {
            return $this->sendError('Something went wrong!!! [RC-02]', 500);
        }

        return $this->sendResponse($updated, 'Data updated successfully!');
    }
}
