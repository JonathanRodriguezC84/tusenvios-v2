<?php

namespace App\Support;

class QrCode
{
    public static function svg(string $value, int $module = 4): string
    {
        $value = trim($value);

        if ($value === '') {
            $value = 'SIN-CODIGO';
        }

        $size = max(150, min(260, $module * 58));
        $encoded = rawurlencode($value);
        $alt = htmlspecialchars('QR '.$value, ENT_QUOTES, 'UTF-8');
        $src = 'https://api.qrserver.com/v1/create-qr-code/?size='.$size.'x'.$size.'&margin=14&data='.$encoded;

        return '<img class="qr-svg" src="'.$src.'" alt="'.$alt.'" width="'.$size.'" height="'.$size.'" loading="eager" decoding="sync">';
    }
}