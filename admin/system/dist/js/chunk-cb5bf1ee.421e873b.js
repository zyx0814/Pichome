(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-cb5bf1ee"],{"0e9b":function(t,e,a){"use strict";var s=a("2f7e"),o=a.n(s);o.a},"2f7e":function(t,e,a){},6254:function(t,e,a){"use strict";a.r(e);var s=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticStyle:{"margin-left":"10px",display:"inherit"}},[a("el-dropdown",{staticStyle:{width:"35px",height:"35px"},attrs:{trigger:"click","hide-on-click":!1},on:{command:t.handleAvatar}},[t.GetUserData.icon?[a("el-tooltip",{staticClass:"item",attrs:{effect:"dark",content:t.GetUserData.username,placement:"left"}},[a("el-avatar",{attrs:{size:35,src:t.GetUserData.icon}})],1)]:[a("el-tooltip",{staticClass:"item",attrs:{effect:"dark",content:t.GetUserData.username,placement:"left"}},[a("el-avatar",{style:{background:t.GetUserData.headerColor},attrs:{size:35}},[t._v(t._s(t.GetUserData.firstword))])],1)],a("el-dropdown-menu",{staticClass:"avatar-dropdown",attrs:{slot:"dropdown"},slot:"dropdown"},[a("el-dropdown-item",{attrs:{command:"personal"}},[t._v("个人中心")]),a("el-dropdown-item",{attrs:{command:"systeminfo"}},[t._v("系统管理")]),a("el-divider",{staticClass:"adjust-divider"}),a("el-dropdown-item",{attrs:{command:"OutLogin"}},[t._v("退出站点")])],1)],2)],1)},o=[],n=(a("96cf"),a("1da1")),i=a("5530"),r=a("2f62"),c={data:function(){return{}},computed:Object(i["a"])({},Object(r["b"])(["GetUserData","GetFormHash","GetLanguage"])),methods:{handleAvatar:function(t){var e=this;switch(t){case"collection":window.location.href="index.php?mod=collection";break;case"personal":window.location.href="user.php?mod=my";break;case"help":window.open("https://www.yuque.com/pichome");break;case"problem":window.open("https://support.qq.com/products/340252");break;case"setting":window.location.href="index.php?mod=pichome&op=admin&do=basic";break;case"library":window.location.href="index.php?mod=pichome&op=library";break;case"about":this.$alert('<div class="aboutlogo">\n      \t\t\t<img src="dzz/pichome/image/phlogo.png" alt="">\n      \t\t</div>\n      \t\t<div class="aboutmessage">\n      \t\t\t<div class="aboutlist">\n      \t\t\t\t<span class="title">软件名称：</span><span class="mes">欧奥PicHome</span>\n      \t\t\t</div>\n      \t\t\t<div class="aboutlist">\n      \t\t\t\t<span class="title">版本信息：</span><span class="mes">'+this.GetUserData.version+'</span>\n      \t\t\t</div>\n      \t\t\t<div class="aboutlist">\n      \t\t\t\t<span class="title">版权信息：</span><span class="mes">Powered By oaooa PicHome © 2020-2022 欧奥图文</span>\n      \t\t\t</div>\n      \t\t\t<div class="aboutlist">\n      \t\t\t\t<span class="title">网站地址：</span><span class="mes"><a class="address" href="https://oaooa.com/" target="_blank">oaooa.com</a></span>\n      \t\t\t</div>\n      \t\t</div>',"",{customClass:"aboutPichome",showClose:!1,showConfirmButton:!1,dangerouslyUseHTMLString:!0,closeOnClickModal:!0});break;case"systeminfo":window.location.href="index.php?mod=systeminfo";break;case"system":window.open("admin.php?mod=system");break;case"orguser":window.open("admin.php?mod=orguser");break;case"systemlog":window.open("admin.php?mod=systemlog");break;case"OutLogin":this.$message,this.$confirm("您确定要注销登录?","提示",{confirmButtonText:"确定",cancelButtonText:"取消",type:"warning"}).then(Object(n["a"])(regeneratorRuntime.mark((function t(){var a,s;return regeneratorRuntime.wrap((function(t){while(1)switch(t.prev=t.next){case 0:return t.next=2,e.axios.post("user.php?mod=login&op=logging&inajax=1&action=logout&formhash="+e.GetFormHash+"&t="+(new Date).getTime());case 2:a=t.sent,s=a.data,s.success?window.location.reload():e.$message.error(s.msg||"退出登录失败");case 5:case"end":return t.stop()}}),t)})))).catch((function(){}));break}return!1}}},l=c,d=(a("0e9b"),a("9c24"),a("2877")),p=Object(d["a"])(l,s,o,!1,null,"a6c00da6",null);e["default"]=p.exports},"864f":function(t,e,a){},"9c24":function(t,e,a){"use strict";var s=a("864f"),o=a.n(s);o.a}}]);