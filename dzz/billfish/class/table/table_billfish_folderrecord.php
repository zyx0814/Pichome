<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 *
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */
if(!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_billfish_folderrecord extends dzz_table
{
    public function __construct() {
        $this->_table = 'billfish_folderrecord';
        $this->_pk    = 'id';
        parent::__construct();
    }


    public function insert_data($bfid,$setarr){
        $returndata =[];

        if($fid = DB::result_first("select fid from %t where bfid = %d and appid = %s",array($this->_table,$bfid,$setarr['appid']))){
            C::t('pichome_folder')->update($fid,$setarr);
            $setarr['fid'] = $fid;
            return $setarr;

        }else{
            $fpathkey = '';
            if($setarr['pfid']){
                $fpathkey =DB::result_first("select pathkey from %t where fid = %s and appid = %s",array('pichome_folder',$setarr['pfid'],$setarr['appid']));
            }
            $setarr['fid'] =  random(13) . $setarr['appid'];
            $setarr['pathkey'] = ($fpathkey) ? $fpathkey.$setarr['fid']:$setarr['fid'];
            if(C::t('pichome_folder')->insert($setarr)){
                $setarr1 = [
                    'bfid'=>$bfid,
                    'fid'=>$setarr['fid'],
                    'appid'=>$setarr['appid']
                ];
                if(parent::insert($setarr1,1)){
                    return $setarr;
                }else{
                    C::t('pichome_folder')->delete($setarr['fid']);
                    return $returndata;
                }
            }
        }

    }

    public function delete_by_bfid($bfids,$appid){
        if(!is_array($bfids)) $bfids = (array)$bfids;
        $delfid = $delid = [];
        foreach(DB::fetch_all("select fid,id from %t where bfid in(%n) and appid = %s",array($this->_table,$bfids,$appid)) as $v){
            $delfid[] = $v['fid'];
            $delid[] = $v['id'];
        }
        DB::delete('pichome_folderresources',"fid in (".dimplode($delfid).")");
        DB::delete('pichome_folder',"fid in (".dimplode($delfid).")");
        parent::delete($delid);
    }

    public function delete_by_appid($appid){
        DB::delete($this->_table,array('appid'=>$appid));
    }

}

