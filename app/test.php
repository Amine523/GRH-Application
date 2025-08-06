<?php

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Storage;

if (!Storage::disk('public')->exists($media->path)) {
    return $media;
}

$resourceType = str_starts_with($media->media_type, 'image/') ? 'image' :
    (str_starts_with($media->media_type, 'video/') ? 'video' : null);

if (!$resourceType) {
    return $media;
}

$path = Storage::disk('public')->path($media->path);

if (app()->isLocal()) {
    $uploadOptions = [
        'folder' => "social_push_media",
        'resource_type' => $resourceType,
    ];

    if ($resourceType === 'video') {
        $uploadOptions['chunk_size'] = 6000000;
    }

    $media->url = Cloudinary::upload($path, $uploadOptions)->getSecurePath();
} else {
    $media->url = Storage::disk('public')->url($media->path);
}

return $media;
