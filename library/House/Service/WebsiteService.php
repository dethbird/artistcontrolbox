<?php

/**
 * Description of House_Service_WebsiteService
 *
 * @author rishi
 */
class House_Service_WebsiteService  {

    private $artist = null;

    public function  __construct(Artist $artist)
    {
    	$this->artist = $artist;
    }
    
    public function find($id = null)
    {

        $websites = Website::find_by_sql("
            SELECT * FROM `websites`
            WHERE 1
            AND `websites`.`artist_id` = ".$this->artist->id."
            ".($id!="" ? " AND `websites`.`id` = ".$id : "")."
        ");
      
        $response = array();
        foreach($websites as $website){
            $_website = json_decode($website->to_json());
            $response[] = $_website;
        }
        return $response;
    }
    
}

?>
