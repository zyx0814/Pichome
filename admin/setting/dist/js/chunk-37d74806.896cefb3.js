(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-37d74806"],{"7e89":function(t,a,e){"use strict";var i=e("b260"),s=e.n(i);s.a},a9f5:function(t,a,e){"use strict";e.r(a);var i=function(){var t=this,a=t.$createElement,e=t._self._c||a;return e("div",{staticClass:"basic-container"},[e("van-form",{attrs:{"label-width":"7.5em"}},[e("van-field",{attrs:{label:t.$t("main_title_page")+":"},model:{value:t.formdata.loginset.title,callback:function(a){t.$set(t.formdata.loginset,"title",a)},expression:"formdata.loginset.title"}}),e("p",{staticClass:"GrayColor",staticStyle:{"font-size":"0.5em",padding:"0 15px"}},[t._v(t._s(t.$t("main_title_page_state")))]),e("van-field",{attrs:{label:t.$t("page_subtitle")+":"},model:{value:t.formdata.loginset.subtitle,callback:function(a){t.$set(t.formdata.loginset,"subtitle",a)},expression:"formdata.loginset.subtitle"}}),e("p",{staticClass:"GrayColor",staticStyle:{"font-size":"0.5em",padding:"0 15px"}},[t._v(t._s(t.$t("page_subtitle_state")))]),e("van-field",{attrs:{label:t.$t("page_background")+":"},model:{value:t.formdata.loginset.background,callback:function(a){t.$set(t.formdata.loginset,"background",a)},expression:"formdata.loginset.background"}}),e("p",{staticClass:"GrayColor",staticStyle:{"font-size":"0.5em",padding:"0 15px"}},[t._v(t._s(t.$t("for_color_set")))]),e("div",{staticStyle:{margin:"16px"}},[e("van-button",{attrs:{round:"",block:"",type:"info","native-type":"submit"},on:{click:t.SubmitDatalist}},[t._v(t._s(t.$t("save_changes")))])],1)],1)],1)},s=[],n={props:["formdata","buttonLoad"],data:function(){return{}},watch:{buttonLoad:{handler:function(t){var a=this.$toast.loading({message:this.$t("loading")+"...",forbidClick:!0,duration:0});t||a.clear()},deep:!0,immediate:!0}},created:function(){},methods:{SubmitDatalist:function(){this.$emit("formSubmit")}}},o=n,l=(e("7e89"),e("2877")),r=Object(l["a"])(o,i,s,!1,null,"711f0036",null);a["default"]=r.exports},b260:function(t,a,e){}}]);