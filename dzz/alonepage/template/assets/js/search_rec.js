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
            <el-form label-width="150px" label-suffix=":">
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
                <el-form-item :label="Lang.text1">
                    <el-input v-model="model.data[0].data[0].title" style="width:50%;" clearable />
                </el-form-item>
                <el-form-item :label="Lang.text2">
                    <el-input v-model="model.data[0].data[0].desc" style="width:50%;" clearable />
                </el-form-item>
                <el-form-item :label="Lang.text3">
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
                <el-form-item :label="Lang.text4">
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
                <el-form-item :label="Lang.text8">
                    <div style="width:50%;">
                        <el-radio-group v-model="model.data[0].data[0].hots">
                            <el-radio :label="0" border>{{Lang.text5}}</el-radio>
                            <el-radio :label="1" border>{{Lang.text6}}</el-radio>
                        </el-radio-group>
                        <div 
                            style="padding-top: 18px;" 
                            v-if="model.data[0].data[0].hots == 1 && model.data[0].data[0].hotsValue.length">
                            <el-form-item
                                style="margin-bottom: 18px"
                                v-for="item in model.data[0].data[0].hotsValue"
                                :label="item.bannername">
                                <el-input v-model="item.value" :placeholder="Lang.text7"  />
                            </el-form-item>
                        </div>
                    </div>
                </el-form-item>
            </el-form>
        </div>
    `,
    setup(props,context){
        let Lang = {
            text1:__lang.title,
            text2:__lang.desc,
            text3:__lang.search_cat,
            text4:__lang.default_groupname,
            text5:__lang.Automatic_acquisition,
            text6:__lang.Manual_settings,
            text7:__lang.Specify_label_tip,
            text8:__lang.Hotword_settings,
        };
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
            Lang,
            handleUploadSucess,
            searchclassifyChange,
            deleteimage
        }
    }
}