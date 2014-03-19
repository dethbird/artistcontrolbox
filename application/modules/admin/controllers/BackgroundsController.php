<?php

class Admin_BackgroundsController extends House_Controller_Admin_Base
{

    public function init()
    {
        /* Initialize action controller here */
    }

    
    public function indexAction()
    {
        $artist = House_Session::getArtist();
        $client = new House_Http_Client();
        $response = $client->httpGet($this->view->site_url."/api/backgrounds", array("api_key"=>$artist->api_key, "status_in"=>array("enabled", "disabled")));
        House_Log::log($response);
        $this->view->data = json_decode($response);
    }
    
    
    public function detailsAction(){
        
        if($this->getRequest()->isPost()){
            $params = $this->_getAllParams();
            $artist = House_Session::getArtist();
            $params['api_key'] = $artist->api_key;
            $params['status_in'] = array("enabled", "disabled");
            //House_Log::log($params);
            $client = new House_Http_Client();
            $response = json_decode($client->httpPost($this->view->site_url."/api/backgrounds", $params));
            //if errors, return that json through flash messenger
            if(count($response->errors)>0){
               foreach($response->errors as $error){
                   $this->_messenger->addMessage(json_encode($error), "errors");
               }
               $this->_helper->_redirector->gotoUrl("/admin/backgrounds/details/id/".$this->_getParam('id'));
            } else {    
                $this->_messenger->addMessage("Background saved", "success");
                $background = $response[0];
                //no image uploaded
                if($_FILES['image_url']['name']!="" && $this->_getParam('external_url')!=""){
                    $this->_helper->_redirector->gotoUrl("/admin/backgrounds/details/id/".$background->id);
                } else if ($_FILES['image_url']['name']!=""){
                    //IMAGE UPLOAD
                    //move and rename the file
                    $upload = new Zend_File_Transfer();
                    $file_name_full_path =  Zend_Registry::get('config')->get('image_upload_path')."/backgrounds/".$background->id.'.'.pathinfo($_FILES['image_url']['name'],PATHINFO_EXTENSION);
                    $upload->addFilter('Rename', array('target' => $file_name_full_path,
                         'overwrite' => true) );
                    $upload->addValidator('MimeType', false, array('image/jpg', 'image/jpeg', 'image/png'));
                    $upload->addValidator('Size', false, array('min' => 20, 'max' => 2097152));
                    //check validity
                    if(!$upload->isValid()){
                        //House_Log::log($upload->getMessages());
                        foreach($upload->getMessages() as $key=>$value){
                            if($key!="fileUploadErrorNoFile"){
                                $this->_messenger->addMessage(json_encode(array("field"=>$key, "message"=>$value)), "errors");
                            }
                        }
                        $this->_helper->_redirector->gotoUrl("/admin/backgrounds/details/id/".$background->id);
                    } else {
                        
                        //remove if exists
                        if($background->image_url!=""){
                            $pathinfo = pathinfo($background->image_url);
                            @unlink(Zend_Registry::get('config')->get('image_upload_path')."/backgrounds/".$pathinfo['basename']);
                        }
                        $this->_messenger->addMessage("Background uploaded", "success");
                        $upload->receive();
                        //save the new url
                        $name = $upload->getFileName('image_url');
                        $pathinfo = pathinfo($name);
                        
                        $params = array();
                        $params['api_key'] = $artist->api_key;
                        $params['id'] = $background->id;
                        $params['image_url'] = Zend_Registry::get('config')->get('image_server_url')."/backgrounds/".$background->id.".".$pathinfo['extension'];
                        $client = new House_Http_Client();
                        $response = json_decode($client->httpPost($this->view->site_url."/api/backgrounds", $params));                        
                        $this->_helper->_redirector->gotoUrl("/admin/backgrounds/details/id/".$background->id);
                    }
                    
                }
                    
            }
        }
        
        $client = new House_Http_Client();
        $response = $client->httpGet($this->view->site_url."/api/backgrounds", array("id"=>$this->_getParam("id"), "status_in"=>array("enabled", "disabled")));
        //House_Log::log(json_decode($response));
        $this->view->data = json_decode($response);
        $this->view->background = $this->view->data[0];
        $this->view->id = $this->_getParam('id');
        
    }
    


}

