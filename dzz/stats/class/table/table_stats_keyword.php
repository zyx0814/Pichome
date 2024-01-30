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

class table_stats_keyword extends dzz_table
{
    public function __construct() {

        $this->_table = 'stats_keyword';
        $this->_pk    = 'id';
        $this->_pre_cache_key = 'stats_keyword_';
        $this->_cache_ttl =0;
        parent::__construct();
    }
    //增加文件数据数据
    public function insert_data($setarr,$repeattime=300){
        if($repeattime){
            $processname = 'PICHOMESTATSKEYWORD_'.md5($setarr['keyword'],$setarr['uid'].$setarr['idtype'].$setarr['idval']);
            if(dzz_process::islocked($processname, $repeattime)){
                return true;
            }
        }

        //如果插入数据成功
        if(parent::insert($setarr,1)){
            //执行出入数据后挂载点
            Hook::listen('statskeywordaddafter',$setarr);
        }

    }


}