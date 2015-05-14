<?php

/**
 * Description of House_Service_ContentService
 *
 * @author rishi
 */
class House_Service_ContentService  {


    private $site_url = null;

    public function  __construct($site_url)
    {
        $this->site_url = $site_url;

    }

    // get all for artist
    public function all(Artist $artist)
    {
        $contents = Content::find_by_sql("
            SELECT
                `contents`.*,
                `galleries`.`name` as gallery_name,
                `galleries`.`type` as gallery_type,
                `galleries`.`artist_id`
            FROM `contents`
            LEFT JOIN `galleries` ON `galleries`.`id` = `contents`.`gallery_id`
            WHERE 1
            AND `galleries`.`artist_id` = ".$artist->id."
        ");

        $response = array();
        foreach($contents as $content){
            $_content = json_decode($content->to_json());
            $response[] = $_content;
        }
        return $response;
    }

    public function find($id = null, $status_in = null)
    {
        $contents = Content::find_by_sql("
            SELECT
                `contents`.*,
                `galleries`.`name` as gallery_name,
                `galleries`.`type` as gallery_type
            FROM `contents`
            LEFT JOIN `galleries` ON `galleries`.`id` = `contents`.`gallery_id`
            WHERE 1
            ".($id!="" ? " AND `contents`.`id` = ".$id : "")."
            ".(is_array($status_in)?" AND `contents`.`status` IN ('".implode("', '", $status_in)."')":" AND `contents`.`status` = 'enabled'")."
            ORDER BY `contents`.`sort_order` ASC
        ");


        $response = array();
        foreach($contents as $content){
            $_content = json_decode($content->to_json());

            //set the buy form
            if($_content->purchase_cost && $_content->purchase_description && $_content->purchase_download_url)
            {
                $form = ButtonGenerator::GenerateForm(
                        Zend_Registry::get('config')->get('amazon_key'),
                        Zend_Registry::get('config')->get('amazon_secret'),
                        "USD ".$_content->purchase_cost,
                        htmlentities($_content->purchase_description, ENT_QUOTES, "UTF-8"),
                        "content-".$_content->id,
                        1,
                        $this->site_url."/default/payments/amazoncomplete",
                        $this->site_url,
                        1,
                        $this->site_url."/api/payments/",
                        false,
                        "HmacSHA256",
                        APPLICATION_ENV=="production"?"prod":"dev"
                 );
                $_content->amazon_buy_link = $form;
            }

            //get prev and next
            $prevContents = Content::find_by_sql("
                SELECT `contents`.*
                FROM `contents`
                WHERE 1
                AND `contents`.`sort_order` < ".$_content->sort_order."
                AND `contents`.`gallery_id` = ".$_content->gallery_id."
                ".(is_array($status_in)?" AND `contents`.`status` IN ('".implode("', '", $status_in)."')":" AND `contents`.`status` = 'enabled'")."
                ORDER BY `contents`.`sort_order` DESC
                LIMIT 1
            ");
            if(count($prevContents)>0){
                $_content->prev = json_decode($prevContents[0]->to_json());
            }

            $nextContents = Content::find_by_sql("
                SELECT `contents`.*
                FROM `contents`
                WHERE 1
                AND `contents`.`sort_order` > ".$_content->sort_order."
                AND `contents`.`gallery_id` = ".$_content->gallery_id."
                ".(is_array($status_in)?" AND `contents`.`status` IN ('".implode("', '", $status_in)."')":" AND `contents`.`status` = 'enabled'")."
                ORDER BY `contents`.`sort_order` ASC
                LIMIT 1
            ");
            if(count($nextContents)>0){
                $_content->next = json_decode($nextContents[0]->to_json());
            }

            $response[] = $_content;
        }

        return $response;

    }
}

?>
