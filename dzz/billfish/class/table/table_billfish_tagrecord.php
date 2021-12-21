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

class table_billfish_tagrecord extends dzz_table
{
    public function __construct() {
        $this->_table = 'billfish_tagrecord';
        $this->_pk    = 'id';
        parent::__construct();
    }

    public function insert_data($setarr,$appid){
        if($id = DB::result_first("select id from %t where lid=%d and appid = %s",array($this->_table,$setarr['lid'],$appid))){
           parent::update($id,array('name'=>$setarr['name']));
        }else{
            $setarr['appid'] = $appid;
            parent::insert($setarr);
        }
        if($tid= DB::result_first("select tid from %t where tagname = %s",array('pichome_tag',$setarr['name']))){
            return array('lid'=>$setarr['lid'],'tid'=>$tid);
        }else{
            $tid = C::t('pichome_tag')->insert($setarr['name']);
        }
        return array('tid'=>$tid,'lid'=>$setarr['lid']);
    }
    public function delete_by_appid($appid){
        DB::delete($this->_table,array('appid'=>$appid));
    }
}

