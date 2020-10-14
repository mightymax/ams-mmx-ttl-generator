### Collections ###

<?php
require_once __DIR__ .'/MemorixDB.php';
$dbh = MemorixDB::getInstance();
$collections = $dbh->getCollections();

while ($row = $collections->fetch(PDO::FETCH_OBJ)) {
    if (!$row->dc_provenance) continue;
    $hash = MemorixDB::slugify($row->dc_provenance);
    $uuid = $dbh->GUID();
    
    include __DIR__ . '/templates/Collection.ttl';
}

### Hard coded extra Collection ###
$uuid = $dbh->GUID();
echo <<<TTL
<{$baseURL}/collection/persons_and_institutions> a mmx:Collection ;
    collection:tenant               <{$baseURL}> ;
    collection:uuid                 "{$uuid}" ;
    collection:removed              false ;
    collection:name                 "Personen en Instellingen"@nl ;
    collection:website              <https://memorix.io> ;
    collection:allowedRecordType    <{$baseURL}/record-type/image> .
    
TTL;
