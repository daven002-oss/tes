<?php
/*
 * Builds the VELA database from schema.sql + seed.sql.
 *
 *   php database/install.php
 *
 * It reads the connection settings from includes/db_config.php.
 * WARNING: schema.sql starts by deleting the old tables, so running this again resets the data.
 * (You can do the same thing by hand in phpMyAdmin: Import -> schema.sql, then Import -> seed.sql.)
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Run this from the command line:  php database/install.php');
}

$cfg = require __DIR__ . '/../includes/db_config.php';

/** Cut an SQL file into single statements (a ';' inside quotes or a comment does not count). */
function split_sql(string $sql): array
{
    $statements = [];
    $current    = '';
    $quote      = '';           // ' or " or ` while inside quotes
    $len        = strlen($sql);

    for ($i = 0; $i < $len; $i++) {
        $c = $sql[$i];

        if ($quote !== '') {
            $current .= $c;
            if ($c === '\\' && $quote !== '`' && $i + 1 < $len) {   // escaped character
                $current .= $sql[++$i];
            } elseif ($c === $quote) {
                if ($i + 1 < $len && $sql[$i + 1] === $quote) {     // doubled quote ('')
                    $current .= $sql[++$i];
                } else {
                    $quote = '';
                }
            }
            continue;
        }
        if ($c === '-' && substr($sql, $i, 2) === '--') {            // comment to end of line
            $end = strpos($sql, "\n", $i);
            $i = $end === false ? $len : $end;
            continue;
        }
        if ($c === '#') {
            $end = strpos($sql, "\n", $i);
            $i = $end === false ? $len : $end;
            continue;
        }
        if ($c === "'" || $c === '"' || $c === '`') {
            $quote = $c;
            $current .= $c;
            continue;
        }
        if ($c === ';') {
            if (trim($current) !== '') {
                $statements[] = trim($current);
            }
            $current = '';
            continue;
        }
        $current .= $c;
    }
    if (trim($current) !== '') {
        $statements[] = trim($current);
    }
    return $statements;
}

try {
    $pdo = new PDO(
        "mysql:host={$cfg['host']};port={$cfg['port']};charset=utf8mb4",
        $cfg['user'],
        $cfg['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    fwrite(STDERR, "Could not connect to MySQL: " . $e->getMessage() . "\n");
    fwrite(STDERR, "Is MySQL running (Laragon -> Start All)? Check host/user/password in includes/db_config.php\n");
    exit(1);
}

foreach (['schema.sql', 'seed.sql'] as $file) {
    $statements = split_sql(file_get_contents(__DIR__ . '/' . $file));
    foreach ($statements as $n => $statement) {
        try {
            $pdo->exec($statement);
        } catch (PDOException $e) {
            fwrite(STDERR, "\nError in $file, statement " . ($n + 1) . ":\n" . substr($statement, 0, 200) . "\n\n" . $e->getMessage() . "\n");
            exit(1);
        }
    }
    echo "$file imported (" . count($statements) . " statements)\n";
}

echo "\nDone. Database '{$cfg['name']}' is ready.\n";
echo "Demo logins:  admin@vela.test / Admin1234   and   alya@vela.test / User1234\n";
