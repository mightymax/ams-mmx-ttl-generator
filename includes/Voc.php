### Vocabulary Terms ###
<?php

include __DIR__ .'/data/Vocabularies.ttl';
include __DIR__ .'/data/sex.ttl';

foreach (['corporate_body_type', 'nationality'] as $vocName) {
    echo "### {$vocName} ###\n";
    $terms = json_decode(file_get_contents(__DIR__.'/data/aat.' . $vocName . '.json'));
    foreach ($terms->results->bindings as $term) {
        $exactMatch = $term->id->value;
        $id = str_replace('http://vocab.getty.edu/aat/', '', $exactMatch);
        $created = $term->latestModification->value.'Z';
        $prefLabel = addcslashes ($term->dutchLabel->value, "\"");
        $scopeNote  = addcslashes ($term->skopeNote->value, "\"");
        include __DIR__ . '/templates/VocTerm.ttl';
    }
}

/*
300111149: nationalities

Sparql op Getty voor Corporate Body Terms:
  PREFIX gvp: <http://vocab.getty.edu/ontology#> 
  PREFIX aat: <http://vocab.getty.edu/aat/>
  PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
  PREFIX prov: <http://www.w3.org/ns/prov#> 
  PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
  PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
  PREFIX dcterms: <http://purl.org/dc/terms/>

  SELECT * 
  WHERE {
    SERVICE <http://vocab.getty.edu/sparql> {
      SELECT 
        ?id ?dutchLabel 
        (SAMPLE(?dutchSkopeNoteValue) AS ?skopeNote)
        #(MAX(?modTime) AS ?latestModification)
      WHERE {
        ?id gvp:broader aat:300025948 .
        ?id skos:prefLabel ?label .
        ?id skos:scopeNote ?skopeNoteID .
        OPTIONAL {
          ?skopeNoteID dcterms:language aat:300388256 .
          ?skopeNoteID rdf:value ?skopeNoteValue .
        }
        ?revID prov:used ?id .
        ?revID a prov:Modify .
        ?revID prov:startedAtTime ?modTime
        FILTER (lang(?label) = "nl")
        BIND (str(?label) AS ?dutchLabel)
        BIND (str(?skopeNoteValue) AS ?dutchSkopeNoteValue)
      } 
      GROUP BY ?id ?dutchLabel 
      ORDER BY ?dutchLabel
    }
  } 
*/