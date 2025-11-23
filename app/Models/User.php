<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Modules\Floors\Models\Floor;
use App\Modules\Hotels\Models\Hotel;
use App\Modules\Rooms\Models\Room;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Log;

//class User extends Authenticatable
class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'role_id',
        'role',
        'ip_address',
        'is_view_all',
        'is_create_all',
        'is_edit_all',
        'password',
        'token',
        'status',
        'email_verified_at',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_view_all'       => 'boolean',
            'is_create_all'     => 'boolean',
            'is_edit_all'       => 'boolean',
            'created_at'        => 'datetime',
            'updated_at'        => 'datetime',
        ];
    }

    // In User model
    public static function rules($request = null)
    {
        $rules = [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role_id' => 'required|exists:roles,id',
            'role' => 'nullable|string|max:100',
            'ip_address' => 'nullable|ip',
            'is_view_all' => 'required|boolean',
            'is_create_all' => 'required|boolean',
            'is_edit_all' => 'required|boolean',
            'email_verified_at' => 'nullable|date',
            'password' => 'required|string|min:6',
            'confirm_password' => 'required|string|min:6|same:password',
        ];

        return $rules;
    }
    public static function profileUpdateRules($request = null)
    {
        $uniqueEmailRule = Rule::unique('users', 'email');

        if ($request && $request->user_id) {
            $uniqueEmailRule->ignore($request->user_id, 'id');
        }

        $rules = [
            'user_id' => 'required|numeric|exists:users,id',
            'full_name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', $uniqueEmailRule],
            'role_id' => 'required|exists:roles,id',
            'role' => 'nullable|string|max:100',
            'ip_address' => 'nullable|ip',
            'is_view_all' => 'required|boolean',
            'is_create_all' => 'required|boolean',
            'is_edit_all' => 'required|boolean',
            'email_verified_at' => 'nullable|date',
            'password' => 'nullable|string|min:6',
            'confirm_password' => 'nullable|string|min:6|same:password',
        ];

        return $rules;
    }
    public static function changePasswordRules()
    {
        $rules = [
            'current_password' => 'required|string|min:6',
            'new_password' => 'required|string|min:6|different:current_password',
            'confirm_new_password' => 'required|string|min:6|same:new_password',
        ];

        return $rules;
    }
    public static function statusUpdateRules()
    {
        $rules = [
            'user_id' => 'required|exists:users,id',
            'package_id' => 'required|exists:packages,id',
            'hotel_id' => 'required|exists:hotels,id',
        ];

        return $rules;
    }
    public static function userInfoRules()
    {
        $rules = [
            'user_id' => 'required|numeric|exists:users,id',
        ];

        return $rules;
    }

    public function hotel(): HasOne
    {
        return $this->hasOne(Hotel::class);
    }

    public function hotels(): HasMany
    {
        return $this->hasMany(Hotel::class);
    }
    public function floors(): HasMany
    {
        return $this->hasMany(Floor::class);
    }
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

}
