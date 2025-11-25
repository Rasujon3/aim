<?php

namespace App\Modules\Companies\Requests;

use App\Modules\Categories\Models\Category;
use App\Modules\Companies\Models\Company;
use App\Modules\Products\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class CompanyRequest extends FormRequest
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

        $id = $this->route('company') ?: null;

        return Company::rules($id);
    }
}
