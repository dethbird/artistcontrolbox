<?php

class House_Controller_Admin_Base extends Zend_Controller_Action  {
    //public your code here
    public $_messenger;
    
    public function preDispatch() {
        
        parent::preDispatch();
        
        
        $this->_messenger = $this->_helper->getHelper('FlashMessenger');
        $this->view->errors = $this->_messenger->getMessages("errors");
        $this->view->success = $this->_messenger->getMessages("success");
        
        $this->view->cache_buster = time();
        
        if($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()){
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
        }
        
        $this->view->site_url = $this->view->serverUrl();
        $this->view->controller = $this->getRequest()->getControllerName();
        $this->view->action = $this->getRequest()->getActionName();
        
        if(!House_Session::isLoggedIn() && $this->getRequest()->getControllerName()!=="login" && $this->getRequest()->isPost()==false){
            $this->_helper->_redirector->gotoUrl("/admin/login");
        } else {
            $this->view->artist = House_Session::getArtist();
            $this->view->is_internal_admin = House_Session::isInternalAdmin();
            if($this->getRequest()->getActionName()!=="auth" && $this->getRequest()->getControllerName()!='login' && $this->getRequest()->getActionName()!='logout'){
                setcookie('last_url', $this->view->serverUrl().$this->view->url());
            }
        }
        
    }
    
    public function postDispatch(){
        parent::postDispatch();
        $this->view->logged_in = House_Session::isLoggedIn();
    }
}

?>
