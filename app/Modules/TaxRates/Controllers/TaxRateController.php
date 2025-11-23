<?php

namespace App\Modules\TaxRates\Controllers;

use App\Http\Controllers\AppBaseController;
use App\Modules\TaxRates\Repositories\TaxRateRepository;
use App\Modules\TaxRates\Requests\TaxRateRequest;

class TaxRateController extends AppBaseController
{
    protected TaxRateRepository $taxRateRepository;

    public function __construct(TaxRateRepository $taxRateRepo)
    {
        $this->taxRateRepository = $taxRateRepo;
    }

    // Fetch all data
    public function index()
    {
        $data = $this->taxRateRepository->all();
        return $this->sendResponse($data, 'Data retrieved successfully.');
    }

    // Store data
    public function store(TaxRateRequest $request)
    {
        $user = getUser();

        $store = $this->taxRateRepository->store($request->all(), $user?->id);
        if (!$store) {
            return $this->sendError('Something went wrong!!! [TRC-01]', 500);
        }

        return $this->sendResponse($store, 'Data created successfully.');
    }

    // Get single details data
    public function show($id)
    {
        $data = $this->taxRateRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found');
        }

        return $this->sendResponse($data, 'Data retrieved successfully.');
    }

    // Update data
    public function update(TaxRateRequest $request, $id)
    {
        $user = getUser();

        $data = $this->taxRateRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found', 404);
        }

        $updated = $this->taxRateRepository->update($data, $request->all(), $user?->id);
        if (!$updated) {
            return $this->sendError('Something went wrong!!! [TRC-02]', 500);
        }

        return $this->sendResponse($updated, 'Data updated successfully!');
    }

    // Delete data
    public function destroy($id)
    {
        $data = $this->taxRateRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found');
        }

        $this->taxRateRepository->delete($data);
        return $this->sendSuccess('Data deleted successfully!');
    }
}
