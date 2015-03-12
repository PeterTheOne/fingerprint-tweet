<?php

require_once 'vendor/autoload.php';
require_once 'config.php';

use Abraham\TwitterOAuth\TwitterOAuth;

$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);

$pdo = new PDO('mysql:host=localhost;dbname=' . PDO_DATABASE . ';charset=utf8', PDO_USERNAME, PDO_PASSWORD);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

$statement = $pdo->query('SELECT MIN(statusid) as minstatusid, MAX(statusid) as maxstatusid FROM fingerprint');
$minmax = $statement->fetch();

$statuses = array();
$statuses1 = $connection->get('search/tweets', array(
    'q' => 'PGP Fingerprint',
    'result_type' => 'recent',
    'count' => '20',
    'include_entities' => 'false',
    'since_id' => $minmax->maxstatusid
));
$statuses2 = $connection->get('search/tweets', array(
    'q' => 'PGP Fingerprint',
    'result_type' => 'recent',
    'count' => '20',
    'include_entities' => 'false',
    'max_id' => $minmax->minstatusid
));
$statuses = array_merge($statuses1->statuses, $statuses2->statuses);

$insertStatement = $pdo->prepare('
        INSERT IGNORE INTO fingerprint
        (
            statusid,
            createdat,
            text,
            userid,
            screenname,
            fingerprint,
            fetched
        )
        VALUES (
            :statusid,
            :createdat,
            :text,
            :userid,
            :screenname,
            :fingerprint,
            NOW()
        )
    ');

function matchFingerprint($text) {
    $result = preg_match("@([A-Z0-9]{4} ){9}[A-Z0-9]{4}@", $text, $matches);
    if ($result === 1) {
        return $matches[0];
    }
    return '';
}

foreach ($statuses as $status) {
    $fingerprint = matchFingerprint($status->text);
    $createdat = date("Y-m-d H:i:s", strtotime($status->created_at));
    $insertStatement->bindParam(':statusid', $status->id_str);
    $insertStatement->bindParam(':createdat', $createdat);
    $insertStatement->bindParam(':text', $status->text);
    $insertStatement->bindParam(':userid', $status->user->id_str);
    $insertStatement->bindParam(':screenname', $status->user->screen_name);
    $insertStatement->bindParam(':fingerprint', $fingerprint);
    $insertStatement->execute();
}
