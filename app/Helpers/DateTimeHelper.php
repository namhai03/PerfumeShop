<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateTimeHelper
{
    /**
     * Format datetime to Vietnamese timezone
     */
    public static function formatVietnamese($datetime, $format = 'd/m/Y H:i')
    {
        if (!$datetime) {
            return '-';
        }
        
        if (is_string($datetime)) {
            $datetime = Carbon::parse($datetime);
        }
        
        return $datetime->setTimezone('Asia/Ho_Chi_Minh')->format($format);
    }
    
    /**
     * Format date only to Vietnamese timezone
     */
    public static function formatVietnameseDate($datetime, $format = 'd/m/Y')
    {
        return self::formatVietnamese($datetime, $format);
    }
    
    /**
     * Format time only to Vietnamese timezone
     */
    public static function formatVietnameseTime($datetime, $format = 'H:i')
    {
        return self::formatVietnamese($datetime, $format);
    }
}
