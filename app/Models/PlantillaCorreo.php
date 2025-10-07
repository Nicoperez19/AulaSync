<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantillaCorreo extends Model
{
    use HasFactory;

    protected $table = 'plantillas_correos';

    protected $fillable = [
        'tipo_correo_masivo_id',
        'nombre',
        'asunto',
        'contenido_html',
        'contenido_texto',
        'variables_disponibles',
        'activo',
        'creado_por',
        'actualizado_por',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'variables_disponibles' => 'array',
    ];

    /**
     * Tipo de correo asociado
     */
    public function tipoCorreo(): BelongsTo
    {
        return $this->belongsTo(TipoCorreoMasivo::class, 'tipo_correo_masivo_id');
    }

    /**
     * Usuario que creó la plantilla
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por', 'run');
    }

    /**
     * Usuario que actualizó la plantilla
     */
    public function actualizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actualizado_por', 'run');
    }

    /**
     * Scope para plantillas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Reemplaza variables en el contenido HTML
     * 
     * @param array $datos ['nombre' => 'Juan', 'fecha' => '2025-10-07', ...]
     * @return string
     */
    public function renderizarContenido(array $datos): string
    {
        $contenido = $this->getContenidoCompleto();
        
        foreach ($datos as $variable => $valor) {
            $contenido = str_replace("{{" . $variable . "}}", $valor, $contenido);
        }
        
        return $contenido;
    }

    /**
     * Obtiene el contenido HTML completo con header y footer fijos
     * 
     * @return string
     */
    public function getContenidoCompleto(): string
    {
        $logoUrl = asset('images/logo.png');
        $year = date('Y');
        
        $headerHTML = <<<HTML
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; color: white;">
            <img src="{$logoUrl}" alt="Logo Institución" style="max-width: 150px; margin-bottom: 10px;" onerror="this.style.display='none'">
            <h1 style="margin: 0; font-size: 24px;">Sistema de Gestión Académica</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9; font-size: 14px;">Notificación Automática</p>
        </div>
        HTML;
        
        $footerHTML = <<<HTML
        <div style="background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #6c757d; border-top: 3px solid #667eea; margin-top: 30px;">
            <p style="margin: 0 0 10px 0;"><strong>Este correo fue generado automáticamente por AulaSync</strong></p>
            <p style="margin: 0 0 5px 0;">Sistema de Gestión Académica</p>
            <p style="margin: 0; font-size: 11px;">Por favor no responda a este mensaje. Para consultas, contacte con su administrador.</p>
            <p style="margin: 10px 0 0 0; font-size: 11px;">&copy; {$year} AulaSync. Todos los derechos reservados.</p>
        </div>
        HTML;
        
        return <<<HTML
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Correo AulaSync</title>
        </head>
        <body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
            <div style="max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                {$headerHTML}
                <div style="padding: 30px;">
                    {$this->contenido_html}
                </div>
                {$footerHTML}
            </div>
        </body>
        </html>
        HTML;
    }

    /**
     * Obtiene las variables usadas en el contenido
     */
    public function getVariablesUsadas(): array
    {
        preg_match_all('/\{\{([^}]+)\}\}/', $this->contenido_html, $matches);
        return array_unique($matches[1]);
    }
}
