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
    $iri = 'https://ams.memorix.io/record/image/' . $row->uuid;

    if (!$row->sk_documenttype) $row->sk_documenttype = 'foto';
    if (!$row->sk_datering) $row->sk_datering = '1970-09-02';
    
    $row->dc_title = addcslashes ($row->dc_title, "\"");
    $cb_name = addcslashes ($row->sr_rechthebbende, "\"");
    
    $copyright_holder_iri = "https://ams.memorix.io/record/corporate_body/{$hash}";
    $creator_iri = MemorixDB::getRandomPersonIri();
    $media_iri = MemorixDB::getRandomImageIri();
    $image_type_iri = "https://ams.memorix.io/collections/vocabularies/image_type/{$row->sk_documenttype}";
    $collectionIri = "https://ams.memorix.io/collection/" . md5($row->dc_provenance);
    
    if ($row->sk_gebouw) {
        $depicted_building_hash = md5($row->sk_gebouw);
        $depicted_building_tuple = "formValues:child <https://ams.memorix.io/record/image/{$row->uuid}/content_description/depicted_building> ;";
    } else {
        $depicted_building_tuple = '';
    }

    if ($row->sr_rechthebbende) {
        $hash = md5($row->sr_rechthebbende);
    } else {
        //fetch a random one:
        $hash = md5($dbh->getRandomAuteursrechthebbende());
    }

    $geo = $dbh->getRecordGeo($row->uuid);
    if ($geo) {
        $sk_geografische_naam = $geo->sk_geografische_naam;
        $street_uuid = $dbh->getKeyStraatnaam($sk_geografische_naam);
        $depicted_addres_tuple = "formValues:child <https://ams.memorix.io/record/image/{$row->uuid}/content_description/depicted_address> ;";
    } else {
        $depicted_addres_tuple = '';
    }
    

    list ($start, $end) = explode('-', $row->sk_datering);
    $y = substr($start, 0, 4);
    $m = substr($start, 4, 2);
    $d = substr($start, 6, 2);

    include __DIR__ .'/templates/Record.ttl';

    if ($depicted_addres_tuple) {
        include __DIR__ .'/templates/Record/depicted_addres.ttl';
    }
    if ($depicted_building_tuple) {
        include __DIR__ .'/templates/Record/depicted_building.ttl';
    }
}