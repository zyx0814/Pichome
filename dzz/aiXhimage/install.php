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

CREATE TABLE IF NOT EXISTS `pichome_ai_imageparse`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `aid` int(11) UNSIGNED NULL DEFAULT 0,
  `tplid` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '模板id',
  `rid` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '文件id',
  `aikey` char(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'ai标识',
  `gettype` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '获取方式',
  `dateline` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '添加时间',
  `isget` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '是否已经获取到',
  `data` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `totaltoken` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '总消耗token数',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `rid`(`rid`) USING BTREE,
  INDEX `aid`(`aid`) USING BTREE,
  INDEX `isget`(`isget`) USING BTREE
) ENGINE = MyISAM;

DROP TABLE IF EXISTS `pichome_ai_xhchat`;
CREATE TABLE `pichome_ai_xhchat`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `idtype` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '0,aid,1,rid,2对话id',
  `idval` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'id值',
  `uid` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '用户id',
  `role` char(15) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '角色，user,assistant',
  `dateline` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '时间',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '内容',
  `totaltoken` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '消耗token数',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM;

DROP TABLE IF EXISTS `pichome_ai_xhimageprompt`;
CREATE TABLE `pichome_ai_xhimageprompt`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` char(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `prompt` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `cate` tinyint(1) NULL DEFAULT NULL COMMENT '0，文件名，1标签，2描述，3标签分类',
  `isdefault` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '是否默认',
  `status` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '是否开启',
  `disp` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '排序数字越小越靠前',
  `dateline` int(11) UNSIGNED NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM;
EOF;

runquery($sql);
$finish = true;
