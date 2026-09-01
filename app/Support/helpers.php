<?php

if (! function_exists('versioned_asset')) {
    /**
     * Build an asset URL with a cache-busting query string based on the
     * file's last modified time, so long-lived (immutable) Cache-Control
     * headers can be used safely for static assets outside the Vite build.
     */
    function versioned_asset(string $path): string
    {
        $fullPath = public_path($path);

        $version = is_file($fullPath) ? filemtime($fullPath) : time();

        return asset($path) . '?v=' . $version;
    }
}
