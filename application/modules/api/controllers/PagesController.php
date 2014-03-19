<?php

class Api_PagesController extends House_Controller_Api_Base
{
    /**
     * 
     * @return array Array of Arists
     */
    public function indexAction()
    {
        if($this->getRequest()->isPost()){

            $response = new stdClass();
            if($this->_getParam('artist_id')==""){
                $response->errors[] = array("field"=>"overall", "message"=>"Authentication error (artist id)");
            }
            
            if($this->_getParam('id')=="new" && $this->_getParam('issue_id')==""){
                $response->errors[] = array("field"=>"overall", "message"=>"Authentication error (id)");
            }
            if(count($response->errors)>0){
                $this->getResponse()->setHttpResponseCode(302);
                $this->getResponse()->setBody(json_encode($response));
                $this->getResponse()->sendResponse();
                exit();
            }
            
            if($this->_getParam('id')!=="new"){
                $page = Page::find($this->_getParam('id'));
            } else {
                $page = new Page();
            }
            
            if(array_key_exists('description', $_POST)){
                $page->description = $this->_getParam('description');
            }
            if(array_key_exists('issue_id', $_POST)){
                $page->issue_id = $this->_getParam('issue_id');
            }
            if(array_key_exists('image_url', $_POST)){
                $page->image_url = $this->_getParam('image_url');
            }
            if(array_key_exists('page_number', $_POST)){
                $page->page_number = $this->_getParam('page_number');
            }
            $page->save();
            
            $this->_setParam('id', $page->id);

        }
        
        //return response
        $service = new House_Service_PageService($this->artist);
        $response = $service->find($this->_getParam('id'));
        echo json_encode($response);
      
      
    }


}

