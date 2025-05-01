<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Ruta raíz que redirige al usuario según su rol
Route::get('/', function () {
    if (Auth::hasUser() && Auth::user()) {
        if (Auth::user()->isConsultationUser()) {
            return redirect('/consulta');
        } else {
            return redirect('/admin');
        }
    }
    return redirect('/admin/login');
})->name('home');

// Redirección de ruta de login directamente al panel de Filament
Route::redirect('/login', '/admin/login')->name('login');
