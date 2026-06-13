<?php

namespace App\Traits;

trait NormalizeIraqiPhone
{
    private function normalizeIraqiPhone(string $phone): string
    {
        if (str_starts_with($phone, '964')) {
            $phone = substr($phone, 3);
        }
        if (str_starts_with($phone, '0')) {
            $phone = substr($phone, 1);
        }

        return '964' . $phone;
    }
}