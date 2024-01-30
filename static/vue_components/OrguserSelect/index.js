
const orguserselect = {
	props:{
		width:{//宽度
			required:false,
			type: Number,
			default:0,
		},
		placeholder:{
			required:false,
			type: String,
			default:'',
		},
		defaultheckeds:{//默认选中
			required:false,
			type: Array,
			default:[],
		},
		defaultexpanded:{//默认打开
			required:false,
			type: Array,
			default:[],
		},
		defaultdata:{//默认显示
			required:true,
			type: Array,
			default:[],
		},
		defaulttype:{
			required:true,
			type: String,
			default:'',
		},
		defaultcheckedtype:{//默认选择类型
			required:false,
			type: Array,
			default:['unlimit','organization','default','user'],
		},
		isunlimit:{
			required:false,
			type: Boolean,
			default:true,
		},
	},
	template: `
		<el-popover
			:ref="'popover_'+defaulttype"
		    placement="bottom"
			popper-class="OrguserSelect-popper"
		    :visible="popovervisible"
			@show="PopoverShow"
			@before-enter="handleWindowResize"
			@hide="PopoverHide"
			:width="popoverwidth?popoverwidth:''">
			<el-scrollbar class="page-component__scroll h350">
				<el-tree
					v-if="popovervisible"
					class="orguser-tree"
					:ref="'orgusertree_'+defaulttype"
					lazy
					:load="TreeLoadNode"
					show-checkbox
					node-key="id"
					check-strictly
					:props="Tree.props"
					check-on-click-node
					:default-checked-keys="Tree.checkeds"
					:default-expanded-keys="Tree.expanded"
					@check="TreeChange">
					<template #default="{ node, data }">
						<div class="custom-tree-node mini" style="line-height: 34px;height:34px;">
							<template v-if="data.type == 'user'">
								<template v-if="data.icon">
									<img style="width:25px;height:25px;margin-right: 6px;" :src="data.icon" class="img-circle" style="vertical-align: middle;" />
									<span>{{ data.label }}</span>
								</template>
								<template v-else>
									<span v-html="data.text"></span>
								</template>
							</template>
							<template v-else-if="data.type == 'default'">
								<el-icon style="margin-right: 6px;">
									<svg t="1682674584641" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg"><path d="M118.101333 80.810667l844.757334 844.714666a21.333333 21.333333 0 0 1 0 30.165334l-30.208 30.165333a21.333333 21.333333 0 0 1-30.165334 0L57.728 141.184a21.333333 21.333333 0 0 1 0-30.165333l30.165333-30.165334a21.333333 21.333333 0 0 1 30.208 0zM128 301.866667l85.333333 85.333333V897.706667h128v-149.333334a21.333333 21.333333 0 0 1 21.333334-21.333333h128a21.333333 21.333333 0 0 1 21.333333 21.333333v149.333334h128v-83.84l169.130667 169.173333H173.952A46.464 46.464 0 0 1 128 936.106667V301.866667zM679.381333 44.373333c25.386667 0 45.952 21.034667 45.952 46.933334v123.733333h128a42.666667 42.666667 0 0 1 42.666667 42.666667v510.464l-85.333333-85.333334V300.373333h-85.333334v297.173334l-85.333333-85.333334V129.706667H257.493333L172.245333 44.373333h507.136V44.373333zM298.666667 472.533333l83.84 83.84H298.666667v-83.84z m256-172.16v85.333334h-41.173334L469.333333 341.546667V300.373333h85.333334z"></path></svg>
								</el-icon>
								<span>{{ data.label }}</span>
							</template>
							<template v-else-if="data.type == 'unlimit'">
								<el-icon style="margin-right: 6px;">
									<svg t="1682674802536" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg"><path d="M400.384 526.208c-115.2 0-211.2-96-211.2-211.2s96-211.2 211.2-211.2 211.2 96 211.2 211.2-96 211.2-211.2 211.2z m0-345.6c-70.4 0-128 57.6-128 128s57.6 128 128 128 128-57.6 128-128-57.6-128-128-128zM739.584 526.208c-12.8 0-32-12.8-38.4-25.6-6.4-19.2 0-44.8 19.2-51.2 57.6-25.6 89.6-64 89.6-102.4s-32-89.6-83.2-108.8c-25.6-12.8-32-32-25.6-57.6 6.4-19.2 32-32 51.2-19.2 83.2 38.4 140.8 102.4 140.8 179.2s-51.2 140.8-140.8 179.2c0 0-6.4 6.4-12.8 6.4z"></path><path d="M725.312 907.392h-640c-19.2 0-38.4-19.2-38.4-38.4v-25.6c0-89.6 0-96 19.2-134.4l6.4-6.4c19.2-38.4 51.2-70.4 89.6-89.6 44.8-25.6 96-25.6 198.4-25.6h96c102.4 0 153.6 0 198.4 25.6 38.4 19.2 70.4 51.2 89.6 89.6l6.4 6.4c19.2 38.4 19.2 44.8 19.2 134.4v25.6c-6.4 19.2-25.6 38.4-44.8 38.4z m-601.6-76.8h563.2c0-64 0-64-12.8-83.2v-12.8c-12.8-19.2-38.4-44.8-57.6-57.6-32-12.8-76.8-12.8-166.4-12.8h-89.6c-89.6 0-134.4 0-160 12.8-25.6 12.8-51.2 38.4-64 57.6v12.8c-12.8 12.8-12.8 19.2-12.8 83.2zM936.512 907.392c-19.2 0-38.4-19.2-38.4-38.4v-25.6c0-76.8 0-76.8-12.8-96l-6.4-6.4c-6.4-25.6-25.6-51.2-51.2-64-19.2-12.8-25.6-32-19.2-51.2 12.8-19.2 32-25.6 51.2-19.2 38.4 19.2 70.4 51.2 89.6 89.6l6.4 6.4c25.6 44.8 25.6 51.2 25.6 140.8v25.6c0 19.2-19.2 38.4-44.8 38.4z"></path></svg>
								</el-icon>
								<span>{{ data.label }}</span>
							</template>
							<template v-else>
								<el-icon style="margin-right: 6px;">
									<svg t="1682674699470" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg"><path d="M173.952 981.333333A46.464 46.464 0 0 1 128 934.4V89.6C128 63.658667 148.565333 42.666667 173.952 42.666667h505.429333C704.768 42.666667 725.333333 63.658667 725.333333 89.6V213.333333h128a42.666667 42.666667 0 0 1 42.666667 42.666667v682.666667a42.666667 42.666667 0 0 1-42.666667 42.666666H173.952zM640 128H213.333333v768h128v-149.333333a21.333333 21.333333 0 0 1 21.333334-21.333334h128a21.333333 21.333333 0 0 1 21.333333 21.333334V896h128V128z m170.666667 170.666667h-85.333334v597.333333h85.333334V298.666667zM384 469.333333v85.333334H298.666667v-85.333334h85.333333z m170.666667 0v85.333334h-85.333334v-85.333334h85.333334zM384 298.666667v85.333333H298.666667V298.666667h85.333333z m170.666667 0v85.333333h-85.333334V298.666667h85.333334z"></path></svg>
								</el-icon>
								<span>{{ data.label }}</span>
							</template>
						</div>
					</template>
	
				</el-tree>
			</el-scrollbar>
			<template #reference>
				<div class="el-cascader" :style="{width:width?width+'px':'100%'}" @mouseover="Inputbox.showclose=true" @mouseleave="Inputbox.showclose=false" ref="slot-cascader" @click="popovervisible=true">
					<div class="el-input el-input--suffix " :class="{'is-focus':popovervisible}">
					<div class="el-input__wrapper">
						<input type="text" readonly="readonly" :placeholder="Inputbox.data.length?'':'请选择'" class="el-input__inner" :style="{height: Inputbox.height+'px'}">
				
						<span class="el-input__suffix">
							<span class="el-input__suffix-inner">
								<template v-if="Inputbox.showclose && Inputbox.data.length">
									<el-icon class="el-input__icon el-icon-circle-close" v-on:click.stop.prevent="deleteEmpty">
										<Circle-Close />
									</el-icon>
								</template>
								<template v-else>
									<i class="el-input__icon el-icon-arrow-down" :class="{'is-reverse':popovervisible}"></i>
								</template>
							</span>
						</span>
					</div>
						
					</div>
					<div class="el-cascader__tags" v-if="Inputbox.data.length" ref="cascader_tags" style="z-index:100;">
						<template v-for="item in Inputbox.data">
							<span class="el-tag el-tag--info el-tag--small el-tag--light">
								<span class="el-tag__content">
									<span>{{item.text}}</span>
								</span>
								<el-icon class="el-tag__close" @click.stop="InputboxDelete(item)" style="z-index:100;">
									<Close></Close>
								</el-icon>
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
				height:30,
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
			this.Inputbox.data = defaultdata;
		}
	},
	methods:{
		InputboxDelete(data){
			let self = this;
			let id = data.id;
			let checkedData = [];
			if(self.$refs['orgusertree_'+this.defaulttype]){
				checkedData = self.$refs['orgusertree_'+this.defaulttype].getCheckedKeys();
				if(checkedData.length){
					if(data.type == 'user'){
						for (let index = 0; index < checkedData.length; index++) {
							const element = checkedData[index];
							if(typeof element == 'string'){
								let fid = parseInt(element.slice(element.lastIndexOf('_') + 1));
								if(fid == parseInt(id)){
									self.$refs['orgusertree_'+this.defaulttype].setChecked(element);
								}
							}
						}
					}else{
						self.$refs['orgusertree_'+this.defaulttype].setChecked(id);
					}
					
				}
			}
			
			let index = this.Inputbox.data.findIndex(function(item){
				return item.type == data.type && item.id == id;
			});

			if(index > -1){
				this.Inputbox.data.splice(index,1);
			}
			this.$nextTick(() => {
				if(self.$refs['orgusertree_'+this.defaulttype]){
					checkedData = self.$refs['orgusertree_'+this.defaulttype].getCheckedKeys();
					this.Tree.checkeds = checkedData;
				}else{
					if(data.type == 'organization'){
						let index = this.Tree.checkeds.findIndex(function(item){
							return item == id;
						});
						if(index > -1){
							this.Tree.checkeds.splice(index,1);
						}
					}
				}
				this.handleEmitChange();
			});


			
		},
		TreeNotChecked(node){
			if(node){
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
			}
		},
		TreeChange(data, node){
			var self = this;
			var checkedNodes = node.checkedNodes;
			var checkedKeys = node.checkedKeys;
			var newcheckedNodes = [];
			var arr = [];
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
					self.$refs['orgusertree_'+this.defaulttype].setChecked('unlimit',false);
				}
				for (let index = 0; index < checkedNodes.length; index++) {
					const item = checkedNodes[index];
					if(item.type == 'unlimit'){
						continue;
					}
					var id = item.id;
					if(item.pid){
						// if(checkedKeys.indexOf(item.pid)<0){
							if(item.type == 'user'){
								id = parseInt(id.slice(id.lastIndexOf('_') + 1));
							}
							newcheckedNodes.push({
								type:item.type,
								id:id,
								text:item.label
							});
						// }
						
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
			let storageid = [];
			if(newcheckedNodes.length){
				for (let index = 0; index < newcheckedNodes.length; index++) {
					const element = newcheckedNodes[index];
					if(element.type == 'user'){
						if(storageid.indexOf(element.id)<0){
							storageid.push(element.id);
							arr.push(element);
						}
					}else{
						arr.push(element);
					}
				}
			}
			this.Tree.checkeds = checkeds;
			this.Inputbox.data = arr;
			this.handleEmitChange();
		},
		async TreeLoadNode(node, resolve){
			const self = this;
			var param = {};
			if (node.level === 0) {
				param['id'] = '#';
			}else{
				param['id'] = node.data.id;
			}
			var res = await axios.post('index.php?mod=pichome&op=orgtree&do=orgtree',param);
			if(res == 'intercept'){
				return false;
			}
			var json = res.data;
			var data = [];
			var checkeddata = [];
			if (node.level === 0 && this.isunlimit) {
				data.push({
					text:'任何人',
					label:'任何人',
					id:'unlimit',
					leaf:true,
					type:'unlimit',
					disabled:this.defaultcheckedtype.indexOf('unlimit')<0
				});
			}
			
			for (let index = 0; index < json.length; index++) {
				const item = json[index];
				
				if (node.level>0) {
					item['pid'] = node.data.id;
				}else{
					item['pid'] = 0;
				}
				if(item.type == 'user'){
					item['leaf'] = true;
					
					let curr = self.Inputbox.data.find(function(current){
						return current.type == 'user' && current.id == item['id'];
						
					});
					item['id'] = param['id']+'_uid_'+item['id'];
					if(curr){
						checkeddata.push(item['id']);
						this.Tree.checkeds.push(item['id'])
					}
					
				}else if(item.type == 'organization'){
					item['id'] = parseInt(item['id']);
					item['leaf'] = false;
				}else{
					item['leaf'] = false;
				}
				item['children'] = [];
				item['disabled'] = this.defaultcheckedtype.indexOf(item.type)<0;
				data.push(item);
			}
			resolve(data);
			this.$nextTick(function(){
				if(self.$refs['orgusertree_'+this.defaulttype] && checkeddata.length){
					for (let index = 0; index < checkeddata.length; index++) {
						const element = checkeddata[index];
						self.$refs['orgusertree_'+this.defaulttype].setChecked(element,true);
					}
				}
			});
		},
		PopoverShow(){
			var self = this;
			self.$nextTick(function(){
				if(!self.Inputbox.data.length){
					self.$refs['orgusertree_'+this.defaulttype].setCheckedKeys([]);
				}
			});
			document.addEventListener('click', this.handleClickOutside);
			if(self.$refs['slot-cascader'].closest('.el-drawer')){
				self.$refs['slot-cascader'].closest('.el-drawer').addEventListener('click', this.handleClickOutside)
			}
			
		},
		handleClickOutside(event) {
			const self = this;
			if (self.$refs['slot-cascader'].contains(event.target)) return;
			this.PopoverHide();
		},
		PopoverHide(val){
			const self = this;
			document.removeEventListener('click', this.handleClickOutside)
			if(self.$refs['slot-cascader'].closest('.el-drawer')){
				self.$refs['slot-cascader'].closest('.el-drawer').removeEventListener('click', this.handleClickOutside)
			}
			
			this.popovervisible = false;
		},
		handleEmitChange(){
			var self = this;
			self.$emit('change',this.Inputbox.data);
		},
		handleWindowResize(){
			var self = this;
			if(self.$refs['slot-cascader']){
				var w = self.$refs['slot-cascader'].clientWidth;
				this.popoverwidth = w;
			}
			
		},
		handleInputboxHeight(){
			var self = this;
			self.$nextTick(function(){
				if(self.$refs.cascader_tags){
					var clientHeight = self.$refs.cascader_tags.clientHeight;
					self.Inputbox.height = clientHeight+6;
				}
				self.handleWindowResize();
			});
		},
		deleteEmpty(e){
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
