<?php

namespace App\Modules\Payments\Models;

use App\Models\User;
use App\Modules\Companies\Models\Company;
use App\Modules\Customers\Models\Customer;
use App\Modules\Invoices\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = [
        'payment_number',
        'invoice_id',
        'company_id',
        'customer_id',
        'user_id',
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
            'invoice_id'   => ['required', 'exists:invoices,id'],
            'company_id'   => ['nullable', 'exists:companies,id'],
            'customer_id'  => ['nullable', 'exists:customers,id'],
            'user_id'      => ['nullable', 'exists:users,id'],

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
    public function invoice()   { return $this->belongsTo(Invoice::class); }
    public function company()   { return $this->belongsTo(Company::class); }
    public function customer()  { return $this->belongsTo(Customer::class); }
    public function user()      { return $this->belongsTo(User::class); }
    public function createdBy() : belongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }
    public function updatedBy() : belongsTo
    {
        return $this->belongsTo(User::class,'updated_by');
    }
}
