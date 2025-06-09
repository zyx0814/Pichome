const Tmpslide = {
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
        },
        typecollection:{
            required:true,
            type: Object,
            default:{},
        }
    },
    template:`
        <el-table ref="DomTable" :data="model.data[0].data" style="width: 100%" row-key="key">
            <el-table-column :label="Lang.text1" width="60" align="center">
                <template #default="scope">
                    <el-icon class="table-move-icon"><Rank /></el-icon>
                </template>
            </el-table-column>
            <el-table-column :label="Lang.text2" type="index" width="60" align="center" />
            <el-table-column :label="Lang.text3" prop="img">
                <template #default="scope">
                    <el-input v-model="scope.row.img">
                        <template #suffix>
                            <template v-if="scope.row.url">
                                <el-icon @click.stop="handleImgDelte(scope.$index)" class="upload-img">
                                    <el-image style="" :src="scope.row.url" fit="cover">
                                        <template #error><div class="el-image__placeholder"></div></template>
                                    </el-image>
                                    <el-icon class="upload-img-icon">
                                        <Close></Close>
                                    </el-icon>
                                </el-icon>
                            </template>
                            <template v-else>
                                <el-icon @click.stop=""  style="cursor:pointer;">
                                    <svg viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" data-v-ea893728=""><path fill="currentColor" d="M160 160v704h704V160H160zm-32-64h768a32 32 0 0 1 32 32v768a32 32 0 0 1-32 32H128a32 32 0 0 1-32-32V128a32 32 0 0 1 32-32z"></path><path fill="currentColor" d="M384 288q64 0 64 64t-64 64q-64 0-64-64t64-64zM185.408 876.992l-50.816-38.912L350.72 556.032a96 96 0 0 1 134.592-17.856l1.856 1.472 122.88 99.136a32 32 0 0 0 44.992-4.864l216-269.888 49.92 39.936-215.808 269.824-.256.32a96 96 0 0 1-135.04 14.464l-122.88-99.072-.64-.512a32 32 0 0 0-44.8 5.952L185.408 876.992z"></path></svg>
                                    <el-upload
                                        style="position: absolute;width: 100%;height: 100%;opacity: 0;left: 0;top: 0;z-index:1;"
                                        :show-file-list="false" 
                                        name="files"
                                        accept="image/gif,image/png,image/jpg,image/jpeg,image/svg"
                                        action="index.php?mod=alonepage&op=alonepageinterface&do=upload" 
                                        :on-success="handleUploadSucess">
                                        <div style="width: 14px;height: 14px;" @click="curRowIndex=scope.$index"></div>
                                    </el-upload>
                                </el-icon>
                            </template>
                        </template>
                    </el-input>
                </template>
            </el-table-column>
            <el-table-column :label="Lang.text4" prop="link">
                <template #default="scope">
                    <div style="display:flex;">
                        <el-select v-model="scope.row.link" style="width: 110px;margin-right:6px;" @change="scope.row.linkval=''">
                            <el-option :label="Lang.text5" value="0"></el-option>
                            <el-option :label="Lang.text6" value="1"></el-option>
                            <el-option :label="Lang.text7" value="2"></el-option>
                            <el-option :label="Lang.text8" value="3"></el-option>
                        </el-select>
                        <template v-if="parseInt(scope.row.link) == 0">
                            <el-input v-model="scope.row.linkval"></el-input>
                        </template>
                        <template v-else-if="parseInt(scope.row.link) == 1">
                            <el-select v-model="scope.row.linkval" style="width: 100%">
                                <el-option v-for="item in typecollection.library" :label="item.appname" :value="item.appid"></el-option>
                            </el-select>
                        </template>
                        <template v-else-if="parseInt(scope.row.link) == 2">
                            <el-select v-model="scope.row.linkval" style="width: 100%">
                                <el-option v-for="item in typecollection.alonepage" :label="item.pagename" :value="item.id" :key="item.id"></el-option>
                            </el-select>
                        </template>
                        <template v-else-if="parseInt(scope.row.link) == 4">
                            <el-select v-model="scope.row.linkval" style="width: 100%">
                                <el-option v-for="item in typecollection.tab" :label="item.name" :value="item.gid" :key="item.gid"></el-option>
                            </el-select>
                        </template>
                        <template v-else-if="parseInt(scope.row.link) == 3">
                            <el-cascader 
                                style="width: 100%"
                                v-model="scope.row.linkval" 
                                :options="typecollection.banner"
                                :show-all-levels="false"
                                :emitPath="false"
                                :props="{value:'id',label:'bannername',checkStrictly:true}" 
                                clearable></el-cascader>
                        </template>
                    </div>
                </template>
            </el-table-column>
            <el-table-column width="80" align="center">
                <template #default="scope">
                    <el-button type="danger" @click="handledelete(scope.$index)" icon="Delete" plain size="small"></el-button>
                </template>
            </el-table-column>
            <template #append>
                <el-button style="width: 100%;border-radius: 0;" plain text @click="handleadd" icon="plus">{{Lang.text10}}</el-button>
            </template>
        </el-table>
    `,
    setup (props,context){
        let Lang = {
            text1:__lang.sort,
            text2:__lang.serial_number,
            text3:__lang.Image_address,
            text4:__lang.Link_address,
            text5:__lang.address,
            text6:__lang.library,
            text7:__lang.page,
            text8:__lang.column,
            text10:__lang.add,
        };
        let curRowIndex = ref(null);//当前第几个上传
        let DomTable = ref(null);
        if(props.model.data && props.model.data.length && props.model.data[0].data.length){
            props.model.data[0].data.forEach(item => {
                item.key = getId();
            });
        }
        function handleadd(){//添加
            if(props.field[0].data && props.field[0].data.length){
                let val = JSON.parse(JSON.stringify(props.field[0].data[0]));
                val.key = getId();
                props.model.data[0].data.push(val);
            }
        };
        function getId(){  //获取随机数id
            let date = Date.now();
            let rund = Math.ceil(Math.random()*1000)
            let id = date + '' + rund;
            return id;
        };
        function handledelete(index){//删除
            props.model.data[0].data.splice(index,1);
        };
        function handleImgDelte(index){//图片删除
            props.model.data[0].data[index].aid = 0;
            props.model.data[0].data[index].url = '';
            props.model.data[0].data[index].img = '';
        };
        function handleUploadSucess(response, file, fileList){//上传成功
            if(response.files && response.files.length){
                let files = response.files[0];
                props.model.data[0].data[curRowIndex.value].aid = files.data.aid;
                props.model.data[0].data[curRowIndex.value].img = files.name;
                props.model.data[0].data[curRowIndex.value].url = files.data.img;
            }
        };
        // function GetLinkVal(){
        //     GetLinkValData
        // };
        onMounted(function(){//排序
            let tbody = DomTable.value.$el.querySelector('.el-table__body-wrapper tbody');
				Sortable.create(tbody, {
					handle: ".table-move-icon",
					animation: 150, // ms, number 单位：ms，定义排序动画的时间
					onUpdate: function(/**Event*/evt) {
						const currentRow = props.model.data[0].data.splice(evt.oldIndex, 1)[0];
                        props.model.data[0].data.splice(evt.newIndex, 0, currentRow);
					}
				})
        });
        return {
            Lang,
            handleadd,
            handledelete,
            DomTable,
            curRowIndex,
            handleUploadSucess,
            handleImgDelte
        }
    }
}