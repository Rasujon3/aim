<?php



namespace App\Http\Controllers\Auth;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Modules\Hotels\Models\Hotel;
use App\Services\S3Service;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;
use Stevebauman\Location\Facades\Location;

class RegisterController extends AppBaseController
{
    public function register(RegisterRequest $request, S3Service $s3)
    {
        DB::beginTransaction();
        try {
            $myReferCode = $this->generateUniqueReferCode();
            $ipAddress = $request->ip();

            $lat = $request->lat ?? '';
            $long = $request->long ?? '';

            if (empty($lat) && empty($long)) {
                $position = Location::get($ipAddress);

                if ($position) {
                    $lat = $position->latitude;
                    $long = $position->longitude;
                }
            }

            $now = Carbon::now();

            $day   = $now->format('d');
            $month = $now->format('M');
            $year  = $now->format('Y');

            $image_url = '';
            $image_path = '';
            if($request->hasFile('image')) {
                $file = $request->file('image');
                $result = $s3->upload($file, 'profile');

                if ($result) {
                    $image_url = $result['url'];
                    $image_path = $result['path'];
                }
            }

            // Create new user
            $user = User::create([
                'full_name'        => $request->full_name,
                'email'            => $request->email,
                'phone'            => $request->phone,
                'user_type_id'     => $request->user_type_id,
                'role'             => $request->role,
                'ip_address'       => $ipAddress,
                'lat'              => $lat,
                'long'             => $long,
                'day'              => $day,
                'month'            => $month,
                'year'             => $year,
                'fbase'            => $request->fbase ?? '',
                'refer_code'       => $request->refer_code ?? '',
                'image_url'        => $image_url,
                'image_path'       => $image_path,
                'my_refer_code'    => $myReferCode,
                'password'         => Hash::make($request->password),
                'status'           => 'Active'
            ]);

            // ✅ If owner, also create hotel record
            if ($request->user_type_id == 3 && $request->role === 'owner') {
                $hotel =  Hotel::create([
                    'user_id'           => $user->id,
                    'hotel_name'        => $request->hotel_name,
                    'hotel_description' => $request->hotel_description,
                    'hotel_address'     => $request->hotel_address,
                    'lat'               => $lat,
                    'long'              => $long,
                    'package_id'        => $request->package_id,
                    'status'            => 'Active',
                ]);
                $user->update(['hotel_id' => $hotel->id]);
            }

            // Generate API token
            $token = $user->createToken('API Token')->plainTextToken;

            DB::commit();

            return $this->sendResponse([
                'token' => $token,
                'user' => $user,
            ], 'User created successfully.');

        } catch (Exception $e) {
            DB::rollBack();

            // Log the error
            Log::error('Error in updating Register: ', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!!!',
            ], 500);
        }
    }
    private function generateUniqueReferCode()
    {
        do {
            $letters = Str::upper(Str::random(3)); // random 3 letters
            $numbers = random_int(100, 999);             // random 3 digit number
            $code = $letters . $numbers;

        } while (User::where('my_refer_code', $code)->exists());

        return (string) $code;
    }
    public function userInfo()
    {
        DB::beginTransaction();
        try {
            $userId = auth()->user()->id;
            $user = User::with('hotel', 'hotel.images')->where('id', $userId)->first();

            DB::commit();

            return $this->sendResponse($user, 'User retrieved successfully.');

        } catch (Exception $e) {
            DB::rollBack();

            // Log the error
            Log::error('Error in updating userInfo: ', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!!!',
            ], 500);
        }
    }
    public function userProfileUpdate(RegisterRequest $request, S3Service $s3)
    {
        DB::beginTransaction();
        try {
            $auth = auth()->user();
            if ($auth->id != $request->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.',
                ], 403);
            }
            $user = User::where('id', $request->user_id)->first();

            $image_url = $user->image_url;
            $image_path = $user->image_path;
            if($request->hasFile('image')) {
                $file = $request->file('image');
                $result = $s3->upload($file, 'profile');

                if ($result) {
                    $image_url = $result['url'];
                    $image_path = $result['path'];
                }
            }

            $user->update([
                'full_name'    => $request->full_name ?? $user->full_name,
                'email'        => $request->email ?? $user->email,
                'image_url'    => $image_url,
                'image_path'   => $image_path,
                'address'      => $request->address ?? $user->address,
            ]);

            DB::commit();

            return $this->sendResponse([
                'user' => $user,
            ], 'User updated successfully.');

        } catch (Exception $e) {
            DB::rollBack();

            // Log the error
            Log::error('Error in updating User: ', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!!!',
            ], 500);
        }
    }
    public function changePassword(RegisterRequest $request)
    {
        DB::beginTransaction();
        try {
            $auth = auth()->user();
            if ($auth->id != $request->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.',
                ], 403);
            }
            $user = User::where('id', $request->user_id)->first();
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password does not match.',
                ], 400);
            }

            $user->update([
                'password'    => Hash::make($request->new_password),
            ]);

            $request->user()->tokens()->delete();

            DB::commit();

            return $this->sendResponse([
                'user' => $user,
            ], 'Password changed successfully.');

        } catch (Exception $e) {
            DB::rollBack();

            // Log the error
            Log::error('Error in updating User: ', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!!!',
            ], 500);
        }
    }

}
