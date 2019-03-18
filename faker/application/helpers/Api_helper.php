<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Reference Id to be used for defining an entity
 * @ref string, reference prefix id
 * @mode int, represents the output format option
 *       0 to default four section format
 *       1 to three section format
 */
 
function create_refid($ref='') {
    if(empty($ref)) $ref = strtotime('-14 years -7 months -25 days -6 hours', strtotime(date("Y-m-d H:i:s")));
    $salt = md5(uniqid());
    $res = substr($ref,0,9).substr($salt,11,6).mt_rand(101,998);
    return $res;
}

function token_generator(){
	return md5(uniqid());
}

function duration_time($time) {
    $res = '';
    if($time>=3600)
        $res = sprintf("%02d",floor($time/3600));
    if(($time%3600) > 0)
        $res .= ':'.sprintf("%02d",floor(($time%3600)/60));
    if((($time%3600)%60) > 0)
        $res .= ':'.sprintf("%02d",(($time%3600)%60));
    $res =preg_replace("/^:/", '', $res);
    return $res;
}

function generate_password($length, $strength){
    $vowels = 'aeuy';
    $consonants = 'bdghjmnpqrstvz';
    if ($strength & 1)
        $consonants .= 'BDGHJLMNPQRSTVWXZ';
    if ($strength & 2) 
        $vowels .= "AEUY";
    if ($strength & 4) 
        $consonants .= '23456789';
    if ($strength & 8) 
        $consonants .= '@#$%';

    $password = '';
    $alt = time() % 2;
    for ($i = 0; $i < $length; $i++){
        if ($alt == 1){
            $password .= $consonants[(rand() % strlen($consonants))];
            $alt = 0;
        }else{
            $password .= $vowels[(rand() % strlen($vowels))];
            $alt = 1;
        }
    }
    return $password;
}
