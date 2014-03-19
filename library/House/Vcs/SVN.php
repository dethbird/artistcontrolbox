<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SVN
 *
 * @author rsatsangi
 */
class House_Vcs_SVN {
    /**
     * 
     * @param type $repository_path
     * @param type $dev_checkout_path
     */
    public function __construct($repository_path, $dev_checkout_path, $prod_checkout_path = null) {
        $this->repository_path = $repository_path;
        $this->dev_checkout_path = $dev_checkout_path;
        $this->prod_checkout_path = $prod_checkout_path;
    }
    
    public function getStatus(){
        //file statuses
        $cmd = "/usr/bin/svn status ".$this->dev_checkout_path;
        exec($cmd . " 2>&1", $output);
        $response = array();
        if(count($output)>0){
            foreach($output as $line){
                if(
                    stripos($line, "library/dwoo/")===false
                    && stripos($line, "assets/img/")===false
                ){
                    $response['files'][] = $this->parseStatusLine($line);
                }
            }
        }
        //get production version
        $output = null;
        $cmd = 'sudo -u root -S svn info -R '.$this->prod_checkout_path.' < /lib/security/sudopass.secret | awk \'/^Last Changed Rev:/ {print $NF}\' | sort -n | tail -n 1';
        //House_Log::log($cmd);
        exec($cmd . " 2>&1", $output);
        $response['prod_revision'] = $output[0];
        
        //get dev version
        $output = null;
        $cmd = 'svn info -R '.$this->dev_checkout_path.' | awk \'/^Last Changed Rev:/ {print $NF}\' | sort -n | tail -n 1';
        exec($cmd . " 2>&1", $output);
        $response['dev_revision'] = $output[0];
        
        return $response;
    }
 
    
    
    
    public function getAssets(){
        
        $response = new stdClass();
        
        
        $handle = opendir($this->dev_checkout_path."/templates"); 
        while($file = readdir($handle)){
            $pathinfo = pathinfo($file);
            if($pathinfo['extension']=='html'){
                $_line = new stdClass();
                $_line->file = "templates/".$pathinfo['basename'];
                $response->templates[] = $_line;
            }
            
        }
        
        //javascript
        $_line = new stdClass();
        $_line->file = "assets/js/application.js";
        $response->javascript[] = $_line;
        
        //css
        $_line = new stdClass();
        $_line->file = "assets/css/docs.css";
        $response->css[] = $_line;
        
        return $response;
    }
    
    public function getFileDetails($file){
        
        $response = new stdClass();
        
        //get contents
        $cmd = "cat ".$this->dev_checkout_path."/".$file;
        exec($cmd . " 2>&1", $output);
        $response->contents = implode("\n",$output);
        
        //get log
        $output = null;
        $cmd = "/usr/bin/svn log --xml ".$this->dev_checkout_path."/".$file;
        exec($cmd . " 2>&1", $output);
        $log_xml = implode("\n",$output)."\n";
        try {
            $log_xml = @simplexml_load_string($log_xml);
        } catch (Exception $e){
            //House_Log::log($e);
        }
        $log_xml = json_encode($log_xml);
        if($log_xml!='false'){
            $response->log = json_decode($log_xml);
            $response->log = is_array($response->log->logentry) ? $response->log->logentry : array($response->log->logentry);
            foreach($response->log as $k=>$v){
                $v->revision =  $v->{'@attributes'}->revision;
                $response->log[$k] = $v;
            }
        } else {
            $response->log = array();
        }
        
        //get view data for this file
        
        
        //get fileinfo
        $response->fileinfo->filesize = filesize($this->dev_checkout_path."/".$file);
        $response->fileinfo->date_modified = date("c", filemtime($this->dev_checkout_path."/".$file));
        
        return $response;
    }
    
    /**
     * 
     * @param type $filename
     * @param type $type html|css|javascript
     */
    public function createWebsiteFile($filename, $type){
        switch ($type) {
            case 'html':    
                $path = 'templates/';
                break;
            case 'javascript':
                $path = 'assets/js/';
                break;
            case 'css':
                $path = 'assets/css/';
                break;
            default:
               $path = 'templates/';
        }
        $file = $path.$filename;
        $cmd = "touch ".$this->dev_checkout_path."/".$file;
        exec($cmd . " 2>&1", $output);
        $output =  implode("\n",$output);
        
        $this->putFileContents($file, '<div class="container"><div class="row span12">Content here</div></div>');
        
        return $output;
    }
    
    public function putFileContents($file, $contents){
        $response = file_put_contents($this->dev_checkout_path."/".$file, $contents);
    }
    
    public function revertFileContents($file, $revision=null){
        if($revision==""){
            $cmd = "/usr/bin/svn revert ".$this->dev_checkout_path."/".$file;
            exec($cmd . " 2>&1", $output);
            $output =  implode("\n",$output);
            return $output;
        } else {
            $cmd = "/usr/bin/svn export -r ".$revision." ".$this->dev_checkout_path."/".$file;
            exec($cmd . " 2>&1", $output);
            $output =  implode("\n",$output);
            return $output;
        }
    }
    
    public function commitModifications($msg){
        $status = $this->getStatus();
        //loop through all files
        //commit all untracked files
        //build commit list
        $filelist = null;
        foreach($status as $_status){
            //House_Log::log($_status);
            if($_status->status=="?"){
                //$cmd = "/usr/bin/svn add ".$this->dev_checkout_path."/".$file;
                $cmd = 'sudo -u root -S /usr/bin/svn add '.$this->dev_checkout_path."/".$_status->file.' < /lib/security/sudopass.secret';
                //House_Log::log($cmd);
                exec($cmd . " 2>&1", $output);
                $output = implode("\n",$output);
            }
            $filelist .= " ".$this->dev_checkout_path."/".$_status->file;
        }
        
        //$cmd = "sudo /usr/bin/svn ci ".$filelist." -m \"". $msg ."\"";
        $cmd = 'sudo -u root -S /usr/bin/svn ci '.$filelist.' -m "'.$msg.'" < /lib/security/sudopass.secret';
        //House_Log::log($cmd);
        exec($cmd . " 2>&1", $output);
        $output = implode("\n",$output);
        
        return $output;

    }
    
    
    public function publishToProd(){
        $cmd = 'sudo -u root -S /usr/bin/svn up '.$this->prod_checkout_path.'/templates < /lib/security/sudopass.secret';
        //House_Log::log($cmd);
        exec($cmd . " 2>&1", $output);
        $output = implode("\n",$output);
        $cmd = 'sudo -u root -S /usr/bin/svn up '.$this->prod_checkout_path.'/assets < /lib/security/sudopass.secret';
        $output = null;
        //House_Log::log($cmd);
        exec($cmd . " 2>&1", $output);
        $output = implode("\n",$output);
        return $output;
    }
    
    public function parseStatusLine($line){
        $_line = new stdClass();
        $_line->status = substr($line, 0, 1);
        $_line->file = str_replace($this->dev_checkout_path, "", trim(substr($line, 1, strlen($line)-1)));
        return $_line;
    }
}

?>
