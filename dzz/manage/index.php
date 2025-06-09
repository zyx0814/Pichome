<?php
    /*
     * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
     * @license     https://www.oaooa.com/licenses/
     *
     * @link        https://www.oaooa.com
     * @author      zyx(zyx@oaooa.com)
     */
    if (!defined('IN_OAOOA')) {//所有的php文件必须加上此句，防止被外部调用
        exit('Access Denied');
    }
    Hook::listen('adminlogin');
    $navtitle=lang('manage_tool');
    $do = isset($_GET['do']) ? trim($_GET['do']) : '';
    if ($do == 'header'){
        $list=array();
        $list[]=array(
            'id'=>'systeminfo',
            'name'=>lang('system_info'),
            'url'=>'index.php?mod=systeminfo',
        );
        $list[]=array(
            'id'=>'library',
            'name'=>lang('library_manage'),
            'url'=>'index.php?mod=pichome&op=library',
        );
       
        $list[]=array(
            'id'=>'alonepage',
            'name'=>lang('page_manage'),
            'url'=>'index.php?mod=alonepage',
        );
        $list[]=array(
            'id'=>'banner',
            'name'=>lang('banner_manage'),
            'url'=>'index.php?mod=banner&op=admin',
        );
        $list[]=array(
            'id'=>'manage',
            'name'=>lang('manage_tool'),
            'url'=>'index.php?mod=manage',
        );
        exit(json_encode(array('data' => $list)));
    }else{
        $list=array();
        //机构和用户,团队版
        if(defined('PICHOME_LIENCE')){
            $list['orguser']=array(
                'id'=>'orguser',
                'name'=>lang('institution_users'),
                'url'=>'admin.php?mod=orguser',
                'img'=>'data/attachment/appico/201712/21/131016is1wjww2uwvljllw.png'
            );
        }
        //系统设置
        $list['setting']=array(
            'id'=>'setting',
            'name'=>lang('system_config'),
            'url'=>'admin.php?mod=setting',
            'img'=>'data/attachment/appico/201712/21/160754fwfmziiiift3gwsw.png'
        );
        $list['system']=array(
            'id'=>'system',
            'name'=>lang('system_tool'),
            'url'=>'admin.php?mod=system',
            'img'=>'data/attachment/appico/201712/21/160537cikgw2v6s6z4scuv.png'
        );
        $list['systemlog']=array(
            'id'=>'systemlog',
            'name'=>lang('login_log'),
            'url'=>'admin.php?mod=systemlog',
            'img'=>'data/attachment/appico/201712/21/113527zz2665xg7d3h2777.png'
        );
        $list['storagesetting']=array(
            'id'=>'storagesetting',
            'name'=>lang('storage_setting'),
            'url'=>'index.php?mod=pichome&op=storagesetting',
            'img'=>'data/attachment/appico/201712/21/171106u1dk40digrrr79ed.png'
        );
        $list['search']=array(
            'id'=>'search',
            'name'=>lang('search_setting'),
            'url'=>'index.php?mod=search&op=setting',
            'img'=>'data/attachment/appico/201712/21/searchset.png'
        );
        if(defined('PICHOME_LIENCE')){
            $list['fileCollect']=array(
                'id'=>'fileCollect',
                'name'=>lang('collection_manage'),
                'url'=>'index.php?mod=fileCollect&op=setting',
                'img'=>'data/attachment/appico/201712/21/collect.png'
            );
        }
        $appdata=DB::fetch_all("select appid,appname,appico,appurl,app_path,identifier,appadminurl,showadmin from %t where ((`group`=3 and isshow>0) OR appadminurl!='')  and `available`>0 order by appid",array('app_market'));
        foreach($appdata as $k => $v){
            if(!$v['showadmin']) continue;
            if(!defined('PICHOME_LIENCE')) {
                if($v['identifier']=='orguser') continue;
                if($v['identifier']=='fileCollect') continue;
            }
            if ($v['appico'] != 'dzz/images/default/icodefault.png' && !preg_match("/^(http|ftp|https|mms)\:\/\/(.+?)/i", $v['appico'])) {
                $v['appico'] = $_G['setting']['attachurl'] . $v['appico'];
            }
            $v['name']=$v['appname'];
            $v['img']= $v['appico'];
            $v['url']=$v['appadminurl']?replace_canshu($v['appadminurl']):replace_canshu($v['appurl']);
    
            if('appname'!=lang('appname', array(),null,($v['app_path']?$v['app_path']:'dzz').'/'.$v['identifier'])) {
                $v['name']=lang('appname', array(),null,($v['app_path']?$v['app_path']:'dzz').'/'.$v['identifier']);
            }
    
            if(empty($list[$v['identifier']]))   $list[$v['identifier']]=$v;
    
        }

        $list_json=json_encode(array_values($list));
        include template('page/index');
    }
	
    
    