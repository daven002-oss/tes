<?php
/*
 * Database connection. Settings are in includes/db_config.php.
 * Use db() anywhere to get the connection (PDO).
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $cfg = require __DIR__ . '/db_config.php';
    try {
        $pdo = new PDO(
            "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['name']};charset=utf8mb4",
            $cfg['user'],
            $cfg['pass'],
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    } catch (PDOException $e) {
        error_log('VELA database error: ' . $e->getMessage());
        http_response_code(500);
        echo '<!DOCTYPE html><meta charset="utf-8"><title>Database not ready</title>'
           . '<body style="font-family:sans-serif;max-width:560px;margin:60px auto;padding:0 20px">'
           . '<h1>The database is not ready</h1>'
           . '<p>The website could not connect to MySQL. Please check:</p><ol>'
           . '<li>MySQL is running (in Laragon: <b>Start All</b>).</li>'
           . '<li>The settings in <code>includes/db_config.php</code> are right.</li>'
           . '<li>The database exists: in phpMyAdmin import <code>database/vela_artspace.sql</code>.</li></ol></body>';
        exit;
    }
    return $pdo;
}
