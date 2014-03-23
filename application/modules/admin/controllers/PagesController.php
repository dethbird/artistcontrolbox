<?php

class Admin_PagesController extends House_Controller_Admin_Base
{

    public function init()
    {
        /* Initialize action controller here */
    }

    
    public function indexAction()
    {
        if($this->_getParam('issue_id')==""){
            $this->_helper->_redirector->gotoUrl("/admin/titles");
        }
        $artist = House_Session::getArtist();
        $client = new House_Http_Client();
        $response = $client->httpGet($this->view->site_url."/api/issues", array("api_key"=>$artist->api_key, "id"=>$this->_getParam('issue_id'), "status_in"=>array("enabled", "disabled")));
        $this->view->data = json_decode($response);$this->view->data = $this->view->data[0];
        //House_Log::log($this->view->data);
    }
    
    
    public function detailsAction(){
        
        $artist = House_Session::getArtist();
        
        if($this->getRequest()->isPost()){
            $params = $this->_getAllParams();
            $params['api_key'] = $artist->api_key;
            $params['issue_id'] = $this->_getParam('issue_id');
            //House_Log::log($params);
            $client = new House_Http_Client();
            $response = json_decode($client->httpPost($this->view->site_url."/api/pages", $params));
            
            //if errors, return that json through flash messenger
            if(count($response->errors)>0){
               foreach($response->errors as $error){
                   $this->_messenger->addMessage(json_encode($error), "errors");
               }
               $this->_helper->_redirector->gotoUrl("/admin/pages/details/id/".$this->_getParam('id'));
            } else {    
                $this->_messenger->addMessage("Page saved", "success");
                $page = $response[0];
                //no image uploaded
                if(is_null($_FILES['image_url'])){
                    $this->_helper->_redirector->gotoUrl("/admin/pages/details/id/".$page->id);
                } else {
                    //IMAGE UPLOAD
                    //move and rename the file
                    $upload = new Zend_File_Transfer();
                    $file_name_full_path =  Zend_Registry::get('config')->get('image_upload_path')."/pages/".$page->id.'.'.pathinfo($_FILES['image_url']['name'],PATHINFO_EXTENSION);
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
                        $this->_helper->_redirector->gotoUrl("/admin/pages/details/id/".$page->id);
                    } else {
                        
                        //remove if exists
                        if($page->image_url!=""){
                            $pathinfo = pathinfo($page->image_url);
                            @unlink(Zend_Registry::get('config')->get('content_upload_dir')."/pages/".$pathinfo['basename']);
                        }
                        $this->_messenger->addMessage("Page uploaded", "success");
                        $upload->receive();
                        //save the new url
                        $name = $upload->getFileName('image_url');
                        $pathinfo = pathinfo($name);
                        
                        $params = array();
                        $params['api_key'] = $artist->api_key;
                        $params['id'] = $page->id;
                        $params['issue_id'] = $page->issue_id;
                        $params['image_url'] = Zend_Registry::get('config')->get('image_server_url')."/pages/".$page->id.".".$pathinfo['extension'];
                        $client = new House_Http_Client();
                        $response = json_decode($client->httpPost($this->view->site_url."/api/pages", $params));                        
                        $this->_helper->_redirector->gotoUrl("/admin/pages/details/id/".$page->id);
                    }
                    
                }
                    
            }
        }
        
        $client = new House_Http_Client();
        $response = $client->httpGet($this->view->site_url."/api/pages", array("api_key"=>$artist->api_key, "id"=>$this->_getParam("id")));
        //House_Log::log(json_decode($response));
        $this->view->data = json_decode($response);
        $this->view->page = $this->view->data[0];
        
        if($this->_getParam('id')=="new"){
            if(!is_numeric($this->_getParam('issue_id'))){
                $this->_helper->_redirector->gotoUrl("/admin/titles/");
            } else {
                //fetch the title name
                $client = new House_Http_Client();
                $response = $client->httpGet($this->view->site_url."/api/issues", array("api_key"=>$artist->api_key, "id"=>$this->_getParam("issue_id")));
                $issue = json_decode($response);
                $issue = $issue[0];
                $this->view->page->id = "new";
                $this->view->page->title_id = $issue->id;
                $this->view->page->title_name = $issue->title_name;
                $this->view->page->issue_id = $issue->id;
                $this->view->page->issue_name = $issue->name;
                $this->view->page->page_number = count($issue->pages) + 1;
            }
        }
        
    }
    


}

