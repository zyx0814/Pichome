<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_pichome_templatetag extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'pichome_templatetag';
        $this->_pk = 'tid';
        $this->_pre_cache_key = 'pichome_templatetag';
        //$this->_cache_ttl = 3600;
        parent::__construct();
    }

    //新建或修改单页
    public function insertdata($setarr)
    {
        $tid = $setarr['tid'];
        if (!$setarr['tagtype'] || !$setarr['pageid']) return false;
        unset($setarr['tid']);
        if ($tid && $tagdata = parent::fetch($tid)) {
            if ($setarr['disp'] != $tagdata['disp']) $this->setDispBytid($tid, $setarr['disp'], 1);
            parent::update($tid, $setarr);
            return $tid;
        } else {
            if ($tid = parent::insert($setarr, 1)) {
                $this->setDispBytid($tid, $setarr['disp'], 1);
                return $tid;
            }
        }
    }

    public function setDispBytid($tid, $disp, $isincrease = 1)
    {
        if (!$tagdata = parent::fetch($tid)) return false;
        if ($isincrease) {
            if ($disp < $tagdata['disp']) {
                foreach (DB::fetch_all("select tid,disp from %t where disp >= %d and disp < %d and tid != %d and pageid  =%d", array($this->_table, $disp, $tagdata['disp'], $tagdata['tid'], $tagdata['pageid'])) as $v) {
                    $disp = intval($v['disp']) + 1;
                    parent::update($v['tid'], array('disp' => $disp));
                }
            }elseif($disp == $tagdata['disp']){
                foreach (DB::fetch_all("select tid,disp from %t where disp >= %d  and tid != %d and pageid  =%d", array($this->_table, $disp, $tagdata['tid'], $tagdata['pageid'])) as $v) {
                    $disp = intval($v['disp']) + 1;
                    parent::update($v['tid'], array('disp' => $disp));
                }
            } else {
                foreach (DB::fetch_all("select tid,disp from %t where disp >= %d and disp < %d and tid != %d and pageid  =%d", array($this->_table, $tagdata['disp'], $disp, $tagdata['tid'], $tagdata['pageid'])) as $v) {
                    $disp = $v['disp'] ? intval($v['disp']) - 1 : 0;
                    parent::update($v['tid'], array('disp' => $disp));
                }
            }

        } else {
            foreach (DB::fetch_all("select tid,disp from %t where disp > %d  and tid != %d and pageid  =%d", array($this->_table, $disp, $tagdata['tid'], $tagdata['pageid'])) as $v) {
                $disp = $v['disp'] ? intval($v['disp']) - 1 : 0;
                parent::update($v['tid'], array('disp' => $disp));
            }

        }

        return true;
    }

    public function delete_by_pageid($pageid)
    {
        foreach (DB::fetch_all("select tid from %t where pageid = %d", [$this->_table, $pageid]) as $v) {
            $this->delete_by_tid($v['tid']);
        }
        return true;
    }

    public function delete_by_tid($tid)
    {
        if (!$tagdata = parent::fetch($tid)) return true;
        C::t('pichome_templatetagtheme')->delete_by_tid($tid);
        C::t('pichome_templatetagdata')->delete_by_tid($tid);
        $this->setDispBytid($tid, $tagdata['disp'], 0);
        return parent::delete($tid);
    }

    public function fetch_by_pageid($pageid)
    {
        global $_G;
        $themeid = isset($_G['setting']['pichometheme']) ? intval($_G['setting']['pichometheme']) : 1;
        $tagdatas = [];
        foreach (DB::fetch_all("select t.tagtype,t.tagname,t.tid,t.disp,t.dateline,th.style from %t t 
left join %t th on t.tid = th.tid and th.themeid = %d where t.pageid = %d order by t.disp,t.tid asc", [$this->_table, 'pichome_templatetagtheme', $themeid, $pageid]) as $v) {
            $v['type'] = $v['tagtype'];
            unset($v['tagtype']);
            $v['title'] = $v['tagname'];
            unset($v['tagname']);
            if (!$v['style']) $v['style'] = '';
            else $v['style'] = unserialize($v['style']);
            if (!$v['size']) $v['size'] = 0;
            $v['data'] = C::t('pichome_templatetagdata')->fetch_data_by_tidandtagtype($v['tid'], $v['type']);
            $tagdatas[] = $v;
        }
        return $tagdatas;
    }

    public function fetch_by_tagflag($themeid, $tagflags)
    {
        $tagdata = [];
        foreach (DB::fetch_all("select * from %t where themeid = %d and tagflag in(%n)", array($this->_table, $themeid, $tagflags)) as $v) {
            $val = [];
            if ($v['tagtype'] == 'banner') {
                $tmpval = unserialize($v['tagval']);
                foreach ($tmpval as $tv) {
                    $val[] = ['img' => getglobal('siteurl') . 'index.php?mod=io&op=getfileStream&path=' . dzzencode('attach::' . $tv['aid']), 'link' => $tv['link']];
                }
            } elseif ($v['tagtype'] == 'recommend') {
                $tmpval = unserialize($v['tagval']);
                foreach ($tmpval as $tv) {
                    $val[] = [name => $tv['name'], data => $tv['dataval']];
                }
            } elseif ($v['tagtype'] == 'case') {
                $tmpval = unserialize($v['tagval']);
                foreach ($tmpval as $tv) {
                    $val[] = ['img' => getglobal('siteurl') . 'index.php?mod=io&op=getfileStream&path=' . dzzencode('attach::' . $tv['aid']), 'link' => $tv['link'], 'name' => $tv['name']];
                }
            } elseif ($v['tagtype'] == 'introduce') {
                $tmpval = $this->parseintroducedata(unserialize($v['tagval']));
                $val = $tmpval;
            } elseif ($v['tagtype'] == 'images') {
                $tmpval = unserialize($v['tagval']);
                foreach ($tmpval as $tv) {
                    $val[] = getglobal('siteurl') . 'index.php?mod=io&op=getfileStream&path=' . dzzencode('attach::' . $tv['aid']);
                }
            } elseif ($v['tagtype'] == 'keyvalue') {
                $tmpval = unserialize($v['tagval']);
                $val = $tmpval;

            }
            $tagdata[$v['tagflag']] = $val;
        }
        return $tagdata;
    }

    //获取所有标签位
    public function fetch_tag_by_pageid($pageid)
    {
        $themeid = isset($_G['setting']['pichometheme']) ? intval($_G['setting']['pichometheme']) : 1;
        $tagdata = [];
        foreach (DB::fetch_all("select t.tagtype,t.tagname,t.tid,t.disp,t.dateline,th.style from %t t 
left join %t th on t.tid = th.tid and th.themeid = %d where t.pageid = %d order by t.disp asc,t.tid asc", [$this->_table, 'pichome_templatetagtheme', $themeid, $pageid]) as $v) {
            if (!$v['style']) $v['style'] = '';
            else $v['style'] = unserialize($v['style']);
            $v['data'] = C::t('pichome_templatetagdata')->fetch_data_by_tidandtagtype($v['tid'], $v['tagtype']);
            $tagdata[] = $v;
        }
        return $tagdata;
    }

}