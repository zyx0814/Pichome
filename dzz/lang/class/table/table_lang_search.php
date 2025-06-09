<?php
if(!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_lang_search extends dzz_table
{
    public function __construct()
    {
        $this->_table = 'lang_search';
        $this->_pk    = 'skey';
        parent::__construct();
    }

    public function updateSearchval($lang,$idtype,$idval,$svalue){
        $setarr = [
            'skey'=>$lang.'-'.$idval.'-'.$idtype,
            'idtype'=>$idtype,
            'svalue'=>$svalue,
            'idvalue'=>$idval,
            'lang'=>$lang,
            'dateline'=>TIMESTAMP
        ];
        if(parent::fetch($setarr['skey'])){
            $skey = $setarr['skey'];
            unset($setarr['skey']);
            $update =  parent::update($skey,$setarr);
        }else{
            $update =  parent::insert($setarr);
        }
        if($update){
            $this->updateLangsearch($idtype,$idval);
        }

    }
    public function updateLangsearch($idtype,$idval){

        global $_G;
        $langlist = $_G['language_list'];
        $svalue = '';
        $skey = 'searchval-'.$idval.'-'.$idtype;
        foreach($langlist as $k=>$v){
            $table_sf = str_replace('-', '_', $k);
            $csvalue = parent::fetch($table_sf.'-'.$idval.'-'.$idtype);
            $svalue .= $csvalue['svalue'];
        }
        $nsetarr = [
            'skey'=>$skey,
            'idtype'=>$idtype,
            'svalue'=>$svalue,
            'idvalue'=>$idval,
            'lang'=>'all',
            'dateline'=>TIMESTAMP
        ];
        if(parent::fetch($skey)){
            $skey = $nsetarr['skey'];
            unset($nsetarr['skey']);
            parent::update($skey,$nsetarr);
            return true;
        }else{
            return parent::insert($nsetarr,1);
        }
    }
    public function deleteByidvalue($idtype,$ids){
        if(!is_array($ids)) $ids = array($ids);
        if(empty($ids)) return true;
        return DB::delete($this->_table, 'idtype = '.$idtype.' AND idvalue In('.dimplode($ids).')');
    }
}