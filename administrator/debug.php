<?php
header('Content-Type: text/plain; charset=utf-8');
echo "=== ROTA 12 - DIAGNOSTICO ADMIN ===\n\n";
echo "REQUEST_URI:    " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
echo "SCRIPT_NAME:    " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
echo "SCRIPT_FILENAME:" . ($_SERVER['SCRIPT_FILENAME'] ?? 'N/A') . "\n";
echo "DOCUMENT_ROOT:  " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
echo "PHP_SELF:       " . ($_SERVER['PHP_SELF'] ?? 'N/A') . "\n";
echo "SERVER_NAME:    " . ($_SERVER['SERVER_NAME'] ?? 'N/A') . "\n";
echo "\n--- Calculo do basePath ---\n";
$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
echo "dirname(SCRIPT_NAME): " . $scriptDir . "\n";
$isAdmin = true;
if ($isAdmin && str_ends_with($scriptDir, '/administrator')) {
    $stripped = substr($scriptDir, 0, -strlen('/administrator'));
    echo "Apos remover /administrator: '" . $stripped . "'\n";
} else {
    echo "NAO detectou /administrator no dirname\n";
}
echo "\n--- URI final que o Router receberia ---\n";
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$pos = strpos($uri, '?');
if ($pos !== false) $uri = substr($uri, 0, $pos);
$uri = rawurldecode($uri);
echo "URI bruta: " . $uri . "\n";
if ($stripped !== '' && $stripped !== '/' && $stripped !== false) {
    $basePath = rtrim($stripped, '/');
    if (strpos($uri, $basePath) === 0) {
        $uri = substr($uri, strlen($basePath));
    }
}
$uri = '/' . ltrim($uri, '/');
echo "URI final: " . $uri . "\n";
echo "\n--- Rota esperada ---\n";
echo "Deve ser: /administrator\n";
echo "Match? " . ($uri === '/administrator' ? 'SIM ✓' : 'NAO ✗') . "\n";
