<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auditoría: se conservan 2 años. Requiere el cron de Laravel en el servidor:
// * * * * * cd /ruta/del/proyecto && php artisan schedule:run >> /dev/null 2>&1
Schedule::command('auditoria:depurar --meses=24')->monthlyOn(1, '03:30');
