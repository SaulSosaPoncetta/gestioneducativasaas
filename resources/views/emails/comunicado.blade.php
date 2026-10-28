<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>{{ $comunicado->titulo }}</title></head>
<body style="margin:0;padding:0;background:#faf7f5;font-family:Arial,Helvetica,sans-serif;color:#222">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#faf7f5;padding:24px 0">
        <tr><td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#fff;border-radius:8px;overflow:hidden">
                <tr><td style="background:#7c2d12;color:#fff;padding:18px 24px;font-size:18px;font-weight:bold">
                    {{ $comunicado->institucion->nombre }}
                </td></tr>
                <tr><td style="padding:24px">
                    <h1 style="font-size:20px;margin:0 0 6px">{{ $comunicado->titulo }}</h1>
                    <p style="color:#777;font-size:13px;margin:0 0 18px">
                        {{ $comunicado->publicado_at?->format('d/m/Y H:i') }}
                        @if ($destinatario->referencia) · {{ $destinatario->referencia }} @endif
                    </p>
                    <div style="font-size:15px;line-height:1.55">{!! $comunicado->cuerpoHtml() !!}</div>

                    @if ($comunicado->adjunto_path || $comunicado->requiere_confirmacion)
                        <p style="margin:24px 0 0">
                            <a href="{{ route('bandeja.show', $comunicado->id) }}"
                               style="display:inline-block;background:#c2410c;color:#fff;text-decoration:none;padding:10px 18px;border-radius:6px">
                                {{ $comunicado->requiere_confirmacion ? 'Ver y confirmar lectura' : 'Ver adjunto' }}
                            </a>
                        </p>
                    @endif
                </td></tr>
                <tr><td style="padding:14px 24px;background:#fff7ed;color:#888;font-size:12px">
                    Recibiste este mensaje porque figurás como destinatario en el sistema de gestión de la institución.
                </td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
