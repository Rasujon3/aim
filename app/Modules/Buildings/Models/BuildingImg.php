<?php

namespace App\Modules\Buildings\Models;

use App\Models\User;
use App\Modules\Hotels\Models\Hotel;
use App\Modules\Rooms\Models\Room;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class BuildingImg extends Model
{
    use HasFactory;

    protected $table = 'building_imgs';

    protected $fillable = [
        'user_id',
        'hotel_id',
        'building_id',
        'image_url',
        'image_path',
    ];

    protected $hidden = [
        'image_path',
        'created_at',
        'updated_at',
    ];

    public function user() : belongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function hotel() : belongsTo
    {
        return $this->belongsTo(Hotel::class,'hotel_id');
    }
    public function floor(): HasMany
    {
        return $this->hasOne(Floor::class);
    }
}
