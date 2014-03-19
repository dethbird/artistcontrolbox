<?php


class House_Log {
    
    
    public static function log($message, $priority = Zend_Log::INFO){
        $logger = new Zend_Log();
        $writer = new Zend_Log_Writer_Stream(Zend_Registry::get('config')->get('log_file'));
        $logger->addWriter($writer);
        $logger->log(is_string($message)==1 ? $message : print_r($message,1), $priority);
    }
    
    public static function logFirebug($message, $priority = Zend_Log::INFO){
        $logger = new Zend_Log();
        $writer = new Zend_Log_Writer_Firebug();
        $logger->addWriter($writer);
        $logger->log($message, $priority);
    }
    
    
}

?>
