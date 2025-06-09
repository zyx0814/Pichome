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
                    <el-form label-width="150px" label-suffix=":">
                        <el-form-item :label="Lang.text1">
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
                        <el-form-item :label="Lang.text2">
                            <el-radio-group v-model="item.data[0].type" @change="handleChangeType(item)">
                                <el-radio style="margin-bottom:6px;" :label="1" border>{{Lang.text4}}</el-radio>
                                <el-radio style="margin-bottom:6px;" :label="2" border>{{Lang.text5}}</el-radio>
                                <el-radio style="margin-bottom:6px;" :label="3" border>{{Lang.text6}}</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item :label="Lang.text6" v-if="item.data[0].type == 3">
                            <el-input v-model="item.data[0].value" :placeholder="Lang.text8" ></el-input>
                        </el-form-item>
                        <el-form-item :label="Lang.text9">
                            <div style="width: 100%;">
                                <el-input-number v-model="item.data[0].number" :min="0"></el-input-number>
                                <el-text size="small" tag="p" type="info">{{Lang.text10}}</el-text>
                            </div>
                        </el-form-item>
                        <el-form-item :label="Lang.text11">
                            <div style="width: 100%;">
                                <el-input style="width:50%;" v-model="item.data[0].link" placeholder="http://" clearable></el-input>
                                <el-text size="small" tag="p" type="info">{{Lang.text12}}</el-text>
                            </div>
                        </el-form-item>
                    </el-form>
                </el-tab-pane>
            </el-tabs>
        </div>
    `,
    setup(props,context){
        let Lang = {
            text1:__lang.data_source,
            text2:__lang.data_sorting,
            text4:__lang.Latest_recommendations,
            text5:__lang.Popular_recommendations,
            text6:__lang.Tag_recommendation,
            text8:__lang.Specify_label_tip,
            text9:__lang.data_quantity,
            text10:__lang.tip1,
            text11:__lang.tip3,
            text12:__lang.tip4,
        };
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
                    __lang.delete_tip,
                    __lang.prompt,
                    {
                      confirmButtonText: __lang.confirms,
                      cancelButtonText: __lang.cancel,
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
                                ElementPlus.ElMessage.error(res.msg || __lang.delete_unsuccess);
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
                ElementPlus.ElMessage.error(res.msg || __lang.get_data_fail);
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
            Lang,
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