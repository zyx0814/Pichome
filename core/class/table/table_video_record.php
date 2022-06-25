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

    public function insert($setarr)
    {
        if (empty($setarr)) return false;
        if (!$setarr['waterstatus']) $setarr['waterstatus'] = 0;
        if (!$setarr['videoquality']) $setarr['videoquality'] = 0;
        if (!$videodata = DB::fetch_first("select * from %t where rid=%s  and  videoquality=%d and  waterstatus = %d  and format=%s ",
            array('video_record', $setarr['rid'], $setarr['videoquality'], $setarr['waterstatus'], $setarr['format']))) {
            $setarr['id'] = parent::insert($setarr, 1);
        } else {
            parent::update($videodata['id'],$setarr);
            $setarr['id']= $videodata['id'];
        }
        return $setarr;
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

}

