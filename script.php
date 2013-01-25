<?php
include('./parser/registry_parser.php');
//$wsdl = 'http://www.clia.livestockid.ca/CLTS/public/help/en/webservices/WebServiceAnimalSearch/IAnimalSearchWSv2.wsdl';
$wsdl = 'http://registry.vamdc.eu/registry-11.12/services/RegistryQueryv1_0?wsdl';
$client = new SOAPClient($wsdl, array('trace'=>1));
$functions = $client->__getFunctions();

/*
foreach ($functions AS $function) {
print $function."\n\n";
}*/


$ks = array('keywords'=>'tap', 'orValues'=>false, 'from'=>1, 'to'=>5, 'identifiersOnly'=>false);
$result = $client->KeywordSearch($ks);

$xml = simplexml_load_string($client->__getLastResponse());
//echo $client->__getLastResponse();
$voresources = SearchResponseParser::getVOResource($xml);
$resources = SearchResponseParser::getResources($xml);

foreach($resources as $res){
    $curation = ResourceParser::getCuration($res);
    //$cap = ResourceParser::getTapCapability($res);
    $tap_access = ResourceParser::getTapAccessUrl($res);
    if($tap_access != null){
        echo ResourceParser::getTitle($res).' is a tap service : '.$tap_access."\n";
        echo 'creation date : '.ResourceParser::getCreated($res)."\n";   
        echo 'published by : '.$curation->publisher."\n";
        echo "\n";
    }

}
//echo ResourceParser::getCreated($resources[0]);

/*
foreach($xml->children('soap', true) as $el)
{    
    if ($el->getName() == 'Body')
    {
        $resource_resp = $el->children('riw', true);
        $resource = $resource_resp->children('ri', true);
        
        //get attributes
        echo $resource->attributes()->updated;
        echo "\n";
        echo $resource->attributes()->status;
        echo "\n";
        //print_r($resource->xpath('./title'));
    
        $interface = $resource->xpath('./capability[@standardID="ivo://vamdc/std/VAMDC-TAP"]/interface');
        echo $interface[0]->accessURL;

        
        //foreach($resource->children() as $field){
        //    print_r($field);
        //} 
    }
}
*/

/*
$res = $client->getResource(array('identifier'=>$resource));
$xml = simplexml_load_string($client->__getLastResponse());
$ns = $xml->getNamespaces(true);
echo $resource;
foreach($xml->children('soap', true) as $el)
{    
    if ($el->getName() == 'Body')
    {
        $resource_resp = $el->children('riw', true);
        $resource = $resource_resp->children('ri', true);
        
        //get attributes
        echo $resource->attributes()->updated;
        echo "\n";
        echo $resource->attributes()->status;
        echo "\n";
        //print_r($resource->xpath('./title'));
    
        $interface = $resource->xpath('./capability[@standardID="ivo://vamdc/std/VAMDC-TAP"]/interface');
        echo $interface[0]->accessURL;

        
        //foreach($resource->children() as $field){
        //    print_r($field);
        //} 
    }
}
*/

/*
if(is_null($res))
    echo "$res is null";
else
    echo "$res is not null";
*/
//print_r($result);

/*
foreach($result->VOResources->Resource as $resource){
    print_r($resource);
    die();
	echo 'title : '.$resource->title."\n";
	echo 'id : '.$resource->identifier."\n\n";
    echo 'capability : '.$resource->capability."\n\n";
}*/


/*
$types = $client->__getTypes();
foreach ($types AS $type) {
print $type."\n\n";
}*/
?>
