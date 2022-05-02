<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 *
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */
if(!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_local_record extends dzz_table
{
    public function __construct() {
        $this->_table = 'local_record';
        $this->_pk    = 'id';
        parent::__construct();
    }

    public function insert_data($setarr){
        $editdateline = $setarr['editdate'];
        $path = $setarr['path'];
        unset($setarr['editdate']);
        unset($setarr['path']);
        if(C::t('pichome_resources')->insert($setarr)){
            $setarr1['rid'] = $setarr['rid'];
            $setarr1['appid'] = $setarr['appid'];
            $setarr1['path'] = $path;
            $setarr1['dateline'] = $editdateline;
            $id = md5($path.$setarr['appid']);
            if(DB::result_first("select id from %t where  id = %s",array($this->_table,$id))){
                parent::update($id,$setarr1);
            }else{
                $setarr1['id'] = $id;
                parent::insert($setarr1);
            }
        }
        return true;

    }

    public function insert($setarr){
        $id = md5($setarr['path'].$setarr['appid']);
        if(DB::result_first("select id from %t where  id = %s",array($this->_table,$id))){
            parent::update($id,$setarr);
        }else{
            $setarr['id'] = $id;
            parent::insert($setarr);
        }
        return true;
    }
    public function delete_by_rids($rids){
        if (!is_array($rids)) $rids = (array)$rids;
        DB::delete($this->_table,'rid in ('.dimplode($rids).')');
    }
    public function delete_by_appid($appid){
        DB::delete($this->_table,array('appid'=>$appid));
    }

}

