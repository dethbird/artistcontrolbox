<?php

/**
 * Description of House_Service_ArtistService
 *
 * @author rishi
 */
class House_Service_ArtistService  {

    private $artist = null;

    public function  __construct(Artist $artist)
    {
    	$this->artist = $artist;
    }
    

    public function find($id = null, $get_api_key = null)
    {   
        $artists = Artist::find_by_sql("
            SELECT * FROM `artists`
            WHERE 1
            ".($id!="" ? " AND `artists`.`id` = ".$id : "")."
            ".($this->artist->api_key!="" ? " AND `artists`.`api_key` = '".$this->artist->api_key."'" : "")."
            ORDER BY TRIM(UPPER(`artists`.`name`)) ASC
        ");
        
        //House_Log::log($artists);
      
        $response = array();
        foreach($artists as $artist){
            $_artist = json_decode($artist->to_json());
            unset($_artist->password);
            if($get_api_key!="Y"){
                unset($_artist->api_key);
            }
            
            $response[] = $_artist;
        }

        return $response;

    }
}

?>
