<?php

namespace App\Modules\Invoices\Requests;

use App\Modules\Categories\Models\Category;
use App\Modules\Invoices\Models\Invoice;
use App\Modules\Products\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
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

        $id = $this->route('invoice') ?: null;

        return Invoice::rules($id);
    }
    public function messages(): array
    {
        return [
            'items.required'      => 'At least one invoice item is required.',
            'items.min'           => 'At least one invoice item is required.',
            'items.*.name.required' => 'Item name is required.',
            'items.*.quantity.required' => 'Item quantity is required.',
            'items.*.price.required' => 'Item price is required.',
            'attachments.*.image' => 'Attachments must be valid image files.',
            'attachments.*.mimes' => 'Attachments must be JPG, PNG, GIF, WebP, or PDF.',
            'attachments.*.max'   => 'Each attachment must not exceed 5MB.',
        ];
    }
}
