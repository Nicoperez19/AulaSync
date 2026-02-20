<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;

class ManualController extends Controller
{
    public function index()
    {
        $manualPath = base_path('docs/MANUAL.md');

        if (!file_exists($manualPath)) {
            abort(404, 'El manual no está disponible.');
        }

        $rawMarkdown = file_get_contents($manualPath);

        // Configurar el conversor CommonMark con extensiones
        $environment = new Environment([
            'html_input' => 'allow',
            'allow_unsafe_links' => false,
        ]);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());

        $converter = new MarkdownConverter($environment);

        // Extraer capítulos (## headings) del markdown
        $chapters = $this->extractChapters($rawMarkdown);

        // Convertir cada capítulo a HTML
        foreach ($chapters as &$chapter) {
            $chapter['html'] = $converter->convert($chapter['content'])->getContent();
        }

        // Convertir el contenido de la introducción (antes del primer ##) a HTML
        $introHtml = !empty($chapters[0]['intro'])
            ? $converter->convert($chapters[0]['intro'])->getContent()
            : '';

        // Reescribir rutas de imágenes relativas a la URL servida por Laravel
        foreach ($chapters as &$chapter) {
            $chapter['html'] = preg_replace_callback(
                '/<img([^>]*?)src="([^"]+)"([^>]*?)>/',
                function ($matches) {
                    $src = $matches[2];
                    // Solo reescribir rutas relativas (sin http/https/data)
                    if (!preg_match('/^(https?:|data:|\/)/', $src)) {
                        $src = route('manual.image', ['file' => basename($src)]);
                    }
                    return '<img' . $matches[1] . 'src="' . $src . '"' . $matches[3] . '>';
                },
                $chapter['html']
            );
        }
        unset($chapter);

        // Preparar datos para el buscador JS (sin html completo, solo texto plano)
        $chaptersSearch = array_values(array_map(function ($c) {
            return [
                'title' => $c['title'],
                'slug'  => $c['slug'],
                'text'  => strip_tags($c['html']),
            ];
        }, $chapters));

        return view('manual.index', [
            'chapters'       => $chapters,
            'introHtml'      => $introHtml,
            'chaptersSearch' => $chaptersSearch,
        ]);
    }

    /**
     * Extrae los capítulos basados en los encabezados ## del markdown.
     * Devuelve array de: ['title', 'slug', 'content', 'html']
     */
    protected function extractChapters(string $markdown): array
    {
        $lines = explode("\n", $markdown);
        $chapters = [];
        $currentChapter = null;
        $introLines = [];
        $firstChapterFound = false;

        foreach ($lines as $line) {
            // Detectar encabezados ##  (pero no ### ni ####)
            if (preg_match('/^## (.+)$/', $line, $matches)) {
                // Guardar capítulo anterior
                if ($currentChapter !== null) {
                    $currentChapter['content'] = implode("\n", $currentChapter['lines']);
                    unset($currentChapter['lines']);
                    $chapters[] = $currentChapter;
                }

                $firstChapterFound = true;
                $title = trim($matches[1]);
                $currentChapter = [
                    'title'   => $title,
                    'slug'    => $this->slugify($title),
                    'lines'   => ["## {$title}"],
                    'content' => '',
                    'html'    => '',
                ];
            } elseif ($firstChapterFound && $currentChapter !== null) {
                $currentChapter['lines'][] = $line;
            } else {
                $introLines[] = $line;
            }
        }

        // Agregar el último capítulo
        if ($currentChapter !== null) {
            $currentChapter['content'] = implode("\n", $currentChapter['lines']);
            unset($currentChapter['lines']);
            $chapters[] = $currentChapter;
        }

        // Adjuntar intro al primer capítulo (para referencia)
        if (!empty($chapters) && !empty($introLines)) {
            $chapters[0]['intro'] = implode("\n", $introLines);
        }

        return $chapters;
    }

    /**
     * Sirve una imagen almacenada en docs/ de forma segura.
     */
    public function serveImage(string $file)
    {
        $path = base_path('docs/' . $file);

        if (!file_exists($path)) {
            abort(404);
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mimeTypes = [
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'svg'  => 'image/svg+xml',
        ];

        $mime = $mimeTypes[$extension] ?? 'application/octet-stream';

        return response()->file($path, ['Content-Type' => $mime]);
    }

    /**
     * Genera un slug URL-compatible a partir de un título.
     */
    protected function slugify(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = str_replace(['á','é','í','ó','ú','ñ','ü'], ['a','e','i','o','u','n','u'], $text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', trim($text));
        return $text;
    }
}
