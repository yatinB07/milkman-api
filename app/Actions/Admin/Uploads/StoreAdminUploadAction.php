<?php

namespace App\Actions\Admin\Uploads;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StoreAdminUploadAction
{
    /** @return array{path: string, url: string} */
    public function execute(UploadedFile $file, string $directory): array
    {
        $storedPath = $file->store($directory, 'public');
        $url = Storage::disk('public')->url($storedPath);

        return [
            'path' => ltrim($url, '/'),
            'url' => $url,
        ];
    }
}
