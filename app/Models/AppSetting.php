<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = [
        'business_name',
        'tracking_url',
        'support_phone',
        'support_email',
        'print_footer',
    ];

    public static function current(): self
    {
        return self::query()->firstOrCreate([], [
            'business_name' => 'Tus Envios',
            'tracking_url' => 'tusenvios.com.co/track',
        ]);
    }
}

