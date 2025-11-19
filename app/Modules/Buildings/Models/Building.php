<?php

namespace App\Modules\Buildings\Models;

use App\Models\User;
use App\Modules\Bookings\Models\BookingDetail;
use App\Modules\Floors\Models\Floor;
use App\Modules\Hotels\Models\Hotel;
use App\Modules\Rooms\Models\Room;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class Building extends Model
{
    use HasFactory;

    protected $table = 'buildings';

    protected $fillable = [
        'user_id',
        'hotel_id',
        'name',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    public static function rules()
    {
        return [
            'hotel_id' => 'required|string|max:191|exists:hotels,id',
            'name' => ['required', 'string', 'max:45'],
            'status' => 'required|in:Active,Inactive',
            'images' => 'required|array|min:1|max:3',
            'images.*' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ];
    }
    public static function listRules()
    {
        return [
            'hotel_id' => 'required|string|max:191|exists:hotels,id',
        ];
    }
    public static function updateRules()
    {
        return [
            'name' => ['required', 'string', 'max:45'],
            'hotel_id' => 'required|string|max:191|exists:hotels,id',
            'status' => 'required|in:Active,Inactive',
            'images' => 'nullable|array|min:1',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }
    public function user() : belongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function hotel() : belongsTo
    {
        return $this->belongsTo(Hotel::class,'hotel_id');
    }
    public function createdBy() : belongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }
    public function updatedBy() : belongsTo
    {
        return $this->belongsTo(User::class,'updated_by');
    }
    public function bookingDetail(): HasMany
    {
        return $this->hasMany(BookingDetail::class, 'building_id');
    }
    public function floors(): HasMany
    {
        return $this->hasMany(Floor::class, 'building_id');
    }
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
    public function images(): HasMany
    {
        return $this->hasMany(BuildingImg::class);
    }
}
