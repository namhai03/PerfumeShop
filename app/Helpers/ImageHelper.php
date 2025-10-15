<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class ImageHelper
{
    /**
     * Get the correct image URL for display
     * 
     * @param string|null $imagePath
     * @return string|null
     */
    public static function getImageUrl(?string $imagePath): ?string
    {
        if (empty($imagePath)) {
            return null;
        }

        // Nếu đã có /storage/ thì dùng trực tiếp, ngược lại dùng Storage::url()
        return str_starts_with($imagePath, '/storage/') ? $imagePath : Storage::url($imagePath);
    }
}
