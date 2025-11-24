<?php

namespace App\Modules\Products\Controllers;

use App\Http\Controllers\AppBaseController;
use App\Modules\Products\Repositories\ProductRepository;
use App\Modules\Products\Requests\ProductRequest;
use App\Modules\Vendors\Repositories\VendorRepository;
use App\Modules\Vendors\Requests\VendorRequest;
use Illuminate\Http\Request;

class ProductController extends AppBaseController
{
    protected ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepo)
    {
        $this->productRepository = $productRepo;
    }

    // Fetch all data
    public function index()
    {
        $data = $this->productRepository->all();
        return $this->sendResponse($data, 'Data retrieved successfully.');
    }

    // Store data
    public function store(ProductRequest $request)
    {
        $user = getUser();

        $store = $this->productRepository->store($request->all(), $user?->id);
        if (!$store) {
            return $this->sendError('Something went wrong!!! [CC-01]', 500);
        }

        return $this->sendResponse($store, 'Data created successfully.');
    }

    // Get single details data
    public function show($id)
    {
        $data = $this->productRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found');
        }

        return $this->sendResponse($data, 'Data retrieved successfully.');
    }

    // Update data
    public function update(ProductRequest $request, $id)
    {
        $user = getUser();

        $data = $this->productRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found', 404);
        }

        $updated = $this->productRepository->update($data, $request->all(), $user?->id);
        if (!$updated) {
            return $this->sendError('Something went wrong!!! [RC-02]', 500);
        }

        return $this->sendResponse($updated, 'Data updated successfully!');
    }

    // Delete data
    public function destroy($id)
    {
        $data = $this->productRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found');
        }

        $this->productRepository->delete($data);
        return $this->sendSuccess('Data deleted successfully!');
    }
}
