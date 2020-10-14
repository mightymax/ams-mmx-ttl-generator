### Straatnamen ###

<?php
require_once __DIR__ .'/MemorixDB.php';
$dbh = MemorixDB::getInstance();
$straatnamen = $dbh->getStraatnamen();

while ($row = $straatnamen->fetch(PDO::FETCH_OBJ)) {
    $created = date('Y-m-d\TH:i:m\Z', strtotime($row->modified_time));

    $hash = MemorixDB::slugify($row->dc_title);

    $prefLabel = addcslashes ($row->dc_title, "\"");
    include __DIR__ . '/templates/Straatnaam.ttl';
}
