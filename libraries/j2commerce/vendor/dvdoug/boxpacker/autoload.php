<?php
/**
 * PSR-4 autoloader for dvdoug/boxpacker bundled with J2Commerce.
 *
 * Registers the DVDoug\BoxPacker namespace. Also registers Psr\Log as a
 * fallback pointing at Joomla's bundled psr/log library, used only when
 * the class has not already been loaded by Joomla's own autoloader.
 *
 * @package  J2Commerce
 */

// DVDoug\BoxPacker autoloader.
spl_autoload_register(function (string $class): void {
    $prefix = 'DVDoug\\BoxPacker\\';
    $baseDir = __DIR__ . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Psr\Log fallback — resolves against Joomla's bundled psr/log library.
// When running inside Joomla, this namespace is already registered; this
// fallback only activates outside that context (e.g., standalone CLI tests).
spl_autoload_register(function (string $class): void {
    $prefix = 'Psr\\Log\\';
    // Path relative to this file: ../../../../vendor/psr/log/src/
    $baseDir = __DIR__ . '/../../../../vendor/psr/log/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
