<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReplaceViteDevServerUrls
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (config('app.env') !== 'local') {
            $content = $response->getContent();
            
            // Replace Vite dev server URLs with compiled asset URLs
            $manifest = public_path('build/manifest.json');
            
            if (file_exists($manifest)) {
                $manifestData = json_decode(file_get_contents($manifest), true);
                
                // Replace @vite/client script
                $content = str_replace(
                    'http://[::1]:5173/@vite/client',
                    '',
                    $content
                );
                $content = str_replace(
                    'src="http://[::1]:5173/@vite/client"',
                    '',
                    $content
                );
                
                // Replace CSS asset references
                $content = preg_replace_callback(
                    '/href="http:\/\/\[::1\]:5173\/(resources\/[^"]*\.css)"/',
                    function ($matches) use ($manifestData) {
                        $asset = str_replace('resources/', '', $matches[1]);
                        if (isset($manifestData[$asset])) {
                            $file = $manifestData[$asset]['file'];
                            return 'href="' . asset("build/$file") . '"';
                        }
                        return $matches[0];
                    },
                    $content
                );
                
                // Replace JS asset references
                $content = preg_replace_callback(
                    '/src="http:\/\/\[::1\]:5173\/(resources\/[^"]*\.js)"/',
                    function ($matches) use ($manifestData) {
                        $asset = str_replace('resources/', '', $matches[1]);
                        if (isset($manifestData[$asset])) {
                            $file = $manifestData[$asset]['file'];
                            return 'src="' . asset("build/$file") . '"';
                        }
                        return $matches[0];
                    },
                    $content
                );
                
                $response->setContent($content);
            }
        }

        return $response;
    }
}
