<?php
    /* @authorcode  codestrings
     * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
     * @license     https://www.dzz.com/licenses/
     *
     * @link        https://www.dzz.com
     * @author      zyx(zyx@dzz.com)
     */
    if (!defined('IN_OAOOA') || !defined('IN_ADMIN')) {
        exit('Access Denied');
    }
    
    $sql = <<<EOF

DROP TABLE IF EXISTS `dzz_pichome_comments`;
CREATE TABLE `dzz_pichome_comments` (
  `id` char(19) NOT NULL DEFAULT '' COMMENT '标注id',
  `x` float(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT 'x轴位置',
  `y` float(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT 'y轴位置',
  `width` float(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '宽',
  `height` float(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '高度',
  `annotation` varchar(255) NOT NULL DEFAULT '' COMMENT '标注内容',
  `lastModified` char(13) NOT NULL DEFAULT '' COMMENT '最后更改时间',
  `appid` char(19) NOT NULL DEFAULT '' COMMENT '库id',
  `rid` char(19) NOT NULL DEFAULT '' COMMENT '文件id',
  PRIMARY KEY (`id`),
  KEY `appid` (`appid`),
  KEY `rid` (`rid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `dzz_pichome_folder`;
CREATE TABLE `dzz_pichome_folder` (
  `fid` char(19) NOT NULL COMMENT '目录id',
  `pfid` char(19) NOT NULL DEFAULT '0' COMMENT '父级目录id',
  `fname` varchar(255) NOT NULL DEFAULT '' COMMENT '目录名称',
  `appid` char(6) NOT NULL DEFAULT '' COMMENT '对应库id',
  `perm` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '权限值',
  `pathkey` varchar(255) NOT NULL DEFAULT '' COMMENT '路径关系',
  `dateline` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `cover` char(13) NOT NULL DEFAULT '' COMMENT '封面，文件rid',
  `password` char(4) NOT NULL DEFAULT '' COMMENT '密码',
  `passwordtips` varchar(120) NOT NULL DEFAULT '' COMMENT '密码提示',
  PRIMARY KEY (`fid`),
  KEY `pfid` (`pfid`),
  KEY `appid` (`appid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `dzz_pichome_folderresources`;
CREATE TABLE `dzz_pichome_folderresources` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `rid` char(19) NOT NULL DEFAULT '' COMMENT '文件id',
  `fid` char(19) NOT NULL DEFAULT '' COMMENT '目录id',
  `appid` char(19) NOT NULL DEFAULT '' COMMENT '库id',
  PRIMARY KEY (`id`),
  KEY `rid` (`rid`),
  KEY `fid` (`fid`),
  KEY `appid` (`appid`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `dzz_pichome_palette`;
CREATE TABLE `dzz_pichome_palette` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `color` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '颜色整型值',
  `r` smallint(6)  NOT NULL DEFAULT '0',
  `g` smallint(6)  NOT NULL DEFAULT '0',
  `b` smallint(6)  NOT NULL DEFAULT '0',
  `rid` char(19) NOT NULL DEFAULT '' COMMENT '文件id',
  `weight` float(5,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '颜色百分比',
  PRIMARY KEY (`id`),
  KEY `rid` (`rid`)
) ENGINE=MyISAM AUTO_INCREMENT=68 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `dzz_pichome_resources`;
CREATE TABLE `dzz_pichome_resources` (
  `rid` char(19) NOT NULL DEFAULT '' COMMENT '文件主键id',
  `uid` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户id',
  `username` char(30) NOT NULL DEFAULT '' COMMENT '用户名',
  `appid` char(6) NOT NULL DEFAULT '' COMMENT '库id',
  `name` char(120) NOT NULL DEFAULT '' COMMENT '文件名称',
  `type` char(15) NOT NULL DEFAULT '' COMMENT '文件类型',
  `ext` char(15) NOT NULL DEFAULT '' COMMENT '文件后缀',
  `height` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '高度',
  `width` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '宽度',
  `dateline` bigint(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '修改时间',
  `hasthumb` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否有缩略图',
  `grade` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '评分',
  `size` bigint(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT '大小',
  `mtime` bigint(13) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `isdelete` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否为删除状态',
  `btime` bigint(13) UNSIGNED NOT NULL COMMENT '添加时间',
  `md5` char(32) NOT NULL DEFAULT '' COMMENT '文件md5值',
  PRIMARY KEY (`rid`),
  KEY `appid` (`appid`),
  KEY `appid_2` (`appid`,`isdelete`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `dzz_pichome_resources_attr`;
CREATE TABLE  `dzz_pichome_resources_attr` (
  `rid` char(19) NOT NULL DEFAULT '' COMMENT '文件id',
  `appid` char(6) NOT NULL DEFAULT '' COMMENT '应用id',
  `shape` char(10) NOT NULL DEFAULT '' COMMENT '图片形状',
  `gray` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否黑白色',
  `colors` text COMMENT '颜色值',
  `duration` float(11,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '时长',
  `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `link` varchar(255) NOT NULL DEFAULT '' COMMENT '链接',
  `tag` text NOT NULL COMMENT '标签id',
  `path` blob NOT NULL COMMENT '路径',
  PRIMARY KEY (`rid`),
  KEY `appid` (`appid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `dzz_pichome_share`;
CREATE TABLE `dzz_pichome_share` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `title` varchar(120) NOT NULL DEFAULT '' COMMENT '分享标题',
  `filepath` text NOT NULL COMMENT '路径',
  `appid` char(6) NOT NULL DEFAULT '' COMMENT 'appid',
  `dateline` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '时间',
  `times` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '分享总次数，为0则为不限制',
  `endtime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '分享结束时间',
  `username` char(60) NOT NULL DEFAULT '' COMMENT '用户名',
  `uid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户名',
  `password` varchar(255) DEFAULT '' COMMENT '留空无密码',
  `count` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '分享使用次数',
  `downloads` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '下载次数',
  `views` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '查看次数',
  PRIMARY KEY (`id`),
  KEY `appid` (`appid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `dzz_pichome_tag`;
CREATE TABLE `dzz_pichome_tag` (
  `tid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '标签id',
  `tagname` varchar(120) NOT NULL DEFAULT '' COMMENT '标签名称',
  `hots` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '使用次数',
  PRIMARY KEY (`tid`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `dzz_pichome_taggroup`;
CREATE TABLE `dzz_pichome_taggroup` (
  `cid` char(19) NOT NULL COMMENT '主键id',
  `catname` varchar(255) NOT NULL DEFAULT '' COMMENT '分类名称',
  `pcid` char(19) NOT NULL DEFAULT '0' COMMENT '父级分类id',
  `appid` char(13) NOT NULL DEFAULT '' COMMENT '应用id',
  `dateline` char(13) NOT NULL DEFAULT '0' COMMENT '最后修改时间',
  PRIMARY KEY (`cid`),
  KEY `pcid` (`pcid`),
  KEY `appid` (`appid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `dzz_pichome_tagrelation`;
CREATE TABLE `dzz_pichome_tagrelation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '0主键id',
  `tid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '标签id',
  `cid` char(19) NOT NULL DEFAULT '' COMMENT '分类id',
  `appid` char(13) NOT NULL DEFAULT '' COMMENT '库id',
  PRIMARY KEY (`id`),
  KEY `cid` (`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `dzz_pichome_vapp`;
CREATE TABLE `dzz_pichome_vapp` (
  `appid` char(6) NOT NULL DEFAULT '' COMMENT '库id',
  `uid` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户id',
  `username` char(30) NOT NULL DEFAULT '' COMMENT '用户名',
  `appname` varchar(255) NOT NULL DEFAULT '',
  `personal` tinyint(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '0公开，1私有',
  `path` blob NOT NULL COMMENT '对应目录路径',
  `dateline` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `extra` text COMMENT '拓展数据',
  `perm` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '权限值',
 `filenum` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '文件个数',
  `lastid` char(13) DEFAULT '' COMMENT '最后执行位置id',
  `percent` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '导入百分比',
  `state` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0，未导入，1导入中，2导入完成',
  `filter` text COMMENT '筛选项',
  `share` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '分享是否开放',
  `download` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否开放下载',
  PRIMARY KEY (`appid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `dzz_pichome_searchrecent`;
CREATE TABLE IF NOT EXISTS `dzz_pichome_searchrecent` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `uid` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户id',
  `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '关键词',
  `ktype` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '关键词类型，0普通关键词，1标签,2分类',
  `appid` char(6) NOT NULL DEFAULT '' COMMENT '库id',
  `dateline` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '搜索时间',
  `hots` int(11) UNSIGNED NOT NULL DEFAULT '1' COMMENT '搜索次数',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `dzz_pichome_resourcestag`;
CREATE TABLE `dzz_pichome_resourcestag` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `tid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '标签id',
  `rid` char(19) NOT NULL DEFAULT '' COMMENT '文件id',
  `appid` char(6) NOT NULL DEFAULT '' COMMENT '库id',
  PRIMARY KEY (`id`),
  KEY `tid` (`tid`),
  KEY `rid` (`rid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
EOF;
    runquery($sql);
    $finish = true;
