<?php

namespace App\Modules\PurPayments\Requests;

use App\Modules\Payments\Models\Payment;
use App\Modules\PurPayments\Models\PurPayment;
use Illuminate\Foundation\Http\FormRequest;

class PurPaymentRequest extends FormRequest
{
    public function authorize()
    {
        // You can add any authorization logic here
        return true;
    }
    public function rules()
    {
        // Get the route name and apply null-safe operator
        $routeName = $this->route()?->getName();

        if ($routeName === 'payments.assignUser') {
            return PurPayment::assignUserRules();
        }

        $id = $this->route('payment') ?: null;

        return PurPayment::rules($id);
    }
}
