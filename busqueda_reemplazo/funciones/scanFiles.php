<?php

/**
 * Escanea archivos PHP recursivamente en un directorio,
 * excluyendo carpetas y archivos concretos.
 *
 * @param string $dir Directorio base
 * @param array $excludeDirs Carpetas a excluir
 * @param array $excludeFiles Archivos a excluir
 * @return array Lista de archivos PHP encontrados
 */
function scanFiles(string $dir, array $excludeDirs = [], array $excludeFiles = []): array
{
    $rii = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    $files = [];
    foreach ($rii as $file) {
        $path = $file->getPathname();
        // Excluir carpetas
        foreach ($excludeDirs as $exDir) {
            if (strpos($path, DIRECTORY_SEPARATOR . $exDir . DIRECTORY_SEPARATOR) !== false) {
                //echo "Excluyendo directorio: $exDir\n";
                echo "⏩ Ignorado dir: $path <br>";
                continue 2; // saltamos este archivo
            }
        }

        // Solo archivos .php
        if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
            $filename = $file->getFilename();
            // Excluir archivos concretos
            if (in_array($filename, $excludeFiles, true)) {
                echo "📇 Ignorado file: $path <br>\n";
                continue;
            }

            $files[] = $path;
        }
    }

    return $files;
}
