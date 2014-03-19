<?php

/**
 * Description of House_Service_IssueService
 *
 * @author rishi
 */
class House_Service_IssueService  {

    private $artist = null;

    public function  __construct(Artist $artist)
    {
    	$this->artist = $artist;
    }
    
    public function find($id = null, $status_in = null, $title_id = null)
    {

        $issues = Issue::find_by_sql("
                SELECT `issues`.*, 
                COUNT(`pages`.`id`) as page_count,
                `page_one`.`image_url` as page_one_image_url,
                `titles`.`name` as `title_name`,
                `titles`.`artist_id`,
                `artists`.`name` as `artist_name`
                FROM `issues`
                LEFT JOIN `titles` ON `issues`.`title_id` = `titles`.`id`
                LEFT JOIN `artists` ON `titles`.`artist_id` = `artists`.`id`
                LEFT JOIN `pages` ON `pages`.`issue_id` = `issues`.`id`
                LEFT JOIN `pages` as page_one ON `page_one`.`issue_id` = `issues`.`id` AND `page_one`.`page_number` = 1
                WHERE 1
                ".( $id!="" ? " AND `issues`.`id` = ".$id : "")."
                ".( $title_id!="" ? " AND `issues`.`title_id` = ".$title_id : "")."
                ".( $this->artist->id!="" ? " AND `titles`.`artist_id` = ".$this->artist->id : "")."
                ".(is_array($status_in)?" AND `issues`.`status` IN ('".implode("', '", $status_in)."')":" AND `issues`.`status` = 'enabled'")."
                GROUP BY `issues`.`id`
                ORDER BY `issues`.`name`
            ");
        
        $response = array();
        foreach($issues as $issue){
            $_issue = json_decode($issue->to_json());
            $pages = Issue::find_by_sql("
                SELECT `pages`.*
                FROM `pages`
                WHERE `pages`.`issue_id` = ".$issue->id."
                ORDER BY `pages`.`page_number` ASC
            ");
            $_issue->pages = array();
            foreach($pages as $page){
                $_issue->pages[] = json_decode($page->to_json());
            }
            $response[] = $_issue;
        }

        return $response;
    }
    
}

?>
