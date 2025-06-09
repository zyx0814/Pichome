const Tmpdb_text = {
    props:{
        model:{
            required:true,
            type: Object,
            default:{},
        },
        ParenIndex:{
            required:true,
            type: Number,
            default:0,
        },
        typecollection:{
            required:true,
            type: Object,
            default:{},
        }
    },
    template:`
        <el-form label-width="150px" label-suffix=":">
            <el-form-item :label="Lang.text1" style="display:none;">
                <el-radio-group v-model="model[0].ftype" @change="handlechange">
                    <el-radio :label="0" border>库</el-radio>
                    <el-radio :label="1" border>智能数据</el-radio>
                </el-radio-group>
            </el-form-item>
            <el-form-item :label="Lang.text4">
                <el-select
                    style="width:50%;"
                    v-model="model[0].id">
                    <el-option
                        v-for="item in DataList"
                        :key="item.id"
                        :label="item.name"
                        :value="item.id"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item :label="Lang.text5">
                <el-radio-group v-model="model[0].sort">
                    <el-radio style="margin-bottom:6px;" :label="1" border>{{Lang.text6}}</el-radio>
                    <el-radio style="margin-bottom:6px;" :label="2" border>{{Lang.text7}}</el-radio>
                    <el-radio style="margin-bottom:6px;" :label="3" border>{{Lang.text8}}</el-radio>
                </el-radio-group>
            </el-form-item>
            
            <el-form-item :label="Lang.text1">
                <div style="width: 50%;">
                    <el-input v-model="model[0].number" :min="0" type="Number"></el-input>
                    <el-text size="small" tag="p" type="info">{{Lang.text9}}</el-text>
                </div>
            </el-form-item>
            <el-form-item :label="Lang.text10">
                <div style="width: 50%;">
                    <el-input v-model="model[0].time" type="Number" ></el-input>
                    <el-text size="small" tag="p" type="info">{{Lang.text11}}</el-text>
                </div>
            </el-form-item>
            <el-form-item :label="Lang.text12">
                <div style="width: 50%;">
                <el-radio-group v-model="model[0].isfilename">
                    <el-radio :label="0" border>{{Lang.text13}}</el-radio>
                    <el-radio :label="1" border>{{Lang.text14}}</el-radio>
                </el-radio-group>
                </div>
            </el-form-item>
            <el-form-item :label="Lang.text15">
                <div style="width: 100%;">
                    <div style="display:flex;width:50%">
                        <el-input style="width: 130px;margin-right:6px;" v-model="model[0].moretxt" ></el-input>
                        <el-select v-model="model[0].link" style="width: 110px;margin-right:6px;" @change="model[0].linkval=''">
                            <el-option :label="Lang.text16" value="0"></el-option>
                            <el-option :label="Lang.text17" value="1"></el-option>
                            <el-option :label="Lang.text18" value="2"></el-option>
                            <el-option :label="Lang.text19" value="3"></el-option>
                        </el-select>
                        <template v-if="parseInt(model[0].link) == 0">
                            <el-input v-model="model[0].linkval"></el-input>
                        </template>
                        <template v-else-if="parseInt(model[0].link) == 1">
                            <el-select v-model="model[0].linkval" style="width: 100%">
                                <el-option v-for="item in typecollection.library" :label="item.appname" :value="item.appid"></el-option>
                            </el-select>
                        </template>
                        <template v-else-if="parseInt(model[0].link) == 2">
                            <el-select v-model="model[0].linkval" style="width: 100%">
                                <el-option v-for="item in typecollection.alonepage" :label="item.pagename" :value="item.id" :key="item.id"></el-option>
                            </el-select>
                        </template>
                        <template v-else-if="parseInt(model[0].link) == 4">
                            <el-select v-model="model[0].linkval" style="width: 100%">
                                <el-option v-for="item in typecollection.tab" :label="item.name" :value="item.gid" :key="item.gid"></el-option>
                            </el-select>
                        </template>
                        <template v-else-if="parseInt(model[0].link) == 3">
                            <el-cascader 
                                style="width: 100%"
                                v-model="model[0].linkval" 
                                :options="typecollection.banner"
                                :show-all-levels="false"
                                :emitPath="false"
                                :props="{value:'id',label:'bannername',checkStrictly:true}" 
                                clearable></el-cascader>
                        </template>
                    </div>
                    <el-text size="small" tag="p" type="info">{{Lang.text21}}</el-text>
                </div>
            </el-form-item>
        </el-form>
    `,
    setup(props,context){
        let Lang = {
            text4:__lang.data_source,
            text5:__lang.data_sorting,
            text6:__lang.newest,
            text7:__lang.hottest,
            text8:__lang.filename,
            text1:__lang.data_quantity,
            text9:__lang.tip1,
            text10:__lang.cache_time,
            text11:__lang.tip2,
            text12:__lang.filename,
            text13:__lang.show,
            text14:__lang.no_show,
            text15:__lang.tip3,
            text16:__lang.address,
            text17:__lang.library,
            text18:__lang.page,
            text19:__lang.column,
            text21:__lang.tip4,
        };
        //记录库数据来源数据
        let KuDataList = [];
        //记录自能数据数据来源数据
        let AutoDataList = [];
        //数据来源数据
        let DataList = ref([]);
        GetData();
        //数据来源请求方法
        async function GetData(){
            DataList.value = [];
            if(props.model[0].ftype == 1 && AutoDataList.length){
                DataList.value = JSON.parse(JSON.stringify(AutoDataList))
                return false;
            }
            if(props.model[0].ftype == 0 && KuDataList.length){
                DataList.value = JSON.parse(JSON.stringify(KuDataList))
                return false;
            }
            const {data: res} = await axios.get(BasicUrl+'getapporsources&stype='+props.model[0].ftype);
            if(res.success){
                DataList.value = res.data;
                if(props.model[0].ftype == 1){
                    AutoDataList = res.data;
                }else{
                    KuDataList = res.data;
                }
            }else{
                ElementPlus.ElMessage.error(res.msg || __lang.get_data_fail);
            }
        };
        //数据类型切换
        function handlechange(){//数据类型改变清空数据源
            props.model[0].id = '';
            GetData();
        }
        return {
            Lang,
            handlechange,
            DataList
        }
    }
}

const Tmpdb_ids = {
    props:{
        model:{
            required:true,
            type: Object,
            default:{},
        },
        field:{
            required:true,
            type: Object,
            default:{},
        },
        ParenIndex:{
            required:true,
            type: Number,
            default:0,
        },
        typecollection:{
            required:true,
            type: Object,
            default:{},
        }
    },
    template:`
        <div ref="DomdragTab">
            <el-tabs
                :model-value="tabsvalue"
                type="card"
                editable
                @edit="handleTabsEdit"
                @tab-change="tabchange">
                <el-tab-pane
                    v-for="item in model.data"
                    :key="item.key"
                    :name="item.key">
                    <template #label>
                        {{item.name}}<el-icon class="tabs-icon-edit" @click.stop="handleTabsItemEdit(item)"><EditPen /></el-icon>
                    </template>
                    <Tmpdb_text :model="item.data" :typecollection="typecollection"></Tmpdb_text>
                </el-tab-pane>
            </el-tabs>
            <el-dialog
                v-model="editDialog.visible"
                :title="Lang.text1">
                <el-form label-position="top">
                    <el-form-item :label="Lang.text2">
                        <div class="language-box">
                            <el-input v-model="editDialog.name" ></el-input>
                            <language 
                                v-if="editDialog.data.langkey&&editDialog.data.langkey.tdataname" 
                                :langkey="editDialog.data.langkey.tdataname"
                                @change="changeTitle"></language>
                        </div>
                    </el-form-item>
                </el-form>
                <template #footer>
                <div class="dialog-footer">
                    <el-button @click="editDialog.visible = false">{{Lang.text3}}</el-button>
                    <el-button type="primary" @click="EditTitleSubmit">{{Lang.text4}}</el-button>
                </div>
                </template>
            </el-dialog>
        </div>
        
    `,
    setup(props,context){
        let Lang = {
            text1:__lang.edit_name,
            text2:__lang.name,
            text3:__lang.cancel,
            text4:__lang.confirms,
        };
        let tabsvalue = ref(null);
        let DomdragTab = ref(null);
        let editDialog = reactive({
            visible:false,
            name:'',
            langkey:'',
            data:''
        });
        props.model.data.forEach((item,index) => {
            let id = getId();
            if(index == 0){
                tabsvalue.value = id;
            }
            item.key = id;
            
        });
        //tabs的title修改
        function handleTabsItemEdit(data){
            // ElementPlus.ElMessageBox.prompt('', __lang.modification, {
            //     confirmButtonText: __lang.confirms,
            //     inputValue:data.name,
            //     cancelButtonText: __lang.close,
            //     inputValidator: (value) => {       // 点击按钮时，对文本框里面的值进行验证
            //         if(!value) {
            //             return __lang.conetnt_not_null;
            //         }
            //     },
            //     inputErrorMessage: __lang.conetnt_not_null,
            // }).then(({ value }) => {
            //     data.name = value;
            // }).catch(() => {

            // })
            editDialog.data = data;
            editDialog.name = data.name;
            editDialog.langkey = data.langkey || '';
            editDialog.visible = true;
        };
        function EditTitleSubmit(){
            if(!editDialog.name)return false;
            editDialog.data.name = editDialog.name;
            editDialog.visible = false;
        };
        function changeTitle(val){
            editDialog.data.name = val;
            editDialog.name = val;
        };
        function getId(){  //获取随机数id
            let date = Date.now();
            let rund = Math.ceil(Math.random()*1000)
            let id = date + '' + rund;
            return id;
        };
        function tabchange(index){//tabs改变时触发
            tabsvalue.value = index;
        };
        function handleTabsEdit(targetName,action){
            if(action == 'add'){
                ElementPlus.ElMessageBox.prompt('', __lang.title, {
                    confirmButtonText: __lang.confirms,
                    inputPlaceholder: __lang.please_input,
                    cancelButtonText: __lang.close,
                    inputValidator: (value) => {       // 点击按钮时，对文本框里面的值进行验证
                        if(!value) {
                            return __lang.conetnt_not_null;
                        }
                    },
                    inputErrorMessage: __lang.conetnt_not_null,
                }).then(({ value }) => {
                    if(props.field && props.field.length){
                        let id = getId();
                        let val = JSON.parse(JSON.stringify(props.field[0]));
                        val.name = value;
                        val.key = id;
                        props.model.data.push(val);
                        tabsvalue.value = id;
                    }
                }).catch(() => {

                })
            }else{
                ElementPlus.ElMessageBox.confirm(
                    __lang.delete_tip,
                    __lang.prompt,
                    {
                      confirmButtonText: __lang.confirms,
                      cancelButtonText: __lang.cancel,
                      icon:'QuestionFilled',
                      type: 'warning',
                    }).then(async () => {
                        let index =  props.model.data.findIndex(current => {
                            return current.key == targetName;
                        });
                        let data = props.model.data[index];
                        if(data.tdid){
                            const {data: res} = await axios.post(BasicUrl+'deltagdata',{
                                tdid:data.tdid,
                            });
                            if(!res.success){
                                ElementPlus.ElMessage.error(res.msg || __lang.delete_unsuccess);
                                return false;
                            }
                        }
                        props.model.data.splice(index,1);
                        if(tabsvalue.value == targetName){
                            nextTick(() => {
                                if(props.model.data && props.model.data.length){
                                    tabsvalue.value = props.model.data[props.model.data.length - 1].key;
                                }
                            });
                        }
                        
                    }).catch(() => {

                    })
            }
        }
        onMounted(()=>{
            dragTab();
          });
           
        const dragTab = () =>{
            const tab = DomdragTab.value.querySelector(".el-tabs__nav"); //获取需要拖拽的tab
            Sortable.create(tab, {
            //oldIIndex拖放前的位置， newIndex拖放后的位置 , editableTabs为遍历的tab签
                onEnd({ newIndex, oldIndex }) {
                    const currTab = props.model.data.splice(oldIndex, 1)[0]; //鼠标拖拽当前的el-tabs-pane
                    props.model.data.splice(newIndex, 0, currTab); 
                },
            });
        }
        return {
            Lang,
            editDialog,
            tabsvalue,
            tabchange,
            handleTabsEdit,
            DomdragTab,
            handleTabsItemEdit,
            EditTitleSubmit,
            changeTitle
        }
    },
    components:{
        Tmpdb_text
    }
}
