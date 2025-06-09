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
DROP TABLE IF EXISTS `pichome_lang`;
CREATE TABLE `pichome_lang` (
  `skey` varchar(255) NOT NULL,
  `idtype` tinyint(1) NOT NULL COMMENT '0,文件，1tab字段，2tab字段值，3标签分类,4标签，5库',
  `svalue` mediumtext,
  `idvalue` char(32) DEFAULT NULL COMMENT 'id值',
  `valtype` tinyint(1) UNSIGNED DEFAULT '0' COMMENT '0值，1搜索',
  `filed` varchar(100) DEFAULT '' COMMENT '字段值',
  `dateline` int(11) unsigned DEFAULT '0' COMMENT '更新时间',
  `deldate` int(11) unsigned DEFAULT '0' COMMENT '删除时间',
  `chkdate` int(11) unsigned DEFAULT '0' COMMENT '检查时间',
  PRIMARY KEY (`skey`) USING BTREE,
  KEY `idtype` (`idtype`),
  KEY `idval` (`idvalue`),
  KEY `valtype` (`valtype`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pichome_lang_en_us`;
CREATE TABLE `pichome_lang_en_us` (
  `skey` varchar(255) NOT NULL,
  `idtype` tinyint(1) NOT NULL COMMENT '',
  `idvalue` char(32) DEFAULT NULL,
  `svalue` mediumtext,
  `valtype` tinyint(1) UNSIGNED DEFAULT '0',
  `filed` varchar(100) DEFAULT '' COMMENT '字段值',
  `dateline` int(11) unsigned DEFAULT '0' COMMENT '更新时间',
  `deldate` int(11) unsigned DEFAULT '0' COMMENT '删除时间',
  `chkdate` int(11) unsigned DEFAULT '0' COMMENT '检查时间',
  PRIMARY KEY (`skey`) USING BTREE,
  KEY `idtype` (`idtype`),
  KEY `idvalue` (`idvalue`),
  KEY `valtype` (`valtype`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pichome_lang_zh_cn`;
CREATE TABLE `pichome_lang_zh_cn` (
  `skey` varchar(255) NOT NULL,
  `idtype` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0,文件，1tab字段，2tab字段值，3标签分类,4标签，5库',
  `idvalue` char(32) DEFAULT NULL,
  `svalue` mediumtext,
  `valtype` tinyint(1) UNSIGNED DEFAULT '0',
  `filed` varchar(100) DEFAULT '' COMMENT '字段值',
  `dateline` int(11) unsigned DEFAULT '0' COMMENT '更新时间',
  `deldate` int(11) unsigned DEFAULT '0' COMMENT '删除时间',
  `chkdate` int(11) unsigned DEFAULT '0' COMMENT '检查时间',
  PRIMARY KEY (`skey`) USING BTREE,
  KEY `idtype` (`idtype`),
  KEY `idvalue` (`idvalue`),
  KEY `valtype` (`valtype`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pichome_lang_search`;
CREATE TABLE `pichome_lang_search` (
  `skey` varchar(50) NOT NULL,
  `idtype` tinyint(1) UNSIGNED DEFAULT '0',
  `idvalue` char(32) DEFAULT NULL,
  `svalue` mediumtext,
  `lang` varchar(60) DEFAULT NULL COMMENT '对应语言',
  `dateline` int(11) unsigned DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`skey`),
  KEY `idtype` (`idtype`),
  KEY `idvalue` (`idvalue`),
  FULLTEXT KEY `svalue` (`svalue`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pichome_language`;
CREATE TABLE `pichome_language`  (
  `langflag` char(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '语言标识',
  `langval` char(60) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `langname` varchar(120) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '语言名字',
  `icon` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '语言图标',
  `isdefault` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '是否默认',
  `state` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '是否开启',
  `elementflag` char(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '前端语言包',
  PRIMARY KEY (`langflag`) USING BTREE
) ENGINE = MyISAM;

DROP TABLE IF EXISTS `pichome_lang_file`;
CREATE TABLE `pichome_lang_file`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `rid` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '文件id',
  `lang` char(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '语言',
  `dateline` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM;

EOF;

runquery($sql);
$finish = true;
