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
                type="index"
                width="50">
            </el-table-column>
            <el-table-column
                prop=""
                align="center"
                :label="langTxt.text1"
                width="60">
                <template #default="scope">
                <el-icon class="td-drag"><Rank /></el-icon>
                </template>
            </el-table-column>
            <el-table-column
                prop="name"
                :label="langTxt.text2">
                <template #default="scope">
                    <el-input v-model="scope.row.name" @blur="handleBlur(scope.$index)"  size="small" :placeholder="langTxt.text3" ></el-input>
                </template>
            </el-table-column>
            <el-table-column
                :label="langTxt.text4"
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
        let langTxt = {
            text1:__lang.sort,
            text2:__lang.name,
            text3:__lang.please_input,
            text4:__lang.operation,
        };
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
                context.emit('change',{
                    data:tableData.value,
                    langkey:props.langkey
                });
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
            context.emit('change',{
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
            langTxt,
            handleAdd,
            handleDelete,
            handleBlur
        }
    }
};

const languageInputOperation = {
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
                type="index"
                width="50">
            </el-table-column>
            <el-table-column
                prop=""
                align="center"
                :label="langTxt.text1"
                width="60">
                <template #default="scope">
                <el-icon class="td-drag"><Rank /></el-icon>
                </template>
            </el-table-column>
            <el-table-column
                prop="name"
                :label="langTxt.text2">
                <template #default="scope">
                    <el-input v-model="scope.row.name" @blur="handleBlur(scope.$index)"  size="small" :placeholder="langTxt.text3" ></el-input>
                </template>
            </el-table-column>
            <el-table-column
                :label="langTxt.text4"
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
        let langTxt = {
            text1:__lang.sort,
            text2:__lang.name,
            text3:__lang.please_input,
            text4:__lang.operation,
        };
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
                context.emit('change',{
                    data:tableData.value,
                    langkey:props.langkey
                });
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
            context.emit('change',{
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
            langTxt,
            handleAdd,
            handleDelete,
            handleBlur
        }
    }
};
const languageLink = {
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
        other:{ //其它数据（库、单页、栏目）数据
            required: false,
            type: Object,
            default: {},
        },
        findex:{ 
            required: true,
            type: Number,
            default: 0,
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
                type="index"
                width="50">
            </el-table-column>
            <el-table-column
                prop=""
                align="center"
                label="${__lang.sort}"
                width="60">
                <template #default="scope">
                    <el-icon class="td-drag"><Rank /></el-icon>
                </template>
            </el-table-column>
            <el-table-column label="${__lang.icon}" width="60" align="center">
                <template #default="scope">
                    <el-upload
                        :show-file-list="false" 
                        name="files"
                        accept="image/gif,image/png,image/jpg,image/jpeg,image/svg"
                        action="index.php?mod=alonepage&op=alonepageinterface&do=upload" 
                        :on-success="handleUploadSucess">
                        <div @click="curRowIndex=scope.$index">
                            <img style="object-fit: contain;height: 32px;width: 32px;border-radius: var(--el-border-radius-small);" v-if="parseInt(scope.row.aid)" :src="scope.row.img" class="avatar" />
                            <el-button v-else plain icon="plus" size="small" style="padding: 0;height: 32px;width: 32px;"></el-button>
                        </div>
                    </el-upload>
                </template>
            </el-table-column>
            <el-table-column
                prop="title"
                label="${__lang.name}">
                <template #default="scope">
                    <el-input v-model="scope.row.title"  size="small" placeholder="${__lang.please_input}" @change="handleTitleBlur"></el-input>
                </template>
            </el-table-column>
            <el-table-column
                prop="message"
                label="${__lang.Link_address}">
                <template #default="scope">
                    <el-input v-model="scope.row.message"  size="small" placeholder="${__lang.Link_address}" ></el-input>
                </template>
            </el-table-column>
            <el-table-column
                label="${__lang.Link_address}">
                <template #default="scope">
                    <div style="display:flex;">
                        <el-select v-model="scope.row.link" style="width: 110px;margin-right:6px;" @change="scope.row.linkval=''">
                            <el-option label="${__lang.address}" value="0"></el-option>
                            <el-option label="${__lang.library}" value="1"></el-option>
                            <el-option label="${__lang.page}" value="2"></el-option>
                            <el-option label="${__lang.column}" value="3"></el-option>
                        </el-select>
                        <template v-if="parseInt(scope.row.link) == 0">
                            <el-input v-model="scope.row.linkval"></el-input>
                        </template>
                        <template v-else-if="parseInt(scope.row.link) == 1">
                            <el-select v-model="scope.row.linkval" style="width: 100%">
                                <el-option v-for="item in other.library" :label="item.appname" :value="item.appid"></el-option>
                            </el-select>
                        </template>
                        <template v-else-if="parseInt(scope.row.link) == 2">
                            <el-select v-model="scope.row.linkval" style="width: 100%">
                                <el-option v-for="item in other.alonepage" :label="item.pagename" :value="item.id" :key="item.id"></el-option>
                            </el-select>
                        </template>
                        <template v-else-if="parseInt(scope.row.link) == 4">
                            <el-select v-model="scope.row.linkval" style="width: 100%">
                                <el-option v-for="item in other.tab" :label="item.name" :value="item.gid" :key="item.gid"></el-option>
                            </el-select>
                        </template>
                        <template v-else-if="parseInt(scope.row.link) == 3">
                            <el-cascader 
                                style="width: 100%"
                                v-model="scope.row.linkval" 
                                :options="other.banner"
                                :show-all-levels="false"
                                :emitPath="false"
                                :props="{value:'id',label:'bannername',checkStrictly:true}" 
                                clearable></el-cascader>
                        </template>
                    </div>
                </template>
            </el-table-column>
            <el-table-column
                label="${__lang.operation}"
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
        let curRowIndex = ref(null);//当前第几个上传
        // 处理传入的value，将其转换为表格数据格式
        try {
            
            if (Array.isArray(props.value) && props.value.length) {
                
                // 使用 Array.prototype.map 来创建新数组，这样可以提高性能和代码的简洁性
                // 假设 Findex 是从1开始递增的
                const newData = props.value.map((element, index) => {
                //     // 确保元素是字符串类型，否则转换为字符串
                    return { 
                        aid:element.aid,
                        img:element.img,
                        title:element.title,
                        message: element.message, 
                        link: element.link, 
                        linkval: element.linkval,
                        id: index + 1 
                    };
                });
                // 假设 tableData.value 已经是一个数组，现在将新数据合并进去
                tableData.value.push(...newData);

                change();
            }
        } catch (error) {
            console.error("Error processing props.value: ", error);
            // 根据业务需求，这里可以选择抛出错误，或者进行其他的错误处理
        }
        // 添加新行
        function handleAdd(){
            tableData.value.push({
                aid:0,
                img:'',
                title:'',
                message:'',
                link:'0',
                linkval:'',
                id:Findex
            });
            Findex ++;
        }
        // 删除指定行
        function handleDelete(index){
            tableData.value.splice(index,1);
            change();
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
        function handleTitleBlur(index){
            if(props.value[index]){
                props.value[index].title = tableData.value[index].title;
            }
            change();
        }
        function handleMessageBlur(index){
            if(props.value[index]){
                props.value[index].message = tableData.value[index].message;
            }
            change();
        }
        function change(){
            context.emit('change',{
                data:tableData.value,
                langkey:props.langkey,
                findex:props.findex
            });
        }
        function handleUploadSucess(response, file, fileList){//上传成功
            if(response.files && response.files.length){
                let files = response.files[0];
                tableData.value[curRowIndex.value].aid = files.data.aid;
                tableData.value[curRowIndex.value].img = files.data.img;
            }

        };
        onMounted(function(){
            handleSortable();
        }) // 组件挂载后执行排序初始化
        return {
            tableData,
            tableRef,
            curRowIndex,
            handleUploadSucess,
            handleAdd,
            handleDelete,
            handleTitleBlur,
            handleMessageBlur
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
        mark: { // 标识
            required: false,
            type: String,
            default: '',
        },
        isbtn:{ // 是否显示按钮
            required: false,
            type: Boolean,
            default: true,
        },
        visible:{ // 显示/隐藏
            required: false,
            type: Boolean,
            default: false,
        },
        other:{ //其它数据（库、单页、栏目）数据
            required: false,
            type: Object,
            default: {},
        },
        id:{
            required: false,
            type: Number,
            default: 0,
        },
    },
    template: `<!-- 根据是否显示图标，选择不同的展示方式 -->
        <template v-if="isbtn">
            <template v-if="icon">
                <el-icon @click="dialogVisible=true">
                    <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M28.2857 37H39.7143M42 42L39.7143 37L42 42ZM26 42L28.2857 37L26 42ZM28.2857 37L34 24L39.7143 37H28.2857Z" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M16 6L17 9" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M6 11H28" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 16C10 16 11.7895 22.2609 16.2632 25.7391C20.7368 29.2174 28 32 28 32" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M24 11C24 11 22.2105 19.2174 17.7368 23.7826C13.2632 28.3478 6 32 6 32" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </el-icon>
            </template>
            <template v-else>
                <div class="lang-btn">
                    <el-button type="primary" style="padding:8px;" @click="dialogVisible=true">
                        <el-icon>
                            <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M28.2857 37H39.7143M42 42L39.7143 37L42 42ZM26 42L28.2857 37L26 42ZM28.2857 37L34 24L39.7143 37H28.2857Z" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M16 6L17 9" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M6 11H28" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 16C10 16 11.7895 22.2609 16.2632 25.7391C20.7368 29.2174 28 32 28 32" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M24 11C24 11 22.2105 19.2174 17.7368 23.7826C13.2632 28.3478 6 32 6 32" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </el-icon>
                    </el-button>
                </div>
            </template>
        </template>
        
        
        <!-- 对话框内容 -->
        <el-dialog
            v-model="dialogVisible"
            width="800"
            :title="langTxt.text1"
            :close-on-press-escape="false"
            :close-on-click-modal="false"
            @open="open"
            append-to-body
            :show-close="!loading">
            <!-- 单页样式设置 -->
            <template v-if="pagestyle == 'single'">
                <el-form label-width="auto">
                    
                    <template v-for="(item,index) in formData">
                        <el-form-item :label="item.lablename+'：'">
                            <!-- 根据输入类型渲染不同输入组件 -->
                            <template v-if="item.inputtype=='input' || item.inputtype=='textarea'">
                                <el-input v-model="item.value" :type="item.inputtype" />
                            </template>
                            <template v-if="item.inputtype=='options'">
                                <el-input v-model="item.value" :type="item.inputtype" />
                            </template>
                            <template v-if="item.inputtype=='select'">
                                <el-select v-model="item.value" style="width: 100%">
                                    <el-option
                                        v-for="fitem in item.options"
                                        :key="fitem"
                                        :label="fitem"
                                        :value="fitem"
                                        />
                                </el-select>
                            </template>
                            <template v-if="item.inputtype=='multiselect'">
                                <el-select v-model="item.value" multiple  style="width: 100%">
                                    <el-option
                                        v-for="fitem in item.options"
                                        :key="fitem"
                                        :label="fitem"
                                        :value="fitem"
                                        />
                                </el-select>
                            </template>
                            <template v-if="item.inputtype=='rich_text'">
                                <div class="question-box" style="width: 100%">
                                    <div class="question-row">
                                        <div class="input-col">
                                            <rich_text_fulltext 
                                            :model="item.value"
                                            :gid="id"
                                            :ParenIndex="item.key"
                                            @change="RichTextFulltextChange"></rich_text_fulltext>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <template v-if="item.inputtype=='fulltext'">
                                <div class="question-box" style="width: 100%">
                                    <div class="question-row">
                                        <div class="input-col">
                                            <fulltext 
                                            :model="item.value"
                                            :fkey="item.key"
                                            :gid="id"
                                            @change="FulltextChange"></fulltext>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <template v-if="item.inputtype=='question'">
                                <div style="width: 100%">
                                    <div class="question-box" v-for="(question,findex) in item.value" :key="findex"  style="width: 100%">
                                        <div class="question-row" style="margin-bottom: 8px;">
                                            <div class="title-col">
                                                {{langTxt.text2}}：
                                            </div>
                                            <div class="input-col">
                                                <el-input v-model="question.title"></el-input>
                                            </div>
                                        </div>
                                        <div class="question-row">
                                            <div class="title-col" style="align-items: start;">{{langTxt.text3}}：</div>
                                            <div class="input-col">
                                                <question_fulltext
                                                    :model="question.answer"
                                                    :ParenIndex="index+'_'+findex"
                                                    @change="QuestionFulltextChange"></question_fulltext>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            
                        </el-form-item>
                    </template>
                </el-form>
            </template>
            <!-- 分组样式设置 -->
            <template v-else-if="pagestyle == 'group'">
                <el-tabs v-model="tabsActiveName">
                    <el-tab-pane v-for="(item,key) in formData" :label="item.lablename" :name="key">
                        <languageinputoperation @change="changeOperation" :langkey="item.key" :value="item.value"></languageoperation>
                    </el-tab-pane>
                </el-tabs>
            </template>
            <template v-else-if="pagestyle == 'idgroup'">
                <el-tabs v-model="tabsActiveName">
                    <el-tab-pane v-for="(item,key) in formData" :label="item.lablename" :name="key">
                        <languageinputoperation @change="changeOperation" :langkey="item.key" :value="item.value"></languageinputoperation>
                    </el-tab-pane>
                </el-tabs>
            </template>
            <template v-else-if="pagestyle == 'link'">
                <el-tabs v-model="tabsActiveName">
                    <el-tab-pane v-for="(item,key) in formData" :label="item.lablename" :name="key">
                        <languagelink 
                            :value="item.value || []" 
                            :langkey="item.key"
                            :other="other"
                            :findex="key"
                            @change="changeTabOperation"></languagelink>
                    </el-tab-pane>
                </el-tabs>
            </template>
            <template #footer>
                <div class="dialog-footer">
                    <el-button @click="dialogVisible = false" :disabled=loading>{{langTxt.text4}}</el-button>
                    <el-button type="primary" @click="handleSubmit" :loading="loading">{{langTxt.text5}}</el-button>
                </div>
            </template>
        </el-dialog>
       
    `,
    setup(props, context){
        let langTxt = {
            text1:__lang.Multilingual_settings,
            text2:__lang.question,
            text3:__lang.answer,
            text4:__lang.cancel,
            text5:__lang.confirms
        };
        let dialogVisible = ref(false); // 对话框是否可见
        let formData = ref([]); // 表单数据
        let defaultTxt = ref(''); // 默认文本
        let loading = ref(false); // 加载状态
        let pagestyle = ref(false); // 页面样式
        let tabsActiveName = ref(0); // 当前激活的标签页
        let formhash = ''; // 表单hash值，用于提交表单验证
        let lang = '';
        // 打开对话框时获取表单数据
        async function open(){
            if(!props.langkey)return false;
            formData.value = [];
            const {data: res} = await axios.post('index.php?mod=lang&op=ajax&do=getLangkeyList',{
                langkey:props.langkey
            });
            formhash = res.langkey.formhash;
            lang = res.lang;
            if(res.langkey.langkey && res.langkey.langkey.length){
                res.langkey.langkey.forEach((current) => {
                    if(current.inputtype == "multiselect"){
                        if(current.value){
                            current.value = current.value.split(',');
                        }
                    }else if(current.inputtype == "question"){
                        current.value = current.value;
                    }
                })
            }
            formData.value = res.langkey.langkey;
            pagestyle.value = res.langkey.pagestyle;
            if(Array.isArray(res.langkey.defaultval)){
                defaultTxt.value= res.langkey.defaultval.join(',');
            }else{
                defaultTxt.value= res.langkey.defaultval;
            }
            
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
                if(element.inputtype == "multiselect"){
                    param.langdata[element.key] = element.value.join(',');
                }else{
                    param.langdata[element.key] = element.value;
                }
                
            }
            const {data: res} = await axios.post('index.php?mod=lang&op=ajax&do=saveData',param);
            if(res.success){
                dialogVisible.value = false;
                let curr = formData.value.find(function(current){
                    return current.lang == lang;
                });
                if(curr){
                    context.emit('change',curr.value,props.mark);
                }
               
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
        function changeTabOperation(data){
            formData.value[data.findex].value = data.data
        }

        
        function FulltextChange(data){
            let curr = formData.value.find(current => {
                return current.key == data.key
            });
            if(!curr)return false;
            curr.value = data.value;
        }
        function RichTextFulltextChange(data){
            let curr = formData.value.find(current => {
                return current.key == data.key
            });
            if(curr){
                curr.value = data.value;
            }
        }
        function QuestionFulltextChange(data){
            let keys = data.key.split('_');
            formData.value[keys[0]].value[keys[1]].answer = data.value;
        }
        
        watch(()=>props.visible, (newValue)=>{
            dialogVisible.value = newValue;
        },{
            deep: true,
        });
        watch(()=>dialogVisible.value, (newValue)=>{
            context.emit('modelvalue',newValue);
        },{
            deep: true,
        });
        return {
            langTxt,
            dialogVisible,
            tabsActiveName,
            pagestyle,
            formData,
            handleSubmit,
            defaultTxt,
            open,
            loading,
            changeOperation,
            changeTabOperation,
            FulltextChange,
            RichTextFulltextChange,
            QuestionFulltextChange
        }
    },
    components: { // 组件所使用的子组件
        'languageoperation': languageOperation,
        'languageinputoperation': languageInputOperation,
        'languagelink': languageLink,
    },
};