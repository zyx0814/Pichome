<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */

if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_cache extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'cache';
        $this->_pk = 'cachekey';

        parent::__construct();
    }

    public function insert_cachedata_by_cachename($setarr, $expiretime = 600,$recordtime = 0)
    {
        $cachename = $setarr['cachekey'];
        if (memory('check')) {
            unset($setarr['cachekey']);
            memory('set', $cachename, $setarr, $expiretime);
            if($recordtime) memory('set', $cachename.'_time', TIMESATMP, $expiretime);
        } else {
            if (DB::result_first("select count(*) from %t where cachekey = %s", array('cache', $cachename))) {
                unset($setarr['cachekey']);
                parent::update($cachename, $setarr);
            } else {
                parent::insert($setarr);
            }

        }
        return true;

    }

    public function fetch_cachedata_by_cachename($cachename, $expiretime = 600)
    {
        if (memory('check')) {
            $data = ($data = memory('get', $cachename)) === false ? array() : (unserialize($data['cachevalue']) ? unserialize($data['cachevalue']):$data['cachevalue']);
        } else {

            $cachedata = parent::fetch($cachename);
            if ($cachedata && ($cachedata['dateline'] + $expiretime) > TIMESTAMP) {
                $data = unserialize($cachedata['cachevalue']) ?  unserialize($cachedata['cachevalue']):$cachedata['cachevalue'];
            } else {
                $data = array();
            }
        }
        //如果缓存无效则删除当前缓存
        if(!$data) $this->delete_cachedata_by_cachename($cachename);
        return $data;
    }

    public function delete_cachedata_by_cachename($cachename)
    {

        if (memory('check')) {
            memory('rm',$cachename);
        } else {
            parent::delete($cachename);
        }
        return true;
    }

    public function get_cachetime_by_cachename($cachename){
        $dateline = false;
        if (memory('check')) {
            $dateline = ($dateline = memory('get', $cachename.'_time')) === false ? array() : $dateline;
        } else {
            $cachedata = parent::fetch($cachename);
            $dateline = $cachedata['dateline'];
        }
        return $dateline;
    }

    public function clear_allcache(){
        if (memory('check')){
            C::memory()->clear();
        }else{
            DB::delete($this->_table,' 1 ');
        }
        return true;

    }
}

