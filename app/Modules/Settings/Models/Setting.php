<?php

namespace App\Modules\Settings\Models;

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

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';

    protected $fillable = [
        'login_logo',
        'invoice_logo',
        'site_name',
        'reference',
        'reference_prefix',
        'currency_code',
        'invoice_number_prefix',
        'quotation_number_prefix',
        'payment_number_prefix',
        'created_by',
        'updated_by',
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    protected $hidden = [
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    public static function rules()
    {
        return [
            // Logos
            'login_logo'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'invoice_logo'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],

            // General
            'site_name'             => ['required', 'string', 'max:255'],
            'reference'             => ['nullable', 'string', 'max:100'],
            'reference_prefix'      => ['nullable', 'string', 'max:20'],
            'currency_code'         => ['nullable', 'string', 'size:3', 'uppercase'],

            // Number Prefixes
            'invoice_number_prefix'    => ['nullable', 'string', 'max:20'],
            'quotation_number_prefix'  => ['nullable', 'string', 'max:20'],
            'payment_number_prefix'    => ['nullable', 'string', 'max:20'],
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
    public function category() : belongsTo
    {
        return $this->belongsTo(Category::class,'category_id');
    }
    public function taxRate() : belongsTo
    {
        return $this->belongsTo(TaxRate::class,'tax_rate_id');
    }
}
