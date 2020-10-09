<?php

class MemorixDB extends PDO
{
    private static $instance = null;
    
    protected $limit = 10;
    protected $offset = 0;
    
    // Cache:
    protected $auteursrechthebbenden = [], $straatnamen = [];
    
    public function __construct()
    {
        list($host, $port, $dbname, $user, $password) = require __DIR__ . '/../config.php';
        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};user={$user};password={$password}";
        parent::__construct($dsn, $user, $password); 
    }
    
    public static function getInstance()
    {
      if (self::$instance == null) {
        self::$instance = new self();
      }
 
      return self::$instance;
    }
    
    public function setLimit($limit) {
        $this->limit = (int)$limit;
        return $this;
    }
    
    public function setOffset($offset) {
        $this->offset = (int)$offset;
        return $this;
    }
    
    public function setLimitAndOffset(&$argv)
    {
        foreach (['limit', 'offset'] as $v) {
            if (count($argv)) {
                if (preg_match('/^\-?\d+$/', $argv[0])) {
                    $this->$v = (int)array_shift($argv);
                }
            }
        }
        return $this;
    }
    
    public function addAuteursrechthebbende($val)
    {
        $this->auteursrechthebbenden[] = $val;
        return $this;
    }
    
    public function addStraatnaam($key, $val)
    {
        $this->straatnamen[$key] = $val;
        return $this;
    }
    
    public function getKeyStraatnaam($val)
    {
        if (count($this->straatnamen) == 0) {
            $sth = $this->getStraatnamen();
            while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
                $this->addStraatnaam($row->uuid, $row->dc_title);
            }
        }
        return array_search($val, $this->straatnamen);
    }
    
    public function getAuteursrechthebbenden()
    {
        $sth = $this->prepare('SELECT DISTINCT sr_rechthebbende FROM ams."col_entiteit_09c7ff50-70a6-11e4-a16c-d31c81183655"');
        $sth->execute();
        return $sth;
    }
    
    public function GUID()
    {
        if (function_exists('com_create_guid') === true)
        {
            return strtolower(trim(com_create_guid(), '{}'));
        }
        return strtolower(sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535)));
    }
    
    // ### Queries ###:

    public function getCollections()
    {
        return $this->getStatement('SELECT DISTINCT dc_provenance FROM ams."col_entiteit_09c7ff50-70a6-11e4-a16c-d31c81183655"');
    }
    
    public function getGebouwen()
    {
        return $this->getStatement('SELECT DISTINCT sk_gebouw FROM ams."col_entiteit_09c7ff50-70a6-11e4-a16c-d31c81183655"');
    }
    
    public function getImageTypes()
    {
        return $this->getStatement('SELECT DISTINCT sk_documenttype FROM ams."col_entiteit_09c7ff50-70a6-11e4-a16c-d31c81183655"');
    }
    
    public function getStraatnamen()
    {
        return $this->getStatement('SELECT uuid, modified_time, dc_title from ams.col_invoerlijst_value where invoerlijst=\'b2c1163a-27dc-0ac8-6a61-d7a3581de2de\'');
    }
    
    public function countRecords() {
       $count = $this->getRecords(true)->fetch(PDO::FETCH_OBJ)->c;
       return $count > $this->limit ? $this->limit : $count;
    }
    
    public function getRecords($count = false)
    {
        $sql = 'SELECT ' . ($count?'COUNT(*) AS c':'*') . ' FROM ams."col_entiteit_09c7ff50-70a6-11e4-a16c-d31c81183655"';
        $hasOrdering = false;

        if (!$count && $this->limit > 0) {
            $sql .= " ORDER BY uuid LIMIT {$this->limit}";
            $hasOrdering = true;
        }
        
        if (!$count && $this->offset > 0) {
            if (!$hasOrdering) $sql .= " ORDER BY";
            $sql .= " OFFSET {$this->offset}";
        }
        return $this->getStatement($sql);
    }
    
    public function getRecordGeo($record_uuid)
    {
        $sql = "
        SELECT uuid, sk_geografische_naam, sk_geografische_naam_number_from, sk_geografische_naam_number_to
        FROM ams.\"col_entiteit_a7482190-70b2-11e4-aafc-df43ca933b2a\" WHERE entity = '{$record_uuid} LIMIT 1";
        $sth = $this->getStatement($sql);
        return $sth->fetch(PDO::FETCH_OBJ);
    }
    
    protected function getStatement($sql)
    {
        $sth = $this->prepare($sql);
        $sth->execute();
        return $sth;
    }
    
    public function getRandomAuteursrechthebbende()
    {
        if (count($this->auteursrechthebbenden) == 0) {
            $sth = $this->getAuteursrechthebbenden();
            while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
                if (!$row->sr_rechthebbende) continue;
                $this->addAuteursrechthebbende($row->sr_rechthebbende);
            }
        }
        return $this->auteursrechthebbenden[rand(0, count($this->auteursrechthebbenden)-1)];
    }
    
    public static function getRandomPersonIri()
    {
        $iris = [
            'https://ams.memorix.io/record/person/nico_swaager',
            'https://ams.memorix.io/record/person/glw_oppenheim',
            'https://ams.memorix.io/record/person/frits_weeda',
            'https://ams.memorix.io/record/person/robert_klein',
            'https://ams.memorix.io/record/person/pieter_oosterhuis'
        ];
        return $iris[rand(0, count($iris)-1)];
    }
    
    public static function getRandomImageIri()
    {
        $iris = [
            'https://ams.memorix-test.nl/iiif/2/be6d1127-44ad-4dfe-81df-40caa4f05a56/info.json',
            'https://ams.memorix-test.nl/iiif/2/76c4c28a-f059-43d0-9ee1-ec4039e788b9/info.json',
            'https://ams.memorix-test.nl/iiif/2/98fd493c-cbbd-49be-92b7-c67f70ac968b/info.json',
            'https://ams.memorix-test.nl/iiif/2/c375b85f-6502-4cf9-82a3-97ae07b8372d/info.json',
            'https://ams.memorix-test.nl/iiif/2/a92dfd79-0475-4618-a4a9-3354e7dcd00b/info.json',
            'https://ams.memorix-test.nl/iiif/2/4aefcc08-3b42-4d69-9387-57a30ea13910/info.json',
            'https://ams.memorix-test.nl/iiif/2/eae7fb86-ee25-46c2-8001-dab803c6a091/info.json',
            'https://ams.memorix-test.nl/iiif/2/5ffbeb1a-40de-4133-9854-b500933bc587/info.json',
            'https://ams.memorix-test.nl/iiif/2/a2044549-191b-4870-ba88-09e5bb5d3b1e/info.json',
            'https://ams.memorix-test.nl/iiif/2/248e7b19-f349-46b3-ad0a-1ea2a480f88c/info.json',
            'https://ams.memorix-test.nl/iiif/2/df8aa2a8-1622-4aaa-9548-b6db658d8ced/info.json',
            'https://ams.memorix-test.nl/iiif/2/64c53356-b27a-41ae-bcde-ee344f491caf/info.json',
            'https://ams.memorix-test.nl/iiif/2/0856f0e1-8129-47f1-9640-bbc8ec765d59/info.json'
        ];
        return $iris[rand(0, count($iris)-1)];
        
    }
    
    public static function aat_documenttype($type)
    {
        switch ($type) {
            case 'tekening':
                return [
                    'http://vocab.getty.edu/aat/300033973',
                    'Visuele werken die gemaakt zijn door te tekenen, wat wil zeggen de toepassing van lijnen op een oppervlak (vaak papier) met een potlood, pen, krijt, of een ander tekeninstrument. Hierin wordt geconcentreerd op de afbakening van de vorm en minder op de kleurgeving.'
                ];
            case 'affiche':
                return [
                    'http://vocab.getty.edu/aat/300027221',
                    'Aankondigingen die zijn bedoeld om te worden opgeplakt als advertentie, reclame of als kennisgeving van een activiteit, doeleinde, product of dienst. Wordt ook gebruikt voor in grote hoeveelheden geproduceerde decoratieve afdrukken die zijn bedoeld om te worden opgehangen. Voor kleine gedrukte aankondigingen die zijn bedoeld om handmatig te worden uitgedeeld wordt \'strooibiljetten\' gebruikt.'
                ];
            case 'foto':
                return [
                    'http://vocab.getty.edu/aat/300046300',
                    "Niet-bewegende afbeeldingen vervaardigd met van stralingsgevoelige materialen (gevoelig voor licht, elektronenstralen of nucleaire straling), met uitzondering van reproductieve afdrukken van documenten en technische tekeningen. Gebruik daarvoor termen, die bijeengebracht zijn onder 'reprografische kopieën'. Foto's kunnen positief of negatief zijn, ondoorschijnend of transparant (in vaktermen: opzicht of doorzicht)."
                ];
            case 'prent':
                return [
                    'http://vocab.getty.edu/aat/300041273',
                    "Afbeeldingen gemaakt door de overdracht van beelden door middel van een drukvorm, zoals een plaat, een blok of een zeef, volgens verschillende druktechnieken. Gebruik 'afdrukken' wanneer specifiek de individuele afbeelding die het resultaat is van het afdrukken bedoeld wordt. Gebruik 'reproductieprenten' voor de prenten gemaakt naar geschilderde of getekende voorstellingen. Gebruik voor de afdrukken van foto's 'fotografische afdrukken'; zie voor termen voor afdrukken van technische tekeningen en documenten de termen onder de zoekleidingsterm 'reprografische kopieën'."
                ];
            case 'prentbriefkaart':
                return [
                    'http://vocab.getty.edu/aat/300026819',
                    "Briefkaarten met een picturale afbeelding aan een kant."
                ];
            case 'kaart':
                return [
                    'http://vocab.getty.edu/aat/300028094',
                    "Verwijst naar grafische of fotogrammetrische voorstellingen van het aardoppervlak of een gedeelte daarvan, inclusief fysieke kenmerken en politieke grenzen, waarbij elk punt overeenkomt met een geografische positie of positie in het heelal volgens een bepaalde schaal of projectie. Kan ook verwijzen naar soortgelijke voorstellingen van andere planeten, zonnen, andere hemellichamen of gebieden in het heelal. Kaarten worden gewoonlijk afgebeeld op een plat medium, zoals papier, een muur of een computerscherm. Gebruik 'globes' voor soortgelijke voorstellingen op een bol."
                ];
            case 'bouwtekening':
                return [
                    'http://vocab.getty.edu/aat/300034787',
                    "Wordt gebruikt voor tekeningen van architectuur en tekeningen voor architectonische projecten, ongeacht of het project is uitgevoerd."
                ];
        }
    }
    
}
