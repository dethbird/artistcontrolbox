<?php

class Api_IssuesController extends House_Controller_Api_Base
{

    public function init()
    {
        /* Initialize action controller here */
    }

    /**
     * 
     * @return array Array of Arists
     */
    public function indexAction()
    {
        if($this->getRequest()->isPost()){
            //House_Log::log($this->_getAllParams());
            $response = new stdClass();
            if(array_key_exists('name', $_POST)){
                if($this->_getParam('name')==""){
                    $response->errors[] = array("field"=>"name", "message"=>"Name cannot be null");
                }
            }
            if(array_key_exists('issue_number', $_POST)){
                if($this->_getParam('issue_number')==""){
                    $response->errors[] = array("field"=>"issue_number", "message"=>"Issue number must be a number");
                }
            }
            if($this->_getParam('artist_id')==""){
                /** 
                 * @todo check to see that the Title belongs to this artist
                 */
                $response->errors[] = array("field"=>"overall", "message"=>"Authentication error");
            }
            if(array_key_exists('title_id', $_POST)){
                if($this->_getParam('title_id')==""){
                    /** 
                     * @todo check to see that the Title belongs to this artist
                     */
                    $response->errors[] = array("field"=>"overall", "message"=>"Authentication error [title_id]");
                }
            }
            if(count($response->errors)>0){
                $this->getResponse()->setHttpResponseCode(302);
                $this->getResponse()->setBody(json_encode($response));
                $this->getResponse()->sendResponse();
                exit();
            }
            
            if($this->_getParam('id')!=="new"){
                $issue = Issue::find($this->_getParam('id'));
            } else {
                $issue = new Issue();
            }
            if(array_key_exists('title_id', $_POST)!==false){
                $issue->title_id = $this->_getParam('title_id');
            }
            if(array_key_exists('name', $_POST)!==false){
                $issue->name = $this->_getParam('name');
            }
            if(array_key_exists('issue_number', $_POST)){
                $issue->issue_number = $this->_getParam('issue_number');
            }
            if(array_key_exists('status', $_POST)){
                $issue->status = $this->_getParam('status');
            }
            $issue->save();
            $this->_setParam('id', $issue->id);
        }

        $service = new House_Service_IssueService($this->artist);
        $response = $service->find($this->_getParam('id'), $this->_getParam('status_in'));
        echo json_encode($response);
      
    }
    
    public function orderPagesAction(){
        if(count($this->_getParam('sorted'))>0){
            foreach($this->_getParam('sorted') as $i=>$id){
                $page = Page::find($id);
                if($page->id){
                    $page->page_number = $i+1;
                    $page->save();
                }
            }
        }
        $this->indexAction();
        
    }


}

