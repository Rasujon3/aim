<?php

namespace App\Modules\Purchases\Models;

use App\Models\User;
use App\Modules\Categories\Models\Category;
use App\Modules\Companies\Models\Company;
use App\Modules\Customers\Models\Customer;
use App\Modules\TaxRates\Models\TaxRate;
use App\Modules\Vendors\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class Purchase extends Model
{
    use HasFactory;

    protected $table = 'purchases';

    protected $fillable = [
        'purchase_number',
        'company_id',
        'vendor_id',
        'date',
        'due_date',
        'reference',
        'hash',
        'total',
        'total_tax',
        'grand_total',
        'shipping',
        'tax_rate_id',
        'order_tax_amount',
        'product_tax_amount',
        'total_tax_amount',
        'order_discount',
        'order_discount_amount',
        'product_discount_amount',
        'total_discount_amount',
        'paid',
        'due_amount',
        'note',
        'status',
        'created_by',
        'updated_by',
    ];
    protected $casts = [
        'date'      => 'date',
        'due_date'  => 'date',
//        'total'                 => 'decimal:2',
//        'total_tax'             => 'decimal:2',
//        'grand_total'           => 'decimal:2',
//        'shipping'              => 'decimal:2',
//        'order_tax_amount'      => 'decimal:2',
//        'product_tax_amount'    => 'decimal:2',
//        'total_tax_amount'      => 'decimal:2',
//        'order_discount'        => 'decimal:2',
//        'order_discount_amount' => 'decimal:2',
//        'product_discount_amount' => 'decimal:2',
//        'total_discount_amount' => 'decimal:2',
//        'paid'                  => 'decimal:2',
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
            'company_id'     => ['nullable', 'exists:companies,id'],
            'vendor_id'    => ['required', 'exists:vendors,id'],
            'date'           => ['required', 'date', 'before_or_equal:due_date'],
            'due_date'       => ['required', 'date', 'after_or_equal:date'],
            'reference'      => ['nullable', 'string', 'max:100'],
            'total'          => ['required', 'numeric', 'min:0'],
            'total_tax'      => ['nullable', 'numeric', 'min:0'],
            'grand_total'    => ['required', 'numeric', 'min:0'],
            'shipping'       => ['nullable', 'numeric', 'min:0'],
            'tax_rate_id'    => ['nullable', 'exists:tax_rates,id'],
            'order_tax_amount'      => ['nullable', 'numeric', 'min:0'],
            'product_tax_amount'    => ['nullable', 'numeric', 'min:0'],
            'total_tax_amount'      => ['nullable', 'numeric', 'min:0'],
            'order_discount'        => ['nullable', 'numeric', 'min:0'],
            'order_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'product_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'total_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'paid'                  => ['nullable', 'numeric', 'min:0', 'lte:grand_total'],
            'note'                  => ['nullable', 'string', 'max:1000'],
            'status'                => ['required', 'in:Pending,Paid,Overdue,Cancelled'],

            // INVOICE ITEMS (Minimum 1 required)
            'items'          => ['required', 'array', 'min:1'],
            'items.*.product_id'  => ['required', 'exists:products,id'],
            'items.*.name'        => ['required', 'string', 'max:255'],
            'items.*.details'     => ['nullable', 'string', 'max:1000'],
            'items.*.quantity'    => ['required', 'numeric', 'min:0.0001'],
            'items.*.price'       => ['required', 'numeric', 'min:0'],
            'items.*.unit_price'  => ['required', 'numeric', 'min:0'],
            'items.*.net_price'   => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_rate_id' => ['required', 'exists:tax_rates,id'],
            'items.*.discount'    => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],

            // ATTACHMENTS (Images only)
            'attachments'    => ['nullable', 'array'],
            'attachments.*'  => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp,pdf', 'max:5120'], // 5MB
        ];
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function taxRate()
    {
        return $this->belongsTo(TaxRate::class);
    }
    public function createdBy() : belongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }
    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function attachments()
    {
        return $this->hasMany(PurchaseAttachment::class);
    }
    public function updatedBy() : belongsTo
    {
        return $this->belongsTo(User::class,'updated_by');
    }
}
