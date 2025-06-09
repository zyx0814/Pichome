<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_pichome_bannertagcat extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'pichome_bannertagcat';
        $this->_pk = 'id';
        $this->_pre_cache_key = 'pichome_bannertagcat_';
        //$this->_cache_ttl = 3600;
        parent::__construct();
    }


}