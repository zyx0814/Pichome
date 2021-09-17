<?php
   if(!defined('IN_OAOOA')) {
    exit('Access Denied');
    }
    Hook::listen('adminlogin');
   global $_G;
   $operation = isset($_GET['operation']) ? trim($_GET['operation']):'';
    $setting = C::t('setting')->fetch_all(null);
   if($operation == 'sitesetting'){//站点设置
       if (!submitcheck('settingsubmit')) {
           exit(json_encode(array('settingdata' => $setting)));
       }
       else {
           $settingnew = $_GET['settingnew'];
           $settingnew['bbname'] = $settingnew['sitename'];
           
           updatesetting($setting,$settingnew);
           exit(json_encode(array('success'=>true)));
       }
   }elseif($operation == 'loginset'){//登录页设置
       if(!submitcheck('settingsubmit')) {
           exit(json_encode(array('setting'=>$setting)));
       }
       else{
           $settingnew = $_GET['settingnew'];
           if ($back = trim($settingnew['loginset']['background'])) {
               if (strpos($back, '#') === 0) {
                   $settingnew['loginset']['bcolor'] = $back;
               } else {
                   $arr = explode('.', $back);
                   $ext = array_pop($arr);
                   if ($ext && in_array(strtolower($ext), array('jpg', 'jpeg', 'gif', 'png'))) {
                       $settingnew['loginset']['img'] = $back;
                       $settingnew['loginset']['bcolor'] = '';
                   } else {
                       $settingnew['loginset']['url'] = $back;
                       $settingnew['loginset']['bcolor'] = '';
                   }
               }
           } else {
               $settingnew['loginset']['bcolor'] = '';
           }
           updatesetting($setting,$settingnew);
           exit(json_encode(array('success'=>true)));
       }
   }elseif($operation == 'pagesetting'){//界面设置
       if(!submitcheck('settingsubmit')) {
           exit(json_encode(array('setting'=>$setting)));
       }
       else{
           $settingnew = $_GET['settingnew'];
           updatesetting($setting,$settingnew);
           exit(json_encode(array('success'=>true)));
       }
   }elseif($operation == 'searchsetting'){//筛选器设置
       if(!submitcheck('settingsubmit')) {
           $setting['pichomefilterfileds'] = explode(',',$setting['pichomefilterfileds']);
           exit(json_encode(array('setting'=>$setting['pichomefilterfileds'])));
       }
       else{
           $filterfileds = implode(',',$_GET['filterfileds']);
           $settingnew = ['pichomefilterfileds'=>$filterfileds];
           updatesetting($setting,$settingnew);
           exit(json_encode(array('success'=>true)));
       }
   }elseif($operation == 'upload'){
       include libfile ( 'class/uploadhandler' );
       $options = array ( 'accept_file_types' => '/\.(gif|jpe?g|png)$/i' , 'upload_dir' => $_G[ 'setting' ][ 'attachdir' ] . 'cache/' , 'upload_url' => $_G[ 'setting' ][ 'attachurl' ] . 'cache/' , 'max_file_size' => 2 * 1024 * 1024 , 'thumbnail' => array ( 'max-width' => 256 , 'max-height' =>256 ) );
       $upload_handler = new uploadhandler( $options );
       exit();
   }
    //更新设置函数
    function updatesetting($setting, $settingnew)
    {
        $updatecache = false;
        $settings = array();
        $updatethumb = false;
        foreach ($settingnew as $key => $val) {
            if ($setting[$key] != $val) {
                $updatecache = TRUE;
                if (in_array($key, array('timeoffset', 'regstatus', 'oltimespan', 'seccodestatus'))) {
                    $val = (float)$val;
                }
                    $settings[$key] = ($key != 'statcode') ? getstr($val):$val;
                
            }
        }
        if ($settings) {
            C::t('setting')->update_batch($settings);
        }
        if ($updatecache) {
            updatecache('setting');
        }
        return true;
    }