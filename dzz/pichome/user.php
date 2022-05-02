<?php
    if (!defined('IN_OAOOA')) {//所有的php文件必须加上此句，防止被外部调用
        exit('Access Denied');
    }
    Hook::listen('check_login');
    global $_G;
    $uid = $_G['uid'];
    $do = isset($_GET['do']) ? trim($_GET['do']) : '';
    require_once libfile('function/user', '', 'user');
    $user = C::t('user')->get_user_by_uid($uid);
	$ismobile = helper_browser::ismobile();
  if(empty($user['avatarstatus']) && dzz_check_avatar($_G['uid'], 'middle')) {
        C::t('user')->update($_G['uid'], array('avatarstatus'=>'1'));
    }
    if ($do == 'personal') {
        if (submitcheck('accountedit')) {
            $uid = $_GET['uid'];
            $user = C::t('user')->fetch_by_uid($uid);
            if (!$uid) exit(json_encode(array('error' => true, 'msg' => '用户不存在')));
            if(isset($_GET['imgpath'])){
				if($ismobile){
					upbase64(trim($_GET['imgpath']),$uid);
				}else{
					$base64img = base64EncodeImage($_GET['imgpath']);
					if(upbase64($base64img,$uid)){
					    @unlink($_GET['imgpath']);
					}
				}
                
            }

            //用户名验证
            $username = trim($_GET['username']);
            if (empty($username)) {
                exit(json_encode(array('error' => true, 'msg' => lang('name_will'))));
            }
            $nickname = trim($_GET['nickname']);
            
            $usernamelen = dstrlen($_GET['nickname']);
            if ($usernamelen < 3) {
                exit(json_encode(array('error' => true, 'msg' => lang('profile_nickname_tooshort'))));
            } elseif ($usernamelen > 30) {
                // showmessage('profile_nickname_toolong');
                exit(json_encode(array('error' => true, 'msg' => lang('profile_nickname_tooshort'))));
            } elseif (!check_username(addslashes(trim(stripslashes($nickname))))) {
                exit(json_encode(array('error' => true, 'msg' => lang('profile_username_illegal'))));
            }
            //如果输入用户名，检查用户名不能重复
            if (strtolower($nickname) != strtolower($user['nickname'])) {
                if (C::t('user')->fetch_by_nickname($nickname)) {
                    exit(json_encode(array('error' => true, 'msg' => lang('user_registered_retry'))));
                }
            }
            
            //如果输入手机号码，检查手机号码不能重复
            $phone = trim($_GET['phone']);
            if ($phone) {
                if (!preg_match("/^\d+$/", $phone)) {
                    exit(json_encode(array('error' => true, 'msg' => lang('user_phone_illegal'))));
                }
                if ($phone != $user['phone'] && C::t('user')->fetch_by_phone($phone)) {
                    exit(json_encode(array('error' => true, 'msg' => lang('user_phone_registered'))));
                }
            }
            //如果输入微信号，检查微信号不能重复
            $weixinid = trim($_GET['weixinid']);
            if ($weixinid) {
                if (!preg_match("/^[a-zA-Z\d_]{5,}$/i", $weixinid)) {
                    exit(json_encode(array('error' => true, 'msg' => lang('weixin_illegal'))));
                }
                if ($weixinid != $user['weixinid'] && C::t('user')->fetch_by_weixinid($weixinid)) {
                    exit(json_encode(array('error' => true, 'msg' => lang('weixin_registered'))));
                    
                }
            }
            //邮箱验证部分
            $email = strtolower(trim($_GET['email']));
            if (!isemail($email)) {
                exit(json_encode(array('error' => true, 'msg' => lang('profile_email_illegal'))));
                
            } elseif (!check_emailaccess($email)) {
                exit(json_encode(array('error' => true, 'msg' => lang('profile_email_domain_illegal'))));
                
            }
            if ($email != strtolower($user['email'])) {
                //邮箱不能重复
                if (C::t('user')->fetch_by_email($email)) {
                    exit(json_encode(array('error' => true, 'msg' => lang('email_registered_retry'))));
                    
                }
            }
            //密码验证部分
            if ($_GET['password']) {
                //验证原密码
                $password0=$_GET['password0'];
                if( md5(md5("").$user['salt'])!=$user['password']) {
                    if(md5(md5($password0).$user['salt'])!=$user['password']){
            			exit(json_encode(array('error' => true, 'msg' => '原密码错误')));
                    }
                }
                if ($_G['setting']['pwlength']) {
                    if (strlen($_GET['password']) < $_G['setting']['pwlength']) {
                        exit(json_encode(array('error' => true, 'msg' => lang('profile_password_tooshort'))));
                        
                    }
                }
                if ($_GET['password'] !== $_GET['password2']) {
                    exit(json_encode(array('error' => true, 'msg' => lang('profile_passwd_notmatch'))));
                    
                }
            }
            $password = $_GET['password'];
            if ($password) {
                $salt = substr(uniqid(rand()), -6);
                $setarr = array('salt' => $salt, 'password' => md5(md5($password) . $salt), 'nickname' => $nickname, 'username' => $username, 'phone' => $phone, 'weixinid' => $weixinid, 'secques' => '', 'email' => $email);
                
            } else {
                $setarr = array('nickname' => $nickname, 'username' => $username, 'email' => $email, 'phone' => $phone, 'weixinid' => $weixinid);
            }
            C::t('user')->update($uid, $setarr);
            $themecolor = isset($_GET['themecolor']) ? trim($_GET['themecolor']) : '';
            C::t('user_setting')->update_by_skey('pichomeusertheme', $themecolor, $uid);
            exit(json_encode(array('success' => true)));
        } else {
            $uid = getglobal('uid');
            $userdata = C::t('user')->fetch($uid);
            $theme = GetThemeColor();
			
			if ($ismobile) {
			    include template('mobile/page/personal');
			} else {
				include template('pc/page/adminPersonal');
			}
            
        }
    }
    elseif ($do == 'uploadimg') {//上传用户头像
        $uid = getglobal('uid');
        $files = $_FILES['file'];
        if (!preg_match('/(gif|jpe?g|png)$/i', $files['type']) || $files['size'] >= 1024 * 1024 * 2) {
            exit(json_encode(array('error' => 'file is not invalite')));
        }
        $type = pathinfo($files['name'],PATHINFO_EXTENSION);
        $imgpath = './data/avatar/'.md5($uid).'.'.$type;
        $return = move_uploaded_file($files["tmp_name"], $imgpath);
        if ($return) {
            exit(json_encode(array('path' => $imgpath)));
        } else {
            exit(json_encode(array('error' => 'upload failed')));
        }
    }
    function dzz_check_avatar($uid, $size = 'middle', $type = 'virtual')
    {
        global $_G;
        $url = $_G['siteurl'] . "avatar.php?uid=$uid&size=$size&type=$type&check_file_exists=1";
        $res = dfsockopen($url, 500000, '', '', TRUE, '', 20);
        if ($res == 1) {
            return 1;
        } else {
            return 0;
        }
    }
    
    function updatesetting($setting, $settingnew)
    {
        $updatecache = false;
        $settings = array();
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
    function base64EncodeImage ($image_file) {
        $base64_image = '';
        $image_info = getimagesize($image_file);
        $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
        $base64_image = chunk_split(base64_encode($image_data));
        return $base64_image;
    }
    function upBase64($base64Data, $uid)
    {
        $img = base64_decode(str_replace(array('data:image/png;base64,','data:image/jpeg;base64,','data:image/gif;base64,','data:image/jpg;base64,'), '', $base64Data));
        $temp = getglobal('setting/attachdir') . 'cache/' . random(5) . '.png';
        //移动文件
        if (!(file_put_contents($temp, $img))) { //移动失败
            return false;
        } else { //移动成功,生成3种尺寸头像
            $home = get_home($uid);
            if (!is_dir(DZZ_ROOT . './data/avatar/' . $home)) {
                set_home($uid, DZZ_ROOT . './data/avatar/');
            }
            $bigavatarfile = DZZ_ROOT . './data/avatar/' . get_avatar($uid, 'big');
            $middleavatarfile = DZZ_ROOT . './data/avatar/' . get_avatar($uid, 'middle');
            $smallavatarfile = DZZ_ROOT . './data/avatar/' . get_avatar($uid, 'small');
            include_once libfile('class/image');
            $image = new image();
            $success = 0;
            if ($thumb = $image->Thumb($temp, $bigavatarfile, 200, 200, 2)) {
                $success++;
            }
            if ($thumb = $image->Thumb($temp, $middleavatarfile, 120, 120, 2)) {
                $success++;
            }
            if ($thumb = $image->Thumb($temp, $smallavatarfile, 48, 48, 2)) {
                $success++;
            }
            if ($success > 2) {
                C::t('user')->update($uid, array('avatarstatus' => '1'));
            }
            @unlink($temp);
            return $success;
        }
    }