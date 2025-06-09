const international = {
    props:{
        language_list:{
            required:true,
            type: Array,
            default:'zh-CN',
        }
    },
    template:`
        <el-dropdown
            v-if="State"
            trigger="click" 
            max-height="300px"
            @command="handlecommand"  
            v-cloak 
            popper-class="menu-dropdown-language">
            <el-button 
                text 
                circle  
                size="large">
                <el-image 
                    style="width: 20px; height: 20px;" 
                    :src="current.icon" 
                    fit="contain"></el-image>
            </el-button>
            <template #dropdown>
                <el-dropdown-menu slot="dropdown" style="width: 165px;">
                    <template v-for="item in DataList">
                        <el-dropdown-item :command="item.langflag">
                            <el-image 
                                style="width: 20px; height: 20px;margin-right: 6px;" 
                                :src="item.icon" 
                                fit="contain"></el-image>
                            {{item.langval}}
                        </el-dropdown-item>
                    </template>
                </el-dropdown-menu>
            </template>
        </el-dropdown>

    `,
    setup(props, context){
        let DataList = [];
        let current = ref(null);
        if(language_list){
            for (const key in language_list) {
                const element = language_list[key];
                DataList.push(element);
                if(element.langflag == LANG){
                    current = element;
                }
            }
        }
        let State = parseFloat(moreLanguageState);
        async function handlecommand(val){
            if(props.current == val)return false;
            let {data: res} = await axios.post('user.php?mod=space&op=lang',{
                lang:val
            });
            if(res.msg == 'success'){
                window.location.reload();
            }
        };
        return {
            current,
            DataList,
            State,
            handlecommand
        }
    }
};