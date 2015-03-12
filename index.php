<?php

require_once 'config.php';

if (POORMANSCRON) {
    include_once('cron.php');
}

$pdo = new PDO('mysql:host=localhost;dbname=' . PDO_DATABASE . ';charset=utf8', PDO_USERNAME, PDO_PASSWORD);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

echo '<h2>found fingerprints</h2>';

$statement = $pdo->query('SELECT * FROM fingerprint WHERE fingerprint <> \'\' ORDER BY createdat DESC');
$result = $statement->fetchAll();

echo '<ul>';
foreach($result as $line) {
    echo '<li>';
    echo '<a href="https://twitter.com/' . $line->screenname . '">' . $line->screenname . '</a>: ';
    echo '<a href="https://twitter.com/' . $line->screenname . '/status/' . $line->statusid . '">' . $line->fingerprint . '</a>';
    echo '</li>';
}
echo '</ul>';
