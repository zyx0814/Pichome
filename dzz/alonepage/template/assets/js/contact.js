const Tmpcontact = {
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
        <el-table ref="DomTable" :data="model.data[0].data" style="width: 100%" row-key="key">
            <el-table-column :label="Lang.text1" width="60" align="center">
                <template #default="scope">
                    <el-icon class="table-move-icon"><Rank /></el-icon>
                </template>
            </el-table-column>
            <el-table-column :label="Lang.text2" type="index" width="60" align="center" />
            <el-table-column :label="Lang.text3" width="60" align="center">
                <template #default="scope">
                    <el-upload
                        :show-file-list="false" 
                        name="files"
                        accept="image/gif,image/png,image/jpg,image/jpeg,image/svg"
                        action="index.php?mod=alonepage&op=alonepageinterface&do=upload" 
                        :on-success="handleUploadSucess">
                        <div @click="curRowIndex=scope.$index">
                            <img style="object-fit: contain;height: 32px;width: 32px;" v-if="parseInt(scope.row.aid)" :src="scope.row.imgurl" class="avatar" />
                            <el-button v-else plain icon="plus" size="small" style="padding: 0;height: 32px;width: 32px;"></el-button>
                        </div>
                    </el-upload>
                </template>
            </el-table-column>
            <el-table-column :label="Lang.text4" prop="title">
                <template #default="scope">
                    <el-input v-model="scope.row.title" />
                </template>
            </el-table-column>
            <el-table-column width="80" align="center">
                <template #default="scope">
                    <el-button type="danger" @click="handledelete(scope.$index)" icon="Delete" plain size="small"></el-button>
                </template>
            </el-table-column>
            <template #append>
                <el-button style="width: 100%;border-radius: 0;" plain text @click="handleadd" icon="plus">{{Lang.text5}}</el-button>
            </template>
        </el-table>

    `,
    setup(props,context){
        let Lang = {
            text1:__lang.sort,
            text2:__lang.serial_number,
            text3:__lang.icon,
            text4:__lang.name,
            text5:__lang.add,
        };
        let DomTable = ref(null);
        let curRowIndex = ref(null);//当前第几个上传
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
            props.model.data[0].data[index].title = '';
            props.model.data[0].data[index].img = '';
        };
        function handleUploadSucess(response, file, fileList){//上传成功
            if(response.files && response.files.length){
                let files = response.files[0];
                props.model.data[0].data[curRowIndex.value].aid = files.data.aid;
                props.model.data[0].data[curRowIndex.value].img = files.data.img;
            }

        };
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