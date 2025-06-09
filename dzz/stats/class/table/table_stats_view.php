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

class table_stats_view extends dzz_table
{
    public function __construct() {

        $this->_table = 'stats_view';
        $this->_pk    = 'id';
        $this->_pre_cache_key = 'stats_view_';
        $this->_cache_ttl =0;
        parent::__construct();
    }
    //增加文件数据数据
    public function insert_data($setarr,$repeattime=300){
        global $_G;
        //如果不是下载，增加统计锁定
        if($setarr['idtype'] != 1 && $repeattime){
            $processname = 'PICHOMESTATSVIEW_'.md5($setarr['uid'].$setarr['idtype'].$setarr['idval']);
            if(dzz_process::islocked($processname, $repeattime)){
                return true;
            }
        }
        $setarr['ip'] = $_G['clientip'];
        //如果插入数据成功
        if(parent::insert($setarr,1)){
            //执行插入数据后挂载点
            Hook::listen('statsviewaddafter',$setarr);
        }

    }


}