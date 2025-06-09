const tabExport = {
    name:'tabExport',
    props:{
        params:{
            required:false,
            type: Object,
            default:{
                gid:0,
                isall:0,
                geturl:'',
                submiturl:'',
                params:{}
            },
        },
    },
    template:`
        <el-dialog 
            v-model="MainExport.visible" 
            title="导出"
            width="50%" 
            @closed="handleClose"
            append-to-body>
                <el-container class="export-container" v-loading=loading style="min-height: 100px;">
                    <template v-if="!loading">
                        <el-aside width="50%" style="border-right: var(--el-border);padding-right: 10px;">
                            <el-scrollbar max-height="500px">
                                <el-row style="margin-bottom: 10px;">
                                    <el-col :span="7">
                                        <div class="title" style="text-align: right;line-height: 32px;">导出格式：</div>
                                    </el-col>
                                    <el-col :span="17">
                                        <el-radio-group v-model="MainExportForm.format">
                                            <el-radio label="xlsx">
                                                <el-icon style="font-size: 18px;vertical-align: bottom;">
                                                    <svg width="26" height="26" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 4H30L40 14V42C40 43.1046 39.1046 44 38 44H10C8.89543 44 8 43.1046 8 42V6C8 4.89543 8.89543 4 10 4Z" fill="#417506" stroke="#417506" stroke-width="2" stroke-linejoin="round"/><path d="M29 18H19V34H29" stroke="#FFF" stroke-width="2" stroke-linecap="square" stroke-linejoin="round"/><path d="M29 26H19" stroke="#FFF" stroke-width="2" stroke-linecap="square" stroke-linejoin="round"/></svg>
                                                </el-icon>
                                                Excel
                                            </el-radio>
                                            <el-radio label="word">
                                                <el-icon style="font-size: 18px;vertical-align: bottom;">
                                                    <svg width="24" height="24" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 4H30L40 14V42C40 43.1046 39.1046 44 38 44H10C8.89543 44 8 43.1046 8 42V6C8 4.89543 8.89543 4 10 4Z" fill="#428FE7" stroke="#428FE7" stroke-width="2" stroke-linejoin="round"/><path d="M16.0083 20L19.0083 34L24.0083 24L29.0083 34L32.0083 20" stroke="#FFF" stroke-width="2" stroke-linecap="square" stroke-linejoin="round"/></svg>
                                                </el-icon>
                                                Word
                                            </el-radio>
                                            <el-radio label="pdf">
                                                <el-icon style="font-size: 18px;vertical-align: bottom;">
                                                    <svg width="24" height="24" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 4H30L40 14V42C40 43.1046 39.1046 44 38 44H10C8.89543 44 8 43.1046 8 42V6C8 4.89543 8.89543 4 10 4Z" fill="#D6424E" stroke="#D6424E" stroke-width="2" stroke-linejoin="round"/><path fill-rule="evenodd" clip-rule="evenodd" d="M18 18H30V25.9917L18.0083 26L18 18Z" stroke="#FFF" stroke-width="2" stroke-linecap="square" stroke-linejoin="round"/><path d="M18 18V34" stroke="#FFF" stroke-width="2" stroke-linecap="square"/></svg>
                                                </el-icon>
                                                PDF
                                            </el-radio>
                                        </el-radio-group>
                                    </el-col>
                                </el-row>
                                <el-row style="margin-bottom: 10px;">
                                    <el-col :span="7">
                                        <div class="title" style="text-align: right;line-height: 32px;">选择全部：</div>
                                    </el-col>
                                    <el-col :span="17">
                                        <el-checkbox 
                                            v-model="MainExportForm.all" 
                                            @change="handleFormAll"
                                            :true-label="true" 
                                            :false-label="false">${__lang.all}</el-checkbox>
                                    </el-col>
                                </el-row>

                                <template v-for="(item,key) in MainExport.data">
                                    <el-row style="margin-bottom: 10px;">
                                        <el-col :span="7">
                                            <div class="title" style="text-align: right;line-height: 32px;">{{item.catname}}：</div>
                                        </el-col>
                                        <el-col :span="17">
                                            <el-checkbox 
                                                v-model="MainExportForm.formlist[item.id+'id'].all"
                                                :indeterminate="MainExportForm.formlist[item.id+'id'].indeterminate"
                                                style="width: 100%;" 
                                                :true-label="true" 
                                                :false-label="false" 
                                                @change="handleFormAllChecked(item.id+'id',key)">${__lang.all}</el-checkbox>
                                            <el-checkbox 
                                                v-for="fitem in item.fileds" 
                                                style="width: 100%;"
                                                @change="handleFormSingleChecked(item.id+'id',fitem.flag,key)"
                                                v-model="MainExportForm.formlist[item.id+'id'].checked" 
                                                :label="fitem.flag">{{fitem.name}}</el-checkbox>
                                        </el-col>
                                    </el-row>
                                </template>
                            </el-scrollbar>
                        </el-aside>
                        <el-aside width="50%" style="padding-left: 10px;overflow: hidden;" class="export-right">
                            <div style="line-height: 32px;"><el-text tag="b">已选字段：可拖拽排序</el-text></div>
                            <el-scrollbar max-height="468px">
                                <ul class="export-right-list" ref="sortableList">
                                    <li v-for="item in MainExportRightForm" :key="item.flag">
                                        <el-text>{{item.name}}</el-text>
                                        <el-icon><Rank /></el-icon>
                                    </li>
                                </ul>
                            </el-scrollbar>
                        </el-aside>
                    </template>
                    
                </el-container>
            </el-scrollbar>
            <template #footer>
                <span class="dialog-footer">
                    <el-button @click="MainExport.visible = false">${__lang.cancel}</el-button>
                    <el-button type="primary" @click="handleConfirm" :disabled="!MainExportRightForm.length">${__lang.export}</el-button>
                </span>
            </template>
        </el-dialog>
    `,
    setup(props, context){
        let sortableList = ref(null);
        let loading = ref(false);
        let MainExport = reactive({
            visible: true,
            data: [],
        });
        let MainExportForm = reactive({
            format:'xlsx',
            all:false,
            allcount:0,
            formlist:{}
        });
        let MainExportRightForm = ref([]);

        /**
         * 异步获取数据并处理
        */
        async function getdata() {
            loading.value = true;
        
            // 定义请求参数
            const param = {
                gid: props.params.gid
            };
        
            const { data: res } = await axios.post(props.params.geturl, param);
        
            // 处理系统字段
            if (res.sysformlist) {
                const fileds = Object.values(res.sysformlist);
                MainExport.data.push({
                    catname: '系统字段',
                    id: 'system',
                    fileds: fileds
                });
            }
        
            // 处理其他数据字段
            Object.keys(res.data).forEach(key => {
                MainExport.data.push(res.data[key]);
            });
        
            // 初始化表单状态
            MainExportForm.allcount = MainExport.data.reduce((acc, curr) => {
                const filedsCount = curr.fileds ? curr.fileds.length : 0;
                acc += filedsCount;
                MainExportForm.formlist[curr.id + 'id'] = {
                    all: false,
                    count: filedsCount,
                    indeterminate: false,
                    checked: []
                };
                return acc;
            }, 0);
            loading.value = false;
            nextTick(() => {
                handleSortable();
            });
        }

        function handleFormAll(val) {
            MainExportRightForm.value = [];
            Object.keys(MainExportForm.formlist).forEach(key => {
                const formListItem = MainExportForm.formlist[key];
                formListItem.checked = [];
                formListItem.indeterminate = false;
                formListItem.all = val;
        
                if (val) {
                    const categoryData = MainExport.data.find(item => item.id + 'id' === key);
                    if (categoryData) {
                        const checkedFlags = categoryData.fileds.map(field => field.flag);
                        formListItem.checked.push(...checkedFlags);
                        MainExportRightForm.value.push(...checkedFlags.map(flag => ({ flag, name: categoryData.fileds.find(f => f.flag === flag).name })));
                    }
                }
            });
        }

        /**
         * 处理表单全选逻辑
         * @param val 表单项的键值
         * @param key 数据项的键值
         */
        function handleFormAllChecked(categoryId, dataIndex) {
            const formListItem = MainExportForm.formlist[categoryId];
            if (!formListItem) return false;
        
            // 先判断formListItem.all，避免不必要的映射操作
            if (formListItem.all) {
                // 当全选为真时，使用映射生成新数组
                formListItem.checked = MainExport.data[dataIndex].fileds.map(field => field.flag);
                formListItem.indeterminate = false;
            } else {
                // 当全选为假时，直接清空数组
                formListItem.checked = [];
            }
            
            // 调用处理函数更新右侧表单
            handleMainExportRight('all', categoryId, dataIndex);
        }
        

        /**
         * 处理表单单选框选中的状态
         *
         * @param {string} id - 表单的ID
         * @param {boolean} flag - 是否选中
         * @param {string} key - 表单键名
         */
        function handleFormSingleChecked(categoryId, fieldFlag, dataIndex) {
            const formListItem = MainExportForm.formlist[categoryId];
            if (!formListItem) return false;
        
            // 更新全选和半选状态
            formListItem.all = formListItem.checked.length === formListItem.count;
            formListItem.indeterminate = formListItem.checked.length > 0 && !formListItem.all;
        
            // 处理单个字段选中状态变化
            handleMainExportRight('', categoryId, dataIndex, fieldFlag);
        }

        function handleMainExportRight(type, checkedId, dataKey, fieldFlag) {
            if (type === 'all') {
                // 清空当前分类的所有字段
                MainExportRightForm.value = MainExportRightForm.value.filter(
                    item => !MainExport.data[dataKey].fileds.some(field => field.flag === item.flag)
                );
                // 如果选中状态为真，则添加所有字段
                if (MainExportForm.formlist[checkedId].all) {
                    MainExport.data[dataKey].fileds.forEach(field => {
                        MainExportRightForm.value.push({ flag: field.flag, name: field.name });
                    });
                }
            } else {
                // 根据字段标志处理单个字段的选中状态
                const fieldIndex = MainExportRightForm.value.findIndex(item => item.flag === fieldFlag);
                if (MainExportForm.formlist[checkedId].checked.includes(fieldFlag)) {
                    if (fieldIndex === -1) {
                        const field = MainExport.data[dataKey].fileds.find(item => item.flag === fieldFlag);
                        MainExportRightForm.value.push({ flag: fieldFlag, name: field.name });
                    }
                } else if (fieldIndex > -1) {
                    MainExportRightForm.value.splice(fieldIndex, 1);
                }
            }
            // 更新全选状态
            nextTick(() => {
                MainExportForm.all = MainExportForm.allcount === MainExportRightForm.value.length;
            });
        }

        function handleSortable(){
            // 初始化 Sortable 对象
            new Sortable(sortableList.value, {
                // 阻止在过滤条件触发时不触发拖拽事件
                preventOnFilter: true,
                // 动画持续时间
                animation: 150,
                // 列表内元素顺序更新的时候触发
                onUpdate ({ newIndex, oldIndex }) {
                    // 获取当前被拖动的行
                    var currRow = MainExportRightForm.value.splice(oldIndex, 1)[0];
                    // 在新的位置插入该行
                    MainExportRightForm.value.splice(newIndex, 0, currRow);
                }
            });
        }

        /**
         * 处理确认操作
         */
        function handleConfirm(){
            let url = props.params.submiturl+'&';
        
            let baseParams  = {
                exporttype: MainExportForm.format,
                ...props.params.params // 使用展开运算符合并props.params
            }
        
            const params = new URLSearchParams(baseParams).toString();
            url += params;
        
            const flags = MainExportRightForm.value.map(item => item.flag);
            if (flags.length > 0) {
                url += '&fileds=' + flags.join(',');
            }
            window.open(url);
            handleClose();
        }
        function handleClose(){
            context.emit('hide');
        }
        onMounted(()=>{
            getdata();
        })
        return {
            sortableList,
            MainExport,
            MainExportForm,
            MainExportRightForm,
            loading,
            handleFormAll,
            handleFormAllChecked,
            handleFormSingleChecked,
            handleConfirm,
            handleClose,
            getdata
        };
    },
    components: {

    },
};