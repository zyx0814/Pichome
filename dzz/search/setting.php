<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2018/11/28
 * Time: 14:32
 */
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
Hook::listen('adminlogin');
$appname=lang('appname');
$navtitle="设置";
include libfile('function/cache');
$do=$_GET['do'];
if($do=='create'){

	$title=getstr($_GET['title']);
	if(empty($title)){
		exit(json_encode(array('success'=>false,'msg'=>'模板名称不能为空')));
	}
	$setarr=array(
		'title'=>$title,
		'exts'=>$_GET['exts'],
		'searchRange'=>implode(',',$_GET['searchRange']),
		'dateline'=>TIMESTAMP,
		'disp'=>intval($_GET['disp']),
		'pageSetting'=>'{"layout":"waterFall","other":"btime","sort":"btime","desc":"desc","opentype":"current","filterstyle":"0"}',
		'screen'=>'[{"key":"color","label":"\u989c\u8272"},{"key":"link","label":"\u94fe\u63a5"},{"key":"desc","label":"\u6ce8\u91ca"},{"key":"duration","label":"\u65f6\u957f"},{"key":"size","label":"\u5c3a\u5bf8"},{"key":"ext","label":"\u7c7b\u578b"},{"key":"shape","label":"\u5f62\u72b6"},{"key":"grade","label":"\u8bc4\u5206"},{"key":"btime","label":"\u6dfb\u52a0\u65f6\u95f4"},{"key":"dateline","label":"\u4fee\u6539\u65e5\u671f"},{"key":"mtime","label":"\u521b\u5efa\u65e5\u671f"},{"key":"level","label":"\u5bc6\u7ea7"},{"key":"tag","label":"\u6807\u7b7e","group":"","auto":"0","sort":"hot"}]'
	);
	if($setarr['tid']=DB::insert('search_template',$setarr,1)){
		exit(json_encode(array('success'=>true,'data'=>$setarr)));
	}else{
		exit(json_encode(array('success'=>false,'msg'=>'模板创建失败')));
	}
	
}elseif($do=='delete'){
	$tid=intval($_GET['tid']);
	if(DB::delete('search_template',array('tid'=>$tid))){
		exit(json_encode(array('success'=>true)));
	}else{
		exit(json_encode(array('success'=>false,'msg'=>'删除失败')));
	}
}elseif($do=='fetch'){
	$tid = isset($_GET['tid']) ? trim($_GET['tid']) : '';

    if (submitcheck('settingsubmit')) {
        if ($_G['adminid'] != 1) return array('success' => false, 'msg' => lang('no_perm'));
        if (!$tid) exit(json_encode(array('success' => false, 'msg' => 'tid is must')));
        foreach ($_GET['screen'] as $k => $v) {
			if (!defined('PICHOME_LIENCE') && $v['key'] == 'level') {
				unset($_GET['screen'][$k]);
			}
        }
        $setarr = [
            'title' => isset($_GET['title']) ? trim($_GET['title']) : '',
			'disp'=>intval($_GET['disp']),
		    'exts'=>htmlspecialchars($_GET['exts']),
		    'searchRange'=>implode(',',$_GET['searchRange']),
			'screen' => json_encode(isset($_GET['screen']) ? $_GET['screen'] : []),
			'pagesetting' => json_encode(isset($_GET['pagesetting']) ? ($_GET['pagesetting']) : []),
			'disp'=>intval($_GET['disp'])
        ];
		DB::update('search_template',$setarr,array('tid'=>$tid));
        exit(json_encode(array('success' => true)));
    } else {
        require_once(DZZ_ROOT . './dzz/class/class_encode.php');
        if ($data = DB::fetch_first("select * from %t where tid=%s ", array('search_template', $tid))) {
        
			if($data['screen']){
				$data['screen']=json_decode($data['screen'],true);
			}else{
				$data['screen'] = [];
			}
			if($data['pagesetting']){
				$data['pagesetting']=json_decode($data['pagesetting'],true);
			}else{
				$data['pagesetting'] = [];
			}
			if($data['searchRange']){
				$data['searchRange']=explode(',',$data['searchRange']);
			}else{
				$data['searchRange'] = [];
			}
            //如果没有设置库筛选项，使用系统默认筛选项作为库筛选项
            $data['filter'] = [
               /* [
                    'key' => 'classify',
                    'label' => '分类',
                    'checked' => 1
                ],*/
                [
                    'key' => 'tag',
                    'label' => '标签',
                    'checked' => 1
                ],
                [
                    'key' => 'color',
                    'label' => '颜色',
                    'checked' => 1
                ],
                [
                    'key' => 'link',
                    'label' => '链接',
                    'checked' => 1
                ],
                [
                    'key' => 'desc',
                    'label' => '注释',
                    'checked' => 1
                ],
                [
                    'key' => 'duration',
                    'label' => '时长',
                    'checked' => 1
                ],
                [
                    'key' => 'size',
                    'label' => '尺寸',
                    'checked' => 1
                ],
                [
                    'key' => 'ext',
                    'label' => '类型',
                    'checked' => 1
                ],
                [
                    'key' => 'shape',
                    'label' => '形状',
                    'checked' => 1
                ],
                [
                    'key' => 'grade',
                    'label' => '评分',
                    'checked' => 1
                ],
                [
                    'key' => 'btime',
                    'label' => '添加时间',
                    'checked' => 1
                ],
                [
                    'key' => 'dateline',
                    'label' => '修改日期',
                    'checked' => 1
                ],
                [
                    'key' => 'mtime',
                    'label' => '创建日期',
                    'checked' => 1
                ]

            ];
            if(defined('PICHOME_LIENCE')){
                $data['filter'][] = [
                    'key' => 'level',
                    'label' => '密级',
                    'checked' => 1
                ];
            }
            $kus=array();
            foreach(DB::fetch_all("select * from %t where isdelete < 1",['pichome_vapp']) as $v){
				if ($v['type'] != 3 && !IO::checkfileexists($v['path'],1)) {
					continue;
				}
				$kus[$v['appid']] = array('id'=>$v['appid'],'name'=>$v['appname']);
			}
			$data['kus']=$kus;
            exit(json_encode(array('success' => true, 'data' => $data)));
        } else {
            exit(json_encode(array('error' => true)));
        }
    }

}elseif($do == 'gettagcat'){//获取标签分类
    exit(json_encode(['success'=>true,'data'=>[]]));
}elseif($do == 'gettag'){//获取标签
   
    $keyword = isset($_GET['keyword']) ? getstr($_GET['keyword']):'';

    $params = ['pichome_tag'];
    $sql = "select tid,tagname from %t ";
    $wheresql = " 1 ";
   
    
    if($keyword){
        $wheresql .= " and tagname like %s ";
        $params[] = '%'.$keyword.'%';
    }
    $perpage = isset($_GET['perpage']) ? intval($_GET['perpage']):50;
    $page = isset($_GET['page']) ? intval($_GET['page']):1;
    $start = ($page - 1) * $perpage;
    $limitsql = "limit $start," . $perpage;
   
    foreach(DB::fetch_all(" $sql where $wheresql order by hots desc $limitsql",$params) as $tv){
        $datas['tag'][] = ['tid'=>$tv['tid'],'tagname'=>$tv['tagname']];
        $tmpnum += 1;
    }
    if($tmpnum >= $perpage) $datas['next'] = true;
    exit(json_encode($datas));

}else{
	$data=array();
	$apps=array();
	$kus=array();
	foreach(DB::fetch_all("select * from %t where isdelete < 1",['pichome_vapp']) as $v){
		$apps[$v['appid']]=$v;
		if ($v['type'] != 3 && !IO::checkfileexists($v['path'],1)) {
			continue;
		}
		$kus[$v['appid']] = array('id'=>$v['appid'],'name'=>$v['appname']);
	}
	foreach(DB::fetch_all("select * from %t where 1 order by disp,dateline desc",array('search_template')) as $value){
		$value['fdateline']=dgmdate($value['dateline'],'Y-m-d H:i:s');
		if($value['screen']){
			$value['screen']=json_decode($value['screen'],true);
		}else{
			$value['screen']=array();
		}
		if($value['pagesetting']){
			$value['pagesetting']=json_decode($value['pagesetting'],true);
			if($value['pagesetting']['layout']) $value['layout']=$value['pagesetting']['layout'];
			else{
				$value['layout']='waterFall';
			}
		}else{
			$value['pagesetting']=array();
			$value['layout']='waterFall';
		}
		$value['flayout']=lang('layout_'.$value['layout']);
		if($value['searchRange']){
			$appids=explode(',',$value['searchRange']);
			$appnames=array();
			foreach($appids as $appid){
				if(isset($apps[$appid])){
					$appnames[]=$apps[$appid]['appname'];
				}
			}
			$value['searchRange_names']=implode(',',$appnames);
			
		}else{
			$value['searchRange']=array();
			$value['searchRange_names']='所有库';
		}
		
		$data[]=$value;
	}
	if($data){
		$data_json=json_encode($data);
	}else{
		$data_json='[]';
	}
	
	if($kus){
		$kus_json=json_encode($kus);
	}else{
		$kus_json='[]';
	}
	
	include template('setting/pc/page/main');
	exit();
}