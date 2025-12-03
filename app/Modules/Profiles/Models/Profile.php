<?php

namespace App\Modules\Profiles\Models;

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

class Profile extends Model
{
    use HasFactory;

    protected $table = 'users';
    public static function updateDataRules()
    {
        return [
            'full_name'   => ['required', 'string', 'max:150'],
            'email'       => ['required', 'email', 'max:150'],
        ];
    }
    public static function changePasswordRules()
    {
        $rules = [
            'current_password' => 'required|string|min:6',
            'new_password' => 'required|string|min:6|different:current_password',
            'confirm_new_password' => 'required|string|min:6|same:new_password',
        ];

        return $rules;
    }
}
