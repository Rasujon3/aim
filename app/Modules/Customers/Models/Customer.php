<?php

namespace App\Modules\Customers\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';

    protected $fillable = [
        'name',
        'company',
        'email',
        'phone',
        'address',
        'city',
        'postal_code',
        'state',
        'country',
        'active',
        'assigned_user',
        'created_by',
        'updated_by',
    ];
    protected $casts = [
        'active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    protected $hidden = [
        'assigned_user',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    public static function rules($id = null)
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:customers,name,' . $id],
            'company'      => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email,' . $id],
            'phone' => ['required', 'string', 'max:255', 'unique:customers,phone,' . $id],
            'address'      => ['nullable', 'string', 'max:500'],
            'city'         => ['nullable', 'string', 'max:100'],
            'postal_code'  => ['nullable', 'string', 'max:20'],
            'state'        => ['nullable', 'string', 'max:100'],
            'country'      => ['nullable', 'string', 'max:100'],
            'active'        => ['nullable', 'boolean'],
            'assigned_user' => ['nullable', 'exists:users,id'],
        ];
    }

    public static function assignUserRules()
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'user_id' => ['required', 'exists:users,id'],
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
