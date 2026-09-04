<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::view('/admin/{any?}', 'admin')
    ->where('any', '.*')
    ->name('admin');
