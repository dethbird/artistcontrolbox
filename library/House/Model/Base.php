<?php

/**
 * Extends ActiveRecord  base model
 *
 * @author rsatsangi
 */
class House_Model_Base extends ActiveRecord\Model {
  
    static $before_save = array('set_timestamps'); # new OR updated records
    
    public function set_timestamps(){
        if($this->date_added==""){
            $this->date_added = date("F j, Y, g:i a"); 
        }
        $this->date_updated = date("F j, Y, g:i a");
        if($this->publish_json!=""){
        	$this->publish_date = date("F j, Y, g:i a");
        }
    }
}

?>
