<?php

namespace App\Modules\Invoices\Models;

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

class InvoiceItem extends Model
{
    use HasFactory;

    protected $table = 'invoice_items';

    protected $fillable = [
        'invoice_id',
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
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
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
