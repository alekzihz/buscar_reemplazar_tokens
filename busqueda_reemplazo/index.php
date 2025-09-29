<?php
include __DIR__ . '/funciones/funciones.php';


// ========================
// CONFIGURACIÓN
// ========================

// Directorio raíz 
$baseDir = __DIR__;

// Carpetas a excluir
$excludeDirs = ['vendor', 'cache', 'uploads', 'include', 'funciones'];
$excluirFiles = ['funciones.php', 'index.php'];

// Reemplazos
#reemplaza id_service por id_partner
$replacements = [
    'id_service' => 'id_partner',
    'services'   => 'partners',
    'serveis'   => 'partners',
];

// Fichero log
$logFile = __DIR__ . '/replace_log.txt_' . date('Ymd_His') . '.txt';

// ========================
// EJECUCIÓN
// ========================
$files = scanFiles($baseDir, $excludeDirs, $excluirFiles);
processFiles($files, $replacements, $logFile);

echo "🎉 Proceso terminado.\n";
