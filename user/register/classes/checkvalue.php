<?php
namespace user\register\classes;

use \dzz_table;
use \core as C;
use \DB as DB;
class Checkvalue{

    public function run(&$params){

        global $_G;

        $type = isset($params['returnType']) ? $params['returnType']:'';
		
        $setting =  $_G['setting'];
		
		$bbrulehash = $setting['bbrules'] ? substr(md5(FORMHASH), 0, 8) : '';

        //验证提交是否合法，阻止外部非法提交
        chk_submitroule($type);
		
		//验证同意协议
		if($setting['bbrules'] && !$params['agree']) {
			showTips(array('error'=>lang('register_rules_agree')), $type);
		}
		
       
       
        //验证用户名 
        $usernamelen = dstrlen($params['nickname']);
        if($usernamelen < 3) {
            showTips(array('error'=>lang('profile_nickname_tooshort')), $type);
        }
        if($usernamelen > 30) {
            showTips(array('error'=>lang('profile_nickname_toolong')), $type);
        } 
        if(empty($params['username'])) {
            showTips(array('error'=>lang('username_will')), $type);
        } 
        //验证邮箱
        $params['email'] = strtolower(trim($params['email']));
        checkemail($params['email'],$type); 
		

        //验证密码长度
        if($setting['pwlength']) {

            if(strlen($params['password']) < $setting['pwlength']) {

                showTips(array('error'=>lang('profile_password_tooshort',array('pwlength' => $setting['pwlength']))),$type);
            }
        }
        //验证密码强度
        if($setting['strongpw']) {

            $strongpw_str = array();

            if(in_array(1, $setting['strongpw']) && !preg_match("/\d+/", $params['password'])) {

                $strongpw_str[] = lang('strongpw_1');

            }

            if(in_array(2, $setting['strongpw']) && !preg_match("/[a-z]+/", $params['password'])) {

                $strongpw_str[] = lang('strongpw_2');

            }

            if(in_array(3, $setting['strongpw']) && !preg_match("/[A-Z]+/", $params['password'])) {

                $strongpw_str[] = lang('strongpw_3');

            }

            if(in_array(4, $setting['strongpw']) && !preg_match("/[^a-zA-z0-9]+/", $params['password'])) {

                $strongpw_str[] = lang('strongpw_4');

            }

            if($strongpw_str) {

                showTips(array('error'=>lang('password_weak').implode(',', $strongpw_str)),$type);
            }
        }
        //验证两次密码一致性
        if($params['password'] !== $params['password2']) {

            showTips(array('error'=>lang('password_not_match')),$type);
        }

        if(!$params['password'] || $params['password'] != addslashes($params['password'])) {

            showTips(array('error'=>lang('profile_passwd_illegal')), $type);
        }

        
    }
}