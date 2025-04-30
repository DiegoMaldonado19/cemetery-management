<?php

/**
 * Helper para generar URLs de archivos almacenados en el storage
 */
if (! function_exists('storage_url')) {
    function storage_url($path)
    {
        if (!$path) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::url($path);
    }
}
