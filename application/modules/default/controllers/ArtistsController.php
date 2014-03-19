<?php

class ArtistsController extends House_Controller_Default_Base
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
        //House_Log::log(House_Session::get());
        $client = new House_Http_Client();
        $response = $client->httpGet($this->view->site_url."/api/artists");
        $this->view->data = json_decode($response);
        //House_Log::log(json_decode($response));
    }
    
    public function bioAction(){
        if($this->_getParam("id")==""){
            $this->_helper->_redirector->gotoUrl("/index/");
        } else {
            $client = new House_Http_Client();
            $response = $client->httpGet($this->view->site_url."/api/artists", array("id"=>$this->_getParam("id")));
            $this->view->data = json_decode($response);
            $this->view->artist = $this->view->data[0];
        }
        
    }


}

