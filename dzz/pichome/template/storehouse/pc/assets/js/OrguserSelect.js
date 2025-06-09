export const orguserselect = {
	name: "orguserselect",
	props:[
		'width',//宽度
		'placeholder',
		'defaultheckeds',//默认选中
		'defaultexpanded',//默认打开
		'defaultdata',//默认显示
		'defaulttype'
	],
	template: `
		<el-popover
			:ref="'popover_'+defaulttype"
		    placement="bottom"
		    trigger="click"
			@show="PopoverShow"
			@hide="PopoverHide"
			:width="popoverwidth?popoverwidth:''">
			<el-scrollbar max-height="350">
				<el-tree
					v-if="popovervisible"
					class="orguser-tree"
					:ref="'orgusertree_'+defaulttype"
					lazy
					:load="TreeLoadNode"
					show-checkbox
					node-key="id"
					:props="Tree.props"
					check-on-click-node
					:default-checked-keys="Tree.checkeds"
					:default-expanded-keys="Tree.expanded"
					@check="TreeChange">
					<template #default="{ node, data }">
						<div class="custom-tree-node mini" style="line-height: 34px;height:34px;">
							<template v-if="data.type == 'user'">
								<template v-if="data.icon">
									<img :src="data.icon" class="img-circle" style="vertical-align: middle;" />
									<span>{{ data.label }}</span>
								</template>
								<template v-else>
									<span v-html="data.text"></span>
								</template>
							</template>
							<template v-else-if="data.type == 'default'">
								<i class="icon ri-shield-user-line" style="vertical-align: bottom;font-size: 16px;"></i>
								<span>{{ data.label }}</span>
							</template>
							<template v-else-if="data.type == 'unlimit'">
								<i class="icon ri-shield-line" style="vertical-align: bottom;font-size: 16px;"></i>
								<span>{{ data.label }}</span>
							</template>
							<template v-else>
								<i class="icon ri-building-4-line" style="vertical-align: bottom;font-size: 16px;"></i>
								<span>{{ data.label }}</span>
							</template>
						</div>
					</template>
					
				</el-tree>
			</el-scrollbar>
			<template #reference>
				<div class="el-cascader" :style="{width:width?width+'px':'100%'}" @mouseover="Inputbox.showclose=true" @mouseleave="Inputbox.showclose=false" ref="slot-cascader">
					<div class="el-input el-input--suffix " :class="{'is-focus':popovervisible}">
						<div class="el-input__wrapper">
							<input type="text" readonly="readonly" :placeholder="Inputbox.data.length?'':'{lang please_choose}'" class="el-input__inner" :style="{height: Inputbox.height+'px'}">
							<span class="el-input__suffix">
								<span class="el-input__suffix-inner">
									<template v-if="Inputbox.showclose && Inputbox.data.length">
										<el-icon class="el-input__icon" @click.stop="deleteEmpty()"><Circle-Close /></el-icon>
									</template>
									<template v-else>
										<el-icon class="el-input__icon icon-arrow-down" :class="{'is-reverse':popovervisible}"><Arrow-Down /></el-icon>
									</template>
								</span>
							</span>
						</div>
					</div>
					<div class="el-cascader__tags" v-if="Inputbox.data.length" ref="cascader_tags">
						<template v-for="item in Inputbox.data">
							<span class="el-tag el-tag--info el-tag--small el-tag--light">
								<span>{{item.text}}</span>
								<el-icon class="el-tag__close" @click.stop="InputboxDelete(item.id)""><Close /></el-icon>
							</span>
						</template>
						
					</div>
				</div>
			</template>
			
		</el-popover>
	`,
	
	data: function() {
		return {
			popovervisible:false,
			popoverwidth:'',
			Inputbox:{
				height:40,
				data:[],
				showclose:false,
				
			},
			Tree:{
				checkeds:[],
				expanded:[],
				props:{
					children: 'children',
					label: 'text',
					isLeaf:'leaf'
				},
				
			},
		};
	},
	watch:{
		'Inputbox.data':{
			handler(val){
				var self = this;
				self.handleInputboxHeight();
			},
			deep:true
		},
	},
	created() {
		var self = this;
		
		if(this.defaultheckeds && this.defaultheckeds.length){
			this.Tree.checkeds = this.defaultheckeds;
		}
		if(this.defaultexpanded && this.defaultexpanded.length){
			this.Tree.expanded = this.defaultexpanded;
		}
		if(this.defaultdata && this.defaultdata.length){
			var defaultdata = JSON.parse(JSON.stringify(this.defaultdata));
			defaultdata.forEach(function(item){
				if(item.type == 'user'){
					item.id = 'uid_'+item.id;
				}
			});
			this.Inputbox.data = defaultdata;
		}
	},
	methods:{
		InputboxDelete(id){
			var self = this;
			var index = 0;
			this.Inputbox.data.find(function(item,ind){
				if(item.id == id){
					index = ind;
				}
			});
			this.Inputbox.data.splice(index,1);
			if(self.$refs['orgusertree_'+this.defaulttype]){
				self.$refs['orgusertree_'+this.defaulttype].setChecked(id);
				var node = self.$refs['orgusertree_'+this.defaulttype].getNode(id);
				this.TreeNotChecked(node);
			}
			this.handleEmitChange();
		},
		TreeNotChecked(node){
			if(node.childNodes && node.childNodes.length){
				var len = node.childNodes.length;
				for(var i in node.childNodes){
					var item = node.childNodes[i];
					item.checked = false;
					if(item.childNodes && item.childNodes.length){
						this.TreeNotChecked(item.childNodes);
					}
				}
			}
		},
		TreeChange(data, node){
			var self = this;
			
			var checkedNodes = node.checkedNodes;
			var checkedKeys = node.checkedKeys;
			var newcheckedNodes = [];
			var arr = [];
			var arrkey = [];
			var checkeds = [];
			var index_unlimit = checkedKeys.indexOf('unlimit');
	
			if(data.type == 'unlimit' && index_unlimit>-1){
				self.$refs['orgusertree_'+this.defaulttype].setCheckedKeys(['unlimit']);
				newcheckedNodes.push({
					type:data.type,
					id:data.id,
					text:data.label
				});
				checkeds.push('unlimit');
			}else{
				if(index_unlimit>-1){
					self.$refs['orgusertree_'+this.defaulttype].setChecked('unlimit');
				}
				for(var i in checkedNodes){
					var item = checkedNodes[i];
					if(item.type == 'unlimit'){
						continue;
					}
					var id = item.id;
					if(item.pid){
						if(checkedKeys.indexOf(item.pid)<0){
							if(item.type == 'user'){
								id = item.li_attr.uid
							}
							newcheckedNodes.push({
								type:item.type,
								id:item.id,
								text:item.label
							});
						}
						
					}else{
						newcheckedNodes.push({
							type:item.type,
							id:id,
							text:item.label
						});
					}
					if(checkeds.indexOf(item.id)<0){
						checkeds.push(item.id);
					}
					
				}
			}
			for(var a in newcheckedNodes){
				if(arrkey.indexOf(newcheckedNodes[a].id)<0){
					arrkey.push(newcheckedNodes[a].id);
					arr.push(newcheckedNodes[a]);
				}
			}
			
			this.Tree.checkeds = checkeds;
			this.Inputbox.data = arr;
			this.handleEmitChange();
		},
		async TreeLoadNode(node, resolve){
			var param = {};
			if (node.level === 0) {
				param['id'] = '#';
			}else{
				param['id'] = node.data.id;
			}
			var res = await axios.post(MOD_URL+'&op=orgtree&do=orgtree',param);
			if(res == 'intercept'){
				return false;
			}
			var json = res.data;
			var data = [];
			if (node.level === 0) {
				data.push({text:'{lang anyone}',label:'{lang anyone}',id:'unlimit',leaf:true,type:'unlimit'});
			}
			for(var i in json){
				var item = json[i];
				if (node.level>0) {
					item['pid'] = node.data.id;
				}else{
					item['pid'] = 0;
				}
				if(item.type == 'user'){
					item['leaf'] = true;
				}else{
					item['leaf'] = false;
				}
				item['children'] = [];
				data.push(item);
			}
			resolve(data);
		},
		PopoverShow(val){
			var self = this;
			this.popovervisible = true;
			self.$nextTick(function(){
				if(!self.Inputbox.data.length){
					self.$refs['orgusertree_'+this.defaulttype].setCheckedKeys([]);
				}
			});
			this.handleWindowResize();
		},
		PopoverHide(val){
			this.popovervisible = false;
		},
		handleEmitChange(){
			var self = this;
			self.$refs['popover_'+self.defaulttype].updatePopper()
			this.Inputbox.data.forEach(function(item){
				if(item.id.indexOf('uid_')>-1){
					item.id = item.id.replace('uid_','');
				}
			})
			self.$emit('change',this.Inputbox.data);
		},
		handleWindowResize(){
			var self = this;
			var w = self.$refs['slot-cascader'].clientWidth;
			this.popoverwidth = w;
		},
		handleInputboxHeight(){
			var self = this;
			self.$nextTick(function(){
				if(self.$refs.cascader_tags){
					var clientHeight = self.$refs.cascader_tags.clientHeight;
					self.Inputbox.height = clientHeight+6;
				}
			});
		},
		deleteEmpty(){
			var self = this;
			if(self.popovervisible){
				self.$refs['orgusertree_'+this.defaulttype].setCheckedKeys([]);
			}
			this.Inputbox.data = [];
			self.Inputbox.height = 34;
			this.handleEmitChange();
		}
	},
	mounted() {
		var self = this;
		window.addEventListener('resize', self.handleInputboxHeight);
	},
	beforeRouteLeave() {
		
	},
	
};