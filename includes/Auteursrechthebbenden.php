### Auteursrechthebbenden ###

<?php
require_once __DIR__ .'/MemorixDB.php';
$dbh = MemorixDB::getInstance();
$rechthebbenden = $dbh->getAuteursrechthebbenden();
while ($row = $rechthebbenden->fetch(PDO::FETCH_OBJ)) {
    if (!$row->sr_rechthebbende) continue;

    //Store in Cache, we need it for Records that do not have sr_rechthebbende
    $dbh->addAuteursrechthebbende($row->sr_rechthebbende);

    $uuid = $dbh->GUID();
    $hash = md5($row->sr_rechthebbende);
    $cb_name = addcslashes ($row->sr_rechthebbende, "\"");
    include __DIR__ . '/templates/Auteursrechthebbende.ttl';
}
