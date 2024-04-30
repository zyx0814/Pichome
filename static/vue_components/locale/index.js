// languageOperation组件：用于操作语言键值的编辑和排序
const languageOperation = {
    props: { // 组件接收的props
        langkey: { // 语言键
            required: true,
            type: String,
            default: '',
        },
        value: { // 初始语言值数组
            required: false,
            type: Array,
            default: [],
        },
    },
    template: `<!-- 表格展示区域，包括添加按钮和表格 -->
    <div ref="tableRef">
        <el-table
            border
            :data="tableData"
            style="width: 100%"
            row-key="id">
            <el-table-column
                prop=""
                align="center"
                label="排序"
                width="60">
                <template #default="scope">
                <el-icon class="td-drag"><Rank /></el-icon>
                </template>
            </el-table-column>
            <el-table-column
                prop="name"
                label="名称">
                <template #default="scope">
                    <el-input v-model="scope.row.name" @blur="handleBlur(scope.$index)"  size="small" placeholder="请输入内容" ></el-input>
                </template>
            </el-table-column>
            <el-table-column
                label="操作"
                width="75">
                <template #default="scope">
                    <el-button @click="handleDelete(scope.$index)" size="small" type="danger" icon="Delete" plain></el-button>
                </template>
            </el-table-column>
        </el-table>
    </div>
    
    <div class="add-table-block" @click="handleAdd">
        <el-icon><Plus /></el-icon>
    </div>
    `,
    emits: ['change'], // 组件发出的事件
    setup(props, context){
        let tableRef = ref(null); // 表格的引用
        let tableData = ref([]); // 表格数据
        let Findex = 0; // 用于生成表格行的唯一ID
        // 处理传入的value，将其转换为表格数据格式
        try {
            if (Array.isArray(props.value) && props.value.length) {
                // 使用 Array.prototype.map 来创建新数组，这样可以提高性能和代码的简洁性
                // 假设 Findex 是从1开始递增的
                const newData = props.value.map((element, index) => {
                    // 确保元素是字符串类型，否则转换为字符串
                    const name = typeof element === 'string' ? element : String(element);
                    return { name, newval: '', oldval: name, id: index + 1 };
                });
        
                // 假设 tableData.value 已经是一个数组，现在将新数据合并进去
                tableData.value.push(...newData);
            }
        } catch (error) {
            console.error("Error processing props.value: ", error);
            // 根据业务需求，这里可以选择抛出错误，或者进行其他的错误处理
        }
        // 添加新行
        function handleAdd(){
            tableData.value.push({
                name:'',
                newval:'',
                oldval:'',
                id:Findex
            });
            Findex ++;
        }
        // 删除指定行
        function handleDelete(index){
            tableData.value.splice(index,1);
        }
        // 初始化表格排序功能
        function handleSortable(){
            const tbody = tableRef.value.querySelector('.el-table__body-wrapper tbody');
            new Sortable(tbody, {
                handle:'.td-drag',
                forcefallback:"true",
                draggable: ".el-table__row",
                onEnd ({ newIndex, oldIndex }) {
                    const currRow = tableData.value.splice(oldIndex, 1)[0]
                    tableData.value.splice(newIndex, 0, currRow);
                }
            });
        }
        // 当输入框失去焦点时，检查值是否更改并更新newval，同时触发change事件
        function handleBlur(index){
            if(tableData.value[index].oldval != tableData.value[index].name){
                tableData.value[index].newval = tableData.value[index].name;
            }
            this.$emit('change',{
                data:tableData.value,
                langkey:props.langkey
            });
        }
        onMounted(function(){
            handleSortable();
        }) // 组件挂载后执行排序初始化
        return {
            tableData,
            tableRef,

            handleAdd,
            handleDelete,
            handleBlur
        }
    }
};

// language组件：用于展示和编辑多语言配置
const language= {
    props: { // 组件接收的props
        langkey: { // 语言键
            required: true,
            type: String,
            default: '',
        },
        icon: { // 是否显示图标
            required: false,
            type: Boolean,
            default: false,
        },
    },
    template: `<!-- 根据是否显示图标，选择不同的展示方式 -->
        <template v-if="langkey">
            <template v-if="icon">
                <el-icon @click="dialogVisible=true">
                    <svg>...</svg> <!-- 图标SVG内容 -->
                </el-icon>
            </template>
            <template v-else>
                <div class="lang-btn">
                    <el-button type="primary" style="padding:8px;" @click="dialogVisible=true">
                        <el-icon>
                            <svg>...</svg> <!-- 图标SVG内容 -->
                        </el-icon>
                    </el-button>
                </div>
            </template>
            
            <!-- 对话框内容 -->
            <el-dialog
                v-model="dialogVisible"
                width="500"
                title="多语言设置"
                :close-on-press-escape="false"
                :close-on-click-modal="false"
                @open="open"
                append-to-body
                :show-close="!loading">
                    
                <!-- 单页样式设置 -->
                <template v-if="pagestyle == 'single'">
                    <el-form label-width="auto">
                        <el-form-item  label=" ">
                            <el-text tag="b">{{defaultTxt}}</el-text>
                        </el-form-item>
                        <template v-for="item in formData">
                            <el-form-item :label="item.lablename+'：'">
                                <!-- 根据输入类型渲染不同输入组件 -->
                                <template v-if="item.inputtype=='input' || item.inputtype=='textarea'">
                                    <el-input v-model="item.value" :type="item.inputtype" />
                                </template>
                                <template v-if="item.inputtype=='options'">
                                    <el-input v-for="" v-model="item.value" :type="item.inputtype" />
                                </template>
                            </el-form-item>
                        </template>
                    </el-form>
                </template>
                <!-- 分组样式设置 -->
                <template v-else-if="pagestyle == 'group'">
                    <el-tabs v-model="tabsActiveName">
                        <el-tab-pane v-for="(item,key) in formData" :label="item.lablename" :name="key">
                            <languageoperation @change="changeOperation" :langkey="item.key"></languageoperation>
                        </el-tab-pane>
                    </el-tabs>
                </template>
                <template #footer>
                    <div class="dialog-footer">
                        <el-button @click="dialogVisible = false" :disabled=loading>取消</el-button>
                        <el-button type="primary" @click="handleSubmit" :loading="loading">确定</el-button>
                    </div>
                </template>
            </el-dialog>
        </template>
       
    `,
    setup(props, context){
        let dialogVisible = ref(false); // 对话框是否可见
        let formData = ref([]); // 表单数据
        let defaultTxt = ref(''); // 默认文本
        let loading = ref(false); // 加载状态
        let pagestyle = ref(false); // 页面样式
        let tabsActiveName = ref(0); // 当前激活的标签页
        let formhash = ''; // 表单hash值，用于提交表单验证
        // 打开对话框时获取表单数据
        async function open(){
            if(!props.langkey)return false;
            const {data: res} = await axios.post('index.php?mod=lang&op=ajax&do=getLangkeyList',{
                langkey:props.langkey
            });
            formhash = res.langkey.formhash;
            formData.value = res.langkey.langkey;
            pagestyle.value = res.langkey.pagestyle;
            defaultTxt.value= res.langkey.defaultval;
        }
        // 提交表单
        async function handleSubmit(){
            loading.value = true;
            let param = {
                langsave:true,
                formhash:formhash,
                langdata:{}
            };
            for (let index = 0; index < formData.value.length; index++) {
                const element = formData.value[index];
                param.langdata[element.key] = element.value;
            }
            const {data: res} = await axios.post('index.php?mod=lang&op=ajax&do=saveData',param);
            if(res.success){
                dialogVisible.value = false;
            }else{
                ElementPlus.ElMessage({
                    type:'error',
                    message:res.error || __lang.submit_error
                });
            }
            loading.value = false;
        };
        // 接收语言操作组件的数据更新
        function changeOperation(data){
            let curr = formData.value.find(function(current){
                return current.key == data.langkey;
            });
            if(!curr)return false;
            curr.value = data.data;
        }
        return {
            dialogVisible,
            tabsActiveName,
            pagestyle,
            formData,
            handleSubmit,
            defaultTxt,
            open,
            loading,
            changeOperation
        }
    },
    components: { // 组件所使用的子组件
        'languageoperation': languageOperation,
    },
};