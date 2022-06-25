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
    
    class table_thumb_record extends dzz_table
    {
        public function __construct()
        {
            
            $this->_table = 'thumb_record';
            $this->_pk = 'id';
            $this->_pre_cache_key = 'thumb_record_';
            $this->_cache_ttl = 5400;
            
            parent::__construct();
        }
        
        public function insert($setarr,$isreturn = false)
        {

            $id = md5($setarr['rid'] . '_' . intval($setarr['width']) . '_' . intval($setarr['height'])
                . '_' . intval($setarr['waterstatus']) . '_' . intval($setarr['waterstatus']) . '_' . intval($setarr['thumbtype']) . '_' . intval($setarr['thumbsign']));
            $setarr['id'] = $id;
            if ($returndata = DB::fetch_first("select * from %t where id = %s",array($this->_table,$id))) {
                if(!$isreturn)$this->update($id,$setarr);
                return $returndata;
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
        public function delete($id){
            if(parent::delete($id)){
                $this->clear_cache('r_'.$id);
            }
            return true;
        }
        public function update($id,$setarr){
            if(parent::update($id,$setarr)){
                $this->clear_cache('r_'.$id);
            }
            return true;
        }
        public function fetch_by_id($id)
        {
            $data = [];
            if($data = $this->fetch_cache('r_'.$id)) return $data;
            if ($data = parent::fetch($id)) {
                if ($data['thumbstatus'] == 1) {
                    $data['thumbimg'] = IO::getFileUri($data['path']);
                }
            }
             $this->store_cache('r_'.$id,$data);
            return $data;
        }
        
        public function fetch_all($ids)
        {
            $rdata = [];
            if (!is_array($ids)) $ids = (array)$ids;
            foreach($ids as $v){
                $rdata[] = self::fetch_by_id($v);
            }
            return $rdata;
        }
        
        public function delete_by_rid($rids)
        {
            foreach (DB::fetch_all("select path,id from %t where rid in(%n)", array($this->_table, $rids)) as $v) {
               if($v['thumbstatus'] == 1) IO::Delete($v['path']);
               self::delete($v['id']);

            }
        }
        
        public function fetch_datas_by_rid($rid){
            return DB::fetch_all("select * from %t  where rid = %s  and thumbstatus = 1",array($this->_table,$rid));
        }
        
        public function fetch_original($rid){
            return DB::fetch_first("select * from %t  where rid = %d  and thumbstatus = 1 and original = 1",array($this->_table,$rid));
        }
        
    }
