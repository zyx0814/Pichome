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

class table_form_setting extends dzz_table
{
    private $type = array('input','time', 'textarea','date', 'timerange', 'select', 'multiselect', 'user', 'label', 'tagcat', 'grade', 'color', 'link');

    public function __construct()
    {

        $this->_table = 'form_setting';
        $this->_pk = 'flag';
        $this->_pre_cache_key = 'form_setting_';
        $this->_cache_ttl = 60 * 60;
        parent::__construct();
    }

    public function fetch($flag,$noisdel = true)
    {
        if($noisdel){
            $data = DB::fetch_first("select * from %t where flag  =%s and isdel = 0",array($this->_table,$flag));
        }else{
            $data = parent::fetch($flag);
        }
        if(empty($data)) return false;
        if ($data['options']) {
            $data['options'] = unserialize($data['options']);
        }
        if ($data['extra']) $data['extra'] = unserialize($data['extra']);

        if (!empty($data['extra']['mindate'])) {
            $data['extra']['mindate'] = dgmdate($data['extra']['mindate'], $data['extra']['dateformat']);
        }
        if (!empty($data['extra']['maxdate'])) {
            $data['extra']['maxdate'] = dgmdate($data['extra']['maxdate'], $data['extra']['dateformat']);
        }

        $data['labelname'] = lang('fs_' . $data['flag']) != 'fs_' . $data['flag'] ? lang('fs_' . $data['flag']) : $data['labelname'];
        return $data;
    }

    //插入表单数据;
    public function insert_by_flag($flag, $form,$hasnewval=1)
    {

        $setarr['appid'] = isset($form['appid']) ? intval($form['appid']) : 0;
        if ((!$setarr['appid'] && !$form['tabgroupid']) && empty($flag)) {
            return false;
        } elseif (!$flag) {
            //自定义app字段flag标志
            if ($setarr['appid']) $flag = 'appid_' . $setarr['appid'] . '_' . time() . random(4);
            if ($form['tabgroupid']) $flag = 'tabgroup_' . $form['gid'] . '_' . time() . random(4);
        }

        if (!in_array($form['type'], $this->type)) return false;
        if (empty($form['labelname'])) return false;
        $oflagdata = parent::fetch($flag);
        $old_flag = $form['old_flag'];
        $setarr['labelname'] = getstr($form['labelname'], 60);
        $setarr['required'] = intval($form['required']);
        $setarr['multiple'] = intval($form['multiple']);
        $setarr['type'] = $form['type'];
        $setarr['disp'] = intval($form['disp']);
        $setarr['system'] = intval($form['system']);
        if ($form['catid']) $setarr['catid'] = intval($form['catid']);
        $setarr['incard'] = intval($form['incard']);
        $setarr['filedtype'] = getstr($form['filedtype'], 60);
        $setarr['isdefault'] = isset($form['appid']) ? intval($form['isdefault']) : 0;
        $setarr['tabgroupid'] = isset($form['tabgroupid']) ? intval($form['tabgroupid']) : 0;
        $setarr['filedcat'] = isset($form['filedcat']) ? intval($form['filedcat']) : 0;
        if ($setarr['appid'] || $setarr['tabgroupid']) {
            $setarr['allowsearch'] = ($form['type'] == 'textarea' || $form['type'] == 'input') ? 0 : 1;
        }
        if($hasnewval){
            if (in_array($form['type'], array('multiselect', 'select'))) {
                if($oflagdata){
                    $editoptions =  [];
                    $oldoptions = unserialize($oflagdata['options']);
                    $suboldoptions = array_column($form['options'], 'oldval');
                    //需要删除的选项
                    $editoptions['delval'] = array_diff($oldoptions,$suboldoptions);

                    //传递的新值
                    $newoptions = array_column($form['options'], 'newval');
                    $newoptions = array_filter($newoptions);

                    if ($newoptions) {
                        foreach ($form['options'] as $k => $v) {
                            //如果原本有旧值，并且新值和旧值不一致视为修改
                            if (in_array($v['oldval'], $oldoptions) && $v['oldval'] != $v['newval']) {
                                $editoptions['editval'][] = $v;
                            }
                        }
                    }

                }
                $saveoptions = [];
                foreach($form['options'] as $k=>$v){
                    if($v['newval'])$saveoptions[$k] = $v['newval'];
                    else $saveoptions[$k] = $v['oldval'];
                }
                $form['options'] = $saveoptions;
            }

        }

        switch ($form['type']) {
            case 'input':
            case 'textarea':
            case 'grade':
                $setarr['length'] = intval($form['length']);
                $setarr['regex'] = trim($form['regex']);
                $extra = array(
                    'hint' => getstr($form['hint']),
                );
                $setarr['extra'] = serialize($extra);
                break;
            case 'select':
                $setarr['options'] = is_array($form['options']) ? serialize($form['options']) : '';
                $setarr['multiple'] = 0;
                break;
            case 'multiselect':
                $setarr['options'] = is_array($form['options']) ? serialize($form['options']) : '';
                break;
            case 'timerange':
                $setarr['multiple'] = 1;
                $extra = array(
                    'maxdate' => $form['maxdate'] ? strtotime($form['maxdate']) : 0,
                    'mindate' => $form['mindate'] ? strtotime($form['mindate']) : 0,
                    'dateformat' => trim($form['dateformat'])
                );
                $setarr['extra'] = serialize($extra);
                break;
            case 'time':
                $setarr['multiple'] = 0;
                $extra = array(
                    'maxdate' => $form['maxdate'] ? strtotime($form['maxdate']) : 0,
                    'mindate' => $form['mindate'] ? strtotime($form['mindate']) : 0,
                    'dateformat' => trim($form['dateformat'])
                );
                $setarr['extra'] = serialize($extra);
                break;

            case 'user':
                break;
            case 'label':
                $extra = array(
                    'label' => $form['label'],
                );


                $setarr['extra'] = serialize($extra);
                break;
            case 'tagcat':
                $extra = array(
                    'label' => $form['label'],
                );


                $setarr['extra'] = serialize($extra);
                break;
            case 'color':

                break;
            default:


        }

        if ($oflagdata) {
            parent::update($flag, $setarr);
            if ($setarr['tabgroupid']) {
                C::t('#tab#tab_attr')->update_val_by_flag($flag, $editoptions);
            } else {
                if (isset($editoptions)) {
                    C::t('resources_attr')->update_val_by_flag($flag, $editoptions);
                }
            }

            $setarr['flag'] = $flag;
        } else {
            $setarr['flag'] = $flag;
            parent::insert($setarr, 1);
        }
        if ($old_flag && $old_flag != $flag) {
            parent::delete($old_flag);
        }

        $setarr['extra'] = unserialize($setarr['extra']);
        if ($setarr['extra']['mindate']) {
            $setarr['extra']['mindate'] = dgmdate($setarr['extra']['mindate'], $setarr['extra']['dateformat']);
        }
        if ($setarr['extra']['maxdate']) {
            $setarr['extra']['maxdate'] = dgmdate($setarr['extra']['maxdate'], $setarr['extra']['dateformat']);
        }
        if ($setarr['extra']['label']) {
            $setarr['extra']['label'] = C::t('tag_cat')->fetch($setarr['extra']['label']);
        }
        $setarr['labelname'] = lang('fs_' . $setarr['flag']) != 'fs_' . $setarr['flag'] ? lang('fs_' . $setarr['flag']) : $setarr['labelname'];
        $setarr['options'] = unserialize($setarr['options']);
        $cachekey = 'alldata_';
        $this->clear_cache($cachekey);
        return $setarr;
    }

    /*获取标注所需所有表单项*/
    public function fetch_flags_by_appid($appid, $allowsearch = 0,$isnodel = 1)
    {
        $data = array();
        $param = array($this->_table, intval($appid),2);
        $sql = " 1 and (appid = %d or (tabgroupid > 0 and system = %d ))";
        if ($allowsearch) {
            $sql .= " and   allowsearch = 1";
        }
        if($isnodel){
            $sql .= " and   isdel = 0 ";
        }
        foreach (DB::fetch_all("select * from %t where $sql order by disp", $param) as $value) {
            if($value['tabgroupid'] && !C::t('#tab#tab_group')->fetch($value['tabgroupid'])){
                continue;
            }
            if ($value['extra']) {
                $value['extra'] = unserialize($value['extra']);
                if ($value['extra']['mindate']) {
                    $value['extra']['mindate'] = dgmdate($value['extra']['mindate'], $value['extra']['dateformat']);
                }
                if ($value['extra']['maxdate']) {
                    $value['extra']['maxdate'] = dgmdate($value['extra']['maxdate'], $value['extra']['dateformat']);
                }
                if ($value['extra']['label']) {
                    $value['extra']['label'] = C::t('tag_cat')->fetch($value['extra']['label']);
                }
            }
            if ($value['options']) {
                $value['options'] = unserialize($value['options']);
            }
            $value['labelname'] = (lang('fs_' . $value['flag']) != 'fs_' . $value['flag']) ? lang('fs_' . $value['flag']) : $value['labelname'];
            $data[] = $value;
        }
        return $data;
    }

    /*获取标签组标注所需所有表单项*/
    public function fetch_flags_by_gid($gid, $allowsearch = 0,$noisdel = 1)
    {
        $data = array();
        $param = array($this->_table, intval($gid));
        $sql = " 1 and tabgroupid = %d ";
        if ($allowsearch) {
            $sql .= " and   allowsearch = 1";
        }
        if($noisdel){
            $sql .= " and   isdel = 0 ";
        }
        foreach (DB::fetch_all("select * from %t where $sql order by disp", $param) as $value) {
            if($value['tabgroupid'] && !C::t('#tab#tab_group')->fetch($value['tabgroupid'])){
                continue;
            }
            if ($value['extra']) {
                $value['extra'] = unserialize($value['extra']);
                if ($value['extra']['mindate']) {
                    $value['extra']['mindate'] = dgmdate($value['extra']['mindate'], $value['extra']['dateformat']);
                }
                if ($value['extra']['maxdate']) {
                    $value['extra']['maxdate'] = dgmdate($value['extra']['maxdate'], $value['extra']['dateformat']);
                }
                if ($value['extra']['label']) {
                    $value['extra']['label'] = C::t('tag_cat')->fetch($value['extra']['label']);
                }
            }
            if ($value['options']) {
                $value['options'] = unserialize($value['options']);
            }
            $value['labelname'] = (lang('fs_' . $value['flag']) != 'fs_' . $value['flag']) ? lang('fs_' . $value['flag']) : $value['labelname'];
            $data[] = $value;
        }
        return $data;
    }

    /*获取所有表单项*/
    public function fetch_all_conditions($flags = array(), $catids = array(), $keyword = '')
    {
        $data = array();
        $sql = 1;
        $param = array($this->_table);
        if ($flags) {
            $sql .= " and flag in (%n)";
            $param[] = $flags;
        }
        if ($catids) {
            $sql .= " and catid in (%n)";
            $param[] = $catids;
        }
        if ($keyword) {
            $sql .= " and ( `flag` LIKE %s OR labelname LIKE %s )";
            $param[] = '%' . $keyword . '%';
            $param[] = '%' . $keyword . '%';
        }
        foreach (DB::fetch_all("select * from %t where $sql order by disp", $param) as $value) {
            if ($value['extra']) {
                $value['extra'] = unserialize($value['extra']);
                if (!empty($value['extra']['mindate'])) {
                    $value['extra']['mindate'] = dgmdate($value['extra']['mindate'], $value['extra']['dateformat']);
                }
                if (!empty($value['extra']['maxdate'])) {
                    $value['extra']['maxdate'] = dgmdate($value['extra']['maxdate'], $value['extra']['dateformat']);
                }
                if (!empty($value['extra']['label'])) {
                    $value['extra']['label'] = C::t('tag_cat')->fetch($value['extra']['label']);
                }
            }
            if ($value['options']) {
                $value['options'] = unserialize($value['options']);
            }
            $value['labelname'] = (lang('fs_' . $value['flag']) != 'fs_' . $value['flag']) ? lang('fs_' . $value['flag']) : $value['labelname'];
            $data[] = $value;
        }
        return $data;
    }

    public function fetch_all_data()
    {
        $cachekey = 'alldata_';
        if ($data = $this->fetch_cache($cachekey)) {
            return $data;
        } else {
            $data = array();
            foreach (DB::fetch_all("select * from %t where 1 ", array($this->_table)) as $value) {
                if ($value['extra']) {
                    $value['extra'] = unserialize($value['extra']);
                    if (!empty($value['extra']['mindate'])) {
                        $value['extra']['mindate'] = dgmdate($value['extra']['mindate'], $value['extra']['dateformat']);
                    }
                    if (!empty($value['extra']['maxdate'])) {
                        $value['extra']['maxdate'] = dgmdate($value['extra']['maxdate'], $value['extra']['dateformat']);
                    }
                    if (!empty($value['extra']['label'])) {
                        $value['extra']['label'] = C::t('tag_cat')->fetch($value['extra']['label']);
                    }
                }
                if ($value['options']) {
                    $value['options'] = unserialize($value['options']);
                }
                $value['labelname'] = (lang('fs_' . $value['flag']) != 'fs_' . $value['flag']) ? lang('fs_' . $value['flag']) : $value['labelname'];
                $data[$value['flag']] = $value;
            }
            $this->store_cache($cachekey, $data);
            return $data;
        }
    }

    public function delete_by_flag($flag, $tabgroupid = 0)
    {
        if ($tabgroupid) {
            foreach(DB::fetch_all("select flag from %t where tabgroupid = %d",array($this->table,$tabgroupid)) as $v) {
                parent::delete($v['flag']);
            }
            return true;
        } else {
            return parent::delete($flag);
        }

    }
    public function delete_filed_by_flag($flag){
        return parent::update($flag,['isdel'=>1]);
    }
    public function fetch_tabflag_by_filedcat($filedcatid){
        $flags = [];
        foreach(DB::fetch_all("select flag from %t where filedcat = %d and tabgroupid > 0 and `type` != %s",array($this->_table,$filedcatid,'tabgroup')) as $v){
            $flags[] = $v['flag'];
        }
        return $flags;
    }

    public function delete_by_appid($appid)
    {
        foreach (DB::fetch_all("select flag from %t where appid = %d", array($this->_table, $appid)) as $v) {
            $this->delete_by_flag($v['flag']);
        }
    }

    public function delete_by_gid($gid)
    {
        foreach (DB::fetch_all("select flag from %t where tabgroupid = %d", array($this->_table, $gid)) as $v) {
            $this->delete_by_flag($v['flag'], 1);
        }
    }

    public function fetch_flag_by_type($type)
    {
        $cachekey = 'skeys_' . $type;
        if ($returndata = $this->fetch_cache($cachekey)) {
            return $returndata;
        } else {
            $skeys = array();
            foreach (DB::fetch_all("select flag from %t where type=%s", array('form_setting', $type)) as $value) {
                $skeys[] = $value['flag'];
            }
            $this->store_cache($cachekey, $skeys);
            return $skeys;
        }
    }

    public function fetch_by_flags($flags)
    {
        if (!is_array($flags)) $flags = (array)$flags;
        $datas = array();
        foreach (DB::fetch_all("select * from %t where flag in(%n)", array($this->_table, $flags)) as $v) {
            if ($v['options']) {
                $v['options'] = unserialize($v['options']);
            }
            if ($v['extra']) $v['extra'] = unserialize($v['extra']);

            if ($v['extra']['mindate']) {
                $v['extra']['mindate'] = dgmdate($v['extra']['mindate'], $v['extra']['dateformat']);
            }
            if ($v['extra']['maxdate']) {
                $v['extra']['maxdate'] = dgmdate($v['extra']['maxdate'], $v['extra']['dateformat']);
            }
            $v['labelname'] = lang('fs_' . $v['flag']) != 'fs_' . $v['flag'] ? lang('fs_' . $v['flag']) : $v['labelname'];
            $datas[] = $v;
        }
        return $datas;
    }
    //添加库时添加应用字段
    public function add_app_filed($appid)
    {
        $appdata = C::t('#vapp#vapp')->fetch($appid);
        $foldertemplate = C::t('#foldertemplate#folder_template')->fetch($appdata['rfid']);
        foreach (DB::fetch_all("select flag,tabgroupid from %t where system = %d  and tabgroupid > 0 ",
            array($this->_table, 2)) as $v) {
            $fileddata = parent::fetch($v['flag']);
            //检查卡片是否被删除
            if(!$tabgroupdata = C::t('#tab#tab_group')->fetch($v['tabgroupid'])) continue;
            $foldertemplate = C::t('#foldertemplate#folder_template')->fetch($appdata['rfid']);
            $tplfiled = unserialize($foldertemplate['formlist']);
            $tplfiled[$v['flag']] = [
                'show' => $fileddata['show'],
                'sort' => $fileddata['sort'],
                'style' => $fileddata['style'],
                'status' => 0,
                'writable' =>  1,//此处值根据默认目录模板设置
                'tofolder' =>  0,//此处值根据默认目录模板设置
                'allowchange' => 1,
            ];
            $setarr1['formlist'] = serialize($tplfiled);
            $setarr1['fname'] = $foldertemplate['fname'];
            if (C::t('#foldertemplate#folder_template')->update($appdata['rfid'], $setarr1) &&
                C::t('#foldertemplate#folder_template')->update($appdata['tfids'], $setarr1)) {
                $searchtemplate = C::t('#vapp#vapp_search')->fetch($foldertemplate['st']);
                $searchtpldata = unserialize($searchtemplate['formlist']);
                if ($fileddata['type'] != 'textarea' || $fileddata['type'] != 'input') {
                    $searchtpldata[$v['flag']] = [
                        'multiple' => intval($fileddata['multiple']),
                        'range' => 0,
                        'dw' => '',
                        'status' => 0];
                    $setarr['formlist'] = serialize($searchtpldata);
                    $return = C::t('#vapp#vapp_search')->update($foldertemplate['st'], $setarr);
                    $data = C::t('#vapp#vapp_search')->fetch_by_id($foldertemplate['st']);
                    memory('check') && memory('set', 'vapp_search_forms_' . $foldertemplate['st'], $data, 60 * 60);

                }
            }
        }
    }
    //添加应用字段到库
    public function add_filed_toapp_by_flag($flag)
    {
        if (!$fileddata = parent::fetch($flag)) return false;
        $appdata = C::t('#vapp#vapp')->fetch_all_data();
        foreach ($appdata as $v) {
            $foldertemplate = C::t('#foldertemplate#folder_template')->fetch($v['rfid']);
            $tplfiled = unserialize($foldertemplate['formlist']);
            $tplfiled[$flag] = [
                'show' => $fileddata['show'],
                'sort' => $fileddata['sort'],
                'style' => $fileddata['style'],
                'status' => 0,
                'writable' =>  1,//此处值根据默认目录模板设置
                'tofolder' =>  0,//此处值根据默认目录模板设置
                'allowchange' => 1,
            ];
            $setarr1['formlist'] = serialize($tplfiled);
            $setarr1['fname'] = $foldertemplate['fname'];
            if (C::t('#foldertemplate#folder_template')->update($v['rfid'], $setarr1) &&
                C::t('#foldertemplate#folder_template')->update($v['tfids'], $setarr1)) {
                $searchtemplate = C::t('#vapp#vapp_search')->fetch($foldertemplate['st']);
                $searchtpldata = unserialize($searchtemplate['formlist']);
                if ($fileddata['type'] != 'textarea' || $fileddata['type'] != 'input') {
                    $searchtpldata[$flag] = [
                        'multiple' => intval($fileddata['multiple']),
                        'range' => 0,
                        'dw' => '',
                        'status' => 0];
                    $setarr['formlist'] = serialize($searchtpldata);
                    $return = C::t('#vapp#vapp_search')->update($foldertemplate['st'], $setarr);
                    $data = C::t('#vapp#vapp_search')->fetch_by_id($foldertemplate['st']);
                    memory('check') && memory('set', 'vapp_search_forms_' . $foldertemplate['st'], $data, 60 * 60);

                }
            }

        }
        return true;
    }
    //删除应用字段时，删除库字段
/*    public function del_filed_toapp_by_flag($flag)
    {
        if (!$fileddata = $this->fetch($flag)) return false;
        $appdata = C::t('#vapp#vapp')->fetch_all_data();
        foreach ($appdata as $v) {
            $foldertemplate = C::t('#foldertemplate#folder_template')->fetch($v['rfid']);
            $tplfiled = unserialize($foldertemplate['formlist']);
            unset($tplfiled[$flag]);
            $setarr1['formlist'] = serialize($tplfiled);
            $setarr1['fname'] = $foldertemplate['fname'];
            if (C::t('#foldertemplate#folder_template')->update($v['rfid'], $setarr1) &&
                C::t('#foldertemplate#folder_template')->update($v['tfids'], $setarr1)) {
                $searchtemplate = C::t('#vapp#vapp_search')->fetch($foldertemplate['st']);
                $searchtpldata = unserialize($searchtemplate['formlist']);
                if (isset($searchtpldata[$flag])) {
                    unset($searchtpldata[$flag]);
                    $setarr['formlist'] = serialize($searchtpldata);
                    $return = C::t('#vapp#vapp_search')->update($foldertemplate['st'], $setarr);
                    $data = C::t('#vapp#vapp_search')->fetch_by_id($foldertemplate['st']);
                    memory('check') && memory('set', 'vapp_search_forms_' . $foldertemplate['st'], $data, 60 * 60);

                }
            }
        }
        return true;

    }*/


   /* public function fetch_allsystem_data($system = 1)
    {
        $cachekey = 'allsystemdata_'.$system.'_';
        if ( $data = $this->fetch_cache($cachekey)) {
            return $data;
        } else {
            $data = array();
            foreach (DB::fetch_all("select * from %t where system = %d ", array($this->_table,$system)) as $value) {
                if ($value['extra']) {
                    $value['extra'] = unserialize($value['extra']);
                    if ($value['extra']['mindate']) {
                        $value['extra']['mindate'] = dgmdate($value['extra']['mindate'], $value['extra']['dateformat']);
                    }
                    if ($value['extra']['maxdate']) {
                        $value['extra']['maxdate'] = dgmdate($value['extra']['maxdate'], $value['extra']['dateformat']);
                    }
                    if ($value['extra']['label']) {
                        $value['extra']['label'] = C::t('tag_cat')->fetch($value['extra']['label']);
                    }
                    if($value['extra']['units'])  $value['units'] = $value['extra']['units'];
                }
                if ($value['options']) {
                    $value['options'] = unserialize($value['options']);
                }
                $value['labelname'] = (lang('fs_' . $value['flag']) != 'fs_' . $value['flag']) ? lang('fs_' . $value['flag']) : $value['labelname'];
                $data[$value['flag']] = $value;
            }
            $this->store_cache($cachekey, $data);
            return $data;
        }
    }*/







}