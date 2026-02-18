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

        return view('manual.index', [
            'chapters'  => $chapters,
            'introHtml' => $introHtml,
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
