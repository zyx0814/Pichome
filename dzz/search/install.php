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


EOF;

runquery($sql);
$finish = true;
