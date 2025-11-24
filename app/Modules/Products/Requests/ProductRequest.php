<?php

namespace App\Modules\Products\Requests;

use App\Modules\Categories\Models\Category;
use App\Modules\Products\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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

        $id = $this->route('product') ?: null;

        return Product::rules($id);
    }
}
