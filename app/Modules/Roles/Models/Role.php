<?php

namespace App\Modules\Roles\Models;

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

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = [
        'name',
        'is_editable',
        'is_deletable',
        'created_by',
        'updated_by',
    ];
    protected $casts = [
        'is_editable'   => 'boolean',
        'is_deletable'  => 'boolean',
    ];
    protected $hidden = [
        'created_by',
        'updated_by',
    ];

    public static function rules($id = null)
    {
        return [
            'name' => ['required', 'string', 'max:45', 'unique:roles,name,' . $id],
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
            'name' => ['required', 'string', 'max:45', 'unique:roles,name'],
        ];
    }
    public function createdBy() : belongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }
    public function updatedBy() : belongsTo
    {
        return $this->belongsTo(User::class,'updated_by');
    }
}
