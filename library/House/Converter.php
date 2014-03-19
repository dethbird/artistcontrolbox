<?php


class House_Converter {
    
    public static function youtubeLinkToThumbnail($youtube_link){
        $url = parse_url($youtube_link);
        $query = parse_str($url['query']);
        return "http://img.youtube.com/vi/".$v."/0.jpg";
    }
    
    public static function youtubeLinkToEmbedCode($youtube_link, $width, $height){
        $url = parse_url($youtube_link);
        $query = parse_str($url['query']);
        return '<iframe width="'.$width.'" height="'.$height.'" src="http://www.youtube.com/embed/'.$v.'" frameborder="0" allowfullscreen></iframe>';
    }
    
}

?>
