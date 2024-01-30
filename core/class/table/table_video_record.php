<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 *
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */

if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_video_record extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'video_record';
        $this->_pk = 'id';

        parent::__construct();
    }


    public function insert_data($setarr){
        if($recodedata = DB::fetch_first("select * from %t where rid = %s and aid = %d",array($this->_table,$setarr['rid'],$setarr['aid']))){
            return $recodedata;
        }else{
            if($id = parent::insert($setarr)){
                $setarr['id'] = $id;
                $setarr['status'] = 0;
                return $setarr;
            }else{
                return false;
            }
        }
    }
    public function fetch_by_rid($rid)
    {
        if ($returndata = DB::fetch_first("select * from %t  where rid = %s order by `status` desc", array($this->_table, $rid))) {
            return $returndata;
        } else {
           /* $resourcesdata = C::t('pichome_resources')->fetch_data_by_rid($rid);
             $hookdata = ['appid'=>$resourcesdata['appid'],'rid'=>$rid,'ext'=>$resourcesdata['ext'],'isforce'=>1,'realpath'=>$resourcesdata['realpath']];

            $return = Hook::listen('pichomeconvert',$hookdata,null,false,true);*/
            return false;

        }

    }

    public function delete_by_rid($rids){
        foreach (DB::fetch_all("select path,id,status from %t where rid in(%n)", array($this->_table, $rids)) as $v) {
            if($v['status'] == 2) {
                IO::Delete($v['path']);
            }
            self::delete($v['id']);

        }
    }

    public function delete_by_aid($aids){
        if(!is_array($aids)) $aids = (array)$aids;
        foreach (DB::fetch_all("select path,id,status from %t where aid in(%n)", array($this->_table, $aids)) as $v) {
            if($v['status'] == 2) {
                IO::Delete($v['path']);
            }
            self::delete($v['id']);
        }
    }

}

