(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["setting_temp"],{"103b":function(t,e,a){"use strict";a.r(e);var r=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a(t.IfuserAgent<2?"PCtemplate":"Mtemplate",{tag:"component",attrs:{IfuserAgent:t.IfuserAgent,formdata:t.formdata,buttonLoad:t.buttonLoad,maildelimiterVal:t.maildelimiterVal},on:{AddSCOCKET:t.AddSCOCKET,AddPHP:t.AddPHP,formSubmit:t.SubmitDatalist}})},n=[];a("4160"),a("a434"),a("d3b7"),a("159b"),a("a4d3"),a("e01a"),a("d28b"),a("3ca3"),a("ddb0"),a("a630"),a("fb6a"),a("b0c0"),a("25f0");function s(t,e){(null==e||e>t.length)&&(e=t.length);for(var a=0,r=new Array(e);a<e;a++)r[a]=t[a];return r}function i(t,e){if(t){if("string"===typeof t)return s(t,e);var a=Object.prototype.toString.call(t).slice(8,-1);return"Object"===a&&t.constructor&&(a=t.constructor.name),"Map"===a||"Set"===a?Array.from(t):"Arguments"===a||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(a)?s(t,e):void 0}}function o(t,e){var a;if("undefined"===typeof Symbol||null==t[Symbol.iterator]){if(Array.isArray(t)||(a=i(t))||e&&t&&"number"===typeof t.length){a&&(t=a);var r=0,n=function(){};return{s:n,n:function(){return r>=t.length?{done:!0}:{done:!1,value:t[r++]}},e:function(t){throw t},f:n}}throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}var s,o=!0,u=!1;return{s:function(){a=t[Symbol.iterator]()},n:function(){var t=a.next();return o=t.done,t},e:function(t){u=!0,s=t},f:function(){try{o||null==a["return"]||a["return"]()}finally{if(u)throw s}}}}a("96cf");var u=a("1da1"),c={props:["FormHash","IfuserAgent"],data:function(){return{buttonLoad:!1,maildelimiterVal:[{val:"0",text:this.$t("email_header_separator2")},{val:"1",text:this.$t("email_header_separator1")},{val:"2",text:this.$t("email_header_separator3")}],formdata:{adminemail:"",mail:{mailsend:"1",maildelimiter:[],mailusername:"0",sendmail_silent:"0",esmtp:[],smtp:[]}}}},created:function(){this.getDatalist()},methods:{AddSCOCKET:function(){var t={id:"",delete:"",server:"",port:25,auth:"0",from:"",auth_username:"",auth_password:""};this.formdata.mail.esmtp.push(t)},AddPHP:function(){var t={id:"",delete:"",server:"",port:25};this.formdata.mail.smtp.push(t)},getDatalist:function(){var t=this;return Object(u["a"])(regeneratorRuntime.mark((function e(){var a,r,n,s,i,u,c,m,l,f,d,p;return regeneratorRuntime.wrap((function(e){while(1)switch(e.prev=e.next){case 0:return t.buttonLoad=!0,e.next=3,t.axios.get(t.AxiosApi+"admin.php?mod=setting&op=interface&operation=mail");case 3:if(a=e.sent,r=a.data,r&&r.setting&&(n=r.setting,s=r.smtps,i={adminemail:n.adminemail?n.adminemail:"",mail:{mailsend:n.mail&&n.mail.mailsend?n.mail.mailsend:"1",maildelimiter:n.mail&&n.mail.maildelimiter?n.mail.maildelimiter:[],mailusername:n.mail&&n.mail.mailusername?n.mail.mailusername:"0",sendmail_silent:n.mail&&n.mail.sendmail_silent?n.mail.sendmail_silent:"0",esmtp:[],smtp:[]}},t.formdata=i,s&&s.length)){u=[],c=[],m=o(s);try{for(m.s();!(l=m.n()).done;)f=l.value,d={id:f.id,delete:"",server:f.server,port:f.port,auth:f.auth?"1":"0",from:f.from,auth_username:f.auth_username,auth_password:f.auth_password},p={id:f.id,delete:"",server:f.server,port:f.port},u.push(d),c.push(p)}catch(g){m.e(g)}finally{m.f()}t.formdata.mail.esmtp=u,t.formdata.mail.smtp=u}t.buttonLoad=!1;case 7:case"end":return e.stop()}}),e)})))()},SubmitDatalist:function(){var t=this;return Object(u["a"])(regeneratorRuntime.mark((function e(){var a,r,n,s;return regeneratorRuntime.wrap((function(e){while(1)switch(e.prev=e.next){case 0:if(!(t.IfuserAgent<2)){e.next=7;break}if(t.FormHash){e.next=4;break}return t.$message.error(t.$t("submit_failure_refresh")),e.abrupt("return",!1);case 4:a=t.$loading({lock:!0,text:t.$t("submission"),spinner:"el-icon-loading"}),e.next=11;break;case 7:if(t.FormHash){e.next=10;break}return t.$notify({type:"danger",message:t.$t("submit_failure_refresh")}),e.abrupt("return",!1);case 10:r=t.$toast.loading({message:t.$t("submission"),forbidClick:!0,duration:0});case 11:return e.next=13,t.axios.post(t.AxiosApi+"admin.php?mod=setting&op=interface&operation=mail",{settingsubmit:!0,formhash:t.FormHash,settingnew:t.formdata});case 13:n=e.sent,s=n.data,s.success&&(t.formdata.mail.esmtp&&t.formdata.mail.esmtp.length&&t.formdata.mail.esmtp.forEach((function(e,a){e.delete&&t.formdata.mail.esmtp.splice(a,1)})),t.formdata.mail.smtp&&t.formdata.mail.smtp.length&&t.formdata.mail.smtp.forEach((function(e,a){e.delete&&t.formdata.mail.smtp.splice(a,1)}))),t.IfuserAgent<2?(a.close(),s.success?t.$message({message:t.$t("submit_success"),type:"success"}):t.$message.error(t.$t("submit_failure"))):(r.clear(),s.success?t.$notify({type:"success",message:t.$t("submit_success")}):t.$notify({type:"danger",message:t.$t("submit_failure")}));case 17:case"end":return e.stop()}}),e)})))()}},components:{PCtemplate:function(){return a.e("chunk-36ed7802").then(a.bind(null,"bea2"))},Mtemplate:function(){return a.e("chunk-8af60fbc").then(a.bind(null,"9149"))}}},m=c,l=a("2877"),f=Object(l["a"])(m,r,n,!1,null,"0b699cec",null);e["default"]=f.exports},"19db":function(t,e,a){"use strict";a.r(e);var r=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a(t.IfuserAgent<2?"PCtemplate":"Mtemplate",{tag:"component",attrs:{IfuserAgent:t.IfuserAgent,formdata:t.formdata},on:{formSubmit:t.SubmitDatalist}})},n=[],s=(a("d3b7"),a("96cf"),a("1da1")),i={props:["FormHash","IfuserAgent"],data:function(){return{formdata:{test_from:"",test_to:""}}},created:function(){},methods:{SubmitDatalist:function(){var t=this;return Object(s["a"])(regeneratorRuntime.mark((function e(){var a,r,n,s;return regeneratorRuntime.wrap((function(e){while(1)switch(e.prev=e.next){case 0:if(!(t.IfuserAgent<2)){e.next=7;break}if(t.FormHash){e.next=4;break}return t.$message.error(t.$t("submit_failure_refresh")),e.abrupt("return",!1);case 4:a=t.$loading({lock:!0,text:t.$t("submission"),spinner:"el-icon-loading"}),e.next=11;break;case 7:if(t.FormHash){e.next=10;break}return t.$notify({type:"danger",message:t.$t("submit_failure_refresh")}),e.abrupt("return",!1);case 10:r=t.$toast.loading({message:t.$t("submission"),forbidClick:!0,duration:0});case 11:return e.next=13,t.axios.post(t.AxiosApi+"admin.php?mod=setting&op=interface&operation=mailcheck",{settingsubmit:!0,formhash:t.FormHash,settingnew:t.formdata});case 13:n=e.sent,s=n.data,t.IfuserAgent<2?(a.close(),s.success?t.$message({message:t.$t("submit_success"),type:"success"}):t.$message.error(t.$t("submit_failure"))):(r.clear(),s.success?t.$notify({type:"success",message:t.$t("submit_success")}):t.$notify({type:"danger",message:t.$t("submit_failure")}));case 16:case"end":return e.stop()}}),e)})))()},router_mail:function(){this.$router.push({path:"/mail"})}},components:{PCtemplate:function(){return a.e("chunk-56b92ffd").then(a.bind(null,"782a"))},Mtemplate:function(){return a.e("chunk-65f82ec1").then(a.bind(null,"801d"))}}},o=i,u=a("2877"),c=Object(u["a"])(o,r,n,!1,null,"8ff2679a",null);e["default"]=c.exports},"25f0":function(t,e,a){"use strict";var r=a("6eeb"),n=a("825a"),s=a("d039"),i=a("ad6d"),o="toString",u=RegExp.prototype,c=u[o],m=s((function(){return"/a/b"!=c.call({source:"a",flags:"b"})})),l=c.name!=o;(m||l)&&r(RegExp.prototype,o,(function(){var t=n(this),e=String(t.source),a=t.flags,r=String(void 0===a&&t instanceof RegExp&&!("flags"in u)?i.call(t):a);return"/"+e+"/"+r}),{unsafe:!0})},"2b2b":function(t,e,a){},"311a":function(t,e,a){"use strict";a.r(e);var r=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a(t.IfuserAgent<2?"PCtemplate":"Mtemplate",{tag:"component"})},n=[],s=(a("d3b7"),a("5530")),i=a("2f62"),o={data:function(){return{}},created:function(){},computed:Object(s["a"])({},Object(i["c"])(["IfuserAgent"])),methods:{},components:{PCtemplate:function(){return a.e("chunk-8253696e").then(a.bind(null,"95ef"))},Mtemplate:function(){return a.e("chunk-27b595b5").then(a.bind(null,"1530"))}},mounted:function(){}},u=o,c=a("2877"),m=Object(c["a"])(u,r,n,!1,null,null,null);e["default"]=m.exports},"371c":function(t,e,a){"use strict";a.r(e);var r=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a(t.IfuserAgent<2?"PCtemplate":"Mtemplate",{tag:"component",attrs:{IfuserAgent:t.IfuserAgent,formdata:t.formdata,buttonLoad:t.buttonLoad},on:{formSubmit:t.SubmitDatalist}})},n=[],s=(a("d3b7"),a("c96a"),a("96cf"),a("1da1")),i=a("5530"),o=a("2f62"),u={props:["FormHash","IfuserAgent"],data:function(){return{formdata:{thumbsize:{small:{width:"",height:""},middle:{width:"",height:""},large:{width:"",height:""}},thumb_active:"0"},buttonLoad:!1}},computed:Object(i["a"])({},Object(o["c"])(["LoadHtml","navTitle"])),watch:{LoadHtml:{handler:function(t){if(t){var e=this.$t(this.$route.meta.title);document.title=e+"-"+this.$t("appname")+"-"+this.navTitle}},immediate:!0}},created:function(){this.getDatalist()},methods:{getDatalist:function(){var t=this;return Object(s["a"])(regeneratorRuntime.mark((function e(){var a,r,n,s;return regeneratorRuntime.wrap((function(e){while(1)switch(e.prev=e.next){case 0:return t.buttonLoad=!0,e.next=3,t.axios.get(t.AxiosApi+"admin.php?mod=setting&op=interface&operation=image");case 3:a=e.sent,r=a.data,r&&r.setting&&(n=r.setting,r.smtps,s={thumbsize:{small:{width:n.thumbsize.small.width?n.thumbsize.small.width:7200,height:n.thumbsize.small.height?n.thumbsize.small.height:360},middle:{width:n.thumbsize.middle.width?n.thumbsize.middle.width:800,height:n.thumbsize.middle.height?n.thumbsize.middle.height:600},large:{width:n.thumbsize.large.width?n.thumbsize.large.width:1440,height:n.thumbsize.large.height?n.thumbsize.large.height:900}},thumb_active:n.thumb_active?n.thumb_active:"0"},t.formdata=s),t.buttonLoad=!1;case 7:case"end":return e.stop()}}),e)})))()},SubmitDatalist:function(){var t=this;return Object(s["a"])(regeneratorRuntime.mark((function e(){var a,r,n,s;return regeneratorRuntime.wrap((function(e){while(1)switch(e.prev=e.next){case 0:if(!(t.IfuserAgent<2)){e.next=7;break}if(t.FormHash){e.next=4;break}return t.$message.error(t.$t("submit_failure_refresh")),e.abrupt("return",!1);case 4:a=t.$loading({lock:!0,text:t.$t("submission"),spinner:"el-icon-loading"}),e.next=11;break;case 7:if(t.FormHash){e.next=10;break}return t.$notify({type:"danger",message:t.$t("submit_failure_refresh")}),e.abrupt("return",!1);case 10:r=t.$toast.loading({message:t.$t("submission"),forbidClick:!0,duration:0});case 11:return e.next=13,t.axios.post(t.AxiosApi+"admin.php?mod=setting&op=interface&operation=image",{settingsubmit:!0,formhash:t.FormHash,settingnew:t.formdata});case 13:n=e.sent,s=n.data,t.IfuserAgent<2?(a.close(),s.success?t.$message({message:t.$t("submit_success"),type:"success"}):t.$message.error(t.$t("submit_failure"))):(r.clear(),s.success?t.$notify({type:"success",message:t.$t("submit_success")}):t.$notify({type:"danger",message:t.$t("submit_failure")}));case 16:case"end":return e.stop()}}),e)})))()}},components:{PCtemplate:function(){return a.e("chunk-45f0a227").then(a.bind(null,"82fb"))},Mtemplate:function(){return a.e("chunk-201f656b").then(a.bind(null,"fc70"))}}},c=u,m=a("2877"),l=Object(m["a"])(c,r,n,!1,null,"f9aa448a",null);e["default"]=l.exports},"3ca3":function(t,e,a){"use strict";var r=a("6547").charAt,n=a("69f3"),s=a("7dd0"),i="String Iterator",o=n.set,u=n.getterFor(i);s(String,"String",(function(t){o(this,{type:i,string:String(t),index:0})}),(function(){var t,e=u(this),a=e.string,n=e.index;return n>=a.length?{value:void 0,done:!0}:(t=r(a,n),e.index+=t.length,{value:t,done:!1})}))},"4df4":function(t,e,a){"use strict";var r=a("0366"),n=a("7b0b"),s=a("9bdd"),i=a("e95a"),o=a("50c4"),u=a("8418"),c=a("35a1");t.exports=function(t){var e,a,m,l,f,d,p=n(t),g="function"==typeof this?this:Array,h=arguments.length,b=h>1?arguments[1]:void 0,w=void 0!==b,x=c(p),k=0;if(w&&(b=r(b,h>2?arguments[2]:void 0,2)),void 0==x||g==Array&&i(x))for(e=o(p.length),a=new g(e);e>k;k++)d=w?b(p[k],k):p[k],u(a,k,d);else for(l=x.call(p),f=l.next,a=new g;!(m=f.call(l)).done;k++)d=w?s(l,b,[m.value,k],!0):m.value,u(a,k,d);return a.length=k,a}},"5b41":function(t,e,a){"use strict";a.r(e);var r=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a(t.IfuserAgent<2?"PCtemplate":"Mtemplate",{tag:"component",attrs:{IfuserAgent:t.IfuserAgent,formdata:t.formdata,buttonLoad:t.buttonLoad},on:{formSubmit:t.SubmitDatalist}})},n=[],s=(a("d3b7"),a("96cf"),a("1da1")),i={props:["FormHash","IfuserAgent"],data:function(){return{formdata:{unRunExts:"",maxChunkSize:""},buttonLoad:!1}},created:function(){this.getDatalist()},methods:{getDatalist:function(){var t=this;return Object(s["a"])(regeneratorRuntime.mark((function e(){var a,r,n,s;return regeneratorRuntime.wrap((function(e){while(1)switch(e.prev=e.next){case 0:return t.buttonLoad=!0,e.next=3,t.axios.get(t.AxiosApi+"admin.php?mod=setting&op=interface&operation=upload");case 3:a=e.sent,r=a.data,r&&r.setting&&(n=r.setting,s={unRunExts:n.unRunExts?n.unRunExts:"",maxChunkSize:n.maxChunkSize?n.maxChunkSize:""},t.formdata=s),t.buttonLoad=!1;case 7:case"end":return e.stop()}}),e)})))()},SubmitDatalist:function(){var t=this;return Object(s["a"])(regeneratorRuntime.mark((function e(){var a,r,n,s;return regeneratorRuntime.wrap((function(e){while(1)switch(e.prev=e.next){case 0:if(!(t.IfuserAgent<2)){e.next=7;break}if(t.FormHash){e.next=4;break}return t.$message.error(t.$t("submit_failure_refresh")),e.abrupt("return",!1);case 4:a=t.$loading({lock:!0,text:t.$t("submission"),spinner:"el-icon-loading"}),e.next=11;break;case 7:if(t.FormHash){e.next=10;break}return t.$notify({type:"danger",message:t.$t("submit_failure_refresh")}),e.abrupt("return",!1);case 10:r=t.$toast.loading({message:t.$t("submission"),forbidClick:!0,duration:0});case 11:return e.next=13,t.axios.post(t.AxiosApi+"admin.php?mod=setting&op=interface&operation=upload",{settingsubmit:!0,formhash:t.FormHash,settingnew:t.formdata});case 13:n=e.sent,s=n.data,t.IfuserAgent<2?(a.close(),s.success?t.$message({message:t.$t("submit_success"),type:"success"}):t.$message.error(t.$t("submit_failure"))):(r.clear(),s.success?t.$notify({type:"success",message:t.$t("submit_success")}):t.$notify({type:"danger",message:t.$t("submit_failure")}));case 16:case"end":return e.stop()}}),e)})))()}},components:{PCtemplate:function(){return a.e("chunk-1ce8ae7e").then(a.bind(null,"9048"))},Mtemplate:function(){return a.e("chunk-745ec13c").then(a.bind(null,"f25b"))}}},o=i,u=a("2877"),c=Object(u["a"])(o,r,n,!1,null,"b020cd5a",null);e["default"]=c.exports},"5fa9":function(t,e,a){"use strict";a.r(e);var r=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a(t.IfuserAgent<2?"PCtemplate":"Mtemplate",{tag:"component",attrs:{IfuserAgent:t.IfuserAgent,formdata:t.formdata,imageUrl:t.imageUrl,applist:t.applist,serverspace:t.serverspace,buttonLoad:t.buttonLoad},on:{formSubmit:t.SubmitDatalist}})},n=[],s=(a("d3b7"),a("96cf"),a("1da1")),i={props:["FormHash","IfuserAgent"],data:function(){return{buttonLoad:!1,imageUrl:"",percentage:0,ispercentage:!1,formdata:{IsWatermarkstatus:"0",watermarkstatus:"0",watermarkminwidth:"",watermarkminheight:"",updatethumbwater:"0",watermarktype:"png",watermarktext:{text:"",fontpath:"",size:"",angle:"",color:"#FFFFFF00",shadowx:"",shadowy:"",shadowcolor:"#DDDDDD00",icolor:"#FFFFFF64",shadowicolor:"#DDDDDD64",translatex:"",translatey:"",skewx:"",skewy:""},watermarktrans:50,watermarkquality:80},fontpathVal:[]}},created:function(){this.getDatalist()},methods:{getDatalist:function(){var t=this;return Object(s["a"])(regeneratorRuntime.mark((function e(){var a,r,n,s;return regeneratorRuntime.wrap((function(e){while(1)switch(e.prev=e.next){case 0:return t.buttonLoad=!0,e.next=3,t.axios.get(t.AxiosApi+"admin.php?mod=setting&op=interface&operation=watermark");case 3:a=e.sent,r=a.data,r&&r.setting&&(n=r.setting,t.fontpathVal=r.fontlist,t.imageUrl=n.waterimg?n.waterimg:"",s={IsWatermarkstatus:n.IsWatermarkstatus?n.IsWatermarkstatus:"0",watermarkstatus:n.watermarkstatus?n.watermarkstatus:"0",watermarkminwidth:n.watermarkminwidth?n.watermarkminwidth:"",watermarkminheight:n.watermarkminheight?n.watermarkminheight:"",updatethumbwater:n.updatethumbwater?n.updatethumbwater:"0",watermarktype:"png",watermarktext:{text:n.watermarktext&&n.watermarktext.text?n.watermarktext.text:"",fontpath:n.watermarktext&&n.watermarktext.fontpath?n.watermarktext.fontpath:"",size:n.watermarktext&&n.watermarktext.size?n.watermarktext.size:"",angle:n.watermarktext&&n.watermarktext.angle?n.watermarktext.angle:"",color:n.watermarktext&&n.watermarktext.color?n.watermarktext.color:"#FFFFFF00",shadowx:n.watermarktext&&n.watermarktext.shadowx?n.watermarktext.shadowx:"",shadowy:n.watermarktext&&n.watermarktext.shadowy?n.watermarktext.shadowy:"",shadowcolor:n.watermarktext&&n.watermarktext.shadowcolor?n.watermarktext.shadowcolor:"#DDDDDD00",icolor:n.watermarktext&&n.watermarktext.icolor?n.watermarktext.icolor:"#FFFFFF64",shadowicolor:n.watermarktext&&n.watermarktext.shadowicolor?n.watermarktext.shadowicolor:"#DDDDDD64",translatex:n.watermarktext&&n.watermarktext.translatex?n.watermarktext.translatex:"",translatey:n.watermarktext&&n.watermarktext.translatey?n.watermarktext.translatey:"",skewx:n.watermarktext&&n.watermarktext.skewx?n.watermarktext.skewx:"",skewy:n.watermarktext&&n.watermarktext.skewy?n.watermarktext.skewy:""},watermarktrans:n.watermarktrans?n.watermarktrans:50,watermarkquality:n.watermarkquality?n.watermarkquality:80},t.formdata=s),t.buttonLoad=!1;case 7:case"end":return e.stop()}}),e)})))()},SubmitDatalist:function(){var t=this;return Object(s["a"])(regeneratorRuntime.mark((function e(){var a,r,n,s;return regeneratorRuntime.wrap((function(e){while(1)switch(e.prev=e.next){case 0:if(!(t.IfuserAgent<2)){e.next=7;break}if(t.FormHash){e.next=4;break}return t.$message.error(t.$t("submit_failure_refresh")),e.abrupt("return",!1);case 4:a=t.$loading({lock:!0,text:t.$t("submission"),spinner:"el-icon-loading"}),e.next=11;break;case 7:if(t.FormHash){e.next=10;break}return t.$notify({type:"danger",message:t.$t("submit_failure_refresh")}),e.abrupt("return",!1);case 10:r=t.$toast.loading({message:t.$t("submission"),forbidClick:!0,duration:0});case 11:return e.next=13,t.axios.post(t.AxiosApi+"admin.php?mod=setting&op=interface&operation=watermark",{settingsubmit:!0,formhash:t.FormHash,settingnew:t.formdata});case 13:n=e.sent,s=n.data,t.IfuserAgent<2?(a.close(),s.success?t.$message({message:t.$t("submit_success"),type:"success"}):t.$message.error(t.$t("submit_failure"))):(r.clear(),s.success?t.$notify({type:"success",message:t.$t("submit_success")}):t.$notify({type:"danger",message:t.$t("submit_failure")}));case 16:case"end":return e.stop()}}),e)})))()}},components:{PCtemplate:function(){return a.e("chunk-42c1b704").then(a.bind(null,"2022"))},Mtemplate:function(){return a.e("chunk-60968146").then(a.bind(null,"161e"))}}},o=i,u=a("2877"),c=Object(u["a"])(o,r,n,!1,null,"eb37d3e6",null);e["default"]=c.exports},6547:function(t,e,a){var r=a("a691"),n=a("1d80"),s=function(t){return function(e,a){var s,i,o=String(n(e)),u=r(a),c=o.length;return u<0||u>=c?t?"":void 0:(s=o.charCodeAt(u),s<55296||s>56319||u+1===c||(i=o.charCodeAt(u+1))<56320||i>57343?t?o.charAt(u):s:t?o.slice(u,u+2):i-56320+(s-55296<<10)+65536)}};t.exports={codeAt:s(!1),charAt:s(!0)}},"676f":function(t,e,a){"use strict";a.r(e);var r=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a(t.IfuserAgent<2?"PCtemplate":"Mtemplate",{tag:"component",attrs:{LeftName:t.LeftName,IfuserAgent:t.IfuserAgent,formdata:t.formdata,buttonLoad:t.buttonLoad},on:{formSubmit:t.SubmitDatalist}})},n=[],s=(a("d3b7"),a("96cf"),a("1da1")),i={props:["FormHash","IfuserAgent"],data:function(){return{formdata:{loginset:{title:"",subtitle:"",background:"",template:""}},buttonLoad:!1}},created:function(){this.getDatalist()},methods:{getDatalist:function(){var t=this;return Object(s["a"])(regeneratorRuntime.mark((function e(){var a,r,n,s;return regeneratorRuntime.wrap((function(e){while(1)switch(e.prev=e.next){case 0:return t.buttonLoad=!0,e.next=3,t.axios.get(t.AxiosApi+"admin.php?mod=setting&op=interface&operation=loginset");case 3:a=e.sent,r=a.data,r&&r.setting&&(n=r.setting,s={title:n.loginset.title?n.loginset.title:"",subtitle:n.loginset.subtitle?n.loginset.subtitle:"",background:n.loginset.background?n.loginset.background:"",template:n.loginset.template?n.loginset.template:"1"},t.formdata.loginset=s),t.buttonLoad=!1;case 7:case"end":return e.stop()}}),e)})))()},SubmitDatalist:function(){var t=this;return Object(s["a"])(regeneratorRuntime.mark((function e(){var a,r,n,s;return regeneratorRuntime.wrap((function(e){while(1)switch(e.prev=e.next){case 0:if(!(t.IfuserAgent<2)){e.next=7;break}if(t.FormHash){e.next=4;break}return t.$message.error(t.$t("submit_failure_refresh")),e.abrupt("return",!1);case 4:a=t.$loading({lock:!0,text:t.$t("submission"),spinner:"el-icon-loading"}),e.next=11;break;case 7:if(t.FormHash){e.next=10;break}return t.$notify({type:"danger",message:t.$t("submit_failure_refresh")}),e.abrupt("return",!1);case 10:r=t.$toast.loading({message:t.$t("submission"),forbidClick:!0,duration:0});case 11:return e.next=13,t.axios.post(t.AxiosApi+"admin.php?mod=setting&op=interface&operation=loginset",{settingsubmit:!0,formhash:t.FormHash,settingnew:t.formdata});case 13:n=e.sent,s=n.data,t.IfuserAgent<2?(a.close(),s.success?t.$message({message:t.$t("submit_success"),type:"success"}):t.$message.error(t.$t("submit_failure"))):(r.clear(),s.success?t.$notify({type:"success",message:t.$t("submit_success")}):t.$notify({type:"danger",message:t.$t("submit_failure")}));case 16:case"end":return e.stop()}}),e)})))()}},components:{PCtemplate:function(){return a.e("chunk-4bd6d832").then(a.bind(null,"b00b"))},Mtemplate:function(){return a.e("chunk-37d74806").then(a.bind(null,"a9f5"))}}},o=i,u=a("2877"),c=Object(u["a"])(o,r,n,!1,null,"022a6c7a",null);e["default"]=c.exports},"857a":function(t,e,a){var r=a("1d80"),n=/"/g;t.exports=function(t,e,a,s){var i=String(r(t)),o="<"+e;return""!==a&&(o+=" "+a+'="'+String(s).replace(n,"&quot;")+'"'),o+">"+i+"</"+e+">"}},a434:function(t,e,a){"use strict";var r=a("23e7"),n=a("23cb"),s=a("a691"),i=a("50c4"),o=a("7b0b"),u=a("65f0"),c=a("8418"),m=a("1dde"),l=a("ae40"),f=m("splice"),d=l("splice",{ACCESSORS:!0,0:0,1:2}),p=Math.max,g=Math.min,h=9007199254740991,b="Maximum allowed length exceeded";r({target:"Array",proto:!0,forced:!f||!d},{splice:function(t,e){var a,r,m,l,f,d,w=o(this),x=i(w.length),k=n(t,x),v=arguments.length;if(0===v?a=r=0:1===v?(a=0,r=x-k):(a=v-2,r=g(p(s(e),0),x-k)),x+a-r>h)throw TypeError(b);for(m=u(w,r),l=0;l<r;l++)f=k+l,f in w&&c(m,l,w[f]);if(m.length=r,a<r){for(l=k;l<x-r;l++)f=l+r,d=l+a,f in w?w[d]=w[f]:delete w[d];for(l=x;l>x-r+a;l--)delete w[l-1]}else if(a>r)for(l=x-r;l>k;l--)f=l+r-1,d=l+a-1,f in w?w[d]=w[f]:delete w[d];for(l=0;l<a;l++)w[l+k]=arguments[l+2];return w.length=x-r+a,m}})},a551:function(t,e,a){"use strict";a.r(e);var r=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a(t.IfuserAgent<2?"PCtemplate":"Mtemplate",{tag:"component",attrs:{IfuserAgent:t.IfuserAgent,formdata:t.formdata,imageUrl:t.imageUrl,applist:t.applist,serverspace:t.serverspace,buttonLoad:t.buttonLoad},on:{formSubmit:t.SubmitDatalist}})},n=[],s=(a("d3b7"),a("96cf"),a("1da1")),i={props:["FormHash","IfuserAgent"],data:function(){return{formdata:{sitelogo:"",sitename:"",pathinfo:0,sitebeiantxt:"",sitebeian:"",metakeywords:"",metadescription:"",statcode:"",overt:"0",bbclosed:"0",closedreason:""},imageUrl:"",applist:[],serverspace:[],buttonLoad:!0,percentage:0,ispercentage:!1}},created:function(){this.getDatalist()},methods:{getDatalist:function(){var t=this;return Object(s["a"])(regeneratorRuntime.mark((function e(){var a,r,n,s,i;return regeneratorRuntime.wrap((function(e){while(1)switch(e.prev=e.next){case 0:return t.buttonLoad=!0,e.next=3,t.axios.get(t.AxiosApi+"admin.php?mod=setting&op=interface&operation=basic");case 3:if(a=e.sent,r=a.data,r){for(n in t.applist=r.appdata,r.serverspace)r.serverspace[n]["remoteid"]=parseInt(r.serverspace[n]["remoteid"]),t.serverspace.push(r.serverspace[n]);r.settingdata&&(s=r.settingdata,i={sitelogo:s.sitelogo?s.sitelogo:"",sitename:s.sitename?s.sitename:"",pathinfo:parseInt(s.pathinfo)?parseInt(s.pathinfo):0,sitebeiantxt:s.sitebeiantxt?s.sitebeiantxt:"",sitebeian:s.sitebeian?s.sitebeian:"",metakeywords:s.metakeywords?s.metakeywords:"",metadescription:s.metadescription?s.metadescription:"",statcode:s.statcode?s.statcode:"",overt:s.overt?s.overt:"0",bbclosed:s.bbclosed?s.bbclosed:"0",closedreason:s.closedreason?s.closedreason:t.$t("website_upgrading")+"..."},t.imageUrl=s.sitelogoPath?s.sitelogoPath:"static/image/common/logo.png",t.formdata=i,t.buttonLoad=!1)}case 7:case"end":return e.stop()}}),e)})))()},SubmitDatalist:function(){var t=this;return Object(s["a"])(regeneratorRuntime.mark((function e(){var a,r,n,s;return regeneratorRuntime.wrap((function(e){while(1)switch(e.prev=e.next){case 0:if(!(t.IfuserAgent<2)){e.next=7;break}if(t.FormHash){e.next=4;break}return t.$message.error(t.$t("submit_failure_refresh")),e.abrupt("return",!1);case 4:a=t.$loading({lock:!0,text:t.$t("submission"),spinner:"el-icon-loading"}),e.next=11;break;case 7:if(t.FormHash){e.next=10;break}return t.$notify({type:"danger",message:t.$t("submit_failure_refresh")}),e.abrupt("return",!1);case 10:r=t.$toast.loading({message:t.$t("submission"),forbidClick:!0,duration:0});case 11:return e.next=13,t.axios.post(t.AxiosApi+"admin.php?mod=setting&op=interface&operation=basic",{settingsubmit:!0,formhash:t.FormHash,settingnew:t.formdata});case 13:n=e.sent,s=n.data,t.IfuserAgent<2?(a.close(),s.success?t.$message({message:t.$t("submit_success"),type:"success"}):t.$message.error(t.$t("submit_failure"))):(r.clear(),s.success?t.$notify({type:"success",message:t.$t("submit_success")}):t.$notify({type:"danger",message:t.$t("submit_failure")}));case 16:case"end":return e.stop()}}),e)})))()}},components:{PCtemplate:function(){return Promise.all([a.e("chunk-6337fcfc"),a.e("chunk-3925df0a")]).then(a.bind(null,"c107"))},Mtemplate:function(){return Promise.all([a.e("chunk-6337fcfc"),a.e("chunk-6dbaec17")]).then(a.bind(null,"23b6"))}}},o=i,u=a("2877"),c=Object(u["a"])(o,r,n,!1,null,"1fed9637",null);e["default"]=c.exports},a630:function(t,e,a){var r=a("23e7"),n=a("4df4"),s=a("1c7e"),i=!s((function(t){Array.from(t)}));r({target:"Array",stat:!0,forced:i},{from:n})},a706:function(t,e,a){"use strict";var r=a("2b2b"),n=a.n(r);n.a},ad6d:function(t,e,a){"use strict";var r=a("825a");t.exports=function(){var t=r(this),e="";return t.global&&(e+="g"),t.ignoreCase&&(e+="i"),t.multiline&&(e+="m"),t.dotAll&&(e+="s"),t.unicode&&(e+="u"),t.sticky&&(e+="y"),e}},af03:function(t,e,a){var r=a("d039");t.exports=function(t){return r((function(){var e=""[t]('"');return e!==e.toLowerCase()||e.split('"').length>3}))}},b0c0:function(t,e,a){var r=a("83ab"),n=a("9bf2").f,s=Function.prototype,i=s.toString,o=/^\s*function ([^ (]*)/,u="name";r&&!(u in s)&&n(s,u,{configurable:!0,get:function(){try{return i.call(this).match(o)[1]}catch(t){return""}}})},c96a:function(t,e,a){"use strict";var r=a("23e7"),n=a("857a"),s=a("af03");r({target:"String",proto:!0,forced:s("small")},{small:function(){return n(this,"small","","")}})},d28b:function(t,e,a){var r=a("746f");r("iterator")},d857:function(t,e,a){"use strict";a.r(e);var r=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a(t.IfuserAgent<2?"PCtemplate":"Mtemplate",{tag:"component",attrs:{IfuserAgent:t.IfuserAgent,formdata:t.formdata,buttonLoad:t.buttonLoad,fstrongpw:t.fstrongpw},on:{formSubmit:t.SubmitDatalist}})},n=[],s=(a("d3b7"),a("96cf"),a("1da1")),i={props:["FormHash","IfuserAgent"],data:function(){return{formdata:{regstatus:!1,reglinkname:"",pwlength:"",strongpw:[],bbrules:"0",bbrulestxt:this.$t("website_upgrading")+"..."},fstrongpw:[{val:"1",text:this.$t("strongpw_1")},{val:"2",text:this.$t("strongpw_2")},{val:"3",text:this.$t("strongpw_3")},{val:"4",text:this.$t("strongpw_4")}],buttonLoad:!1}},created:function(){this.getDatalist()},methods:{getDatalist:function(){var t=this;return Object(s["a"])(regeneratorRuntime.mark((function e(){var a,r,n,s;return regeneratorRuntime.wrap((function(e){while(1)switch(e.prev=e.next){case 0:return t.buttonLoad=!0,e.next=3,t.axios.get(t.AxiosApi+"admin.php?mod=setting&op=interface&operation=access");case 3:a=e.sent,r=a.data,r&&r.setting&&(n=r.setting,s={regstatus:"1"==n.regstatus,reglinkname:n.reglinkname?n.reglinkname:"",pwlength:n.pwlength?n.pwlength:"",strongpw:n.strongpw?n.strongpw:[],bbrules:"1"==n.bbrules?"1":"0",bbrulestxt:n.bbrulestxt?n.bbrulestxt:""},t.formdata=s),t.buttonLoad=!1;case 7:case"end":return e.stop()}}),e)})))()},SubmitDatalist:function(){var t=this;return Object(s["a"])(regeneratorRuntime.mark((function e(){var a,r,n,s,i;return regeneratorRuntime.wrap((function(e){while(1)switch(e.prev=e.next){case 0:if(!(t.IfuserAgent<2)){e.next=7;break}if(t.FormHash){e.next=4;break}return t.$message.error(t.$t("submit_failure_refresh")),e.abrupt("return",!1);case 4:a=t.$loading({lock:!0,text:t.$t("submission"),spinner:"el-icon-loading"}),e.next=11;break;case 7:if(t.FormHash){e.next=10;break}return t.$notify({type:"danger",message:t.$t("submit_failure_refresh")}),e.abrupt("return",!1);case 10:r=t.$toast.loading({message:t.$t("submission"),forbidClick:!0,duration:0});case 11:return n=JSON.parse(JSON.stringify(t.formdata)),n.regstatus?n.regstatus=1:n.regstatus=0,e.next=15,t.axios.post(t.AxiosApi+"admin.php?mod=setting&op=interface&operation=access",{settingsubmit:!0,formhash:t.FormHash,settingnew:n});case 15:s=e.sent,i=s.data,t.IfuserAgent<2?(a.close(),i.success?t.$message({message:t.$t("submit_success"),type:"success"}):t.$message.error(t.$t("submit_failure"))):(r.clear(),i.success?t.$notify({type:"success",message:t.$t("submit_success")}):t.$notify({type:"danger",message:t.$t("submit_failure")}));case 18:case"end":return e.stop()}}),e)})))()}},components:{PCtemplate:function(){return a.e("chunk-e9e5fa9a").then(a.bind(null,"d27d"))},Mtemplate:function(){return a.e("chunk-4850fbf9").then(a.bind(null,"1c40"))}}},o=i,u=(a("a706"),a("2877")),c=Object(u["a"])(o,r,n,!1,null,"a9682ac0",null);e["default"]=c.exports},ddb0:function(t,e,a){var r=a("da84"),n=a("fdbc"),s=a("e260"),i=a("9112"),o=a("b622"),u=o("iterator"),c=o("toStringTag"),m=s.values;for(var l in n){var f=r[l],d=f&&f.prototype;if(d){if(d[u]!==m)try{i(d,u,m)}catch(g){d[u]=m}if(d[c]||i(d,c,l),n[l])for(var p in s)if(d[p]!==s[p])try{i(d,p,s[p])}catch(g){d[p]=s[p]}}}},e01a:function(t,e,a){"use strict";var r=a("23e7"),n=a("83ab"),s=a("da84"),i=a("5135"),o=a("861d"),u=a("9bf2").f,c=a("e893"),m=s.Symbol;if(n&&"function"==typeof m&&(!("description"in m.prototype)||void 0!==m().description)){var l={},f=function(){var t=arguments.length<1||void 0===arguments[0]?void 0:String(arguments[0]),e=this instanceof f?new m(t):void 0===t?m():m(t);return""===t&&(l[e]=!0),e};c(f,m);var d=f.prototype=m.prototype;d.constructor=f;var p=d.toString,g="Symbol(test)"==String(m("test")),h=/^Symbol\((.*)\)[^)]+$/;u(d,"description",{configurable:!0,get:function(){var t=o(this)?this.valueOf():this,e=p.call(t);if(i(l,t))return"";var a=g?e.slice(7,-1):e.replace(h,"$1");return""===a?void 0:a}}),r({global:!0,forced:!0},{Symbol:f})}},fb6a:function(t,e,a){"use strict";var r=a("23e7"),n=a("861d"),s=a("e8b5"),i=a("23cb"),o=a("50c4"),u=a("fc6a"),c=a("8418"),m=a("b622"),l=a("1dde"),f=a("ae40"),d=l("slice"),p=f("slice",{ACCESSORS:!0,0:0,1:2}),g=m("species"),h=[].slice,b=Math.max;r({target:"Array",proto:!0,forced:!d||!p},{slice:function(t,e){var a,r,m,l=u(this),f=o(l.length),d=i(t,f),p=i(void 0===e?f:e,f);if(s(l)&&(a=l.constructor,"function"!=typeof a||a!==Array&&!s(a.prototype)?n(a)&&(a=a[g],null===a&&(a=void 0)):a=void 0,a===Array||void 0===a))return h.call(l,d,p);for(r=new(void 0===a?Array:a)(b(p-d,0)),m=0;d<p;d++,m++)d in l&&c(r,m,l[d]);return r.length=m,r}})}}]);