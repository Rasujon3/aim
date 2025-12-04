<?php

namespace App\Modules\Purchases\Models;

use App\Models\User;
use App\Modules\Categories\Models\Category;
use App\Modules\Companies\Models\Company;
use App\Modules\Customers\Models\Customer;
use App\Modules\Products\Models\Product;
use App\Modules\TaxRates\Models\TaxRate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $table = 'purchase_items';

    protected $fillable = [
        'purchase_id',
        'product_id',
        'name',
        'details',
        'quantity',
        'price',
        'unit_price',
        'net_price',
        'tax_rate_id',
        'discount',
        'discount_amount',
        'tax_amount',
        'total_discount_amount',
        'total_tax_amount',
        'total',
    ];
    protected $casts = [
        'quantity'              => 'decimal:4',
        'price'                 => 'decimal:4',
        'unit_price'            => 'decimal:4',
        'net_price'             => 'decimal:4',
        'discount'              => 'decimal:4',
        'discount_amount'       => 'decimal:4',
        'tax_amount'            => 'decimal:4',
        'total_discount_amount' => 'decimal:4',
        'total_tax_amount'      => 'decimal:4',
        'total'                 => 'decimal:4',
    ];
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function taxRate()
    {
        return $this->belongsTo(TaxRate::class);
    }
}
