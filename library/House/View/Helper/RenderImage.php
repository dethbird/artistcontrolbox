<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RenderImage
 *
 * @author rishi
 */
class House_View_Helper_RenderImage extends Zend_View_Helper_Abstract {
    
    /**
     * renders html for image using https://github.com/lencioni/SLIR
     * expects the given image url to be living on the server with SLIR installed and running, for example: imgsrc.artistcontrolbox.com/h100-w100/images/image.jpg
     * 
     * @param type $url
     * @param type $size
     * @param type $class
     * @return type
     */
    function renderImage($url, $size=150, $class=null)
    {   
        $pathinfo = parse_url($url);
        return '<img src="'.$pathinfo['scheme'].'://'.$pathinfo['host'].'/w'.$size.'-h'.$size."/".$pathinfo['path'].'?cachebuster='.time().'" border="0" '.($class==null?null:'class="'.$class.'"').' height="'.$size.'" width="'.$size.'" />';
        
            
        
    }
    
}

?>