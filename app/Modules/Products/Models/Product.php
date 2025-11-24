<?php

namespace App\Modules\Products\Models;

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

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'code',
        'name',
        'price',
        'category_id',
        'photo',
        'details',
        'tax_rate_id',
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
            'code'         => ['required', 'string', 'max:255', 'unique:products,code,' . $id],
            'name'         => ['required', 'string', 'max:255', 'unique:products,name,' . $id],
            'price'        => ['required', 'numeric', 'min:1', 'max:99999999.99'],
            'category_id'  => ['required', 'exists:categories,id'],
            'photo'        => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
            'details'      => ['nullable', 'string', 'max:10000'],
            'tax_rate_id'  => ['required', 'exists:tax_rates,id'],
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
