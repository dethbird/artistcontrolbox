<?php

/**
 * Description of House_Service_BackgroundService
 *
 * @author rishi
 */
class House_Service_BackgroundService  {

    private $artist = null;

    public function  __construct(Artist $artist)
    {
    	$this->artist = $artist;
    }
    
    public function find($id = null, $status_in = null)
    {   
        //House_Log::log($this->_getAllParams());
        $backgrounds = Background::find_by_sql("
            SELECT 
                `backgrounds`.*
            FROM `backgrounds`
            WHERE 1
            ".($id!="" ? " AND `backgrounds`.`id` = ".$id : "")."
            AND `backgrounds`.`artist_id` = ".$this->artist->id."
            ".(is_array($status_in)?" AND `backgrounds`.`status` IN ('".implode("', '", $status_in)."')":" AND `backgrounds`.`status` = 'enabled'")."
            ORDER BY `backgrounds`.`sort_order` ASC
        ");
        
        //House_Log::log($backgrounds);
        $response = array();
        foreach($backgrounds as $background){
            $_background = json_decode($background->to_json());
            $response[] = $_background;
        }

        return $response;

    }
}

?>
