<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $url;
    protected string $username;
    protected string $password;
    protected string $sender;

    public function __construct()
    {
        $this->url = config('services.icombd.baseurl', 'http://api.icombd.com/api/v2/sendsms/plaintext');
        $this->username = config('services.icombd.username');
        $this->password = config('services.icombd.password');
        $this->sender = config('services.icombd.sender');
    }

    /**
     * Send SMS via ICOMBD API
     */
    public function send(string $phone, string $message): bool
    {
        // ✅ Format phone for Bangladesh (11 digits => add 88)
        $phone = preg_replace('/\D/', '', $phone); // remove non-digits
        if (strlen($phone) === 11 && str_starts_with($phone, '01')) {
            $phone = '88' . $phone;
        }

        try {
            $response = Http::asJson()
                ->timeout(10)
                ->post($this->url, [
                    'username' => $this->username,
                    'password' => $this->password,
                    'sender'   => $this->sender,
                    'message'  => $message,
                    'to'       => $phone,
                ]);

            $body = $response->body();

            // Log full API interaction
            /*
            Log::info('ICOMBD SMS send attempt', [
                '$this->url' => $this->url,
                'username' => $this->username,
                'password' => $this->password,
                'sender' => $this->sender,
                'message' => $message,
                'to' => $phone,
                'phone' => $phone,
                'status' => $response->status(),
                'response' => $body,
            ]);
            */

            if ($response->successful() && str_contains($body, 'Success')) {
                return true;
            }

            Log::error('ICOMBD SMS failed', [
                'phone' => $phone,
                'response' => $body,
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('ICOMBD SMS Exception', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
