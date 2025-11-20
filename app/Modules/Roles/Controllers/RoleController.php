<?php

namespace App\Modules\Roles\Controllers;

use App\Http\Controllers\AppBaseController;
use App\Modules\Roles\Repositories\RoleRepository;
use App\Modules\Roles\Requests\RoleRequest;
use Illuminate\Http\Request;

class RoleController extends AppBaseController
{
    protected RoleRepository $roleRepository;

    public function __construct(RoleRepository $roleRepo)
    {
        $this->roleRepository = $roleRepo;
    }

    // Fetch all data
    public function index()
    {
        $data = $this->roleRepository->all();
        return $this->sendResponse($data, 'Data retrieved successfully.');
    }

    // Store data
    public function store(RoleRequest $request)
    {
        $user = getUser();

        $store = $this->roleRepository->store($request->all(), $user?->id);
        if (!$store) {
            return $this->sendError('Something went wrong!!! [RC-01]', 500);
        }

        return $this->sendResponse($store, 'Data created successfully.');
    }

    // Get single details data
    public function show($id)
    {
        $data = $this->roleRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found');
        }

        return $this->sendResponse($data, 'Data retrieved successfully.');
    }

    // Update data
    public function update(RoleRequest $request, $id)
    {
        $user = getUser();

        $data = $this->roleRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found', 404);
        }

        $isEditable = $this->roleRepository->isEditable($id);
        if (!$isEditable) {
            return $this->sendError('Data not have permission to edit', 403);
        }

        $updated = $this->roleRepository->update($data, $request->all(), $user?->id);
        if (!$updated) {
            return $this->sendError('Something went wrong!!! [RC-02]', 500);
        }

        return $this->sendResponse($updated, 'Data updated successfully!');
    }

    // Delete data
    public function destroy($id)
    {
        $data = $this->roleRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found');
        }

        $isDeletable = $this->roleRepository->isDeletable($id);
        if (!$isDeletable) {
            return $this->sendError('Data not have permission to delete', 403);
        }

        $this->roleRepository->delete($data);
        return $this->sendSuccess('Data deleted successfully!');
    }
}
