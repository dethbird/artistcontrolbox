<?php

class TitlesController extends House_Controller_Default_Base
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $client = new House_Http_Client();
        $response = $client->httpGet($this->view->site_url."/api/titles");
        $this->view->data = json_decode($response);
        //House_Log::log(json_decode($response));
        
    }
    
    public function coverAction(){
        if($this->_getParam("id")==""){
            $this->_helper->_redirector->gotoUrl("/index/");
        } else {
            $client = new House_Http_Client();
            $response = $client->httpGet($this->view->site_url."/api/titles", array("id"=>$this->_getParam("id")));
            $this->view->data = json_decode($response);
            $this->view->title = $this->view->data[0];
        }
        
    }


}

