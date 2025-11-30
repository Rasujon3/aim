<?php

namespace App\Modules\Notes\Requests;

use App\Modules\Categories\Models\Category;
use App\Modules\Notes\Models\Note;
use App\Modules\Products\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class NoteRequest extends FormRequest
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

        $id = $this->route('note') ?: null;

        return Note::rules($id);
    }
}
