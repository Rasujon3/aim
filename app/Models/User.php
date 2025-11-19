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
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
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
        'phone',
        'address',
        'user_type_id',
        'role',
        'ip_address',
        'lat',
        'long',
        'day',
        'month',
        'year',
        'fbase',
        'refer_code',
        'my_refer_code',
        'email_verified_at',
        'password',
        'token',
        'status',
        'hotel_id',
        'image_url',
        'image_path',
        'otp',
        'otp_expires_at',
        'otp_request_count',
        'otp_last_request_date',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp',
        'image_path',
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
            'password' => 'hashed',
            'otp_enabled' => 'boolean',
            'otp_last_request_date' => 'datetime',
            'otp_expires_at' => 'datetime', // if you have this field
        ];
    }

    // In User model
    public static function rules($request = null)
    {
        $rules = [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'user_type_id' => 'required|in:2,3',
            'role' => 'required|string|in:user,owner',
            'ip_address' => 'nullable|ip',
            'lat' => 'nullable|numeric',
            'long' => 'nullable|numeric',
            'day' => 'nullable|string|max:2',
            'month' => 'nullable|string',
            'year' => 'nullable|string|max:4',
            'fbase' => 'nullable|string|max:255',
            'refer_code' => 'nullable|string|max:50|exists:users,my_refer_code',
            'my_refer_code' => 'nullable|string|max:50',
            'email_verified_at' => 'nullable|date',
            'password' => 'required|string|min:6',
            'confirm_password' => 'required|string|min:6|same:password',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ];

        // If request is passed, check for owner-specific rules
        if ($request && $request->user_type_id == 3 && $request->role === 'owner') {
            $rules = array_merge($rules, [
                'hotel_name' => 'required|string|max:255',
                'hotel_description' => 'required|string',
                'hotel_address' => 'required|string|max:255',
                'lat' => 'required|numeric',
                'long' => 'required|numeric',
                'package_id' => 'required|numeric|exists:packages,id',
            ]);
        }

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
            'address' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
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

}
