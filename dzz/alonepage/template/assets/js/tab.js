const Tmptab_rec = {
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
                    :key="item.data[0].key"
                    :name="item.data[0].key">
                    <template #label>
                        {{item.data[0].name}}<el-icon class="tabs-icon-edit" @click.stop="handleTabsItemEdit(item.data[0])"><EditPen /></el-icon>
                    </template>
                    <el-form label-width="120px" label-suffix=":">
                        <el-form-item label="数据来源">
                            <el-select
                                style="width:24%;"
                                v-model="item.data[0].id"
                                @change="handleChangeId">
                                <el-option
                                    v-for="fitem in typecollection.tab"
                                    :key="fitem.gid"
                                    :label="fitem.name"
                                    :value="fitem.gid"></el-option>
                            </el-select>
                            <template v-if="item.data[0].id">
                                <el-popover
                                    placement="bottom"
                                    :width="200"
                                    trigger="click"
                                    :teleported="false"
                                    :popper-style="{width:'auto',padding: 0}">
                                    <el-scrollbar height="260px">
                                        <div style="padding: var(--el-popover-padding);">
                                            <el-tree
                                                :props="{label:'catname',children: 'children',isLeaf:'leaf'}"
                                                :load="classifyloadNode"
                                                lazy
                                                node-key="cid"
                                                check-strictly
                                                :default-expanded-keys="item.data[0].classify.expandedkeys"
                                                :default-checked-keys="item.data[0].classify.checked"
                                                @check="classifyCheck"
                                                show-checkbox/>
                                        </div>
                                    </el-scrollbar>
                                    <template #reference>
                                        <div class="el-textarea"  style="width:24%;margin-left: 2%;">
                                            <div class="el-textarea__inner" style="min-height: 31px;">
                                                <template v-if="item.data[0].classify.text.length">
                                                    <div style="margin-bottom: -5px;margin-right: -5px;">
                                                        <el-tag
                                                            v-for="tag in item.data[0].classify.text"
                                                            style="margin-right:5px;margin-bottom:5px;"
                                                            :key="tag.cid"
                                                            closable
                                                            disable-transitions
                                                            @close="classifyClose(tag.cid)"
                                                            type="info">
                                                            {{ tag.catname }}
                                                        </el-tag>
                                                    </div>
                                                </template>
                                                <template v-else>
                                                    <el-tag
                                                    disable-transitions
                                                        type="info">全部</el-tag>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </el-popover>
                            </template>
                            
                        </el-form-item>
                        <el-form-item label="数据排序">
                            <el-radio-group v-model="item.data[0].sort">
                                <el-radio style="margin-bottom:6px;" :label="1" border>最新</el-radio>
                                <el-radio style="margin-bottom:6px;" :label="2" border>最热</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item label="数据数量">
                            <el-select v-model="item.data[0].number" style="width:50%;">
                                <el-option
                                    v-for="num in 10"
                                    :key="num"
                                    :label="num +'排'"
                                    :value="num"
                                    ></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="缓存时间">
                            <div style="width: 50%;">
                                <el-input v-model="item.data[0].time" type="Number" ></el-input>
                                <el-text size="small" tag="p" type="info">数据缓存时间间隔，单位秒；0：不使用缓存</el-text>
                            </div>
                        </el-form-item>
                        <el-form-item label="更多链接">
                            <div style="width: 100%;">
                                <div style="display:flex;width:50%;">
                                    <el-input style="width: 130px;margin-right:6px;" v-model="item.data[0].moretxt" ></el-input>
                                    <el-select v-model="item.data[0].link" style="width: 110px;margin-right:6px;" @change="item.data[0].linkval=''">
                                        <el-option label="地址" value="0"></el-option>
                                        <el-option label="库" value="1"></el-option>
                                        <el-option label="单页" value="2"></el-option>
                                        <el-option label="栏目" value="3"></el-option>
                                        <el-option label="专辑" value="4"></el-option>
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
                                <el-text size="small" tag="p" type="info">会出现更多内容按钮，点击可跳转到链接地址</el-text>
                            </div>
                        </el-form-item>
                    </el-form>
                </el-tab-pane>
            </el-tabs>
        </div>
    `,
    setup(props,context){
        let DomdragTab = ref(null);
        //记录库数据来源数据
        let KuDataList = [];
        //记录自能数据数据来源数据
        let AutoDataList = [];
        let tabsvalue = ref(null);
        if(props.model.data && props.model.data.length){
            props.model.data.forEach((item,index) => {
                item.data[0].key = getId();
            });
            tabsvalue.value =  props.model.data[props.model.data.length - 1].data[0].key;
        }
        function getId(){  //获取随机数id
            let date = Date.now();
            let rund = Math.ceil(Math.random()*1000)
            let id = date + '' + rund;
            return id;
        };
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
                        let val = JSON.parse(JSON.stringify(props.field[0]));
                        val.data[0].name = value;
                        val.data[0].key = getId();
                        props.model.data.push(val);
                        DataList.value.push([]);
                        GetData(val);
                        nextTick(() => {
                            if(props.model.data && props.model.data.length){
                                tabsvalue.value = props.model.data[props.model.data.length - 1].data[0].key;
                            }
                        });
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
                        let index = props.model.data.findIndex(function(current){
                            return current.data[0].key == targetName;
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

                        DataList.value.splice(index,1);
                        props.model.data.splice(index,1);
                        if(tabsvalue.value == targetName){
                            nextTick(() => {
                                if(props.model.data && props.model.data.length){
                                    tabsvalue.value = props.model.data[props.model.data.length - 1].data[0].key;
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
                return current.data[0].key == item.data[0].key;
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
                ElementPlus.ElMessage.error(res.msg || '数据来源获取失败');
            }
        };

        //分类加载
        async function classifyloadNode(node,resolve){
            var level = node.level;
            let gid = 0;
            if(props.model.data && props.model.data.length){
                for (let index = 0; index < props.model.data.length; index++) {
                    const item = props.model.data[index];
                    if(item.data[0].key == tabsvalue.value){
                        gid = item.data[0].id;
                    }
                }
                
            }
            var param = {
                gid:gid
            };
            if(level>0){
                param['pcid'] = node.data.cid;
            }
            let {data: res} = await axios.post('index.php?mod=tab&op=tabviewinterface&do=getcat&iswindow=1',param);
            resolve(res.data);
        };
        //分类
        function classifyCheck(data,checks){
            let curr = null;
            if(props.model.data && props.model.data.length){
                for (let index = 0; index < props.model.data.length; index++) {
                    const item = props.model.data[index];
                    if(item.data[0].key == tabsvalue.value){
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
                    if(item.data[0].key == tabsvalue.value){
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
                    if(item.data[0].key == tabsvalue.value){
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
                return current.cid == id;
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
        };


        return {
            tabsvalue,
            tabchange,
            handleTabsEdit,
            DataList,
            DomdragTab,
            handleTabsItemEdit,
            classifyloadNode,
            classifyCheck,
            handleChangeId,
            classifyClose,
        }
    }
}

