const Tmpfile_rec = {
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
                    v-for="(item,key) in model.data"
                    :key="item.key"
                    :name="item.key">
                    <template #label>
                        {{item.name}}<el-icon class="tabs-icon-edit" @click.stop="handleTabsItemEdit(item)"><EditPen /></el-icon>
                    </template>
                    <el-form label-width="150px" label-suffix=":">
                        <el-form-item label="数据类型" style="display:none;">
                            <el-radio-group v-model="item.data[0].ftype" @change="handlechange(item)">
                                <el-radio :label="0" border>库</el-radio>
                                <el-radio :label="1" border>智能数据</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item :label="Lang.text1">
                            <el-select
                                style="width:24%;"
                                v-model="item.data[0].id"
                                @change="handleChangeId">
                                <el-option
                                    v-for="fitem in DataList[key]"
                                    :key="fitem.id"
                                    :label="fitem.name"
                                    :value="fitem.id"></el-option>
                            </el-select>
                            <el-select v-model="item.data[0].type" style="width:24%;margin-left: 2%;" @change="handleChangeType(item)">
                                <el-option :key="1" :label="Lang.text2" :value="1" ></el-option>
                                <el-option :key="2" :label="Lang.text3" :value="2" ></el-option>
                                <el-option :key="3" :label="Lang.text4" :value="3" ></el-option>
                                <el-option :key="4" :label="Lang.text5" :value="4" ></el-option>
                            </el-select>
                        </el-form-item>
                        
                        <el-form-item :label="Lang.text3" v-if="item.data[0].type == 2">
                            <el-input style="width:50%;" v-model="item.data[0].value" :placeholder="Lang.text6" ></el-input>
                        </el-form-item>
                        <el-form-item :label="Lang.text7" v-if="item.data[0].type == 3">
                            <el-select
                                style="width:50%;"
                                v-model="item.data[0].gradetype">
                                <el-option :key="0" :label="Lang.text8" :value="0"></el-option>
                                <el-option :key="1" :label="Lang.text9" :value="1"></el-option>
                                <el-option :key="2" :label="Lang.text10" :value="2"></el-option>
                                <el-option :key="3" :label="Lang.text11" :value="3"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item :label="Lang.text4" v-if="item.data[0].type == 3">
                            <el-select
                                style="width:50%;"
                                v-model="item.data[0].value">
                                <el-option :key="0" :label="Lang.text12" :value="0">
                                    <el-rate model-value="0" disabled></el-rate>
                                </el-option>
                                <el-option :key="1" label="1" :value="1">
                                    <el-rate model-value="1" disabled></el-rate>
                                </el-option>
                                <el-option :key="2" label="2" :value="2">
                                    <el-rate model-value="2" disabled></el-rate>
                                </el-option>
                                <el-option :key="3" label="3" :value="3">
                                    <el-rate model-value="3" disabled></el-rate>
                                </el-option>
                                <el-option :key="4" label="4" :value="4">
                                    <el-rate model-value="4" disabled></el-rate>
                                </el-option>
                                <el-option :key="5" label="5" :value="5">
                                    <el-rate model-value="5" disabled></el-rate>
                                </el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item :label="Lang.text5" v-if="item.data[0].type == 4 && item.data[0].id">
                        <el-popover
                                placement="bottom"
                                :width="200"
                                trigger="click"
                                :teleported="false"
                                :popper-style="{width:'auto',padding: 0}">
                                <el-scrollbar height="260px">
                                    <div style="padding: var(--el-popover-padding);">
                                        <el-tree
                                            :props="{label:'fname',children: 'children',isLeaf:'leaf'}"
                                            :load="classifyloadNode"
                                            lazy
                                            node-key="fid"
                                            check-strictly
                                            :default-expanded-keys="item.data[0].classify.expandedkeys"
                                            :default-checked-keys="item.data[0].classify.checked"
                                            @check="classifyCheck"
                                            show-checkbox/>
                                    </div>
                                </el-scrollbar>
                                <template #reference>
                                    <div class="el-textarea" style="width:50%;">
                                        <div class="el-textarea__inner" style="min-height: 31px;">
                                            <template v-if="item.data[0].classify.text.length">
                                                <div style="margin-bottom: -5px;margin-right: -5px;">
                                                    <el-tag
                                                        v-for="tag in item.data[0].classify.text"
                                                        style="margin-right:5px;margin-bottom:5px;"
                                                        :key="tag.fid"
                                                        closable
                                                        @close="classifyClose(tag.fid)"
                                                        type="info">
                                                        {{ tag.fname }}
                                                    </el-tag>
                                                </div>
                                            </template>
                                            
                                        </div>
                                    </div>
                                </template>
                            </el-popover>
                        </el-form-item>
                        <el-form-item :label="Lang.text13">
                            <el-radio-group v-model="item.data[0].sort">
                                <el-radio style="margin-bottom:6px;" :label="1" border>{{Lang.text19}}</el-radio>
                                <el-radio style="margin-bottom:6px;" :label="2" border>{{Lang.text20}}</el-radio>
                                <el-radio style="margin-bottom:6px;" :label="3" border>{{Lang.text21}}</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item :label="Lang.text14">
                            <el-select v-model="item.data[0].number" style="width:50%;">
                                <el-option
                                    v-for="num in 10"
                                    :key="num"
                                    :label="num + Lang.text15"
                                    :value="num"
                                    ></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item :label="Lang.text21">
                            <div style="width: 50%;">
                            <el-radio-group v-model="item.data[0].isfilename">
                                <el-radio :label="0" border>{{Lang.text22}}</el-radio>
                                <el-radio :label="1" border>{{Lang.text23}}</el-radio>
                            </el-radio-group>
                            </div>
                        </el-form-item>
                        <el-form-item :label="Lang.text16">
                            <div style="width: 50%;">
                            <el-radio-group v-model="item.data[0].isdown">
                                <el-radio :label="0" border>{{Lang.text22}}</el-radio>
                                <el-radio :label="1" border>{{Lang.text23}}</el-radio>
                            </el-radio-group>
                            </div>
                        </el-form-item>
                        <el-form-item :label="Lang.text25">
                            <div style="width: 50%;">
                                <el-input v-model="item.data[0].time" type="Number" ></el-input>
                                <el-text size="small" tag="p" type="info">{{Lang.text17}}</el-text>
                            </div>
                        </el-form-item>
                        <el-form-item :label="Lang.text18">
                            <div style="width: 100%;">
                                <div style="display:flex;width:50%;">
                                    <el-input style="width: 130px;margin-right:6px;" v-model="item.data[0].moretxt" ></el-input>
                                    <el-select v-model="item.data[0].link" style="width: 110px;margin-right:6px;" @change="item.data[0].linkval=''">
                                        <el-option :label="Lang.text26" value="0"></el-option>
                                        <el-option :label="Lang.text27" value="1"></el-option>
                                        <el-option :label="Lang.text28" value="2"></el-option>
                                        <el-option :label="Lang.text29" value="3"></el-option>
                                    </el-select>
                                    <template v-if="parseInt(item.data[0].link) == 0">
                                        <el-input v-model="item.data[0].linkval"></el-input>
                                    </template>
                                    <template v-else-if="parseInt(item.data[0].link) == 1">
                                        <el-select v-model="item.data[0].linkval" style="width: 100%">
                                            <el-option v-for="item in typecollection.library" :label="item.appname" :value="item.appid"></el-option>
                                        </el-select>
                                    </template>
                                    <template v-else-if="parseInt(item.data[0].link) == 2">
                                        <el-select v-model="item.data[0].linkval" style="width: 100%">
                                            <el-option v-for="item in typecollection.alonepage" :label="item.pagename" :value="item.id" :key="item.id"></el-option>
                                        </el-select>
                                    </template>
                                    <template v-else-if="parseInt(item.data[0].link) == 4">
                                        <el-select v-model="item.data[0].linkval" style="width: 100%">
                                            <el-option v-for="item in typecollection.tab" :label="item.name" :value="item.gid" :key="item.gid"></el-option>
                                        </el-select>
                                    </template>
                                    <template v-else-if="parseInt(item.data[0].link) == 3">
                                        <el-cascader 
                                            style="width: 100%"
                                            v-model="item.data[0].linkval" 
                                            :options="typecollection.banner"
                                            :show-all-levels="false"
                                            :emitPath="false"
                                            :props="{value:'id',label:'bannername',checkStrictly:true}" 
                                            clearable></el-cascader>
                                    </template>
                                </div>
                                <el-text size="small" tag="p" type="info">{{Lang.text24}}</el-text>
                            </div>
                        </el-form-item>
                    </el-form>
                </el-tab-pane>
            </el-tabs>
            <el-dialog
                v-model="editDialog.visible"
                :title="Lang1.text1">
                <el-form label-position="top">
                    <el-form-item :label="Lang1.text2">
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
                    <el-button @click="editDialog.visible = false">{{Lang1.text3}}</el-button>
                    <el-button type="primary" @click="EditTitleSubmit">{{Lang1.text4}}</el-button>
                </div>
                </template>
            </el-dialog>
        </div>
    `,
    setup(props,context){
        let Lang = {
            text1:__lang.data_source,
            text2:__lang.all,
            text3:__lang.label,
            text4:__lang.grade,
            text5:__lang.classify,
            text6:__lang.Specify_label_tip,
            text7:__lang.Rating_range,
            text8:__lang.yes,
            text9:__lang.no,
            text10:__lang.Including_below,
            text11:__lang.Including_above,
            text12:__lang.Unmarked_star,
            text13:__lang.data_sorting,
            text14:__lang.data_quantity,
            text15:__lang.row,
            text16:__lang.download_button,
            text17:__lang.cache_time_tip,
            text18:__lang.tip3,
            text19:__lang.newest,
            text20:__lang.hottest,
            text21:__lang.filename,
            text22:__lang.show,
            text23:__lang.no_show,
            text24:__lang.tip4,
            text25:__lang.cache_time,
            text26:__lang.address,
            text27:__lang.library,
            text28:__lang.page,
            text29:__lang.column,
        };
        let Lang1 = {
            text1:__lang.edit_name,
            text2:__lang.name,
            text3:__lang.cancel,
            text4:__lang.confirms,
        };
        let editDialog = reactive({
            visible:false,
            name:'',
            langkey:'',
            data:''
        });
        let DomdragTab = ref(null);
        //记录库数据来源数据
        let KuDataList = [];
        //记录自能数据数据来源数据
        let AutoDataList = [];
        let tabsvalue = ref(null);
        if(props.model.data && props.model.data.length){
            props.model.data.forEach((item,index) => {
                let id = getId();
                if(index == 0){
                    tabsvalue.value = id;
                }
                item.key = id;
                
            });
        }
        
        function getId(){  //获取随机数id
            let date = Date.now();
            let rund = Math.ceil(Math.random()*1000)
            let id = date + '' + rund;
            return id;
        };
        //tabs的title修改
        function handleTabsItemEdit(data){
            editDialog.data = data;
            editDialog.name = data.name;
            editDialog.langkey = data.langkey || '';
            editDialog.visible = true;
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
        };
        function handleTabsEdit(targetName,action){
            if(action == 'add'){
                ElementPlus.ElMessageBox.prompt('', __lang.title, {
                    confirmButtonText: __lang.confirms,
                    inputPlaceholder:__lang.please_input,
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
                        if(DataList.value.length) {
                            let newarr = DataList.value[DataList.value.length - 1];
                            DataList.value.push(newarr);
                        }
                    }
                }).catch(() => {

                })
            }else{
                ElementPlus.ElMessageBox.confirm(
                    __lang.del_library_confirm,
                    __lang.prompt,
                    {
                      confirmButtonText: __lang.confirms,
                      cancelButtonText: __lang.cancel,
                      icon:'QuestionFilled',
                      type: 'warning',
                    }).then(async () => {
                        let index = props.model.data.findIndex(function(current){
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

                        DataList.value.splice(index,1);
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
        function tabchange(targetName){//tabs改变时触发
            tabsvalue.value = targetName;
        };
        //数据来源数据
        let DataList = ref([]);
        foreachData();
        function foreachData(){
            if(props.model.data && props.model.data.length){
                props.model.data.forEach(item => {
                    DataList.value.push([]);
                    GetData(item);
                });
            }
            
        }

        
        //数据来源请求方法
        async function GetData(item){
            let index = props.model.data.findIndex(function(current){
                return current.key == item.key;
            });
            if(!DataList.value[index])return false;
            DataList.value[index] = [];
            if(item.data[0].ftype == 1 && AutoDataList.length){
                DataList.value[index] = JSON.parse(JSON.stringify(AutoDataList))
                return false;
            }
            if(item.data[0].ftype == 0 && KuDataList.length){
                DataList.value[index] = JSON.parse(JSON.stringify(KuDataList))
                return false;
            }
            const {data: res} = await axios.get(BasicUrl+'getapporsources&stype='+item.data[0].ftype);
            if(res.success){
                DataList.value[index] = res.data;
                if(item.data[0].ftype == 1){
                    AutoDataList = res.data;
                }else{
                    KuDataList = res.data;
                }
            }else{
                ElementPlus.ElMessage.error(res.msg || __lang.get_data_fail);
            }
        };
        //数据类型改变
        function handlechange(item){
            item.id = '';
            GetData(item);
        }
        //数据排序改变
        function handleChangeType(item){
            let index = props.model.data.findIndex(function(current){
                return current.key == item.key;
            });
            props.model.data[index].data[0].value = '';
            props.model.data[index].data[0].classify.text = [];
            props.model.data[index].data[0].classify.checked = [];
            props.model.data[index].data[0].classify.expandedkeys = [];
            props.model.data[index].data[0].gradetype = 0;
        }
        //分类加载
        async function classifyloadNode(node,resolve){
            var level = node.level;
            let appid = 0;
            if(props.model.data && props.model.data.length){
                for (let index = 0; index < props.model.data.length; index++) {
                    const item = props.model.data[index];
                    if(item.key == tabsvalue.value){
                        appid = item.data[0].id;
                    }
                }
                
            }
            var param = {
                appid:appid
            };
            if(level>0){
                param['pfids'] = node.data.fid;
            }
            var res = await axios.post('index.php?mod=pichome&op=library&do=ajax&operation=getsearchfolder',param);
            var new_data = res.data.folderdatanum;
            resolve(new_data);
        };
        //分类
        function classifyCheck(data,checks){
            let curr = null;
            if(props.model.data && props.model.data.length){
                for (let index = 0; index < props.model.data.length; index++) {
                    const item = props.model.data[index];
                    if(item.key == tabsvalue.value){
                        curr = item.data[0];
                    }
                }
                
            }
            if(curr){
                let pathkeys = [];
                if(checks.checkedNodes.length){
                    for (let index = 0; index < checks.checkedNodes.length; index++) {
                        const element = checks.checkedNodes[index];
                        let pathkey = element.pathkey.split(curr.id).filter(item => item != '');
                        pathkey.pop();
                        if(pathkey.length){
                            for (let findex = 0; findex < pathkey.length; findex++) {
                                const key = pathkey[findex];
                                if(pathkeys.indexOf(key+curr.id) < 0){
                                    pathkeys.push(key+curr.id)
                                }
                            }
                        }
                        
                    }
                }
                curr.classify.checked = JSON.parse(JSON.stringify(checks.checkedKeys));
                curr.classify.expandedkeys = pathkeys;
                curr.classify.text = JSON.parse(JSON.stringify(checks.checkedNodes));
                curr.value = JSON.parse(JSON.stringify(checks.checkedKeys)).join(',');
            }
        };
        function handleChangeId(id){
            let curr = null;
            if(props.model.data && props.model.data.length){
                for (let index = 0; index < props.model.data.length; index++) {
                    const item = props.model.data[index];
                    if(item.key == tabsvalue.value){
                        curr = item.data[0];
                    }
                }
                
            }
            if(!curr) return false;
            curr.id = '';
            nextTick(() => {
                curr.classify.checked = [];
                curr.classify.expandedkeys = [];
                curr.classify.text = [];
                curr.value = '';
                curr.id = id;
            });
        }
        function classifyClose(id){
            let curr = null;
            if(props.model.data && props.model.data.length){
                for (let index = 0; index < props.model.data.length; index++) {
                    const item = props.model.data[index];
                    if(item.key == tabsvalue.value){
                        curr = item.data[0];
                    }
                }
                
            }
            if(!curr) return false;
            let value = curr.value.split(',');
            let Vindex = value.indexOf(id);
            if(Vindex > -1){
                value.splice(Vindex,1);
                curr.value = value.join(',');
            }

            let Cindex = curr.classify.checked.indexOf(id);
            if(Cindex > -1){
                curr.classify.checked.splice(Cindex,1);
            }
            let Tindex = curr.classify.text.findIndex(current => {
                return current.fid == id;
            });
            if(Tindex > -1){
                curr.classify.text.splice(Tindex,1);
            }
        };
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
                    const list = DataList.value.splice(oldIndex, 1)[0];
                    DataList.value.splice(newIndex, 0, list); 
                },
            });
        }
        function EditTitleSubmit(){
            if(!editDialog.name)return false;
            editDialog.data.name = editDialog.name;
            editDialog.visible = false;
        };
        function changeTitle(val){
            editDialog.data.name = val;
            editDialog.name = val;
        };
        return {
            Lang1,
            Lang,
            editDialog,
            tabsvalue,
            tabchange,
            handlechange,
            handleTabsEdit,
            DataList,
            handleChangeType,
            DomdragTab,
            handleTabsItemEdit,
            classifyloadNode,
            classifyCheck,
            handleChangeId,
            classifyClose,
            EditTitleSubmit,
            changeTitle
            
        }
    }
}