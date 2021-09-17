<?php
    if (!defined('IN_OAOOA')) {
        exit('Access Denied');
    }
    
    class table_pichome_resources extends dzz_table
    {
        public function __construct()
        {
            
            $this->_table = 'pichome_resources';
            $this->_pk = 'rid';
            $this->_pre_cache_key = 'pichome_resources';
            $this->_cache_ttl = 3600;
            parent::__construct();
        }
        
        public function insert($setarr)
        {
            if (DB::result_first("select count(*) from %t where rid = %s", array($this->_table, $setarr['rid']))) {
                $rid = $setarr['rid'];
                unset($setarr['rid']);
                return parent::update($rid, $setarr);
            } else {
                return parent::insert($setarr);
            }
        }
        
        public function delete_by_appid($appid)
        {
            $rids = [];
            foreach (DB::fetch_all("select rid from %t where appid = %s", array($this->_table, $appid)) as $v) {
                $rids[] = $v['rid'];
            }
            return $this->delete_by_rid($rids);
        }
        
        public function fetch_by_path($path)
        {
            $path = trim($path);
            return DB::result_first("select * from %t  where  path = %s", array($this->_table, $path));
        }
        
        public function delete_by_rid($rids)
        {
            if (!is_array($rids)) $rids = (array)$rids;
            C::t('pichome_resources_attr')->delete_by_rid($rids);
            C::t('pichome_folderresources')->delete_by_rid($rids);
            C::t('pichome_palette')->delete_by_rid($rids);
            C::t('pichome_comments')->delete_by_rid($rids);
            C::t('pichome_resourcestag')->delete_by_rid($rids);
            C::t('pichome_share')->delete_by_rid($rids);
            return parent::delete($rids);
        }
        
        //以rid获取文件缩略图
        public function get_icon_by_rids($rids)
        {
            if (!is_array($rids)) $rids = (array)$rids;
            $icondatas = [];
            foreach (DB::fetch_all("select r.rid,r.type,r.hasthumb,ra.path from  %t  r left join %t  ra on r.rid=ra.rid where rid in(%n) ", array($this->_table, $rids)) as $v) {
                if ($v['hasthumb']) {
                    $filename = str_replace(strrchr($v['name'], "."), "", $v['name']);
                    $filepath = dirname($v['path']);
                    $thumbpath = $filepath . $filename . '_thumb.png';
                    // if (!file_exists($thumbpath)) $thumbpath = iconv('UTF-8', 'GB2312', $thumbpath);
                    $icondatas[$v['rid']] = $thumbpath;
                } else {
                    if ($v['type'] == 'commonimage') {
                        $v['icondata'] = $v['path'];
                    } else {
                        $v['icondata'] = geticonfromext($v['ext'], $v['type']);
                    }
                }
            }
            return $icondatas;
        }
        
        public function fetch_by_rids($rids)
        {
            global $Opentype;
            if (!is_array($rids)) $rids = (array)$rids;
            
            $datas = $tmpdatas = [];
            foreach (parent::fetch_all($rids) as $v) {
                $v['fsize'] = formatsize($v['size']);
                $v['mtime'] = dgmdate(round($v['mtime'] / 1000), 'Y/m/d H:i');
                $v['dateline'] = dgmdate(round($v['dateline'] / 1000), 'Y/m/d H:i');
                $v['name'] = str_replace(strrchr($v['name'], "."), "", $v['name']);
                $v['btime'] = dgmdate(round($v['btime'] / 1000), 'Y/m/d H:i');
                $v['dpath'] = dzzencode($v['rid'], '', 0);
                if (in_array($v['ext'], $Opentype['video'])) {
                    $v['opentype'] = 'video';
                } elseif (in_array($v['ext'], $Opentype['text'])) {
                    $v['opentype'] = 'text';
                } elseif (in_array($v['ext'], $Opentype['pdf'])) {
                    $v['opentype'] = 'pdf';
                } elseif (in_array($v['ext'], $Opentype['image'])) {
                    $v['opentype'] = 'image';
                } else {
                    $v['opentype'] = 'other';
                }
                $tmpdatas[$v['rid']] = $v;
            }
            foreach($rids as $rid){
                $datas[$rid] = $tmpdatas[$rid];
            }
            foreach (C::t('pichome_resources_attr')->fetch_all($rids) as $v) {
                if ($datas[$v['rid']]['hasthumb']) {
                    
                    $filepath = dirname($v['path']);
                    $filename =  substr($v['path'],strrpos($v['path'],'/')+1);
                    $filename = str_replace(strrchr($filename, "."),"",$filename);
                    $thumbpath =  'library/' . $filepath . '/' . $filename . '_thumbnail.png';
                    $datas[$v['rid']]['icondata'] = str_replace('+',' ',urlencode($thumbpath));
                }
                else {
                    if ($datas[$v['rid']]['type'] == 'commonimage') {
                        $datas[$v['rid']]['icondata'] =  str_replace('+',' ',urlencode('library/' . $v['path']));
                    } else {
                        $datas[$v['rid']]['icondata'] = geticonfromext($datas[$v['rid']]['ext'], $datas[$v['rid']]['type']);
                    }
                }
                $datas[$v['rid']]['realpath'] =  str_replace('+',' ',urlencode('library/' . $v['path']));
                $colorsarr = [];
                //获取颜色数据
                $colordata = C::t('pichome_palette')->fetch_colordata_by_rid($v['rid']);
                foreach ($colordata as $cv) {
                    $colorsarr[] = $cv;
                }
                $datas[$v['rid']]['color'] = $colorsarr[0];
                $datas[$v['rid']]['link'] = $v['link'];
            }
            //array_multisort($datas, 'rid', SORT_ASC, $rids);
            foreach (C::t('pichome_resourcestag')->fetch_all_tag_by_rids($rids) as $k => $v) {
                $datas[$k]['tags'] = $v;
                // $datas[$k]['tags'] = '•'.implode('•',$v);
            }
            
            return $datas;
        }
        
        
        public function fetch_by_rid($rid)
        {
            global $Opentype;
            if (!$resourcesdata = parent::fetch($rid)) return array();
            if ($resourcesdata['isdelete'] > 0) return array();
            $attrdata = C::t('pichome_resources_attr')->fetch($rid);
            $resourcesdata = array_merge($resourcesdata, $attrdata);
            $resourcesdata['colors'] = C::t('pichome_palette')->fetch_colordata_by_rid($rid);
            if (in_array($resourcesdata['ext'], $Opentype['video'])) {
                $resourcesdata['opentype'] = 'video';
            } elseif (in_array($resourcesdata['ext'], $Opentype['text'])) {
                $resourcesdata['opentype'] = 'text';
            } elseif (in_array($resourcesdata['ext'], $Opentype['pdf'])) {
                $resourcesdata['opentype'] = 'pdf';
            } elseif (in_array($resourcesdata['ext'], $Opentype['image'])) {
                $resourcesdata['opentype'] = 'image';
            } else {
                $resourcesdata['opentype'] = 'other';
            }
            if ($resourcesdata['hasthumb']) {
                $filepath = dirname($resourcesdata['path']);
                $filename =  substr($resourcesdata['path'],strrpos($resourcesdata['path'],'/')+1);
                $filename = str_replace(strrchr($filename, "."),"",$filename);
                $thumbpath =  'library/' . $filepath . '/' . $filename . '_thumbnail.png';
                $resourcesdata['icondata'] = $thumbpath;
                //}
            } else {
                if ($resourcesdata['type'] == 'commonimage') {
                    $resourcesdata['icondata'] = 'library/' . $resourcesdata['path'];
                    
                } else {
                    $resourcesdata['icondata'] = geticonfromext($resourcesdata['ext'], $resourcesdata['type']);
                }
            }
            $imginfo = @getimagesize($resourcesdata['icondata']);
            $resourcesdata['iconwidth'] = $imginfo[0];
            $resourcesdata['iconheight'] = $imginfo[1];
            $resourcesdata['icondata'] =  str_replace('+',' ',urlencode($resourcesdata['icondata']));
            
            //获取所有库分享和下载权限
            $downshare = C::t('pichome_vapp')->fetch_all_sharedownlod();
            $resourcesdata['share'] = $downshare[$resourcesdata['appid']]['share'];
            $resourcesdata['download'] = $downshare[$resourcesdata['appid']]['download'];
            $resourcesdata['originalimg'] =  str_replace('+',' ',urlencode('library/' . $resourcesdata['path']));
            $resourcesdata['realpath'] =  str_replace('+',' ',urlencode('library/' . $resourcesdata['path']));
            $resourcesdata['name'] = str_replace(strrchr($resourcesdata['name'], "."), "", $resourcesdata['name']);
            $resourcesdata['fsize'] = formatsize($resourcesdata['size']);
            $resourcesdata['mtime'] = dgmdate(round($resourcesdata['mtime'] / 1000), 'Y/m/d H:i');
            $resourcesdata['dateline'] = dgmdate(round($resourcesdata['dateline'] / 1000), 'Y/m/d H:i');
            $resourcesdata['btime'] = dgmdate(round($resourcesdata['btime'] / 1000), 'Y/m/d H:i');
            unset($resourcesdata['path']);
            $resourcesdata['foldernames'] = C::t('pichome_folderresources')->get_foldername_by_rid($rid);
            $resourcesdata['tag'] = C::t('pichome_resourcestag')->fetch_tag_by_rid($rid);
            $resourcesdata['dpath'] = dzzencode($rid, '', 0);
            
            
            return $resourcesdata;
        }
        
        public function getdatasbyrids($rids)
        {
            $returndata = [];
            //文件数据
            $resourcesdata = $this->fetch_by_rids($rids);
            //获取所有库分享和下载权限
            $downshare = C::t('pichome_vapp')->fetch_all_sharedownlod();
            //文件附属表数据
            //  $attrdata = C::t('pichome_resources_attr')->fetch_by_rids($rids);
            //文件标注数
            $annonationnumdata = C::t('pichome_comments')->fetch_annonationnum_by_rids($rids);
            foreach ($resourcesdata as $v) {
                $v['annonationnum'] = $annonationnumdata[$v['rid']]['num'];
                $v['share'] = $downshare[$v['appid']]['share'];
                $v['download'] = $downshare[$v['appid']]['download'];
                $returndata[] = $v;
            }
            return $returndata;
        }
        
        public function fetch_like_words($keyword, $limit = 10)
        {
            $likewords = [];
            $presql = " case when name like %s then 3 when name like %s then 2 when name like %s then 1 end as rn";
            $wheresql = " name like %s";
            $params = [$keyword . '%', '%' . $keyword, '%' . $keyword . '%', $this->_table, '%' . $keyword . '%'];
            foreach (DB::fetch_all("select name,$presql from %t where $wheresql order by rn desc  limit 0,$limit", $params) as $v) {
                $likewords[] = $v['name'];
            }
            return $likewords;
        }
        
    }