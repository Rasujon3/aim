<?php

namespace App\Modules\Companies\Models;

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

class Company extends Model
{
    use HasFactory;

    protected $table = 'companies';

    protected $fillable = [
        'logo',
        'logo_dark',
        'qrcode',
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'city',
        'postal_code',
        'state',
        'country',
        'show_name',
        'show_address',
        'allow_transfer',
        'bank_account_details',
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

    public static function rules($id = null)
    {
        return [
            'logo'         => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'logo_dark'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'qrcode'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg', 'max:5120'],

            // Company Info
            'name'          => ['required', 'string', 'max:255'],
            'contact_person'=> ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255', 'unique:companies,email,' . $id],
            'phone'         => ['nullable', 'string', 'max:20', 'unique:companies,phone,' . $id],

            // Address
            'address'       => ['nullable', 'string', 'max:500'],
            'city'          => ['nullable', 'string', 'max:100'],
            'postal_code'   => ['nullable', 'string', 'max:20'],
            'state'         => ['nullable', 'string', 'max:100'],
            'country'       => ['nullable', 'string', 'max:100'],

            // Checkboxes
            'show_name'     => ['required', 'boolean'],
            'show_address'  => ['required', 'boolean'],
            'allow_transfer'=> ['required', 'boolean'],

            // Bank Details
            'bank_account_details' => ['nullable', 'string', 'max:1000'],
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
