
const collect_all = {
    name:'collect_all',
    template:`
		<van-collapse v-model="parammodel.active" :border="false">
			<van-collapse-item v-for="(item,index) in paramdata" :key="item.cid" :name="item.cid"  :class="{'active':parammodel.checkedvalue.indexOf(item.cid)>-1}" :ref="setDom">
				<template #title>
					<div @click.stop.self.prevent="select(item)">
						{{item.catname}}
					</div>
				</template>
				<template #right-icon>
					<template v-if="!item.leaf">
						<i @click.stop="toggle(item,index)" class="van-icon van-icon-arrow van-cell__right-icon"></i>
					</template>
					<template v-else>
						<div style="width: 20px;"></div>
					</template>
				</template>
				<div class="van-cell" v-if="item.loading">
					<van-loading type="spinner" size="24px" />
				</div>
				<template v-if="item.children && item.children.length">
					<collect-all
						:parammodel="parammodel" 
						:paramdata="item.children" 
						@select="select" 
						@append="append"
						:level="parseInt(level)+1"></collect-all>
				</template>
			</van-collapse-item>
		</van-collapse>
    `,
    props: {
		paramdata:{
			required:true,
			type: Array,
			default:[],
		},
		parammodel:{
			required:true,
			type: String,
			default:'',
		},
		level:{
			required:true,
			type: String,
			default:'',
		},
    },
    setup(props, context){
		let getnum = 0;
		if(props.level == 0){
			getdata();
		}else{
			openChild(this.paramdata);
		}
		async function getdata(data){
			var param = {};
			if(data){
				data.loading = true;
				if(data.parent){
					param['clid'] = data.clid;
				}else{
					param['clid'] = data.clid;
					param['cid'] = data.cid;
				}
			}
			var res = await axios.post('index.php?mod=collection&op=collect&do=collectlist',param);
			var json = res.data;
			if(json.success){
				var arr = [];
				for(var i in json.success){
					var item = json.success[i];
					if(data){
						item['cid'] = parseInt(item['cid']);
						item['parent'] = false;
					}else{
						item['cid'] = 'p'+item['clid'];
						item['catname'] = item['name'];
						item['parent'] = true;
					}
					item['children'] = [];
					item['loaded'] = false;
					item['loading'] = false;
					item['leaf'] = item['leaf'];
					arr.push(item);
					
				}
				
				if(data){
					if(arr.length){
						append({
							data:data,
							val:arr
						});
						// data.leaf = false;
					}else{
						// data.leaf = true;
					}
					data.loading = false;
					data.loaded = true;
				}else{
					append({
						type:'parent',
						val:arr
					});
				}
				if(getnum == 0){
					nextTick(function(){
						getnum = 1;
						// openChild(arr);
					});
				}
				
			}else{
				
			}
		}
		function toggle(item,index){
			if(domList.length){
				var expanded = domList[index].expanded;
				if(!item.loaded){
					getdata(item);
				}
				nextTick(function(){
					domList[index].toggle(!expanded.value);
				});
				
			}
		};
		function append(data){
			context.emit('append',data);
		}
		function select(data){
			context.emit('select',data);
		}
		function openChild(data){
				for(var i in data){
					var item = data[i];
					getdata(item);
						
				}
		}
		var domList = []
		const setDom = (el) => {
		  	domList.push(el)
		}
		onBeforeUpdate(()=>{
			domList = []
		})
        return {
			select,
			toggle,
			setDom,
			append
        }
    }
};

