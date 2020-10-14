### Auteursrechthebbenden ###

<?php
require_once __DIR__ .'/MemorixDB.php';
$dbh = MemorixDB::getInstance();
$rechthebbenden = $dbh->getAuteursrechthebbenden();
while ($row = $rechthebbenden->fetch(PDO::FETCH_OBJ)) {
    if (!$row->sr_rechthebbende) continue;

    $uuid = $dbh->GUID();
    $hash = MemorixDB::slugify($row->sr_rechthebbende);
    $cb_name = addcslashes ($row->sr_rechthebbende, "\"");
    include __DIR__ . '/templates/Auteursrechthebbende.ttl';
}
