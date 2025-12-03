<?php

namespace App\Modules\Profiles\Requests;

use App\Modules\Payments\Models\Payment;
use App\Modules\Profiles\Models\Profile;
use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
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

        if ($routeName === 'profiles.changePassword') {
            return Profile::changePasswordRules();
        }

        $id = $this->route('profile') ?: null;

        return Profile::updateDataRules($id);
    }
}
