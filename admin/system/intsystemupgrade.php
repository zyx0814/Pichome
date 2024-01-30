<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */
if (!defined('IN_OAOOA') || !defined('IN_ADMIN')) {
    exit('Access Denied');
}
$navtitle = lang('upgrade') . ' - ' . lang('admin_navtitle');
@set_time_limit(0);
include_once DZZ_ROOT . './core/core_version.php';
include_once libfile('function/admin');
include_once libfile('function/cache');
$dzz_upgrade = new dzz_upgrade();
$step = intval($_GET['step']);
$op = $_GET['op'];
$step = $step ? $step : 1;
$operation = $_GET['operation'] ? trim($_GET['operation']) : 'check';

$steplang = array('', lang('founder_upgrade_updatelist'), lang('founder_upgrade_download'), lang('founder_upgrade_compare'), lang('founder_upgrade_upgrading'), lang('founder_upgrade_complete'), 'dbupdate' => lang('founder_upgrade_dbupdate'));

if ($operation == 'patch' || $operation == 'cross') {
    if (!$_G['setting']['bbclosed']) {
        C::t('setting')->update('bbclosed', 1);
        updatecache('setting');
    }

    $msg = '';
    $version = trim($_GET['version']);
    $release = trim($_GET['release']);
    $locale = trim($_GET['locale']);
    $charset = trim($_GET['charset']);
    $upgradeinfo = $upgrade_step = array();

   //  if ($_GET['ungetfrom']) {
   //      if (md5($_GET['ungetfrom'] . $_G['config']['security']['authkey']) == $_GET['ungetfrommd5']) {
			// $dbreturnurl = $_G['siteurl'] . ADMINSCRIPT . '?mod=system#/systemupgrade/'.$version; 
   //          $url = outputurl(  $_G['siteurl'] . 'install/update.php?step=prepare&from=' . rawurlencode($dbreturnurl) . '&frommd5=' . rawurlencode(md5($dbreturnurl . $_G['config']['security']['authkey'])) );
			// exit(json_encode(array('iframe'=>true,'url'=>$url)));
   //          dheader('Location: ' . $url);
   //      } else {
			// exit(json_encode(array('error'=>true,'msg'=>lang('upgrade_param_error'))));
   //      }
   //  }

    $upgrade_step = C::t('cache') -> fetch('upgrade_step');
    $upgrade_step = dunserialize($upgrade_step['cachevalue']);
    $upgrade_step['step'] = $step;
    $upgrade_step['operation'] = $operation;
    $upgrade_step['version'] = $version;
    $upgrade_step['release'] = $release;
    $upgrade_step['charset'] = $charset;
    $upgrade_step['locale'] = $locale;
    C::t('cache') -> insert(array('cachekey' => 'upgrade_step', 'cachevalue' => serialize($upgrade_step), 'dateline' => $_G['timestamp'], ), false, true);

    $upgrade_run = C::t('cache') -> fetch('upgrade_run');
    if (!$upgrade_run) {
        C::t('cache') -> insert(array('cachekey' => 'upgrade_run', 'cachevalue' => serialize($_G['setting']['upgrade']), 'dateline' => $_G['timestamp'], ), false, true);
        $upgrade_run = $_G['setting']['upgrade'];
    } else {
        $upgrade_run = dunserialize($upgrade_run['cachevalue']);
    }

    if ($step != 5) {

        foreach ($upgrade_run as $type => $list) {
            if ($type == $operation && $version == $list['latestversion']) {
                $dzz_upgrade -> locale = $locale;
                $dzz_upgrade -> charset = $charset;
                $upgradeinfo = $list;
                break;
            }
        }
        if (!$upgradeinfo) {
			upgradeinformation(-1);
            exit(json_encode(array('upgradeNone'=>true)));
        }

        $updatefilelist = $dzz_upgrade -> fetch_updatefile_list($upgradeinfo);
        $updatemd5filelist = $updatefilelist['md5'];
        $updatefilelist = $updatefilelist['file'];
        $theurl = $_G['siteurl'].ADMINSCRIPT . '?mod=system&op=intsystemupgrade&operation=' . $operation . '&version=' . $version . '&locale=' . $locale . '&charset=' . $charset;
        if (empty($updatefilelist)) {
			upgradeinformation(-2);
            exit(json_encode(array('upgradeError'=>true)));
        }

    }

    if ($step == 1) {
        exit(json_encode(array('step'=>1,'data'=>$updatefilelist)));
    } elseif ($step == 2) {
        $fileseq = intval($_GET['fileseq']);
        $fileseq = $fileseq ? $fileseq : 1;
        if ($fileseq > count($updatefilelist)) {
            $linkurl = $theurl . '&step=3';
			upgradeinformation(0);
            exit(json_encode(array('step'=>2,'downloadstatus'=>4)));
        } else {
			$downloadstatus = $dzz_upgrade -> download_file($upgradeinfo, $updatefilelist[$fileseq - 1], 'upload', $updatemd5filelist[$fileseq - 1]);
			if ($downloadstatus == 1) {
				$data = array('file' => $updatefilelist[$fileseq - 1],'fileseq'=>$fileseq,'percent' => sprintf("%2d", 100 * $fileseq / count($updatefilelist)));
				upgradeinformation(1);
				exit(json_encode(array('step'=>2,'data'=>$data,'downloadstatus'=>1)));
			} elseif ($downloadstatus == 2) {
				$data = array('file' => $updatefilelist[$fileseq - 1],'fileseq'=>($fileseq + 1),'percent' => sprintf("%2d", 100 * $fileseq / count($updatefilelist)));
				upgradeinformation(1);
				exit(json_encode(array('step'=>2,'data'=>$data,'downloadstatus'=>2)));
			} else {
				$data = array('file' => $updatefilelist[$fileseq - 1]);
				upgradeinformation(-3);
				exit(json_encode(array('step'=>2,'data'=>$data,'downloadstatus'=>3)));
			}
        }
    } elseif ($step == 3) { 
		
        list($modifylist, $showlist, $ignorelist,$newlist) = $dzz_upgrade -> compare_basefile($upgradeinfo, $updatefilelist,$updatemd5filelist);

		$tableData = array();
		$button = '';

        if (empty($modifylist) && empty($showlist) && empty($ignorelist) && empty($newlist)) {
            
        }else{
			if(empty($modifylist)){
				$button = lang('founder_upgrade_regular');
			}else{
				$button = lang('founder_upgrade_force');
			}
			foreach($updatefilelist as $val){
				if(isset($modifylist[$val])){
					$res = array('name'=>$val,'status'=>1);
				}elseif(isset($showlist[$val])){
					$res = array('name'=>$val,'status'=>2);
				}elseif(isset($newlist[$val])){	
					$res = array('name'=>$val,'status'=>3);
				}else{
                    $res = array('name'=>$val,'status'=>1);
                }
				$tableData[] = $res;
			}
		}
		upgradeinformation(-4);
		exit(json_encode(array('step'=>3,'tableData'=>$tableData,'button'=>$button,'oldversion'=>CORE_VERSION)));
    } elseif ($step == 4) {

        $confirm = $_GET['confirm'];
        if (!$confirm) {
            if ($_GET['siteftpsetting']) {
                $action = $theurl . '&step=4&confirm=ftp&formhash='.FORMHASH . ($_GET['startupgrade'] ? '&startupgrade=1' : '');
				exit(json_encode(array('siteftpsetting'=>true,'url'=>$action)));
            }
            if ($upgradeinfo['isupdatedb']) {
                $checkupdatefilelist = array('install/update.php', 'install/data/install.sql', 'install/data/install_data.sql');
                $checkupdatefilelist = array_merge($checkupdatefilelist, $updatefilelist);
            } else {
                $checkupdatefilelist = $updatefilelist;
            }
            if ($dzz_upgrade -> check_folder_perm($checkupdatefilelist)) {
                $confirm = 'file';
            } else {
				exit(json_encode(array('againsiteftpsetting'=>true)));
            }
        }

        $paraftp = '';
        if ($_GET['siteftp']) {
            foreach ($_GET['siteftp'] as $k => $v) {
                $paraftp .= '&siteftp[' . $k . ']=' . $v;
            }
        }
        if (!$_GET['startupgrade']) {
            if (!$_GET['backfile']) {
                $linkurl = $theurl . '&step=4&backfile=1&confirm=' . $confirm . $paraftp;
				upgradeinformation(2);
				exit(json_encode(array('backfile'=>true,'url'=>$linkurl)));
            }
            foreach ($updatefilelist as $updatefile) {
                $destfile = DZZ_ROOT . $updatefile;
                $backfile = DZZ_ROOT . './data/back/pichome' . CORE_VERSION . '/' . $updatefile;
                if (is_file($destfile)) {
                    if (!$dzz_upgrade -> copy_file($destfile, $backfile, 'file')) {
						upgradeinformation(-5);
                        exit(json_encode(array('destfile'=>true)));
                    }
                }
            }
			upgradeinformation(3);
			exit(json_encode(array('complete'=>true,'url'=>$theurl.'&step=4&startupgrade=1&confirm='.$confirm.$paraftp)));
        }

        $linkurl = $theurl . '&step=4&startupgrade=1&confirm=' . $confirm . $paraftp;
        $ftplinkurl = $theurl . '&step=4&startupgrade=1&siteftpsetting=1';
        foreach ($updatefilelist as $updatefile) {
            $srcfile = DZZ_ROOT . './data/update/pichome' . $version . '/' . $updatefile;
            if ($confirm == 'ftp') {
                $destfile = $updatefile;
            } else {
                $destfile = DZZ_ROOT . $updatefile;
            }
            if (!$dzz_upgrade -> copy_file($srcfile, $destfile, $confirm)) {
                if ($confirm == 'ftp') {
					upgradeinformation(-6);
                    exit(json_encode(array('ftpError'=>true,'file'=>$updatefile,'againUpdate'=>$linkurl,'ftpUrl'=>$ftplinkurl)));
                } else {
					upgradeinformation(-7);
                    exit(json_encode(array('copyError'=>true,'file'=>$updatefile,'againUpdate'=>$linkurl,'ftpUrl'=>$ftplinkurl)));
                }
            }
        }
        if ($upgradeinfo['isupdatedb']) {
           
            $upgrade_step['step'] = 'dbupdate';
            C::t('cache') -> insert(array('cachekey' => 'upgrade_step', 'cachevalue' => serialize($upgrade_step), 'dateline' => $_G['timestamp'], ), false, true);
            // $dbreturnurl = $_G['siteurl'] . ADMINSCRIPT . '?mod=system&op=systemupgrade&operation=' . $operation . '&version=' . $version . '&step=5';
			$dbreturnurl = $_G['siteurl'] . ADMINSCRIPT . '?mod=system#/systemupgrade/'.$version; 
            $linkurl = $_G['siteurl'] . 'install/update.php?step=prepare&from=' . rawurlencode($dbreturnurl) . '&frommd5=' . rawurlencode(md5($dbreturnurl . $_G['config']['security']['authkey']));
			upgradeinformation(4);
			exit(json_encode(array('updateMysql'=>true,'url'=>$linkurl)));

        }
		exit(json_encode(array('nextStep'=>true)));

    } elseif ($step == 5) {
        $file = DZZ_ROOT . './data/update/pichome' . $version . '/updatelist.tmp';
        @unlink($file);
        @unlink(DZZ_ROOT . './install/update.php');
		//打开站点
		C::t('setting')->update('bbclosed', 0);
        C::t('cache') -> delete('upgrade_step');
        C::t('cache') -> delete('upgrade_run');
        C::t('setting') -> update('upgrade', '');
        updatecache('setting');
        $old_update_dir = './data/update/';
        $new_update_dir = './data/update' . md5('update' . $_G['config']['security']['authkey']) . '/';
        $old_back_dir = './data/back/';
        $new_back_dir = './data/back' . md5('back' . $_G['config']['security']['authkey']) . '/';
        $dzz_upgrade -> copy_dir(DZZ_ROOT . $old_update_dir, DZZ_ROOT . $new_update_dir);
        $dzz_upgrade -> copy_dir(DZZ_ROOT . $old_back_dir, DZZ_ROOT . $new_back_dir);
        $dzz_upgrade -> rmdirs(DZZ_ROOT . $old_update_dir);
        $dzz_upgrade -> rmdirs(DZZ_ROOT . $old_back_dir);
		upgradeinformation(0);
		exit(json_encode(array('version' => $version,'dir'=>$new_update_dir,'backdir'=>$new_back_dir)));
        // $msg = lang('upgrade_successful', array('version' => $version, 'save_update_dir' => $new_update_dir, 'save_back_dir' => $new_back_dir, 'upgradeurl' => upgradeinformation(0)));

    }

}elseif ($operation == 'check') {
    $msg = '';
	$upgrade_step = C::t('cache') -> fetch('upgrade_step');
	if (!empty($upgrade_step['cachevalue'])) {
		$upgrade_step['cachevalue'] = dunserialize($upgrade_step['cachevalue']);
		if (!empty($upgrade_step['cachevalue']['step'])) {

			$theurl = ADMINSCRIPT . '?mod=system&op=systemupgrade&operation=' . $upgrade_step['cachevalue']['operation'] . '&version=' . $upgrade_step['cachevalue']['version'] . '&locale=' . $upgrade_step['cachevalue']['locale'] . '&charset=' . $upgrade_step['cachevalue']['charset'];

			$recheckurl = ADMINSCRIPT . '?mod=system&op=systemupgrade&operation=recheck';
			if ($upgrade_step['cachevalue']['step'] == 'dbupdate') {
				// $dbreturnurl = $_G['siteurl'] . $theurl . '&step=5';
				$dbreturnurl = $_G['siteurl'] . ADMINSCRIPT . '?mod=system#/systemupgrade/'.$upgrade_step['cachevalue']['version']; 
				$stepurl = $_G['siteurl'] . 'install/update.php?step=prepare&from=' . rawurlencode($dbreturnurl) . '&frommd5=' . rawurlencode(md5($dbreturnurl . $_G['config']['security']['authkey']));
				exit(json_encode(array('iframe'=>true,'steplang'=>$steplang['dbupdate'],'url'=>$stepurl)));
			} else {
				exit(json_encode(array('html'=>true,'steplang'=>$steplang[$upgrade_step['cachevalue']['step']],'data'=>$upgrade_step['cachevalue'])));
			}
		}else{
			$dzz_upgrade -> check_upgrade();
			exit(json_encode(array('html'=>false)));
		}
	}else{
		$dzz_upgrade -> check_upgrade();
		exit(json_encode(array('html'=>false)));
	}
    exit();
}elseif ($operation == 'showupgrade') {

    if ($_G['setting']['upgrade']) {

        C::t('cache') -> insert(array('cachekey' => 'upgrade_step', 'cachevalue' => serialize(array('curversion' => $dzz_upgrade -> versionpath())), 'dateline' => $_G['timestamp'], ), false, true);

        $upgraderow = $patchrow = array();
        $charset = str_replace('-', '', strtoupper($_G['config']['output']['charset']));
        $dbversion = helper_dbtool::dbversion();
        $locale = '';

        if ($charset == 'BIG5') {
            $locale = 'TC';
        } elseif ($charset == 'GBK') {
            $locale = 'SC';
        } elseif ($charset == 'UTF8') {
            if ($_G['config']['output']['language'] == 'zh-cn' || $_G['config']['output']['language'] == 'zh_cn') {
                $locale = 'SC';
            } elseif ($_G['config']['output']['language'] == 'zh-tw' || $_G['config']['output']['language'] == 'zh_tw') {
                $locale = 'TC';
            }else{
                $locale = 'SC';
            }
        }

        if (!is_array($_G['setting']['upgrade']))
            $_G['setting']['upgrade'] = unserialize($_G['setting']['upgrade']);
        $list = array();
        foreach ($_G['setting']['upgrade'] as $type => $upgrade) {
            $flist = array();
            $unupgrade = 0;
            if (version_compare($upgrade['phpversion'], PHP_VERSION) > 0 || version_compare($upgrade['mysqlversion'], $dbversion) > 0) {
                $unupgrade = 1;
            }
            if ($unupgrade) {
                $flist['title'] = 'oaooa PicHome ' . $upgrade['latestversion'] . '_' . $locale . '_' . $charset;
                $flist['msg'] = lang('founder_upgrade_require_config') . ' php v' . PHP_VERSION . 'MYSQL v' . $dbversion;
                $flist['update'] = false;
            } else {
                $flist['title'] = 'oaooa PicHome ' . $upgrade['latestversion'] . '_' . $locale . '_' . $charset;
                $flist['update'] = true;
                $flist['version'] = $upgrade['latestversion'];
                $flist['locale'] = $locale;
                $flist['charset'] = $charset;
            }
            $list[] = $flist;
        }
        exit(json_encode(array('content'=>true,'data'=>$list)));
    } else {
        exit(json_encode(array('content'=>false)));
    }
}elseif ($operation == 'recheck') {
    $upgrade_step = C::t('cache') -> fetch('upgrade_step');
    $upgrade_step = dunserialize($upgrade_step['cachevalue']);
    $file = DZZ_ROOT . './data/update/pichome' . $upgrade_step['version'] . '/updatelist.tmp';
    @unlink($file);
    @unlink(DZZ_ROOT . './install/update.php');
    C::t('cache') -> delete('upgrade_step');
    C::t('cache') -> delete('upgrade_run');
    C::t('setting') -> update('upgrade', '');
    updatecache('setting');
    $old_update_dir = './data/update/';
    $dzz_upgrade -> rmdirs(DZZ_ROOT . $old_update_dir);
	$dzz_upgrade -> check_upgrade();
    // $url = outputurl($_G['siteurl'].MOD_URL.'&op=systemupgrade' );
    // dheader('Location: ' . $url);
}
// include template('upgrade');
?>