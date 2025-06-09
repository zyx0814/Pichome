<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_lang extends dzz_table
{
    public function __construct()
    {
        $this->_table = 'lang';
        $this->_pk = 'skey';
        parent::__construct();
    }

    public function createIndexTable($tablename)
    {
        global $_G;
        $tablepre = $_G['config']['db'][1]['tablepre'];
        $tablename = str_replace('-','_',$tablename);
        $tablename = strtolower($tablename);
        $tablenamesarr = DB::fetch_all("show tables ");
        $tabnames = [];
         foreach($tablenamesarr as $k=>$v){
             $tabnames = array_merge($tabnames,array_values($v));
         }
        if (!in_array($tablepre . 'lang_' . $tablename, $tabnames)) {
            //如果表不存在，创建此表
            DB::query("CREATE TABLE IF NOT EXISTS " . $tablepre . 'lang_' . $tablename . " ("
                . "skey varchar(120) NOT NULL DEFAULT '',"
                . "idtype tinyint(1) NOT NULL DEFAULT '0',"
                . "svalue mediumtext NOT NULL DEFAULT '',"
                . "idvalue char(32) DEFAULT NULL,"
                . "valtype tinyint(1) unsigned DEFAULT '0',"
                . "filed varchar(100) DEFAULT '',"
                . "`dateline` int(11) unsigned DEFAULT '0',"
                . "`deldate` int(11) unsigned DEFAULT '0',"
                . "`chkdate` int(11) unsigned DEFAULT '0',"
                . "PRIMARY KEY (skey) USING BTREE,"
                . " KEY `idtype` (`idtype`),"
                . " KEY `idval` (`idvalue`),"
                . " KEY `valtype` (`valtype`)"
                . ") ENGINE=MyISAM;"

            );
        }

        return true;
    }

    public function inittable($tablename = 'zh_CN')
    {

        $this->_table = strtolower('lang_' . $tablename);
    }

    public function insertData($lang, $setarr)
    {
        $this->inittable($lang);

        if ($setarr['skey']) $setarr['skey'] = getstr($setarr['skey']);
        else return true;
        $setarr['dateline'] = TIMESTAMP;
        if($setarr['idtype'] == 5){
            $values = unserialize($setarr['svalue']);
            $saveoptions = [];
            foreach($values as $k=>$v){
                if($v['newval'])$saveoptions[$k] = $v['newval'];
                else $saveoptions[$k] = $v['oldval'];
            }

            $setarr['svalue'] = serialize(array_unique($saveoptions));
        }


        if($setarr['idtype'] == 8 && DB::result_first("select count(*) from %t where svalue = %s and idtype = %d",[$this->_table,$setarr['svalue'],$setarr['idtype']])){
            return false;
        }


        if ($odata = DB::fetch_first("select * from %t where skey = %s", [$this->_table, $setarr['skey']]))
        {

            /*if($forms && $forms['type'] == 'fulltext'){
                $setarr['svalue'] = $this->getcontentdata($setarr['svalue'],$odata['svalue']);
            }*/

            $skey = $setarr['skey'];
            unset($setarr['skey']);
            if(parent::update($skey, $setarr)){
                if(in_array($setarr['idtype'],[0,3,16])){
                    $this->updateSearchvalById($lang,$setarr['idtype'],$setarr['idvalue']);
                }elseif($setarr['idtype'] == 5){
                    $this->updateOptions($lang,$values,$odata);
                }
            }
            return true;
        } else {
           /* if($forms && $forms['type'] == 'fulltext'){
                $setarr['svalue'] = $this->getcontentdata($setarr['svalue'],'');
            }*/
            if (parent::insert($setarr)) {
                if (in_array($setarr['idtype'],[0,3,16])) {
                   $this->updateSearchvalById($lang,$setarr['idtype'],$setarr['idvalue']);
                }
                return true;
            }
        }
    }

    public function updateOptions($lang,$values,$odata){//更新选项值

        $editoptions = [];
        //处理原有选项值
        $ovalues = unserialize($odata['svalue']);
        $oldoptions = array_column($values,'oldval');
        $editoptions['delval'] = array_diff($ovalues,$oldoptions);
        foreach ($values as $k => $v) {
            //如果原本有旧值，并且新值和旧值不一致视为修改
            if($v['oldval'] && $v['newval']){
                if (in_array($v['oldval'], $oldoptions) && $v['oldval'] != $v['newval']) {
                    $editoptions['editval'][] = $v;
                }
            }else{
               if(in_array($v['newval'],$editoptions['delval'])){
                   $index = array_search($v['newval'],$editoptions['delval']);
                   unset($editoptions['delval'][$index]);
               }
            }

        }
        $skeyarr = explode(':',$odata['skey']);
        $prekey = $skeyarr[1];
        $this->updateFiledvalByOtions($lang,$prekey,$editoptions);


    }

    public function updateFiledvalByOtions($lang,$filed,$editoptions){
        $this->inittable($lang);
        if(isset($editoptions['delval'])){
            foreach($editoptions['delval'] as $v){
                $params = [$this->_table,",$v,",',',TIMESTAMP,$filed,3];
                DB::query("update %t set svalue = replace(svalue,%s,%s),dateline = %d where filed= %s and idtype = %d",$params);
                $params = [$this->_table,",$v",'',TIMESTAMP,$filed,3];
                DB::query("update %t set svalue = replace(svalue,%s,%s),dateline = %d where filed= %s and idtype = %d",$params);
                $params = [$this->_table,"$v,",'',TIMESTAMP,$filed,3];
                DB::query("update %t set svalue = replace(svalue,%s,%s),dateline = %d where filed= %s and idtype = %d",$params);
                $params = [$this->_table,'',TIMESTAMP,$filed,3,$v];
                DB::query("update %t set svalue = %s,dateline = %d where filed= %s and idtype = %d and svalue = %s",$params);
            }
        }
        if(isset($editoptions['editval'])){
            foreach($editoptions['editval'] as $v){
                $params = [$this->_table,','.$v['oldval'].',',','.$v['newval'].',',TIMESTAMP,$filed,3];
                DB::query("update %t set svalue = replace(svalue,%s,%s),dateline = %d where filed= %s and idtype = %d",$params);
                $params = [$this->_table,$v['oldval'].',',$v['newval'].',',TIMESTAMP,$filed,3];
                DB::query("update %t set svalue = replace(svalue,%s,%s),dateline = %d where filed= %s and idtype = %d",$params);
                $params = [$this->_table,','.$v['oldval'],','.$v['newval'],TIMESTAMP,$filed,3];
                DB::query("update %t set svalue = replace(svalue,%s,%s),dateline = %d where filed= %s and idtype = %d",$params);
                $params = [$this->_table,$v['newval'],TIMESTAMP,$filed,3,$v['oldval']];
                DB::query("update %t set svalue = %s,dateline = %d where filed= %s and idtype = %d and svalue = %s",$params);
            }
        }


     /*   //初始化表

        $newvals = $oldvals = [];
        $params = [$this->_table,$prekey.'-[0-9]+'];
        $wheresql = " skey REGEXP %s";
        $orsql = [];
        //符合替换的条件
        if(isset($editoptions['editval'])){
            foreach($editoptions['editval'] as $v){
                $newvals[] = $v['newval'];
                $oldvals[] = $v['oldval'];
                $orsql[] = " find_in_set(%s,svalue)";
                $params[] = $v['oldval'];
            }
        }
        //符合删除的条件
        if(isset($editoptions['delval'])){
            foreach($editoptions['delval'] as $v){
                $orsql[] = " find_in_set(%s,svalue)";
                $params[] = $v;
            }
        }
        if($orsql)$wheresql .= " and (".implode(' or ',$orsql).')';
        foreach(DB::fetch_all("select skey,svalue from %t where $wheresql",$params) as $v){
            //处理原值为数组
            $svalarr = explode(',',$v['svalue']);
            //移除数组中需要删除的
            foreach($svalarr as $sk=>$sv){
                if(in_array($sv,$editoptions['delval'])){
                    unset($svalarr[$sk]);
                }
            }
            //替换需要修改的
            foreach($oldvals as $ok=>$ov){
                if(in_array($ov,$svalarr)){
                    $index = array_search($ov,$svalarr);
                    $svalarr[$index] = $newvals[$ok];
                }
            }
            $nsval = implode(',',$svalarr);
            parent::update($v['id'],['svalue'=>$nsval,'dateline'=>TIMESTAMP]);*/
        //}
    }

    //更新searchval值
    public function updateSearchvalById($lang, $idtype, $id)
    {
        require_once DZZ_ROOT . 'dzz' . BS . 'lang' . BS . 'class' . BS . 'idtypeMap.php';
        global $idTypeMap;
        //如果是文件
        if ($idtype == 0) {
            $idtypestr = 'file';
            $rid = $id;
            $searchval = '';
            $resourcesdata = C::t('pichome_resources')->fetch($rid);
            if(!$$resourcesdata){
                C::t('#lang#lang_search')->deleteByidvalue($idtype,$id);
                return true;
            }else{
                //获取属性数据
                $attrdata = C::t('pichome_resources_attr')->fetch($rid);
                $resourcesdata = array_merge($resourcesdata,$attrdata);
                //获取对应语言包值
                foreach ($idTypeMap[$idtypestr]['Key'] as $key => $v) {
                    if (is_array($resourcesdata[$v['id']])) {
                        foreach ($resourcesdata[$v['id']] as $idstr) {
                            $skeyarr[] = $idtypestr . '|' . $key . ':' . $idstr;
                        }
                    } else {

                        $skeyarr[] = $idtypestr . '|' . $key . ':' . $resourcesdata[$v['id']];
                    }

                }
                $langdata = C::t('#lang#lang')->fetchBySkeysIdtypes($lang, $idTypeMap[$idtypestr]['idtype'], $skeyarr);
                $tmpdata = [];
                foreach ($langdata as $v) {
                    $skey = str_replace($idtypestr . '|', '', $v['skey']);
                    list($filedname, $id) = explode(":", $skey);
                    if ($v['svalue']) $tmpdata[$filedname] = $v['svalue'];
                }
                $tmpdata = array_filter($tmpdata);
                $resourcesdata = array_merge($resourcesdata, $tmpdata);

                $searchval .= $resourcesdata['name'].$resourcesdata['link'];
                $tids = explode(',',$resourcesdata['tag']);
                if($tids){
                    $tagkeyarr = [];
                    $tagidtypestr = 'tag';
                    foreach ($idTypeMap[$tagidtypestr]['Key'] as $key => $v) {
                        if (is_array($tids)) {
                            foreach ($tids as $idstr) {
                                $tagkeyarr[] = $tagidtypestr . '|' . $key . ':' . $idstr;
                            }
                        } else {

                            $tagkeyarr[] = $tagidtypestr . '|' . $key . ':' . $tids;
                        }

                    }
                    $taglangdata = C::t('#lang#lang')->fetchBySkeysIdtypes($lang, $idTypeMap[$tagidtypestr]['idtype'],$tagkeyarr);
                    foreach ($taglangdata as $v){
                        $searchval .= $v['svalue'];
                    }
                }
                foreach (DB::fetch_all("select annotation from %t where rid =%s",array('pichome_comments',$tids)) as $v){
                    $searchval .= $v['annotation'];
                }
                $searchval .= $resourcesdata['desc'];
                $searchval = strip_tags($searchval);
                $searchval = htmlspecialchars($searchval);
            }


        }
        elseif(in_array($idtype,[3,16])){
            $tid = $id;
            $tabdata = [];
            $searchval = '';
            $tab = C::t('#tab#tab')->fetch($tid);
            if(!$tab){
                C::t('#lang#lang_search')->deleteByidvalue($idtype,$id);
                return true;
            }else{
                $tabdata['tabname']= $tab['tabname'];
                $gid = $tab['gid'];
                $forms = C::t('form_setting')->fetch_flags_by_gid($gid);
                $aforms =[];
                foreach($forms as $v){
                    $aforms[$v['flag']]=$v;
                }

                //获取特殊类型值加入到搜索属性
                foreach($aforms as $v){
                    if(in_array($v['type'],['inputselect','inputmultiselect'])){
                        foreach (DB::fetch_all("select valid from %t   where tid =%d and filed = %s ", array('tab_filedval', $tid, $v['flag'])) as $value) {
                            $o[] = $value['valid'];
                        }
                        if($o){
                            $valdatas = C::t('form_filedvals')->fetch_by_id($o);
                            $valdata = array_column($valdatas,'filedval');
                            $tabdata[$v['flag']]= implode('', $valdata);
                        }
                    }
                }
                $tabattrdata = C::t('#tab#tab_attr')->fetchDataByTid($tid,['searchattr']);
                if(!$tabattrdata) $tabattrdata=[];
                foreach($tabattrdata as $k=>$v){
                    if(!in_array($aforms['type'],['tabgroup'])) unset($tabattrdata[$k]);
                    else{
                        $reltids = explode(',',$v);
                        if($reltids){
                            foreach(DB::fetch_all("select tid,tabname from %t where tid in(%n)",['tab',$reltids]) as $val){
                                Hook::listen('lang_parse',$val,['getTablangData',0,$lang]);
                                $searchval .= $val['tabname'];
                            }
                        }

                    }
                    unset($tabattrdata[$k]);
                }
                $tabdata = array_merge($tabdata,$tabattrdata);

                //获取语言包值
                $langdata =[];
                $idtypearr = [3,16];
                foreach($idtypearr as $cidtype){
                    $tmplangdata = C::t('#lang#lang')->getAllDataByIdtypeIdval($lang, $cidtype, $tid);
                    if($cidtype == 16){
                        foreach($tmplangdata as $k=>$v){
                            $k = str_replace('tab|','',$k);
                            list($filedname, $id) = explode(":", $k);
                            if ($v) $langdata[$filedname] = $v;
                        }
                    }else{
                        foreach($tmplangdata as $k=>$v){
                            list($pre, $idstr) = explode(":", $k);
                            list($filedname, $id) = explode('-', $idstr);
                            if($v) $langdata[$filedname] = $v;
                        }
                    }
                }

                $tabdata = array_merge($tabdata,$langdata);

                foreach ($tabdata as $k=>$v){
                    if($v){
                        $searchval .= $v;
                    }
                }
                $searchval = strip_tags($searchval);
                $searchval = htmlspecialchars($searchval);
                $idtype = 3;
            }

        }
        C::t('#lang#lang_search')->updateSearchval($lang,$idtype,$id,$searchval);

    }

    public function copySearchvalByTidToNtid($lang, $tid,$ntid)
    {
        require_once DZZ_ROOT . 'dzz' . BS . 'lang' . BS . 'class' . BS . 'idtypeMap.php';
        global $idTypeMap;

        $tabdata = [];
        $tab = C::t('#tab#tab')->fetch($tid);
        $tabdata['tabname']= $tab['tabname'];

        $tabattrdata = C::t('#tab#tab_attr')->fetchDataByTid($tid,['searchattr']);
        if(!$tabattrdata) $tabattrdata=[];
        $tabdata = array_merge($tabdata,$tabattrdata);
        //获取语言包值
        $langdata =[];
        $idtypearr = [3,16];
        //获取所有的语言包值
        foreach($idtypearr as $cidtype){
            $tmplangdata = C::t('#lang#lang')->getByIdtypeIdval($lang, $cidtype, $tid);
            if($cidtype == 16){
                foreach($tmplangdata as $k=>$v){
                    $oskey = $k;
                    $k = str_replace('tab|','',$k);
                    list($filedname, $id) = explode(":", $k);
                    if ($v['svalue']) {
                        $langdata[$filedname] = $v['svalue'];
                        $nk = str_replace(':'.$tid,':'.$ntid,$oskey);
                        $v['skey'] = $nk;
                        $v['idvalue'] = $ntid;
                        C::t('#lang#lang')->insertData($lang,$v);
                    }
                }
            }else{
                foreach($tmplangdata as $k=>$v){
                    list($pre, $idstr) = explode(":", $k);
                    list($filedname, $id) = explode('-', $idstr);
                    if($v['svalue']) {
                        $langdata[$filedname] = $v['svalue'];
                         $nk = str_replace('-'.$tid,'-'.$ntid,$k);
                         $v['skey'] = $nk;
                         $v['idvalue'] = $ntid;
                        C::t('#lang#lang')->insertData($lang,$v);
                    }

                }
            }
        }

        $tabdata = array_merge($tabdata,$langdata);
        $searchval = '';
        foreach ($tabdata as $k=>$v){
            if($v){
                $searchval .= $v;
            }
        }
        $searchval = strip_tags($searchval);
        $idtype = 3;

        C::t('#lang#lang_search')->updateSearchval($lang,$idtype,$id,$searchval);

    }
    public function updateData($lang, $setarr)
    {
        $this->inittable($lang);
        if (parent::fetch($setarr['skey'])) {
            $skey = $setarr['skey'];
            unset($setarr['skey']);
            parent::update($skey, $setarr);
            return true;
        } else {
            return parent::insert($setarr, 1);
        }
    }

    public function fetchSvalueBySkey($lang, $skey)
    {
        $this->inittable($lang);
        $data = parent::fetch($skey);
        return $data['svalue'];
    }
    public function fetchBySkey($lang,$skey){
        $this->inittable($lang);
        return parent::fetch($skey);
    }
    public function fetchBySkeys($lang, $skey = [])
    {
        $this->inittable($lang);
        return parent::fetch_all($skey);
    }


    public function fetchBySkeysIdtypes($lang, $idtype, $skey = [])
    {
        $returndata = [];
        $this->inittable($lang);
        foreach (DB::fetch_all("select * from %t where skey in(%n) and idtype = %d and deldate = %d", array($this->_table, $skey, $idtype,0)) as $v) {
            $returndata[] = $v;
        }
        return $returndata;
    }

    //根据id和类型清空所有语言包值
    public function deleteByidvalue($idtype,$ids){
        global $_G;
        if(!is_array($ids)) $ids = (array)$ids;
        $langlist = $_G['language_list'];
        foreach ($langlist as $k => $v) {
            $table_sf = str_replace('-', '_', $k);
            $this->delByIdvalue($table_sf, $idtype,$ids);
        }
        C::t('#lang#lang_search')->deleteByidvalue($idtype,$ids);

    }
    //根据类型和id删除某种语言的语言包值
    public function delByIdvalue($lang, $idtype, $ids)
    {
        if (!is_array($ids)) $ids = (array)$ids;
        $this->inittable($lang);
        if(empty($ids)) return true;

        foreach(DB::fetch_all("select skey,svalue from %t where idtype = %d and idvalue In(%n)",array($this->_table,$idtype,$ids)) as $v){
            preg_match_all('/attach::\d+/',$v['svalue'],$match);
            foreach($match[0] as $val){
                $oaid = str_replace('attach::','',$val);
                C::t('attachment')->delete_by_aid($oaid);
            }
            parent::delete($v['skey']);

        }
        //return DB::delete($this->_table, 'idtype = ' . $idtype . ' AND idvalue In(' . dimplode($ids) . ')');
        return true;
    }
    public function delByIdvalueOrFiled($lang,$filed){
        $allowidtype = [2,3,5];
        $returntids = [];
        $this->inittable($lang);
        foreach(DB::fetch_all("select * from %t where idtype in(%n) and (idvalue = %s or filed = %s)",[$this->_table,$allowidtype,$filed,$filed]) as $v){
            //如果是字段和字段选项直接删除
            if(in_array($v['idtype'],[2,5])){
                $this->delByIdvalue($lang,$v['idtype'],$v['idvalue']);
            }else{
                //如果是字段值则作伪删除
                $this->updateDelete($lang,$v['idtype'],$v['idvalue']);
            }
        }
        return $returntids;
    }
    public function updateDelete($lang,$idtype,$idvalue){
        $this->inittable($lang);
        return DB::update($this->_table,array('deldate'=>TIMESTAMP),"idtype = " . $idtype . " AND idvalue = '" . $idvalue."'");
    }
    public function delBySkeysIdtypes($idtype, $skey)
    {
        if (!is_array($skey)) $skey = (array)$skey;
        global $_G;
        $langlist = $_G['language_list'];
        foreach ($langlist as $k => $v) {
            $table_sf = str_replace('-', '_', $k);
            $this->delBySkeys($table_sf, $idtype, $skey);
        }
        $searchkey = '';
        if ($idtype == 0) {
            $skeyarr = explode(':', $skey[0]);
            $rid = $skeyarr[1];
            $searchkey = 'searchval:' . $rid;
        } elseif ($idtype == 3) {
            $skeyarr = explode(':', $skey[0]);
            $tid = $skeyarr[1];
            $searchkey = 'searchval:' . $tid;
        }
        if ($searchkey) C::t('#lang#lang_search')->delete($searchkey);
        return true;

    }

    public function delBySkeys($lang, $idtype, $skeys)
    {
        $this->inittable($lang);
        if ($idtype == 3) {
            $skeyarr = explode(':', $skeys[0]);
            $tid = $skeyarr[1];
            $skeys[] = 'searchval:' . $tid;
        } elseif ($idtype == 0) {
            $skeyarr = explode(':', $skeys[0]);
            $rid = $skeyarr[1];
            $skeys[] = 'searchval:' . $rid;
        }
        return parent::delete($skeys);
    }
    /*获取所有的语言包值包含删除的*/
    public function getAllDataByIdtypeIdval($lang,$idtype,$idvalue){
        $this->inittable($lang);
        $datas = [];
        foreach(DB::fetch_all("select skey,svalue,deldate from %t where idtype=%s and idvalue = %s",[$this->_table,$idtype,$idvalue]) as $v){
            if($v['deldate']){
                parent::delete($v['skey']);
            }else{
                $datas[$v['skey']] = $v['svalue'];
            }

        }
        return $datas;
    }

    public function getByIdtypeIdval($lang,$idtype,$idvalue)
    {
        $this->inittable($lang);
        $datas = [];
        foreach(DB::fetch_all("select * from %t where idtype=%s and idvalue = %s and deldate = %d",[$this->_table,$idtype,$idvalue,0]) as $v){
            $datas[$v['skey']] = $v;
        }
        return $datas;
    }
    public function getDataByIdtypeIdval($lang,$idtype,$idvalue)
    {
        $this->inittable($lang);
        $datas = [];
        foreach(DB::fetch_all("select * from %t where idtype=%s and idvalue = %s and deldate = %d",[$this->_table,$idtype,$idvalue,0]) as $v){
            $datas[$v['skey']] = $v['svalue'];
        }
        return $datas;
    }



}