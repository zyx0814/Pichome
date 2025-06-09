<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_pichome_collectuser extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'pichome_collectuser';
        $this->_pk = 'id';
        $this->_pre_cache_key = 'pichome_collectuser';
        $this->_cache_ttl = 3600;

        parent::__construct();
    }

    //获取某个用户的收藏夹权限值
    public function get_perm_by_clid($clid, $uid = 0)
    {
        global $_G;
        $perm = 0;
        if (!$uid && $_G['adminid'] == 1) {
            $perm = 5;
        } else {
            if (!$uid) $uid = $_G['uid'];
            $perm = DB::result_first("select perm from %t where uid = %d and clid = %d", array($this->_table, $uid, $clid));


        }
        return $perm;
    }

    //删除收藏夹所有用户
    public function delete_by_clid($clid)
    {
        return DB::delete($this->_table, array('clid' => $clid));
    }

    //为收藏夹添加用户
    public function add_user_to_collect($setarr)
    {
        $permtitle = ['参与者', '协作成员', '管理员', '创建者'];
        if (!$setarr['uid']) return array('error' => 'obj_user_notallow_empty');
        $perm = C::t('pichome_collectuser')->get_perm_by_clid($setarr['clid']);

        if ($perm < 3) {
            return array('error' => 'no_perm');
        }

        //如果有数据就修改
        if ($id = DB::result_first("select id from %t where uid = %d and clid = %d", array($this->_table, $setarr['uid'], $setarr['clid']))) {
            if(getglobal('uid') == $setarr['uid']) return array('error'=>'unallow do self');
            $objuser= DB::fetch_first("select username,adminid from %t where uid = %d", array('user', $setarr['uid']));
            if($objuser['adminid'] == 1) $setarr['perm'] = 5;
            if (parent::update($id, $setarr)) {
                $collectname = DB::result_first("select name from %t where clid = %d", array('pichome_collect', $setarr['cild']));
                //添加事件
                $enventbodydata = ['username' => getglobal('username'), 'name' => $collectname, 'objusername' => $objuser['username'], 'permtitle' => $permtitle[$setarr['perm']]];
                $enventdata = [
                    'eventbody' => 'addcollectuer',
                    'uid' => getglobal('uid'),
                    'username' => getglobal('username'),
                    'bodydata' => json_encode($enventbodydata),
                    'clid' => $setarr['clid'],
                    'do' => 'add_collectuer',
                    'do_obj' => $collectname,
                    'dateline' => TIMESTAMP
                ];
                C::t('pichome_collectevent')->insert($enventdata);
            }
        } else {
            $objuser= DB::fetch_first("select username,adminid from %t where uid = %d", array('user', $setarr['uid']));
            if($objuser['adminid'] == 1) $setarr['perm'] = 5;
            if (parent::insert($setarr, 1)) {
                $collectname = DB::result_first("select name from %t where clid = %d", array('pichome_collect', $setarr['cild']));
                //$objusername = DB::result_first("select username from %t where uid = %d", array('user', $setarr['uid']));
                //添加事件
                $enventbodydata = ['username' => getglobal('username'), 'name' => $collectname, 'objusername' => $objuser['username'], 'permtitle' => $permtitle[$setarr['perm']]];
                $enventdata = [
                    'eventbody' => 'addcollectuer',
                    'uid' => getglobal('uid'),
                    'username' => getglobal('username'),
                    'bodydata' => json_encode($enventbodydata),
                    'clid' => $setarr['clid'],
                    'do' => 'add_collectuer',
                    'do_obj' => $collectname,
                    'dateline' => TIMESTAMP
                ];
                C::t('pichome_collectevent')->insert($enventdata);
            }
        }
        return true;
    }

    //移除收藏夹用户
    public function delete_user_to_collect($uid, $clid)
    {
        $permtitle = ['参与者', '协作成员', '管理员', '创建者'];
        $perm = C::t('pichome_collectuser')->get_perm_by_clid($clid);
        if ($perm < 3 && $uid != getglobal('uid')) {
            return array('error' => 'no_perm');
        }
        $data = DB::fetch_first("select * from %t where uid = %d and clid = %d", array($this->_table, $uid, $clid));
        if (!$data) return true;
        //如果管理员个数小于1
        if ($data['perm'] > 2 && (DB::result_first("select count(cu.uid) from %t  cu left join %t u on cu.uid=u.uid where cu.clid = %d and (cu.perm > %d or u.adminid = 1)", array($this->_table,'user',$clid, 2)) < 2)) {
            return array('error' => 'manager_is_must');
        }

        if (parent::delete($data['id'])) {
            $collectname = DB::result_first("select name from %t where clid = %d", array('pichome_collect', $clid));
            $objusername = DB::result_first("select username from %t where uid = %d", array('user', $uid));
            //添加事件
            $enventbodydata = ['username' => getglobal('username'), 'name' => $collectname, 'objusername' => $objusername, 'permtitle' => $permtitle[$setarr['perm']]];
            $enventdata = [
                'eventbody' => 'delcollectuer',
                'uid' => getglobal('uid'),
                'username' => getglobal('username'),
                'bodydata' => json_encode($enventbodydata),
                'clid' => $setarr['clid'],
                'do' => 'del_collectuer',
                'do_obj' => $collectname,
                'dateline' => TIMESTAMP
            ];
            C::t('pichome_collectevent')->insert($enventdata);
        }

        return true;
    }

}
