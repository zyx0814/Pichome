Vue.component('add-star-tree', {
	props:['rids','dialogvisible'],
	template: `<el-dialog
					title="创建收藏夹"
					custom-class="addcollection-dialog"
					@opened="PopoverShow"
					@closed="PopoverHide"
					:visible.sync="dialogvisible">
					<el-form ref="form" :model="formdata" label-position="top">
						<el-form-item label="名称" required>
							<el-input v-model.trim="formdata.name" placeholder="请输入名称"></el-input>
						</el-form-item>
						<el-form-item label="用户权限">
						    <el-radio-group v-model="formdata.perm">
								<el-radio :label="1">观察员</el-radio>
								<el-radio :label="2">协作成员</el-radio>
								<el-radio :label="3">管理员</el-radio>
						    </el-radio-group>
						</el-form-item>
						<el-form-item label="添加合作用户">
						    <el-input v-model.trim="Userkeyword" @input="UserKeywordInput" placeholder="姓名搜索" clearable></el-input>
							<div style="padding: 12px 24px 0 24px;">
								<template v-if="user.loading">
									<div style="position: relative;width: 100%;height: 100px;">
										<div class="img-loading center">
											<span class="el-icon-loading"></span>加载中...
										</div>
									</div>
								</template>
								<template v-else>
									<el-checkbox-group v-model="formdata.user">
									    <el-checkbox v-for="item in user.data" :label="item.uid">
											<div>
												<div style="display:inline-block" v-html="item.icon"></div>
												{{item.username}}
											</div>
										</el-checkbox>
									</el-checkbox-group>
								</template>
							</div>
						</el-form-item>
					</el-form>
					<span slot="footer" class="dialog-footer">
					    <el-button @click="dialogvisible = false">取 消</el-button>
					    <el-button type="primary" @click="handleSubmit" :disabled="formdata.name?false:true">确 定</el-button>
					  </span>
				</el-dialog>`,
	
	data: function() {
		return {
			user:{
				data:[],
				loading:true
			},
			Userkeyword:'',
			formdata:{
				name:'',
				perm:1,
				user:[],
			}
		}
	},
	watch:{

	},
	created() {
		
	},
	methods:{
		UserKeywordInput:debounce(function(val){//输入
			this.GetUsers();
		},800),
		PopoverShow(){
			this.GetUsers();
		},
		PopoverHide(){
			var self = this;
			self.user.loading = true;
			self.Userkeyword = '';
			self.formdata = {
				name:'',
				perm:1,
				user:[],
			};
			self.$emit('closeaddcollectdialog');
		},
		handleSubmit(){
			var self = this;
			var param = {
				name:self.formdata.name,
				perm:self.formdata.perm,
				uids:self.formdata.user.join(','),
			};
			$.post(SITEURL+DZZSCRIPT+'?mod=collection&op=collect&do=addcollect',param,function(json){
				if(json.success){
					self.addCollect(json.success.clid);
				}else{
					self.$message.error(json.error);
				}
				
			},'json');
		},
		addCollect(clid){
			var self = this;
			var param = {
					rids:this.rids.join(','),
					clid:clid
				};
			$.post(SITEURL+DZZSCRIPT+'?mod=collection&op=collect&do=addfilecollect',param,function(json){
				if(json.success){
					self.$message({
						type:'success',
						message:'收藏夹创建成功'
					});
					self.$message({
						type:'success',
						message:'文件收藏成功'
					});
					self.PopoverHide();
				}else{
					self.$message.error(json.error);
				}
			},'json');
		},
		GetUsers(){
			var self = this;
			self.user.loading = true;
			$.post(SITEURL+DZZSCRIPT+'?mod=collection&op=collect&do=getuser',{
				keyword:self.Userkeyword
			},function(json){
				var data = [];
				self.user.data = json.success;
				self.user.loading = false;
			},'json');
		}
	},
	mounted() {
		
	},
	beforeRouteLeave() {
	},
});
