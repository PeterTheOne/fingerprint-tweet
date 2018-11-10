<?php

require_once 'config.php';

if (POORMANSCRON) {
    include_once('cron.php');
}

$pdo = new PDO('mysql:host=' . PDO_HOST . ';dbname=' . PDO_DATABASE . ';charset=utf8', PDO_USERNAME, PDO_PASSWORD);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8" />

        <title>fingerprint-tweet</title>

    </head>
    <body>

    <h1>fingerprint-tweet</h1>
    <p>
        Tweets with <a href="https://en.wikipedia.org/wiki/Public_key_fingerprint">PGP fingerprint</a> found since 9. March 2015.<br />
        Created by <a href="https://twitter.com/petertheone">PeterTheOne</a>,
        source on <a href="https://github.com/PeterTheOne/fingerprint-tweet">GitHub</a>.<br />
        Don't trust me, don't trust twitter and don't trust the MIT. ^^°
    </p>

    <table>
        <tr>
            <th>created</th>
            <th>screenname</th>
            <th>fingerprint</th>
            <th>signature</th>
            <th>key</th>
        </tr>
<?php
$statement = $pdo->query('SELECT * FROM fingerprint WHERE fingerprint <> \'\' ORDER BY createdat DESC');
$result = $statement->fetchAll();

foreach($result as $line) {
    $createdat = date("d.m.Y H:i:s", strtotime($line->createdat));
    $fetched = date("d.m.Y H:i:s", strtotime($line->fetched));
    $keyId = substr(str_replace(' ', '', $line->fingerprint), -16);
    echo '<tr>';
    echo '<td title="fetched: ' . $fetched . '">' . $createdat . '</td>';
    echo '<td><a href="https://twitter.com/' . $line->screenname . '">@' . $line->screenname . '</a></td>';
    echo '<td><a href="https://twitter.com/' . $line->screenname . '/status/' . $line->statusid . '">' . $line->fingerprint . '</a></td>';
    echo '<td><a href="https://pgp.mit.edu/pks/lookup?op=vindex&search=0x' . $keyId . '">' . $keyId . '</a></td>';
    echo '<td><a href="https://pgp.mit.edu/pks/lookup?op=get&search=0x' . $keyId . '">key</a></td>';
    echo '</tr>';
}
?>
    </table>

    </body>
</html>
