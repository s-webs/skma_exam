<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\PublicStorageImage;
use Symfony\Component\HttpFoundation\Response;

class PublicMediaController extends Controller
{
    public function show(string $filename): Response
    {
        if (! preg_match('/^[a-zA-Z0-9._-]+$/', $filename)) {
            abort(404);
        }

        $absolutePath = PublicStorageImage::absolutePathForFilename($filename);

        if ($absolutePath === null) {
            abort(404);
        }

        return response()->file($absolutePath, [
            'Content-Type' => mime_content_type($absolutePath) ?: 'application/octet-stream',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
