<?php
    if(!defined('IN_OAOOA')) {
        exit('Access Denied');
    }
    class table_pichome_share extends dzz_table
    {
        public function __construct()
        {
            
            $this->_table = 'pichome_share';
            $this->_pk = 'id';
            $this->_pre_cache_key = 'pichome_share';
            $this->_cache_ttl = 3600;
            parent::__construct();
        }
        
        public function add_share($rid,$stype =0,$params=array()){
            $appid='';
            $viewurl='';
            switch ($stype){
                case 0://文件
                    if(!$data= C::t('pichome_resources')->fetch($rid)){
                        return false;
                    }
                    $appid=$data['appid'];
                    $viewurl=getglobal('siteurl').'index.php?mod=pichome&op=share';
                    break;
                case 1://收藏夹文件
                    if(!$data = C::t('pichome_collectlist')->fetch($rid)){
                        return false;
                    }
                    if(!$resource= C::t('pichome_resources')->fetch($data['rid'])){
                        return false;
                    }
                    $data['name']=$resource['name'];
                    $appid=$data['appid'];
                    $viewurl=getglobal('siteurl').'index.php?mod=pichome&op=share';
                    break;
                case 2://收藏夹
                    if(!$data = C::t('pichome_collect')->fetch($rid)){
                        return false;
                    }
                    $viewurl=getglobal('siteurl').'index.php?mod=collection&op=share';
                    break;
                case 3: //专辑
                    if(!$data = C::t('pichome_tab')->fetch($rid)){
                        return false;
                    }
                    $viewurl=getglobal('siteurl').'index.php?mod=tab&op=share';
                    break;
            }

            $setarr = [
                'title'=>$data['name'],
                'filepath'=>$rid,
                'appid'=>$appid,
                'clid'=>isset($data['clid']) ? $data['clid']:0,
                'dateline'=>TIMESTAMP,
                'uid'=>getglobal('uid'),
                'username'=>getglobal('username'),
                'stype'=>$stype
            ];
            if($params['title']){
                $setarr['title']=$params['title'];
            }
            if($params['endtime']){
                $setarr['endtime']=intval($params['endtime']);
            }
            if($params['password']){
                $setarr['password']=$params['password'];
            }
            if($params['times']){
                $setarr['times']=intval($params['times']);
            }
            if($params['perm']){
                $setarr['perm']=intval($params['perm']);
            }
            if($id = DB::result_first("select id from %t where filepath = %s and uid = %d and stype = %d ",array($this->_table,$setarr['filepath'],$setarr['uid'],$setarr['stype']))){
                 parent::update($id,$setarr);
            }else{
                $id = parent::insert($setarr,1);
            }
            $shareurl = C::t('shorturl')->getShortUrl($viewurl.'&sid='.dzzencode($id,'',0,0));
            return $shareurl;
        }
        public function getShareUrl($data){
            if(!$data) {
                if(!$data = parent::fetch($data)) return false;
            }
            $viewurl='';
            switch ($data['stype']){
                case 0://文件
                    $viewurl=getglobal('siteurl').'index.php?mod=pichome&op=share';
                    break;
                case 1://收藏夹文件
                    $viewurl=getglobal('siteurl').'index.php?mod=pichome&op=share';
                    break;
                case 2://收藏夹
                    $viewurl=getglobal('siteurl').'index.php?mod=collection&op=share';
                    break;
                case 3: //专辑
                    $viewurl=getglobal('siteurl').'index.php?mod=tab&op=share';
                    break;
           }

           return C::t('shorturl')->getShortUrl($viewurl.'&sid='.dzzencode($data['id'],'',0,0));
        }
        public function fetch_by_sid($sid){
            if(!$data=parent::fetch($sid)) return false;
            $viewurl='';
            switch($data['stype']){
                case 0://文件
                    $viewurl=getglobal('siteurl').'index.php?mod=pichome&op=share';
                    break;
                case 1://收藏夹文件
                    $viewurl=getglobal('siteurl').'index.php?mod=pichome&op=share';
                    break;
                case 2://收藏夹
                    $viewurl=getglobal('siteurl').'index.php?mod=collection&op=share';
                    break;
                case 3: //专辑
                    $viewurl=getglobal('siteurl').'index.php?mod=tab&op=share';
                    break;
            }

            $data['shareurl'] = C::t('shorturl')->getShortUrl($viewurl.'&sid='.dzzencode($sid,'',0,0));
            $data['fshareurl'] = $data['shareurl'].($data['password'] ? '  提取密码：'.$data['password']:'');
            $data['fdateline']=dgmdate($data['dateline'],'Y-m-d H:i');
            if($data['endtime']) $data['fendtime']=dgmdate($data['endtime'],'Y-m-d');
            $data['fstatus']=lang('share_status_'.$data['status']);
            $data['fstype']=lang('share_stype_'.$data['stype']);
            $data['fperm']=lang('share_perm_'.$data['perm']);
            $data['qrcodeurl']='';
            $data['isqrcode']=false;
            return $data;
        }
        public function getQRcodeBySid($sid){
            if(!$data = parent::fetch($sid)) return false;
            $viewurl='';
            switch ($data['stype']){
                case 0://文件
                    $viewurl=getglobal('siteurl').'index.php?mod=pichome&op=share';
                    break;
                case 1://收藏夹文件
                    $viewurl=getglobal('siteurl').'index.php?mod=pichome&op=share';
                    break;
                case 2://收藏夹
                    $viewurl=getglobal('siteurl').'index.php?mod=collection&op=share';
                    break;
                case 3: //专辑
                    $viewurl=getglobal('siteurl').'index.php?mod=tab&op=share';
                    break;
            }
            return C::t('shorturl')->getQrcodeUrl($viewurl.'&sid='.dzzencode($sid,'',0,0));
        }
        public function count_by_uid($uid=0){
            if(!$uid) $uid = getglobal('uid');
            return DB::result_first("select count(*) from %t where uid = %d",array($this->_table,$uid));
        }
        public function delete_by_riduid($rid){
            $uid = getglobal('uid') ? getglobal('uid'):0;
            $id = DB::result_first("select id from %t where filepath = %s and uid = %d",array($this->_table,$rid,$uid));
            if($id) parent::delete($id);
            return true;
        }
        public function delete_by_rid($rid){
            if(!is_array($rid)) $rid = (array) $rid;
            $uid = getglobal('uid') ? getglobal('uid'):0;
            $ids = [];
            foreach(DB::fetch_all("select id from %t where filepath in(%n) ",array($this->_table,$rid,$uid)) as $v){
                $ids[] = $v['id'];
            }
            if(!empty($ids)) parent::delete($ids);
            return true;
        }
        public function fetch_by_idandtype($id,$stype=0){
            if($stype != 2){
                $data =parent::fetch($id);
            }else{
                $data = DB::fetch_first("select * from %t where id = %d and stype = %d",array($this->_table,$id,$stype));
                return $data;
            }
            if(!$data) return false;
            if($data['stype'] == 0){

                $resourcesdata  = C::t('pichome_resources')->fetch_by_rid($data['filepath'],1);
            }elseif($data['stype'] == 1){
                $rid = DB::result_first("select rid from %t where id = %d",array('pichome_collectlist',$data['filepath']));
                $resourcesdata  = C::t('pichome_resources')->fetch_by_rid($rid);
            }

            if(empty($resourcesdata)){
                parent::delete($id);
                return false;
            }else{
                $data['resourcesdata'] = $resourcesdata;
            }
            return $data;
        }
  
    }