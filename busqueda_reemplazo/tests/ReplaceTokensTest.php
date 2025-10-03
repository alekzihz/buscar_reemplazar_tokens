<?php

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../funciones/processFiles.php'; // ajusta según dónde esté tu replaceTokens

class ReplaceTokensTest extends TestCase
{
    #[Test]
    public function reemplazaUnTokenSimple()
    {
        $replacements = ['id_service' => 'id_partner'];
        $content = '<?php $sql = "SELECT id_service FROM tabla"; ?>';

        $result = replaceTokens($content, $replacements);

        $this->assertStringContainsString('id_partner', $result);
        $this->assertStringNotContainsString('id_service', $result);
    }

    #[Test]
    public function noModificaIncludes()
    {
        $replacements = ['services' => 'partners'];
        $content = "<?php include 'services.php'; ?>";
        $content .= "<?\$services= 'services'; ?>"; // agrego otra línea con 'services'

        $result = replaceTokens($content, $replacements);

        // el include debe quedar igual
        $this->assertStringContainsString("<?php include 'services.php'; ?>",     $result);

        // pero si hubiera 'services' en otro lado, se reemplaza
        $this->assertStringContainsString('partners', $result);
    }
}
