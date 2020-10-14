### Gebouwen ###

<?php
require_once __DIR__ .'/MemorixDB.php';
$gebouwen = MemorixDB::getInstance()->getGebouwen();
while ($row = $gebouwen->fetch(PDO::FETCH_OBJ)) {
    if (!$row->sk_gebouw) continue;
    $sk_gebouwen = explode(',', trim($row->sk_gebouw, '{}'));
    array_walk($sk_gebouwen, function (&$val) {
        $val = trim($val, '"');
    });
    foreach ($sk_gebouwen as $sk_gebouw) {
        $hash = MemorixDB::slugify($sk_gebouw);
        $created = date('Y-m-md\TH:i:m\Z');
        $sk_gebouw = addcslashes ($sk_gebouw, "\"");
        include __DIR__ . '/templates/Gebouw.ttl';
    }
}

