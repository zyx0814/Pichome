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

class table_stats_userlogin extends dzz_table
{
    public function __construct() {

        $this->_table = 'stats_userlogin';
        $this->_pk    = 'id';
        $this->_pre_cache_key = 'stats_userlogin_';
        $this->_cache_ttl =0;
        parent::__construct();
    }
    //增加文件数据数据
    public function insert_data($setarr){
        global $_G;
        //如果不是下载，增加统计锁定
        $setarr['machine'] = get_os();
        $setarr['ip'] = $_G['clientip'];
        //如果插入数据成功
        return  parent::insert($setarr,1);



    }


}