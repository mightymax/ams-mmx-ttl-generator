### Beeldbank Records ###

<?php
require_once __DIR__ .'/MemorixDB.php';
$dbh = MemorixDB::getInstance();

if (count($argv)) {
    array_shift($argv);
    $dbh->setLimitAndOffset($argv);
    include __DIR__ . '/Prefix.php';
}

$records = $dbh->getRecords();
$count = $dbh->countRecords();
$c = 0;
$cLength = strlen($count);

while ($row = $records->fetch(PDO::FETCH_OBJ)) {
    $c ++;
    $counter = sprintf("[%0{$cLength}d/%0{$cLength}d]", $c, $count);
    $iri = $baseURL . '/record/image/' . $row->uuid;

    if (!$row->sk_documenttype) $row->sk_documenttype = 'foto';
    if (!$row->sk_datering) $row->sk_datering = '19700209-19701209';
    
    $row->dc_title = addcslashes ($row->dc_title, "\"");
    $cb_name = addcslashes ($row->sr_rechthebbende, "\"");
    
    $copyright_holder_iri = "{$baseURL}/record/corporate_body/" . MemorixDB::slugify($row->sr_rechthebbende);
    $creator_iri = MemorixDB::getRandomPersonIri();
    $media_iri = MemorixDB::getRandomImageIri();
    $image_type_iri = "{$baseURL}/collections/vocabularies/image_type/{$row->sk_documenttype}";
    $collectionIri = "{$baseURL}/collection/" . MemorixDB::slugify($row->dc_provenance);
    
    if ($row->sk_gebouw) {
        $depicted_building_hash =  MemorixDB::slugify($row->sk_gebouw);
        $depicted_building_tuple = "formValues:child <{$baseURL}/record/image/{$row->uuid}/content_description/depicted_building> ;";
    } else {
        $depicted_building_tuple = '';
    }

    $geo = $dbh->getRecordGeo($row->uuid);
    if ($geo) {
        $sk_geografische_naam = $geo->sk_geografische_naam;
        $sk_geografische_naam_hash = MemorixDB::slugify($geo->sk_geografische_naam);
        $depicted_address_tuple = "formValues:child <{$baseURL}/record/image/{$row->uuid}/content_description/depicted_address> ;";
    } else {
        $depicted_address_tuple = '';
    }
    

    list ($start, $end) = explode('-', $row->sk_datering);
    $y = substr($start, 0, 4);
    $m = substr($start, 4, 2);
    $d = substr($start, 6, 2);

    include __DIR__ .'/templates/Record.ttl';

    if ($depicted_address_tuple) {
        include __DIR__ .'/templates/Records/depicted_address.ttl';
    }
    if ($depicted_building_tuple) {
        include __DIR__ .'/templates/Records/depicted_building.ttl';
    }
}