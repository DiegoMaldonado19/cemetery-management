<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Helper para generar URLs de archivos almacenados en el storage
 */
if (! function_exists('storage_url')) {
    function storage_url($path) {
        if (!$path) {
            return null;
        }

        return Storage::url($path);
    }
}

/**
 * Helper para obtener el ID del usuario actual de forma segura
 */
if (! function_exists('current_user_id')) {
    function current_user_id() {
        try {
            if (Auth::hasUser() && Auth::user()) {
                return Auth::id();
            }
        } catch (\Exception $e) {
            // Fallback silencioso
        }
        return null;
    }
}

/**
 * Helper para verificar si hay un usuario autenticado
 */
if (! function_exists('is_user_logged_in')) {
    function is_user_logged_in() {
        try {
            return Auth::hasUser() && Auth::user() !== null;
        } catch (\Exception $e) {
            return false;
        }
    }
}

/**
 * Helper para verificar si el usuario actual es administrador
 */
if (! function_exists('is_admin')) {
    function is_admin() {
        try {
            if (Auth::hasUser() && Auth::user()) {
                return Auth::user()->role && Auth::user()->role->name === 'Administrador';
            }
        } catch (\Exception $e) {
            // Fallback silencioso
        }
        return false;
    }
}

/**
 * Helper para verificar si el usuario actual es ayudante
 */
if (! function_exists('is_helper')) {
    function is_helper() {
        try {
            if (Auth::hasUser() && Auth::user()) {
                return Auth::user()->role && Auth::user()->role->name === 'Ayudante';
            }
        } catch (\Exception $e) {
            // Fallback silencioso
        }
        return false;
    }
}

/**
 * Helper para verificar si el usuario actual es auditor
 */
if (! function_exists('is_auditor')) {
    function is_auditor() {
        try {
            if (Auth::hasUser() && Auth::user()) {
                return Auth::user()->role && Auth::user()->role->name === 'Auditor';
            }
        } catch (\Exception $e) {
            // Fallback silencioso
        }
        return false;
    }
}

/**
 * Helper para obtener el usuario actual de forma segura
 */
if (! function_exists('current_user')) {
    function current_user() {
        try {
            if (Auth::hasUser()) {
                return Auth::user();
            }
        } catch (\Exception $e) {
            // Fallback silencioso
        }
        return null;
    }
}
