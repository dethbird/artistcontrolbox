<?php

class Admin_IssuesController extends House_Controller_Admin_Base
{

    public function init()
    {
        //print_r($this->_getAllParams());
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        if($this->_getParam('title_id')==""){
            $this->_helper->_redirector->gotoUrl("/admin/titles");
        }
        $artist = House_Session::getArtist();
        $client = new House_Http_Client();
        $response = $client->httpGet($this->view->site_url."/api/titles", array("api_key"=>$artist->api_key, "id"=>$this->_getParam('title_id'), "status_in"=>array("enabled", "disabled")));
        $this->view->data = json_decode($response);
        $this->view->data = $this->view->data[0];
    }
    
    public function detailsAction(){
        
        if($this->getRequest()->isPost()){
            $params = $this->_getAllParams();
            $artist = House_Session::getArtist();
            $params['api_key'] = $artist->api_key;
            $params['title_id'] = $this->_getParam('title_id');
            $params['status_in'] = array("enabled", "disabled");
            $client = new House_Http_Client();
            $response = json_decode($client->httpPost($this->view->site_url."/api/issues", $params));
            if(count($response->errors)>0){
               foreach($response->errors as $error){
                   $this->_messenger->addMessage(json_encode($error), "errors");
               }
               $this->_helper->_redirector->gotoUrl("/admin/issues/details/id/".$this->_getParam('id'));
            } else {
                $issue = $response[0];
                $this->_messenger->addMessage("Issue saved", "success");
                $this->_helper->_redirector->gotoUrl("/admin/issues/details/id/".$issue->id);
            }
        }
        $client = new House_Http_Client();
        $response = $client->httpGet($this->view->site_url."/api/issues", array("api_key"=>$artist->api_key, "id"=>$this->_getParam("id"), "status_in"=>array("enabled", "disabled")));
        //House_Log::log(json_decode($response));
        $this->view->data = json_decode($response);
        $this->view->issue = $this->view->data[0];
        
        if($this->_getParam('id')=="new"){
            if(!is_numeric($this->_getParam('title_id'))){
                $this->_helper->_redirector->gotoUrl("/admin/titles/");
            } else {
                //fetch the title name
                $client = new House_Http_Client();
                $response = $client->httpGet($this->view->site_url."/api/titles", array("id"=>$this->_getParam("title_id"), "status_in"=>array("enabled", "disabled")));
                $title = json_decode($response);
                $title = $title[0];
                $this->view->issue->id = "new";
                $this->view->issue->title_id = $title->id;
                $this->view->issue->title_name = $title->name;
                $this->view->issue->issue_number = count($title->issues) + 1;
            }
        }
        
        //House_Log::log($this->view->issue);
        
        
    }


}

