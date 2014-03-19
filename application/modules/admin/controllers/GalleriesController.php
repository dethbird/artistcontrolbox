<?php

class Admin_GalleriesController extends House_Controller_Admin_Base
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
        $artist = House_Session::getArtist();
        $client = new House_Http_Client();
        $response = $client->httpGet($this->view->site_url."/api/galleries", array("api_key"=>$artist->api_key, "status_in"=>array("enabled", "disabled")));
        $this->view->data = json_decode($response);
        
        //House_Log::log(json_decode($response));
        //$this->view->gallery = $this->view->data[0]; 
    }
    
    
    public function coverAction(){
        if($this->_getParam("id")==""){
            $this->_helper->_redirector->gotoUrl("/index/");
        } else {
            $client = new House_Http_Client();
            $response = $client->httpGet($this->view->site_url."/api/galleries", array("id"=>$this->_getParam("id"), "status_in"=>array("enabled", "disabled")));
            $this->view->data = json_decode($response);
            $this->view->gallery = $this->view->data[0];
        }
        
    }
    
    public function detailsAction(){
        //echo "<pre>".print_r($_REQUEST,1)."</pre>";
        //echo "<pre>".print_r($_FILES,1)."</pre>";
        if($this->getRequest()->isPost()){
            $params = $this->_getAllParams();
            $artist = House_Session::getArtist();
            $params['api_key'] = $artist->api_key;
            $params['status_in'] = array("enabled", "disabled");
            $client = new House_Http_Client();
            $response = json_decode($client->httpPost($this->view->site_url."/api/galleries", $params));
            
            //if errors, return that json through flash messenger
            if(count($response->errors)>0){
               foreach($response->errors as $error){
                   $this->_messenger->addMessage(json_encode($error), "errors");
               }
               $this->_helper->_redirector->gotoUrl("/admin/galleries/details/id/".$this->_getParam('id'));
            } else {    
                $this->_messenger->addMessage("Gallery saved", "success");
                $gallery = $response[0];
                //no image uploaded
                if(is_null($_FILES['cover_image_url'])){
                    $this->_helper->_redirector->gotoUrl("/admin/galleries/details/id/".$gallery->id);
                } else {
                    //IMAGE UPLOAD
                    //move and rename the file
                    
                    $upload = new Zend_File_Transfer();
                    $file_name_full_path =  Zend_Registry::get('config')->get('image_upload_path')."/galleries/".$gallery->id.'.'.pathinfo($_FILES['cover_image_url']['name'],PATHINFO_EXTENSION);
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
                        $this->_helper->_redirector->gotoUrl("/admin/galleries/details/id/".$gallery->id);
                    } else {
                        $this->_messenger->addMessage("Image uploaded", "success");
                        //remove if exists
                        if($gallery->cover_image_url!=""){    
                            $pathinfo = pathinfo($gallery->cover_image_url);
                            //House_Log::log($pathinfo);
                            @unlink(Zend_Registry::get('config')->get('content_upload_dir')."/galleries/".$pathinfo['basename']);
                        }
                        
                        $upload->receive();
                        //save the new url
                        $name = $upload->getFileName('cover_image_url');
                        $pathinfo = pathinfo($name);
                        
                        $params = array();
                        $params['api_key'] = $artist->api_key;
                        $params['id'] = $gallery->id;
                        $params['cover_image_url'] = Zend_Registry::get('config')->get('image_server_url')."/galleries/".$gallery->id.".".$pathinfo['extension'];
                        $client = new House_Http_Client();
                        $response = json_decode($client->httpPost($this->view->site_url."/api/galleries", $params));
                        $this->_helper->_redirector->gotoUrl("/admin/galleries/details/id/".$gallery->id);
                        
                    }
                    
                }
                    
            }
        }
        
        $this->view->id = $this->_getParam('id');
        if(is_numeric($this->_getParam('id'))){
            $client = new House_Http_Client();
            $response = $client->httpGet($this->view->site_url."/api/galleries", array("id"=>$this->_getParam("id"), "status_in"=>array("enabled", "disabled")));
            $this->view->data = json_decode($response);
            $this->view->gallery = $this->view->data[0];
        }
    }
    


}

