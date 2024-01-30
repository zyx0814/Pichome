const Tmppicture_rec = {
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
                    v-for="(item,key) in model.data"
                    :key="item.data[0].key"
                    :label="item.data[0].name"
                    :name="item.data[0].key">
                    <el-form label-width="120px" label-suffix=":">
                        <el-form-item label="数据来源">
                            <el-select
                                style="width:50%;"
                                v-model="item.data[0].id">
                                <el-option
                                    v-for="fitem in DataList"
                                    :key="fitem.id"
                                    :label="fitem.name"
                                    :value="fitem.id"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="数据排序">
                            <el-radio-group v-model="item.data[0].type" @change="handleChangeType(item)">
                                <el-radio style="margin-bottom:6px;" :label="1" border>最新推荐</el-radio>
                                <el-radio style="margin-bottom:6px;" :label="2" border>热门推荐</el-radio>
                                <el-radio style="margin-bottom:6px;" :label="3" border>标签推荐</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item label="标签推荐" v-if="item.data[0].type == 3">
                            <el-input v-model="item.data[0].value" placeholder="例：标签1,标签2" ></el-input>
                        </el-form-item>
                        <el-form-item label="数据数量">
                            <div style="width: 100%;">
                                <el-input-number v-model="item.data[0].number" :min="0"></el-input-number>
                                <el-text size="small" tag="p" type="info">数量0或者不填为不限数量</el-text>
                            </div>
                        </el-form-item>
                        <el-form-item label="更多链接">
                            <div style="width: 100%;">
                                <el-input style="width:50%;" v-model="item.data[0].link" placeholder="http://" clearable></el-input>
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

        let tabsvalue = ref(null);
        if(props.model.data && props.model.data.length && props.model.data[0].data.length){
            props.model.data[0].data.forEach(item => {
                item.key = getId();
            });
        }
        if(props.model.data.length){
            tabsvalue.value =  props.model.data[props.model.data.length - 1].data[0].key;
        }
        function getId(){  //获取随机数id
            let date = Date.now();
            let rund = Math.ceil(Math.random()*1000)
            let id = date + '' + rund;
            return id;
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
                        GetData(val);
                        nextTick(() => {
                            tabsvalue.value = props.model.data[props.model.data.length - 1].data[0].key;
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
                        props.model.data.splice(index,1);
                        if(tabsvalue.value == targetName){
                            nextTick(() => {
                                tabsvalue.value = props.model.data[props.model.data.length - 1].data[0].key;
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

        
        //数据来源请求方法
        GetData();
        async function GetData(){
            if(DataList.value.length)return false;
            const {data: res} = await axios.get(BasicUrl+'getapporsources&stype=2');
            if(res.success){
                DataList.value = res.data;
            }else{
                ElementPlus.ElMessage.error(res.msg || '数据来源获取失败');
            }
        };
        //数据类型改变
        function handlechange(item){
            item.data[0].id = '';
            GetData(item);
        }
        //数据排序改变
        function handleChangeType(item){
            let index = props.model.data.findIndex(function(current){
                return current.data[0].key == item.data[0].key;
            });
            props.model.data[index].data[0].value = '';
            
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
            handlechange,
            handleTabsEdit,
            DataList,
            handleChangeType,
            DomdragTab
        }
    }
}