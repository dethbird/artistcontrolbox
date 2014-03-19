<?php

class IssuesController extends House_Controller_Default_Base
{

    public function init()
    {
        //print_r($this->_getAllParams());
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $this->_helper->_redirector->gotoUrl("/index/");
    }
    
    public function viewAction(){
        if($this->_getParam("id")==""){
            $this->_helper->_redirector->gotoUrl("/index/");
        } else {
            $client = new House_Http_Client();
            $response = $client->httpGet($this->view->site_url."/api/issues", array("id"=>$this->_getParam("id")));
            $this->view->data = json_decode($response);
            $this->view->issue = $this->view->data[0];
        }
        
    }


}

