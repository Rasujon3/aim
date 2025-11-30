<?php

namespace App\Modules\Notes\Models;

use App\Models\User;
use App\Modules\Categories\Models\Category;
use App\Modules\TaxRates\Models\TaxRate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class Note extends Model
{
    use HasFactory;

    protected $table = 'notes';

    protected $fillable = [
        'name',
        'details',
        'default_invoice',
        'default_quote',
        'created_by',
        'updated_by',
    ];
    protected $casts = [
        'default_invoice' => 'boolean',
        'default_quote' => 'boolean',
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
            'name'         => ['required', 'string', 'max:255', 'unique:notes,name,' . $id],
            'details'       => ['nullable', 'string', 'max:1000'],
            'default_sale'  => ['nullable', 'boolean'],
            'default_quote' => ['nullable', 'boolean'],
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
