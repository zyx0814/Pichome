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

class table_ffmpegimage_cache extends dzz_table
{
    public function __construct() {

        $this->_table = 'ffmpegimage_cache';
        $this->_pk    = 'id';

        parent::__construct();
    }
    public function insert($setarr){
        $path = trim($setarr['path']);
        if(!$path) return false;
        if($id = DB::result_first("select id from %t where path = %s",array($this->_table,$path))){
            return parent::update($id,$setarr);
        }else{
            return parent::insert($setarr,1);
        }
    }
    public function fetch_by_path($path){
        $path = trim($path);
        return DB::fetch_first("select * from %t where path = %s",array($this->_table,$path));
    }

    public function delete_by_path($path){
        if(!is_array($path)) $path = (array)$path;
        foreach(DB::fetch_all("select id from %t where path in(%n)",array($this->_table,$path)) as $v){
            parent::delete($v['id']);
        }

    }

}
