
const Tmpmanual_recTable = {
    props:{
        model:{
            required:true,
            type: Object,
            default:{},
        },
        typecollection:{
            required:true,
            type: Object,
            default:{},
        }
    },
    template:`
        <el-table 
            ref="DomTable" 
            :data="model" 
            style="width: 100%" 
            row-key="key">
            <el-table-column label="排序" width="60" align="center">
                <template #default="scope">
                    <el-icon class="table-move-icon"><Rank /></el-icon>
                </template>
            </el-table-column>
            <el-table-column label="序号" type="index" width="60" align="center" />
            <el-table-column label="名称" prop="title">
                <template #default="scope">
                    <el-input v-model="scope.row.title" />
                </template>
            </el-table-column>
            <el-table-column label="图片地址" prop="img">
                <template #default="scope">
                    <el-input v-model="scope.row.img">
                        <template #suffix>
                            <template v-if="scope.row.url">
                                <el-icon @click.stop="handleImgDelte(scope.$index)" class="upload-img">
                                    <el-image style="" :src="scope.row.url" fit="cover">
                                        <template #error><div class="el-image__placeholder"></div></template>
                                    </el-image>
                                    <el-icon class="upload-img-icon">
                                        <Close></Close>
                                    </el-icon>
                                </el-icon>
                            </template>
                            <template v-else>
                                <el-icon @click.stop=""  style="cursor:pointer;">
                                    <svg viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" data-v-ea893728=""><path fill="currentColor" d="M160 160v704h704V160H160zm-32-64h768a32 32 0 0 1 32 32v768a32 32 0 0 1-32 32H128a32 32 0 0 1-32-32V128a32 32 0 0 1 32-32z"></path><path fill="currentColor" d="M384 288q64 0 64 64t-64 64q-64 0-64-64t64-64zM185.408 876.992l-50.816-38.912L350.72 556.032a96 96 0 0 1 134.592-17.856l1.856 1.472 122.88 99.136a32 32 0 0 0 44.992-4.864l216-269.888 49.92 39.936-215.808 269.824-.256.32a96 96 0 0 1-135.04 14.464l-122.88-99.072-.64-.512a32 32 0 0 0-44.8 5.952L185.408 876.992z"></path></svg>
                                    <el-upload
                                        style="position: absolute;width: 100%;height: 100%;opacity: 0;left: 0;top: 0;z-index:1;"
                                        :show-file-list="false" 
                                        name="files"
                                        accept="image/gif,image/png,image/jpg,image/jpeg,image/svg"
                                        action="index.php?mod=alonepage&op=alonepageinterface&do=upload" 
                                        :on-success="handleUploadSucess">
                                        <div style="width: 14px;height: 14px;" @click="curRowIndex=scope.$index"></div>
                                    </el-upload>
                                </el-icon>
                            </template>
                        </template>
                    </el-input>
                </template>
            </el-table-column>
            <el-table-column label="链接地址" prop="link">
                <template #default="scope">
                    <div style="display:flex;">
                        <el-select v-model="scope.row.link" style="width: 110px;margin-right:6px;" @change="scope.row.linkval=''">
                            <el-option label="地址" value="0"></el-option>
                            <el-option label="库" value="1"></el-option>
                            <el-option label="单页" value="2"></el-option>
                            <el-option label="栏目" value="3"></el-option>
                        </el-select>
                        <template v-if="parseInt(scope.row.link) == 0">
                            <el-input v-model="scope.row.linkval"></el-input>
                        </template>
                        <template v-else-if="parseInt(scope.row.link) == 1">
                            <el-select v-model="scope.row.linkval" style="width: 100%">
                                <el-option v-for="item in typecollection.library" :label="item.appname" :value="item.appid"></el-option>
                            </el-select>
                        </template>
                        <template v-else-if="parseInt(scope.row.link) == 2">
                            <el-select v-model="scope.row.linkval" style="width: 100%">
                                <el-option v-for="item in typecollection.alonepage" :label="item.pagename" :value="item.id" :key="item.id"></el-option>
                            </el-select>
                        </template>
                        <template v-else-if="parseInt(scope.row.link) == 4">
                            <el-select v-model="scope.row.linkval" style="width: 100%">
                                <el-option v-for="item in typecollection.tab" :label="item.name" :value="item.gid" :key="item.gid"></el-option>
                            </el-select>
                        </template>
                        <template v-else-if="parseInt(scope.row.link) == 3">
                            <el-cascader 
                                style="width: 100%"
                                v-model="scope.row.linkval" 
                                :options="typecollection.banner"
                                :show-all-levels="false"
                                :emitPath="false"
                                :props="{value:'id',label:'bannername',checkStrictly:true}" 
                                clearable></el-cascader>
                        </template>
                    </div>
                </template>
            </el-table-column>
            <el-table-column width="80" align="center">
                <template #default="scope">
                    <el-button type="danger" @click="handledelete(scope.$index)" icon="Delete" plain size="small"></el-button>
                </template>
            </el-table-column>
            <template #append>
                <el-button style="width: 100%;border-radius: 0;" plain text @click="handleadd" icon="plus">添加</el-button>
            </template>
        </el-table>
    `,
    setup(props,context){
        let DomTable = ref(null);
        let curRowIndex = ref(null);//当前第几个上传
        if(props.model && props.model.length){
            props.model.forEach(item => {
                item.key = getId();
            });
        }
        
        function getId(){  //获取随机数id
            let date = Date.now();
            let rund = Math.ceil(Math.random()*1000)
            let id = date + '' + rund;
            return id;
        };
        function handleadd(){//添加
            let str = {key:getId(),title:'',img:'',aid:0,link:'0',linkval:''};
            props.model.push(str);
        };
        function handledelete(index){//删除
            props.model.splice(index,1);
        };
        function handleImgDelte(index){//图片删除
            props.model[index].aid = 0;
            props.model[index].url = '';
            props.model[index].img = '';
        };
        function handleUploadSucess(response, file, fileList){//上传成功
            if(response.files && response.files.length){
                let files = response.files[0];
                props.model[curRowIndex.value].aid = files.data.aid;
                props.model[curRowIndex.value].img = files.name;
                props.model[curRowIndex.value].url = files.data.img;
            }

        };
        onMounted(function(){//排序
            let tbody = DomTable.value.$el.querySelector('.el-table__body-wrapper tbody');
            Sortable.create(tbody, {
                handle: ".table-move-icon",
                animation: 150, // ms, number 单位：ms，定义排序动画的时间
                onUpdate: function(/**Event*/evt) {
                    const currentRow = props.model.splice(evt.oldIndex, 1)[0];
                    props.model.splice(evt.newIndex, 0, currentRow);
                }
            })
        });
        return {
            DomTable,
            handleadd,
            handledelete,
            curRowIndex,
            handleUploadSucess,
            handleImgDelte
        }
    }
}
const Tmpmanual_rec = {
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
                    <Tmpmanual_recTable :model="item.data" :typecollection="typecollection"></Tmpmanual_recTable>
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
        Tmpmanual_recTable
    }
}

