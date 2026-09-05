<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment('Stay hungry, stay foolish.');
})->purpose('Display an inspiring quote');
