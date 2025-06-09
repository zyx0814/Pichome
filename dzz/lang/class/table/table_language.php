<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_language extends dzz_table
{
    public function __construct()
    {
        $this->_table = 'language';
        $this->_pk = 'langflag';
        parent::__construct();
    }

    public function insertData($setarr){
        $langflag = $setarr['langflag'];
        if(DB::result_first("select COUNT(*) from %t where langflag = %s",array($this->_table,$langflag))){
            return ['error'=>lang('langflag_exist')];
        }else{
            return parent::insert($setarr);
        }
    }

    public function enableLanguage($langflag){
        if(C::t('lang')->createIndexTable($langflag)){
            return parent::update($langflag,['state'=>1]);
        }

    }



}