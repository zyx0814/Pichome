<?php
/* @authorcode  codestrings
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 *
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 * input:单行文本；textarea:多行文本；select:单选；multiselect:多选；date:日期类型；user:用户选择
 */

if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_form_setting_filedcat extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'form_setting_filedcat';
        $this->_pk = 'id';
        parent::__construct();
    }

    public function fetch_by_id($id)
    {
        $data = parent::fetch($id);
        return $data;
    }

    public function updateById($ids, $setarr)
    {
        if (!is_array($ids)) $ids = (array)$ids;
        Hook::listen('lang_parse', $setarr, ['saveFiledcatLangData', $ids]);
        if ($setarr) parent::update($ids, $setarr);
        return true;

    }

    public function fetch_all_by_tabgroupid($tabgroupid)
    {
        $data = array();
        foreach (DB::fetch_all("select * from %t where tabgroupid = %d order by disp", array($this->_table, $tabgroupid)) as $value) {
            $data[$value['id']] = $value;
        }
        Hook::listen("lang_parse", $data, ['getFiledcatLangKey', 1]);
        Hook::listen("lang_parse", $data, ['getFiledcatLangData', 1]);
        return $data;
    }
    public function copyByTabgroupid($tabgroupid,$ngid)
    {
        $returndata = [];
        foreach (DB::fetch_all("select * from %t where tabgroupid = %d order by disp", array($this->_table, $tabgroupid)) as $value) {
            $value['tabgroupid'] = $ngid;
            $id = $value['id'];
            unset($value['id']);
            $fileds = [];
            if($filedcatid = parent::insert($value, 1)){
                foreach(DB::fetch_all("select * from %t where filedcat = %d and tabgroupid = %d", array('form_setting', $id,$tabgroupid)) as $v){
                    $v['filedcat'] = $filedcatid;
                    $flag = $v['flag'];
                    unset($v['flag']);
                    unset($v['id']);
                    $v['gid'] = $v['tabgroupid']= $ngid;
                    $nflag = '';
                    $filedata = C::t('form_setting')->insert_by_flag($nflag,$v);
                    if($filedata['flag']) $fileds[] = ['flag'=>$flag,'nflag'=>$filedata['flag']];
                }
                $returndata[] = ['id'=>$id,'nid'=>$filedcatid,'fileds'=>$fileds];
            }
        }
        return $returndata;
    }

    public function delByTabgroupid($tabgroupid)
    {
        global $_G;
        $filedcatids = [];
        //查询字段分类
        foreach (DB::fetch_all("select * from %t where tabgroupid = %d", array($this->_table, $tabgroupid)) as $v) {
            //查询分类下字段
            $catfiled = [];
            foreach (DB::fetch_all("select flag from %t where filedcat = %d and tabgroupid > 0 and `type` != %s and isdel < 1", array('form_setting', $v['id'], 'tabgroup')) as $v) {
                $catfiled[] = $v['flag'];
            }
            foreach ($catfiled as $k => $val) {
                //删除字段
                if (C::t('form_setting')->delete_filed_by_flag($val)) {
                    unset($catfiled[$k]);
                    dfsockopen($_G['localurl'] . 'misc.php?mod=delfiled&flag=' . $val, 0, '', '', false, '', 1);
                }
            }
            if (!empty($catfiled)) {
                $filedcatids[] = $v['id'];
            } else {
                if(!$this->delete($v['id'])){
                    $filedcatids[] = $v['id'];
                }
            }
        }
        return (empty($filedcatids)) ? true : false;
    }

    public function fetchCateDataByTabgroupid($tabgroupid)
    {
        $data = array();
        foreach (DB::fetch_all("select * from %t where tabgroupid = %d order by disp", array($this->_table, $tabgroupid)) as $value) {
            $data[$value['id']] = $value;
        }
        Hook::listen('lang_parse', $data, ['getFiledcatLangData', 1]);
        return $data;
    }

    public function delete($ids)
    {
        if (!is_array($ids)) $ids = (array)$ids;
        Hook::listen('lang_parse', $ids, ['delFiledcatLangData']);
        return parent::delete($ids);
    }

    public function delete_by_id($id, $field)
    {
        if (parent::fetch($id)) {
            parent::delete($id);
            $data = DB::fetch_all("select * from %t where catid=%d", array('form_setting', $id));
            if ($field) {
                foreach ($data as $value) {
                    C::t('form_setting')->delete_by_flag($value['flag']);
                }
            } else {
                foreach ($data as $value) {
                    C::t('form_setting')->update($value['flag'], array('catid' => 0));
                }
            }
            return true;
        }
        return false;
    }
}