<?php
    
    if (!defined('IN_OAOOA')) {//所有的php文件必须加上此句，防止被外部调用
        exit('Access Denied');
    }
    Hook::listen('adminlogin');
//管理权限进入
    $do = isset($_GET['do']) ? trim($_GET['do']) : '';
	include libfile('function/cache');
    global $_G;
    if (isset($_G['setting'])) $setting = $_G['setting'];
    else  $setting = C::t('setting')->fetch_all();
    $theme = GetThemeColor();
    if ($do == 'basic') {
        if (submitcheck('settingsubmit')) {
            $settingnew = $_GET['settingnew'];
            updatesetting($setting, $settingnew);
            exit(json_encode(array('success' => true)));
        } else {
			
			$operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';
			if($operation=='getdata'){
				exit(json_encode(array('data' => $setting)));
			}else{
				$waterfilepath = $_G['setting']['attachurl'] . 'sitelogo/sitelogo.png';
				if (!file_exists($waterfilepath)) {
					$waterfilepath = htmlspecialchars;
				}
				 include template('page/adminBasic');
			}
           
        }
    } elseif ($do == 'uploadlogo') {//上传logo
        global $_G;
        $files = $_FILES['file'];
        if ($files["type"] != 'image/png' || $files['size'] >= 1024 * 1024 * 2) {
            exit(json_encode(array('error' => 'file is not invalite')));
        }
        $waterfilepath = $_G['setting']['attachurl'] . 'sitelogo/sitelogo.png';
        $return = move_uploaded_file($files["tmp_name"], $waterfilepath);
        exit(json_encode(array('success' => true)));
    } elseif ($do == 'pagesetting') {//界面设置
        if (submitcheck('settingsubmit')) {
            $settingnew = $_GET['settingnew'];
            updatesetting($setting, $settingnew);
            exit(json_encode(array('success' => true)));
        } else {
			$waterfilepath = $_G['setting']['attachurl'] . 'sitelogo/sitelogo.png';
            include template('page/adminPagesetting');
        }
    } elseif ($do == 'loginpage') {//登录页设置
        if (submitcheck('settingsubmit')) {
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
            updatesetting($setting, $settingnew);
            exit(json_encode(array('success' => true)));
        } else {
            include template('page/adminLoginpage');
            
        }
    } elseif ($do == 'fileterset') {//筛选项设置
        if (submitcheck('settingsubmit')) {
            $settingnew = $_GET['settingnew'] ? $_GET['settingnew'] : array();
            if ($settingnew['pichomefilterfileds']) {
                $settingnew['pichomefilterfileds'] = serialize($settingnew['pichomefilterfileds']);
            } else {
                $settingnew['pichomefilterfileds'] = serialize(array());
            }
			
            updatesetting($setting, $settingnew);
            exit(json_encode(array('success' => true)));
        } else {
			if($setting['pichomefilterfileds']){
				$data = json_encode($setting['pichomefilterfileds']);
			}else{
				$data = json_encode(array());
			}
            include template('page/adminFileterset');
        }
    }
    function updatesetting($setting, $settingnew){
        $updatecache = false;
        $settings = array();
        $updatethumb = false;
        foreach ($settingnew as $key => $val) {
            if ($setting[$key] != $val) {
                $updatecache = TRUE;
                if (in_array($key, array('timeoffset', 'regstatus', 'oltimespan', 'seccodestatus'))) {
                    $val = (float)$val;
                }
                $settings[$key] = $val;
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
    exit();