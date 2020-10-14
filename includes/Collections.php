### Collections ###

<?php
require_once __DIR__ .'/MemorixDB.php';
$dbh = MemorixDB::getInstance();
$sth = $dbh->prepare('SELECT DISTINCT dc_provenance FROM ams."col_entiteit_09c7ff50-70a6-11e4-a16c-d31c81183655"');
$sth->execute();

while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
    if (!$row->dc_provenance) continue;
    $hash = md5($row->dc_provenance);
    $uuid = $dbh->GUID();
    
    include __DIR__ . '/templates/Collection.ttl';
}

### Hard coded extra Collection ###
$row->dc_provenance = "Personen en Instellingen";
$uuid = "1e8ecdeb-7053-40a2-9f59-b4da140c9547";
include __DIR__ . '/templates/Collection.ttl';

