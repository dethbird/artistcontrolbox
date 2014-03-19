<?php

class House_Controller_Default_Base extends Zend_Controller_Action  {
    //put your code here
    public function preDispatch() {
        parent::preDispatch();
        $this->view->site_url = "http://".$_SERVER['SERVER_NAME'];
        $this->view->controller = $this->getRequest()->getControllerName();
        $this->view->cache_buster = time();
        
        if($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()){
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
        }
    }
}

?>
