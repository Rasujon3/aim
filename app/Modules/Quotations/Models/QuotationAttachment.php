<?php

namespace App\Modules\Quotations\Models;

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

class QuotationAttachment extends Model
{
    use HasFactory;

    protected $table = 'quotation_attachments';

    protected $fillable = [
        'quotation_id',
        'img',
    ];
    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }
}
