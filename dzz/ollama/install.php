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

CREATE TABLE IF NOT EXISTS pichome_ollama_chat (
  id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  idtype tinyint(1) UNSIGNED DEFAULT '0' COMMENT '0,aid,1,rid,2对话id',
  idval char(32) DEFAULT NULL COMMENT 'id值',
  uid int(11) UNSIGNED DEFAULT '0' COMMENT '用户id',
  role char(15) DEFAULT NULL COMMENT '角色，user,assistant',
  dateline int(11) UNSIGNED DEFAULT '0' COMMENT '时间',
  content mediumtext COMMENT '内容',
  totaltoken int(11) UNSIGNED DEFAULT '0' COMMENT '消耗token数',
  PRIMARY KEY (id) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS pichome_ollama_imageprompt (
  id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  name varchar(60) DEFAULT '',
  prompt text,
  prompts text NOT NULL COMMENT 'json数据',
  cate tinyint(1) DEFAULT NULL COMMENT '0，文件名，1标签，2描述，3标签分类',
  isdefault tinyint(1) UNSIGNED DEFAULT '0' COMMENT '是否默认',
  status tinyint(1) UNSIGNED DEFAULT '0' COMMENT '是否开启',
  disp int(11) UNSIGNED DEFAULT '0' COMMENT '排序数字越小越靠前',
  dateline int(11) UNSIGNED DEFAULT '0',
  PRIMARY KEY (id) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

EOF;

runquery($sql);
$finish = true;
