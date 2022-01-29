
Vue.component('star-tree-dialog', {
	props:['rids','dialogvisible'],
	template: `<el-dialog
					title="选择收藏夹"
					custom-class="collection-dialog"
					@open="PopoverShow"
					@close="PopoverHide"
					:visible.sync="dialogvisible">
					<div class="collection-dialog-content">
						<div class="header">
							<el-input
								clearable 
								v-model.trim="filterText" 
								placeholder="请输入关键字"></el-input>
						</div>
						<div class="content">
							<el-scrollbar style="height:100%;">
								<div style="padding:0 12px;">
									<el-tree
										v-if="NodeShow"
										ref="lefttree"
										:load="GetTreeData"
										node-key="cid"
										lazy
										:props="defaultProps"
										:filter-node-method="TreeDataFilterNode"
										:default-expanded-keys="ExpandedNodeKeys">
											<div class="custom-tree-node" :class="{notpadding:!data.parent}" slot-scope="{ node, data }">
												<div class="img" v-if="data.parent">
													<img v-if="data.covert" :src="data.covert" />
												</div>
												<div class="name" v-cloak>{{ data.catname }}</div>
												<ul class="avatar">
													<li v-for="item in data.uids" v-html="item.icon"></li>
												</ul>
												<el-button class="btn" @click.stop.prevent="handleSubmit(data.cid,data.clid,data.parent)" type="primary" size="medium">收藏</el-button>
											</div>
										</el-tree>
								</div>
							</el-scrollbar>
						</div>
						<div class="footer" @click="handleOpenAdd">
							<i class="el-icon-circle-plus"></i>创建收藏夹
						</div>
					</div>
				</el-dialog>`,
	data: function() {
		return {
			filterText:'',
			defaultProps: {
				children: 'children',
				label: 'catname'
			},
			loading:true,
			NodeShow:false,
			ExpandedNodeKeys:[],
			alreadyExpandedNodeKeys:[]
		}
	},
	watch:{
		filterText:debounce(function(val){
			var self = this;
			if(val){
				self.alreadyExpandedNodeKeys = [];
				self.ExpandedNodeKeys = [];
				self.loading = true;
				this.NodeShow = false;
				$.post(SITEURL+DZZSCRIPT+'?mod=collection&op=collect&do=searchcollect',{
					keyword:val
				},function(json){
					var data = [];
					for(var i in json.clid){
						var id = json.clid[i];
						if(data.indexOf('p'+id)<0){
							data.push('p'+id);
						}
					}
					for(var x in json.cids){
						var id = json.cids[x];
						if(data.indexOf(parseInt(id))<0){
							data.push(parseInt(id));
						}
					}
					self.ExpandedNodeKeys = data;
					self.$nextTick(function(){
						self.NodeShow = true;
					});
				},'json');
			}else{
				self.filterTextclear();
			}
		},800)
	},
	created() {
		// console.log(this.tabindex)
	},
	methods:{
		filterTextclear(){
			var self = this;
			self.alreadyExpandedNodeKeys = [];
			self.ExpandedNodeKeys = [];
			self.loading = true;
			self.NodeShow = false;
			self.$nextTick(function(){
				self.NodeShow = true;
			});
		},
		GetTreeData(node,resolve){
			var self = this;
			var param = {};
			if(node.level == 1){
				param = {
					clid:node.data.cid.replace('p','')
				}
			}
			if(node.level > 1){
				param = {
					cid:node.data.cid,
					clid:node.data.clid
				}
			}
			$.post(SITEURL+DZZSCRIPT+'?mod=collection&op=collect&do=collectlist',param,function(json){
				var data = [];
				for(var i in json.success){
					var item = json.success[i];
					if(node.level == 0){
						item['cid'] = 'p'+item.clid;
						item['catname'] = item.name;
						item['parent'] = true;
						self.alreadyExpandedNodeKeys.push(item['cid']);
					}else{
						item['cid'] = parseInt(item.cid);
						item['parent'] = false;
						self.alreadyExpandedNodeKeys.push(parseInt(item['cid']));
					}
					
					data.push(item)
				}
				resolve(data);
				self.$nextTick(function(){
					self.GetTreeDataFinish();
				})
			},'json');
		},
		GetTreeDataFinish(){
			var self = this;
			var finish = false;
			if(self.ExpandedNodeKeys.length){
				for(var i in self.ExpandedNodeKeys){
					var id = self.ExpandedNodeKeys[i];
					if(self.alreadyExpandedNodeKeys.indexOf(id)>-1){
						finish = true;
					}else{
						return false;
					}
				}
				if(finish){
					if(self.filterText){
						self.$refs['lefttree'].filter(self.filterText);
					}
					self.loading = false;
				}
			}else{
				if(self.filterText){
					self.$refs['lefttree'].filter(self.filterText);
				}
				self.loading = false;
			}
		},
		handleSubmit(cid,clid,parent){
			var param = {}
			var self = this;
			if(parent){
				param = {
					rids:this.rids.join(','),
					clid:cid.replace('p','')
				}
			}else{
				param = {
					rids:this.rids.join(','),
					cid:cid,
					clid:clid,
				}
				
			}
			$.post(SITEURL+DZZSCRIPT+'?mod=collection&op=collect&do=addfilecollect',param,function(json){
				if(json.success){
					var node = self.$refs['lefttree'].getNode(cid);
					if(parent){
						var collect = {
							name:node.data.catname,
							key:cid
						};
					}else{
						// var collectkey = clid+'-'+node.data.pathkey;
						var collect = {
							name:node.data.catname,
							key:'p'+clid+'-'+node.data.pathkey.replaceAll('_','')
						};
					}
					localStorage.setItem('collectkey', JSON.stringify(collect));
					self.$message({
						type:'success',
						message:'文件收藏成功'
					});
					self.$emit('addcollectsuccess');
					self.PopoverHide();
				}else{
					self.$message.error(json.error);
				}
			},'json');
		},
		PopoverShow(){
			var self = this;
			var collectkey = JSON.parse(localStorage.getItem('collectkey'));
			if(collectkey){
				var keys = collectkey.key.split('-');
				if(keys.length>1){
					keys.pop();
				}
				var newkeys = [];
				for(var i in keys){
					if(keys[i].indexOf('p')>-1){
						newkeys.push(keys[i]);
					}else{
						newkeys.push(parseInt(keys[i]));
					}
				}
				this.ExpandedNodeKeys = newkeys;
			}
			
			this.NodeShow = true;
		},
		PopoverHide(){
			var self = this;
			self.NodeShow = false;
			self.filterText = '';
			self.alreadyExpandedNodeKeys = [];
			self.$emit('closecollectdialog');
		},
		handleOpenAdd(){
			var self = this;
			self.$emit('openaddcollect',this.rids);
		},
		TreeDataFilterNode(value, data) {
			if (!value) return true;
			return data.catname.indexOf(value) !== -1;
		}
	},
	mounted() {
		
	},
	beforeRouteLeave() {
	},
});
