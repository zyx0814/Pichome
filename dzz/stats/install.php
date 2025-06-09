<?php
/*
 * //应用安装文件；
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_OAOOA') || !defined('IN_ADMIN')) {
    exit('Access Denied');
}

$sql = <<<EOF
ROP TABLE IF EXISTS `pichome_stats_keyword`;
CREATE TABLE `pichome_stats_keyword`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `keyword` char(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '关键词',
  `idtype` smallint(1) UNSIGNED NULL DEFAULT 0 COMMENT '0,库统计，1标签组统计，2项目关键词统计,3全部搜索',
  `idval` char(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '库id或标签组id或标签id',
  `uid` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `isadmin` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否是管理端',
  `username` char(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户名',
  `dateline` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `keyword`(`keyword`) USING BTREE,
  INDEX `gid`(`idval`) USING BTREE,
  INDEX `username`(`username`) USING BTREE,
  INDEX `uid`(`uid`) USING BTREE
) ENGINE = MyISAM;

-- ----------------------------
-- Table structure for pichome_stats_token
-- ----------------------------
DROP TABLE IF EXISTS `pichome_stats_token`;
CREATE TABLE `pichome_stats_token`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` int(11) UNSIGNED NULL DEFAULT 0,
  `dateline` int(11) UNSIGNED NULL DEFAULT 0,
  `gettype` tinyint(1) NOT NULL COMMENT '根据app定义，如果为图片理解0为获取标签，1为描述；对话则为0',
  `app` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '应用，暂时记录为应用名称',
  `totaltoken` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '消耗token总数',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM;

-- ----------------------------
-- Table structure for pichome_stats_userlogin
-- ----------------------------
DROP TABLE IF EXISTS `pichome_stats_userlogin`;
CREATE TABLE `pichome_stats_userlogin`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id',
  `username` char(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户名',
  `ip` char(15) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '登录ip',
  `dateline` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '登录时间',
  `machine` char(15) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '登录设备',
  `isadmin` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '是否为后端登录',
  `msg` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '登录提示信息',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM;

-- ----------------------------
-- Table structure for pichome_stats_view
-- ----------------------------
DROP TABLE IF EXISTS `pichome_stats_view`;
CREATE TABLE `pichome_stats_view`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `idtype` smallint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0文件，1文件下载，2卡片,3上传统计',
  `idval` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT 'id值',
  `name` char(120) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '名字',
  `uid` int(11) NOT NULL COMMENT '用户id',
  `username` char(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户名',
  `isadmin` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否后端访问',
  `dateline` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '时间',
  `ip` char(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'ip地址',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idtype`(`idtype`) USING BTREE,
  INDEX `uid`(`uid`) USING BTREE,
  INDEX `username`(`username`) USING BTREE,
  INDEX `idval`(`idval`) USING BTREE
) ENGINE = MyISAM;


EOF;
runquery($sql);

$finish = true;  //结束时必须加入此句，告诉应用安装程序已经完成自定义的安装流程
