<?php
    defined( 'ABSPATH' ) or die( 'Nope, not accessing this' );
    
    add_shortcode('popup', function($perms){
        if(isset($perms['id']) && !empty($perms['id'])){
            global $wpdb;
            $id = $perms['id'];
            $ip = getUserIpAddr();
            $popup      = $wpdb->get_row("SELECT * FROM `".CXL_POPUP."` WHERE `id` = '$id' AND `status` = 'Active'");
            if(!empty($popup)){
                $video = $popup->video ? '<video  controls 
                src="'.CXL_UPLOAD["baseurl"].'/popup-video/'.$popup->video.'"">' : '';
                return $video;
            }
        }
        else{
            return '<code>Please add id perms with shortcode</code>';
        }
    });
    
    function getUserIpAddr(){
        if(!empty($_SERVER['HTTP_CLIENT_IP'])){
            //ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            //ip pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
