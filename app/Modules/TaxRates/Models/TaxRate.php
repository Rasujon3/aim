<?php

namespace App\Modules\TaxRates\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class TaxRate extends Model
{
    use HasFactory;

    protected $table = 'tax_rates';

    protected $fillable = [
        'name',
        'rate',
        'is_fixed_amount',
        'created_by',
        'updated_by',
    ];
    protected $casts = [
        'is_fixed_amount' => 'boolean',
        'rate' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    protected $hidden = [
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    public static function rules($id = null)
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:tax_rates,name,' . $id],
            'rate' => ['required', 'numeric', 'min:1', 'max:999999.99'],
            'is_fixed_amount' => ['required', 'boolean'],
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
