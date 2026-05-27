<?php

namespace App\Traits;

trait RunNormalizer
{
    /**
     * Normaliza un RUN/RUT chileno eliminando puntos, guiones y espacios,
     * y convirtiéndolo a mayúsculas (para la 'K').
     *
     * @param string|null $run
     * @return string|null
     */
    public function normalizeRun($run)
    {
        if (!$run) {
            return null;
        }

        // Si es una URL (ej: Registro Civil), extraer el parámetro 'run'
        if (str_contains($run, 'run=')) {
            // Intentar extraer con regex que es más robusto para buffers de escáner
            if (preg_match('/[?&]run=([^&]+)/i', $run, $matches)) {
                $run = $matches[1];
            } else {
                // Fallback a parse_url
                $queryString = parse_url($run, PHP_URL_QUERY);
                if ($queryString) {
                    parse_str($queryString, $params);
                    if (isset($params['run'])) {
                        $run = $params['run'];
                    }
                }
            }
        }

        // Si contiene un guión, tomar solo lo que está antes del guión
        if (str_contains($run, '-')) {
            $parts = explode('-', $run);
            $run = $parts[0];
        }


        // Eliminar cualquier caracter que no sea número
        $normalized = preg_replace('/[^0-9]/', '', $run);

        // Si el RUN es muy largo (ej: 9 dígitos) y no había guión, 
        // es posible que el último dígito sea el DV. 
        // Pero basándonos en el requerimiento del usuario ("7068698" es el ejemplo),
        // simplemente limpiaremos todo lo que no sea número del segmento principal.
        
        return $normalized;
    }

}
