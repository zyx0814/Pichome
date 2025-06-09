const menuitem = {
    name:'menuitem',
    template:`
        <template v-for="item in datalist">
            <template v-if="item.children && item.children.length">
                <el-sub-menu 
                    :class="{'is-active':active==item.id}"
                    :index="item.id">
                    <template #title>
                        <el-image v-if="item.icon" class="icon" :src="item.icon" fit="cover">
                            <template #error><div class="el-image__placeholder"></div></template>
                        </el-image>
                        {{item.bannername}}
                        <div class="title-text" @click.stop="handleSelect(item)"></div>
                    </template>
                    <menuitem 
                        :datalist="item.children"
                        @handleselect="handleSelect"
                        :active="active"></menuitem>
                </el-sub-menu>
            </template>
            <template v-else>
                <el-menu-item 
                    :index="item.id"
                    :disabled="parseInt(item.btype) == 3"
                    :class="{'is-active':active==item.id}">
                    <template #title>
                        <el-image v-if="item.icon" class="icon" :src="item.icon" fit="cover">
                            <template #error><div class="el-image__placeholder"></div></template>
                        </el-image>
                        {{item.bannername}}
                        <div class="title-text" @click.stop="handleSelect(item)"></div>
                    </template>
                </el-menu-item>
            </template>
        </template>
    `,
    props: {
        datalist:{
            required:true,
            type: Array,
            default:[],
        },
        active:{
            required:true,
            type: Number,
            default:0,
        }
    },
    setup(props, context){
        function handleSelect(key){
            context.emit('handleselect',key);
        }
        return {
            handleSelect
        }
    }
};