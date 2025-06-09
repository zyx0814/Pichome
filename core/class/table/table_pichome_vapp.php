<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_pichome_vapp extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'pichome_vapp';
        $this->_pk = 'appid';
        $this->_pre_cache_key = 'pichome_vapp';
        $this->_cache_ttl = 3600;
        parent::__construct();
    }

    public function fetchByAppid($appid,$hasdel=1){
        $appdata = [];
        //不包含删除状态库
        if(!$hasdel){
            $data =  DB::fetch_first('SELECT * FROM %t WHERE appid=%s and isdelete < %d', array($this->_table, $appid,1));
        }else{
            $data = parent::fetch($appid);
        }
        Hook::listen("lang_parse",$data,['getVappLangData']);
        return $data;
    }
    public function updateByAppid($appid,$setarr){
        $setarr['appid'] = $appid;
        Hook::listen('lang_parse',$setarr,['saveVppLangData']);
        if($setarr)parent::update($appid,$setarr);
        return $appid;
    }
    private function code62($x)
    {
        $show = '';
        while ($x > 0) {
            $s = $x % 62;
            if ($s > 35) {
                $s = chr($s + 61);
            } elseif ($s > 9 && $s <= 35) {
                $s = chr($s + 55);
            }
            $show .= $s;
            $x = floor($x / 62);
        }
        return $show;
    }

    public function getSid($url)
    {
        $microtime = microtime();
        list($msec, $sec) = explode(' ', $microtime);
        $msec = $msec * 1000000;
        $url = crc32($url . $sec . random(6) . $msec);
        $result = sprintf("%u", $url);
        $sid = self::code62($result);
        $len = strlen($sid);
        if ($len < 6) {
            $sid .= random(1);
        }
        if (strlen($sid) > 6) {
            $sid = substr($sid, 0, 6);
        }
        if (DB::result_first("select appid from %t where appid = %s", array($this->_table, $sid))) {
            $sid = $this->getSid($url);
        }
        return $sid;
    }

    public function insert_data($setarr)
    {
        //如果为oaooa库时
        $path = $setarr['path'];
        if ($path && $appid = DB::result_first("select appid from %t where path = %s and isdelete = 0", array($this->_table, $setarr['path']))) {
            parent::update($appid, $setarr);
            return $appid;
        }
        //生成appid
        $setarr['appid'] = $this->getSid($path);

        if (parent::insert($setarr)) {
            $indexarr = $setarr;
            Hook::listen('addvappafter', $indexarr);
            Hook::listen('lang_parse', $setarr, ['saveVppLangData']);
            return $setarr['appid'];
        }
    }

    public function fetch_by_path($path)
    {
        return DB::fetch_first("select * from %t where path = %s", array($this->_table, $path));
    }

    //获取不重复的应用名称
    public function getNoRepeatName($name)
    {
        static $i = 0;
        if (DB::result_first("select COUNT(appid) from %t where appname=%s ", array($this->_table, $name))) {
            $name = preg_replace("/\(\d+\)/i", '', $name) . '(' . ($i + 1) . ')';
            $i += 1;
            return $this->getNoRepeatName($name);
        } else {
            return $name;
        }
    }

    //删除虚拟应用
    public function delete_vapp_by_appid($appid)
    {
        $appdata = parent::fetch($appid);
        //删除文件表数据
        C::t('pichome_resources')->delete_by_appid($appid);
        //删除目录表数据
        C::t('pichome_folder')->delete_by_appid($appid);
        //删除目录文件关系表数据
        C::t('pichome_folderresources')->delete_by_appid($appid);
        //删除标签分类表数据
        C::t('pichome_taggroup')->delete_by_appid($appid);
        //删除标签关系表数据
        C::t('pichome_tagrelation')->delete_by_appid($appid);
        //删除最近搜索表数据
        C::t('pichome_searchrecent')->delete_by_appid($appid);
        //删除库成员数据
        C::t('pichome_vappmember')->delete_by_appid($appid);
        //删除库标签数据
        C::t('pichome_folder_tag')->delete_by_appid($appid);
        C::t('pichome_resources_tag')->delete_by_appid($appid);
        C::t('pichome_vapp_tag')->delete_by_appid($appid);
        C::t('pichome_route')->delete_by_appid($appid);
        //resources表数据未完成删除前不允许删除vapp表
        if (DB::result_first("select count(rid) from %t where appid = %s", array('pichome_resources', $appid))) {
            return;
        }

        $hookdata = ['appid' => $appid, 'apptype' => $appdata['type']];
        Hook::listen('pichomevappdelete', $hookdata);
        if(parent::delete($appid)){
            Hook::listen('lang_parse',$appid,['delVappLangData']);
            return true;
        }



    }

    public function getpermbypermdata($permdata,$appid, $perm = '')
    {
        global $_G;
        $permdata = ($permdata === '1') ? '1':unserialize($permdata);
        $uid = isset($_G['uid']) ? $_G['uid'] : 0;
        if($_G['amidnid'] == 1 || C::t('pichome_vappmember')->checkuserperm_by_appid($appid)){
            $adminperm = 1;
        }else{
            $adminperm = 0;
        }
        if ($adminperm) return true;

        if ($perm == 'download' && (isset($_G['config']['pichomeclosedownload']) && $_G['config']['pichomeclosedownload'])) {
            return false;
        }

        if ($perm == 'share' && (isset($_G['config']['pichomecloseshare']) && $_G['config']['pichomecloseshare'])) {
            return false;
        }

        if (!$permdata) return false;
        if ($permdata === '1') return true;
        $uorgids = [];
        if ($uid && $_G['adminid'] != 1) {
            //获取用户机构部门数据
            foreach (DB::fetch_all("select ou.orgid,o.pathkey from %t ou left join %t o on o.orgid=ou.orgid 
                where ou.uid = %d", array('organization_user', 'organization', $uid)) as $v) {
                $tmporgids = explode(',', str_replace('-', '', $v['pathkey']));
                $torgids = [];
                foreach ($tmporgids as $ov) {
                    $tmpgid = explode('_', $ov);
                    $torgids = array_merge($torgids, $tmpgid);
                }
                $torgids = array_unique(array_filter($torgids));
                $uorgids = array_merge($uorgids, $torgids);
            }
        }
        $hasother = false;
        //判断是否包含无用户组用户
        if (isset($permdata['groups'])) {
            if (in_array('other', $permdata['groups'])) {
                $otherindex = array_search('other', $permdata['groups']);
                unset($permdata['groups'][$otherindex]);
                $hasother = true;
            }
        }
        //判断有权限用户中是否有当前用户
        if ($permdata['uids'] || $hasother) {
            //查询无组用户
            if ($hasother) {
                foreach (DB::fetch_all("select u.uid from %t u left join %t ou on u.uid=ou.uid where 1", array('user', 'organization_user')) as $u) {
                    $permdata['uids'][] = $u['uid'];
                }
            }
            if (in_array($uid, $permdata['uids'])) return true;
        }
        //判断有权限组中是否有当前用户
        if ($permdata['groups']) {
            $intersectarr = array_intersect($permdata['groups'], $uorgids);
            if (!empty($intersectarr)) return true;
        }
        return false;

    }

    public function fetch_all_sharedownlod($appid = '')
    {
        $downshare = array();
        if ($appid) {
            $downshare = DB::fetch_first("select * from %t where isdelete < 1 and appid = %s", array($this->_table, $appid));
           /* $downshare['download'] =$downshare['download'];
            $downshare['share'] =$downshare['share'];
            $downshare['view'] =$downshare['view'];*/
        } else {

            foreach (DB::fetch_all("select * from %t where isdelete < 1", array($this->_table)) as $v) {

             /*   $v['download'] =$v['download'];
                $v['share'] = $v['share'];
                $v['view'] = $v['view'];*/
                $downshare[$v['appid']] = $v;
            }
        }
        return $downshare;
    }

    public function add_getinfonum_by_appid($appid, $ceof = 1)
    {
        $appdata = C::t('pichome_vapp')->fetch($appid);
        if ($ceof < 0) {
            if ($appdata['getinfonum'] == 0) return true;
            elseif ($appdata['getinfonum'] < abs($ceof)) $ceof = -$appdata['getinfonum'];

        }

        if ($ceof > 0) {
            DB::query("update %t set getinfonum=getinfonum+%d where appid = %s", array($this->_table, $ceof, $appid));
        } else {
            DB::query("update %t set getinfonum=getinfonum-%d where appid = %s", array($this->_table, abs($ceof), $appid));
        }
        $this->clear_cache($appid);
    }


    public function addcopy_by_appid($appids, $ceof = 1)
    {
        if (!is_array($appids)) $appids = array($appids);

        if ($ceof > 0) {
            DB::query("update %t set filenum=filenum+%d where appid IN(%n)", array($this->_table, $ceof, $appids));
        } else {
            DB::query("update %t set filenum=filenum-%d where appid IN(%n)", array($this->_table, abs($ceof), $appids), true);
        }
        $this->clear_cache($appids);
    }

    public function fetch_all_by_type($type = -1, $isdelete = 0)
    {
        $param = array($this->table, $isdelete);
        $sql = "isdelete=%d";
        if ($type > -1) {
            $sql .= " and type=%d";
            $param[] = $type;
        }
        return DB::fetch_all("select * from %t where $sql", $param);
    }

//删除标注中的标签组字段
    public function del_fileds_by_gid($gid)
    {
        if (!$gid) return false;
        foreach (DB::fetch_all("select appid,fileds from %t where isdelete < 1", array('pichome_vapp')) as $v) {
            $appid = $v['appid'];
            $fileds = unserialize($v['fileds']);
            if ($fileds) {
                foreach ($fileds as $k => $val) {
                    if ($val['type'] == 'tabgroup' && $val['flag'] == 'tabgroup_' . $gid) {
                        unset($fileds[$k]);
                        C::t('#pichome#pichome_vapp')->update($appid, ['fileds' => serialize($fileds)]);
                    }

                }
            }
        }
    }

    //改变标注中标签组的状态
    public function update_fileds_by_gid($gid, $status = 0)
    {
        if (!$gid) return false;
        foreach (DB::fetch_all("select appid,fileds from %t where isdelete < 1", array('pichome_vapp')) as $v) {
            $appid = $v['appid'];
            $fileds = unserialize($v['fileds']);
            if ($fileds) {
                foreach ($fileds as $k => $val) {
                    if ($val['type'] == 'tabgroup' && $val['flag'] == 'tabgroup_' . $gid) {
                        $fileds[$k] = ['flag' => $v['flag'], 'type' => 'tabgroup', 'name' => $v['name'], 'checked' => $v['checked'], 'enable' => $status];
                        C::t('pichome_vapp')->update($appid, ['fileds' => serialize($fileds)]);

                    }

                }
            }
        }
    }

    //增加标注字段
    public function add_filed_to_fileds($fileddata)
    {
        if (!$fileddata) return false;
        foreach (DB::fetch_all("select appid,fileds from %t where isdelete < 1", array('pichome_vapp')) as $v) {
            $appid = $v['appid'];
            $fileds = unserialize($v['fileds']);
            if (!$fileds) $fileds = [];
            $fileds[] = $fileddata;
            C::t('pichome_vapp')->update($appid, ['fileds' => serialize($fileds)]);
        }

    }

    public function fetch_fileds_by_appid($appid,$hasfiled = 0){
        global $_G;
        $data = C::t('pichome_vapp')->fetch($appid);
        $defaultfileds = [
            [
                'flag' => 'tag',
                'type' => 'multiselect',
                'name' => lang('tag'),
                'enable' => 1,
                'checked' => 1
            ],
            [
                'flag' => 'desc',
                'type' => 'input',
                'name' => lang('describe'),
                'enable' => 1,
                'checked' => 1
            ],
            [
                'flag' => 'link',
                'type' => 'input',
                'name' => lang('link'),
                'enable' => 1,
                'checked' => 1
            ],

            [
                'flag' => 'grade',
                'type' => 'grade',
                'name' => lang('grade'),
                'enable' => 1,
                'checked' => 1
            ],
            [
                'flag' => 'fid',
                'type' => 'multiselect',
                'name' => lang('classify'),
                'enable' => 1,
                'checked' => 1
            ]
        ];
        if (defined('PICHOME_LIENCE')) {
            $defaultfileds[] = [
                'flag' => 'level',
                'type' => 'grade',
                'name' => lang('level'),
                'enable' => 0,
                'checked' => 1
            ];
        }
        if ($data['type'] == 1 || $data['type'] == 3) {
            $defaultfileds[] = [
                'flag' => 'preview',
                'type' => 'multiupload',
                'name' => lang('more_picture_preview'),
                'checked' => 0,
                'enable' => 1
            ];
        }
        $lang = '';
        Hook::listen('lang_parse', $lang, ['checklang']);
        if ($lang) {
            $langList = $_G['language_list'];
            $langoption = [['name' => 'all', 'value' => '通用']];
            foreach ($langList as $k => $v) {
                $langoption[] = ['name' => $k, 'value' => $v['langval']];
            }
            $defaultfileds[] = [
                'flag' => 'lang',
                'type' => 'select',
                'name' => lang('lang'),
                'enable' => 1,
                'checked' => 0,
                'options' => $langoption
            ];
        }
        //获取tab部分以处理默认筛选和标注字段数据
        $tabstatus = 0;
        Hook::listen('checktab', $tabstatus);
        if ($tabstatus) {//获取有tab数据
            $tabgroupdata = [];
            Hook::listen('getavailabletabdata', $tabgroupdata);
            foreach ($tabgroupdata as $v) {
                if($v['available']){
                    $defaultfileds[] = ['flag' => 'tabgroup_' . $v['gid'], 'type' => 'tabgroup', 'name' => $v['name'], 'checked' => 0];
                    $data['filter'][] = ['key' => 'tabgroup_' . $v['gid'], 'type' => 'tabgroup', 'label' => $v['name'], 'checked' => 0];
                }
            }
        }
        if ($data['type'] == 1 || $data['type'] == 3) {
            $defaultfileds[] = [
                'flag' => 'preview',
                'type' => 'multiupload',
                'name' => lang('more_picture_preview'),
                'checked' => 0,
                'enable' => 1
            ];

        }
        $data['fileds'] = $data['fileds'] ? unserialize($data['fileds']) : $defaultfileds;
        if ($data['fileds']) {
            $temp = [];
            foreach($data['fileds'] as $k=>$v){
                if(!in_array($v['flag'],$temp)) $temp[] = $v['flag'];
                else unset($data['fileds'][$k]);
            }
            //获取默认字段数据的flag
            $dfkeys =array_unique(array_column($defaultfileds, 'flag'));
            //获取当前设置标注字段的flag
            $ffkeys = array_unique(array_column($data['fileds'], 'flag'));
            //处理默认设置数据
            foreach ($ffkeys as $k => $v) {
                if(!in_array($v, $dfkeys)){
                    unset($data['fileds'][$k]);
                }else{
                    $index = array_search($v, $dfkeys);
                    $data['fileds'][$k] = [
                        'flag' => $v,
                        'type' => isset($defaultfileds[$index]['type']) ? $defaultfileds[$index]['type'] : '',
                        'name' =>  $defaultfileds[$index]['name'],
                        'checked' => $data['fileds'][$k]['checked'],
                        'enable'=>$data['fileds'][$k]['enable'],
                        'options'=>$data['fileds'][$k]['options']
                    ];
                }
            }
            foreach ($dfkeys as $k => $v) {
                if (!in_array($v, $ffkeys)) {
                    $data['fileds'][] = $defaultfileds[$k];
                }
            }
        }
        if($hasfiled){
            $fileds = array_values($data['fileds']);
        }else{
            $fileds = [];
            foreach($data['fileds'] as $k=>$v){
                if($v['checked']){
                    $fileds[] = $v['flag'];
                }
            }
        }

        return $fileds;
    }


}