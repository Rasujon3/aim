<?php



namespace App\Http\Controllers\Auth;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Modules\Hotels\Models\Hotel;
use App\Modules\Roles\Models\Role;
use App\Services\S3Service;
use Illuminate\Support\Facades\Auth;
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
    public function register(RegisterRequest $request)
    {
        DB::beginTransaction();
        try {
            $ipAddress = $request->ip();
            $role = Role::where('id', $request->role_id)->first();

            // Create new user
            $user = User::create([
                'full_name'        => $request->full_name,
                'email'            => $request->email,
                'role_id'          => $request->role_id,
                'role'             => $role->name ?? null,
                'is_view_all'      => $request->is_view_all ?? false,
                'is_create_all'    => $request->is_create_all ?? false,
                'is_edit_all'      => $request->is_edit_all ?? false,
                'ip_address'       => $ipAddress,
                'password'         => $request->password,
                'created_by'       => Auth::user()->id ?? null,
                'status'           => 'Active'
            ]);

            DB::commit();

            return $this->sendResponse([
                'user' => $user,
            ], 'User created successfully.');

        } catch (Exception $e) {
            DB::rollBack();

            // Log the error
            Log::error('Error in Register: ', [
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
    public function userInfo(RegisterRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = User::where('id', $request->user_id)->first();

            DB::commit();

            return $this->sendResponse($user, 'User retrieved successfully.');

        } catch (Exception $e) {
            DB::rollBack();

            // Log the error
            Log::error('Error in userInfo: ', [
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
    public function userList()
    {
        try {
            $data = User::where('role_id', '!=', null)
                ->where('role', '!=', 'super_admin')
                ->where('status', 'Active')
                ->orderBy('created_at', 'DESC')
                ->get();

            return $this->sendResponse($data, 'Data retrieved successfully.');

        } catch (Exception $e) {
            // Log the error
            Log::error('Error in userList: ', [
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
    public function userProfileUpdate(RegisterRequest $request)
    {
        DB::beginTransaction();
        try {
            $ipAddress = $request->ip();
            $user = User::where('id', $request->user_id)->first();

            $role_id = $user->role_id;
            $role = $user->role;

            if ($role_id != $request->role_id) {
                $roleData = Role::where('id', $request->role_id)->first();
                $role_id = $roleData->id ?? null;
                $role = $roleData->name ?? null;
            }

            $user->update([
                'full_name'        => $request->full_name ?? $user->full_name,
                'email'            => $request->email ?? $user->email,
                'role_id'          => $role_id,
                'role'             => $role,
                'is_view_all'      => $request->is_view_all ?? $user->is_view_all,
                'is_create_all'    => $request->is_create_all ?? $user->is_create_all,
                'is_edit_all'      => $request->is_edit_all ?? $user->is_edit_all,
                'ip_address'       => $ipAddress,
                'password'         => $request->password ?? $user->password,
                'updated_by'       => Auth::user()->id ?? null,
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
    public function userProfileDelete(RegisterRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = User::where('id', $request->user_id)->first();
            $user->delete();

            DB::commit();

            return $this->sendSuccess('User deleted successfully.');

        } catch (Exception $e) {
            DB::rollBack();

            // Log the error
            Log::error('Error in deleting User: ', [
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
