<?php
include('./parser/registry_parser.php');

//initializes registry client
$client = new Requester(Requester::$REGISTRY_12_07);
$request = 'tap basecol';

//request by keywords example
$resources = $client->keywordSearch($request);
echo "\n\nResult of keywords search : $request \n";
foreach($resources as $resource){
    echo "--- Resource description : \n";
    echo "resource created : ".ResourceParser::getCreated($resource)."\n";
    echo "resource title : ".ResourceParser::getTitle($resource)."\n";
    echo "tap access url : ".ResourceParser::getTapAccessUrl($resource)."\n";
    
    //get a curation object
    $curation = ResourceParser::getCuration($resource);
    echo "publisher : ".$curation->publisher."\n";
    
    echo "capabilities access url : ".ResourceParser::getCapabilitiesAccessUrl($resource)."\n";
    echo "availability access url : ".ResourceParser::getAvailabilityAccessUrl($resource)."\n";
    echo "tap version : ".ResourceParser::getTapVersion($resource)."\n";
    
    //uses a capability object
    $capability = ResourceParser::getTapCapability($resource);
    foreach($capability->sampleQuery as $query)
        echo "sample query : ".$query."\n";
    foreach($capability->returnable as $returnable)
        echo "returnable : ".$returnable."\n";
    
    echo "---\n";
}

//get only ivo ids 
$ivo_ids = $client->keywordSearchIdOnly($request);
echo "\n\nResult of keywords search : $request \n";
foreach($ivo_ids as $id){
    echo "--- Resource ids : \n";
    echo "$id \n";
    echo "---\n";
}

//get all tap services
$resources = $client->getTapServices(); 
echo "\n\nResult of keywords search : $request \n";
foreach($resources as $resource){
    echo "--- Resource description : \n";
    echo "resource title : ".ResourceParser::getTitle($resource)."\n";
    echo "tap access url : ".ResourceParser::getTapAccessUrl($resource)."\n";
    echo "capabilities access url : ".ResourceParser::getCapabilitiesAccessUrl($resource)."\n";
    echo "availability access url : ".ResourceParser::getAvailabilityAccessUrl($resource)."\n";
    echo "---\n";
}
?>
