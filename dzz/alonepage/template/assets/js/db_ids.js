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
        <el-form label-width="120px" label-suffix=":">
            <el-form-item label="数据类型" style="display:none;">
                <el-radio-group v-model="model[0].ftype" @change="handlechange">
                    <el-radio :label="0" border>库</el-radio>
                    <el-radio :label="1" border>智能数据</el-radio>
                </el-radio-group>
            </el-form-item>
            <el-form-item label="数据来源">
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
            <el-form-item label="数据排序">
                <el-radio-group v-model="model[0].sort">
                    <el-radio style="margin-bottom:6px;" :label="1" border>最新</el-radio>
                    <el-radio style="margin-bottom:6px;" :label="2" border>最热</el-radio>
                    <el-radio style="margin-bottom:6px;" :label="3" border>文件名</el-radio>
                </el-radio-group>
            </el-form-item>
            
            <el-form-item label="数据数量">
                <div style="width: 50%;">
                    <el-input v-model="model[0].number" :min="0" type="Number"></el-input>
                    <el-text size="small" tag="p" type="info">数量0或者不填为不限数量</el-text>
                </div>
            </el-form-item>
            <el-form-item label="缓存时间">
                <div style="width: 50%;">
                    <el-input v-model="model[0].time" type="Number" ></el-input>
                    <el-text size="small" tag="p" type="info">数据缓存时间间隔，单位秒；0：不使用缓存</el-text>
                </div>
            </el-form-item>
            <el-form-item label="文件名">
                <div style="width: 50%;">
                <el-radio-group v-model="model[0].isfilename">
                    <el-radio :label="0" border>显示</el-radio>
                    <el-radio :label="1" border>不显示</el-radio>
                </el-radio-group>
                </div>
            </el-form-item>
            <el-form-item label="更多链接">
                <div style="width: 100%;">
                    <div style="display:flex;width:50%">
                        <el-input style="width: 130px;margin-right:6px;" v-model="model[0].moretxt" ></el-input>
                        <el-select v-model="model[0].link" style="width: 110px;margin-right:6px;" @change="model[0].linkval=''">
                            <el-option label="地址" value="0"></el-option>
                            <el-option label="库" value="1"></el-option>
                            <el-option label="单页" value="2"></el-option>
                            <el-option label="栏目" value="3"></el-option>
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



                    <el-text size="small" tag="p" type="info">会出现更多内容按钮，点击可跳转到链接地址</el-text>
                </div>
            </el-form-item>
        </el-form>
    `,
    setup(props,context){
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
                ElementPlus.ElMessage.error(res.msg || '数据来源获取失败');
            }
        };
        //数据类型切换
        function handlechange(){//数据类型改变清空数据源
            props.model[0].id = '';
            GetData();
        }
        return {
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
        </div>
        
    `,
    setup(props,context){
        let tabsvalue = ref(null);
        let DomdragTab = ref(null);
        props.model.data.forEach((item,index) => {
            let id = getId();
            if(index == 0){
                tabsvalue.value = id;
            }
            item.key = id;
            
        });
        //tabs的title修改
        function handleTabsItemEdit(data){
            ElementPlus.ElMessageBox.prompt('', '修改', {
                confirmButtonText: '确定',
                inputValue:data.name,
                cancelButtonText: '关闭',
                inputValidator: (value) => {       // 点击按钮时，对文本框里面的值进行验证
                    if(!value) {
                        return '输入不能为空';
                    }
                },
                inputErrorMessage: '输入不能为空',
            }).then(({ value }) => {
                data.name = value;
            }).catch(() => {

            })
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
                ElementPlus.ElMessageBox.prompt('', '标题', {
                    confirmButtonText: '确定',
                    inputPlaceholder:'请输入标题',
                    cancelButtonText: '关闭',
                    inputValidator: (value) => {       // 点击按钮时，对文本框里面的值进行验证
                        if(!value) {
                            return '输入不能为空';
                        }
                    },
                    inputErrorMessage: '输入不能为空',
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
                    '此操作无法恢复，确定删除？',
                    '提示',
                    {
                      confirmButtonText: '确定',
                      cancelButtonText: '取消',
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
                                ElementPlus.ElMessage.error(res.msg || '删除失败');
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
            tabsvalue,
            tabchange,
            handleTabsEdit,
            DomdragTab,
            handleTabsItemEdit
        }
    },
    components:{
        Tmpdb_text
    }
}
