<?php

class Api_ArtistsController extends House_Controller_Api_Base
{

    public function preDispatch() {
        parent::preDispatch();
        /* Initialize action controller here */
        if($this->_getParam("artist_id")!=""){
            $this->_setParam("id", $this->_getParam("artist_id"));
        }
    }

    /**
     * 
     * @return array Array of Arists
     */
    public function indexAction()
    {
        
        if($this->getRequest()->isPost()){
            $response = new stdClass();
            if(array_key_exists('name', $_POST)){
                if($this->_getParam('name')==""){
                    $response->errors[] = array("field"=>"name", "message"=>"Name cannot be null");
                }
            }
            if($this->_getParam('id')==""){
                $response->errors[] = array("field"=>"overall", "message"=>"Authentication error");
            }
            if(count($response->errors)>0){
                $this->getResponse()->setHttpResponseCode(302);
                $this->getResponse()->setBody(json_encode($response));
                $this->getResponse()->sendResponse();
                exit();
            }
            
            if($this->_getParam('id')!=="new"){
                $artist = Artist::find($this->_getParam('id'));
            } else {
                $artist = new Artist();
            }
            if(array_key_exists('name', $_POST)!==false){
                $artist->name = $this->_getParam('name');
            }
            if(array_key_exists('bio', $_POST)){
                $artist->bio = $this->_getParam('bio');
            }
            if(array_key_exists('url', $_POST)){
                $artist->url = $this->_getParam('url');
            }
            if(array_key_exists('bio_image_url', $_POST)){
                $artist->bio_image_url = $this->_getParam('bio_image_url');
            }
            $artist->save();
            $this->_setParam('id', $artist->id);
            
            //House_Log::log($title);
        }
        
        //return response
        $service = new House_Service_ArtistService($this->artist);
        $response = $service->find($this->_getParam('id'), $this->_getParam('get_api_key'));
        echo json_encode($response);
      
    }
    
    
    public function authAction(){
        $artists = Artist::find_by_sql(
            "SELECT 
            `artists`.`id`, `artists`.`name`, `artists`.`api_key`
             FROM `artists`
             WHERE 1
             AND TRIM(UPPER(`artists`.`email`)) = '".trim(strtoupper($this->_getParam('email')))."'
             AND `artists`.`password` = '".md5($this->_getParam('password'))."'
        ");
        if(count($artists)>0){
            echo $artists[0]->to_json();
        } else {
            return false;
        }
    }

}

