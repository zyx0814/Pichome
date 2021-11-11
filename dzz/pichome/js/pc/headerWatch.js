var headerWatch = {
	filterText(val){
		var self = this;
		self.$refs.tree[0].filter(val);
	},
	ParamsTagData(val){
		var fstr = {};
		var str1 = {};
		if(this.GetParamsInit.tag.length){
			for(var x in this.GetParamsInit.tag){
				var p = this.GetParamsInit.tag[x];
				if(fstr[p.cid]){
					fstr[p.cid].text.push(p.tagname);
					fstr[p.cid].value.push(p.tid);
				}else{
					fstr[p.cid] = {
						search:'',
						text:[p.tagname],
						data:[],
						value:[p.tid],
						page:1,
						finish:false,
						loading:false
					}
					
				}
				
			}
		}
		for(var i in val){
			if(fstr[val[i].cid]){
				fstr[val[i].cid].text = fstr[val[i].cid].text.join(',');
				str1[val[i].cid] = fstr[val[i].cid];
			}else{
				str1[val[i].cid] = {
					search:'',
					text:'',
					data:[],
					page:1,
					finish:false,
					loading:false,
					value:[],
				};
			}
	
			
		}
		this.modelParamsTag = str1;
	},
	GetParamsInit:{
		handler(item){
			if(item){
				for(var i in item){
					if(i == 'tag' && item[i]){
						var newArrtid = [];
						var newArrtxt = [];
						var classs = {};
						for(var t in item[i]){
							newArrtid.push(item[i][t].tid);
							newArrtxt.push(item[i][t].tagname);
						}
						var tagfid = localStorage.getItem("tagfid");
						if(tagfid){
							this.tagData.checkedsFid = tagfid;
						}
						this.tagData.checkedsId = newArrtid;
						this.tagData.checkedstxt = newArrtxt.join(',');
					}
				}
			}
			
		},
		deep:true
	},
	'link.val':debounce(function(val){//链接输入
		VuexStore.commit('SetParams',{key:'linkval',val:val});
		if(!this.FirstLoad&&val){
			VuexStore.dispatch('handleHash');
		}
	},800),
	'desc.val':debounce(function(val){//注释输入
		VuexStore.commit('SetParams',{key:'descval',val:val});
		if(!this.FirstLoad&&val){
			VuexStore.dispatch('handleHash');
		}
	},800),
	'duration.start':debounce(function(val){//时长输入
		if(val || this.duration.end){
			var str = val+'_'+this.duration.end;	
			VuexStore.commit('SetParams',{key:'duration',val:str});
			VuexStore.commit('SetParams',{key:'dunit',val:this.duration.dunit});
			if(!this.FirstLoad){
				VuexStore.dispatch('handleHash');
			}
		}else{
			if(!this.FirstLoad){
				this.handleClickDelete('duration');
			}
			
		}
	},800),
	'duration.end':debounce(function(val){//时长输入
		if(val || this.duration.start){
			var str = this.duration.start+'_'+val;
			VuexStore.commit('SetParams',{key:'duration',val:str});
			VuexStore.commit('SetParams',{key:'dunit',val:this.duration.dunit});
			if(!this.FirstLoad){
				VuexStore.dispatch('handleHash');
			}
		}else{
			if(!this.FirstLoad){
				this.handleClickDelete('duration');
			}
		}
		
	},800),
	'wsize.start':debounce(function(val){//尺寸宽
		if(val || this.wsize.end){
			var str = val+'_'+this.wsize.end;
			VuexStore.commit('SetParams',{key:'wsize',val:str});
		}else{
			VuexStore.commit('SetParams',{key:'wsize',val:''});
		}
		if(!this.FirstLoad){
			VuexStore.dispatch('handleHash');
		}
	},800),
	'wsize.end':debounce(function(val){//尺寸宽
		if(val || this.wsize.start){
			var str = this.wsize.start+'_'+val;
			VuexStore.commit('SetParams',{key:'wsize',val:str});
		}else{
			VuexStore.commit('SetParams',{key:'wsize',val:''});
		}
		if(!this.FirstLoad){
			VuexStore.dispatch('handleHash');
		}
	},800),
	'hsize.start':debounce(function(val){//尺寸高
		if(val || this.hsize.end){
			var str = val+'_'+this.hsize.end;
			VuexStore.commit('SetParams',{key:'hsize',val:str});
		}else{
			VuexStore.commit('SetParams',{key:'hsize',val:''});
		}
		if(!this.FirstLoad){
			VuexStore.dispatch('handleHash');
		}
	},800),
	'hsize.end':debounce(function(val){//尺寸高
		if(val || this.hsize.start){
			var str = this.hsize.start+'_'+val;
			VuexStore.commit('SetParams',{key:'hsize',val:str});
		}else{
			VuexStore.commit('SetParams',{key:'hsize',val:''});
		}
		if(!this.FirstLoad){
			VuexStore.dispatch('handleHash');
		}
	},800),
	'GetParams.shape':{
		handler(val){
			if(val){
				var data = val.split(',');
				if(data.indexOf('自定义')>-1){
					data.splice(data.indexOf('自定义'),1);
					this.shape.txt = data.join(',');
				}else{
					this.shape.txt = val;
				}
			}else{
				this.shape.txt = '';
			}
		},
		deep:true,
	},
	'shape.width':debounce(function(val){
		this.handleWatchShape();
	},800),
	'shape.height':debounce(function(val){
		this.handleWatchShape();
	},800),
	GetclassifyInit(val){
		this.classify.text = val;
	}
}