<?php

class SearchResponseParser{
    
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
    
    public static function getResources($xml){
        $voresource = self::getVOResource($xml);
        if($voresource != null)
            return $voresource->children('ri', true);
    }
    

}   

class ResourceParser{
    public static function getCreated($resource){
        return $resource->attributes()->created;
    }
    
    public static function getStatus($resource){
        return $resource->attributes()->status;
    }
    
    public static function getUpdated($resource){
        return $resource->attributes()->updated;
    }

    public static function getCuration($resource){
        $result = $resource->xpath('./curation');
        return $result[0];
    }    
    
    public static function getTitle($resource){
        $result = $resource->xpath('./title');
        return $result[0];
    }      
    
    public static function getContent($resource){
        $result = $resource->xpath('./content');
        return $result[0];      
    }
    
    
    public static function getTapCapability($resource){
        $result = $resource->xpath('./capability[@standardID="ivo://vamdc/std/VAMDC-TAP"]');
        if($result != false)
            return $result;
        return null;        
    }
    
    public static function getTapAccessUrl($resource){
        $result = $resource->xpath('./capability[@standardID="ivo://vamdc/std/VAMDC-TAP"]/interface/accessURL');
        if($result != false)
            return $result[0][0];
        return null;        
    }
    
    public static function getCapabilities($resource){
        $result = $resource->xpath('./capability[@standardID="ivo://ivoa.net/std/VOSI#capabilities"]');
        if($result != false)
            return $result;
        return null;        
    }
    
    public static function getCapabilitiesAccessUrl($resource){
        $result = $resource->xpath('./capability[@standardID="ivo://ivoa.net/std/VOSI#capabilities"]/interface/accessURL');
        if($result != false)
            return $result[0][0];
        return null;        
    }
    
}
?>
