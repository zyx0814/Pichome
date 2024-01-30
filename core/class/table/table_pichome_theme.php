<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_pichome_theme extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'pichome_theme';
        $this->_pk = 'id';
        $this->_pre_cache_key = 'pichome_theme';
        //$this->_cache_ttl = 3600;
        parent::__construct();
    }
    public function insert_data($themedata){
        if(isset($themedata['isdefault'])){
            $isdefault = intval($themedata['isdefault']);
            unset($themedata['isdefault']);
        }
        if($tid = DB::result_first("select id from %t where themename = %s",array($this->_table,$themedata['themename']))){
            parent::update($tid,$themedata);
        }else{

            if($tid = parent::insert($themedata,1)){
                if($isdefault){
                    C::t('setting')->update('pichometheme',$tid);
                }
            }
        }

        include libfile('function/cache');
        updatecache('setting');
        return $tid;
    }

    public function fech_all_theme(){
        $themedata = [];
         foreach(DB::fetch_all("select * from %t where 1",array($this->_table)) as $v){
             $v['themestyle'] = unserialize($v['themestyle']);
             $themedata[] = $v;
         }
        return $themedata;
    }

}