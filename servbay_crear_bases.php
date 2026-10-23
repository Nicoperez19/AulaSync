<?php
/**
 * Crea las bases de datos de AulaSync en el MySQL de ServBay.
 * Lee DB_HOST, DB_PORT, DB_USERNAME y DB_PASSWORD de .env.servbay
 */
$cfg = [];
foreach (file(__DIR__ . '/.env.servbay', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if ($line[0] === '#' || strpos($line, '=') === false) continue;
    [$k, $v] = explode('=', $line, 2);
    $cfg[trim($k)] = trim($v, " \t\"'");
}

$dsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $cfg['DB_HOST'] ?? '127.0.0.1', $cfg['DB_PORT'] ?? '3306');

try {
    $pdo = new PDO($dsn, $cfg['DB_USERNAME'] ?? 'root', $cfg['DB_PASSWORD'] ?? '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    fwrite(STDERR, "\n[ERROR] No se pudo conectar a MySQL: " . $e->getMessage() . "\n");
    fwrite(STDERR, "Revisa DB_USERNAME / DB_PASSWORD / DB_PORT en .env.servbay (contraseña visible en ServBay > Base de datos).\n");
    exit(1);
}

foreach (['central', 'th', 'ch', 'la', 'ct', 'ccp'] as $sufijo) {
    $db = 'aulamanager_' . $sufijo;
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "[OK] Base de datos $db lista\n";
}
