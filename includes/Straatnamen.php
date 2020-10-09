### Straatnamen ###

<?php
require_once __DIR__ .'/MemorixDB.php';
$dbh = MemorixDB::getInstance();
$straatnamen = $dbh->getStraatnamen();
while ($row = $straatnamen->fetch(PDO::FETCH_OBJ)) {
    $created = date('Y-m-d\TH:i:m\Z', strtotime($row->modified_time));

    //Store in Cache, we need it for Records that do not have sr_rechthebbende
    $dbh->addStraatnaam($row->uuid, $row->dc_title);

    $prefLabel = addcslashes ($row->dc_title, "\"");
    include __DIR__ . '/templates/Straatnaam.ttl';
}
