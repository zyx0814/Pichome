const Tmprich_toolbar = {
    props:{
        model:{
            required:true,
            type: String,
            default:'',
        },
        ParenIndex:{
            required:true,
            type: Number,
            default:0,
        }
    },
    template:`
        <div class="editor—wrapper">
            <div class="toolbar-container" ref="DomToolbar"><!-- 工具栏 --></div>
            <div class="editor-container" ref="DomEditor"><!-- 编辑器 --></div>
        </div>
    `,
    setup(props,context){
        let DomToolbar = ref(null);
        let DomEditor = ref(null);
        let { createEditor, createToolbar } = window.wangEditor;
        var uploadImage = {
            server: BasicUrl+'upload',
            timeout: 5 * 1000, // 5s
            maxNumberOfFiles: 1,
            allowedFileTypes: ['image/*'],
            fieldName: 'files',
            meta: {},
            headers: { Accept: 'text/x-json' },
            maxFileSize: 10 * 1024 * 1024, // 10M
            base64LimitSize: 5 * 1024, // insert base64 format, if file's size less than 5kb
            onBeforeUpload(file) {
                var tt = '';
                for(var x in file){
                    var formData = new FormData();
                    formData.append('files', file[x].data);
                    file[x].meta = formData
                }
                return file; // prevent upload
            },
            customInsert(res, insertFn) {
                var url = res.files[0].data.img || '';
                var alt = res.files[0].name || '';
                var href = res.files[0].href || '';
                insertFn(url, alt, href)
            },
            onFailed(file, res) {
                ElementPlus.ElMessage.error('上传失败');
            },
            onError(file, err, res) {
                ElementPlus.ElMessage.error('上传错误');
            },
        };
        var uploadVideo = {
            server: BasicUrl+'upload',
            timeout: 15 * 1000, // 15 秒
            maxNumberOfFiles: 1,
            allowedFileTypes: ['video/*'],
            fieldName: 'files',
            meta: {},
            headers: { Accept: 'text/x-json' },
            maxFileSize: 10 * 1024 * 1024, // 10M
            onBeforeUpload(file) {
                var tt = '';
                for(var x in file){
                    var formData = new FormData();
                    formData.append('files', file[x].data);
                    file[x].meta = formData
                }
                return file; // prevent upload
            },
            customInsert(res, insertFn) {
                var url = res.files[0].data.img || '';
                insertFn(url)
            },
            onFailed(file, res) {
                ElementPlus.ElMessage.error('上传失败');
            },
            onError(file, err, res) {
                ElementPlus.ElMessage.error('上传错误');
            },
        };
        var editorConfig = {
            placeholder: '内容',
 
            MENU_CONF:{
                uploadImage:uploadImage,
                uploadVideo:uploadVideo
            },
            onChange(editor) {
                var html = editor.getHtml();
                props.model.data = html
            // 也可以同步到 <textarea>
            }
        };
        
        onMounted(function(){
            let editor = createEditor({
                selector: DomEditor.value,
                html: props.model.data,
                config: editorConfig,
                mode:'simple',
            });
            createToolbar({
                editor,
                selector: DomToolbar.value,
                config: {},
                mode:'simple',
            });
        });
        return {
            DomToolbar,
            DomEditor
        }
    }
}


const Tmprich_text = {
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
                    <Tmprich_toolbar :model="item"></Tmprich_toolbar>
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
        Tmprich_toolbar
    }
}