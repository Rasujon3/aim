<?php

namespace App\Modules\Buildings\Repositories;

use App\Modules\Buildings\Models\Building;
use App\Modules\Buildings\Models\BuildingImg;
use App\Modules\Floors\Models\Floor;
use App\Modules\Floors\Models\FloorImg;
use App\Modules\Hotels\Models\Hotel;
use App\Services\S3Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class BuildingRepository
{
    public function all($hotelId)
    {
        $data = Building::with('images')
            ->where('hotel_id', $hotelId)
            ->get();

        return $data;
    }
    public function store(array $data, $userId)
    {
        DB::beginTransaction();
        try {
            $data['user_id'] = $userId;
            $data['created_by'] = $userId;
            $hotelId = $data['hotel_id'];
            // Create the record in the database
            $created = Building::create($data);

            if (!empty($data['images'])) {
                $s3 = app(S3Service::class);
                foreach ($data['images'] as $file) {
                    $image_url = null;
                    $image_path = null;

                    $result = $s3->upload($file, 'building');

                    if ($result) {
                        $image_url = $result['url'];
                        $image_path = $result['path'];
                    }

                    BuildingImg::create([
                        'user_id' => $userId,
                        'hotel_id' => $hotelId,
                        'building_id' => $created->id,
                        'image_url'  => $image_url,
                        'image_path' => $image_path,
                    ]);
                }
            }

            DB::commit();

            return $created;
        } catch (Exception $e) {
            DB::rollBack();

            // Log the error
            Log::error('Error in storing data: ' , [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }
    public function update(Building $building, array $data, $userId)
    {
        DB::beginTransaction();
        try {
            $data['updated_by'] = $userId;

            // Perform the update
            $building->update($data);

            if (!empty($data['images'])) {
                $s3 = app(S3Service::class);

                $oldImages = BuildingImg::where('building_id', $building->id)->get();
                if (count($oldImages) > 0) {
                    foreach ($oldImages as $img) {
                        if ($img->image_path) {
                            $s3->delete($img->image_path);
                        }
                        $img->delete();
                    }
                }

                $hotelId = $data['hotel_id'] ?? $building->hotel_id;
                foreach ($data['images'] as $file) {
                    $image_url = null;
                    $image_path = null;

                    $result = $s3->upload($file, 'building');

                    if ($result) {
                        $image_url = $result['url'];
                        $image_path = $result['path'];
                    }

                    BuildingImg::create([
                        'user_id'   => $userId,
                        'hotel_id'  => $hotelId,
                        'building_id'  => $building->id,
                        'image_url'   => $image_url,
                        'image_path'  => $image_path,
                    ]);
                }
            }

            DB::commit();
            return $building;
        } catch (Exception $e) {
            DB::rollBack();

            // Log the error
            Log::error('Error updating data: ' , [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }
    // In FloorRepository.php
    public function delete(Building $building)
    {
        DB::beginTransaction();
        try {
            // 1. Delete related floors, rooms first
            $building->floors()->delete();
            $building->rooms()->delete();

            // 2. Get all floor images
            $oldImages = $building->images; // Use the relationship property to get the collection

            // 3. Delete images from S3
            if ($oldImages->isNotEmpty()) {
                $s3 = app(S3Service::class);
                foreach ($oldImages as $img) {
                    if ($img->image_path) {
                        $key = ltrim($img->image_path, '/');
                        Log::info('Deleting from S3:', ['path' => $img->image_path]);
                        Log::info('Deleting from S3 $key:', ['$key' => $key]);
//                        $s3->delete($img->image_path);
                        $s3->delete($key);
                    }
                }
            }

            // 4. Delete the image records from the database
            $building->images()->delete();

            // 5. Finally, delete the floor itself
            $building->delete();

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error deleting data: ' , [
                'id' => $building->id,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }
    public function find($id)
    {
        return Building::with('images')->find($id);
    }
    public function checkValid($userId, $hotelId)
    {
        $checkValid = Hotel::where('user_id', $userId)
            ->where('id', $hotelId)
            ->where('status', 'Active')
            ->exists();
        return $checkValid;
    }
    public function checkNameExist($hotelId, $name)
    {
        $checkNameExist = Building::where('hotel_id', $hotelId)
            ->where('name', $name)
            ->exists();
        return $checkNameExist;
    }
    public function checkNameUpdateExist($id, $hotelId, $name)
    {
        $checkNameExist = Building::where('hotel_id', $hotelId)
            ->where('name', $name)
            ->where('id', '!=', $id)
            ->exists();
        return $checkNameExist;
    }

    public function myHotelList($userId){
        $data = Hotel::where('user_id',$userId)->get();
        return $data;
    }
}
