# 🐘 Script PHP para Buscar y Reemplazar Tokens en Archivos

Este script en **PHP** permite escanear recursivamente un directorio, encontrar archivos `.php`, excluir carpetas/archivos concretos, y reemplazar tokens definidos en el código.  
Incluye sistema de **backup** automático (`.bak`) y **log de cambios**.

---

## 🚀 Uso

1. Copia el código en un archivo PHP, por ejemplo:

   ```bash
   replace.php
   ```

2. Configura las variables de ejecución:

   ```php
   $baseDir = __DIR__; // Carpeta raíz donde buscar
   $excludeDirs = ['vendor', 'storage']; // Carpetas a excluir
   $excludeFiles = ['config.php', 'index.php']; // Archivos a excluir
   $replacements = [
       'clientes'   => 'partners',
       'id_client'  => 'id_partner',
   ];
   $logFile = __DIR__ . '/replace.log';
   ```

3. Ejecuta el script en tu servidor:
   ```bash
   php replace.php
   ```

---

## 📂 Funciones

### `scanFiles()`

Escanea los archivos PHP de un directorio recursivamente, permitiendo excluir carpetas y archivos concretos.

```php
function scanFiles(string $dir, array $excludeDirs = [], array $excludeFiles = []): array
```

- **$dir** → Directorio base
- **$excludeDirs** → Array de nombres de carpetas a ignorar
- **$excludeFiles** → Array de nombres de archivos a ignorar
- **Return** → Array con rutas completas de archivos `.php` encontrados

---

### `replaceTokens()`

Busca tokens en el contenido y los reemplaza **respetando palabras completas** (para que `clientes` no afecte a `misclientes`).

```php
function replaceTokens(string $content, array $replacements): string
```

- **$content** → Contenido del archivo
- **$replacements** → Array asociativo `['buscar' => 'reemplazar']`
- **Return** → Contenido con reemplazos aplicados

---

### `processFiles()`

Procesa los archivos encontrados: hace backup, aplica reemplazos y guarda un log.

```php
function processFiles(array $files, array $replacements, string $logFile): void
```

- **$files** → Array de archivos a procesar
- **$replacements** → Tokens a reemplazar
- **$logFile** → Ruta del archivo de log

---

## 📜 Código Completo

```php
<?php

function scanFiles(string $dir, array $excludeDirs = [], array $excludeFiles = []): array
{
    $rii = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    $files = [];
    foreach ($rii as $file) {
        $path = $file->getPathname();

        foreach ($excludeDirs as $exDir) {
            if (strpos($path, DIRECTORY_SEPARATOR . $exDir . DIRECTORY_SEPARATOR) !== false) {
                echo "⏩ Ignorado dir: $path <br>";
                continue 2;
            }
        }

        if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
            $filename = $file->getFilename();
            if (in_array($filename, $excludeFiles, true)) {
                echo "📇 Ignorado file: $path <br>";
                continue;
            }
            $files[] = $path;
        }
    }
    return $files;
}

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
            $log.append(f"Modificado: {file}")
        }
    }

    if (!empty($log)) {
        file_put_contents($logFile, implode(PHP_EOL, $log));
        echo "📄 Log guardado en $logFile<br>\n";
    } else {
        echo "ℹ️ No se hicieron cambios.<br>\n";
    }
}
```

---

## ⚠️ Recomendaciones

- Siempre revisa los backups `.bak` antes de subir cambios a producción.
- Usa un entorno de pruebas antes de correr en el servidor principal.
- Puedes mejorar la velocidad excluyendo carpetas pesadas como `vendor/` o `node_modules/`.
