<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
class table_pichome_folder_autodata extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'pichome_folder_autodata';
        $this->_pk = 'id';
        $this->_pre_cache_key = 'pichome_folder_autodata';
        $this->_cache_ttl = 3600;
        parent::__construct();
    }
    public function addData($setarr){
        if(!$folder = C::t('pichome_folder')->fetch($setarr['fid'])) return false;

        $setarr['appid'] = $folder['appid'];
        $appfileds = C::t('pichome_vapp')->fetch_fileds_by_appid($folder['appid']);
        $olddatas = $this->fetch_by_fid($setarr['fid']);
        $oldkeys = array_keys($olddatas);
        $nkeys = array_keys($setarr['keys']);
        $delkeys = array_diff($oldkeys,$nkeys);
        $haschange = false;
        if(!empty($delkeys)){
            foreach($delkeys as $v){
                $id = DB::result_first("select id from %t where fid=%s and skey=%s",array($this->_table,$setarr['fid'],$v));
                if($id && parent::delete($id)){
                    C::t('task_record')->delete_by_idtype_idvalue(0,$setarr['fid']);
                }
            }
            $haschange = true;
        }

        foreach($setarr['keys'] as $k=>$v){
            if(!in_array($k,$appfileds)) continue;
            if($id = DB::result_first("select id from %t where fid=%s and skey=%s",array($this->_table,$setarr['fid'],$k))){
                if(parent::update($id,['svalue'=>$v])) $haschange = true;
            }else{
                $tmparr = ['fid'=>$setarr['fid'],'appid'=>$setarr['appid'],'skey'=>$k,'svalue'=>$v];
                 if(parent::insert($tmparr)) $haschange = true;
            }
        }

        if($haschange){
            $tsetarr = [
                'idtype'=>0,
                'idvalue'=>$setarr['fid'],
                'dateline'=>TIMESTAMP,
                'donum'=>0,
                'totalnum'=>0,
                'lastid'=>'',
                'lastdate'=>0,
                'appid'=>$setarr['appid']

            ];
            C::t('task_record')->addData($tsetarr);
        }
        return true;

    }
    public function fetch_by_fid($fid){
        $datas = [];
        foreach(DB::fetch_all("select * from %t where fid=%s",[$this->_table,$fid]) as $v){
            $datas[$v['skey']] = $v['svalue'];
        }
        return $datas;
    }
    //获取需要变更的属性
    public function fetch_attrs_by_fid($fid){
        $folderdata = C::t('pichome_folder')->fetch($fid);
        $pathkey = $folderdata['pathkey'];
        $appid = $folderdata['appid'];
        $allowflags = C::t('pichome_vapp')->fetch_fileds_by_appid($appid);
        $fids = C::t('pichome_folder')->splitPathkeyToFids($pathkey);
        $attrs = [];
        //按照pathkey上下级查询，以实现下级覆盖上级（多值字段不覆盖）
        foreach(DB::fetch_all("select a.*,f.pathkey from %t a left join %t f on a.fid = f.fid where a.fid in(%n)
        order by CHAR_LENGTH(f.pathkey) ASC",[$this->_table,'pichome_folder',$fids]) as $v){
            //判断当前字段是否开启，未开启则跳过
            if(!in_array($v['skey'],$allowflags)) continue;
            //处理标签和专辑字段
            if($v['skey'] == 'tag' || strpos($v['skey'],'tabgroup_') === 0){
                $svalue = $v['svalue'] ? explode(',',$v['svalue']):[];
                if(!isset($attrs[$v['skey']])) $attrs[$v['skey']] = $svalue;
                else $attrs[$v['skey']] = array_merge($attrs[$v['skey']],$svalue);
            }
        }
        return $attrs;
    }

    public function delete_by_fid($fid){
        foreach(DB::fetch_all("select id from %t where fid=%s",[$this->_table,$fid]) as $v){
            if(parent::delete($v['id'])){
                C::t('task_record')->delete_by_idtype_idvalue(0,$fid);
            }
        }
    }


}