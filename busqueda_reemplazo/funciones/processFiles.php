<?php
// excepciones para no tocar ciertos bloques
define('EXCEPCIONES', [
    'php' => '/<\?(.*?)\?>/s',                   // bloque <? ... 
    'img_src' => '/<img\b[^>]*\bsrc="([^"]*)"/i', // atributo src en <img>
    'a_href' => '/<a\b[^>]*\bhref="([^"]*)"/i',   // atributo href en <a>
    'include' => '/\b(include|require)(_once)?\s*\(.*?\)\s*;?/i'  // includes/requires
]);
/**
 * Reemplaza tokens en el contenido de un archivo.
 */
// Definimos reglas de filtrado
function replaceTokens(string $content, array $replacements): string
{

    foreach (EXCEPCIONES as $type => $pattern) {
        $content = preg_replace_callback($pattern, function ($matches) use ($type, $replacements) {
            if ($type === 'php') {
                $inner = $matches[1];
                // --- excepción: no tocar includes/requires ---
                // capturamos todos los includes/requires para guardarlos
                preg_match_all(
                    EXCEPCIONES['include'],
                    $inner,
                    $incMatches,
                    PREG_OFFSET_CAPTURE
                );

                if ($incMatches[0]) {
                    $result = '';
                    $lastPos = 0;
                    foreach ($incMatches[0] as $incMatch) {
                        [$incCode, $pos] = $incMatch;

                        // procesar lo que hay ANTES del include
                        $before = substr($inner, $lastPos, $pos - $lastPos);
                        $result .= replaceTokensInText($before, $replacements);

                        // dejar el include intacto
                        $result .= $incCode;

                        // actualizar posición
                        $lastPos = $pos + strlen($incCode);
                    }
                    // procesar lo que queda después del último include
                    $result .= replaceTokensInText(substr($inner, $lastPos), $replacements);

                    return '<?' . $result . '?>';
                }

                // si no hay includes/requires, procesar todo normalmente
                $processed = replaceTokensInText($inner, $replacements);
                return '<?' . $processed . '?>';
            } else {
                // caso atributos (img[src], a[href])
                $attr = $matches[1];
                $processed = replaceTokensInText($attr, $replacements);
                return str_replace($matches[1], $processed, $matches[0]);
            }
        }, $content);
    }

    return $content;
}

// auxiliar para tokenizar y reemplazar
function replaceTokensInText(string $text, array $replacements): string
{
    $tokens = preg_split('/(\W+)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

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
