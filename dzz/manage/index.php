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
    $navtitle="管理工具";
	$list=array();
    //机构和用户,团队版
    if(defined('PICHOME_LIENCE')){
        $list[]=array(
            'name'=>'机构用户',
            'url'=>'admin.php?mod=orguser',
            'img'=>'data/attachment/appico/201712/21/131016is1wjww2uwvljllw.png'
        );
    }
    //系统设置
    $list['setting']=array(
        'name'=>'系统设置',
        'url'=>'admin.php?mod=setting',
        'img'=>'data/attachment/appico/201712/21/160754fwfmziiiift3gwsw.png'
    );
    $list['system']=array(
        'name'=>'系统工具',
        'url'=>'admin.php?mod=system',
        'img'=>'data/attachment/appico/201712/21/160537cikgw2v6s6z4scuv.png'
    );
    $list['systemlog']=array(
        'name'=>'登录日志',
        'url'=>'admin.php?mod=systemlog',
        'img'=>'data/attachment/appico/201712/21/113527zz2665xg7d3h2777.png'
    );
    $list['storagesetting']=array(
        'name'=>'存储位置',
        'url'=>'index.php?mod=pichome&op=storagesetting',
        'img'=>'data/attachment/appico/201712/21/171106u1dk40digrrr79ed.png'
    );
    $list['search']=array(
        'name'=>'搜索设置',
        'url'=>'index.php?mod=search&op=setting',
        'img'=>'data/attachment/appico/201712/21/searchset.png'
    );

    $appdata=DB::fetch_all("select appid,appname,appico,appurl,identifier,appadminurl,showadmin from %t where ((`group`=3 and isshow>0) OR appadminurl!='')  and `available`>0 order by appid",array('app_market'));

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
        $list[$v['identifier']]=$v;
    }

    $list_json=json_encode(array_values($list));
    include template('page/index');
    
    