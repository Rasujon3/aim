<?php

namespace App\Modules\Buildings\Controllers;

use App\Http\Controllers\AppBaseController;
use App\Modules\Buildings\Repositories\BuildingRepository;
use App\Modules\Buildings\Requests\BuildingRequest;
use App\Modules\Floors\Repositories\FloorRepository;
use App\Modules\Floors\Requests\FloorRequest;
use Illuminate\Http\Request;

class BuildingController extends AppBaseController
{
    protected BuildingRepository $buildingRepository;

    public function __construct(BuildingRepository $buildingRepo)
    {
        $this->buildingRepository = $buildingRepo;
    }

    // Fetch all data
    public function index(BuildingRequest $request)
    {
        $user = getUser();
        $userHotelIds = getUserHotelIds($user?->id, $user?->user_type_id);
        $hotelId = $request->hotel_id;

        if (!in_array($hotelId, $userHotelIds,false)) {
            return $this->sendError('You can not access this data.', 403);
        }

        $data = $this->buildingRepository->all($hotelId);
        return $this->sendResponse($data, 'Data retrieved successfully.');
    }

    // Store data
    public function store(BuildingRequest $request)
    {
        $user = getUser();
        $userHotelIds = getUserHotelIds($user?->id, $user?->user_type_id);
        $name = $request->name;
        $hotelId = $request->hotel_id;

        if (!in_array($hotelId, $userHotelIds,false)) {
            return $this->sendError('You can not access this data.', 403);
        }

        $checkNameExist = $this->buildingRepository->checkNameExist($hotelId, $name);
        if ($checkNameExist) {
            return $this->sendError('Building name already exist.', 404);
        }

        $store = $this->buildingRepository->store($request->all(), $user?->id);
        if (!$store) {
            return $this->sendError('Something went wrong!!! [BC-01]', 500);
        }

        return $this->sendResponse($store, 'Data created successfully!');
    }

    // Get single details data
    public function show($id)
    {
        $data = $this->buildingRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found');
        }

        return $this->sendResponse($data, 'Data retrieved successfully.');
    }

    // Update data
    public function update(BuildingRequest $request, $id)
    {
        $userId = getUser()?->id;
        $hotelId = $request->hotel_id;
        $name = $request->name;

        $data = $this->buildingRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found');
        }

        $checkNameExist = $this->buildingRepository->checkNameUpdateExist($data->id, $hotelId, $name);
        if ($checkNameExist) {
            return $this->sendError('Building name already exist.', 404);
        }

        $updated = $this->buildingRepository->update($data, $request->all(), $userId);
        if (!$updated) {
            return $this->sendError('Something went wrong!!! [FC-02]', 500);
        }

        return $this->sendResponse($updated, 'Data updated successfully!');
    }

    // Delete data
    public function destroy($id)
    {
        $data = $this->buildingRepository->find($id);
        if (!$data) {
            return $this->sendError('Data not found');
        }
        $this->buildingRepository->delete($data);
        return $this->sendSuccess('Data deleted successfully!');
    }
}
