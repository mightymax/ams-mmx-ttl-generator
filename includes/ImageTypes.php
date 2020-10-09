### Image types ###

<?php
require_once __DIR__ .'/MemorixDB.php';
$imageTypes = MemorixDB::getInstance()->getImageTypes();
while ($row = $imageTypes->fetch(PDO::FETCH_OBJ)) {
    if (!$row->sk_documenttype) continue;
    $created = date('Y-m-d\TH:i:m\Z');
    
    list($iri, $scopeNote) = MemorixDB::aat_documenttype($row->sk_documenttype);
    $scopeNote = addcslashes ($scopeNote, "\"");
    include __DIR__ . '/templates/ImageType.ttl';
}
