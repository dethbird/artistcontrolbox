<?php

class House_Controller_Api_Base extends Zend_Controller_Action  {
    //put your code here
    public function preDispatch() {
        parent::preDispatch();
        $this->api_key = $this->_getParam('api_key');
        $this->isAuthorizedByApiKey($this->_getParam('api_key'));

        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json");

        $this->view->site_url = "http://".$_SERVER['SERVER_NAME'];
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    private function isAuthorizedByApiKey($key){
        $artists = Artist::find_by_sql("SELECT * FROM `artists` WHERE `api_key` = '".$key."'");
        if(count($artists)!=1){
            $this->_response->setHttpResponseCode(403);
        } else {
            $this->artist = $artists[0];
            $this->setParam('artist_id', $this->artist->id);
        }

    }
}

?>
