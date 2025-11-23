<?php

namespace App\Modules\Categories\Controllers;

use App\Http\Controllers\AppBaseController;
use App\Modules\Categories\Repositories\CategoryRepository;
use App\Modules\Categories\Requests\CategoryRequest;
use App\Modules\Roles\Repositories\RoleRepository;
use App\Modules\Roles\Requests\RoleRequest;
use Illuminate\Http\Request;

class CategoryController extends AppBaseController
{
    protected CategoryRepository $categoryRepository;

    public function __construct(CategoryRepository $categoryRepo)
    {
        $this->categoryRepository = $categoryRepo;
    }

    // Fetch all data
    public function index()
    {
        $data = $this->categoryRepository->all();
        return $this->sendResponse($data, 'Data retrieved successfully.');
    }

    // Store data
    public function store(CategoryRequest $request)
    {
        $user = getUser();

        $store = $this->categoryRepository->store($request->all(), $user?->id);
        if (!$store) {
            return $this->sendError('Something went wrong!!! [CC-01]', 500);
        }

        return $this->sendResponse($store, 'Data created successfully.');
    }

    // Get single details data
    public function show($id)
    {
        $data = $this->categoryRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found');
        }

        return $this->sendResponse($data, 'Data retrieved successfully.');
    }

    // Update data
    public function update(CategoryRequest $request, $id)
    {
        $user = getUser();

        $data = $this->categoryRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found', 404);
        }

        $updated = $this->categoryRepository->update($data, $request->all(), $user?->id);
        if (!$updated) {
            return $this->sendError('Something went wrong!!! [RC-02]', 500);
        }

        return $this->sendResponse($updated, 'Data updated successfully!');
    }

    // Delete data
    public function destroy($id)
    {
        $data = $this->categoryRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found');
        }

        $this->categoryRepository->delete($data);
        return $this->sendSuccess('Data deleted successfully!');
    }
}
