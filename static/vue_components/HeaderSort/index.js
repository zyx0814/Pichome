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
                            <span class="el-checkbox__label">{{langTxt.text1}}</span>
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
                                label="asc" >{{langTxt.text2}}</el-radio>
                        </li>
                        <li class="el-dropdown-menu__item">
                            <el-radio 
                                :size="size"
                                v-model="PropDefaultVal.sort" 
                                @change="handlechange('sort')"
                                label="desc" >{{langTxt.text3}}</el-radio>
                        </li>
                    </ul>
                    <template #reference>
                        <li class="el-dropdown-menu__item">
                            <label class="el-checkbox" :class="['el-checkbox--'+size]">
                                <span class="el-checkbox__input">
                                    <span class="el-checkbox__inner" style="opacity:0;"></span>
                                </span>
                                <span class="el-checkbox__label">{{langTxt.text4}}</span>
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
                        label="left" >{{langTxt.text5}}</el-checkbox>
                </li>
                <li v-if="isrightcolumn" class="el-dropdown-menu__item">
                    <el-checkbox 
                        :size="size"
                        v-model="PropDefaultVal.column" 
                        @change="handlechange('column')"
                        label="right" >{{langTxt.text6}}</el-checkbox>
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
        let langTxt = {
            text1:__lang.display_information,
            text2:__lang.sort_asc,
            text3:__lang.sort_desc,
            text4:__lang.sortord,
            text5:__lang.Left_column,
            text6:__lang.Right_column,
        };
        let SortData = [
            {val:__lang.add_time,key:'btime'},
            {val:__lang.modify_time,key:'dateline'},
            {val:__lang.creation_time,key:'mtime'},
            {val:__lang.title,key:'name'},
            {val:__lang.filesize,key:'filesize'},
            {val:__lang.size,key:'whsize'},
            {val:__lang.grade,key:'grade'},
            {val:__lang.duration,key:'duration'},
        ];
        let DisplayData = [
            {key:'name',val:__lang.name},
            {key:'extension',val:__lang.extension},
            {key:'other',val:__lang.other_info,children:[
                {key:'size',val:__lang.size},
                {key:'filesize',val:__lang.file_size},
                {key:'tag',val:__lang.label},
                {key:'grade',val:__lang.grade},
                {key:'btime',val:__lang.add_time},
                {key:'dateline',val:__lang.modify_time},
                {key:'mtime',val:__lang.creation_time},
            ]},
        ];
        let DisplayDataOther = [
            {key:'size',val:__lang.size},
            {key:'filesize',val:__lang.file_size},
            {key:'tag',val:__lang.label},
            {key:'grade',val:__lang.grade},
            {key:'btime',val:__lang.add_time},
            {key:'dateline',val:__lang.modify_time},
            {key:'mtime',val:__lang.creation_time},
        ];
        let LayoutData = [
            {key:'waterFall',text:__lang.image_waterFall},
            {key:'rowGrid',text:__lang.image_rowGrid},
            {key:'imageList',text:__lang.image_imageList},
            {key:'tabodd',text:__lang.image_tabodd},
            {key:'tabeven',text:__lang.image_tabeven},
            {key:'details',text:__lang.image_details}
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
            langTxt,
            handlechange
        }
    }
};