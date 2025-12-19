<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;

class AssetHelper
{
    /**
     * Render assets tags from manifest.json in production
     * or from Vite dev server in local environment
     */
    public static function render($expression): string
    {
        $assets = json_decode($expression, true);
        if (!is_array($assets)) {
            // If it's a string like 'resources/css/app.css', wrap it
            $assets = [$expression];
        }

        $html = '';
        $manifestPath = public_path('build/manifest.json');

        if (config('app.env') !== 'local' && File::exists($manifestPath)) {
            // Production: load from manifest.json
            $manifest = json_decode(File::get($manifestPath), true);

            foreach ($assets as $asset) {
                // Remove leading 'resources/'
                $assetKey = str_replace('resources/', '', $asset);
                
                if (isset($manifest[$assetKey])) {
                    $file = $manifest[$assetKey]['file'];
                    
                    if (str_ends_with($file, '.css')) {
                        $html .= sprintf(
                            '<link rel="stylesheet" href="%s" />' . PHP_EOL,
                            asset("build/$file")
                        );
                    } elseif (str_ends_with($file, '.js')) {
                        $html .= sprintf(
                            '<script type="module" src="%s"></script>' . PHP_EOL,
                            asset("build/$file")
                        );
                    }
                    
                    // Load CSS dependencies
                    if (isset($manifest[$assetKey]['css'])) {
                        foreach ($manifest[$assetKey]['css'] as $cssFile) {
                            $html .= sprintf(
                                '<link rel="stylesheet" href="%s" />' . PHP_EOL,
                                asset("build/$cssFile")
                            );
                        }
                    }
                }
            }
        } else {
            // Local development: use Vite dev server
            // Fallback to @vite() behavior or manual script tags
            foreach ($assets as $asset) {
                if (str_ends_with($asset, '.css')) {
                    $html .= sprintf(
                        '<link rel="stylesheet" href="http://localhost:5173/%s" />' . PHP_EOL,
                        $asset
                    );
                } else {
                    $html .= sprintf(
                        '<script type="module" src="http://localhost:5173/%s"></script>' . PHP_EOL,
                        $asset
                    );
                }
            }
        }

        return $html;
    }
}
