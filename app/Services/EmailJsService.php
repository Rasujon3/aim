<?php

namespace App\Services;

use App\Modules\Settings\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmailJsService
{
    public static function sendOtpEmail($email, $otp)
    {
        $setting = Setting::first();

        $serviceId  = $setting->emailjs_service_id;
        $templateId = $setting->emailjs_template_id;
        $userId     = $setting->emailjs_user_id;

        $payload = [
            'service_id'  => $serviceId,
            'template_id' => $templateId,
            'user_id'     => $userId,
            'template_params' => [
                'email' => $email,
                'otp'   => $otp,
            ]
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->post('https://api.emailjs.com/api/v1.0/email/send', $payload);

        if ($response->failed()) {
            Log::error('EmailJS API Error: ', [
                'body' => $response->body()
            ]);
            throw new \Exception('EmailJS API Error');
        }

        return true;
    }
}
