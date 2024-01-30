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

class table_thumb_cache extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'thumb_cache';
        $this->_pk = 'id';
        $this->_pre_cache_key = 'thumb_cache_';
        $this->_cache_ttl = 5400;

        parent::__construct();
    }
    public function insertdata($setarr){
        //组合图片唯一id
       if(isset($setarr['thumbsign']) && $setarr['thumbsign'] == 'original'){
           $id = md5($setarr['aid'].$setarr['thumbtype'].$setarr['watermd5']);
       }else{
           $id = md5($setarr['width'].$setarr['height'].$setarr['aid'].$setarr['thumbtype'].$setarr['watermd5']);
       }
        $insertarr = [
            'id'=>$id,
            'width'=>$setarr['width'],
            'height'=>$setarr['height'],
            'aid'=>$setarr['aid'],
            'remoteid'=>$setarr['remoteid'],
            'path'=>$setarr['path'],
            'dateline'=>TIMESTAMP
        ];

        if(parent::fetch($id)){
            parent::update($id,$insertarr);
            return $id;
        }
        if(parent::insert($insertarr)){
            return $id;
        }else{
            return false;
        }
    }

    public function fetch_data_by_thumbparam($thumbarr){
        //组合图片唯一id
        if(!$thumbarr['width'] || !$thumbarr['height']){
            $id = md5($thumbarr['aid'].$thumbarr['thumbtype'].$thumbarr['watermd5']);
        }
        else $id = md5($thumbarr['width'].$thumbarr['height'].$thumbarr['aid'].$thumbarr['thumbtype'].$thumbarr['watermd5']);
        return parent::fetch($id);
    }
    public function insert($setarr,$isreturn = false)
    {
        $aid = $setarr['aid'];
        if ($returndata = DB::fetch_first("select * from %t where aid = %d",array($this->_table,$aid))) {
            if(!$isreturn)$this->update($aid,$setarr);
            return array_merge($returndata,$setarr);
        } else {
            try{
                if (parent::insert($setarr)) {
                    return $setarr;
                }
            }catch(\Exception $e){
                return array('error'=>$e->getMessage());
            }
        }
    }
    public function insert_data($setarr,$isreturn = false){
        $aid = $setarr['aid'];
        if ($returndata = DB::fetch_first("select * from %t where aid = %d",array($this->_table,$aid))) {
            if(!$isreturn)$this->update($aid,$setarr);
            return array_merge($returndata,$setarr);
        } else {
            try{
                if (parent::insert($setarr)) {
                    return $setarr;
                }
            }catch(\Exception $e){
                return array('error'=>$e->getMessage());
            }
        }
    }
    public function delete($aid){
        if(parent::delete($aid)){
            $this->clear_cache('r_'.$aid);
        }
        return true;
    }
    public function update($aid,$setarr){
        if(parent::update($aid,$setarr)){
            $this->clear_cache('r_'.$aid);
        }
        return true;
    }
    public function fetch_by_aid($aid)
    {
        $data = [];
        if($data = $this->fetch_cache('r_'.$aid)) return $data;
        if ($data = parent::fetch($aid)) {
            if ($data['thumbstatus'] == 1) {
                $data['thumbimg'] = IO::getFileUri($data['path']);
            }
        }
        $this->store_cache('r_'.$aid,$data);
        return $data;
    }

    public function fetch_all($aids)
    {
        $rdata = [];
        if (!is_array($aids)) $aids = (array)$aids;
        foreach($aids as $v){
            $rdata[] = self::fetch_by_aid($v);
        }
        return $rdata;
    }

    public function delete_by_aid($aids)
    {
        if(!is_array($aids)) $aids = (array)$aids;
        foreach (DB::fetch_all("select id,path,aid,remoteid from %t where aid in(%n)", array($this->_table, $aids)) as $v) {
            $bz = io_remote::getBzByRemoteid($v['remoteid']);
            IO::Delete($bz.$v['path']);
            parent::delete($v['id']);

        }
    }

    public function fetch_datas_by_aid($aid){
        return DB::fetch_all("select * from %t  where aid = %d  and thumbstatus = 1",array($this->_table,$aid));
    }

    public function fetch_path_by_aid($aid){
        return DB::fetch_first("select path from %t  where aid = %d  and thumbstatus = 1 ",array($this->_table,$aid));
    }

}
