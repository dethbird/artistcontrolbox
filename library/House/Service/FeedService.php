<?php

/**
 * Description of House_Service_FeedService
 *
 * @author rishi
 */
class House_Service_FeedService  {

    private $artist = null;

    public function  __construct(Artist $artist)
    {
    	$this->artist = $artist;
    }
    
    public function find($id = null, $status_in = null, $load_feeds = true)
    {   
        //House_Log::log($this->_getAllParams());
        $feeds = Feed::find_by_sql("
            SELECT 
                `feeds`.*, 
                `artists`.`name` as artist_name
            FROM `feeds`
            LEFT JOIN `artists` ON `feeds`.`artist_id` = `artists`.`id`
            WHERE 1
            ".($id!="" ? " AND `feeds`.`id` = ".$id : "")."
            AND `feeds`.`artist_id` = ".$this->artist->id."
            ".(is_array($status_in)?" AND `feeds`.`status` IN ('".implode("', '", $status_in)."')":" AND `feeds`.`status` = 'enabled'")."
            GROUP BY `feeds`.`id`
            ORDER BY TRIM(UPPER(`feeds`.`name`)) ASC
        ");
        //Load Post Previews
        $response = array();
        foreach($feeds as $feed){
            $_feed = json_decode($feed->to_json());
            
            if($load_feeds){
                $items = array();
                try {
                    $items = Zend_Feed_Reader::import($feed->rss_url);
                } catch (Exception $e) {
                    //House_Log::log($e);  
                }
                if(count($items)>0){
                    foreach($items as $i=>$item){
                        $_feed->items[] = $this->rssEntryToJSON($item);
                        if($i==$_feed->item_limit){
                            break;
                        }
                    }
                }
            }
            $response[] = $_feed;
        }

        return $response;

    }

    public function rssEntryToJSON(Zend_Feed_Reader_Entry_Rss $item){
        $_item = new stdClass();
        $_item->title = $item->getTitle();
        $_item->url = $item->getLink();
        $_item->comment_link = $item->getCommentLink();
        $_item->date_modified = sprintf($item->getDateModified());
        $_item->content = $item->getContent();

        $authors = $item->getAuthors() ;
        $_authors = null;
        if(count($authors)>0){
            foreach($authors as $author){
                $_authors[] = $author['name'];
            }
            $_item->author = implode(', ', $_authors);
        }

        $categories = $item->getCategories() ;
        $_categories = null;
        if(count($categories)>0){
            foreach($categories as $category){
                $_categories[] = $category['term'];
            }
            $_item->categories = implode(', ', $_categories);
        }

        return $_item;
    }
}

?>
