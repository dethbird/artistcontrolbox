<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    public function _initApp(){
        date_default_timezone_set('America/Los_Angeles');
        error_reporting(E_ERROR | E_WARNING | E_PARSE);
        ini_set('session.gc_maxlifetime', 7200);
        
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace('House_');
        
        $this->config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        Zend_Registry::set('config', $this->config);
        
        //amazon buy link generator
        require_once 'Amazon/ButtonGenerator.php';
        require_once 'Amazon/SignatureUtils.php';
        
        $bg = new ButtonGenerator();
        
        require_once 'ActiveRecord/ActiveRecord.php';
        $cfg = ActiveRecord\Config::instance();
        $cfg->set_model_directory(APPLICATION_PATH.'/models');
        $cfg->set_connections(array('development' => 'mysql://'.Zend_Registry::get('config')->get('mysql')->username.':'.Zend_Registry::get('config')->get('mysql')->password.'@'.Zend_Registry::get('config')->get('mysql')->host.'/'.Zend_Registry::get('config')->get('mysql')->database.";charset=utf8")); //local
        
        session_set_cookie_params(Zend_Registry::get('config')->get('session')->timeout);
        Zend_Session::setOptions(array (
                          'cache_expire' => '180',
                          'cookie_httponly' => 'on',
                          'cookie_lifetime' => Zend_Registry::get('config')->get('session')->timeout,
                          /*'cookie_secure' => 'on',*/
                          'use_cookies' => 'on',
                          'use_only_cookies' => 'on',
                          'remember_me_seconds' => Zend_Registry::get('config')->get('session')->timeout
        ));
        
        Zend_Session::start();
        
        $this->layout = Zend_Layout::startMvc();
        
        if(PHP_SAPI!='cli'){
            $front = Zend_Controller_Front::getInstance();
            $front->addModuleDirectory(APPLICATION_PATH.'/modules');
        } else {
            
            $this->bootstrap ('frontcontroller');
            $front = $this->getResource('frontcontroller');
            $front->setRouter (new BuddyMedia_Router_Cli ());
            $front->setRequest (new Zend_Controller_Request_Simple ());
        }
        
        
    }

}

