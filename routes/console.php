<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\InstallPdfFonts;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Register PDF font installation command
Artisan::command('pdf:install-fonts', function () {
    $command = new InstallPdfFonts();
    return $command->handle();
})->purpose('Install additional fonts for PDF generation with better language support');
