<?php

/**
 * Description of House_Service_Page
 *
 * @author rishi
 */
class House_Service_PageService  {

    private $artist = null;

    public function  __construct(Artist $artist)
    {
    	$this->artist = $artist;
    }
    
    public function find($id = null)
    {


        $pages = Page::find_by_sql("
            SELECT 
                `pages`.*, 
                `issues`.`name` as issue_name,
                `issues`.`issue_number`,
                `titles`.`name` as title_name,
                `titles`.`id` as title_id,
                COUNT(p.id) as issue_page_count
            FROM `pages`
            LEFT JOIN `issues` ON `issues`.`id` = `pages`.`issue_id`
            LEFT JOIN `titles` ON `titles`.`id` = `issues`.`title_id`
            LEFT JOIN `pages` as p ON `pages`.`issue_id` = p.`issue_id`
            WHERE 1
            ".($this->artist->id!="" ? " AND `titles`.`artist_id` = ".$this->artist->id : "")."
            ".($id!="" ? " AND `pages`.`id` = ".$id : "")."
            GROUP BY `pages`.`id`
            ORDER BY TRIM(UPPER(`pages`.`page_number`)) ASC
        ");
        
        
        $response = array();
        
        foreach($pages as $page){
            $_page = json_decode($page->to_json());

            //get prev and next
            $prevPages = Page::find_by_sql("
                SELECT `pages`.*
                FROM `pages` 
                WHERE 1
                AND `pages`.`page_number` < ".$_page->page_number."
                AND `pages`.`issue_id` = ".$_page->issue_id."
                ORDER BY `pages`.`page_number` DESC
                LIMIT 1
            ");
            
            if(count($prevPages)>0){
                $_page->prev = json_decode($prevPages[0]->to_json());
            }
            
            $nextPages = Page::find_by_sql("
                SELECT `pages`.*
                FROM `pages` 
                WHERE 1
                AND `pages`.`page_number` > ".$_page->page_number."
                AND `pages`.`issue_id` = ".$_page->issue_id."
                ORDER BY `pages`.`page_number` ASC
                LIMIT 1
            ");
            if(count($nextPages)>0){
                $_page->next = json_decode($nextPages[0]->to_json());
            }
            
            $response[] = $_page;
        }
        return $response;
    }
    
}

?>
