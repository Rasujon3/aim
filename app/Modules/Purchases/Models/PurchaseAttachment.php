<?php

namespace App\Modules\Purchases\Models;

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

class PurchaseAttachment extends Model
{
    use HasFactory;

    protected $table = 'purchase_attachments';

    protected $fillable = [
        'purchase_id',
        'img',
    ];
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}
