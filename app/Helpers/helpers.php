<?php

use App\Modules\Hotels\Models\Hotel;
use App\Modules\Receptionists\Models\Receptionist;

function getUser()
{
    return Auth::user();
}

function calculateBookingPrice($price, $bookingPercentage)
{
    return ceil(($price * $bookingPercentage) / 100);
}

function getBedIcon(string $bedType, string $numBeds): ?string
{
    $key = strtolower($bedType) . '_' . $numBeds; // e.g., "single_2"
    $icons = config('services.bed_icons');
    return $icons[$key] ?? null;
}
if (! function_exists('getUserHotelIds')) {
    function getUserHotelIds($userId, $userTypeId)
    {
        if ($userTypeId == 3) {
            return Hotel::where('user_id', $userId)->pluck('id')->toArray();
        }

        if ($userTypeId == 4) {
            return Receptionist::where('user_id', $userId)
                ->pluck('hotel_id')
                ->toArray();
        }

        // অন্য টাইপ হলে খালি array রিটার্ন করবে
        return [];
    }
}
if (!function_exists('formatBangladeshPhone')) {
    /**
     * Format Bangladeshi phone number (e.g., 017xxxxxxx → 88017xxxxxxx)
     */
    function formatBangladeshPhone(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/\D/', '', $phone);

        // If number starts with '880' already, keep it
        if (str_starts_with($phone, '880')) {
            return $phone;
        }

        // If starts with '0' and total 11 digits (e.g., 017XXXXXXXX)
        if (strlen($phone) === 11 && str_starts_with($phone, '0')) {
            return '88' . $phone;
        }

        // If invalid length, return as is or handle error
        return $phone;
    }
}

