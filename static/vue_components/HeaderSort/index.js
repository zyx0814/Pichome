const comsort = {
    props:{
        isleftcolumn:{//是否有左侧侧边
            required:false,
            type: Boolean,
            default:false,
        },
        isrightcolumn:{//是否有右侧侧边
            required:false,
            type: Boolean,
            default:false,
        },
        issort:{//是否显示排序
            required:false,
            type: Boolean,
            default:false,
        },
        defaultval:{//显示信息值
            required:true,
            type: Object,
            default:{
                display:[],//显示内容
                other:'',//显示内容其它
                sort:'',//排序方式
                order:'',//升序、降序
                column:[],//侧边栏
                layout:''//图片布局方式
            },
        },
        size:{
            required:false,
            type: String,
            default:'default',//large,default,small
        },
        placement:{
            required:false,
            type: String,
            default:'left-start',//top/top-start/top-end/bottom/bottom-start/bottom-end/left/left-start/left-end/right/right-start/right-end
        },
        
    },
    template:`
        <ul class="el-dropdown-menu notpadding" :class="['el-dropdown-menu--'+size]">
            <el-popover
                :placement="placement"
                :hide-after="0"
                popper-class="isdropdown header-dropdown-menu"
                :teleported="false">
                <ul class="el-dropdown-menu" :class="['el-dropdown-menu--'+size]">
                    <el-checkbox-group 
                        v-model="PropDefaultVal.display"
                        :size="size"
                        @change="handlechange('display')" >
                        <template v-for="item in DisplayData">
                            <li class="el-dropdown-menu__item">
                                <el-checkbox :label="item.key" >{{ item.val }}</el-checkbox>
                            </li>
                        </template>
                    </el-checkbox-group>
                    <li class="el-dropdown-menu__item--divided"></li>
                        <el-radio-group 
                            :size="size"
                            :disabled="PropDefaultVal.display.indexOf('other')<0"
                            style="width:100%;"
                            v-model="PropDefaultVal.other"
                            @change="handlechange('other')">
                            <template v-for="item in DisplayDataOther">
                                <li class="el-dropdown-menu__item" style="width:100%;">
                                    <el-radio :label="item.key" >{{ item.val }}</el-radio>
                                </li>
                            </template>
                        </el-radio-group>
                    </li>
                </ul>
                <template #reference>
                    <li class="el-dropdown-menu__item">
                        <label class="el-checkbox" :class="['el-checkbox--'+size]">
                            <span class="el-checkbox__input">
                                <span class="el-checkbox__inner" style="opacity:0;"></span>
                            </span>
                            <span class="el-checkbox__label">显示信息</span>
                            <el-icon :size="size" class="el-icon--right">
                                <Arrow-Right/>
                            </el-icon>
                        </label>
                    </li>
                </template>
            </el-popover>
            <template v-if="issort">
                <el-popover
                    :placement="placement"
                    :hide-after="0"
                    popper-class="isdropdown header-dropdown-menu"
                    :teleported="false">
                    <ul class="el-dropdown-menu" :class="['el-dropdown-menu--'+size]">
                        <el-radio-group 
                            :size="size"
                            style="width:100%;"
                            v-model="PropDefaultVal.order"
                            @change="handlechange('order')">
                            <template v-for="item in SortData">
                                <li class="el-dropdown-menu__item" style="width:100%;">
                                    <el-radio :label="item.key" >{{ item.val }}</el-radio>
                                </li>
                            </template>
                        </el-radio-group>
                        <li class="el-dropdown-menu__item--divided"></li>
                        <li class="el-dropdown-menu__item">
                            <el-radio 
                                :size="size"
                                v-model="PropDefaultVal.sort" 
                                @change="handlechange('sort')"
                                label="asc" >升序</el-radio>
                        </li>
                        <li class="el-dropdown-menu__item">
                            <el-radio 
                                :size="size"
                                v-model="PropDefaultVal.sort" 
                                @change="handlechange('sort')"
                                label="desc" >降序</el-radio>
                        </li>
                    </ul>
                    <template #reference>
                        <li class="el-dropdown-menu__item">
                            <label class="el-checkbox" :class="['el-checkbox--'+size]">
                                <span class="el-checkbox__input">
                                    <span class="el-checkbox__inner" style="opacity:0;"></span>
                                </span>
                                <span class="el-checkbox__label">排序方式</span>
                                <el-icon :size="size" class="el-icon--right">
                                    <Arrow-Right/>
                                </el-icon>
                            </label>
                        </li>
                        
                    </template>
                </el-popover>
            </template>
            
            <template v-if="isleftcolumn || isrightcolumn">
                <li class="el-dropdown-menu__item--divided"></li>
                <li v-if="isleftcolumn" class="el-dropdown-menu__item">
                    <el-checkbox 
                        :size="size"
                        v-model="PropDefaultVal.column" 
                        @change="handlechange('column')"
                        label="left" >左栏</el-checkbox>
                </li>
                <li v-if="isrightcolumn" class="el-dropdown-menu__item">
                    <el-checkbox 
                        :size="size"
                        v-model="PropDefaultVal.column" 
                        @change="handlechange('column')"
                        label="right" >右栏</el-checkbox>
                </li>
            </template>
            <li class="el-dropdown-menu__item--divided"></li>
            <el-radio-group 
                style="width:100%;"
                :size="size"
                v-model="PropDefaultVal.layout"
                @change="handlechange('layout')">
                <template v-for="item in LayoutData">
                    <li class="el-dropdown-menu__item" style="width:100%;">
                        <el-radio :label="item.key" >{{item.text}}</el-radio>
                    </li>
                </template>
            </el-radio-group>
            <slot name="centent"></slot>
        </ul>
    `,
    setup(props,context){
        let SortData = [
            {val:'添加时间',key:'btime'},
            {val:'修改时间',key:'dateline'},
            {val:'创建日期',key:'mtime'},
            {val:'标题',key:'name'},
            {val:'文件大小',key:'filesize'},
            {val:'尺寸',key:'whsize'},
            {val:'评分',key:'grade'},
            {val:'时长',key:'duration'},
        ];
        let DisplayData = [
            {key:'name',val:'名称'},
            {key:'extension',val:'扩展名'},
            {key:'other',val:'其它信息',children:[
                {key:'size',val:'尺寸'},
                {key:'filesize',val:'文件大小'},
                {key:'tag',val:'标签'},
                {key:'grade',val:'评分'},
                {key:'btime',val:'添加时间'},
                {key:'dateline',val:'修改时间'},
                {key:'mtime',val:'创建日期'},
            ]},
        ];
        let DisplayDataOther = [
            {key:'size',val:'尺寸'},
            {key:'filesize',val:'文件大小'},
            {key:'tag',val:'标签'},
            {key:'grade',val:'评分'},
            {key:'btime',val:'添加时间'},
            {key:'dateline',val:'修改时间'},
            {key:'mtime',val:'创建日期'},
        ];
        let LayoutData = [
            {key:'waterFall',text:'瀑布流'},
            {key:'rowGrid',text:'自适应'},
            {key:'imageList',text:'网格'},
            {key:'tabodd',text:'列表单列'},
            {key:'tabeven',text:'列表双列'},
            {key:'details',text:'详情'}
        ];
        let PropDefaultVal = reactive({
            display: props.defaultval.display || [],//显示内容
            other: props.defaultval.other || '',//显示内容其它
            order: props.defaultval.order || '',//排序方式
            sort: props.defaultval.sort || '',//升序、降序
            column: props.defaultval.column || [],//侧边栏
            layout: props.defaultval.layout || '',//图片布局方式
        });

        watch(()=>props.defaultval, (newVal, oldVal)=>{//监听数据变化
            PropDefaultVal.display = newVal.display || [];
            PropDefaultVal.other = newVal.other || '';
            PropDefaultVal.order = newVal.order || '';
            PropDefaultVal.sort = newVal.sort || '';
            PropDefaultVal.column = newVal.column || [];
            PropDefaultVal.layout = newVal.layout || '';
        },{
            deep: true,
        });
        
        function handlechange(type){//改变事件
            
            let value = null;
            switch(type){
                case 'display':
                    value = PropDefaultVal.display;
                break;
                case 'other':
                    value = PropDefaultVal.other;
                break;
                case 'sort':
                    value = PropDefaultVal.sort;
                break;
                case 'order':
                    value = PropDefaultVal.order;
                break;
                case 'column':
                    value = PropDefaultVal.column;
                break;
                case 'layout':
                    value = PropDefaultVal.layout;
                break;
            }
            if(value){
                context.emit('change',{
                    type:type,
                    value:value
                });
            }
            
        }
        return {
            SortData,
            DisplayData,
            DisplayDataOther,
            LayoutData,
            PropDefaultVal,
            handlechange
        }
    }
};