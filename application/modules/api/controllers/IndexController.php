<?php

class Api_IndexController extends House_Controller_Api_Base
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        //get full site info
        $website = Website::find_by_artist_id($this->artist->id);
        echo $website->publish_json;
        
    }


}

