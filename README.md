# AMS TTL Generator

generator.php [--help|-?] [limit [offset] [module 1] ... [module n]]
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
      - Prefix
      - RecordTypes
      - ConditionSet
      - Collections
      - ImageTypes
      - Straatnamen
      - Gebouwen
      - Auteursrechthebbenden
      - Persons
      - Voc
      - Records
