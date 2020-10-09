<?php
if (!file_exists(__DIR__. '/config.php')) {
    fwrite(STDERR, "config file is missing, did you copy the dist config?\n");
    exit(1);
}

$modules = [
    'Prefix',
    'RecordTypes',
    'ConditionSet',
    'Collections',
    'ImageTypes',
    'Straatnamen',
    'Gebouwen',
    'Auteursrechthebbenden',
    'Persons',
    'Voc',
    'Records'
];

$scriptName = basename(array_shift($argv));

$helpIds = ['?', 'help', 'info'];
if ($argv && in_array(trim($argv[0], '--'), $helpIds)) {
    $modulesTxt = implode("\n      - ", $modules);
    echo <<<USG
{$scriptName} [--help|-?] [limit [offset] [module 1] ... [module n]]
  Parser from AMS Memorix Maior Beeldbank collection to Memorix Turtle (TTL) file,
  writes output directly to STDOUT. Requires connection to MM PgSQL db server (VPN).

  Controle TTL Generator:
    limit:         maximum numbers of records to fetch from Memorix Maior, 
                   default value is 10, use '-1' to fetch all record (!!!)
    ofset:         start fetching records from Memorix Maior from this offset,
                   default value is 0
    module:        name of Module you want to use to generate TTL file, 
                   default mode is run all Modules

  Possible modules: 
      - {$modulesTxt}

USG;
    exit;
}
require_once __DIR__ .'/includes/MemorixDB.php';

$dbh = MemorixDB::getInstance()->setLimitAndOffset($argv);

$requestedModules = [];
while (count($argv)) {
    $module = array_shift($argv);
    if (false !== $ix = array_search($module, $modules)) {
        $requestedModules[] = $modules[$ix];
    }
}

if (count($requestedModules))
    $modules = $requestedModules;

foreach ($modules as $module) {
    include __DIR__ . "/includes/{$module}.php";
}
