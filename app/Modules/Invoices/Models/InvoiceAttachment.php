<?php

namespace App\Modules\Invoices\Models;

use App\Models\User;
use App\Modules\Categories\Models\Category;
use App\Modules\Companies\Models\Company;
use App\Modules\Customers\Models\Customer;
use App\Modules\TaxRates\Models\TaxRate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class InvoiceAttachment extends Model
{
    use HasFactory;

    protected $table = 'invoice_attachments';

    protected $fillable = [
        'invoice_id',
        'img',
    ];
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
