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

class table_stats_token extends dzz_table
{
    public function __construct() {

        $this->_table = 'stats_token';
        $this->_pk    = 'id';
        $this->_pre_cache_key = 'stats_token_';
        $this->_cache_ttl =0;
        parent::__construct();
    }
    //增加文件数据数据
    public function insertData($setarr){
        return parent::insert($setarr);
    }


}