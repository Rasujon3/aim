<?php

namespace App\Modules\Categories\Requests;

use App\Modules\Categories\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
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

        $id = $this->route('category') ?: null;

        return Category::rules($id);
    }
}
