<?php

namespace App\Modules\Customers\Requests;

use App\Modules\Categories\Models\Category;
use App\Modules\Customers\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
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

        if ($routeName === 'customers.assignUser') {
            return Customer::assignUserRules();
        }

        $id = $this->route('customer') ?: null;

        return Customer::rules($id);
    }
}
