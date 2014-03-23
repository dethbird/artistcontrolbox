<?php

class Admin_ContentsController extends House_Controller_Admin_Base
{

    public function init()
    {
        /* Initialize action controller here */
    }

    
    public function indexAction()
    {
        if($this->_getParam('gallery_id')==""){
            $this->_helper->_redirector->gotoUrl("/admin/galleries");
        }
        $artist = House_Session::getArtist();
        $client = new House_Http_Client();
        $response = $client->httpGet($this->view->site_url."/api/galleries", array("api_key"=>$artist->api_key, "id"=>$this->_getParam('gallery_id'), "status_in"=>array("enabled", "disabled")));
        $this->view->data = json_decode($response);
        $this->view->data = $this->view->data[0];
    }
    
    
    public function detailsAction(){

        $artist = House_Session::getArtist();
        
        if($this->getRequest()->isPost()){
            $params = $this->_getAllParams();
            
            $params['api_key'] = $artist->api_key;
            $params['gallery_id'] = $this->_getParam('gallery_id');
            $params['status_in'] = array("enabled", "disabled");
            //House_Log::log($params);
            $client = new House_Http_Client();
            $response = json_decode($client->httpPost($this->view->site_url."/api/contents", $params));

            //if errors, return that json through flash messenger
            if(count($response->errors)>0){
               foreach($response->errors as $error){
                   $this->_messenger->addMessage(json_encode($error), "errors");
               }
               $this->_helper->_redirector->gotoUrl("/admin/contents/details/id/".$this->_getParam('id')."/gallery_id/".$this->_getParam('gallery_id'));
            } else {    
                $this->_messenger->addMessage("Content saved", "success");
                $content = $response[0];
                //no image uploaded
                if($_FILES['image_url']['name']!="" && $this->_getParam('external_url')!=""){
                    $this->_helper->_redirector->gotoUrl("/admin/contents/details/id/".$content->id);
                } else if ($_FILES['image_url']['name']!=""){
                    //IMAGE UPLOAD
                    //move and rename the file
                    $upload = new Zend_File_Transfer();
                    $file_name_full_path =  Zend_Registry::get('config')->get('image_upload_path')."/contents/".$content->id.'.'.pathinfo($_FILES['image_url']['name'],PATHINFO_EXTENSION);
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
                        $this->_helper->_redirector->gotoUrl("/admin/contents/details/id/".$content->id);
                    } else {
                        
                        //remove if exists
                        if($content->image_url!=""){
                            $pathinfo = pathinfo($content->image_url);
                            @unlink(Zend_Registry::get('config')->get('content_upload_dir')."/contents/".$pathinfo['basename']);
                        }
                        $this->_messenger->addMessage("Content uploaded", "success");
                        $upload->receive();
                        //save the new url
                        $name = $upload->getFileName('image_url');
                        $pathinfo = pathinfo($name);
                        
                        $params = array();
                        $params['type'] = "image";
                        $params['api_key'] = $artist->api_key;
                        $params['id'] = $content->id;
                        $params['gallery_id'] = $content->gallery_id;
                        $params['image_url'] = Zend_Registry::get('config')->get('image_server_url')."/contents/".$content->id.".".$pathinfo['extension'];
                        $client = new House_Http_Client();
                        $response = json_decode($client->httpPost($this->view->site_url."/api/contents", $params));                        
                        $this->_helper->_redirector->gotoUrl("/admin/contents/details/id/".$content->id);
                    }
                    
                } else if ($this->_getParam('external_url')!=""){
                    //download the image and move to contents folder
                    if($content->image_url!=""){
                        $pathinfo = pathinfo($content->image_url);
                        @unlink(Zend_Registry::get('config')->get('content_upload_dir')."/contents/".$pathinfo['basename']);
                    }
                    
                    $client = new House_Http_Client();
                    $file_name_full_path =  Zend_Registry::get('config')->get('image_upload_path')."/contents/".$content->id.'.'.pathinfo($this->_getParam('external_url'),PATHINFO_EXTENSION);
                    file_put_contents($file_name_full_path, $client->httpGet($this->_getParam('external_url')));
                    $params = array();
                    $params['type'] = "image";
                    $params['api_key'] = $artist->api_key;
                    $params['id'] = $content->id;
                    $params['gallery_id'] = $content->gallery_id;
                    $params['image_url'] = Zend_Registry::get('config')->get('image_server_url')."/contents/".$content->id.".".pathinfo($this->_getParam('external_url'),PATHINFO_EXTENSION);
                    $client = new House_Http_Client();
                    $response = json_decode($client->httpPost($this->view->site_url."/api/contents", $params));     
                    $this->_helper->_redirector->gotoUrl("/admin/contents/details/id/".$content->id);
                    
                } else if ($this->_getParam('youtube_link')!=""){
                    //download the image and move to contents folder
                    if($content->image_url!=""){
                        $pathinfo = pathinfo($content->image_url);
                        @unlink(Zend_Registry::get('config')->get('content_upload_dir')."/contents/".$pathinfo['basename']);
                    }
                    $youtube_thumbnail = House_Converter::youtubeLinkToThumbnail($this->_getParam('youtube_link'));
                    $client = new House_Http_Client();
                    $file_name_full_path =  Zend_Registry::get('config')->get('image_upload_path')."/contents/".$content->id.'.'.pathinfo($youtube_thumbnail,PATHINFO_EXTENSION);
                    file_put_contents($file_name_full_path, $client->httpGet($youtube_thumbnail));
                    $params = array();
                    $params['type'] = "youtube";
                    $params['api_key'] = $artist->api_key;
                    $params['id'] = $content->id;
                    $params['gallery_id'] = $content->gallery_id;
                    $params['image_url'] = Zend_Registry::get('config')->get('image_server_url')."/contents/".$content->id.".".pathinfo($youtube_thumbnail,PATHINFO_EXTENSION);
                    $client = new House_Http_Client();
                    $response = json_decode($client->httpPost($this->view->site_url."/api/contents", $params));     
                    $this->_helper->_redirector->gotoUrl("/admin/contents/details/id/".$content->id);
                    
                }
                    
            }
        }
        
        $client = new House_Http_Client();
        $response = $client->httpGet($this->view->site_url."/api/contents", array("api_key"=>$artist->api_key, "id"=>$this->_getParam("id"), "status_in"=>array("enabled", "disabled")));
        
        //House_Log::log(json_decode($response));
        $this->view->data = json_decode($response);
        $this->view->content = $this->view->data[0];
        
        if($this->_getParam('id')=="new"){
            if(!is_numeric($this->_getParam('gallery_id'))){
                $this->_helper->_redirector->gotoUrl("/admin/galleries/");
            } else {
                //fetch the gallery name
                $client = new House_Http_Client();
                $response = $client->httpGet($this->view->site_url."/api/galleries", array("api_key"=>$artist->api_key, "id"=>$this->_getParam("gallery_id"), "status_in"=>array("enabled", "disabled")));
                //var_dump($response);
                //exit();
                $gallery = json_decode($response);
                $gallery = $gallery[0];
                $this->view->content->id = "new";
                $this->view->content->gallery_id = $gallery->id;
                $this->view->content->gallery_name = $gallery->name;
                $this->view->content->gallery_type = $gallery->type;
                $this->view->content->sort_order = count($gallery->contents) + 1;
            }
        }
        
    }
    
    public function youtubePreviewAction(){
        $this->view->thumbnail = House_Converter::youtubeLinkToThumbnail($this->_getParam('youtube_link'));
        $this->view->embed = House_Converter::youtubeLinkToEmbedCode($this->_getParam('youtube_link'), 640, 480);
        echo $this->view->render("contents/youtube-preview.phtml");
    }
    


}

