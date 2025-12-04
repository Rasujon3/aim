<?php

namespace App\Modules\PurPayments\Models;

use App\Models\User;
use App\Modules\Companies\Models\Company;
use App\Modules\Customers\Models\Customer;
use App\Modules\Invoices\Models\Invoice;
use App\Modules\Purchases\Models\Purchase;
use App\Modules\Vendors\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class PurPayment extends Model
{
    use HasFactory;

    protected $table = 'pur_payments';

    protected $fillable = [
        'pur_payment_number',
        'purchase_id',
        'company_id',
        'vendor_id',
        'date',
        'reference',
        'amount',
        'method',
        'note',
        'created_by',
        'updated_by',
    ];
    protected $casts = [
        'date'   => 'date',
        'amount' => 'decimal:2',
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
            'purchase_id'   => ['required', 'exists:purchases,id'],
            'company_id'   => ['nullable', 'exists:companies,id'],
            'vendor_id'  => ['nullable', 'exists:vendors,id'],

            'date'         => ['required', 'date'],
            'reference'    => ['nullable', 'string', 'max:100'],
            'amount'       => ['required', 'numeric', 'min:1', 'max:99999999999.99'],
            'method'       => ['nullable', 'string'],
            'note'         => ['nullable', 'string', 'max:1000'],
        ];
    }

    public static function assignUserRules()
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'user_id' => ['required', 'exists:users,id'],
        ];
    }
    public function purchase()   { return $this->belongsTo(Purchase::class); }
    public function company()   { return $this->belongsTo(Company::class); }
    public function vendor()  { return $this->belongsTo(Vendor::class); }
    public function createdBy() : belongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }
    public function updatedBy() : belongsTo
    {
        return $this->belongsTo(User::class,'updated_by');
    }
}
