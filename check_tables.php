<?php

$pdo = new PDO('sqlite:database/database.sqlite');
$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
foreach ($tables as $table) {
    echo $table['name'] . "\n";
}
?>
