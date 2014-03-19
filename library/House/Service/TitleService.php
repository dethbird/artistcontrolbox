<?php

/**
 * Description of House_Service_TitleService
 *
 * @author rishi
 */
class House_Service_TitleService  {

    private $artist = null;

    public function  __construct(Artist $artist)
    {
    	$this->artist = $artist;
    }
    
    public function find($id = null, $status_in = null)
    {


        $titles = Title::find_by_sql("
            SELECT 
                `titles`.*, 
                `artists`.`name` as artist_name,
                COUNT(`issues`.`id`) as issue_count
            FROM `titles`
            LEFT JOIN `artists` ON `titles`.`artist_id` = `artists`.`id`
            LEFT JOIN `issues` ON `issues`.`title_id` = `titles`.`id`
            WHERE 1
            ".($id!="" ? " AND `titles`.`id` = ".$id : "")."
            ".($this->artist->id!="" ? " AND `titles`.`artist_id` = ".$this->artist->id : "")."
            ".(is_array($status_in)?" AND `titles`.`status` IN ('".implode("', '", $status_in)."')":" AND `titles`.`status` = 'enabled'")."
            GROUP BY `titles`.`id`
            ORDER BY TRIM(UPPER(`titles`.`name`)) ASC
        ");
        
        $response = array();
        foreach($titles as $title){
            $_title = json_decode($title->to_json());

            $service = new House_Service_IssueService($this->artist);
            $issues = $service->find(null, $status_in, $_title->id);
            

            $_title->issues = array();
            foreach($issues as $issue){
                //House_Log::log($issue);    
                $_title->issues[] = $issue;
            }
            $response[] = $_title;
        }
        return $response;
    }
    
}

?>
