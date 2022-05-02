<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_pichome_searchrecent extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'pichome_searchrecent';
        $this->_pk = 'id';
        $this->_pre_cache_key = 'pichome_searchrecent';
        $this->_cache_ttl = 3600;
        parent::__construct();
    }

    public function add_search($keyword, $appid = '', $ktype = 0)
    {
        global $_G;
        $wheresql = ' keywords = %s and ktype=%d ';
        $params = array($this->_table, $keyword, $ktype);
        if ($_G['uid']) {
            $wheresql .= ' and uid = %d ';
            $params[] = $_G['uid'];
        }
        if ($appid) {
            $wheresql .= ' and appid = %s ';
            $params[] = $appid;
        }
        if ($data = DB::fetch_first("select id,hots from %t where$wheresql", $params)) {
            $hots = $data['hots'] + 1;
            return parent::update($data['id'], array('hots' => $hots, 'dateline' => TIMESTAMP));
        } else {
            $setarr = [
                'keywords' => $keyword,
                'ktype' => $ktype,
                'dateline' => TIMESTAMP,
                'uid' => isset($_G['uid']) ? $_G['uid'] : 0,
                'hots' => 1,
                'appid' => $appid
            ];
            return parent::insert($setarr);
        }
    }

    //查询最近搜索标签
    public function fetch_renctent_search_tag($appid = '', $limit = 8)
    {
        global $_G;
        $wheresql = ' s.ktype = 1 ';
        $params = array($this->_table, 'pichome_tag');
        if ($appid) {
            $wheresql .= ' and s.appid = %s ';
            $params[] = $appid;
        }
        if ($_G['uid']) {
            $wheresql .= ' and s.uid = %d';
            $params[] = $_G['uid'];
        }
        $datas = [];
        foreach (DB::fetch_all("select s.keywords,t.tid from %t  s left join %t  t on s.keywords = t.tagname where
                $wheresql  and t.tid > 0 order by s.hots desc,s.dateline desc limit 0,$limit", $params) as $v) {
            $datas[$v['tid']] = $v['keywords'];
        }
        return $datas;
    }

    public function fetch_hotkeyword_by_appid($appid = '', $limit = 8, $noids = array(), $datas = array())
    {
        /* $start = strtotime(date("Y-m-d", strtotime("-7 day")));
         $end = strtotime(dgmdate(time(), 'Y-m-d')) + 86400;*/
        $params = array($this->_table/*, $start, $end*/);

        $count = DB::result_first("select count(id) from %t  where 1 order by hots desc limit 0,$limit", $params);
        //print_r(DB::fetch_all("select keywords,id from %t  where 1 order by hots desc limit 0,$limit", $params));die;
        foreach (DB::fetch_all("select keywords,id from %t  where 1 order by hots desc limit 0,$limit", $params) as $v) {
            $data = $this->get_data_by_keyword($v['keywords']);
            if (!$data) {
                parent::delete($v['id']);
            } else {
                $datas[$v['id']] = $data;
            }
        }
        // print_r($datas);die;
        $resultcount = count($datas);
        //如果有关键词没有结果,并且结果数量大于查询出来的数量
        if ($resultcount < $limit && $count > $limit) {
            $ids = array_keys($datas);
            $limit = $limit - $resultcount;
            $datas = $this->fetch_hotkeyword_by_appid($appid, $limit, $ids, $datas);
        }
        return $datas;
    }

    public function get_data_by_keyword($keyword)
    {
        $rid = DB::result_first("select r.rid from %t  r left join %t ra on ra.rid=r.rid 
                         where ra.searchval  LIKE %s order by rand() limit 0,1 ",
            array('pichome_resources', 'pichome_resources_attr', '%' . $keyword . '%'));

        $tmpdata = C::t('pichome_resources')->geticondata_by_rid($rid);

        if (!$tmpdata) {
            return false;
        } else {
            $tmpdata['keyword'] = $keyword;
        }
        return $tmpdata;
    }

    //查询最近搜索分类
    public function fetch_renctent_search_foldername($appid, $limit = 10)
    {
        global $_G;
        $wheresql = ' s.ktype = 2 and s.appid = %s';
        $params = array($this->_table, 'pichome_folder', $appid);

        if ($_G['uid']) {
            $wheresql .= ' and s.uid = %d';
            $params[] = $_G['uid'];
        }
        $datas = [];
        foreach (DB::fetch_all("select s.keywords,f.fid from %t  s  left join %t f on f.fname = s.keywords and f.appid = s.appid
                    where $wheresql and f.fid !=''  and s.hots > 0 order by  s.hots desc,s.dateline desc limit 0,$limit", $params) as $v) {
            $datas[$v['fid']]['name'] = $v['keywords'];
            $datas[$v['fid']]['fid'] = $v['fid'];
        }
        $fids = array_keys($datas);
        if (!empty($fids)) {
            foreach (DB::fetch_all("select f.fname,f.fid,count(fr.rid) as num  from %t  fr
                    left  join %t  f  on f.fid=fr.fid where fr.fid in(%n) group by fr.fid", array('pichome_folderresources', 'pichome_folder', $fids)) as $val) {
                $datas[$val['fid']]['sum'] = $val['num'];
                $datas[$val['fid']]['fname'] = $val['fname'];
            }
        }


        return $datas;
    }

    public function fetch_like_words($keyword, $limit = 10)
    {
        $likewords = [];
        $presql = " case when keywords like %s then 3 when keywords like %s then 2 when keywords like %s then 1 end as rn";
        $wheresql = " keywords like %s";
        $params = [$keyword . '%', '%' . $keyword, '%' . $keyword . '%', $this->_table, '%' . $keyword . '%'];
        foreach (DB::fetch_all("select keywords,$presql from %t where $wheresql order by rn desc  limit 0,$limit", $params) as $v) {
            $likewords[] = $v['keywords'];
        }
        return $likewords;
    }

    public function delete_by_appid($appid)
    {
        $delid = [];
        foreach (DB::fetch_all("select id from %t where appid = %s", array($this->_table, $appid)) as $v) {
            $delid[] = $v['id'];
        }
        return parent::delete($delid);
    }
}
