const tab_message_system = {
    props:{
        formdata:{
            required:true,
            type: Array,
            default:[]
        },
        gid:{
            required:true,
            type: Number,
            default:0,
        },
        catdata:{
            required:true,
            type: Object,
            default:{}
        },
        ids:{
            required:true,
            type: Array,
            default:[],
        },
        manageperm:{
            required:false,
            type: Boolean,
            default:false,
        },
    },
    template:`
    <template v-for="(fitem,key) in formdata">
        <template v-if="fitem.type == 'input'">
            <el-form-item :label="fitem.labelname" label-width="auto" required>
                <div class="language-box">
                    <el-input 
                        v-model.trim="fitem.values" 
                        @input="DocumentPopover=true" 
                        :disabled="fitem.islocked"
                        @change="SaveData(fitem.flag,key,'system')"></el-input>
                    <template v-if="manageperm">
                        <template v-if="!fitem.islocked">
                            <language 
                                v-if="fitem.langkey&&fitem.langkey.tabname" 
                                :langkey="fitem.langkey.tabname"
                                @change="LanguageChange"
                                :mark="fitem.flag"></language>
                            <el-tooltip content="${__lang.locking}">
                                <el-button 
                                    class="locked-btn"
                                    plain 
                                    icon="Unlock" 
                                    type="danger" 
                                    @click="handlelocked(fitem.flag,key,1)"
                                    v-loading="fitem.lockedload"></el-button>
                            </el-tooltip>
                        </template>
                        <template v-else>
                            <el-tooltip content="${__lang.Unlock}">
                                <el-button 
                                    plain 
                                    icon="Lock" 
                                    type="info" 
                                    @click="handlelocked(fitem.flag,key,0)" 
                                    v-loading="fitem.lockedload"
                                    class="locked-btn"></el-button>
                            </el-tooltip>
                        </template>
                    </template>
                </div>
            </el-form-item>
        </template>
        <template v-else-if="fitem.type == 'classify'">
            <el-form-item :label="catdata.labelname">
                    <div class="language-box">
                        <el-tree-select
                            style="width: 100%;"
                            placeholder=" "
                            v-model="catdata.value"
                            multiple
                            :disabled="fitem.islocked"
                            lazy
                            check-strictly
                            :load="GetCatData" 
                            :props="{label:'catname',children: 'children',isLeaf:'isLeaf'}"
                            node-key="cid"
                            :default-expanded-keys="catdata.defaultOpenkeys"
                            show-checkbox
                            @change="classifychange">
                        </el-tree-select>
                        <template v-if="manageperm">
                            <template v-if="!fitem.islocked">
                                <el-tooltip content="${__lang.locking}">
                                    <el-button 
                                        class="locked-btn"
                                        plain 
                                        icon="Unlock" 
                                        type="danger" 
                                        @click="handlelocked(fitem.flag,key,1)"
                                        v-loading="fitem.lockedload"></el-button>
                                </el-tooltip>
                            </template>
                            <template v-else>
                                <el-tooltip content="${__lang.Unlock}">
                                    <el-button 
                                        plain 
                                        icon="Lock" 
                                        type="info" 
                                        @click="handlelocked(fitem.flag,key,0)" 
                                        v-loading="fitem.lockedload"
                                        class="locked-btn"></el-button>
                                </el-tooltip>
                            </template>
                        </template>
                    </div>
                
            </el-form-item>
        </template>
        <template v-else-if="fitem.type == 'bool'">
            <el-form-item :label="fitem.labelname">
                <div class="language-box">
                    <el-radio-group v-model="fitem.values" :disabled="fitem.islocked"  @change="SaveData(fitem.flag,key,'system')" style="flex:1;">
                        <el-radio :label="0" border>${__lang.no}</el-radio>
                        <el-radio :label="1" border>${__lang.yes}</el-radio>
                    </el-radio-group>
                    <template v-if="manageperm">
                        <template v-if="!fitem.islocked">
                            <el-tooltip content="${__lang.locking}">
                                <el-button 
                                    class="locked-btn"
                                    plain 
                                    icon="Unlock" 
                                    type="danger" 
                                    @click="handlelocked(fitem.flag,key,1)"
                                    v-loading="fitem.lockedload"></el-button>
                            </el-tooltip>
                        </template>
                        <template v-else>
                            <el-tooltip content="${__lang.Unlock}">
                                <el-button 
                                    plain 
                                    icon="Lock" 
                                    type="info" 
                                    @click="handlelocked(fitem.flag,key,0)" 
                                    v-loading="fitem.lockedload"
                                    class="locked-btn"></el-button>
                            </el-tooltip>
                        </template>
                    </template>
                </div>
            </el-form-item>
        </template>
        <template v-else-if="fitem.type == 'user'">
            <el-form-item :label="fitem.labelname">
                <template v-if="!fitem.islocked">
                    <div class="language-box">
                        <orguser-select
                            defaulttype="view_perm" 
                            @change="PermChange" 
                            :defaultheckeds="fitem.value" 
                            :defaultexpanded="fitem.expanded" 
                            :isunlimit="false"
                            :isdefault="false"
                            placement="top"
                            :defaultdata="fitem.data"></orguser-select>
                        
                        <el-tooltip content="${__lang.locking}">
                            <el-button 
                                class="locked-btn"
                                plain 
                                icon="Unlock" 
                                type="danger" 
                                @click="handlelocked(fitem.flag,key,1)"
                                v-loading="fitem.lockedload"
                                v-if="manageperm"></el-button>
                        </el-tooltip>
                    </div>
                </template>
                <template v-else>
                    <div class="language-box">
                        <orguser-select
                            defaulttype="view_perm" 
                            @change="PermChange" 
                            :defaultheckeds="fitem.value" 
                            :defaultexpanded="fitem.expanded" 
                            :isunlimit="false"
                            :disabled="true"
                            :isdefault="false"
                            placement="top"
                            :defaultdata="fitem.data"></orguser-select>
                        
                        <el-tooltip content="${__lang.Unlock}">
                            <el-button 
                                plain 
                                icon="Lock" 
                                type="info" 
                                @click="handlelocked(fitem.flag,key,0)" 
                                v-loading="fitem.lockedload"
                                class="locked-btn"
                                v-if="manageperm"></el-button>
                        </el-tooltip>
                    </div>
                    
                </template>

            </el-form-item>
        </template>
    </template>
    `,
    setup(props, context){
        async function GetCatData(Node,resolve){
            let self = this;
            let level = Node.level;
            var param = {
                gid:props.gid,
            }
            if (level === 0) {

            }else{
                param['pcid'] = Node.data.cid;
            }
            const {data: res} = await axios.post('index.php?mod=tab&op=tabviewinterface&do=getcat',param);
            let arr = [];
            for(var i in res.data){
                res.data[i]['cid'] = parseInt(res.data[i]['cid']);
                res.data[i]['pcatname'] = res.data[i]['catname'];
                res.data[i]['isRename'] = false;
                arr.push(res.data[i])
            }
            resolve(arr);
        }
        function PermChange(data){
            let uids = [];
            let orgids = [];
            for (let index = 0; index < data.length; index++) {
                const element = data[index];
                if(element.type == 'unlimit'){
                    continue;
                }
                if(element.type == 'user'){
                    uids.push(element.id);
                }else{
                    orgids.push(element.id);
                }
            }
            let curr = props.formdata.find(function(current){
                return current.type == 'user';
            });
            let index = props.formdata.findIndex(function(current){
                return current.type == 'user';
            });
            curr.uids = uids;
            curr.groups = orgids;
            SaveData(curr.flag,index,'system')
        }
        function classifychange(){
            context.emit('save','cat')
        }
        function SaveData(flag,index,type){
            context.emit('save',flag,index,type)
        }
        function LanguageChange(val,flag){
            let curr = props.formdata.find(current => {
                return current.flag == flag;
            });
            if(curr){
                curr.values = val;
            }
        }


        async function handlelocked(flag,key,islock){
            let curr = props.formdata[key];
            if(curr.lockedload)return false;
            let param = {
                gid:props.gid,
                tid:props.ids.join(','),
                flag:flag,
                islock:islock
            }
            curr.lockedload = true;
            const {data: res} = await axios.post('index.php?mod=tab&op=tabeditinterface&do=lockfiled',param);
            if(res.success){
                curr.islocked = islock?true:false;
            }else{
                ElementPlus.ElMessage({
                    type:'error',
                    message:res.msg || __lang.submit_error
                });
            }
            
            curr.lockedload = false;
        }
        return {
            GetCatData,
            SaveData,
            classifychange,
            PermChange,
            LanguageChange,
            handlelocked
        }
    }
};



const tab_message_tab = {
    props:{
        parentkey:{
            required:true,
            type: Number,
            default:0
        },
        formdata:{
            required:true,
            type: Object,
            default:{}
        },
        gid:{
            required:true,
            type: Number,
            default:0,
        },
        ids:{
            required:true,
            type: Array,
            default:[],
        },
        manageperm:{
            required:false,
            type: Boolean,
            default:false,
        },
    },
    template:`
    
        <div class="language-box">
            <div class="tag-box" style="flex:1;">
                <el-tag
                    v-for="folder in formdata.data"
                    :key="folder.tid"
                    disable-transitions
                    type="info"
                    :closable="!formdata.islocked"
                    @close="TabDelete(folder.tid,formdata.flag)">
                    <template v-if="folder.tabname">{{folder.tabname}}</template>
                    <template v-else>${__lang.Unnamed}</template>
                </el-tag>
                <template v-if="!formdata.islocked">
                    <el-popover
                        placement="top"
                        :disabled="formdata.islocked"
                        @before-enter="TabPopverShow(formdata.flag)"
                        @after-leave="TabPopverHide(formdata.flag)"
                        width="auto"
                        trigger="click">
                        <div class="popbox-body" style="padding: 5px 10px;width:850px;height: 480px; overflow:hidden;max-height: unset;">
                            <iframe frameborder="0" width="100%" height="100%"  :src="formdata.iframe"></iframe>
                        </div>
                        <template #reference>
                            <el-icon style="color: var(--el-color-info);vertical-align: middle;cursor:pointer;font-size: var(--el-font-size-large);"><Circle-Plus-Filled /></el-icon>
                        </template>
                    </el-popover>
                </template>
                
            </div>
            

            <template v-if="manageperm">
                <template v-if="!formdata.islocked">
                    <el-tooltip content="${__lang.locking}">
                        <el-button 
                            class="locked-btn"
                            plain 
                            icon="Unlock" 
                            type="danger" 
                            @click="handlelocked(formdata.flag,1)"
                            v-loading="formdata.lockedload"></el-button>
                    </el-tooltip>
                </template>
                <template v-else>
                    <el-tooltip content="${__lang.Unlock}">
                        <el-button 
                            plain 
                            icon="Lock" 
                            type="info" 
                            @click="handlelocked(formdata.flag,0)"
                            v-loading="formdata.lockedload"
                            class="locked-btn"></el-button>
                    </el-tooltip>
                </template>
            </template>
            
        </div>


    
    `,
    setup(props, context){
        async function TabDelete(id,flag){
            let curr = props.formdata;
            if(!curr || !curr.data.length)return false;
            let dindex = curr.data.findIndex(function(current){
                return parseInt(current.tid) == parseInt(id);
            });
            curr.data.splice(dindex,1);
            let findex = curr.data.findIndex(function(current){
                return parseInt(current) == parseInt(id);
            });
            curr.value.splice(findex,1);
            context.emit('save',flag,props.parentkey)
        }
        function TabPopverShow(flag){
            let curr = props.formdata;
            curr.iframe = 'index.php?mod=tab&op=OutPopover&isall=1&gid='+curr.extra.gid+'&ids='+curr.value.join(',')+'&callback=tabgroup_callback&cacheflag='+curr.flag+'__'+props.parentkey;
        }
        function TabPopverHide(flag,index){
            props.formdata.iframe = '';
        }
        async function handlelocked(flag,islock){
            let curr = props.formdata;
            if(curr.lockedload)return false;
            let param = {
                gid:props.gid,
                tid:props.ids.join(','),
                flag:flag,
                islock:islock
            }
            curr.lockedload = true;
            const {data: res} = await axios.post('index.php?mod=tab&op=tabeditinterface&do=lockfiled',param);
            if(res.success){
                curr.islocked = islock?true:false;
            }else{
                ElementPlus.ElMessage({
                    type:'error',
                    message:res.msg || __lang.submit_error
                });
            }
            
            curr.lockedload = false;
        }
        return {
            TabDelete,
            TabPopverShow,
            TabPopverHide,
            handlelocked
        }
    }
};



const tab_inputSelect = {
    props:{
        parentkey:{
            required:true,
            type: Number,
            default:0
        },
        formdata:{
            required:true,
            type: Object,
            default:{}
        },
        gid:{
            required:true,
            type: Number,
            default:0,
        },
        keys:{
            required:true,
            type: Number,
            default:0,
        },
        ids:{
            required:true,
            type: Array,
            default:[],
        },
        manageperm:{
            required:false,
            type: Boolean,
            default:false,
        },
    },
    template:`
    
        <div class="language-box">
            <div style="width:100%;position:relative;">
                <el-popover
                    placement="bottom"
                    :disabled="formdata.islocked"
                    width="100%"
                    :teleported="false"
                    popper-style="padding:0;left: 0;"
                    trigger="click">
                    <div style="padding: 12px;" 
                    :style="{ 'padding-bottom': formdata.options&&formdata.options.length>0?'0':'12px' }">
                        <el-input v-model="FilterText" placeholder="${__lang.search}" @input="handleFilterText" clearable></el-input>
                    </div>
                    <div class="el-select-dropdown is-multiple">
                        <ul 
                            class="el-scrollbar__view el-select-dropdown__list"
                            style="overflow-x: auto;max-height: 272px;"
                            v-infinite-scroll="getdata"
                            :infinite-scroll-disabled="!paramData.next || paramData.loading"
                            infinite-scroll-distance="50"
                            :infinite-scroll-immediate="false">
                            <template v-if="formdata.type == 'inputmultiselect'">
                                <li 
                                    v-for="item in formdata.options" 
                                    class="el-select-dropdown__option-item"
                                    @click="handleSelectmultiple(item)"
                                    :class="{'is-selected':formdata.value.indexOf(parseInt(item.id))>-1}">
                                    {{item.name}}
                                </li>
                            </template>
                            <template v-else>
                                <li 
                                    v-for="item in formdata.options" 
                                    class="el-select-dropdown__option-item"
                                    @click="handleSelect(item)"
                                    :class={'is-selected':parseInt(item.id)==parseInt(formdata.value)}>
                                    {{item.name}}
                                </li>
                            </template>
                            
                        </ul>
                    </div>
                    <div class="select-add" style="padding: 10px 20px 0px;border-top: var(--el-border);padding-bottom: 10px;width:100%">
                        <template v-if="!inputselectCreate.Adding">
                            <el-button size="small" @click="inputselectCreate.Adding=true">${__lang.Add_options}</el-button>
                        </template>
                        <template v-else>
                            <el-input v-model="inputselectCreate.name" />
                            <div style="padding-top: 10px;">
                                <el-button type="primary" size="small" @click="inputselectCreateConfirm">${__lang.confirms}</el-button>
                                <el-button size="small" @click="inputselectCreateclear">${__lang.cancel}</el-button>
                            </div>
                        </template>
                    </div>
                    <template #reference>
                        <div class="inputselect-box">
                            <div style="width: 100%;overflow: hidden;">
                                <template v-if="formdata.type == 'inputmultiselect'">
                                    <el-tag 
                                        v-for="tag in formdata.data" 
                                        :key="tag.id" 
                                        style="margin-right: 6px;max-width: 100%;" 
                                        closable 
                                        type="info"
                                        @close="handleClose(tag)">
                                        {{ tag.name }}
                                    </el-tag> 
                                </template>
                                <template v-else>
                                    <template v-if="formdata.data">
                                        {{formdata.data.name}}
                                    </template>
                                    
                                </template>
                            </div>
                        </div>
                    </template>
                </el-popover>
            </div>
            
            <template v-if="manageperm">
                <template v-if="!formdata.islocked">
                    <el-tooltip content="${__lang.locking}">
                        <el-button 
                            class="locked-btn"
                            plain 
                            icon="Unlock" 
                            type="danger" 
                            @click="handlelocked(1)"
                            v-loading="formdata.lockedload"></el-button>
                    </el-tooltip>
                </template>
                <template v-else>
                    <el-tooltip content="${__lang.Unlock}">
                        <el-button 
                            plain 
                            icon="Lock" 
                            type="info" 
                            @click="handlelocked(0)"
                            v-loading="formdata.lockedload"
                            class="locked-btn"></el-button>
                    </el-tooltip>
                </template>
            </template>
        </div>


    
    `,
    setup(props, context){
        let FilterText = ref(null);
        let paramData = reactive({
            page:2,
            prepage:10,
            loading:false,
            next:true
        });
        function handleSelect(data){
            if(props.formdata.value == data.id){
                props.formdata.value = 0;
                props.formdata.data = '';
            }else{
                props.formdata.value = data.id;
                props.formdata.data = data;
            }
            context.emit('save', props.formdata.flag,props.keys);
        };
        function handleSelectmultiple(data){
            if(props.formdata.value.indexOf(data.id) > -1){
                props.formdata.value.splice(props.formdata.value.indexOf(data.id),1);
                let index = props.formdata.data.findIndex(item => item.id == data.id);
                props.formdata.data.splice(index,1);
            }else{
                props.formdata.value.push(data.id);
                props.formdata.data.push(data);
            }
            context.emit('save', props.formdata.flag,props.keys);
        };
        function handleClose(data){
            props.formdata.value.splice(props.formdata.value.indexOf(data.id),1);
            let index = props.formdata.data.findIndex(item => item.id == data.id);
            props.formdata.data.splice(index,1);
            context.emit('save', props.formdata.flag,props.keys);
        };
        function debounce(fun, delay) {
            var time;
            return function(args) {
                var that = this;
                var _args = args;
                if (time) clearTimeout(time);
                time = setTimeout(function() {
                    fun.call(that, _args)
                }, delay)
            }
        };
        let handleFilterText = debounce(function(val){//输入
            paramData.page = 1;
            getdata();
        },800);
        function getdata(){//点击
            paramData.loading = true;
            let param = {
                gid:props.gid,
                filed:props.formdata.flag,
                keyword:FilterText.value,
                page:paramData.page,
                prepage:paramData.prepage,
            };
            if(props.formdata.value){
                if(props.formdata.type == 'inputmultiselect'){
                    param['defaultvals'] = props.formdata.value.join(',');
                }else{
                    param['defaultvals'] = props.formdata.value;
                }
                
            }
            axios.post('index.php?mod=tab&op=tabviewinterface&do=getfiledvals', param).then(function (res) {
                if(paramData.page > 1){
                    for (const key in res.data.data) {
                        const element = res.data.data[key];
                        props.formdata.options.push(element);
                    }
                }else{
                    props.formdata.options = res.data.data;
                }
                paramData.page += 1;
                
                nextTick(() => {
                    paramData.next = res.data.next;
                    paramData.loading = false;
                });
            }).catch(function (error) {
                console.log(error);
            });
        };

        let inputselectCreate = reactive({
            Adding:false,
            name:'',
            loading:false
        });
        async function inputselectCreateConfirm(){
            if(!inputselectCreate.name || inputselectCreate.loading)return false;
            let param = {
                gid:props.gid,
                flag:props.formdata.flag,
                val:inputselectCreate.name
            }
            inputselectCreate.loading = true;
            const {data: res} = await axios.post('index.php?mod=tab&op=tabeditinterface&do=addoptions',param);
            if(res.success){
                props.formdata.options.push({
                    name:inputselectCreate.name,
                    id:String(res.id)
                });
                if(props.formdata.type == 'inputmultiselect'){
                    props.formdata.value.push(String(res.id));
                    props.formdata.data.push({name:inputselectCreate.name,id:String(res.id)});
                }else{
                    props.formdata.value = String(res.id);
                    props.formdata.data = {name:inputselectCreate.name,id:String(res.id)};

                }
                
                inputselectCreate.name = '';
                context.emit('save', props.formdata.flag,props.keys);
                inputselectCreate.loading = false;
            }else{
                ElementPlus.ElMessage({
                    type:'error',
                    message:res.msg || __lang.submit_error
                });
            }
            
        }
        function inputselectCreateclear(){
            inputselectCreate.name = '';
            inputselectCreate.Adding=false;
        }
        async function handlelocked(islock){
            if(props.formdata.lockedload)return false;
            let param = {
                gid:props.gid,
                tid:props.ids.join(','),
                flag:props.formdata.flag,
                islock:islock
            }
            props.formdata.lockedload = true;
            const {data: res} = await axios.post('index.php?mod=tab&op=tabeditinterface&do=lockfiled',param);
            if(res.success){
                props.formdata.islocked = islock?true:false;
            }else{
                ElementPlus.ElMessage({
                    type:'error',
                    message:res.msg || __lang.submit_error
                });
            }
            
            props.formdata.lockedload = false;
        }
        return {
            FilterText,
            paramData,
            inputselectCreate,
            inputselectCreateConfirm,
            inputselectCreateclear,
            handleSelect,
            handleSelectmultiple,
            handleClose,
            handleFilterText,
            getdata,
            handlelocked
        }
    }
};


const tab_input = {
    props:{
        parentkey:{
            required:true,
            type: Number,
            default:0
        },
        formdata:{
            required:true,
            type: Object,
            default:{}
        },
        gid:{
            required:true,
            type: Number,
            default:0,
        },
        ids:{
            required:true,
            type: Array,
            default:[],
        },
    },
    template:`
        <template v-if="parseInt(formdata.length)">
            <el-autocomplete
                style="width:100%"
                v-model="formdata.value"
                :disabled="formdata.islocked"
                :debounce="600"
                @change="SaveData" 
                :fetch-suggestions="InputSearchAsync"
                :maxlength="formdata.length">
                <template #loading>
                    <svg class="circular" viewBox="0 0 50 50">
                        <circle class="path" cx="25" cy="25" r="20" fill="none" />
                    </svg>
                </template>
            </el-autocomplete>
        </template>
        <template v-else>
            <el-autocomplete
                style="width:100%"
                v-model="formdata.value"
                :disabled="formdata.islocked"
                :debounce="600"
                @change="SaveData" 
                :fetch-suggestions="InputSearchAsync">
                <template #loading>
                    <svg class="circular" viewBox="0 0 50 50">
                        <circle class="path" cx="25" cy="25" r="20" fill="none" />
                    </svg>
                </template>
            </el-autocomplete>
        </template>


    
    `,
    setup(props, context){
        async function InputSearchAsync(string,cb){
            let param = {
                gid:props.gid,
                tid:props.ids.join(','),
                flag:props.formdata.flag,
                keyword:props.formdata.value
            }
            const {data: res} = await axios.post('index.php?mod=tab&op=tabeditinterface&do=getInputVals',param);
            if(res.success){
                let results = [];
                if(res.data && res.data.length){
                    for (let index = 0; index < res.data.length; index++) {
                        const element = res.data[index];
                        results.push({
                            value:element.svalue
                        });
                    }
                }
                
                cb(results)
            }else{
                cb([])
            }
            
        }
        function SaveData(){
            context.emit('save',props.formdata.flag,props.parentkey);
        }
        return {
            InputSearchAsync,
            SaveData
        }
    }
};
const tab_message_basic = {
    props:{
        formdata:{
            required:true,
            type: Array,
            default:[]
        },
        gid:{
            required:true,
            type: Number,
            default:0,
        },
        ids:{
            required:true,
            type: Array,
            default:[],
        },
        manageperm:{
            required:false,
            type: Boolean,
            default:false,
        },
    },
    template:`
    <template v-for="(fitem,key) in formdata">
        <div v-if="fitem.fileds && fitem.fileds.length" :label="fitem.catname" :name="key">
            <div style="height: 32px;line-height: 32px;margin-bottom: 6px;">
                <el-text tag="b">{{fitem.catname}}</el-text>
            </div>
            <template v-for="(item,tkey) in fitem.fileds">
                <div :class="{'el-form--label-top':!parseInt(item.style)}"> 
                    <el-form-item :label="item.labelname" :label-width="!parseInt(item.style)?'auto':''">
                        <template v-if="item.type == 'input'">
                                <div class="language-box">
                                    <tab_input 
                                        :formdata="item" 
                                        :parentkey="key"
                                        :gid="gid"
                                        :ids="ids"
                                        @save="SaveData"></tab_input>
                                    <template v-if="manageperm">
                                        <language 
                                            v-if="item.langkey" 
                                            :langkey="item.langkey"
                                            @change="LanguageChange"
                                            :mark="{flag:item.flag,key:key}"></language>
                                            
                                        <template v-if="!item.islocked">
                                            <el-tooltip content="${__lang.locking}">
                                                <el-button 
                                                    class="locked-btn"
                                                    plain 
                                                    icon="Unlock" 
                                                    type="danger" 
                                                    @click="handlelocked(item.flag,key,tkey,1)"
                                                    v-loading="fitem.lockedload"></el-button>
                                            </el-tooltip>
                                        </template>
                                        <template v-else>
                                            <el-tooltip content="${__lang.Unlock}">
                                                <el-button 
                                                    plain 
                                                    icon="Lock" 
                                                    type="info" 
                                                    @click="handlelocked(item.flag,key,tkey,0)"
                                                    v-loading="fitem.lockedload"
                                                    class="locked-btn"></el-button>
                                            </el-tooltip>
                                        </template>
                                    </template>
                                </div>
                            
                        </template>
                        <template v-else-if="item.type == 'textarea'">
                                <div class="language-box">
                                    <template v-if="parseInt(item.length)">
                                        <el-input 
                                            v-model="item.value"
                                            @change="SaveData(item.flag,key)" 
                                            type="textarea" 
                                            resize="none" 
                                            :disabled="item.islocked"
                                            :autosize="{minRows:2 }" 
                                            :maxlength="item.length"></el-input>
                                    </template>
                                    <template v-else>
                                        <el-input 
                                            v-model="item.value"
                                            @change="SaveData(item.flag,key)" 
                                            type="textarea" 
                                            resize="none" 
                                            :disabled="item.islocked"
                                            :autosize="{minRows:2 }" ></el-input>
                                    </template>
                                    <template  v-if="manageperm">
                                        <template v-if="!item.islocked">
                                            <language 
                                                v-if="item.langkey" 
                                                :langkey="item.langkey"
                                                @change="LanguageChange"
                                                :mark="{flag:item.flag,key:key}"></language>
                                            <el-tooltip content="${__lang.locking}">
                                                <el-button 
                                                    class="locked-btn"
                                                    plain 
                                                    icon="Unlock" 
                                                    type="danger" 
                                                    @click="handlelocked(item.flag,key,tkey,1)"
                                                    v-loading="fitem.lockedload"></el-button>
                                            </el-tooltip>
                                        </template>
                                        <template v-else>
                                            <el-tooltip content="${__lang.Unlock}">
                                                <el-button 
                                                    plain 
                                                    icon="Lock" 
                                                    type="info" 
                                                    @click="handlelocked(item.flag,key,tkey,0)"
                                                    v-loading="fitem.lockedload"
                                                    class="locked-btn"></el-button>
                                            </el-tooltip>
                                        </template>
                                    </template>
                                    
                                </div>
                        </template>
                        <template v-else-if="item.type == 'inputselect' || item.type == 'inputmultiselect'">
                            <tab_inputSelect
                                :formdata="item"
                                :parentkey="key"
                                :gid="gid"
                                :keys="key"
                                :ids="ids"
                                :manageperm="manageperm"
                                @save="SaveData"></tab_inputSelect>
                        </template>
                        <template v-else-if="item.type == 'tabgroup'">
                            <message_tab 
                                :formdata="item"
                                :parentkey="key"
                                :gid="gid"
                                :ids="ids"
                                :manageperm="manageperm"
                                @save="SaveData"></message_tab>
                        </template>
                        <template v-else-if="item.type == 'select'">
                            <div class="language-box">
                                <el-select
                                    :class="{suffix:item.value}"
                                    style="width: 100%;"
                                    v-model="item.value"
                                    clearable
                                    suffix-icon=""
                                    placeholder=" "
                                    :disabled="item.islocked"
                                    @change="SaveData(item.flag,key)">
                                    <el-option
                                        v-for="gitem in item.options"
                                        :key="gitem"
                                        :label="gitem"
                                        :value="gitem">
                                    </el-option>
                                </el-select>
                                <template v-if="manageperm">
                                    <template v-if="!item.islocked">
                                        <language 
                                            v-if="item.langkey" 
                                            :langkey="item.langkey"
                                            @change="LanguageChange"
                                            :mark="{flag:item.flag,key:key}"></language>
                                        <el-tooltip content="${__lang.locking}">
                                            <el-button 
                                                class="locked-btn"
                                                plain 
                                                icon="Unlock" 
                                                type="danger" 
                                                @click="handlelocked(item.flag,key,tkey,1)"
                                                v-if="manageperm"
                                                v-loading="fitem.lockedload"></el-button>
                                        </el-tooltip>
                                    </template>
                                    <template v-else>
                                        <el-tooltip content="${__lang.Unlock}">
                                            <el-button 
                                                plain 
                                                icon="Lock" 
                                                type="info" 
                                                @click="handlelocked(item.flag,key,tkey,0)"
                                                v-if="manageperm"
                                                v-loading="fitem.lockedload"
                                                class="locked-btn"></el-button>
                                        </el-tooltip>
                                    </template>
                                </template>
                            </div>
                                
                            
                        </template>
                        <template v-else-if="item.type == 'multiselect'" >
                            <div class="language-box">
                                <el-select
                                    style="width: 100%;"
                                    v-model="item.value"
                                    suffix-icon=""
                                    placeholder=" "
                                    :disabled="item.islocked"
                                    @change="SaveData(item.flag,key)"
                                    multiple>
                                    <el-option
                                        v-for="gitem in item.options"
                                        :key="gitem"
                                        :label="gitem"
                                        :value="gitem">
                                    </el-option>
                                </el-select>
                                <template v-if="manageperm">
                                    <template v-if="!item.islocked">
                                        <language 
                                            v-if="item.langkey" 
                                            :langkey="item.langkey"
                                            @change="LanguageChange"
                                            :mark="{flag:item.flag,key:key}"></language>
                                        <el-tooltip content="${__lang.locking}">
                                            <el-button 
                                                class="locked-btn"
                                                plain 
                                                icon="Unlock" 
                                                type="danger" 
                                                @click="handlelocked(item.flag,key,tkey,1)"
                                                v-loading="fitem.lockedload"></el-button>
                                        </el-tooltip>
                                    </template>
                                    <template v-else>
                                        <el-tooltip content="${__lang.Unlock}">
                                            <el-button 
                                                plain 
                                                icon="Lock" 
                                                type="info" 
                                                @click="handlelocked(item.flag,key,tkey,0)"
                                                v-loading="fitem.lockedload"
                                                class="locked-btn"></el-button>
                                        </el-tooltip>
                                    </template>
                                </template>
                                    
                            </div>
                        </template>    
                        <template v-else-if="item.type == 'time'">
                            <div class="language-box">
                                <el-date-picker 
                                    style="width: 100%;"
                                    v-model="item.value" 
                                    :type="item.extra.type?item.extra.type:'date'" 
                                    @change="SaveData(item.flag,key)" 
                                    :start="item.extra.mindate" 
                                    :end="item.extra.mindate" 
                                    :disabled="item.islocked"
                                    :disabled-date="RightTimeDisabledDate"
                                    @visible-change="RightCommonVisibleChange"
                                    :value-format="item.extra.dateformat?item.extra.dateformat:'YYYY.MM.DD'"></el-date-picker>
                                <template v-if="manageperm">
                                    <template v-if="!item.islocked">
                                        <el-tooltip content="${__lang.locking}">
                                            <el-button 
                                                class="locked-btn"
                                                plain 
                                                icon="Unlock" 
                                                type="danger" 
                                                @click="handlelocked(item.flag,key,tkey,1)"
                                                v-loading="fitem.lockedload"></el-button>
                                        </el-tooltip>
                                    </template>
                                    <template v-else>
                                        <el-tooltip content="${__lang.Unlock}">
                                            <el-button 
                                                plain 
                                                icon="Lock" 
                                                type="info" 
                                                @click="handlelocked(item.flag,key,tkey,0)"
                                                v-loading="fitem.lockedload"
                                                class="locked-btn"></el-button>
                                        </el-tooltip>
                                    </template>
                                </template>
                                
                            </div>
                        </template>
                        <template v-else-if="item.type == 'timerange'">
                                <div class="language-box">
                                    <div style="flex:1;"> 
                                        <el-tag
                                            v-for="(titem,ckey) in item.value"
                                            :closable="!item.islocked"
                                            disable-transitions
                                            type="info"
                                            style="margin: 2px 6px 2px 0;"
                                            @close="TimeRangeClose(item.flag,key,tkey,ckey)">
                                            {{ titem }}
                                        </el-tag>
                                        <div class="timerangeadd" v-if="!item.islocked">
                                            <el-button type="info" icon="plus" size="small"></el-button>
                                            <el-date-picker
                                                v-model="item.timevalue"
                                                :type="item.extra.type?item.extra.type:'daterange'" 
                                                @visible-change="RightCommonVisibleChange"
                                                :value-format="item.extra.dateformat?item.extra.dateformat:'YYYY.MM.DD'"
                                                @change="TimeRangeChange(item.flag,key,tkey)">
                                            </el-date-picker>
                                        </div>
                                    </div>
                                    <template v-if="manageperm">
                                        <template v-if="!item.islocked">
                                            <el-tooltip content="${__lang.locking}">
                                                <el-button 
                                                    class="locked-btn"
                                                    plain 
                                                    icon="Unlock" 
                                                    type="danger" 
                                                    @click="handlelocked(item.flag,key,tkey,1)"
                                                    v-loading="fitem.lockedload"></el-button>
                                            </el-tooltip>
                                        </template>
                                        <template v-else>
                                            <el-tooltip content="${__lang.Unlock}">
                                                <el-button 
                                                    plain 
                                                    icon="Lock" 
                                                    type="info" 
                                                    @click="handlelocked(item.flag,key,tkey,0)"
                                                    
                                                    v-loading="fitem.lockedload"
                                                    class="locked-btn"></el-button>
                                            </el-tooltip>
                                        </template>
                                    </template>
                                    
                                </div>
                            
                        </template>
                        <template v-else-if="item.type == 'link'">
                            <div class="language-box">
                                <el-input 
                                    v-model.trim="item.value" 
                                    :disabled="item.islocked"
                                    @change="SaveData(item.flag,key)"></el-input>
                                <template v-if="manageperm">
                                    <template v-if="!item.islocked">
                                        <el-tooltip content="${__lang.locking}">
                                            <el-button 
                                                class="locked-btn"
                                                plain 
                                                icon="Unlock" 
                                                type="danger" 
                                                @click="handlelocked(item.flag,key,tkey,1)"
                                                v-loading="fitem.lockedload"></el-button>
                                        </el-tooltip>
                                    </template>
                                    <template v-else>
                                        <el-tooltip content="${__lang.Unlock}">
                                            <el-button 
                                                plain 
                                                icon="Lock" 
                                                type="info" 
                                                @click="handlelocked(item.flag,key,tkey,0)"
                                                v-loading="fitem.lockedload"
                                                class="locked-btn"></el-button>
                                        </el-tooltip>
                                    </template>
                                </template>
                                
                            </div>
                        </template>
                        <template v-else-if="item.type == 'bool'">
                            <div class="language-box">
                                <el-radio-group 
                                    style="flex: 1;"
                                    :disabled="item.islocked"
                                    v-model="item.value"  
                                    @change="SaveData(item.flag,key)">
                                    <el-radio label="0" border>${__lang.no}</el-radio>
                                    <el-radio label="1" border>${__lang.yes}</el-radio>
                                </el-radio-group>
                                <template v-if="manageperm">
                                    <template v-if="!item.islocked">
                                        <el-tooltip content="${__lang.locking}">
                                            <el-button 
                                                class="locked-btn"
                                                plain 
                                                icon="Unlock" 
                                                type="danger" 
                                                @click="handlelocked(item.flag,key,tkey,1)"
                                                v-loading="fitem.lockedload"></el-button>
                                        </el-tooltip>
                                    </template>
                                    <template v-else>
                                        <el-tooltip content="${__lang.Unlock}">
                                            <el-button 
                                                plain 
                                                icon="Lock" 
                                                type="info" 
                                                @click="handlelocked(item.flag,key,tkey,0)"
                                                v-loading="fitem.lockedload"
                                                class="locked-btn"></el-button>
                                        </el-tooltip>
                                    </template>
                                </template>
                                
                            </div>
                        </template>
                        

                        
                        <template v-else-if="item.type == 'fulltext'">
                            <div class="language-box">
                                <template v-if="!item.islocked">
                                    <fulltext 
                                        v-if="item.isshow"
                                        :flag="item.flag"
                                        :fkey="key"
                                        :gid="gid"
                                        @submit="SaveData(item.flag,key)"  
                                        @change="FulltextChange" 
                                        :model="item.value"></fulltext>
                                </template>
                                <template v-else>
                                    <div class="w-e-text-container el-text"  style="flex:1;"><div data-slate-editor v-html="item.value"></div></div>
                                </template>
                                <template v-if="manageperm">
                                    <template v-if="!item.islocked">
                                        <language 
                                            v-if="item.langkey" 
                                            :langkey="item.langkey"
                                            @change="LanguageChange"
                                            :id="gid"
                                            :mark="{flag:item.flag,key:key}"></language>
                                        <el-tooltip content="${__lang.locking}">
                                            <el-button 
                                                class="locked-btn"
                                                plain 
                                                icon="Unlock" 
                                                type="danger" 
                                                @click="handlelocked(item.flag,key,tkey,1)"
                                                v-loading="fitem.lockedload"></el-button>
                                        </el-tooltip>
                                    </template>
                                    <template v-else>
                                        <div></div>
                                        <el-tooltip content="${__lang.Unlock}">
                                            <el-button 
                                                plain 
                                                icon="Lock" 
                                                type="info" 
                                                @click="handlelocked(item.flag,key,tkey,0)"
                                                v-loading="fitem.lockedload"
                                                class="locked-btn"></el-button>
                                        </el-tooltip>
                                    </template>
                                </template>

                                
                            </div>
                            
                        </template>


                        
                    </el-form-item>
                </div>
            </template>	
        
        </div>

    </template>
    `,
    setup(props, context){
        function FulltextChange(data){
            let curr = props.formdata[data.key].fileds.find(function(current){
                return current.flag == data.flag;
            });
            if(!curr)return false;
            curr.value = data.value
        }
        function TimeRangeChange(flag,key,tkey){
            let timevalue = props.formdata[key].fileds[tkey].timevalue;
            if(!timevalue)return false;
            let value = props.formdata[key].fileds[tkey].value;
            let str = timevalue[0]+'-'+timevalue[1];
            if(value.indexOf(str)>-1){
                ElementPlus.ElMessage({
                    type:'error',
                    message:__lang.Time_repetition
                });
                return false;
            }
            props.formdata[key].fileds[tkey].value.push(str);
            props.formdata[key].fileds[tkey].timevalue = [];
            SaveData(flag,key);
        }
        function TimeRangeClose(flag,key,tkey,ckey){
            props.formdata[key].fileds[tkey].value.splice(ckey,1);
            SaveData(flag,key);
        }
        function SaveData(flag,index){
            context.emit('save',flag,index);
        }
        function LanguageChange(val,data){
            let curr = props.formdata[data.key].fileds.find(current => {
                return current.flag == data.flag;
            });
            if(curr){
                if(curr.type == 'fulltext'){
                    curr.isshow = false;
                }
                curr.value = val;
                curr.values = val;
                nextTick(() => {
                    curr.isshow = true;
                });
            }
        }

        async function handlelocked(flag,key,ckey,islock){
            let curr = props.formdata[key].fileds[ckey];
            if(curr.lockedload)return false;
            let param = {
                gid:props.gid,
                tid:props.ids.join(','),
                flag:flag,
                islock:islock
            }
            curr.lockedload = true;
            const {data: res} = await axios.post('index.php?mod=tab&op=tabeditinterface&do=lockfiled',param);
            if(res.success){
                curr.islocked = islock?true:false;
            }else{
                ElementPlus.ElMessage({
                    type:'error',
                    message:res.msg || __lang.submit_error
                });
            }
            
            curr.lockedload = false;
        }
        
        return {
            SaveData,
            TimeRangeClose,
            FulltextChange,
            TimeRangeChange,
            LanguageChange,
            handlelocked,
        }
    },
    components: {
        message_tab:tab_message_tab,
        tab_inputSelect:tab_inputSelect,
        tab_input:tab_input
    },
};





const tab_message = {
    props:{
        ids:{
            required:true,
            type: Array,
            default:[],
        },
        icotype:{
            required:true,
            type: Number,
            default:1,
        },
        gid:{
            required:true,
            type: Number,
            default:0,
        },
        allsubmit:{
            required:false,
            type: Boolean,
            default:false,
        },
        formhash:{
            required:false,
            type: String,
            default:'',
        },
        create:{
            required:false,
            type: Boolean,
            default:false,
        },
        ispc:{
            required:false,
            type: Boolean,
            default:true,
        }
    },
    template:`
        <div class="tab-message">
            <template v-if="ids.length==1 || create">
                <div 
                    class="right-upload" 
                    :class="'type_'+icotype">
                    <div class="el-upload-text">
                        <template v-if="FormDataVal.imageUrl[0]">
                            <el-image class="logoimg" :src="FormDataVal.imageUrl[0]" fit="cover" >
                                <template #error><div class="el-image__placeholder"></div></template>
                            </el-image>
                        </template>
                        <template v-else>
                            <div class="el-image logoimg" style="background-color: var(--el-fill-color);"></div>
                        </template>
                    </div>
                    <div class="upload-btn" :style="{opacity:!ispc?1:''}">
                        <el-dropdown :teleported="false" @command="handleCommandUpload"   v-if="!iconlock || create">
                            <el-button plain type="primary" icon="Upload" size="small">${__lang.web_upload}</el-button>
                            <template #dropdown>
                                <el-dropdown-menu>
                                    <el-dropdown-item command="">
                                        <el-upload
                                            style="width: 100%;"
                                            class="upload-image"
                                            action=""
                                            name="iconnew"
                                            accept="image/png, image/jpeg,image/jpg, image/bmp"
                                            :auto-upload="false"
                                            :on-change="ImgUploadSuccess"
                                            :show-file-list="false">
                                            <template #trigger>
                                                <div>
                                                    <el-icon><Monitor /></el-icon>${__lang.web_upload}
                                                </div>
                                            </template>
                                        </el-upload>
                                        
                                    </el-dropdown-item>
                                    <el-dropdown-item command="address">
                                        <el-icon><Location-Information /></el-icon>${__lang.Image_address}
                                    </el-dropdown-item>
                                </el-dropdown-menu>
                            </template>
                        </el-dropdown>
                        <el-button plain type="success" icon="RefreshLeft" size="small" style="margin-left: 12px;"  v-if="!iconlock" @click="handleRecovery">${__lang.reset_cover}</el-button>

                        <template v-if="manageperm">
                            <template v-if="!iconlock">
                                <el-button plain icon="Unlock" type="danger" size="small" @click="handlelocked('icon',1)">${__lang.locking}</el-button>
                            </template>
                            <template v-else>
                                <el-button plain icon="Lock" type="info" size="small" @click="handlelocked('icon',0)">${__lang.Unlock}</el-button>
                            </template>
                        </template>
                         


                    </div>
                </div>

            </template>
            <template v-else>
                <div class="right-upload" :class="'type_'+icotype">
                    <div v-for="src in FormDataVal.imageUrl" class="el-upload-text notneed">
                        <el-image class="logoimg" :src="src" fit="cover" >
                            <template #error><div class="el-image__placeholder"></div></template>
                        </el-image>
                    </div>
                </div>
            </template>
            <div v-if="ids.length>1" style="text-align: center;margin-bottom: 8px;">
                <div style="font-size: var(--el-font-size-base);color: var(--el-text-color-regular);margin-bottom: 8px;">
                    {{ids.length}} ${__lang.card}
                </div>
            </div>
            <el-form
                label-position="top"
                label-width="32%"
                label-suffix=":"
                :model="formData">
                <message_system 
                    v-if="!FormDataVal.loading"
                    :formdata="FormDataVal.SystemData" 
                    :gid="gid"
                    :catdata="FormDataVal.Catdata"
                    @save="SaveData"
                    :manageperm="manageperm"
                    :ids="ids"></message_system>
                <message_basic 
                    v-if="FormDataVal.formData.length"
                    :gid="gid"
                    :manageperm="manageperm"
                    :formdata="FormDataVal.formData" 
                    @save="SaveData"
                    :ids="ids"></message_basic>
            </el-form>
        </div>
        

        <el-dialog
            v-model="MainfilterBox.visible"
            title="${__lang.Crop_image}"
            :close-on-click-modal="false"
            :close-on-press-escape="false"
            append-to-body
            width="700px">
            <div style="height: 500px;">
                <vue-cropper 
                    v-if="MainfilterBox.visible"
                    ref="vuecropper"
                    output-type="png"
                    :center-box="true"
                    :img="MainfilterBox.img"
                    :auto-crop="true" 
                    :auto-crop-width="500" 
                    :auto-crop-height="500"
                    @realTime="handleCrop"
                    @img-load="cropperImageLoad"
                ></vue-cropper>
            </div>
            <template #footer>
                <span>
                    <el-button @click="handleclose">${__lang.cancel}</el-button>
                    <el-button type="primary" @click="MainAddUploadPrimary">${__lang.confirms}</el-button>
                </span>
            </template>
        </el-dialog>

    `,
    setup(props, context){
        let MainfilterBox = reactive({
            visible:false,
            img:'',
            imgname:'',
            type:null,//哪里的数据需要截取,
            message:false
        });
        let iconnew = '';
        let ficonnew = '';
        let manageperm = ref(false);
        let iconlock = ref(true);
        let lockedload = ref(false);
        let FormDataVal = reactive({
            loading:true,
            imageUrl:[],
            name:'',
            number:'',
            formData:[],
            SystemData:[],
            CatDefaultVal:[],
            Catdata:[],
            folodernum:0,
            PostParam:'',
        });

        async function GetData(){
            FormDataVal.loading = true;
            FormDataVal.formData = [];
            var CancelToken = axios.CancelToken;
            if(FormDataVal.PostParam){
                FormDataVal.PostParam();
            }
            axios.post('index.php?mod=tab&op=tabviewinterface&do=gettabdetail',{
                gid:props.gid,
                tid:props.ids.join(','),
            },{
                cancelToken: new CancelToken(function executor(c) {
                    FormDataVal.PostParam = c;
                })
            }).then(function ({data: res}) {
                const data = res.data;
                manageperm.value = data.manageperm;
                iconlock.value = data.iconlock;
                FormDataVal.number = data.number;
                if(data.icon){
                    if(props.ids.length>1){
                        let icon = [];
                        for (let index = 0; index < data.icon.length; index++) {
                            const element = data.icon[index];
                            if(element.includes('?')){
                                icon.push(element+'&'+Math.random()*10)
                            }else{
                                icon.push(element+'?'+Math.random()*10)
                            }
                            
                        }
                        FormDataVal.imageUrl = icon;
                    }else{
                        
                        if(data.icon.includes('?')){
                            FormDataVal.imageUrl = [data.icon+'&'+Math.random()*10];
                        }else{
                            FormDataVal.imageUrl = [data.icon+'?'+Math.random()*10];
                        }
                    }
                    
                }else{
                    FormDataVal.imageUrl = [];
                }

                FormDataVal.SystemData = [];
                if(data.sysforms){
                    for (const key in data.sysforms){
                        const element = data.sysforms[key];
                        element['flag'] = key;
                        element['lockedload'] = false;
                        if(element.type == 'bool'){
                            element['values'] = parseFloat(element.values)?1:0;
                        
                        }else if(element.type == 'user'){
                            let groups = [],uids = [],fdata = [],checked = [],expanded = [];
                            if(element.values && element.values.group){
                                for (let index = 0; index < element.values.group.length; index++) {
                                    const felement = element.values.group[index];
                                    fdata.push({
                                        id:parseFloat(felement.orgid),
                                        text:felement.text,
                                        type: 'organization'
                                    });
                                    groups.push(parseFloat(felement.orgid));
                                    checked.push(parseFloat(felement.orgid));
                                    expanded.push(parseFloat(felement.orgid));
                                }
                            }
                            if(element.values && element.values.user){
                                for (let index = 0; index < element.values.user.length; index++) {
                                    const felement = element.values.user[index];
                                    fdata.push({
                                        id:parseFloat(felement.uid),
                                        text:felement.text,
                                        type: 'user'
                                    });
                                    uids.push(parseFloat(felement.uid))
                                    checked.push(parseFloat(felement.uid));
                                    expanded.push(parseFloat(felement.uid));
                                }
                            }
                            element['groups'] = groups;
                            element['uids'] = uids;
                            element['data'] = fdata;
                            element['checked'] = checked;
                            element['expanded'] = expanded;
                        }
                        FormDataVal.SystemData.push(element)
                    }
                }
                for(var i in data.forms){
                    for(var x in data.forms[i].fileds){
                        let curr = data.forms[i].fileds[x];
                        curr['lockedload'] = false;
                        switch (curr.type){
                            case 'input':
                            case 'textarea':
                            case 'select':
                            case 'link':
                            case 'bool':
                                if(curr.values){
                                    curr['value'] = curr.values;
                                }else{
                                    curr['value'] = '';
                                }
                                
                            break;
                            case 'fulltext':
                                curr['isshow'] = true;
                                if(curr.values){
                                    curr['value'] = curr.values;
                                }else{
                                    curr['value'] = '';
                                }
                                
                            break;
                            case 'time':
                                if(curr.values){
                                    curr['value'] = curr.values;
                                }else{
                                    curr['value'] = '';
                                }
                                
                                var dataVal = convertDateFormat(curr.extra.dateformat,curr.type);
                                curr.extra.dateformat = dataVal.dateFormat;
                                curr.extra['type'] = dataVal.type;
                            break;
                            case 'timerange':
                                if(curr.values){
                                    curr['value'] = curr.values;
                                }else{
                                    curr['value'] = [];
                                }
                                curr['timevalue'] = [];
                                var dataVal = convertDateFormat(curr.extra.dateformat,curr.type);
                                curr.extra.dateformat = dataVal.dateFormat;
                                curr.extra['type'] = dataVal.type;
                            break;
                            case 'inputselect':
                                curr['value'] = 0;
                                curr['data'] = {};
                                if(curr.values && curr.values.length){
                                    curr['value'] = parseInt(curr.values[0]);
                                    let options = [];
                                    for (const key in curr.options) {
                                        const element = curr.options[key];
                                        element['id'] = parseInt(element['id']);
                                        options.push(element);
                                    }
                                    curr.options = options;
                                    let fcurr = curr.options.find(function(current){
                                        return parseInt(current.id) == curr.value;
                                    });
                                    if(fcurr){
                                        curr['data'] = fcurr;
                                    }
                                }
                            break;
                            case 'multiselect':
                                if(props.ids.length > 1){
                                    curr['value'] = curr.values || [];
                                }else{
                                    if(curr.values){
                                        curr['value'] = curr.values.split(',');
                                    }else{
                                        curr['value'] = [];
                                    }
                                }
                            break;
                            case 'inputmultiselect':
                                curr['value'] = [];
                                curr['data'] = [];
                                if(curr.values && curr.values.length){
                                    for (let index = 0; index < curr.values.length; index++) {
                                        const element = curr.values[index];
                                        curr['value'].push(parseInt(element));
                                    }
                                    if(curr.options && curr.options.length){
                                        let options = [];
                                        for (const key in curr.options) {
                                            const element = curr.options[key];
                                            element['id'] = parseInt(element['id']);
                                            options.push(element);
                                        }
                                        curr.options = options;
                                        curr.options.forEach(function(option){
                                            if(curr['value'].indexOf(parseInt(option.id)) > -1){
                                                curr['data'].push(option);
                                            }
                                        });
                                    }
                                }
                            break;
                            case 'tabgroup':
                                curr['iframe'] = '';
                                curr['data'] = curr.values || [];
                                curr['value'] = [];
                                if(curr.values && curr.values.length){
                                    let val = [];
                                    for (let index = 0; index < curr.values.length; index++) {
                                        const element = curr.values[index];
                                        val.push(element.tid);
                                    }
                                    curr['value'] = val;
                                }
                            break;
                        }
                    }
                    FormDataVal.formData.push(data.forms[i])
                }
                
                var str = {
                    type:'cat',
                    flag:'cat',
                    value:[],
                    style:1,
                    defaultOpenkeys:[],
                    labelname:__lang.classify
                }
                if(data.catdata){
                    let value = [];
                    let defaultOpenkeys = [];
                    for(let i in data.catdata){
                        if(manageperm.value){
                            var pathkey = data.catdata[i].pathkey;
                            var num = pathkey.match(/\d+(\.\d+)?/g); 
                            num.pop();
                            if(num.length){
                                for(let i in num){
                                    let id = parseInt(num[i]);
                                    if(defaultOpenkeys.indexOf(id)<0){
                                        defaultOpenkeys.push(id)
                                    }
                                }
                            }
                        }
                        value.push(parseInt(data.catdata[i].cid));
                    }
                    str.value = value;
                    FormDataVal.CatDefaultVal = JSON.parse(JSON.stringify(value));
                    str.defaultOpenkeys = defaultOpenkeys;
                }
                FormDataVal.Catdata = str;
                FormDataVal.loading = false;
                FormDataVal.PostParam = '';
            }).catch(function (error) {
                console.log(error);
            });
        }

        const DATE_FORMAT_YYYY_MM_DD = 'YYYY.MM.DD';
        const DATE_FORMAT_YYYY_MM_DD_HH_MM_SS = 'YYYY.MM.DD HH:mm:ss';
        const DATE_FORMAT_YYYY_SLASH_MM_SLASH_DD = 'YYYY/MM/DD';
        const DATE_FORMAT_YYYY_SLASH_MM_SLASH_DD_HH_MM_SS = 'YYYY/MM/DD HH:mm:ss';

        function convertDateFormat(dateStringFormat, inputType) {
            let dateFormat, type;

            if (inputType === 'timerange') {
                type = 'daterange'; // 默认为日期范围
            } else {
                type = 'date'; // 默认为日期
            }
            switch (dateStringFormat) {
                case 'Y.m.d':
                    dateFormat = DATE_FORMAT_YYYY_MM_DD;
                break;
                case 'Y.m.d H:i:s':
                    dateFormat = DATE_FORMAT_YYYY_MM_DD_HH_MM_SS;
                    type = inputType === 'timerange' ? 'datetimerange' : 'datetime';
                break;
                case 'Y/m/d':
                    dateFormat = DATE_FORMAT_YYYY_SLASH_MM_SLASH_DD;
                break;
                case 'Y/m/d H:i:s':
                    dateFormat = DATE_FORMAT_YYYY_SLASH_MM_SLASH_DD_HH_MM_SS;
                    type = inputType === 'timerange' ? 'datetimerange' : 'datetime';
                break;
            }

            return {
                dateFormat,
                type
            };
        }

        const TAB_EDIT_URL = 'index.php?mod=tab&op=tabeditinterface&do=edittab';
        const ALLOWED_TYPES = ['inputmultiselect', 'multiselect', 'cat', 'timerange', 'tabgroup', 'tabgroupdata'];

        async function SaveAllData() {
            let param = {
                gid: props.gid,
                tid: props.ids.join(','),
                edittab: true,
                formhash: props.formhash,
            };

            if (iconnew) {
                param['iconnew'] = iconnew;
            }

            // 合并两个循环，简化逻辑
            const allData = [...FormDataVal.SystemData, ...FormDataVal.formData];
            for (const element of allData) {
                if (element.flag == 'cat') {
                    param[element.flag] = FormDataVal.Catdata.value.join(',');
                } else if (element.flag == 'viewperm') {
                    param[element.flag] = {
                        uids: element.uids.join(','),
                        orgids: element.groups.join(','),
                    };
                } else if (element.flag == 'tabname') {
                    if (!element.values) {
                        showError(__lang.name_cannot_empty);
                        return;
                    }
                    param[element.flag] = element.values;
                } else if (element.fileds && element.fileds.length) {
                    for (const felement of element.fileds) {
                        param[felement.flag] = ALLOWED_TYPES.includes(felement.type) ? felement.value.join(',') : felement.value;
                    }
                } else {
                    param[element.flag] = element.values;
                }
            }

            try {
                const { data: res } = await axios.post(TAB_EDIT_URL, param);
                if (res.success) {
                    if (props.create) {
                        window.location.href = `index.php?mod=tab&op=information&kid=tb_${props.gid}&gid=${props.gid}&tid=${res.tid}`;
                    } else {
                        context.emit('change', param);
                    }
                } else {
                    showError(res.msg || __lang.modification_error);
                }
            } catch (error) {
                console.error('Error saving data:', error);
                showError(__lang.unexpected_error);
            }
        }

        function showError(message) {
            ElementPlus.ElMessage({
                type: 'error',
                message,
            });
        }
        async function CreateData(){
            FormDataVal.loading = true;
            FormDataVal.formData = [];
            iconnew = '';
            var CancelToken = axios.CancelToken;
            if(FormDataVal.PostParam){
                FormDataVal.PostParam();
            }
            axios.post('index.php?mod=tab&op=tabeditinterface&do=edittab',{
                gid:props.gid,
            },{
                cancelToken: new CancelToken(function executor(c) {
                    FormDataVal.PostParam = c;
                })
            }).then(function ({data: res}) {
                const data = res.data;
                manageperm.value = false;
                iconlock.value = true;
                FormDataVal.number = 0;
                FormDataVal.imageUrl = [];

                FormDataVal.SystemData = [];
                if(data.sysforms){
                    for (const key in data.sysforms){
                        const element = data.sysforms[key];
                        element['flag'] = key;
                        element['lockedload'] = false;
                        if(element.type == 'bool'){
                            element['values'] = 0;
                        
                        }else if(element.type == 'user'){
                            let groups = [],uids = [],fdata = [],checked = [],expanded = [];
                            element['groups'] = groups;
                            element['uids'] = uids;
                            element['data'] = fdata;
                            element['checked'] = checked;
                            element['expanded'] = expanded;
                        }
                        FormDataVal.SystemData.push(element)
                    }
                }
                for(var i in data.forms){
                    for(var x in data.forms[i].fileds){
                        let curr = data.forms[i].fileds[x];
                        curr['lockedload'] = false;
                        switch (curr.type){
                            case 'input':
                            case 'textarea':
                            case 'select':
                            case 'inputselect':
                            case 'link':
                            case 'bool':
                                curr['value'] = '';
                                
                            break;

                            case 'time':
                                curr['value'] = '';
                                var dataVal = convertDateFormat(curr.extra.dateformat,curr.type);
                                curr.extra.dateformat = dataVal.dateFormat;
                                curr.extra['type'] = dataVal.type;
                            break;
                            case 'timerange':
                                curr['timevalue'] = [];
                                curr['value'] = [];
                                var dataVal = convertDateFormat(curr.extra.dateformat,curr.type);
                                curr.extra.dateformat = dataVal.dateFormat;
                                curr.extra['type'] = dataVal.type;
                            break;
                            case 'fulltext':
                                curr['isshow'] = true;
                                curr['value'] = '';
                                
                            break;
                            case 'multiselect':
                            case 'inputmultiselect':
                                curr['value'] = [];
                            break;
                            case 'tabgroup':
                                curr['iframe'] = '';
                                curr['data'] = [];
                                curr['value'] = [];
                            break;
                        }
                    }
                    FormDataVal.formData.push(data.forms[i])
                }
                var str = {
                    type:'cat',
                    flag:'cat',
                    value:[],
                    style:1,
                    defaultOpenkeys:[],
                    labelname:__lang.classify
                }
                FormDataVal.Catdata = str;
                FormDataVal.loading = false;
                FormDataVal.PostParam = '';
            }).catch(function(action){
                console.log(action)
            });
        }
        async function SaveData(flag,index,type){
            if(props.allsubmit)return false;
            let curr = '';
            if(type == 'system'){
                curr = FormDataVal.SystemData[index];
            }else{
                if(flag == 'cat'){
                    curr = FormDataVal.Catdata;
                }else{
                    curr = FormDataVal.formData[index].fileds.find(function(current){
                        return current.flag == flag;
                    });
                }
            }
            if(!curr)return false;
            if(type == 'system' || type == 'tabgroup'){
                var val = curr.values;
            }else{
                var val = curr.value;
            }
            if(curr.type == 'multiselect' || curr.type == 'inputmultiselect' || curr.type =='cat' || curr.type =='timerange' || curr.type =='tabgroup' || curr.type =='tabgroupdata'){
                val = val.join(',');
            }else if(curr.type =='user'){
                val = {
                    uids:curr.uids.join(','),
                    orgids:curr.groups.join(','),
                };
            }
            if(type == 'system' && curr.flag == 'tabname' && !val){
                ElementPlus.ElMessage({
                    type:'error',
                    message:__lang.name_cannot_empty
                });
                return false;
            }
            
            const { data: res } = await axios.post('index.php?mod=tab&op=tabeditinterface&do=save',{
                gid:props.gid,
                tid:props.ids.join(','),
                flag:flag,
                val:val
            });
            if(res.success){
                if(flag == 'cat' || flag == 'tabname'){
                    context.emit('change',flag,val);
                }
            }else{
                ElementPlus.ElMessage({
                    type:'error',
                    message:res.msg || __lang.modification_error
                });
            }
        }
        async function handlelocked(flag,islock){
            let param = {
                gid:props.gid,
                tid:props.ids.join(','),
                flag:flag,
                islock:islock
            }
            lockedload.value = true;
            const {data: res} = await axios.post('index.php?mod=tab&op=tabeditinterface&do=lockfiled',param);
            if(res.success){
                iconlock.value = islock?true:false;
            }else{
                ElementPlus.ElMessage({
                    type:'error',
                    message:res.msg || __lang.submit_error
                });
            }
            lockedload.value = false;
        }   
        function handTabVal(data,flag,index){
            let curr = FormDataVal.formData[index].fileds.find(function(current){
                return current.flag == flag;
            });
            if(!curr)return false;

            let val = [];
            for (let index = 0; index < data.length; index++) {
                const element = data[index];
                val.push(element.tid);
            }
            curr.value = val;
            curr.data = data;
            
            SaveData(flag,index);
        }
        function SetCatVal(type,val){
            FormDataVal.Catdata[type] = val;
            if(type == 'value'){
                SaveData('cat');
            }
        }
        function SetImgVal(val){
            FormDataVal.imageUrl = [val];
        }
        function SetNameVal(val){
            let curr = FormDataVal.SystemData.find((current) => {
                return current.flag == 'tabname';
            });
            if(curr){
                curr.values = val;
            }
        }
        function init(){
            GetData();
        };
        
        function ImageLocation(type){//输入图片地址
            let newurl = '';
            ElementPlus.ElMessageBox.prompt(__lang.Image_address, __lang.modification, {
                confirmButtonText: __lang.confirms,
                cancelButtonText: __lang.cancel,
                beforeClose: async (action, instance, done) => {
                    if (action === 'confirm') {
                        var ImgObj = new Image(); //判断图片是否存在  
                        instance.confirmButtonLoading = true;
                        const { data: res } = await axios.post('index.php?mod=tab&op=tabeditinterface&do=getimg',{
                            gid:props.gid,
                            img:instance.inputValue
                        });
                        if(!res.success){
                            ElementPlus.ElMessage({
                                type:'error',
                                message:res.msg || __lang.Address_error
                            });
                            instance.confirmButtonLoading = false;
                            return false;
                        }
                        newurl = res.path;
                        instance.confirmButtonLoading = false
                        done();
                    } else {
                        done();
                    }
                },
            }).then(function({value}){
                if(type == 'right'){
                    MainfilterBox.type = 'right';
                }else{
                    MainfilterBox.type = 'main';
                }
                MainfilterBox.img  = newurl;
                MainfilterBox.visible = true;
            }).catch(function(action){
                console.log(action)
            });
    }
        function handleCommandUpload(command) {
            if(command == 'address'){
                ImageLocation('right')
            }
        }
        function ImgUploadSuccess(response,uploadFile){
            let reader = new FileReader()
            reader.readAsDataURL(response.raw)
            reader.onload = e => {
                MainfilterBox.type = 'right';
                MainfilterBox.img  = e.target.result;
                MainfilterBox.visible = true;
            }
        }

        function cropperImageLoad(msg){
            if(msg == 'error' && !MainfilterBox.message){
                MainfilterBox.message = true;
                ElementPlus.ElMessage({
                    type:'error',
                    message:__lang.Unable_address,
                    'on-close':function(){
                        MainfilterBox.message = false;
                    }
                });
            }
        }
        let vuecropper = ref(null);
        function handleCrop(data){
            ficonnew = [data.url];
        }
        function MainAddUploadPrimary() {
            vuecropper.value.getCropBlob(async (data) => {
                const formdata = new FormData();
                formdata.append('gid', props.gid);
                formdata.append('iconnew', data);
                MainfilterBox.visible = false;
                if(props.create){
                    iconnew = formdata.get('iconnew');
                    FormDataVal.imageUrl = [ficonnew];
                    return false;
                }
                
                if(MainfilterBox.type == 'right'){
                    formdata.append('iconnew', data);
                    formdata.append('tid', props.ids[0]);
                    const { data: res } = await axios.post('index.php?mod=tab&op=tabeditinterface&do=uploadico&notqs=1',formdata);
                    if(res.success){
                        if(res.filepath.includes('?')){
                            context.emit('change','icon',res.filepath+'&'+Math.random()*10);
                            FormDataVal.imageUrl = [res.filepath+'&'+Math.random()*10];
                        }else{
                            context.emit('change','icon',res.filepath+'?'+Math.random()*10);
                            FormDataVal.imageUrl = [res.filepath+'?'+Math.random()*10];
                        }
                        
                    }else{
                        ElementPlus.ElMessage({
                            type:'error',
                            message:res.msg || __lang.set_unsuccess,
                        });
                    }
                    
                }else{
                    
                    
                }
                
            });
        }

        async function AllLocked(val){
            let param = {
                gid:props.gid,
                tid:props.ids.join(','),
                islock:val
            }
            const {data: res} = await axios.post('index.php?mod=tab&op=tabeditinterface&do=lockfiledbytid',param);
            if(res.success){
                if(FormDataVal.SystemData && FormDataVal.SystemData.length){
                    FormDataVal.SystemData.forEach(current => {
                        current.islocked = parseFloat(val)?true:false;
                    });
                }
                if(FormDataVal.formData && FormDataVal.formData.length){
                    FormDataVal.formData.forEach(current => {
                        if(current.fileds && current.fileds.length){
                            current.fileds.forEach(fcurrent => {
                                fcurrent.islocked = parseFloat(val)?true:false;
                            })
                        }
                    });
                }
                iconlock.value = parseFloat(val)?true:false;
            }else{
                ElementPlus.ElMessage({
                    type:'error',
                    message:res.msg || __lang.submit_error
                });
            }
        }

        async function handleRecovery(){
            let param = {
                gid:props.gid,
                tid:props.ids.join(','),
            }
            const {data: res} = await axios.post('index.php?mod=tab&op=tabeditinterface&do=delico',param);
            if(res.success){
                FormDataVal.imageUrl[0] = res.filepath;
            }else{
                ElementPlus.ElMessage({
                    type:'error',
                    message:res.msg || __lang.submit_error
                });
            }
        }
        function handleclose(){
            MainfilterBox.visible = false
        }
        return {
            FormDataVal,
            MainfilterBox,
            vuecropper,
            iconlock,
            lockedload,
            manageperm,
            init,
            SaveData,
            handTabVal,
            SetCatVal,
            SetImgVal,
            SetNameVal,
            ImageLocation,
            handleCommandUpload,
            ImgUploadSuccess,
            cropperImageLoad,
            handleCrop,
            MainAddUploadPrimary,
            AllLocked,
            handlelocked,
            handleRecovery,
            SaveAllData,
            CreateData,
            handleclose
            
        }
    },
    components: {
        message_system:tab_message_system,
        message_basic:tab_message_basic,
    },
};