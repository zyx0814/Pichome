const Tmpsearch_rec = {
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
        <div >
            <el-form label-width="120px" label-suffix=":">
                <el-form-item label="LOGO">
                    <el-upload
                        class="avatar-uploader search_rec-uploader"
                        style="overflow: unset;"
                        action="index.php?mod=alonepage&op=alonepageinterface&do=upload"
                        :show-file-list="false"
                        accept="image/gif,image/png,image/jpg,image/jpeg,image/svg"
                        name="files"
                        :on-success="handleUploadSucess">
                        <el-image 
                            v-if="model.data[0].data[0].img"
                            class="avatarimg" 
                            fit="contain" 
                            :src="model.data[0].data[0].img" 
                            style="max-width:120px;max-height:120px;"></el-image>
                        <el-icon v-else class="avatar-uploader-icon"><Plus /></el-icon>
                        <el-icon class="delete" @click.stop="deleteimage" v-if="model.data[0].data[0].img"><Circle-Close-Filled /></el-icon>
                    </el-upload>
                </el-form-item>
                <el-form-item label="标题">
                    <el-input v-model="model.data[0].data[0].title" style="width:50%;" clearable />
                </el-form-item>
                <el-form-item label="描述">
                    <el-input v-model="model.data[0].data[0].desc" style="width:50%;" clearable />
                </el-form-item>
                <el-form-item label="搜索分类">
                    <el-select
                        v-model="model.data[0].data[0].searchclassify"
                        multiple
                        style="width: 50%"
                        @change="searchclassifyChange">
                        <el-option
                            v-for="item in typecollection.search"
                            :label="item.bannername"
                            :value="item.id"
                            />
                    </el-select>
                </el-form-item>
                <el-form-item label="默认分类">
                    <el-select
                        style="width:50%;"
                        v-model="model.data[0].data[0].defaultclassify">
                        <el-option
                            v-for="item in model.data[0].data[0].hotsValue"
                            :label="item.bannername"
                            :value="item.id"
                            />
                    </el-select>
                </el-form-item>
                <el-form-item label="热词设置">
                    <div style="width:50%;">
                        <el-radio-group v-model="model.data[0].data[0].hots">
                            <el-radio :label="0" border>自动获取</el-radio>
                            <el-radio :label="1" border>手动设置</el-radio>
                        </el-radio-group>
                        <div 
                            style="padding-top: 18px;" 
                            v-if="model.data[0].data[0].hots == 1 && model.data[0].data[0].hotsValue.length">
                            <el-form-item
                                style="margin-bottom: 18px"
                                v-for="item in model.data[0].data[0].hotsValue"
                                :label="item.bannername">
                                <el-input v-model="item.value" placeholder="例：标签1,标签2"  />
                            </el-form-item>
                        </div>
                    </div>
                </el-form-item>
            </el-form>
        </div>
    `,
    setup(props,context){
        function handleUploadSucess(response, file, fileList){//上传成功
            if(response.files && response.files.length){
                let files = response.files[0];
                props.model.data[0].data[0].aid = files.data.aid;
                props.model.data[0].data[0].img = files.data.img;
            }
        };
        function searchclassifyChange(data){
            let datas = [];
            if(data.length){
                for (let index = 0; index < data.length; index++) {
                    const element = data[index];
                    for (let findex = 0; findex < props.typecollection.search.length; findex++) {
                        const felement = props.typecollection.search[findex];
                        if(element == felement.id){
                            datas.push({
                                id: felement.id,
                                icon: felement.icon || '',
                                bannername: felement.bannername,
                                btype: felement.btype || '',
                                bdata: felement.bdata || '',
                                realurl: felement.realurl || '',
                                url: felement.url || '',
                                value:''
                            });
                        }
                    }
                }


                datas.forEach(element => {
                    let curr = props.model.data[0].data[0].hotsValue.find(function(current){
                        return current.id == element.id;
                    })
                    if(curr){
                        element.value = curr.value;
                    }
                });
                props.model.data[0].data[0].hotsValue = datas;
                let xindex = data.indexOf(props.model.data[0].data[0].defaultclassify);
                if(xindex < 0){
                    props.model.data[0].data[0].defaultclassify = data[0]+'';
                }
            }else{
                props.model.data[0].data[0].defaultclassify = ''
                props.model.data[0].data[0].hotsValue = [];
            }
        }
        function deleteimage(){
            props.model.data[0].data[0].aid = 0;
            props.model.data[0].data[0].img = '';
        }
        return {
            handleUploadSucess,
            searchclassifyChange,
            deleteimage
        }
    }
}