<?php
/* @authorcode  codestrings
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 *
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
input:单行文本；textarea:多行文本；select:单选；multiselect:多选；date:日期类型；user:用户选择
 */

if(!defined('IN_OAOOA')) {
    exit('Access Denied');
}
class table_form_setting_filedcat extends dzz_table
{
    public function __construct() {

        $this->_table = 'form_setting_filedcat';
        $this->_pk    = 'id';
        parent::__construct();
    }

    public function fetch_by_id($id){
        $data=parent::fetch($id);
        return $data;
    }

    public function fetch_all_by_tabgroupid($tabgroupid){
        $data=array();
        foreach(DB::fetch_all("select * from %t where tabgroupid = %d order by disp",array($this->_table,$tabgroupid)) as $value){
            $data[$value['id']]=$value;
        }
        return $data;
    }
    public function delete_by_id($id,$field){
        if(parent::fetch($id)){
            parent::delete($id);
            $data = DB::fetch_all("select * from %t where catid=%d",array('form_setting',$id));
            if($field){
                foreach($data as $value){
                    C::t('form_setting')->delete_by_flag($value['flag']);
                }
            }else{
                foreach($data as $value){
                    C::t('form_setting')->update($value['flag'],array('catid'=>0));
                }
            }
            return true;
        }
        return false;
    }
}