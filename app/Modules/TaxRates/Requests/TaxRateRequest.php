<?php

namespace App\Modules\TaxRates\Requests;

use App\Modules\TaxRates\Models\TaxRate;
use Illuminate\Foundation\Http\FormRequest;

class TaxRateRequest extends FormRequest
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

        $id = $this->route('taxRate') ?: null;

        return TaxRate::rules($id);
    }
}
