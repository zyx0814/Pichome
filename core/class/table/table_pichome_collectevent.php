<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
class table_pichome_collectevent extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'pichome_collectevent';
        $this->_pk = 'id';

        parent::__construct();
    }


   }