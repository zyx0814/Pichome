(function(e){function t(t){for(var o,r,s=t[0],u=t[1],d=t[2],c=0,l=[];c<s.length;c++)r=s[c],Object.prototype.hasOwnProperty.call(i,r)&&i[r]&&l.push(i[r][0]),i[r]=0;for(o in u)Object.prototype.hasOwnProperty.call(u,o)&&(e[o]=u[o]);p&&p(t);while(l.length)l.shift()();return n.push.apply(n,d||[]),a()}function a(){for(var e,t=0;t<n.length;t++){for(var a=n[t],o=!0,r=1;r<a.length;r++){var s=a[r];0!==i[s]&&(o=!1)}o&&(n.splice(t--,1),e=u(u.s=a[0]))}return e}var o={},r={index:0},i={index:0},n=[];function s(e){return u.p+"js/"+({system_temp:"system_temp"}[e]||e)+"."+{"chunk-74c32c70":"1f5e9382","chunk-76f23146":"0d2957ed",system_temp:"c7e32270","chunk-2b4f90f7":"ec4ac270","chunk-7828662a":"e587f025","chunk-9f9c2568":"538835dd","chunk-af3b1b98":"850ec152","chunk-e730cc06":"3ce9a994","chunk-5cdcd199":"070f9c94","chunk-2d0a3327":"ad2684c9","chunk-2d0bdbc6":"4ec7bc5e","chunk-2d0dd46d":"0816c17e","chunk-2d0efd3c":"44a093d1","chunk-2d20fcd9":"50f81306","chunk-2d21ddf7":"9f7b329e","chunk-494f643e":"77a11d42","chunk-5ae5cc35":"25761a21"}[e]+".js"}function u(t){if(o[t])return o[t].exports;var a=o[t]={i:t,l:!1,exports:{}};return e[t].call(a.exports,a,a.exports,u),a.l=!0,a.exports}u.e=function(e){var t=[],a={"chunk-74c32c70":1,"chunk-76f23146":1,system_temp:1,"chunk-2b4f90f7":1,"chunk-7828662a":1,"chunk-9f9c2568":1,"chunk-af3b1b98":1,"chunk-e730cc06":1,"chunk-5cdcd199":1,"chunk-494f643e":1,"chunk-5ae5cc35":1};r[e]?t.push(r[e]):0!==r[e]&&a[e]&&t.push(r[e]=new Promise((function(t,a){for(var o="css/"+({system_temp:"system_temp"}[e]||e)+"."+{"chunk-74c32c70":"4b7d665e","chunk-76f23146":"6fc79cd8",system_temp:"a56510eb","chunk-2b4f90f7":"519dec70","chunk-7828662a":"c81657ef","chunk-9f9c2568":"5356ad7d","chunk-af3b1b98":"b822363f","chunk-e730cc06":"987283b7","chunk-5cdcd199":"6f46c04d","chunk-2d0a3327":"31d6cfe0","chunk-2d0bdbc6":"31d6cfe0","chunk-2d0dd46d":"31d6cfe0","chunk-2d0efd3c":"31d6cfe0","chunk-2d20fcd9":"31d6cfe0","chunk-2d21ddf7":"31d6cfe0","chunk-494f643e":"7502109f","chunk-5ae5cc35":"d763cbce"}[e]+".css",i=u.p+o,n=document.getElementsByTagName("link"),s=0;s<n.length;s++){var d=n[s],c=d.getAttribute("data-href")||d.getAttribute("href");if("stylesheet"===d.rel&&(c===o||c===i))return t()}var l=document.getElementsByTagName("style");for(s=0;s<l.length;s++){d=l[s],c=d.getAttribute("data-href");if(c===o||c===i)return t()}var p=document.createElement("link");p.rel="stylesheet",p.type="text/css",p.onload=t,p.onerror=function(t){var o=t&&t.target&&t.target.src||i,n=new Error("Loading CSS chunk "+e+" failed.\n("+o+")");n.code="CSS_CHUNK_LOAD_FAILED",n.request=o,delete r[e],p.parentNode.removeChild(p),a(n)},p.href=i;var m=document.getElementsByTagName("head")[0];m.appendChild(p)})).then((function(){r[e]=0})));var o=i[e];if(0!==o)if(o)t.push(o[2]);else{var n=new Promise((function(t,a){o=i[e]=[t,a]}));t.push(o[2]=n);var d,c=document.createElement("script");c.charset="utf-8",c.timeout=120,u.nc&&c.setAttribute("nonce",u.nc),c.src=s(e);var l=new Error;d=function(t){c.onerror=c.onload=null,clearTimeout(p);var a=i[e];if(0!==a){if(a){var o=t&&("load"===t.type?"missing":t.type),r=t&&t.target&&t.target.src;l.message="Loading chunk "+e+" failed.\n("+o+": "+r+")",l.name="ChunkLoadError",l.type=o,l.request=r,a[1](l)}i[e]=void 0}};var p=setTimeout((function(){d({type:"timeout",target:c})}),12e4);c.onerror=c.onload=d,document.head.appendChild(c)}return Promise.all(t)},u.m=e,u.c=o,u.d=function(e,t,a){u.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:a})},u.r=function(e){"undefined"!==typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},u.t=function(e,t){if(1&t&&(e=u(e)),8&t)return e;if(4&t&&"object"===typeof e&&e&&e.__esModule)return e;var a=Object.create(null);if(u.r(a),Object.defineProperty(a,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var o in e)u.d(a,o,function(t){return e[t]}.bind(null,o));return a},u.n=function(e){var t=e&&e.__esModule?function(){return e["default"]}:function(){return e};return u.d(t,"a",t),t},u.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},u.p="/admin/system/dist/",u.oe=function(e){throw console.error(e),e};var d=window["webpackJsonp"]=window["webpackJsonp"]||[],c=d.push.bind(d);d.push=t,d=d.slice();for(var l=0;l<d.length;l++)t(d[l]);var p=c;n.push([0,"chunk-vendors"]),a()})({0:function(e,t,a){e.exports=a("1181")},1181:function(e,t,a){"use strict";a.r(t);a("e260"),a("e6cf"),a("cca6"),a("a79d");var o=a("2b0e"),r=function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{attrs:{id:"app"}},[a("router-view")],1)},i=[],n=a("2877"),s={},u=Object(n["a"])(s,r,i,!1,null,null,null),d=u.exports,c=(a("96cf"),a("1da1")),l=a("5530"),p=a("2f62"),m=a("bc3a"),f=a.n(m),_=(a("d3b7"),a("8c4f")),h=function(){return a.e("system_temp").then(a.bind(null,"d9c5"))},g=function(){return a.e("system_temp").then(a.bind(null,"b2b4"))},b=function(){return a.e("system_temp").then(a.bind(null,"dc5b"))},y=function(){return a.e("system_temp").then(a.bind(null,"29ce"))},v=function(){return a.e("system_temp").then(a.bind(null,"8f18"))},k=function(){return a.e("system_temp").then(a.bind(null,"4c0a"))},w=function(){return a.e("system_temp").then(a.bind(null,"18cd"))},x=function(){return a.e("system_temp").then(a.bind(null,"77f3"))},z=function(){return a.e("system_temp").then(a.bind(null,"f100"))},D=function(){return a.e("system_temp").then(a.bind(null,"1d9c"))};o["default"].use(_["a"]);var S=[{path:"/",name:"index",component:h,redirect:"/updatecache",meta:{title:"appname"},children:[{path:"/updatecache",name:"updatecache",component:g,meta:{title:"updatecache",active:"updatecache"}},{path:"/cron",name:"cron",component:w,redirect:"/cron/view",children:[{path:"/cron/view",name:"cronView",component:x,meta:{title:"cron",active:"cron"}},{path:"/cron/edit/:id",name:"cronEdit",component:z,meta:{title:"cron2",active:"cron"}}]},{path:"/database",name:"database",component:b,redirect:"/database/export",children:[{path:"/database/export",name:"DatabaseExport",component:y,meta:{title:"database1",active:"database"}},{path:"/database/import",name:"DatabaseImport",component:v,meta:{title:"database2",active:"database"}},{path:"/database/runquery",name:"DatabaseRunquery",component:k,meta:{title:"database3",active:"database"}}]},{path:"/systemupgrade/:version?",name:"systemupgrade",component:D,meta:{title:"upgrade",active:"systemupgrade"}}]}],P=new _["a"]({routes:S}),T=P,L=a("50a0"),C=a("a925"),A=a("b2d6"),M=a.n(A),I=a("f0d9"),F=a.n(I),N=a("1ae0"),E=a.n(N),O=Object(l["a"])({appname:"系统工具",updatecache:"更新缓存",database:"数据库",database1:"导出-数据库",database2:"恢复-数据库",database3:"升级-数据库",cron:"计划任务",cron1:"编辑计划任务",cron2:"编辑-计划任务",upgrade:"在线升级",input_name:"请输入名称",add_plan:"添加计划",default_data:"未获取到相关数据",nav_updatecache_confirm:"确认开始",nav_updatecache_verify:"开始更新",nav_updatecache_completed:"更新结果",tools_updatecache_data:"数据缓存",tools_updatecache_tpl:"模板缓存",tools_updatecache_memory:"内存缓存",tools_updatecache_tips:'<li style="list-style-type:none;margin-bottom: 10px;font-weight: 700;font-size: 16px;">提示信息</li><li>当站点进行了数据恢复、升级或者工作出现异常的时候，您可以使用本功能重新生成缓存。更新缓存的时候，可能让服务器负载升高，请尽量避开会员访问的高峰时间</li><li>数据缓存：更新站点的全部数据缓存</li><li>模板缓存：更新论坛模板、风格等缓存文件，当您修改了模板或者风格，但是没有立即生效的时候使用</li>',tools_updatecache_waiting:"正在更新缓存，请稍候......",update_cache_succeed:"全部缓存更新完毕",update_cache_succeed1:"{num} 秒后将回退",update_cache_succeed2:"直接回退",db_export_tips:'<li style="list-style-type:none;margin-bottom: 10px;font-weight: 700;font-size: 16px;">提示信息</li><li>数据备份功能根据您的选择备份全部Dzz!数据，导出的数据文件可用“数据恢复”功能或 phpMyAdmin 导入。</li><li>全部备份均不包含模板文件和附件文件。模板、附件的备份只需通过 FTP 等下载 template/、data/attachment/ 目录即可，Dzz! 不提供单独备份。</li><li>MySQL Dump 的速度比 Dzz! 分卷备份快很多，但需要服务器支持相关的 Shell 权限，同时由于 MySQL 本身的兼容性问题，通常进行备份和恢复的服务器应当具有相同或相近的版本号才能顺利进行。因此 MySQL Dump 是有风险的：一旦进行备份或恢复操作的服务器其中之一禁止了 Shell，或由于版本兼容性问题导致导入失败，您将无法使用 MySQL Dump 备份或由备份数据恢复；Dzz! 分卷备份没有此限制。</li><li>数据备份选项中的设置，仅供高级用户的特殊用途使用，当您尚未对数据库做全面细致的了解之前，请使用默认参数备份，否则将导致备份数据错误等严重问题。</li><li>十六进制方式可以保证备份数据的完整性，但是备份文件会占用更多的空间。</li><li>压缩备份文件可以让您的备份文件占用更小的空间。</li>',nav_db_runquery:"升级",db_export_type:"数据备份类型",all_data_table:"所有数据表",db_export_custom:"自定义备份",more_options:"更多选项",db_export_method:"数据备份方式",db_export_shell:"系统 MySQL Dump (Shell) 备份",db_export_multivol:"Dzz! 分卷备份 - 文件长度限制(单位：KB)",db_export_options_extended_insert:"使用扩展插入(Extended Insert)方式",db_export_options_sql_compatible:"建表语句格式",db_export_options_charset:"强制字符集",db_export_usehex:"十六进制方式",db_export_usezip:"压缩备份文件",db_export_zip:"压缩备份",db_export_zip_1:"多分卷压缩成一个文件",db_export_zip_2:"每个分卷压缩成单独文件",db_export_zip_3:"不压缩",db_export_filename:"备份文件名",db_import_tips:'<li style="list-style-type:none;margin-bottom: 10px;font-weight: 700;font-size: 16px;">提示信息</li><li>本功能在恢复备份数据的同时，将全部覆盖原有数据，请确定恢复前已将程序关闭，恢复全部完成后可以将程序重新开放。</li><li>恢复数据前请<a href="http://down.oaooa.com/restore.zip" target="_bank">下载恢复</a>文件 解压并找到 restore.php 文件，然后将 restore.php 文件上传到程序文件夹data目录下。<span class="help-block">为了您站点的安全，成功恢复数据后请务必及时删除 restore.php 文件。</span></li><li>您可以在数据备份记录处查看站点的备份文件的详细信息，删除过期的备份,并导入需要的备份。</li><li>您可以在本页面数据备份记录处导入备份恢复数据，也可以通过在浏览器中执行 <a href="{furl}" target="_bank">{furl}</a> 恢复数据</li>',db_volume:"卷数",db_import_confirm:"导入和当前 Dzz! 版本不一致的数据极有可能产生无法解决的故障，您确定继续吗？",db_import_confirm_sql:"您确定导入该备份吗？",db_import_confirm_zip:"您确定解压该备份吗？",db_import_unzip:"解压缩",db_import_del_custom:"确定删除该条备份数据？",db_runquery_tips:'<li style="list-style-type:none;margin-bottom: 10px;font-weight: 700;font-size: 16px;">提示信息</li><li>出于安全考虑，Dzz! 后台默认情况下禁止 SQL 语句直接执行，只能使用常用 SQL 当中的内容，</li><li>如果您想自己随意书写 SQL 升级语句，需要将 config/config_global.php 当中的 $_config[admincp][runquery] 设置修改为 1。</li>',db_runquery_sql:"Dzz! 数据库升级 - 请将数据库升级语句粘贴在下面",db_runquery_createcompatible:"转换建表语句格式和字符集",appname_core:"计划任务",misc_cron_tips:'<li style="list-style-type:none;margin-bottom: 10px;font-weight: 700;font-size: 16px;">提示信息</li><li>计划任务是 Dzz! 提供的一项使系统在规定时间自动执行某些特定任务的功能，在需要的情况下，您也可以方便的将其用于站点功能的扩展。</li><li>计划任务是与系统核心紧密关联的功能特性，不当的设置可能造成站点功能的隐患，严重时可能导致站点无法正常运行，因此请务必仅在您对计划任务特性十分了解，并明确知道正在做什么、有什么样后果的时候才自行添加或修改任务项目。</li><li>此处和其他功能不同，本功能中完全按照站点系统默认时差对时间进行设定和显示，而不会依据某一用户或管理员的时差设定而改变显示或设置的时间值。</li><li class="help-block">计划任务默认通过用户访问触发。缺点是影响用户访问体验；计划任务执行不及时。可以修改config.php文件，设置参数 $_config[remote][on]=1; $_config[remote][cron]=1; 停止这种触发方式。</li><li class="help-block">推荐设置通过系统计划任务来触发。如linux系统，可以修改/etc/crontab,加入一行 * * * * root php F:qcoscron.php >>/dev/null 2>$1</li><li>详细请查阅官方文档 <a target="_blank" href="http://help.oaooa.com/corpus/list?cid=24#fid_330">管理员手册-计划任务</a> 中的相关内容</li>',available:"可用",inbuilt:"内置",misc_cron_last_run:"上次执行时间",misc_cron_next_run:"下次执行时间",cron_run_task:"确定执行任务？",cron_del_task:"确定删除该条数据？",misc_cron_edit_tips:"<li>您正在对系统内置的计划任务进行编辑，除非非常了解 Dzz! 结构，否则强烈建议不要修改默认设置。</li><li>请在修改之前记录原有设置，不当的设置将可能导致站点出现不可预期的错误。</li>",misc_cron_edit_weekday_comment:"设置星期几执行本任务，“*”为不限制，本设置会覆盖下面的“日”设定",misc_cron_edit_day_comment:"设置哪一日执行本任务，“*”为不限制",misc_cron_edit_hour_comment:"设置哪一小时执行本任务，“*”为不限制",misc_cron_edit_minute_comment:'设置哪些分钟执行本任务，至多可以设置 12 个分钟值，多个值之间用半角逗号 "," 隔开，留空为不限制',misc_cron_edit_filename:"任务脚本",misc_cron_edit_filename_comment:"设置本任务的执行程序文件名，请勿包含路径，系统计划任务位于 core/cron/ 目录中",founder_upgrade_updatelist:"获取待更新文件列表",founder_upgrade_download:"下载更新",founder_upgrade_compare:"本地文件比对",founder_upgrade_upgrading:"正在升级",founder_upgrade_complete:"升级完成",founder_upgrade_preupdatelist:"待更新文件列表",founder_upgrade1_tip1:"您上次升级到",founder_upgrade1_tip2:"请继续完成升级",founder_upgrade_continue:"继续升级",founder_upgrade_recheck:"重新检测",upgrade_checking:"正在检测新的升级版本",upgrade_latest_version:"您目前使用的已经是最新版本，不需要升级",founder_upgrade_select_version:"检测到有新的版本可供升级，您可以选择自动升级或者下载安装包手动升级。",founder_upgrade_backup_remind:"自动升级前请您先备份程序及数据库，确定开始升级吗？",founder_upgrade_automatically:"自动升级",upgrade_close_site:"升级前，请先关闭站点，并对文件及数据备份",founder_upgrade_store_directory:'<li style="list-style-type:none;margin-bottom: 10px;font-weight: 700;font-size: 16px;">提示信息</li><li>文件存放目录: ./data/update/pichome{msg}</li>',upgrade_redownload:"文件 {file} 下载出现问题，请查看您的服务器网络以及data目录是否有写权限，请确认无误点击重试",upgrade_downloading_file:"正在从官方下载更新文件",upgrade_download_complete_to_compare:"文件下载完成，即将进行下一步",founder_upgrade_diff_show:'<li style="list-style-type:none;margin-bottom: 10px;font-weight: 700;font-size: 16px;">提示信息</li><li>与本地文件的比对结果，状态&nbsp;&nbsp;<a class="el-link el-link--danger"><span class="el-link--inner">差异</span></a>&nbsp;&nbsp;表示该本地文件被修改过。</li><li>注意：如果您的源文件是通过&nbsp;&nbsp;<a class="el-link el-link--danger"><span class="el-link--inner">非二进制</span></a>&nbsp;&nbsp;方式上传到服务器，可能导致对比结果不准确</li><li>升级文件已经全部下载完毕，并存储到服务器目录: ./data/update/pichome{version}</li><li>继续升级，将会把现有的旧文件备份到目录: ./data/back/pichome{oldversion}，并用新的文件进行覆盖</li>',founder_upgrade_diff:"差异",founder_upgrade_normal:"正常",founder_upgrade_new:"新增",filecheck_nofound_md5file:"不存在校验文件，无法进行此操作",server_address:"服务器地址",FTP_server_IP_site_domain:"可以是 FTP 服务器的 IP 地址或域名",server_port:"服务器端口",default_for_the_21st:"默认为 21",accounts_supreme_authority:"该帐号必需具有以下权限：读取文件、写入文件、删除文件、创建目录、子目录继承",sitepath:"站点根目录",site_absolute_path_root_directory:"站点根目录的绝对路径或相对于 FTP 主目录的相对路径，结尾不要加斜杠“/”，“.”表示 FTP 主目录",use_Passive_Mode:"使用被动模式",general_condition_passive_mode:"一般情况下非被动模式即可，如果存在上传失败问题，可尝试打开此设置",enable_secure_link:"启用安全链接",notice_FTP_open_SSL:"注意：FTP 服务器必需开启了 SSL",upgrade_cannot_access_file:"发现您的目录及文件无修改权限，请您填写 ftp 账号，或者修改文件权限为可读可写后重试",upgrade_backuping:"正在备份原始文件...",upgrade_backup_error:"备份原始文件出错",upgrade_backup_complete:"备份完成，正在进行升级...",upgrade_ftp_upload_error:"ftp上传文件 {file} 出错， 请修改文件权限后重新上传 或 重新设置ftp账号",upgrade_copy_error:"复制文件 {file} 出错，请检测原始文件是否存在，重新复制 或 通过ftp上传复制文件",upgrade_file_successful:"文件升级成功，即将进入更新数据库",founder_upgrade_reset_ftp:"重新设置 ftp 账号",founder_upgrade_set_ftp:"设置 ftp 账号",founder_upgrade_recopy:"重新复制",upgrade_successful1:"恭喜您，升级成功!",upgrade_successful2:"您当前的版本为：[pichome{msg}]",upgrade_successful3:"为安全起见，升级文件已保存至{dir}目录，",upgrade_successful4:"备份文件已保存至{backdir}目录",upgrade_download_upgradelist_error:"获取待更新文件列表失败，是否重新获取？",upgrade_none:"没有该升级信息"},E.a),q=O,U=a("b3136"),j=a.n(U),B=Object(l["a"])({appname:"System Tool",updatecache:"Refresh Cache",database:"Database",database1:"Export-Database",database2:"Restore-Database",database3:"Upgrade-Database",cron:"Scheduled Tasks",cron1:"Edit schedule task",cron2:"Edit-Schedule task",upgrade:"Online Upgrade",input_name:"Please enter a name",add_plan:"Add plan",default_data:"No relevant data was obtained",nav_updatecache_confirm:"Confirmation Begins",nav_updatecache_verify:"Start Update",nav_updatecache_completed:"Update Result",tools_updatecache_data:"Data cache",tools_updatecache_tpl:"Template cache",tools_updatecache_memory:"Memory Cache",tools_updatecache_tips:'<li style="list-style-type:none;margin-bottom: 10px;font-weight: 700;font-size: 16px;">Prompt message</li><li>You can use this feature to regenerate the cache when the site is having data recovery, upgrades, or anomalies in your work. When updating the cache, it may increase the server load. Please try to avoid the peak time of member access</li><li>Data Cache: Update all data caches for your site</li><li>Template Cache: Update the cache file of forum template, style, etc., when you modify the template or style, but it does not take effect immediately</li>',tools_updatecache_waiting:"Updating cache, please wait......",update_cache_succeed:"All cached updates are complete",update_cache_succeed1:"It will be back in {num} seconds",update_cache_succeed2:"Directly back",db_export_tips:'<li style="list-style-type:none;margin-bottom: 10px;font-weight: 700;font-size: 16px;">Prompt message</li><li>The data backup function backs up all Dzz! data according to your choice. The exported data files can be imported with the “Data Recovery” function or phpMyAdmin.</li><li>MySQL Dump is much faster than Dzz! Volume Backup, but requires the server to support the relevant shell permissions, and due to compatibility issues with MySQL itself, servers that are typically backed up and restored should have the same or similar version number to proceed smoothly. Therefore, MySQL Dump is risky: once one of the servers that perform the backup or restore operation bans the shell, or the import fails due to version compatibility issues, you will not be able to use MySQL Dump backup or restore from backup data; Dzz! Backups do not have this limitation.</li><li>The settings in the data backup option are only for special users. For those who have not yet fully understood the database, please use the default parameter backup, otherwise it will cause serious problems such as backup data errors.</li><li>The hexadecimal method guarantees the integrity of the backup data, but the backup file takes up more space.</li><li>Compressing backup files can make your backup files take up less space.</li>',nav_db_runquery:"upgrade",db_export_type:"Data backup type",all_data_table:"All data sheets",db_export_custom:"Custom backup",more_options:"More options",db_export_method:"Data backup method",db_export_shell:"System MySQL Dump (Shell) backup",db_export_multivol:"Dzz! Volume backup - file length limit (unit: KB)",db_export_options_extended_insert:"Use Extended Insert",db_export_options_sql_compatible:"Table statement format",db_export_options_charset:"Forced character set",db_export_usehex:"Hexadecimal mode",db_export_usezip:"Compress backup file",db_export_zip:"Compressed backup",db_export_zip_1:"Multi-volume compression into one file",db_export_zip_2:"Each sub-volume is compressed into a separate file",db_export_zip_3:"Not compressed",db_export_filename:"Backup file name",db_import_tips:'<li style="list-style-type:none;margin-bottom: 10px;font-weight: 700;font-size: 16px;">Prompt message</li><li>This function will completely overwrite the original data while restoring the backup data. Please make sure that the program is closed before the recovery. After the recovery is completed, the program can be reopened.</li><li>Please <a href="http://down.oaooa.com/restore.zip" target="_bank">download the recovery file before restoring the data </a>Unzip and find the restore.php file, then upload the restore.php file to the program folder data directory.<span class="help-block">For the security of your site, be sure to delete the restore.php file in time after successfully recovering the data.</span></li><li>You can view the details of the site is backup files at the data backup record, delete the expired backups, and import the required backups.</li><li>ou can import backup recovery data from the data backup record on this page, or you can restore data by executing <a href="{furl}" target="_bank">{furl}</a> in your browser.</li>',db_volume:"Number of volumes",db_import_confirm:"Importing data that is inconsistent with the current Dzz! version is very likely to cause unresolved failures. Are you sure you want to continue？",db_import_confirm_sql:"Are you sure you want to import this backup？",db_import_confirm_zip:"Are you sure you unzipped the backup？",db_import_unzip:"unzip",db_import_del_custom:"Are you sure you want to delete this backup data?",db_runquery_tips:'<li style="list-style-type:none;margin-bottom: 10px;font-weight: 700;font-size: 16px;">Prompt message</li><li>For security reasons, Dzz! background prohibits SQL statements from executing directly by default. You can only use the contents of common SQL </li><li>If you want to write SQL upgrade statements at will, you need to put $ in config/config_global.php. The $_config[admincp][runquery] setting is changed to 1</li>',db_runquery_sql:"Dzz! Database upgrade - Please paste the database upgrade statement below",db_runquery_createcompatible:"Convert table statement format and character set",appname_core:"Planning tasks",misc_cron_tips:'<li style="list-style-type:none;margin-bottom: 10px;font-weight: 700;font-size: 16px;">Prompt message</li><li>The scheduled task is a feature provided by Dzz! that allows the system to automatically perform certain tasks at specified times, and you can easily use it for site functionality extensions if needed.</li><li>The scheduled task is a feature that is closely related to the core of the system. Improper settings may cause hidden dangers of the site function. In severe cases, the site may not work properly. Therefore, please be sure to know only the characteristics of the scheduled task and know that you are doing it. What and what kind of consequences will be added or modified by the task.</li><li>Different from other functions here, this function sets and displays the time according to the default time difference of the site system, and does not change the time value of display or setting according to the time difference setting of a certain user or administrator.</li><li class="help-block">The scheduled task is triggered by default through user access. The disadvantage is that it affects the user access experience; the scheduled tasks are not performed in time. It can be modified config.php Files, setting parameters$_ config[remote][on]=1; $_ Config[remote] [cron] =1; stop this trigger.</li><li class="help-block">The recommended settings are triggered by system scheduled tasks. For example, in Linux system, you can modify / etc / crontab and add a line of * * root PHP F:: QCOS cron.php  >>/dev/null 2>$1</li><li>Please refer to the official documents for details <a target="_blank" href="http://help.oaooa.com/corpus/list?cid=24#fid_330">Administrator task manual</a> Related contents in</li>',available:"Available",inbuilt:"Built in",misc_cron_last_run:"Last execution time",misc_cron_next_run:"Next execution time",cron_run_task:"Are you sure to carry out the task?",cron_del_task:"Are you sure you want to delete this data?",misc_cron_edit_tips:'<li style="list-style-type:none;margin-bottom: 10px;font-weight: 700;font-size: 16px;">Prompt message</li><li>You are editing the scheduled tasks built into the system, and unless you understand the Dzz! structure very well, it is highly recommended not to modify the default settings.</li><li>Please record the original settings before making changes. Improper settings may cause unexpected errors on your site.</li>',misc_cron_edit_weekday_comment:"Set the day of the week to perform this task. “*” is not limited. This setting will override the “Day” setting below",misc_cron_edit_day_comment:'Set which day to perform this task, "*" is not limited',misc_cron_edit_hour_comment:'Set which hour to perform this task, "*" is not limited',misc_cron_edit_minute_comment:'Set which minutes to perform this task, you can set up to 12 minutes value, separated by a comma ",", and left blank',misc_cron_edit_filename:"Task script",misc_cron_edit_filename_comment:"Set the executor file name for this task. Do not include the path. The system plan task is located in the core/cron/ directory",founder_upgrade_updatelist:"Get the list of files to be updated",founder_upgrade_download:"Download update",founder_upgrade_compare:"Local file comparison",founder_upgrade_upgrading:"upgrading",founder_upgrade_complete:"update completed",founder_upgrade_preupdatelist:"List of files to be updated",founder_upgrade1_tip1:"You last upgraded to",founder_upgrade1_tip2:"Please continue to complete the upgrade",founder_upgrade_continue:"Continue to upgrade",founder_upgrade_recheck:"Retest",upgrade_checking:"Detecting new upgraded version",upgrade_latest_version:"You are currently using the latest version and do not need to upgrade",founder_upgrade_select_version:"A new version has been detected for upgrade, you can choose to upgrade automatically or download the installation package manually.",founder_upgrade_backup_remind:"Please backup the program and database before the automatic upgrade, be sure to start the upgrade？",founder_upgrade_automatically:"auto update",upgrade_close_site:"Please close the site and back up files and data before upgrading",founder_upgrade_store_directory:'<li style="list-style-type:none;margin-bottom: 10px;font-weight: 700;font-size: 16px;">Prompt message</li><li>File storage directory: ./data/update/pichome{msg}</li>',upgrade_redownload:"There is a problem with the file {file} download. Please check if your server network and data directory have write permission. Please confirm after retry",upgrade_downloading_file:"Downloading updates from the official",upgrade_download_complete_to_compare:"The download is complete and the next step is about to take",founder_upgrade_diff_show:'<li style="list-style-type:none;margin-bottom: 10px;font-weight: 700;font-size: 16px;">Prompt message</li><li>The result of the comparison with the local file, the status <a class="el-link el-link--danger"><span class="el-link--inner">difference</span></a> indicates that the local file has been modified</li><li>Note: If your source file is via <a class="el-link el-link--danger"><span class="el-link--inner">Non-binary</span></a> mode upload to the server, may result in inaccurate comparison results</li><li>The upgrade files have all been downloaded and stored in the server directory: ./data/update/pichome{version}</li><li>Continue to upgrade，will back up existing old files to the directory:  ./data/back/pichome{oldversion},and overwrite with new files</li>',founder_upgrade_diff:"difference",founder_upgrade_normal:"normal",founder_upgrade_new:"New",filecheck_nofound_md5file:"There is no verification file and this operation is not possible",server_address:"server address",FTP_server_IP_site_domain:"Can be the IP address or domain name of the FTP server",server_port:"Server port",default_for_the_21st:"The default is 21",accounts_supreme_authority:"The account must have the following permissions: read files, write files, delete files, create directories, subdirectory inheritance",sitepath:"Site root directory",site_absolute_path_root_directory:'The absolute path of the site root directory or the relative path relative to the FTP home directory, do not add a slash "/" at the end, "." indicates the FTP home directory',use_Passive_Mode:"Use passive mode",general_condition_passive_mode:"In general, non-passive mode is OK. If there is an upload failure, try opening this setting",enable_secure_link:"Enable secure links",notice_FTP_open_SSL:"Note: SSL must be enabled on the FTP server",upgrade_cannot_access_file:"Found that your directory and files have no modification rights, please fill in the ftp account, or modify the file permissions to be readable and writable and try again",upgrade_backuping:"Backing up the original file...",upgrade_backup_error:"Error backing up the original file",upgrade_backup_complete:"Backup completed, upgrade is in progress...",upgrade_ftp_upload_error:"ftp upload file {file} error, please modify file permissions and re-upload or reset ftp account",upgrade_copy_error:"Error copying file {file}, please check if the original file exists, re-copy or upload the file by ftp",upgrade_file_successful:"The file upgrade is successful and will enter the update database",founder_upgrade_reset_ftp:"Reset ftp account",founder_upgrade_set_ftp:"Set ftp account",founder_upgrade_recopy:"Recopy",upgrade_successful1:"Congratulations, the upgrade is successful!",upgrade_successful2:"Your current version is:[pichome{msg}]",upgrade_successful3:"For security reasons, the upgrade file has been saved to the {dir} directory,",upgrade_successful4:"backup file Saved to {backdir} directory",upgrade_download_upgradelist_error:"Failed to get the list of files to be updated, re-acquire?",upgrade_none:"No such upgrade information"},j.a),R=B,Q=a("4897"),$=a.n(Q);o["default"].use(p["a"]),o["default"].use(C["a"]);var H={en:Object(l["a"])(Object(l["a"])({},R),M.a),zh:Object(l["a"])(Object(l["a"])({},q),F.a)},G=new C["a"]({locale:"zh-CN",messages:H});$.a.i18n((function(e,t){return G.t(e,t)}));var Y=new p["a"].Store({state:{LoadHtml:!1,FormHash:"",IfuserAgent:0,userData:[],notice_num:0,navMenu:[],navTitle:"",appName:"",headerName:"system",leftActive:"",DataLeft:[]},mutations:{initLoadHtml:function(e){e.LoadHtml=!0},initLeftActive:function(e,t){e.leftActive=t},setFormHash:function(e,t){e.FormHash=t},initDataLeft:function(e){var t=[{text:G.t("updatecache"),name:"updatecache",icon:"el-icon-refresh"},{text:G.t("upgrade"),name:"systemupgrade",icon:"el-icon-upload"}];e.DataLeft=t},initappName:function(e){e.appName=G.t("appname")},initNavTitle:function(e,t){e.navTitle=t},initUserData:function(e,t){e.userData=t},initNotice_num:function(e,t){e.notice_num=t},initNavMenu:function(e,t){e.navMenu=t},initCommonMessage:function(e,t){G.locale=t.userData.language,this.commit("initDataLeft"),this.commit("initappName"),this.commit("initNavTitle",t.navtitle),this.commit("setFormHash",t.hash),this.commit("initUserData",t.userData),this.commit("initNotice_num",t.notice_num),this.commit("initNavMenu",t.navMenu),this.commit("initLoadHtml")},initIfuserAgent:function(e,t){e.IfuserAgent=t}},getters:{GetHeaderName:function(e){return e.headerName},GetFormHash:function(e){return e.FormHash},GetDataLeft:function(e){return e.DataLeft},GetUserData:function(e){return e.userData},GetNavMenu:function(e){return e.navMenu},GetNavTitle:function(e){return e.navTitle},GetAppName:function(e){return e.appName},GetLanguage:function(e){return e.userData.language}},actions:{commonMessage:function(e){var t=this;return Object(c["a"])(regeneratorRuntime.mark((function a(){var o,r,i,n;return regeneratorRuntime.wrap((function(a){while(1)switch(a.prev=a.next){case 0:return o=e.commit,r=t,T.beforeEach((function(e,t,a){r.dispatch("updatesession"),o("initLeftActive",e.meta.active),a()})),Object(L["b"])("theme"),a.next=6,f.a.post("admin.php?mod=system&op=common");case 6:i=a.sent,n=i.data,o("initCommonMessage",n);case 9:case"end":return a.stop()}}),a)})))()},updatesession:function(){f.a.post("admin.php?mod=login&op=updatesession")}}}),K=a("5c96");o["default"].use(K["Button"]),o["default"].use(K["Container"]),o["default"].use(K["Header"]),o["default"].use(K["Autocomplete"]),o["default"].use(K["Aside"]),o["default"].use(K["Main"]),o["default"].use(K["Footer"]),o["default"].use(K["Image"]),o["default"].use(K["Menu"]),o["default"].use(K["Submenu"]),o["default"].use(K["MenuItem"]),o["default"].use(K["MenuItemGroup"]),o["default"].use(K["Form"]),o["default"].use(K["Input"]),o["default"].use(K["FormItem"]),o["default"].use(K["Radio"]),o["default"].use(K["RadioGroup"]),o["default"].use(K["Table"]),o["default"].use(K["TableColumn"]),o["default"].use(K["Row"]),o["default"].use(K["Col"]),o["default"].use(K["Tag"]),o["default"].use(K["Select"]),o["default"].use(K["Option"]),o["default"].use(K["Tooltip"]),o["default"].use(K["DatePicker"]),o["default"].use(K["PageHeader"]),o["default"].use(K["Dropdown"]),o["default"].use(K["DropdownMenu"]),o["default"].use(K["DropdownItem"]),o["default"].use(K["Badge"]),o["default"].use(K["Avatar"]),o["default"].use(K["Tabs"]),o["default"].use(K["TabPane"]),o["default"].use(K["Tree"]),o["default"].use(K["Popover"]),o["default"].use(K["Loading"].directive),o["default"].use(K["ColorPicker"]),o["default"].use(K["Rate"]),o["default"].use(K["Dialog"]),o["default"].use(K["Popconfirm"]),o["default"].use(K["Upload"]),o["default"].use(K["Switch"]),o["default"].use(K["Checkbox"]),o["default"].use(K["Link"]),o["default"].use(K["Cascader"]),o["default"].use(K["CascaderPanel"]),o["default"].use(K["Scrollbar"]),o["default"].use(K["Progress"]),o["default"].use(K["CheckboxGroup"]),o["default"].use(K["CheckboxButton"]),o["default"].use(K["Card"]),o["default"].use(K["Pagination"]),o["default"].use(K["Alert"]),o["default"].use(K["Drawer"]),o["default"].use(K["Steps"]),o["default"].use(K["Step"]),o["default"].use(K["Breadcrumb"]),o["default"].use(K["BreadcrumbItem"]),o["default"].use(K["Slider"]),o["default"].use(K["Divider"]),o["default"].prototype.$loading=K["Loading"].service,o["default"].prototype.$message=K["Message"],o["default"].prototype.$confirm=K["MessageBox"].confirm,o["default"].prototype.$alert=K["MessageBox"].alert,o["default"].prototype.$prompt=K["MessageBox"].prompt,o["default"].prototype.$notify=K["Notification"],o["default"].use(K["Carousel"]),o["default"].use(K["CarouselItem"]);a("ab05"),a("4dcb");var V=a("4328"),W=a.n(V);o["default"].config.productionTip=!1,o["default"].prototype.AxiosApi="",o["default"].prototype.hash="",f.a.interceptors.request.use((function(e){return"post"===e.method&&(e.data=W.a.stringify(e.data)),e})),window.onresize=function(){J()};var J=function(){document.body.clientWidth<=1024?Y.commit("initIfuserAgent",1):Y.commit("initIfuserAgent",0)};J(),Y.dispatch("commonMessage"),f.a.interceptors.response.use((function(e){if(e.data){var t=encodeURIComponent(window.location.href);e.data.isuser?window.location.href="user.php?mod=login&referer="+t:0==e.data.loginstatus&&(window.location.href="admin.php?mod=login&referer="+t)}return e})),o["default"].prototype.axios=f.a,new o["default"]({i18n:G,store:Y,router:T,render:function(e){return e(d)}}).$mount("#app")},"1ae0":function(e,t,a){var o,r=a("9523");e.exports=(o={setting:"设置",retry:"重试",Upload_again:"重新上传",upload_failed:"上传失败",submit:"提交",submit_success:"提交成功",submit_failure:"提交失败",submit_failure_refresh:"提交失败，刷新重试",confirm:"确定",cancel:"取消",submission:"提交中...",reset:"重置",import:"导入",importing:"导入中",import_type_app:"导入应用",import_failure:"导入失败",start_import:"开始导入",continue_import:"继续导入",import_click_stop:"导入中,点击停止",import_set:"导入设置",choose_import_way:"选择导入方式",import_way:"导入方式",import_finish:"导入完成",import_success:"导入成功",all_import:"全部导入",export:"导出",select_all_export:"全选导出",all_export:"全部导出",export_immediately:"立即导出",export_failure:"导出失败！",confirm_export_data:"确定导出数据吗？",options:"选项",add_options:"添加选项",export_change:"导出选项",nape:"项",export_range:"导出范围",select:"请选择",edit:"编辑",edit_success:"编辑成功",edit_error:"编辑失败",modification:"修改",modification_success:"修改成功",no_modification:"不可修改",revisability:"可修改",modification_hou:"修改后",recovery:"恢复",recovery_unsuccess:"恢复失败",label:"标签",select_all:"全选",yes:"是",no:"否",default:"默认",filename:"文件名称",name:"名字",version:"版本",time:"时间",type:"类型",size:"大小",mode:"方式",number:"数量",operation:"操作",delete:"删除",delete1:"删？",delete_success:"删除成功",delete_unsuccess:"删除失败",content_cannot:"内容不能为空",add:"添加",add_successfully:"添加成功",add_failed:"添加失败",custom:"自定义",execute:"执行",update_success:"更新成功",update_unsuccess:"更新失败",weekly:"每周",everyday:"每日",per_hour:"每小时",hour:"小时",minute:"分钟",per_minute:"每分钟",account:"帐号",password:"密码",open_start:"开启",close:"关闭",save:"保存",save_changes:"保存更改",save_success:"保存成功",save_failed:"保存失败",loading:"加载中",pieces:"个",width:"宽度",height:"高度",times:"次",people:"人"},r(o,"filename","文件名"),r(o,"username","用户名"),r(o,"time","时间"),r(o,"position","位置"),r(o,"please_input","请输入"),r(o,"warning","警告"),r(o,"search","搜索"),r(o,"data_error","数据错误，刷新重试"),r(o,"create","创建"),r(o,"header_avatar1","用户中心"),r(o,"header_avatar2","选择语言"),r(o,"header_avatar3","中文"),r(o,"header_avatar4","英文"),r(o,"header_avatar5","修改头像"),r(o,"header_avatar6","密码与安全"),r(o,"header_avatar7","退出登录"),r(o,"header_notice1","通知"),r(o,"header_notice2","还没有通知"),r(o,"header_notice3","查看所有通知"),o)},"4dcb":function(e,t,a){},"50a0":function(e,t,a){"use strict";a.d(t,"a",(function(){return o})),a.d(t,"b",(function(){return r}));a("d3b7");function o(){var e=document.querySelector("#leftResize"),t=document.querySelector(".el-aside");e.onmousedown=function(e){return document.onmousemove=function(e){var a=e.clientX+1;t.style.width=a+"px"},document.onmouseup=function(){document.onmousemove=null,document.onmouseup=null},!1}}function r(e){a("b8d7")("./".concat(e,"/index.css"))}},b3136:function(e,t,a){var o,r=a("9523");e.exports=(o={setting:"setting",retry:"Retry",Upload_again:"Upload again",upload_failed:"Upload failed",submit:"submit",submit_success:"submit successfully",submit_failure:"submit failure",submit_failure_refresh:"Commit failed, refresh and try again",confirm:"confirm",cancel:"cancel",submission:"submitting...",reset:"reset",import:"import",importing:"Importing",import_type_app:"Import application",import_failure:"Import failed",start_import:"Start importing",continue_import:"Continue to import",import_click_stop:"In the import, click to stop",import_set:"Import settings",choose_import_way:"Choose import method",import_way:"Import method",import_finish:"Import completed",import_success:"Successfully imported",all_import:"Import all",export:"export",select_all_export:"Select all",all_export:"Export all",export_immediately:"Export now",export_failure:"Export failed!",confirm_export_data:"Are you sure to export data?",options:"option",add_options:"Add option",export_change:"Export options",nape:"item",export_range:"Export range",select:"please choose",edit:"edit",edit_success:"Successful editing",edit_error:"Edit failed",modification:"modify",modification_success:"Successfully modified",no_modification:"not editable",revisability:"Can be modified",modification_hou:"After modification",recovery:"restore",recovery_unsuccess:"Recovery failure",label:"label",select_all:"select all",yes:"yes",no:"no",default:"default",filename:"file name",name:"name",version:"version",time:"time",type:"type",size:"size",mode:"mode",number:"number",operation:"operation",delete:"delete",delete1:"delete?",delete_success:"successfully deleted",delete_unsuccess:"failed to delete",content_cannot:"Content cannot be empty",add:"Add",add_successfully:"Added successfully",add_failed:"Add failed",custom:"customize",execute:"carried out",update_success:"Update completed",update_unsuccess:"Update failed",weekly:"weekly",everyday:"daily",per_hour:"per hour",hour:"hour",minute:"minute",per_minute:"per minute",account:"accounts",password:"Password",open_start:"Open",close:"Close",save:"Preservation",save_changes:"Save changes",save_success:"Save success",save_failed:"Save failed",loading:"Loading",pieces:"pcs",width:"width",height:"height",times:"times",people:"people"},r(o,"filename","File name"),r(o,"username","User name"),r(o,"time","Time"),r(o,"position","Position"),r(o,"please_input","Please input"),r(o,"warning","Warning"),r(o,"search","Search"),r(o,"data_error","Data error, refresh and try again"),r(o,"create","Create"),r(o,"header_avatar1","User center"),r(o,"header_avatar2","Language"),r(o,"header_avatar3","简体中文"),r(o,"header_avatar4","English"),r(o,"header_avatar5","Avatar"),r(o,"header_avatar6","Password"),r(o,"header_avatar7","Sign out"),r(o,"header_notice1","Notice"),r(o,"header_notice2","No notification yet"),r(o,"header_notice3","View all notifications"),o)},b8d7:function(e,t,a){var o={"./portal_theme/index.css":["a99d","chunk-76f23146"],"./theme/index.css":["8aa1","chunk-74c32c70"]};function r(e){if(!a.o(o,e))return Promise.resolve().then((function(){var t=new Error("Cannot find module '"+e+"'");throw t.code="MODULE_NOT_FOUND",t}));var t=o[e],r=t[0];return a.e(t[1]).then((function(){return a.t(r,7)}))}r.keys=function(){return Object.keys(o)},r.id="b8d7",e.exports=r}});