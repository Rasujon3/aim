<?php

namespace App\Modules\Notes\Controllers;

use App\Http\Controllers\AppBaseController;
use App\Modules\Notes\Repositories\NoteRepository;
use App\Modules\Notes\Requests\NoteRequest;
use Illuminate\Http\Request;

class NoteController extends AppBaseController
{
    protected NoteRepository $noteRepository;

    public function __construct(NoteRepository $noteRepo)
    {
        $this->noteRepository = $noteRepo;
    }

    // Fetch all data
    public function index()
    {
        $data = $this->noteRepository->all();
        return $this->sendResponse($data, 'Data retrieved successfully.');
    }

    // Store data
    public function create(NoteRequest $request)
    {
        $user = getUser();

        $store = $this->noteRepository->store($request->all(), $user?->id);
        if (!$store) {
            return $this->sendError('Something went wrong!!! [NC-01]', 500);
        }

        return $this->sendResponse($store, 'Data created successfully.');
    }

    // Get single details data
    public function show($id)
    {
        $data = $this->noteRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found');
        }

        return $this->sendResponse($data, 'Data retrieved successfully.');
    }

    // Update data
    public function update(NoteRequest $request, $id)
    {
        $user = getUser();

        $data = $this->noteRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found', 404);
        }

        $updated = $this->noteRepository->update($data, $request->all(), $user?->id);
        if (!$updated) {
            return $this->sendError('Something went wrong!!! [NC-02]', 500);
        }

        return $this->sendResponse($updated, 'Data updated successfully!');
    }

    // Delete data
    public function destroy($id)
    {
        $data = $this->noteRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found');
        }

        $this->noteRepository->delete($data);
        return $this->sendSuccess('Data deleted successfully!');
    }
}
