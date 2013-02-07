<?php
/**
 * An API to help querying VAMDC registry via SOAP
 * 
 */
 
/**
* @author Nicolas Moreau
* @author Nicolas Moreau <nicolas.moreau@obspm.fr>
*/

/**
 * ivo id of tap services
 */
define('IVO_VAMDC_TAP', 'ivo://vamdc/std/VAMDC-TAP');

/**
 * ivo id of capabilities interface
 */
define('IVO_CAPABILITY', 'ivo://ivoa.net/std/VOSI#capabilities');

/**
 * ivo id of availability interface
 */
define('IVO_AVAILABILITY', 'ivo://ivoa.net/std/VOSI#availability');

/**
 * Object doing SOAP request against a registry
 */
class Requester{
    /**
     * wsdl of registry containing 11.12 version of services
     */
    public static $REGISTRY_11_12 = 'http://registry.vamdc.eu/registry-11.12/services/RegistryQueryv1_0?wsdl';
    
    /**
     * wsdl of registry containing 12.07 version of services
     */
    public static $REGISTRY_12_07 = 'http://registry.vamdc.eu/registry-12.07/services/RegistryQueryv1_0?wsdl';
    
    /**
     * client object
     */
    private $_client = null;
    
    /**
    * @param string $wsdl Url of wsdl file to build query object
    */
    public function __construct($wsdl){
        $this->_client = new SOAPClient($wsdl, array('trace'=>1));
    }
    
    /**
     * returns a list of all functions declared in wsdl
     * @return array A list of function provided by the service
     */
    public function getFunctions(){
        if($this->_client != null)
            return $this->_client->__getFunctions();
        return null;
    }
    
    /**
    * returns a list of all types declared in wsdl 
    * @return array a list of objects used by the service
    */
    public function getTypes(){
        if($this->_client != null)
            return $this->_client->__getTypes();
        return null;
    }
    
    /**
     * returns Resource from a keyword query into the registry
     * @param string keywords a list of words
     * @param boolean orValues if true, apply multiple word/phrase constraints with a logical OR; if false, apply with a logical AND 
     * @param int from the minimum position in the complete set of matched records of the range of records to be returned
     * @param int to the maximum number of matched records to return. The service may choose to return fewer.
     * @return SimpleXMLElement 
     */
    public function keywordSearch($keywords, $orValues=false, $from=1, $to=null){
        $this->_client->KeywordSearch(array('keywords'=>$keywords, 'orValues'=>$orValues, 'from'=>$from, 'to'=>$to, 'identifiersOnly'=>false));
        return SearchResponseParser::getResources(simplexml_load_string($this->_client->__getLastResponse()));  
    }    
    
    /**
     * searches registry according to a list of words, returns only an array of identifiers
     * @param string keywords a list of words
     * @param boolean orValues if true, apply multiple word/phrase constraints with a logical OR; if false, apply with a logical AND 
     * @param int from the minimum position in the complete set of matched records of the range of records to be returned
     * @param int to the maximum number of matched records to return. The service may choose to return fewer.
     * @return array 
     */
    public function keywordSearchIdOnly($keywords, $orValues=false, $from=1, $to=5){
        $this->_client->KeywordSearch(array('keywords'=>$keywords, 'orValues'=>$orValues, 'from'=>$from, 'to'=>$to, 'identifiersOnly'=>true));
        $values = simplexml_load_string($this->_client->__getLastResponse());    
        $sr = SearchResponseParser::getSearchResponse($values);
        $values = $sr->children('ri', true)->children('ri', true);        
        $ivos = array();
        foreach($values as $value)
            $ivos[] = (string)$value;
        return $ivos;    
    }   
   
    /**
     *  finds a resource from its identifier
     * @param string identifier
     * @return SimpleXMLElement
     */
    public function getResource($identifier){
        $this->_client->GetResource(array('identifier'=>$identifier));
        return simplexml_load_string($this->_client->__getLastResponse());    
    }    
    
    /**
     *  returns registry info
     * @return SimpleXMLElement
     */
    public function getIdentity(){
        $this->_client->GetIdentity($identifier);
        return simplexml_load_string($this->_client->__getLastResponse());    
    }   
        
    /**
     * does an XQuery search in the registry
     * @param string xquery an xquery request
     * @return SimpleXMLElement
     */
    public function xquerySearch($xquery){
        $this->_client->XQuerySearch(array('xquery'=>$xquery));
        return XQueryResponseParser::getResources(simplexml_load_string($this->_client->__getLastResponse()));
    }
    
    /**
     * get all tap Resources in the registry from an xquery request
     * @return SimpleXMLElement
     */
    public function getTapServices(){
        $xquery = 'declare namespace ri="http://www.ivoa.net/xml/RegistryInterface/v1.0"; ' .
                    'declare namespace vs="http://www.ivoa.net/xml/VODataService/v1.0"; ' .
                    'declare namespace vr="http://www.ivoa.net/xml/VOResource/v1.0"; ' .
                    'declare namespace xsi="http://www.w3.org/2001/XMLSchema-instance"; ' .
                    'for $x in //ri:Resource ' .
                    'where $x/capability[@standardID="'.IVO_VAMDC_TAP.'"] ' .
                    'return $x';                    
        return $this->xquerySearch($xquery);
    }
}

/**
 * Parses a SimpleXMLObject containing a SearchResponse into a SOAP response
 */
class SearchResponseParser{    
    /**
    * returns a SearchResponse
    * @param SimpleXMLObject xml
    * @return SimpleXMLObject
    */
    public static function getSearchResponse($xml){
        foreach($xml->children('soap', true) as $el){    
            if ($el->getName() == 'Body'){
                $resource_resp = $el->children('riw', true);
                return $resource_resp;
            }
        return null;
        }
    }
    
    /**
    * returns a VOResource
    * @param SimpleXMLObject xml
    * @return SimpleXMLObject
    */
    public static function getVOResource($xml){
        foreach($xml->children('soap', true) as $el){    
            if ($el->getName() == 'Body'){
                $resource_resp = $el->children('riw', true);
                $voresource = $resource_resp->children('ri', true);
                return $voresource;
            }
        return null;
        }
    }
    
    /**
    * returns an array of Resource
    * @param SimpleXMLObject xml
    * @return array
    */
    public static function getResources($xml){
        $voresource = self::getVOResource($xml);
        if($voresource != null)
            return $voresource->children('ri', true);
    }
}   

/**
 * Parses a SimpleXMLObject containing a XQueryResponse into a SOAP response
 */
class XQueryResponseParser{        
    /**
    * returns an array of Resource
    * @param SimpleXMLObject xml
    * @return array
    */
    public static function getResources($xml){
        foreach($xml->children('soap', true) as $el){    
            if ($el->getName() == 'Body'){                
                $resource_resp = $el->children('ri', true);
                $resources = $resource_resp->children('ri', true);
                return $resources;
            }
        return null;
        }
    }  
}  
 
/**
 * Parses a Resource SimpleXMLObject containing a Resource
 */
class ResourceParser{
    /**
     * returns creation date of a resource
     * @param SimpleXMLObject resource
     * @return string
     */
    public static function getCreated($resource){
        return (string)$resource->attributes()->created;
    }
    
    /**
     * returns status of a resource
     * @param SimpleXMLObject resource
     * @return string
     */
    public static function getStatus($resource){
        return (string)$resource->attributes()->status;
    }
    
    /**
     * returns last update date of a resource
     * @param SimpleXMLObject resource
     * @return string
     */
    public static function getUpdated($resource){
        return (string)$resource->attributes()->updated;
    }

    /**
     * returns Curation informations
     * @param SimpleXMLObject resource
     * @return SimpleXMLObject
     */
    public static function getCuration($resource){
        $result = $resource->xpath('./curation');
        if($result != false)
            return $result[0];
        return null;
    }    
    
    /**
     * returns title of a resource
     * @param SimpleXMLObject resource
     * @return string
     */
    public static function getTitle($resource){
        $result = $resource->xpath('./title');
        if($result != false)
            return (string)$result[0];
        return null;
    }      
    
    /**
     * returns Content information
     * @param SimpleXMLObject resource
     * @return SimpleXMLObject
     */
    public static function getContent($resource){
        $result = $resource->xpath('./content');
        if($result != false)
            return $result[0];      
        return null;
    }
    
    /**
     * returns Capability information for Tap interface
     * @param SimpleXMLObject resource
     * @return SimpleXMLObject
     */
    public static function getTapCapability($resource){
        $result = $resource->xpath('./capability[@standardID="'.IVO_VAMDC_TAP.'"]');
        if($result != false)
            return $result[0];
        return null;        
    }
    
    /**
     * returns url of Tap interface
     * @param SimpleXMLObject resource
     * @return string
     */
    public static function getTapAccessUrl($resource){
        $result = $resource->xpath('./capability[@standardID="'.IVO_VAMDC_TAP.'"]/interface/accessURL');
        if($result != false)
            return (string)$result[0][0];
        return null;        
    }
    
     /**
     * returns VAMDC Tap standard version
     * @param SimpleXMLObject resource
     * @return string
     */
    public static function getTapVersion($resource){
        $result = $resource->xpath('./capability[@standardID="'.IVO_VAMDC_TAP.'"]/versionOfStandards');
        if($result != false)
            return (string)$result[0][0];
        return null;        
    }    
   
    /**
     * returns Capability informations for VOSI capabilities 
     * @param SimpleXMLObject resource
     * @return SimpleXMLObject
     */
    public static function getCapabilities($resource){
        $result = $resource->xpath('./capability[@standardID="'.IVO_CAPABILITY.'"]');
        if($result != false)
            return $result[0];
        return null;        
    }
    
    /**
     * returns url of VOSI capabilities 
     * @param SimpleXMLObject resource
     * @return string
     */
    public static function getCapabilitiesAccessUrl($resource){
        $result = $resource->xpath('./capability[@standardID="'.IVO_CAPABILITY.'"]/interface/accessURL');
        if($result != false)
            return (string)$result[0][0];
        return null;        
    }    
    
    /**
     * returns Capability informations for VOSI availability 
     * @param SimpleXMLObject resource
     * @return SimpleXMLObject
     */
    public static function getAvailability($resource){
        $result = $resource->xpath('./capability[@standardID="'.IVO_AVAILABILITY.'"]');
        if($result != false)
            return $result[0];
        return null;        
    }
    
    /**
     * returns url of VOSI availability 
     * @param SimpleXMLObject resource
     * @return string
     */
    public static function getAvailabilityAccessUrl($resource){
        $result = $resource->xpath('./capability[@standardID="'.IVO_AVAILABILITY.'"]/interface/accessURL');
        if($result != false)
            return (string)$result[0][0];
        return null;        
    }
    
}
?>
