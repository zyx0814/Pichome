<?php
/* @authorcode  codestrings
  * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */
if(!defined('IN_OAOOA') || !defined('IN_ADMIN')) {
	exit('Access Denied');
}

$sql = <<<EOF
CREATE TABLE IF NOT EXISTS pichome_search_template (
  tid int(10) NOT NULL AUTO_INCREMENT COMMENT '模板TID自增',
  title varchar(255) NOT NULL DEFAULT '' COMMENT '模板名称',
  data text NOT NULL,
  screen text NOT NULL COMMENT '筛选项',
  pagesetting text NOT NULL COMMENT '偏好设置',
  searchRange text NOT NULL COMMENT '搜索范围',
  exts text NOT NULL COMMENT '限制的文件后缀',
  dateline int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  disp smallint(6) NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (tid),
  KEY disp (disp,dateline) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `pichome_search_template` (`tid`, `title`, `data`, `screen`, `pagesetting`, `searchRange`, `exts`, `dateline`, `disp`) VALUES
(3, '音频', '', '[{\"key\":\"ext\",\"label\":\"\\u7c7b\\u578b\"},{\"key\":\"tag\",\"label\":\"\\u6807\\u7b7e\",\"group\":\"\",\"auto\":\"0\",\"sort\":\"hot\"},{\"key\":\"duration\",\"label\":\"\\u65f6\\u957f\"}]', '{\"layout\":\"imageList\",\"display\":[\"name\",\"extension\"],\"other\":\"btime\",\"sort\":\"btime\",\"desc\":\"desc\",\"opentype\":\"current\",\"filterstyle\":\"1\"}', '', 'wav,ogg,mp3,m4a,flac,aac,ape,aiff,amr', 1709696619, 3),
(1, '综合', '', '[{\"key\":\"tag\",\"label\":\"\\u6807\\u7b7e\",\"group\":\"\",\"auto\":\"0\",\"sort\":\"hot\"}]', '{\"layout\":\"details\",\"display\":[\"name\",\"extension\"],\"other\":\"btime\",\"sort\":\"btime\",\"desc\":\"desc\",\"opentype\":\"current\",\"filterstyle\":\"1\"}', '', '', 1709696484, 0),
(2, '图片', '', '[{\"key\":\"color\",\"label\":\"\\u989c\\u8272\"},{\"key\":\"link\",\"label\":\"\\u94fe\\u63a5\"},{\"key\":\"desc\",\"label\":\"\\u6ce8\\u91ca\"},{\"key\":\"duration\",\"label\":\"\\u65f6\\u957f\"},{\"key\":\"size\",\"label\":\"\\u5c3a\\u5bf8\"},{\"key\":\"ext\",\"label\":\"\\u7c7b\\u578b\"},{\"key\":\"shape\",\"label\":\"\\u5f62\\u72b6\"},{\"key\":\"grade\",\"label\":\"\\u8bc4\\u5206\"},{\"key\":\"btime\",\"label\":\"\\u6dfb\\u52a0\\u65f6\\u95f4\"},{\"key\":\"dateline\",\"label\":\"\\u4fee\\u6539\\u65e5\\u671f\"},{\"key\":\"mtime\",\"label\":\"\\u521b\\u5efa\\u65e5\\u671f\"},{\"key\":\"level\",\"label\":\"\\u5bc6\\u7ea7\"},{\"key\":\"tag\",\"label\":\"\\u6807\\u7b7e\",\"group\":\"\",\"auto\":\"0\",\"sort\":\"hot\"}]', '{\"layout\":\"waterFall\",\"other\":\"btime\",\"sort\":\"btime\",\"desc\":\"desc\",\"opentype\":\"current\",\"filterstyle\":\"0\"}', '', 'svg,png,jpg,jpeg,jpe,webp,jfif,ico,heic,gif,eps,bmp,tga,hdr,exr,dds,ppm,pnm,pgm,pdd,pcx,pbm,pam,mpo,mng,miff,jpx,jps,jpf,jpc,jp2,j2k,j2c,dib,cur,cin,tif,wmf,emf,tiff,psd,ai,3fr,arw,cr2,cr3,crw,dng,erf,mrw,nef,nrw,orf,otf,pef,raf,raw,rw2,sr2,srw,x3f', 1709696566, 2),
(4, '视频', '', '[{\"key\":\"duration\",\"label\":\"\\u65f6\\u957f\"},{\"key\":\"shape\",\"label\":\"\\u5f62\\u72b6\"},{\"key\":\"tag\",\"label\":\"\\u6807\\u7b7e\",\"group\":\"\",\"auto\":\"0\",\"sort\":\"hot\"}]', '{\"layout\":\"rowGrid\",\"other\":\"btime\",\"sort\":\"btime\",\"desc\":\"desc\",\"opentype\":\"current\",\"filterstyle\":\"1\"}', '', 'wmv,webm,mp4,mov,m4v,avi,ts,swf,rmvb,rm,mkv,flv,vob,trp,sct,ogv,mxf,mpg,m2ts,f4v,dv,dcr,asf,3g2p', 1709696675, 4),
(5, '文档', '', '[{\"key\":\"ext\",\"label\":\"\\u7c7b\\u578b\"},{\"key\":\"tag\",\"label\":\"\\u6807\\u7b7e\",\"group\":\"\",\"auto\":\"0\",\"sort\":\"hot\"}]', '{\"layout\":\"tabodd\",\"display\":[\"name\",\"extension\",\"other\"],\"other\":\"filesize\",\"sort\":\"btime\",\"desc\":\"desc\",\"opentype\":\"current\",\"filterstyle\":\"1\"}', '', 'xlsx,xls,pptx,ppt,pdf,docx,doc,pdf,txt,rtf,odt,htm,html,mht,pps,ppsx,odp,ods,csv', 1709696731, 5),
(6, '其它', '', '[{\"key\":\"ext\",\"label\":\"\\u7c7b\\u578b\"},{\"key\":\"tag\",\"label\":\"\\u6807\\u7b7e\",\"group\":\"\",\"auto\":\"0\",\"sort\":\"hot\"}]', '{\"layout\":\"tabodd\",\"display\":[\"name\",\"extension\"],\"other\":\"btime\",\"sort\":\"btime\",\"desc\":\"desc\",\"opentype\":\"current\",\"filterstyle\":\"1\"}', '', 'zip,rar,7z', 1709696795, 6);

EOF;

runquery($sql);
$finish = true;
