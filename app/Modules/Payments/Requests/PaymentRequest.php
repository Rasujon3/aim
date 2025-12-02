<?php

namespace App\Modules\Payments\Requests;

use App\Modules\Payments\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
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
            return Payment::assignUserRules();
        }

        $id = $this->route('payment') ?: null;

        return Payment::rules($id);
    }
}
