<?php

/**
 * Description of House_Service_GalleryService
 *
 * @author rishi
 */
class House_Service_GalleryService  {

    private $artist = null;

    public function  __construct(Artist $artist)
    {
    	$this->artist = $artist;
    }
    
    public function find($id = null, $status_in = null)
    {   
        $galleries = Gallery::find_by_sql("
            SELECT 
                `galleries`.*, 
                `artists`.`name` as artist_name,
                COUNT(`contents`.`id`) as content_count
            FROM `galleries`
            LEFT JOIN `artists` ON `galleries`.`artist_id` = `artists`.`id`
            LEFT JOIN `contents` ON `contents`.`gallery_id` = `galleries`.`id`
            WHERE 1
            ".($id!="" ? " AND `galleries`.`id` = ".$id : "")."
            ".($this->artist->id!="" ? " AND `galleries`.`artist_id` = ".$this->artist->id : "")."
            ".(is_array($status_in)?" AND `galleries`.`status` IN ('".implode("', '", $status_in)."')":" AND `galleries`.`status` = 'enabled'")."
            GROUP BY `galleries`.`id`
            ORDER BY `galleries`.`sort_order` ASC
        ");
        
        $response = array();
        foreach($galleries as $gallery){
            $_gallery = json_decode($gallery->to_json());
            $contents = Gallery::find_by_sql("
                SELECT `contents`.*
                FROM `contents`
                WHERE `contents`.`gallery_id` = ".$gallery->id."
                ".(is_array($status_in)?" AND `contents`.`status` IN ('".implode("', '", $status_in)."')":" AND `contents`.`status` = 'enabled'")."
                ORDER BY `contents`.`sort_order`
            ");
            $_gallery->contents = array();
            foreach($contents as $content){
                //House_Log::log($content);    
                $_gallery->contents[] = json_decode($content->to_json());
            }
            $response[] = $_gallery;
        }

        return $response;

    }
}

?>
