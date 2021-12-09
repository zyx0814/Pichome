<?php
    /*
     * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
     * @license     https://www.oaooa.com/licenses/
     *
     * @link        https://www.oaooa.com
     * @author      zyx(zyx@oaooa.com)
     */
//此页的调用地址  index.php?mod=test;
//同目录的其他php文件调用  index.php?mod=test&op=test1;
    if (!defined('IN_OAOOA')) {//所有的php文件必须加上此句，防止被外部调用
        exit('Access Denied');
    }
    $ismobile = helper_browser::ismobile();
    
    $overt = getglobal('setting/overt');
    if(!$overt && !$overt = C::t('setting')->fetch('overt')){
        Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
    }

//主题
    $theme = GetThemeColor();
//主题
    $apps = [];
    foreach(DB::fetch_all("select * from %t where isdelete = 0",array('pichome_vapp')) as $v){
        $v['path'] = urlencode($v['path']);
        $v['leaf'] = DB::result_first("select count(*) from %t where appid = %s",array('pichome_folder',$v['appid'])) ? false:true;
        $hascatnum = DB::result_first("SELECT count(DISTINCT rid) FROM %t where appid = %s",array('pichome_folderresources',$v['appid']));
        $v['nosubfilenum'] = $v['filenum'] - $hascatnum;
        $apps[] = $v;
    }
    $apps = json_encode($apps);

	$ImageExpanded = C::t('user_setting')->fetch_by_skey('pichomeimageexpanded',$uid);
//筛选
    $screen = C::t('user_setting')->fetch_by_skey('pichomeuserscreen',$uid);
    $screen = $screen?intval($screen):0;
    
    $setting = $_G['setting'];
	
    $pagesetting = $setting['pichomepagesetting'] ? $setting['pichomepagesetting'] : [];
    $pichomesortfileds = C::t('user_setting')->fetch_by_skey('pichomesortfileds',$_G['uid']);
    $pichomeshowfileds = C::t('user_setting')->fetch_by_skey('pichomeshowfileds',$_G['uid']);
    $pichomelayout = C::t('user_setting')->fetch_by_skey('pichomelayout',$_G['uid']);
    if($pichomesortfileds){
        $sortdatarr = unserialize($pichomesortfileds);
        $sortfilearr = ['btime'=>1,'mtime'=>2,'dateline'=>3,'name'=>4,'size'=>5,'grade'=>6,'duration'=>7,'whsize'=>8];
        $pagesetting['sort'] = $sortfilearr[$sortdatarr['filed']];
        $pagesetting['desc'] = $sortdatarr['sort'];
    }
    if($pichomelayout){
        $layout = unserialize($pichomelayout);
        $pagesetting['layout'] = $layout['layout'];
    }
    if($pichomeshowfileds){
        $pichomeshowfileds = unserialize($pichomeshowfileds);
        $pagesetting['show'] = $pichomeshowfileds['filed'];
        $pagesetting['other'] = $pichomeshowfileds['other'];
    }
	$template = 1;
	if($pagesetting['template']){
		$template = $pagesetting['template'];
	}
    $pagesetting = json_encode($pagesetting);
	if ($ismobile) {
	    include template('mobile/page/index');
	} else {
		include template('pc/page/index'.$template);
	}
    