<?php
namespace App\Core;

class Autoloader
{
    public static function register()
    {
        spl_autoload_register([__CLASS__, 'load']);
    }
    
    private static function load($className)
    {
        // Remove namespace prefix
        $className = str_replace('App\\', '', $className);
        
        // Convert namespace to file path
        $file = __DIR__ . '/../' . str_replace('\\', '/', $className) . '.php';
        
        if (file_exists($file)) {
            require_once $file;
        }
    }
}

