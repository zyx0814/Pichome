var headerMethods = {
	handleSearchScreenList(type){
		var boxR = $(".txtScroll-box").outerWidth();
		var listR = $(".txtScroll").outerWidth();
		var listL = $(".txtScroll").position().left;
		if(type == 'left'){
			var left = listL+100;
			if(left>0){
				left = 0;
			}
		}else{
			var left = Math.abs(listL)+100;
			var listR1 = $(".txtScroll-box").outerWidth()-$(".txtScroll").outerWidth();
			if(~left<listR1){
				left = listR1;
			}else{
				left = '-'+left;
			}
		}
		$(".txtScroll").css('left',left+'px');
	},
	handleSearchresize(){
		if($('.txtScroll .list').length == 0){
			$('.txtScroll').css('left',0);
			$('.Kscreen').removeClass('ShowScroll');
			$('.Kscreen .arrow-close').hide();
			return false;
		}else{
			$('.Kscreen .arrow-close').show();
		}
		if($('.txtScroll').outerWidth() > $('.txtScroll-box').outerWidth()){
			$('.Kscreen').addClass('ShowScroll');
		}else{
			$('.txtScroll').css('left',0);
			$('.Kscreen').removeClass('ShowScroll');
		}
	},
	handlelibrary(appid){//点击库
		if(appid == this.GetAppid){
			return false;
		}
		window.sessionStorage.removeItem("tagfid");
		if(appid){
			location.hash='appid='+appid;
		}else{
			location.hash='';
		}
		window.location.reload();
	},
	handleRefreshLibrary(){
		if(this.GetAppid){
			location.hash='appid='+this.GetAppid;
		}else{
			location.hash='';
		}
		window.location.reload();
	},
	ShowPopoverSearch(){
		var self = this;
		$('.InputKeyword').addClass('focus');
		$('.search-keyword input').focus();
		this.popoverSearch.width = $('.el-header').find('.input-box').width();
		this.popoverSearch.height = $('body').height()*0.6;
		var host = window.sessionStorage.getItem('HostKeyword');
		if(host){
			self.popoverSearch.recenttag = host.split(',');
		}
		if(!this.keyword){
			$.post(MOD_URL+'&op=ajax&operation=getsearchtag',{
				appid:self.GetAppid
			},function(data){
				// var recenttag = [];
				// if(data.recenttag){
				// 	for(var i in data.recenttag){
				// 		var str = {
				// 			tid:i,
				// 			tagname:data.recenttag[i]
				// 		};
				// 		recenttag.push(str);
				// 	}
				// }
				self.popoverSearch.hottags = data.hottags?data.hottags:[];
				// self.popoverSearch.recenttag = recenttag;
			},'json');
		}
		return false;
	},
	ShowPopoverSearchHide(){
		$('.InputKeyword').removeClass('focus');
	},
	handleKeywordList(keywrod){
		if(this.keyword != keywrod){
			this.keyword = keywrod;
			this.handleKeyword();
		}
	},
	handleKeywordInput:debounce(function(){//头部搜索
		var value = this.keyword;
		if(value.length>100){
			this.keyword=value.slice(0,100)
		}
		this.handleKeyword();
	},300),
	handleKeyword(){
		var value = this.keyword
		if(value){
			var host = window.sessionStorage.getItem('HostKeyword');
			if(host){
				host = host.split(',');
				var index = host.indexOf(value);
				if(index>-1){
					host.splice(index,1);
				}else{
					if(host.length>9){
						host.pop();
					}
				}
				host.unshift(value);
				var newhost = host.join(',');
				if(host){
					window.sessionStorage.setItem("HostKeyword",newhost);
				}else{
					window.sessionStorage.removeItem("HostKeyword");
				}
			}else{
				window.sessionStorage.setItem("HostKeyword",value);
			}
			VuexStore.commit('SetimgParameter', {key:'keyword',val:value});
			if(!this.GetFirstLoading){
				this.Addkeyword(this.GetAppid,value,0);
			}
			this.handleLinkKeyword();
		}else{
			this.popoverSearch.keywordList = [];
			VuexStore.commit('SetimgParameter', {key:'keyword',val:''});
		}
		VuexStore.dispatch('handleHash');
	},
	handleLinkKeyword(){
		var self = this;
		$.post(MOD_URL+'&op=ajax&operation=likewords',{
			keyword:self.keyword
		},function(data){
			if(data && data.likewords){
				self.popoverSearch.keywordList = data.likewords;
			}else{
				self.popoverSearch.keywordList = [];
			}
			
		},'json');
	},
	handleSearchRecent(value){
		// VuexStore.commit('SetParams', {key:'tag',val:tid});
		// VuexStore.commit('SetParams', {key:'tagrelative',val:1});
		var host = window.sessionStorage.getItem('HostKeyword');
		if(host){
			host = host.split(',');
			var index = host.indexOf(value);
			if(index>-1){
				host.splice(index,1);
			}else{
				if(host.length>9){
					host.pop();
				}
			}
			host.unshift(value);
			var newhost = host.join(',');
			if(host){
				window.sessionStorage.setItem("HostKeyword",newhost);
			}else{
				window.sessionStorage.removeItem("HostKeyword");
			}
			
		}else{
			window.sessionStorage.setItem("HostKeyword",value);
		}
		this.keyword = value;
		
		this.ShowPopoverSearchHide();
		VuexStore.commit('SetimgParameter', {key:'keyword',val:value});
		this.Addkeyword(this.GetAppid,value,0);
		VuexStore.dispatch('handleHash');
	},
	// GetClassify(){//获取分类
	// 	var self = this;
	// 	jQuery.post(MOD_URL+'&op=ajax&operation=getsearchfolder',{
	// 		appid:appid
	// 	},function(data){
	// 		self.classify.folderdatanum = data.folderdatanum;
	// 	},'json')
	// },
	
	filterClassify(value, data) {//分类搜索
		if (!value) return true;
		return data.fname.indexOf(value) !== -1;
	},
	handleCheck(Node,data){//分类点击操作
		if(data.checkedKeys && data.checkedKeys.length){
			var str = {
				key:'classify',
				val:data.checkedKeys.join(',')
			};
			var texts = [];
			for(var i in data.checkedNodes){
				texts.push(data.checkedNodes[i].fname);
			}
			this.classify.text = texts.join(',');
			VuexStore.commit('SetParams', str);
			VuexStore.dispatch('handleHash');
		}else{
			this.handleClickDelete('classify');
		}
		
		// if(data.checkedKeys.length && data.checkedKeys.indexOf(Node.fid)>-1){
		// 	this.Addkeyword(this.GetAppid,Node.fname,2);
		// }
		
		
	},
	handleShowclassify(){//分类打开时设置默认选中项
		var self = this;
		if(this.GetParams.classify){
			self.$refs.tree[0].setCheckedKeys(this.GetParams.classify.split(','));
		}
		var params = {};
		if(this.GetAppid){
			params['appid'] = this.GetAppid;
		}
		jQuery.post(MOD_URL+'&op=ajax&operation=getsearchfolder',params,function(data){
			self.classify.folderdatanum = data.folderdatanum;
		},'json')
	},
	handlehotsearchnum(item){//分类点击历史搜索文字
		this.filterText = item.name;
	},
	Addkeyword(appid,keyword,ktype){//添加关键词次数
		var self = this;
		jQuery.post(MOD_URL+'&op=ajax&operation=addsearch',{
			appid:appid,
			keyword:keyword,
			ktype:ktype
		});
	},
	
	// GetHashParams(){
	// 	var arr = (location.hash || "").replace(/^\?/,'').split("&");
	// 	var params = {};
	// 	var appid = '';
	// 	for(var i=0; i<arr.length; i++){
	// 		var data = arr[i].split("=");
	// 		if(data.length == 2){
	// 			if(i==0){
	// 				data[0]=data[0].replace("#","");
	// 			}
	// 			switch(data[0]){
	// 				case 'keyword':
	// 					this.keyword = decodeURI(data[1]);
	// 				break;
	// 				case 'appid':
	// 					appid = data[1];
	// 				break;
	// 			}
	// 		}
	// 	}
		
	// 	if(appid){
	// 		if(this.librarys && this.librarys.length){
	// 			var appname = '全部库';
	// 			for(var i in this.librarys){
	// 				if(this.librarys[i].appid == appid){
	// 					appname = this.librarys[i].appname;
	// 				}
	// 			}
	// 			this.librarysName = appname;
	// 		}else{
	// 			this.librarysName = '全部库';
	// 		}
	// 		VuexStore.dispatch('GetHashParams');
	// 		this.GetClassify(appid);
	// 	}else{
	// 		if(this.librarys && this.librarys.length == 1){
	// 			VuexStore.commit('SetAppid',this.librarys[0].appid);
	// 			this.GetClassify(this.librarys[0].appid);
	// 		}
	// 		this.librarysName = '全部库';
	// 		VuexStore.dispatch('GetHashParams');
	// 	}
	// },
	handleavatar(type) {//头像点击
		switch(type){
			case 'personal':
				window.location.href = MOD_URL + '&op=user&do=personal';
			break;
			case 'help':
				window.open('https://www.yuque.com/pichome');
			break;
			case 'problem':
				window.open('https://support.qq.com/products/340252');
			break;
			case 'setting':
				window.location.href = MOD_URL + '&op=admin&do=basic';
			break;
			case 'library':
				window.location.href = MOD_URL + '&op=library';
			break;
			case 'about':
				this.$alert(
				`<div class="aboutlogo">
					<img src="`+MOD_PATH+`/image/phlogo.png" alt="">
				</div>
				<div class="aboutmessage">
					<div class="aboutlist">
						<span class="title">软件名称：</span><span class="mes">欧奥PicHome</span>
					</div>
					<div class="aboutlist">
						<span class="title">版本信息：</span><span class="mes">`+this.AboutVersion+`</span>
					</div>
					<div class="aboutlist">
						<span class="title">版权信息：</span><span class="mes">Powered By oaooa PicHome © 2020-2021 欧奥图文</span>
					</div>
					<div class="aboutlist">
						<span class="title">网站地址：</span><span class="mes"><a class="address" href="https://oaooa.com/" target="_blank">oaooa.com</a></span>
					</div>
				</div>`,
				'',
				{
					customClass:'aboutPichome',
					showClose:false,
					showConfirmButton:false,
					dangerouslyUseHTMLString: true,
					closeOnClickModal:true
				});
			break;
			case 'OutLogin':
				this.OutLogin();
			break;
			case 'system':
				window.open(SITEURL+'admin.php?mod=system');
			break;
		}
		return false;
	},
	
	
	
	
	
	handleScreenTime(type,val){
		if(type=='btime'){
			if(this.btime.btime == val){
				this.handleClickDelete('btime');
				return false;
			}
			this.btime.btime = val;
			if(val != '自定义范围'){
				this.btime.datelinepicker = [];
				VuexStore.commit('SetParams',{key:'btime',val:val});
				VuexStore.dispatch('handleHash');
			}
			
		}else if(type=='dateline'){
			if(this.dateline.dateline == val){
				this.handleClickDelete('dateline');
				return false;
			}
			this.dateline.dateline = val;
			if(val != '自定义范围'){
				this.dateline.datelinepicker = [];
				VuexStore.commit('SetParams',{key:'dateline',val:val});
				VuexStore.dispatch('handleHash');
			}
		}else if(type=='mtime'){
			if(this.mtime.mtime == val){
				this.handleClickDelete('mtime');
				return false;
			}
			this.mtime.mtime = val;
			if(val != '自定义范围'){
				this.mtime.datelinepicker = [];
				VuexStore.commit('SetParams',{key:'mtime',val:val});
				VuexStore.dispatch('handleHash');
			}
		}
	},
	handleClickLeftTag(fid){//标签左侧点击
		this.tagData.checkedsFid = fid;
		window.sessionStorage.setItem("tagfid",fid);
		if(fid == 'all'){
			this.tagData.Rightdata = this.tagData.alltagdata.alltagdata;
		}else{
			if(this.tagData.alltagdata.catdata && this.tagData.alltagdata.catdata[fid]){
				this.tagData.Rightdata = this.tagData.alltagdata.catdata[fid].tdatas;
			}else{
				this.tagData.Rightdata = [];
			}
			
		}
	},
	handleClickLeftlogic(value){//标签右侧逻辑点击
		if(this.tagData.checkedsId.length){
			VuexStore.commit('SetParams',{key:'tagrelative',val:value});
			VuexStore.dispatch('handleHash');
		}
		this.tagData.tagrelative = value;
	},
	
	handleClickRightTag(val){//标签右侧点击
		if(this.tagData.checkedsId.length){
			var newVal = [];
			var keyword = '';
			var data = JSON.parse(JSON.stringify(this.tagData.alltagdata.alltagdata));
			for(var i in data){
				var item = data[i];
				if(this.tagData.checkedsId.indexOf(item.tid)>-1){
					newVal.push(item.tagname);
				}
				if(val==data[i].tid){
					keyword = item.tagname;
				}
			}
			VuexStore.commit('SetParams',{key:'tag',val:this.tagData.checkedsId.join(',')});
			VuexStore.commit('SetParams',{key:'tagrelative',val:this.tagData.tagrelative});
			VuexStore.dispatch('handleHash');
			this.tagData.checkedstxt = newVal.join(',');
			var self = this;
			if(this.tagData.checkedsId.indexOf(val)>-1 && keyword){
				$.post(MOD_URL+'&op=ajax&operation=addsearch',{
					appid:self.GetAppid,
					keyword:keyword,
					ktype:1
				});
			}
		}else{
			this.handleClickDelete('tag');
		}
	},
	handleClickRightGroupTag(cid,tid){//标签右侧点击
		if(this.modelParamsTag[cid].data.length){
			var arr = [];
			var texts = [];
			for(var i in this.modelParamsTag){
				arr.push.apply(arr,this.modelParamsTag[i].data);
			}
			for(var x in this.paramsTag[cid].oldVal){
				var val = this.paramsTag[cid].oldVal[x];
				if(this.modelParamsTag[cid].data.indexOf(val.tid)>-1){
					texts.push(val.tagname);
				}
			}
			this.modelParamsTag[cid].text = texts.join(',');
			VuexStore.commit('SetParams',{key:'tag',val:arr.join(',')});
			VuexStore.dispatch('handleHash');
		}else{
			this.handleClickDelete('grouptag',cid);
		}
	},
	handleRightTagSearch(val){//标签右侧搜索
		var tagfid = window.sessionStorage.getItem("tagfid");
		if(tagfid == 'all'){
			var data = JSON.parse(JSON.stringify(this.tagData.alltagdata.alltagdata));
		}else{
			if(this.tagData.alltagdata.catdata && this.tagData.alltagdata.catdata[tagfid]){
				var data = JSON.parse(JSON.stringify(this.tagData.alltagdata.catdata[tagfid].tdatas));
			}else{
				var data = [];
			}
		}
		if(val){
			var newObj = [];
			for(var i in data){
				var item = data[i];
				if(item.tagname.indexOf(val)>-1){
					newObj.push(item);
				}
			}
			this.tagData.Rightdata = newObj;
		}else{
			this.tagData.Rightdata = data;
		}
	},
	handleRightGroupTagSearch(cid){//标签右侧搜索
		var keyword =  this.modelParamsTag[cid].search;
		var data = JSON.parse(JSON.stringify(this.paramsTag[cid].oldVal));
		if(keyword){
			var newObj = [];
			for(var i in data){
				var item = data[i];
				if(item.tagname.indexOf(keyword)>-1){
					newObj.push(item);
				}
			}
			this.paramsTag[cid].newVal = newObj;
		}else{
			this.paramsTag[cid].newVal = data;
		}
	},
	handleLink(type){//链接改变
		if(type != this.link.link){
			this.link.link = type
			VuexStore.commit('SetParams',{key:'link',val:type});
			VuexStore.dispatch('handleHash');
		}else{
			this.handleClickDelete('link');
		}
	},
	handleDesc(type){//注释改变
		if(type != this.desc.desc){
			this.desc.desc = type
			VuexStore.commit('SetParams',{key:'desc',val:type});
			VuexStore.dispatch('handleHash');
		}else{
			this.handleClickDelete('desc');
		}
	},
	handleChangeExt(){//类型改变
		VuexStore.commit('SetParams',{key:'ext',val:this.ext.val.join(',')});
		VuexStore.dispatch('handleHash');
	},
	handleChangeShape(){//形状改变
		VuexStore.commit('SetParams',{key:'shape',val:this.shape.shape.join(',')});
		VuexStore.dispatch('handleHash');
	},
	handleChangeShapeCustom(type){//形状自定义改变
		if(!type){
			this.shape.width = '';
			this.shape.height = '';
		}
	},
	handleWatchShape(type){//形状改变
		if(parseInt(this.shape.width)>0 && parseInt(this.shape.height)>0){
			var str = this.shape.width+':'+this.shape.height;
			VuexStore.commit('SetParams',{key:'shapesize',val:str});
			if(!this.FirstLoad){
				VuexStore.dispatch('handleHash');
			}
			return false;
		}
		if(this.shape.width=='' && this.shape.height=='' && this.GetParams.shapesize){
			VuexStore.commit('SetParams',{key:'shapesize',val:''});
			if(!this.FirstLoad){
				VuexStore.dispatch('handleHash');
			}
			return false;
		}
	},
	handleChangeGrade(){//评分
		VuexStore.commit('SetParams',{key:'grade',val:this.grade.grade.join(',')});
		VuexStore.dispatch('handleHash');
	},
	handleChangebtimepicker(val){//添加时间自定义
		VuexStore.commit('SetParams',{key:'btime',val:val.join('_')});
		VuexStore.dispatch('handleHash');
	},
	handleChangemtimepicker(val){//创建日期自定义
		VuexStore.commit('SetParams',{key:'mtime',val:val.join('_')});
		VuexStore.dispatch('handleHash');
	},
	handleChangedatelinepicker(val){//修改日期自定义
		VuexStore.commit('SetParams',{key:'dateline',val:val.join('_')});
		VuexStore.dispatch('handleHash');
	},
	
	handleColoBlock(value){//颜色块点击
		if(this.colors.color == value){
			this.colors.color = '';
		}else{
			this.colors.color = value;
		}
		this.handleColor();
	},
	handleColoPicker(value){//颜色块插件
		if(value){
			this.colors.color = value;
		}else{
			this.colors.color = '';
		}
		this.handleColor();
	},
	handleColorInput:debounce(function(){//颜色输入
		this.handleColor();
	},800),
	handleColor(){
		if(this.colors.color){
			VuexStore.commit('SetParams',{key:'color',val:this.colors.color});
			VuexStore.commit('SetParams',{key:'persion',val:this.colors.persion});
			VuexStore.dispatch('handleHash');
		}else{
			this.handleClickDelete('color');
		}
	},
	handleColoSlider(value){//颜色滑块
		VuexStore.commit('SetParams',{key:'persion',val:value});
		VuexStore.dispatch('handleHash');
	},
	handleduration(value){//时长单位选择
		VuexStore.commit('SetParams',{key:'dunit',val:value});
		VuexStore.dispatch('handleHash');
	},
	handleShowPopover(type,cid){//弹窗显示
		if(type == 'grouptag'){
			var data = this.GetScreenData(type,cid);
		}else{
			var data = this.GetScreenData(type);
		}
		switch(type){
			case 'grouptag'://标签弹窗显示
				if(this.paramsTag[cid]){
					this.paramsTag[cid] = [];
					var arr = [];
					var arr1 = [];
					for(var i in data){
						arr.push(data[i]);
						arr1.push(data[i]);
					}
					this.paramsTag[cid].newVal = arr;
					this.paramsTag[cid].oldVal = arr1;
				}
			break;
			case 'tag'://标签弹窗显示
				var alltagdata = JSON.parse(JSON.stringify(data.alltagdata));
				var catdata = JSON.parse(JSON.stringify(data.catdata));
				this.tagData.alltagdata = {
					alltagdata:alltagdata,
					catdata:catdata
				};
				if(this.GetAppid){
					var tagfid = window.sessionStorage.getItem("tagfid");
					if(tagfid){
						if(tagfid == 'all'){
							this.tagData.Rightdata = alltagdata;
						}else{
							if(catdata && catdata[tagfid]){
								this.tagData.Rightdata = catdata[tagfid].tdatas;
							}else{
								this.tagData.Rightdata = [];
							}
						}
					}
				}else{
					this.tagData.Rightdata = alltagdata;
				}
			break;
			case 'ext'://类型弹窗显示
				if(data){
					this.ext.data = data;
					this.ext.height = data.length*35+24;
				}else{
					this.ext.data = [];
				}
			break;
			case 'shape'://形状弹窗显示
				if(data){
					this.shape.data = data;
				}else{
					this.shape.data = [];
				}
			break;
			case 'grade'://评分弹窗显示
				if(data){
					var fdata = [];
					var notStr = {num: 0, grade: 0};
					var not = false;
					for(var i in data){
						if(parseInt(data[i].grade) == 0){
							not = true;
							notStr.num = data[i].num;
						}else{
							fdata.push(data[i]);
						}
					}
					fdata.push(notStr);
					this.grade.data = fdata;
				}else{
					this.grade.data = [];
				}
			break;
			case 'btime'://形状弹窗显示
				if(data){
					this.btime.data = data;
				}else{
					this.btime.data = [];
				}
			break;
			case 'mtime'://形状弹窗显示
				if(data){
					this.mtime.data = data;
				}else{
					this.mtime.data = [];
				}
			break;
			case 'dateline'://形状弹窗显示
				if(data){
					this.dateline.data = data;
				}else{
					this.dateline.data = [];
				}
			break;
		}
	},
	handleClickDelete(type,cid){//清除标签选项
		var self = this;
		if(this.$refs.ScreenPopoverRef && this.$refs.ScreenPopoverRef.length){
			for(var s in this.$refs.ScreenPopoverRef){
				if(this.$refs.ScreenPopoverRef[s].showPopper){
					this.$refs.ScreenPopoverRef[s].showPopper = false;
				}
			}
		}
		if(type == 'empty'){
			this.FirstLoad = true;
			if(this.keyword){
				this.keyword = '';
				this.popoverSearch.keywordList = [];
				VuexStore.commit('SetimgParameter', {key:'keyword',val:''});
			}
			if(this.classify.text){
				this.classify.text = '';
				VuexStore.commit('SetParams',{key:'classify',val:''});
			}
			if(this.colors.color){
				var colors = {
					color:'',
					persion:50
				};
				this.colors = colors;
				VuexStore.commit('SetParams',{key:'color',val:''});
				VuexStore.commit('SetParams',{key:'persion',val:50});
			}
			
			if(this.link.link){
				this.link = {
					link:'',
					val:''
				};
				VuexStore.commit('SetParams',{key:'link',val:''});
				VuexStore.commit('SetParams',{key:'linkval',val:''});
			}
			
			if(this.desc.desc){
				this.desc = {
					desc:'',
					val:''
				};
				VuexStore.commit('SetParams',{key:'desc',val:''});
				VuexStore.commit('SetParams',{key:'descval',val:''});
			}
			
			if(this.GetParams.duration){
				this.duration = {
					start:'',
					end:'',
					dunit:'s'
				};
				VuexStore.commit('SetParams',{key:'duration',val:''});
				VuexStore.commit('SetParams',{key:'dunit',val:''});
			}
			
			if(this.GetParams.wsize||this.GetParams.hsize){
				this.wsize = {
					start:'',
					end:''
				};
				this.hsize = {
					start:'',
					end:''
				};
				VuexStore.commit('SetParams',{key:'wsize',val:''});
				VuexStore.commit('SetParams',{key:'hsize',val:''});
			}
			
			if(this.GetParams.ext){
				this.ext = {
					val:[],
					height:0,
					data:[]
				};
				VuexStore.commit('SetParams',{key:'ext',val:''});
			}
			
			if(this.shape.txt){
				this.shape = {
					shape:[],
					width:'',
					height:'',
					txt:'',
					data:[]
				};
				VuexStore.commit('SetParams',{key:'shape',val:''});
				VuexStore.commit('SetParams',{key:'shapesize',val:''});
			}
			
			if(this.GetParams.grade){
				this.grade = {
					grade:[],
					data:[]
				};
				VuexStore.commit('SetParams',{key:'grade',val:''});
			}
			
			if(this.GetParams.btime){
				this.btime = {
					btime:'',
					datelinepicker:[],
					data:this.btime.data
				};
				VuexStore.commit('SetParams',{key:'btime',val:''});
			}
			
			if(this.GetParams.dateline){
				this.dateline = {
					dateline:'',
					datelinepicker:[],
					data:this.dateline.data
				};
				VuexStore.commit('SetParams',{key:'dateline',val:''});
			}
			
			if(this.GetParams.mtime){
				this.mtime = {
					mtime:'',
					datelinepicker:[],
					data:this.mtime.data
				};
				VuexStore.commit('SetParams',{key:'mtime',val:''});
			}
			
			if($('.alltag-block').length && this.tagData.checkedstxt){
				this.tagData.checkedsId = [];
				this.tagData.checkedstxt = '';
				this.tagData.search = '';
				this.tagData.tagrelative = '1';
				VuexStore.commit('SetParams',{key:'tag',val:''});
				VuexStore.commit('SetParams',{key:'tagrelative',val:''});
				if(this.tagData.checkedsFid == 'all'){
					var data = JSON.parse(JSON.stringify(this.tagData.alltagdata.alltagdata));
					this.tagData.Rightdata = data;
				}else{
					var data = JSON.parse(JSON.stringify(this.tagData.alltagdata.catdata[this.tagData.checkedsFid].tdatas));
					
					this.tagData.Rightdata = data;
				}
			}
			
			if(!$.isEmptyObject(this.modelParamsTag)){
				for(var t in this.modelParamsTag){
					this.modelParamsTag[t] = {
						search:'',
						text:'',
						data:[]
					};
				}
				var fata = JSON.parse(JSON.stringify(this.paramsTag[t].oldVal));
				this.paramsTag[t].newVal = fata;
				VuexStore.commit('SetParams',{key:'tag',val:''});
			}
			
			
			
			
	
		}else{
			switch(type){
				case 'tag':
					// localStorage.removeItem('tagfid');
					// this.tagData.checkedsFid = 'all';
					this.tagData.checkedsId = [];
					this.tagData.checkedstxt = '';
					this.tagData.search = '';
					this.tagData.tagrelative = '1';
					VuexStore.commit('SetParams',{key:'tag',val:''});
					VuexStore.commit('SetParams',{key:'tagrelative',val:''});
					
					if(this.tagData.checkedsFid == 'all'){
						var data = JSON.parse(JSON.stringify(this.tagData.alltagdata.alltagdata));
						this.tagData.Rightdata = data;
					}else{
						var data = JSON.parse(JSON.stringify(this.tagData.alltagdata.catdata[this.tagData.checkedsFid].tdatas));
						this.tagData.Rightdata = data;
					}
				break;
				case 'color':
					var colors = {
						color:'',
						persion:50
					};
					this.colors = colors;
					VuexStore.commit('SetParams',{key:'color',val:''});
					VuexStore.commit('SetParams',{key:'persion',val:50});
				break;
				case 'classify':
					this.classify.text = '';
					VuexStore.commit('SetParams',{key:'classify',val:''});
				break;
				
				case 'link':
					this.link = {
						link:'',
						val:''
					};
					VuexStore.commit('SetParams',{key:'link',val:''});
					VuexStore.commit('SetParams',{key:'linkval',val:''});
				break;
				case 'desc':
					this.desc = {
						desc:'',
						val:''
					};
					VuexStore.commit('SetParams',{key:'desc',val:''});
					VuexStore.commit('SetParams',{key:'descval',val:''});
				break;
				case 'duration':
					this.duration = {
						start:'',
						end:'',
						dunit:'s'
					};
					VuexStore.commit('SetParams',{key:'duration',val:''});
					VuexStore.commit('SetParams',{key:'dunit',val:''});
				break;
				case 'csize':
					this.wsize = {
						start:'',
						end:''
					};
					this.hsize = {
						start:'',
						end:''
					};
					VuexStore.commit('SetParams',{key:'wsize',val:''});
					VuexStore.commit('SetParams',{key:'hsize',val:''});
				break;
				case 'ext':
					this.ext = {
						val:[],
						height:0,
						data:[]
					};
					VuexStore.commit('SetParams',{key:'ext',val:''});
				break;
				case 'shape':
					this.shape = {
						shape:[],
						width:'',
						height:'',
						txt:'',
						data:[]
					};
					VuexStore.commit('SetParams',{key:'shape',val:''});
					VuexStore.commit('SetParams',{key:'shapesize',val:''});
				break;
				case 'grade':
					this.grade = {
						grade:[],
						data:[]
					};
					VuexStore.commit('SetParams',{key:'grade',val:''});
				break;
				case 'btime':
					this.btime = {
						btime:'',
						datelinepicker:[],
						data:this.btime.data
					};
					VuexStore.commit('SetParams',{key:'btime',val:''});
				break;
				case 'mtime':
					this.mtime = {
						mtime:'',
						datelinepicker:[],
						data:this.mtime.data
					};
					VuexStore.commit('SetParams',{key:'mtime',val:''});
				break;
				case 'dateline':
					this.dateline = {
						dateline:'',
						datelinepicker:[],
						data:this.dateline.data
					};
					VuexStore.commit('SetParams',{key:'dateline',val:''});
				break;
				case 'grouptag':
					var tags = [];
					for(var t in this.modelParamsTag){
						if(t!=cid){
							tags.push.apply(tags,this.modelParamsTag[t].data);
						}
					}
					VuexStore.commit('SetParams',{key:'tag',val:tags.join(',')});
					this.modelParamsTag[cid] = {
						search:'',
						text:'',
						data:[]
					};
					var fata = JSON.parse(JSON.stringify(this.paramsTag[cid].oldVal));
					this.paramsTag[cid].newVal = fata;
				break;
				
			}
		}
		
		VuexStore.dispatch('handleHash');
		setTimeout(function(){
			self.FirstLoad = false;
		},1000)
		
	},
	GetScreenData(type,cid){
		var self = this;
		var param;
		if(!type){
			return false;
		}
		var str = {
			skey:type
		};
		if(type == 'grouptag'){
			str['cid']=cid;
		}
		if(this.GetAppid){
			str['appid'] = this.GetAppid;
		}
		if(this.GetimgParameter && this.GetimgParameter.keyword){
			str['keyword'] = this.GetimgParameter.keyword;
		}
		var Params = this.GetParams;
		for(var p in Params){
			if(p==type){
				continue;
			}
			if(type == 'shape' && p =='shapesize'){
				continue;
			}
			if(Params[p]){
				if(p=='classify'){
					str['fids'] = Params[p];
				}else if(p=='grade'){
					if(Params[p].indexOf('未评分')>-1){
						var farr = Params[p].split(',');
						if(farr.length){
							farr.splice(farr.indexOf('未评分'),1);
						}
						farr.push(0);
						str[p] = farr.join(',');
					}else{
						str[p] = Params[p];
					}
					
				}else if(p=='shape'){
					var shape = [];
					var fshape = Params[p].split(',');
					var shapeData = this.shapeData;
					for(var s in fshape){
						for(var b in shapeData){
							if(fshape[s] == shapeData[b].name){
								shape.push(shapeData[b].val);
							}
						}
					}
					str[p] = shape.join(',');
				}else if(p=='btime' || p=='mtime' || p=='dateline'){
					var len = Params[p].split('_');
					if(len.length>1){
						str[p] = Params[p];
					}else{
						str[p] = GetDateVal(Params[p]);
					}
				}else{
					str[p] = Params[p];
				}
			}
			
		}
		var vtag = [];
		for(var t in this.modelParamsTag){
			if(t!=cid){
				vtag.push.apply(vtag,this.modelParamsTag[t].data);
			}
		}
		if(vtag.length){
			str['tag'] = vtag.join(',');
		}
		$.ajax({
			url : MOD_URL+'&op=ajax&operation=searchmenu_num',
			type : "post",
			data : str,
			async : false,
			dataType: "json",
			success : function(data) {
				param = data;
			}
		});
		return param;
	},
	GetHashParams(){
		var arr = (location.hash || "").replace(/^\?/,'').split("&");
		var params = {};
		for(var i=0; i<arr.length; i++){
			var data = arr[i].split("=");
			if(data.length == 2){
				if(i==0){
					data[0]=data[0].replace("#","");
				}
				switch(data[0]){
					case 'tagrelative':
						this.tagData.tagrelative = data[1];
					break;
					case 'color':
						this.colors.color = data[1];
					break;
					case 'persion':
						this.colors.persion = parseInt(data[1]);
					break;
					case 'link':
						this.link.link = data[1];
					break;
					case 'linkval':
						this.link.val = data[1];
					break;
					case 'desc':
						this.desc.desc = data[1];
					break;
					case 'descval':
						this.desc.val = data[1];
					break;
					case 'duration':
						var p = data[1].split('_');
						this.duration.start = p[0];
						this.duration.end = p[1];
					break;
					case 'dunit':
						this.duration.dunit = data[1];
					break;
					case 'wsize':
						var p = data[1].split('_');
						this.wsize.start = p[0];
						this.wsize.end = p[1];
					break;
					case 'hsize':
						var p = data[1].split('_');
						this.hsize.start = p[0];
						this.hsize.end = p[1];
					break;
					case 'ext':
						this.ext.val = data[1].split(',');
					break;
					case 'shape':
						var value = decodeURI(data[1]);
						this.shape.shape = value.split(',');
						this.shape.txt = value;
					break;
					case 'shapesize':
						var p = data[1].split(':');
						this.shape.width = p[0];
						this.shape.height = p[1];
						this.shape.custom = true;
					break;
					case 'grade':
						var value = decodeURI(data[1]);
						this.grade.grade = value.split(',');
					break;
					case 'btime':
						var value = decodeURI(data[1]);
						var value1 = value.split('_');
						if(value1.length>1){
							this.btime.btime = '自定义范围';
							this.btime.datelinepicker = value1;
						}else{
							this.btime.btime = value;
						}
					break;
					case 'mtime':
						var value = decodeURI(data[1]);
						var value1 = value.split('_');
						if(value1.length>1){
							this.mtime.mtime = '自定义范围';
							this.mtime.datelinepicker = value1;
						}else{
							this.mtime.mtime = value;
						}
					break;
					case 'dateline':
						var value = decodeURI(data[1]);
						var value1 = value.split('_');
						if(value1.length>1){
							this.dateline.dateline = '自定义范围';
							this.dateline.datelinepicker = value1;
						}else{
							this.dateline.dateline = value;
						}
					break;
				}
			}
		}
		
		
		
		// var arr = (location.hash || "").replace(/^\?/,'').split("&");
		var params = {};
		var appid = '';
		for(var i=0; i<arr.length; i++){
			var data = arr[i].split("=");
			if(data.length == 2){
				if(i==0){
					data[0]=data[0].replace("#","");
				}
				switch(data[0]){
					case 'keyword':
						this.keyword = decodeURI(data[1]);
					break;
					case 'appid':
						appid = data[1];
					break;
				}
			}
		}
		
		if(appid){
			if(this.librarys && this.librarys.length){
				var appname = '全部库';
				for(var i in this.librarys){
					if(this.librarys[i].appid == appid){
						appname = this.librarys[i].appname;
					}
				}
				this.librarysName = appname;
			}else{
				this.librarysName = '全部库';
			}
			VuexStore.dispatch('GetHashParams');
			// this.GetClassify(appid);
		}else{
			if(this.librarys && this.librarys.length == 1){
				VuexStore.commit('SetAppid',this.librarys[0].appid);
				// this.GetClassify(this.librarys[0].appid);
			}
			this.librarysName = '全部库';
			VuexStore.dispatch('GetHashParams');
		}
		this.FirstLoad = false;
	},
	GoLogin(){
		window.location.href = SITEURL+'user.php?mod=login';
	},
};