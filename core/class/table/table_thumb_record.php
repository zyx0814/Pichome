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
            $this->_pk = 'rid';
            $this->_pre_cache_key = 'thumb_record_';
            //$this->_cache_ttl = 5400;
            
            parent::__construct();
        }
        
        public function insert_data($setarr,$isreturn = false)
        {

            if ($returndata = DB::fetch_first("select * from %t where rid = %s",array($this->_table,$setarr['rid']))) {
                if(!$isreturn)$this->update($setarr['rid'],$setarr);
                return $returndata;
            } else {
				try{

                if (parent::insert($setarr,1,1)) {
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
            if(!is_array($rids)) $rids = (array)$rids;
            foreach (DB::fetch_all("select * from %t where rid in(%n)", array($this->_table, $rids)) as $v) {
               //if($v['sstatus'] == 1) IO::Delete($v['spath']);
               //if($v['lstatus'] == 1) IO::Delete($v['lpath']);
              // if($v['opath']) IO::Delete($v['opath']);
               self::delete($v['rid']);

            }
        }
        
        public function fetch_datas_by_rid($rid){
            return DB::fetch_all("select * from %t  where rid = %s  and thumbstatus = 1",array($this->_table,$rid));
        }
        
        public function fetch_original($rid){
            return DB::fetch_first("select * from %t  where rid = %d  and thumbstatus = 1 and original = 1",array($this->_table,$rid));
        }
        
    }
