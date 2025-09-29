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
                echo "📇 Ignorado file: $path <br>";
                continue;
            }

            $files[] = $path;
        }
    }

    return $files;
}


/**
 * Reemplaza tokens en el contenido de un archivo.
 */
function replaceTokens(string $content, array $replacements): string
{
    $tokens = preg_split('/(\W+)/u', $content, -1, PREG_SPLIT_DELIM_CAPTURE);

    foreach ($tokens as &$token) {
        if (isset($replacements[$token])) {
            $token = $replacements[$token];
        }
    }
    unset($token);

    return implode('', $tokens);
}

/**
 * Procesa todos los ficheros encontrados, hace backup y log.
 */
function processFiles(array $files, array $replacements, string $logFile): void
{
    $log = [];

    foreach ($files as $file) {
        $content = file_get_contents($file);
        $newContent = replaceTokens($content, $replacements);
        if ($content !== $newContent) {

            copy($file, $file . '.bak'); // backup
            file_put_contents($file, $newContent);
            echo "✅ Modificado: $file <br>\n";
            $log[] = "Modificado: $file";
        }
    }

    if (!empty($log)) {
        file_put_contents($logFile, implode(PHP_EOL, $log));
        echo "📄 Log guardado en $logFile<br>\n";
    } else {
        echo "ℹ️ No se hicieron cambios.<br>\n";
    }
}
