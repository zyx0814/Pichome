-- ----------------------------
-- Table structure for `dzz_admincp_session`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_admincp_session`;
CREATE TABLE `dzz_admincp_session` (
`uid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`adminid`  smallint(6) UNSIGNED NOT NULL DEFAULT 0 ,
`panel`  tinyint(1) NOT NULL DEFAULT 0 ,
`ip`  varchar(15) NOT NULL DEFAULT '' ,
`dateline`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`errorcount`  tinyint(1) NOT NULL DEFAULT 0 ,
`storage`  mediumtext NOT NULL ,
PRIMARY KEY (`uid`, `panel`)
)ENGINE=MyISAM;

DROP TABLE IF EXISTS dzz_app_market;
CREATE TABLE dzz_app_market (
`appid`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`mid`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '云端应用ID' ,
`appname`  varchar(255) NOT NULL ,
`appico`  varchar(255) NOT NULL ,
`appdesc`  text NOT NULL ,
`appurl`  varchar(255) NOT NULL ,
`appadminurl`  varchar(255) NULL DEFAULT NULL COMMENT '管理设置地址' ,
`noticeurl`  varchar(255) NOT NULL DEFAULT '' COMMENT '通知接口地址' ,
`dateline`  int(10) UNSIGNED NOT NULL ,
`disp`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`vendor`  varchar(255) NOT NULL COMMENT '提供商' ,
`haveflash`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 ,
`isshow`  tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否显示应用图标' ,
`havetask`  tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否显示任务栏' ,
`hideInMarket`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '应用市场里不显示' ,
`feature`  text NOT NULL COMMENT '窗体feature' ,
`fileext`  text NOT NULL COMMENT '可以打开的文件类型' ,
`group`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '应用的分组:0:全部；-1:游客可用，3:系统管理员可用;2：部门管理员可用;1:所有成员可用',
 `orgid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '可以使用的部门id，为0表示不限制',
`position`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '2：desktop,3:taskbar,1：apparea' ,
`system`  tinyint(1) NOT NULL DEFAULT 0,
`notdelete`  tinyint(1) NOT NULL DEFAULT 0,
`open`  tinyint(1) NOT NULL DEFAULT 0,
`nodup`  tinyint(1) NOT NULL DEFAULT 0,
`identifier`  varchar(40) NOT NULL DEFAULT '',
`app_path`  varchar(50) NULL DEFAULT NULL COMMENT 'APP路劲',
`available`  tinyint(1) NOT NULL DEFAULT 1,
`version`  varchar(20) NOT NULL DEFAULT '',
`upgrade_version`  text NOT NULL COMMENT '升级版本',
`check_upgrade_time`  int(11) NOT NULL DEFAULT 0 COMMENT '最近次检测升级时间' ,
`extra`  text NOT NULL,
`uids`  text NULL COMMENT '访问用户',
`showadmin`  tinyint(3) UNSIGNED NULL DEFAULT 0 COMMENT '0不显示在后台管理，1显示在后台管理' ,
PRIMARY KEY (`appid`),
UNIQUE KEY `appurl` (`appurl`) USING BTREE,
KEY `available` (`available`) USING BTREE,
KEY `identifier` (`identifier`) USING BTREE
)ENGINE=MyISAM;

DROP TABLE IF EXISTS `dzz_app_open`;
CREATE TABLE `dzz_app_open` (
`ext`  varchar(255) NOT NULL DEFAULT '' ,
`appid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`disp`  smallint(6) NOT NULL DEFAULT 0 ,
`extid`  smallint(6) UNSIGNED NOT NULL AUTO_INCREMENT ,
`isdefault`  tinyint(1) NOT NULL DEFAULT 0 ,
PRIMARY KEY (`extid`),
KEY `appid` (`appid`) USING BTREE ,
KEY `ext` (`ext`, `disp`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_app_open_default`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_app_open_default`;
CREATE TABLE `dzz_app_open_default` (
`uid`  int(10) UNSIGNED NOT NULL ,
`ext`  varchar(255) NOT NULL DEFAULT '' ,
`extid`  smallint(6) UNSIGNED NOT NULL DEFAULT 0 ,
`dateline`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
UNIQUE KEY `defaultext` (`ext`, `uid`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_app_organization`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_app_organization`;
CREATE TABLE `dzz_app_organization` (
`appid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`orgid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`dateline`  int(10) NOT NULL DEFAULT 0 ,
UNIQUE KEY `orgid` (`appid`, `orgid`) USING BTREE ,
KEY `appid` (`appid`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_app_pic`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_app_pic`;
CREATE TABLE `dzz_app_pic` (
`picid`  mediumint(8) NOT NULL AUTO_INCREMENT ,
`appid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`uid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`username`  varchar(15) NOT NULL DEFAULT '' ,
`dateline`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`aid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
PRIMARY KEY (`picid`),
KEY `uid` (`uid`) USING BTREE ,
KEY `idtype` (`appid`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_app_relative`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_app_relative`;
CREATE TABLE `dzz_app_relative` (
`rid`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`appid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`tagid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
PRIMARY KEY (`rid`),
UNIQUE KEY `appid` (`appid`, `tagid`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_app_tag`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_app_tag`;
CREATE TABLE `dzz_app_tag` (
`tagid`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`hot`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`tagname`  char(15) NOT NULL DEFAULT '0' ,
`dateline`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
PRIMARY KEY (`tagid`),
KEY `appid` (`hot`) USING BTREE ,
KEY `classid` (`tagname`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_app_user`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_app_user`;
CREATE TABLE `dzz_app_user` (
`id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`appid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`uid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`lasttime`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`dateline`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`num`  smallint(6) UNSIGNED NOT NULL DEFAULT 0 ,
PRIMARY KEY (`id`),
UNIQUE KEY `appuser` (`appid`, `uid`) USING BTREE ,
KEY `appid` (`appid`) USING BTREE ,
KEY `uid` (`uid`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_attach`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_attach`;
CREATE TABLE `dzz_attach` (
`qid`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`uid`  int(10) NOT NULL DEFAULT 0 ,
`username`  char(30) NOT NULL DEFAULT '' ,
`fid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`aid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`filename`  char(80) NOT NULL DEFAULT '' ,
`area`  char(15) NOT NULL DEFAULT '' ,
`areaid`  int(10) NOT NULL DEFAULT 0 ,
`reversion`  smallint(6) NOT NULL DEFAULT 0 ,
`downloads`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`dateline`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`deletetime`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`deleteuid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
PRIMARY KEY (`qid`),
KEY `dateline` (`dateline`) USING BTREE ,
KEY `tid` (`fid`) USING BTREE ,
KEY `area` (`area`) USING BTREE ,
KEY `areaid` (`areaid`, `area`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_attachment`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_attachment`;
CREATE TABLE `dzz_attachment` (
`aid`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`filename`  char(100) NOT NULL DEFAULT '' ,
`filetype`  char(15) NOT NULL DEFAULT '' ,
`filesize`  bigint(20) UNSIGNED NOT NULL DEFAULT 0 ,
`attachment`  char(60) NOT NULL DEFAULT '' ,
`remote`  smallint(6) UNSIGNED NOT NULL DEFAULT 0 ,
`copys`  smallint(6) NOT NULL DEFAULT 0 ,
`md5`  char(32) NOT NULL ,
`thumb`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 ,
`dateline`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`unrun`  tinyint(1) NOT NULL DEFAULT 0 ,
PRIMARY KEY (`aid`),
KEY `dateline` (`dateline`) USING BTREE ,
KEY `md5` (`md5`, `filesize`) USING BTREE ,
KEY `filetype` (`filetype`) USING BTREE ,
KEY `unrun` (`unrun`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_cache`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_cache`;
CREATE TABLE `dzz_cache` (
`cachekey`  varchar(255) NOT NULL DEFAULT '' ,
`cachevalue`  mediumblob NOT NULL ,
`dateline`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
PRIMARY KEY (`cachekey`)
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_collect`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_collect`;
CREATE TABLE `dzz_collect` (
`cid`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`ourl`  varchar(255) NOT NULL DEFAULT '' ,
`data`  text NOT NULL ,
`dateline`  int(10) UNSIGNED NOT NULL ,
`copys`  int(10) UNSIGNED NOT NULL ,
`type`  varchar(30) NOT NULL DEFAULT '' ,
PRIMARY KEY (`cid`)
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_connect`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_connect`;
CREATE TABLE `dzz_connect` (
`name`  varchar(255) NOT NULL ,
`key`  varchar(255) NOT NULL DEFAULT '' ,
`secret`  varchar(255) NOT NULL DEFAULT '' ,
`type`  varchar(255) NOT NULL COMMENT 'pan,mail,storage,web' ,
`bz`  varchar(255) NOT NULL COMMENT 'Dropbox,Box,Google,Aliyun,Grandcloud' ,
`root`  varchar(255) NOT NULL DEFAULT '' ,
`available`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否可用' ,
`dname`  varchar(255) NOT NULL COMMENT '数据库名称' ,
`curl`  varchar(255) NOT NULL COMMENT '授权地址' ,
`disp`  smallint(6) NOT NULL DEFAULT 0 ,
UNIQUE KEY `bz` (`bz`) USING BTREE ,
KEY `disp` (`disp`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_connect_disk`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_connect_disk`;
CREATE TABLE `dzz_connect_disk` (
`id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`cloudname`  varchar(255) NOT NULL DEFAULT '' ,
`attachdir`  varchar(255) NOT NULL DEFAULT '' COMMENT '绝对位置' ,
`attachurl`  varchar(255) NOT NULL DEFAULT '' COMMENT '访问地址（选填）' ,
`uid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`perm`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`bz`  varchar(10) NOT NULL DEFAULT 'DISK' ,
`dateline`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`charset`  varchar(30) NOT NULL DEFAULT 'GBK' ,
PRIMARY KEY (`id`)
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_connect_ftp`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_connect_ftp`;
CREATE TABLE `dzz_connect_ftp` (
`id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`host`  varchar(255) NOT NULL DEFAULT '' ,
`cloudname`  varchar(255) NOT NULL DEFAULT '' ,
`username`  varchar(255) NOT NULL DEFAULT '' ,
`password`  varchar(255) NOT NULL DEFAULT '' ,
`port`  smallint(6) NOT NULL DEFAULT 0 ,
`timeout`  smallint(6) NOT NULL DEFAULT 90 ,
`ssl`  tinyint(1) NOT NULL DEFAULT 0 ,
`pasv`  tinyint(1) NOT NULL DEFAULT 0 ,
`uid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`perm`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`bz`  varchar(10) NOT NULL DEFAULT 'FTP' ,
`dateline`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`on`  tinyint(1) NOT NULL DEFAULT 1 ,
`charset`  varchar(30) NOT NULL DEFAULT 'GBK' ,
PRIMARY KEY (`id`)
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_connect_onedrive`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_connect_onedrive`;
CREATE TABLE `dzz_connect_onedrive` (
`id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`bz`  varchar(30) NOT NULL ,
`cloudname`  varchar(255) NOT NULL DEFAULT '' ,
`cuid`  char(60) NOT NULL DEFAULT '' ,
`cusername`  varchar(255) NOT NULL ,
`uid`  int(10) UNSIGNED NOT NULL ,
`expires_in`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`dateline`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`refreshtime`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`perm`  smallint(6) UNSIGNED NOT NULL DEFAULT 29751 ,
`refresh_token`  text NOT NULL ,
`access_token`  text NOT NULL ,
`scope`  varchar(255) NOT NULL ,
PRIMARY KEY (`id`),
UNIQUE KEY `cuid` (`cuid`, `uid`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_connect_pan`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_connect_pan`;
CREATE TABLE `dzz_connect_pan` (
`id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`bz`  varchar(255) NOT NULL ,
`cloudname`  varchar(255) NOT NULL DEFAULT '' ,
`cuid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`cusername`  varchar(255) NOT NULL ,
`portrait`  varchar(255) NOT NULL DEFAULT '' ,
`uid`  int(10) UNSIGNED NOT NULL ,
`expires_in`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`dateline`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`refreshtime`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`perm`  int(10) UNSIGNED NOT NULL DEFAULT 29751 ,
`refresh_token`  varchar(255) NOT NULL ,
`access_token`  varchar(255) NOT NULL ,
`scope`  varchar(255) NOT NULL ,
`session_key`  varchar(255) NOT NULL ,
`session_secret`  varchar(255) NOT NULL ,
PRIMARY KEY (`id`),
UNIQUE KEY `cuid` (`cuid`, `uid`, `bz`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_connect_storage`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_connect_storage`;
CREATE TABLE `dzz_connect_storage` (
`id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`uid`  int(10) UNSIGNED NOT NULL ,
`cloudname`  varchar(255) NOT NULL DEFAULT '' ,
`dateline`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`perm`  int(10) UNSIGNED NOT NULL DEFAULT 29751 ,
`access_id`  varchar(255) NOT NULL ,
`access_key`  varchar(255) NOT NULL ,
`bucket`  char(30) NOT NULL DEFAULT '' ,
`bz`  varchar(30) NOT NULL DEFAULT '' ,
`hostname`  varchar(255) NOT NULL DEFAULT '' ,
`internalhostname`  varchar(255) NOT NULL DEFAULT '' ,
`host`  varchar(255) NOT NULL DEFAULT '' ,
`internalhost`  varchar(255) NOT NULL DEFAULT '' ,
`extra`  varchar(150) NOT NULL DEFAULT '' ,
PRIMARY KEY (`id`)
)ENGINE=MyISAM;
-- ----------------------------
-- Table structure for `dzz_cron`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_cron`;
CREATE TABLE `dzz_cron` (
`cronid`  smallint(6) UNSIGNED NOT NULL AUTO_INCREMENT ,
`available`  tinyint(1) NOT NULL DEFAULT 0 ,
`type`  enum('user','system','app') NOT NULL DEFAULT 'user' ,
`name`  char(50) NOT NULL DEFAULT '' ,
`filename`  char(50) NOT NULL DEFAULT '' ,
`lastrun`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`nextrun`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`weekday`  tinyint(1) NOT NULL DEFAULT 0 ,
`day`  tinyint(2) NOT NULL DEFAULT 0 ,
`hour`  tinyint(2) NOT NULL DEFAULT 0 ,
`minute`  char(36) NOT NULL DEFAULT '' ,
PRIMARY KEY (`cronid`),
KEY `nextrun` (`available`, `nextrun`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_district`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_district`;
CREATE TABLE `dzz_district` (
`id`  mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT ,
`name`  varchar(255) NOT NULL DEFAULT '' ,
`level`  tinyint(4) UNSIGNED NOT NULL DEFAULT 0 ,
`usetype`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 ,
`upid`  mediumint(8) UNSIGNED NOT NULL DEFAULT 0 ,
`displayorder`  smallint(6) NOT NULL DEFAULT 0 ,
PRIMARY KEY (`id`),
KEY `upid` (`upid`, `displayorder`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_document`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_document`;
CREATE TABLE `dzz_document` (
`did`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`tid`  int(10) UNSIGNED NOT NULL ,
`fid`  smallint(6) UNSIGNED NOT NULL DEFAULT 0 COMMENT '文库分类id' ,
`uid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`username`  char(30) NOT NULL DEFAULT '' ,
`area`  char(15) NOT NULL DEFAULT '' ,
`areaid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`aid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`version`  smallint(6) UNSIGNED NOT NULL DEFAULT 0 ,
`isdelete`  tinyint(10) UNSIGNED NOT NULL DEFAULT 0 ,
`disp`  smallint(6) NOT NULL DEFAULT 0 ,
`dateline`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
PRIMARY KEY (`did`),
KEY `dateline` (`dateline`) USING BTREE ,
KEY `disp` (`disp`) USING BTREE ,
KEY `fid` (`fid`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_document_event`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_document_event`;
CREATE TABLE `dzz_document_event` (
`eid`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`did`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`action`  char(15) NOT NULL DEFAULT '' ,
`uid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`username`  char(30) NOT NULL DEFAULT '' ,
`dateline`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
PRIMARY KEY (`eid`),
KEY `did` (`did`, `uid`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_document_reversion`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_document_reversion`;
CREATE TABLE `dzz_document_reversion` (
`did`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`dateline`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`aid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`revid`  int(10) NOT NULL AUTO_INCREMENT ,
`uid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`username`  varchar(30) NOT NULL DEFAULT '' ,
`version`  smallint(6) UNSIGNED NOT NULL DEFAULT 0 ,
`attachs`  text NOT NULL ,
PRIMARY KEY (`revid`),
KEY `dateline` (`dateline`) USING BTREE ,
KEY `qid` (`did`) USING BTREE ,
KEY `uid` (`uid`) USING BTREE ,
KEY `username` (`username`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_failedlogin`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_failedlogin`;
CREATE TABLE `dzz_failedlogin` (
`ip`  char(15) NOT NULL DEFAULT '' ,
`username`  char(32) NOT NULL DEFAULT '' ,
`count`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 ,
`lastupdate`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
PRIMARY KEY (`ip`, `username`)
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_hooks`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_hooks`;
CREATE TABLE `dzz_hooks` (
`id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`app_market_id`  int(11) NOT NULL COMMENT '应用ID' ,
`name`  varchar(40) NOT NULL DEFAULT '' COMMENT '钩子名称' ,
`description`  text NOT NULL COMMENT '描述' ,
`type`  tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '类型' ,
`update_time`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间' ,
`addons`  varchar(255) NOT NULL DEFAULT '' COMMENT '钩子对应程序' ,
`status`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态1正常;-1删除' ,
`priority`  smallint(6) NOT NULL DEFAULT 0 COMMENT '运行优先级，挂载点下的钩子按优先级从高到低顺序执行' ,
PRIMARY KEY (`id`),
KEY `app_market_id` (`name`) USING BTREE ,
KEY `priority` (`priority`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_icon`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_icon`;
CREATE TABLE `dzz_icon` (
`did`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`domain`  varchar(255) NOT NULL ,
`reg`  varchar(255) NOT NULL DEFAULT '' COMMENT '匹配正则表达式' ,
`ext`  varchar(30) NOT NULL DEFAULT '' ,
`pic`  varchar(255) NOT NULL ,
`dateline`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`check`  tinyint(1) UNSIGNED NOT NULL DEFAULT 1 ,
`uid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`username`  varchar(255) NOT NULL ,
`copys`  int(10) NOT NULL DEFAULT 0 ,
`disp`  smallint(6) UNSIGNED NOT NULL DEFAULT 0 ,
PRIMARY KEY (`did`),
KEY `domain` (`domain`) USING BTREE ,
KEY `uid` (`uid`) USING BTREE ,
KEY `copys` (`copys`) USING BTREE ,
KEY `dateline` (`dateline`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_iconview`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_iconview`;
CREATE TABLE `dzz_iconview` (
`id`  smallint(6) UNSIGNED NOT NULL AUTO_INCREMENT ,
`name`  varchar(255) NOT NULL ,
`width`  smallint(6) UNSIGNED NOT NULL DEFAULT 64 ,
`height`  smallint(6) UNSIGNED NOT NULL DEFAULT 64 ,
`divwidth`  smallint(6) UNSIGNED NOT NULL DEFAULT 100 ,
`divheight`  smallint(6) UNSIGNED NOT NULL DEFAULT 100 ,
`paddingtop`  smallint(6) UNSIGNED NOT NULL DEFAULT 0 ,
`paddingleft`  smallint(6) UNSIGNED NOT NULL DEFAULT 0 ,
`textlength`  smallint(6) UNSIGNED NOT NULL DEFAULT 30 ,
`align`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 ,
`avaliable`  tinyint(1) UNSIGNED NOT NULL DEFAULT 1 ,
`disp`  smallint(6) UNSIGNED NOT NULL DEFAULT 0 ,
`cssname`  varchar(60) NOT NULL ,
PRIMARY KEY (`id`),
KEY `avaliable` (`avaliable`, `disp`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_imagetype`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_imagetype`;
CREATE TABLE `dzz_imagetype` (
`typeid`  smallint(6) UNSIGNED NOT NULL AUTO_INCREMENT ,
`available`  tinyint(1) NOT NULL DEFAULT 0 ,
`name`  char(20) NOT NULL ,
`type`  enum('smiley','icon','avatar') NOT NULL DEFAULT 'smiley' ,
`displayorder`  tinyint(3) NOT NULL DEFAULT 0 ,
`directory`  char(100) NOT NULL ,
PRIMARY KEY (`typeid`)
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_local_router`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_local_router`;
CREATE TABLE `dzz_local_router` (
`routerid`  smallint(6) UNSIGNED NOT NULL AUTO_INCREMENT ,
`name`  char(60) NOT NULL DEFAULT '' ,
`remoteid`  smallint(6) UNSIGNED NOT NULL DEFAULT 0 ,
`router`  text NOT NULL ,
`dateline`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`available`  tinyint(1) NOT NULL DEFAULT 0 ,
`priority`  smallint(6) UNSIGNED NOT NULL DEFAULT 100 ,
PRIMARY KEY (`routerid`),
KEY `priority` (`priority`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_local_storage`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_local_storage`;
CREATE TABLE `dzz_local_storage` (
`remoteid`  smallint(6) UNSIGNED NOT NULL AUTO_INCREMENT ,
`name`  varchar(255) NOT NULL ,
`bz`  varchar(255) NOT NULL COMMENT 'Dropbox,Box,Google,Aliyun,Grandcloud' ,
`isdefault`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '默认' ,
`dname`  varchar(255) NOT NULL COMMENT '数据库名称' ,
`did`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`disp`  smallint(6) NOT NULL DEFAULT 0 ,
`usesize`  bigint(20) UNSIGNED NOT NULL DEFAULT 0 ,
`totalsize`  bigint(20) UNSIGNED NOT NULL DEFAULT 0 ,
`lastupdate`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`dateline`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
PRIMARY KEY (`remoteid`),
KEY `disp` (`disp`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_mailcron`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_mailcron`;
CREATE TABLE `dzz_mailcron` (
`cid`  mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT ,
`touid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`email`  varchar(100) NOT NULL DEFAULT '' ,
`sendtime`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
PRIMARY KEY (`cid`),
KEY `sendtime` (`sendtime`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_mailqueue`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_mailqueue`;
CREATE TABLE `dzz_mailqueue` (
`qid`  mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT ,
`cid`  mediumint(8) UNSIGNED NOT NULL DEFAULT 0 ,
`subject`  text NOT NULL ,
`message`  text NOT NULL ,
`dateline`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
PRIMARY KEY (`qid`),
KEY `mcid` (`cid`, `dateline`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_notification`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_notification`;
CREATE TABLE `dzz_notification` (
`id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`uid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`type`  varchar(60) NOT NULL DEFAULT '' ,
`new`  tinyint(1) NOT NULL DEFAULT 0 ,
`authorid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`author`  varchar(30) NOT NULL DEFAULT '' ,
`note`  text NOT NULL ,
`wx_note`  text NOT NULL ,
`wx_new`  tinyint(1) NOT NULL DEFAULT 1 ,
`redirecturl`  varchar(255) NOT NULL DEFAULT '' COMMENT '跳转地址' ,
`title`  varchar(255) NOT NULL DEFAULT '' ,
`dateline`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`from_id`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`from_idtype`  varchar(20) NOT NULL DEFAULT '' ,
`from_num`  mediumint(8) UNSIGNED NOT NULL DEFAULT 0 ,
`category`  tinyint(1) NOT NULL DEFAULT 0 COMMENT ' 提醒分类 1系统消息 0应用消息' ,
PRIMARY KEY (`id`),
KEY `uid` (`uid`, `new`) USING BTREE ,
KEY `category` (`uid`, `dateline`) USING BTREE ,
KEY `by_type` (`uid`, `type`, `dateline`) USING BTREE ,
KEY `from_id` (`from_id`, `from_idtype`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_onlinetime`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_onlinetime`;
CREATE TABLE `dzz_onlinetime` (
`uid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`thismonth`  smallint(6) UNSIGNED NOT NULL DEFAULT 0 ,
`total`  mediumint(8) UNSIGNED NOT NULL DEFAULT 0 ,
`lastupdate`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
PRIMARY KEY (`uid`)
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_organization`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_organization`;
CREATE TABLE `dzz_organization` (
`orgid`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`orgname`  varchar(255) NOT NULL DEFAULT '' ,
`forgid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`worgid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`fid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`disp`  smallint(6) NOT NULL DEFAULT 0 ,
`dateline`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`usesize`  bigint(20) UNSIGNED NOT NULL DEFAULT 0 ,
`maxspacesize`  bigint(20) NOT NULL DEFAULT 0 COMMENT '0：不限制，-1表示無空間' ,
`indesk`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否创建快捷方式' ,
`available`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否启用' ,
`pathkey`  varchar(255) NOT NULL DEFAULT '' ,
`type`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0一般机构，1群组机构' ,
`desc`  varchar(200) NOT NULL DEFAULT '' COMMENT '群组描述' ,
`groupback`  int(11) UNSIGNED NOT NULL COMMENT '群组背景图' ,
`aid`  varchar(30) NOT NULL DEFAULT '' COMMENT '群组缩略图,可以是aid,也可以是颜色值' ,
`manageon`  tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '群組管理员开启关闭0关闭，1开启' ,
`syatemon`  tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '系统管理员开启群组，关闭群组，0关闭，1开启' ,
`diron`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '群组管理员共享目录开启，0关闭，1开启' ,
`extraspace`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '机构群组额外空间大小' ,
`buyspace`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '购买空间' ,
`allotspace`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '分配空间大小' ,
PRIMARY KEY (`orgid`),
KEY `disp` (`disp`) USING BTREE ,
KEY `pathkey` (`pathkey`) USING BTREE ,
KEY `dateline` (`dateline`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_organization_admin`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_organization_admin`;
CREATE TABLE `dzz_organization_admin` (
`id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`orgid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`uid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`dateline`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`opuid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`admintype`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0管理员，1群组创始人' ,
PRIMARY KEY (`id`),
UNIQUE KEY `orgid` (`orgid`, `uid`) USING BTREE
)ENGINE=MyISAM;


-- ----------------------------
-- Table structure for `dzz_organization_guser`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_organization_guser`;
CREATE TABLE `dzz_organization_guser` (
`id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`orgid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`guid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`dateline`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`opuid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`admintype`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0:普通成员，1：管理员，2群组创始人' ,
PRIMARY KEY (`id`),
UNIQUE KEY `orgid` (`orgid`, `guid`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_organization_job`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_organization_job`;
CREATE TABLE `dzz_organization_job` (
`jobid`  smallint(6) UNSIGNED NOT NULL AUTO_INCREMENT ,
`orgid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`name`  varchar(30) NOT NULL DEFAULT '' ,
`dateline`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`opuid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
PRIMARY KEY (`jobid`),
KEY `orgid` (`orgid`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_organization_upjob`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_organization_upjob`;
CREATE TABLE `dzz_organization_upjob` (
`id`  smallint(6) NOT NULL AUTO_INCREMENT ,
`jobid`  smallint(6) UNSIGNED NOT NULL DEFAULT 0 ,
`uid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`dateline`  int(10) NOT NULL DEFAULT 0 ,
`opuid`  int(10) NOT NULL DEFAULT 0 ,
PRIMARY KEY (`id`),
UNIQUE KEY `uid` (`uid`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_organization_user`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_organization_user`;
CREATE TABLE `dzz_organization_user` (
`orgid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`uid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`jobid`  smallint(6) UNSIGNED NOT NULL DEFAULT 0 ,
`dateline`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
UNIQUE KEY `orgid` (`orgid`, `uid`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_pichome_comments`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_pichome_comments`;
CREATE TABLE `dzz_pichome_comments` (
`id`  char(19) NOT NULL DEFAULT '' COMMENT '标注id' ,
`x`  float(11,2)  NOT NULL DEFAULT 0.00 COMMENT 'x轴位置' ,
`y`  float(11,2)  NOT NULL DEFAULT 0.00 COMMENT 'y轴位置' ,
`width`  float(11,2)  NOT NULL DEFAULT 0.00 COMMENT '宽' ,
`height`  float(11,2)  NOT NULL DEFAULT 0.00 COMMENT '高度' ,
`annotation`  varchar(255) NOT NULL DEFAULT '' COMMENT '标注内容' ,
`lastModified`  char(13) NOT NULL DEFAULT '' COMMENT '最后更改时间' ,
`appid`  char(19) NOT NULL DEFAULT '' COMMENT '库id' ,
`rid`  char(32) NOT NULL DEFAULT '' COMMENT '文件id' ,
PRIMARY KEY (`id`),
KEY `appid` (`appid`) USING BTREE ,
KEY `rid` (`rid`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_pichome_ffmpeg_record`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_pichome_ffmpeg_record`;
CREATE TABLE `dzz_pichome_ffmpeg_record` (
`rid`  char(32) NOT NULL DEFAULT '' COMMENT '文件id' ,
`stype`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0，获取信息；1获取缩略图' ,
`ext`  char(15) NOT NULL DEFAULT '' COMMENT '文件类型' ,
`thumbdonum`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '执行次数' ,
`thumbstatus`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '缩略图是否完成' ,
`infodonum`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '获取信息执行次数' ,
`infostatus`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '获取信息是否完成' ,
`thumb`  blob NULL COMMENT '缩略图路径' ,
`appid`  char(6) NOT NULL DEFAULT '' COMMENT '库id' ,
PRIMARY KEY (`rid`),
KEY `thumbdonum` (`thumbdonum`) USING BTREE ,
KEY `infodonum` (`infodonum`) USING BTREE ,
KEY `thumbstatus` (`thumbstatus`, `infostatus`) USING BTREE ,
KEY `appid` (`appid`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_pichome_folder`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_pichome_folder`;
CREATE TABLE `dzz_pichome_folder` (
`fid`  char(19) NOT NULL COMMENT '目录id' ,
`pfid`  char(19) NOT NULL DEFAULT '' COMMENT '父级目录id' ,
`fname`  varchar(255) NOT NULL DEFAULT '' COMMENT '目录名称' ,
`appid`  char(6) NOT NULL DEFAULT '' COMMENT '对应库id' ,
`perm`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '权限值' ,
`pathkey`  varchar(255) NOT NULL DEFAULT '' COMMENT '路径关系' ,
`dateline`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间' ,
`cover`  char(13) NOT NULL DEFAULT '' COMMENT '封面，文件rid' ,
`password`  char(4) NOT NULL DEFAULT '' COMMENT '密码' ,
`passwordtips`  varchar(120) NOT NULL DEFAULT '' COMMENT '密码提示' ,
`filenum`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '包含文件数' ,
`disp`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '目录排序' ,
PRIMARY KEY (`fid`),
KEY `pfid` (`pfid`) USING BTREE ,
KEY `appid` (`appid`) USING BTREE ,
KEY `pathkey` (`pathkey`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_pichome_folderresources`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_pichome_folderresources`;
CREATE TABLE `dzz_pichome_folderresources` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id' ,
`rid`  char(32) NOT NULL DEFAULT '' COMMENT '文件id' ,
`fid`  char(19) NOT NULL DEFAULT '' COMMENT '目录id' ,
`appid`  char(19) NOT NULL DEFAULT '' COMMENT '库id' ,
PRIMARY KEY (`id`),
KEY `rid` (`rid`) USING BTREE ,
KEY `fid` (`fid`) USING BTREE ,
KEY `appid` (`appid`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_pichome_imagickrecord`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_pichome_imagickrecord`;
CREATE TABLE `dzz_pichome_imagickrecord` (
`rid`  char(32) NOT NULL DEFAULT '' COMMENT '文件rid' ,
`ext`  char(15) NOT NULL DEFAULT '' COMMENT '类型' ,
`thumbdonum`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '缩略图执行次数' ,
`thumbstatus`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否成功' ,
`path`  blob NOT NULL COMMENT '缩略图路径' ,
`colordonum`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '获取颜色执行次数' ,
`colorstatus`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '获取颜色是否完成' ,
`appid`  char(6) NOT NULL DEFAULT '' COMMENT '库id' ,
PRIMARY KEY (`rid`),
KEY `thumbdonum` (`thumbdonum`) USING BTREE ,
KEY `colordonum` (`colordonum`) USING BTREE ,
KEY `thumbstatus` (`thumbstatus`, `colorstatus`) USING BTREE ,
KEY `appid` (`appid`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_pichome_palette`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_pichome_palette`;
CREATE TABLE `dzz_pichome_palette` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id' ,
`color`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '颜色整型值' ,
`r`  smallint(6) NOT NULL DEFAULT 0 ,
`g`  smallint(6) NOT NULL DEFAULT 0 ,
`b`  smallint(6) NOT NULL DEFAULT 0 ,
`rid`  char(32) NOT NULL DEFAULT '' COMMENT '文件id' ,
`weight`  float(5,2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '颜色百分比' ,
PRIMARY KEY (`id`),
KEY `rid` (`rid`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_pichome_resources`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_pichome_resources`;
CREATE TABLE `dzz_pichome_resources` (
  `rid` char(32) NOT NULL DEFAULT '' COMMENT '文件主键id',
  `uid` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户id',
  `username` char(30) NOT NULL DEFAULT '' COMMENT '用户名',
  `appid` char(6) NOT NULL DEFAULT '' COMMENT '库id',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '文件名称',
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
  `lastdate` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更改时间',
  `apptype` smallint(1) UNSIGNED DEFAULT '0' COMMENT '0，eagle文件；1，本地文件',
  PRIMARY KEY (`rid`),
  KEY `appid_2` (`appid`,`isdelete`) USING BTREE,
  KEY `mtime` (`mtime`) USING BTREE,
  KEY `btime` (`btime`) USING BTREE,
  KEY `dateline` (`dateline`) USING BTREE,
  KEY `name` (`name`) USING BTREE,
  KEY `ext` (`ext`) USING BTREE,
  KEY `height` (`height`,`width`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `namerid` (`name`,`rid`),
  KEY `datelinerid` (`dateline`,`rid`),
  KEY `mtimerid` (`mtime`,`rid`),
  KEY `btimerid` (`btime`,`rid`),
  KEY `sizerid` (`size`,`rid`),
  KEY `whrid` (`width`,`height`,`rid`)
) ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_pichome_resources_attr`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_pichome_resources_attr`;
CREATE TABLE `dzz_pichome_resources_attr` (
`rid`  char(32) NOT NULL DEFAULT '' COMMENT '文件id' ,
`appid`  char(6) NOT NULL DEFAULT '' COMMENT '应用id' ,
`shape`  char(10) NOT NULL DEFAULT '' COMMENT '图片形状' ,
`gray`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否黑白色' ,
`colors`  text NULL COMMENT '颜色值' ,
`duration`  float(11,2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '时长' ,
`desc`  varchar(255) NOT NULL DEFAULT '' COMMENT '描述' ,
`link`  varchar(255) NOT NULL DEFAULT '' COMMENT '链接' ,
`tag`  text NOT NULL COMMENT '标签id' ,
`path`  blob NOT NULL COMMENT '路径' ,
`isget`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否已获取数据' ,
`searchval`  text NULL COMMENT '关键词' ,
`pathmd5`  char(32) NULL COMMENT '路径唯一标识' ,
PRIMARY KEY (`rid`),
KEY `appid` (`appid`) USING BTREE ,
KEY `duration` (`duration`) USING BTREE ,
KEY `isget` (`isget`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_pichome_resourcestag`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_pichome_resourcestag`;
CREATE TABLE `dzz_pichome_resourcestag` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id' ,
`tid`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '标签id' ,
`rid`  char(32) NOT NULL DEFAULT '' COMMENT '文件id' ,
`appid`  char(6) NOT NULL DEFAULT '' COMMENT '库id' ,
PRIMARY KEY (`id`),
KEY `tid` (`tid`) USING BTREE ,
KEY `rid` (`rid`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_pichome_searchrecent`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_pichome_searchrecent`;
CREATE TABLE `dzz_pichome_searchrecent` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id' ,
`uid`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id' ,
`keywords`  varchar(255) NOT NULL DEFAULT '' COMMENT '关键词' ,
`ktype`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '关键词类型，0普通关键词，1标签,2分类' ,
`appid`  char(6) NOT NULL DEFAULT '' COMMENT '库id' ,
`dateline`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '搜索时间' ,
`hots`  int(11) UNSIGNED NOT NULL DEFAULT 1 COMMENT '搜索次数' ,
PRIMARY KEY (`id`)
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_pichome_share`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_pichome_share`;
CREATE TABLE `dzz_pichome_share` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id' ,
`title`  varchar(120) NOT NULL DEFAULT '' COMMENT '分享标题' ,
`filepath`  text NOT NULL COMMENT '路径' ,
`appid`  char(6) NOT NULL DEFAULT '' COMMENT 'appid' ,
`dateline`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '时间' ,
`times`  smallint(6) UNSIGNED NOT NULL DEFAULT 0 COMMENT '分享总次数，为0则为不限制' ,
`endtime`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '分享结束时间' ,
`username`  char(60) NOT NULL DEFAULT '' COMMENT '用户名' ,
`uid`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id' ,
`password`  varchar(255) NULL DEFAULT '' COMMENT '留空无密码' ,
`count`  smallint(6) UNSIGNED NOT NULL DEFAULT 0 COMMENT '分享使用次数' ,
`downloads`  smallint(6) UNSIGNED NOT NULL DEFAULT 0 COMMENT '下载次数' ,
`views`  smallint(6) UNSIGNED NOT NULL DEFAULT 0 COMMENT '查看次数' ,
`stype` smallint (1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '分享类型，0，pichome文件，1收藏文件，2收藏',
`clid`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '分享id' ,
PRIMARY KEY (`id`),
KEY `appid` (`appid`) USING BTREE,
KEY `uid` (`uid`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_pichome_tag`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_pichome_tag`;
CREATE TABLE `dzz_pichome_tag` (
`tid`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '标签id' ,
`tagname`  varchar(120) NOT NULL DEFAULT '' COMMENT '标签名称' ,
`hots`  smallint(6) UNSIGNED NOT NULL DEFAULT 0 COMMENT '使用次数' ,
`initial` char(6)  NOT NULL DEFAULT '' COMMENT '首字母',
PRIMARY KEY (`tid`)
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_pichome_taggroup`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_pichome_taggroup`;
CREATE TABLE `dzz_pichome_taggroup` (
`cid`  char(19) NOT NULL COMMENT '主键id' ,
`catname`  varchar(255) NOT NULL DEFAULT '' COMMENT '分类名称' ,
`pcid`  char(19) NOT NULL DEFAULT '0' COMMENT '父级分类id' ,
`appid`  char(13) NOT NULL DEFAULT '' COMMENT '应用id' ,
`dateline`  char(13) NOT NULL DEFAULT '0' COMMENT '最后修改时间' ,
PRIMARY KEY (`cid`),
KEY `pcid` (`pcid`) USING BTREE ,
KEY `appid` (`appid`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_pichome_tagrelation`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_pichome_tagrelation`;
CREATE TABLE `dzz_pichome_tagrelation` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '0主键id' ,
`tid`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '标签id' ,
`cid`  char(19) NOT NULL DEFAULT '' COMMENT '分类id' ,
`appid`  char(13) NOT NULL DEFAULT '' COMMENT '库id' ,
PRIMARY KEY (`id`),
KEY `cid` (`cid`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_pichome_vapp`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_pichome_vapp`;
CREATE TABLE `dzz_pichome_vapp` (
`appid`  char(6) NOT NULL DEFAULT '' COMMENT '库id' ,
`uid`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户id' ,
`username`  char(30) NOT NULL DEFAULT '' COMMENT '用户名' ,
`appname`  varchar(255) NOT NULL DEFAULT '' ,
`personal`  tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '0公开，1私有' ,
`path`  blob NOT NULL COMMENT '对应目录路径' ,
`dateline`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间' ,
`extra`  text NULL COMMENT '拓展数据' ,
`perm`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '权限值' ,
`filenum`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '文件个数' ,
`lastid`  char(13) NULL DEFAULT '' COMMENT '最后执行位置id' ,
`percent`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '导入百分比' ,
`state`  tinyint(1)  NOT NULL DEFAULT 0 COMMENT '0，未导入，1准备中，2导入中，3校验中，4已完成,-1导入失败' ,
`filter`  text NULL COMMENT '筛选项' ,
`share`  tinyint(1) NOT NULL COMMENT '分享是否开放' ,
`download`  tinyint(1) NOT NULL COMMENT '是否开放下载' ,
`donum`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '已导入文件数' ,
`charset`  char(15) NOT NULL DEFAULT '' COMMENT '库编码类型' ,
`type`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0，eagle库，1本地目录库，billfish库' ,
`isdelete`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否已删除' ,
`sort`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序，值越大越靠前' ,
`allowext`  text NULL COMMENT '允许导入后缀' ,
`notallowext`  text NULL COMMENT '不允许后缀' ,
`iswebsitefile`  tinyint(1) UNSIGNED NULL DEFAULT 1 COMMENT '是否是站点下文件' ,
`getinfonum`  int(11) UNSIGNED NULL DEFAULT 0 COMMENT '获取信息文件数' ,
`getinfo`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否获取信息' ,
`nosubfilenum`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '未分类文件总数' ,
`disp`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序' ,
`deluid`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '删除用户id' ,
`delusername`   char(30) NOT NULL DEFAULT '' COMMENT '删除用户名' ,
`version`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '版本号' ,
PRIMARY KEY (`appid`)
)ENGINE=MyISAM;
-- ----------------------------
-- Table structure for `dzz_process`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_process`;
CREATE TABLE `dzz_process` (
`processid`  char(60) NOT NULL ,
`expiry`  int(10) NULL DEFAULT NULL ,
`extra`  int(10) NULL DEFAULT NULL ,
PRIMARY KEY (`processid`),
KEY `expiry` (`expiry`) USING HASH
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_session`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_session`;
CREATE TABLE `dzz_session` (
`sid`  char(6) NOT NULL DEFAULT '' ,
`ip1`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 ,
`ip2`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 ,
`ip3`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 ,
`ip4`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 ,
`uid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`username`  char(15) NOT NULL DEFAULT '' ,
`groupid`  smallint(6) UNSIGNED NOT NULL DEFAULT 0 ,
`invisible`  tinyint(1) NOT NULL DEFAULT 0 ,
`action`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 ,
`lastactivity`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`lastolupdate`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
UNIQUE KEY `sid` (`sid`) USING HASH ,
KEY `uid` (`uid`) USING HASH
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_setting`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_setting`;
CREATE TABLE `dzz_setting` (
`skey`  varchar(255) NOT NULL DEFAULT '' ,
`svalue`  text NOT NULL ,
PRIMARY KEY (`skey`)
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_shorturl`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_shorturl`;
CREATE TABLE `dzz_shorturl` (
`sid`  char(10) NOT NULL ,
`url`  text NOT NULL ,
`count`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
PRIMARY KEY (`sid`)
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_syscache`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_syscache`;
CREATE TABLE `dzz_syscache` (
`cname`  varchar(32) NOT NULL ,
`ctype`  tinyint(3) UNSIGNED NOT NULL ,
`dateline`  int(10) UNSIGNED NOT NULL ,
`data`  mediumblob NOT NULL ,
PRIMARY KEY (`cname`)
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_thame`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_thame`;
CREATE TABLE `dzz_thame` (
`id`  smallint(6) UNSIGNED NOT NULL AUTO_INCREMENT ,
`name`  varchar(255) NOT NULL ,
`folder`  varchar(255) NOT NULL DEFAULT 'window_jd' ,
`backimg`  varchar(255) NOT NULL ,
`thumb`  varchar(255) NOT NULL ,
`btype`  tinyint(1) NOT NULL DEFAULT 1 ,
`url`  varchar(255) NOT NULL ,
`default`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 ,
`dateline`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`disp`  smallint(6) UNSIGNED NOT NULL DEFAULT 0 ,
`modules`  text NOT NULL ,
`color`  varchar(255) NOT NULL DEFAULT '' ,
`enable_color`  tinyint(1) NOT NULL DEFAULT 0 ,
`vendor`  varchar(255) NOT NULL ,
`version`  varchar(15) NOT NULL DEFAULT '1.0' ,
PRIMARY KEY (`id`),
KEY `disp` (`disp`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_user`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_user`;
CREATE TABLE `dzz_user` (
`uid`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`email`  char(60) NOT NULL DEFAULT '' ,
`phone`  varchar(255) NOT NULL DEFAULT '' ,
`weixinid`  varchar(255) NOT NULL DEFAULT '' COMMENT '微信号' ,
`wechat_userid`  varchar(255) NOT NULL DEFAULT '' ,
`wechat_status`  tinyint(1) NOT NULL DEFAULT 4 COMMENT '1:已关注；2：已冻结；4：未关注' ,
`nickname`  char(30) NOT NULL DEFAULT '' ,
`username`  char(30) NOT NULL DEFAULT '' COMMENT '用户名' ,
`password`  char(32) NOT NULL DEFAULT '' ,
`status`  tinyint(1) NOT NULL DEFAULT 0 ,
`phonestatus`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '手机绑定状态' ,
`emailsenddate`  varchar(50) NOT NULL DEFAULT '0' ,
`emailstatus`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '邮箱绑定状态' ,
`avatarstatus`  tinyint(1) NOT NULL DEFAULT 0 ,
`adminid`  tinyint(1) NOT NULL DEFAULT 0 ,
`groupid`  smallint(6) UNSIGNED NOT NULL DEFAULT 9 ,
`language`  varchar(12) NOT NULL DEFAULT 'zh-cn' COMMENT '语言' ,
`regip`  char(15) NOT NULL ,
`regdate`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`secques`  char(8) NOT NULL DEFAULT '' ,
`salt`  char(6) NOT NULL DEFAULT '' ,
`authstr`  char(30) NOT NULL ,
`newprompt`  smallint(6) UNSIGNED NOT NULL DEFAULT 0 COMMENT '消息数' ,
`timeoffset`  char(4) NOT NULL DEFAULT '9999' ,
`grid`  smallint(6) NOT NULL DEFAULT 0 ,
`uploads`  int(11) NOT NULL DEFAULT 0 ,
`downloads`  smallint(6) NOT NULL DEFAULT 0 ,
`collects`  smallint(6) NOT NULL DEFAULT 0 ,
PRIMARY KEY (`uid`),
UNIQUE KEY `email` (`email`) USING BTREE ,
KEY `groupid` (`groupid`) USING BTREE ,
KEY `username` (`username`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_user_field`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_user_field`;
CREATE TABLE `dzz_user_field` (
`uid`  int(10) UNSIGNED NOT NULL ,
`docklist`  text NOT NULL ,
`screenlist`  text NOT NULL ,
`applist`  text NOT NULL ,
`noticebanlist`  text NOT NULL ,
`iconview`  tinyint(1) NOT NULL DEFAULT 2 ,
`iconposition`  tinyint(1) NOT NULL DEFAULT 0 ,
`direction`  tinyint(1) NOT NULL DEFAULT 0 ,
`autolist`  tinyint(1) NOT NULL DEFAULT 1 ,
`taskbar`  enum('bottom','left','top','right') NOT NULL DEFAULT 'bottom' ,
`dateline`  int(10) UNSIGNED NOT NULL ,
`updatetime`  int(10) UNSIGNED NOT NULL ,
`attachextensions`  varchar(255) NOT NULL DEFAULT '-1' ,
`maxattachsize`  int(10) NOT NULL DEFAULT '-1' ,
`usesize`  bigint(20) UNSIGNED NOT NULL DEFAULT 0 ,
`addsize`  bigint(20) UNSIGNED NOT NULL DEFAULT 0 ,
`buysize`  bigint(20) UNSIGNED NOT NULL DEFAULT 0 ,
`wins`  text NOT NULL ,
`perm`  int(10) NOT NULL DEFAULT 0 ,
`privacy`  text NOT NULL ,
`userspace`  int(11) NOT NULL DEFAULT 0 COMMENT '用户空间大小，-1表示无空间，0表示不限制' ,
UNIQUE KEY `uid` (`uid`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_user_profile`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_user_profile`;
CREATE TABLE `dzz_user_profile` (
`uid`  int(10) UNSIGNED NOT NULL ,
`fieldid`  varchar(30) NOT NULL DEFAULT '' ,
`value`  text NOT NULL ,
`privacy`  smallint(3) NOT NULL DEFAULT 0 COMMENT '资料权限' ,
PRIMARY KEY (`uid`, `fieldid`)
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_user_profile_setting`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_user_profile_setting`;
CREATE TABLE `dzz_user_profile_setting` (
`fieldid`  varchar(255) NOT NULL DEFAULT '' ,
`available`  tinyint(1) NOT NULL DEFAULT 0 ,
`invisible`  tinyint(1) NOT NULL DEFAULT 0 ,
`needverify`  tinyint(1) NOT NULL DEFAULT 0 ,
`title`  varchar(255) NOT NULL DEFAULT '' ,
`description`  varchar(255) NOT NULL DEFAULT '' ,
`displayorder`  smallint(6) UNSIGNED NOT NULL DEFAULT 0 ,
`required`  tinyint(1) NOT NULL DEFAULT 0 ,
`unchangeable`  tinyint(1) NOT NULL DEFAULT 0 ,
`showincard`  tinyint(1) NOT NULL DEFAULT 0 ,
`showinthread`  tinyint(1) NOT NULL DEFAULT 0 ,
`showinregister`  tinyint(1) NOT NULL DEFAULT 0 ,
`allowsearch`  tinyint(1) NOT NULL DEFAULT 0 ,
`formtype`  varchar(255) NOT NULL DEFAULT 'text' ,
`size`  smallint(6) UNSIGNED NOT NULL DEFAULT 0 ,
`choices`  text NOT NULL ,
`privacy`  smallint(3) NOT NULL DEFAULT 0 COMMENT '资料权限' ,
`validate`  text NOT NULL ,
`customable`  tinyint(1) NOT NULL DEFAULT 1 ,
PRIMARY KEY (`fieldid`)
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_user_setting`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_user_setting`;
CREATE TABLE `dzz_user_setting` (
`id`  int(10) NOT NULL AUTO_INCREMENT COMMENT '主键id' ,
`uid`  int(10) UNSIGNED NOT NULL COMMENT '用户id' ,
`skey`  varchar(30) NOT NULL DEFAULT '' COMMENT '用户设置选项键' ,
`svalue`  text NULL COMMENT '用户设置值' ,
PRIMARY KEY (`id`),
UNIQUE KEY `skey` (`skey`, `uid`) USING BTREE ,
KEY `uid` (`uid`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_user_status`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_user_status`;
CREATE TABLE `dzz_user_status` (
`uid`  int(10) UNSIGNED NOT NULL ,
`regip`  char(15) NOT NULL DEFAULT '' ,
`lastip`  char(15) NOT NULL DEFAULT '' ,
`lastvisit`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`lastactivity`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`lastsendmail`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`invisible`  tinyint(1) NOT NULL DEFAULT 0 ,
`profileprogress`  tinyint(2) UNSIGNED NOT NULL DEFAULT 0 ,
PRIMARY KEY (`uid`),
KEY `lastactivity` (`lastactivity`, `invisible`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_user_thame`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_user_thame`;
CREATE TABLE `dzz_user_thame` (
`uid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`custom_backimg`  varchar(255) NOT NULL ,
`custom_url`  varchar(255) NOT NULL ,
`custom_btype`  tinyint(1) UNSIGNED NOT NULL ,
`custom_color`  varchar(255) NOT NULL DEFAULT '' ,
`thame`  smallint(6) UNSIGNED NOT NULL DEFAULT 0 ,
UNIQUE KEY `uid` (`uid`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_user_verify`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_user_verify`;
CREATE TABLE `dzz_user_verify` (
`uid`  int(10) UNSIGNED NOT NULL ,
`verify1`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '-1:已拒绝，0：待审核，1认证通过' ,
`verify2`  tinyint(1) NOT NULL DEFAULT 0 ,
`verify3`  tinyint(1) NOT NULL DEFAULT 0 ,
`verify4`  tinyint(1) NOT NULL DEFAULT 0 ,
`verify5`  tinyint(1) NOT NULL DEFAULT 0 ,
`verify6`  tinyint(1) NOT NULL DEFAULT 0 ,
`verify7`  tinyint(1) NOT NULL DEFAULT 0 ,
PRIMARY KEY (`uid`),
KEY `verify1` (`verify1`) USING BTREE ,
KEY `verify2` (`verify2`) USING BTREE ,
KEY `verify3` (`verify3`) USING BTREE ,
KEY `verify4` (`verify4`) USING BTREE ,
KEY `verify5` (`verify5`) USING BTREE ,
KEY `verify6` (`verify6`) USING BTREE ,
KEY `verify7` (`verify7`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_user_verify_info`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_user_verify_info`;
CREATE TABLE `dzz_user_verify_info` (
`vid`  mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT ,
`uid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`username`  varchar(30) NOT NULL DEFAULT '' ,
`verifytype`  tinyint(1) NOT NULL DEFAULT 0 COMMENT ' 审核类型0:资料审核, 1:认证1, 2:认证2, 3:认证3, 4:认证4, 5:认证5' ,
`flag`  tinyint(1) NOT NULL DEFAULT 0 COMMENT ' -1:被拒绝 0:待审核 1:审核通过' ,
`field`  text NOT NULL ,
`orgid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`dateline`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
PRIMARY KEY (`vid`),
KEY `verifytype` (`verifytype`, `flag`) USING BTREE ,
KEY `uid` (`uid`, `verifytype`, `dateline`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_user_wechat`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_user_wechat`;
CREATE TABLE `dzz_user_wechat` (
`uid`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`openid`  char(28) NOT NULL DEFAULT '' ,
`appid`  char(18) NOT NULL DEFAULT '' ,
`unionid`  char(29) NOT NULL DEFAULT '' ,
`dateline`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
UNIQUE KEY `uid` (`uid`) USING BTREE ,
UNIQUE KEY `openid` (`openid`, `appid`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_usergroup`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_usergroup`;
CREATE TABLE `dzz_usergroup` (
`groupid`  smallint(6) UNSIGNED NOT NULL AUTO_INCREMENT ,
`radminid`  tinyint(3) NOT NULL DEFAULT 0 ,
`type`  enum('system','special','member') NOT NULL DEFAULT 'member' ,
`system`  varchar(255) NOT NULL DEFAULT 'private' ,
`grouptitle`  varchar(255) NOT NULL DEFAULT '' ,
`creditshigher`  int(10) NOT NULL DEFAULT 0 ,
`creditslower`  int(10) NOT NULL DEFAULT 0 ,
`stars`  tinyint(3) NOT NULL DEFAULT 0 ,
`color`  varchar(255) NOT NULL DEFAULT '' ,
`icon`  varchar(255) NOT NULL DEFAULT '' ,
`allowvisit`  tinyint(1) NOT NULL DEFAULT 0 ,
`allowsendpm`  tinyint(1) NOT NULL DEFAULT 1 ,
`allowinvite`  tinyint(1) NOT NULL DEFAULT 0 ,
`allowmailinvite`  tinyint(1) NOT NULL DEFAULT 0 ,
`maxinvitenum`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 ,
`inviteprice`  smallint(6) UNSIGNED NOT NULL DEFAULT 0 ,
`maxinviteday`  smallint(6) UNSIGNED NOT NULL DEFAULT 0 ,
PRIMARY KEY (`groupid`),
KEY `creditsrange` (`creditshigher`, `creditslower`) USING BTREE
)ENGINE=MyISAM;

-- ----------------------------
-- Table structure for `dzz_usergroup_field`
-- ----------------------------
DROP TABLE IF EXISTS `dzz_usergroup_field`;
CREATE TABLE `dzz_usergroup_field` (
`groupid`  smallint(6) UNSIGNED NOT NULL DEFAULT 0 ,
`maxspacesize`  int(10) NOT NULL DEFAULT 0 ,
`attachextensions`  varchar(255) NOT NULL ,
`maxattachsize`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
`perm`  int(10) UNSIGNED NOT NULL DEFAULT 0 ,
UNIQUE KEY `groupid` (`groupid`) USING BTREE
)ENGINE=MyISAM;

DROP TABLE IF EXISTS `dzz_billfish_folderrecord`;
CREATE TABLE `dzz_billfish_folderrecord` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `bfid` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'billfish目录id',
  `fid` char(19) NOT NULL DEFAULT '' COMMENT 'pichome目录id',
  `appid` char(6) NOT NULL DEFAULT '' COMMENT '库id',
  PRIMARY KEY (id),
  KEY `bfid` (`bfid`,`appid`),
  KEY `fid` (`fid`),
  KEY `appid` (`appid`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- table dzz_billfish_record
--
DROP TABLE IF EXISTS `dzz_billfish_record`;
CREATE TABLE `dzz_billfish_record` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `bid` int(11) UNSIGNED NOT NULL,
  `rid` char(32) NOT NULL DEFAULT '' COMMENT '对应resources表id',
  `appid` char(6) NOT NULL DEFAULT '' COMMENT '库id',
  `thumb` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '缩略图id',
  PRIMARY KEY (`id`),
  KEY `rid` (`rid`),
  KEY `appid` (`appid`),
  KEY `bid` (`bid`,`appid`) USING BTREE
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- table dzz_billfish_taggrouprecord
--
DROP TABLE IF EXISTS `dzz_billfish_taggrouprecord`;
CREATE TABLE dzz_billfish_taggrouprecord (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `cid` char(19) NOT NULL DEFAULT '' COMMENT '标签分类id',
  `bcid` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'billfish标签分类id',
  `appid` char(6) NOT NULL DEFAULT '' COMMENT 'appid',
  PRIMARY KEY (`id`),
  KEY `bcid` (`bcid`,`appid`),
  KEY `appid` (`appid`),
  KEY `cid` (`cid`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- table dzz_billfish_tagrecord
--
DROP TABLE IF EXISTS `dzz_billfish_tagrecord`;
CREATE TABLE `dzz_billfish_tagrecord` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `lid` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '标签id',
  `name` varchar(128) NOT NULL DEFAULT '' COMMENT 'billfish标签',
  `appid` char(6) NOT NULL DEFAULT '' COMMENT '库id',
  PRIMARY KEY (`id`),
  KEY `btid` (`name`,`appid`),
  KEY `tid` (`lid`),
  KEY `appid` (`appid`)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `dzz_eagle_folderrecord`;
CREATE TABLE `dzz_eagle_folderrecord`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `efid` char(64) NOT NULL DEFAULT '0' COMMENT 'eagle目录id',
  `fid` char(19) NOT NULL DEFAULT '' COMMENT 'pichome目录id',
  `appid` char(6) NOT NULL DEFAULT '' COMMENT '库id',
  PRIMARY KEY (`id`),
  KEY `bfid` (`efid`,`appid`),
  KEY `fid` (`fid`),
  KEY `appid` (`appid`)
) ENGINE=MyISAM;

-- ----------------------------
-- Table structure for pichome_eagle_record
-- ----------------------------
DROP TABLE IF EXISTS `dzz_eagle_record`;
CREATE TABLE `dzz_eagle_record`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `rid` char(32) NOT NULL DEFAULT '',
  `eid` char(64) NOT NULL DEFAULT '',
  `dateline` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '时间',
  `appid` char(6) NOT NULL DEFAULT '' COMMENT '库id',
  PRIMARY KEY (`id`),
  KEY `rid` (`rid`),
  KEY `eid` (`eid`),
  KEY `appid` (`appid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `dzz_pichome_vapp_tag`;
CREATE TABLE `dzz_pichome_vapp_tag`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tid` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '标签id',
  `appid` char(6) NOT NULL DEFAULT '' COMMENT '库id',
  `hots` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '使用次数',
  PRIMARY KEY (`id`),
  KEY `tappid` (`tid`, `appid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `dzz_local_record`;
CREATE TABLE `dzz_local_record`  (
  `id` char(32) NOT NULL DEFAULT '' COMMENT '主键id',
  `appid` char(6) NOT NULL DEFAULT '' COMMENT '库id',
  `dateline` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '时间',
  `rid` char(32) NOT NULL DEFAULT '' COMMENT '文件id',
  `path` blob NULL COMMENT '路径',
  PRIMARY KEY (`id`),
  KEY `appid` (`appid`),
  KEY `dateline` (`dateline`),
  KEY `rid` (`rid`)
) ENGINE = MyISAM;