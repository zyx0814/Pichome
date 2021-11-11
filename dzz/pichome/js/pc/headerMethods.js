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
		var self = this;
		var status = true;
	
		if(data.checkedKeys.indexOf(Node.fid)>-1){
			status = true;
		}else{
			status = false;
		}
		if(Node.children){
			this.classify.childFids = [];
			this.handleClassifyForEach(Node.children);
			for(var f in this.classify.childFids){
				self.$refs.tree[0].setChecked(this.classify.childFids[f],status);
			}
		}
		var itemNode = self.$refs.tree[0].getCheckedNodes();
		var checkedFids = [];
		var checkedTexts = [];
		if(itemNode.length){
			for(var i in itemNode){
				checkedFids.push(itemNode[i].fid);
				checkedTexts.push(itemNode[i].fname);
			}
			var str = {
				key:'classify',
				val:checkedFids.join(',')
			};
			this.classify.text = checkedTexts.join(',');
			VuexStore.commit('SetParams', str);
			VuexStore.dispatch('handleHash');
		}else{
			this.handleClickDelete('classify');
		}
		if(data.checkedKeys.length && data.checkedKeys.indexOf(Node.fid)>-1){
			this.Addkeyword(this.GetAppid,Node.fname,2);
		}
		
		
	},
	handleClassifyForEach(item){//分类寻找子节点
		for(var i in item){
			this.classify.childFids.push(item[i].fid);
			if(item[i].children){
				this.handleClassifyForEach(item[i].children);
			}
		}
	},
	handleShowafteclassify(){//分类打开时设置默认选中项
		var self = this;
		self.classify.loading=true
		self.classify.DefaultFids = this.GetParams.classify.split(',');
		var params = {};
		if(this.GetAppid){
			params['appid'] = this.GetAppid;
		}
		jQuery.post(MOD_URL+'&op=ajax&operation=getsearchfolder',params,function(data){
			self.classify.folderdatanum = data.folderdatanum;
			self.classify.loading=false;
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
		var self = this;
		if(this.tagData.checkedsFid == fid){
			return false;
		}
		$('.scrollbarTag .el-scrollbar__wrap')[0].scrollTop = 0;
		window.sessionStorage.setItem("tagfid",fid);
		this.tagData.checkedsFid = fid;
		this.tagData.alltagdata.catdata[fid].loading = true;
		this.tagData.alltagdata.catdata[fid].finish = false;
		this.tagData.alltagdata.catdata[fid].page = 1;
		var data = this.GetScreenData('tag');
		var alltagdata = data.alltagdata;
		var tagval = [];
		for(var g in alltagdata){
			tagval.push({
				num:alltagdata[g].num,
				tagname:alltagdata[g].tagname,
				tid:parseInt(g),
			})
		}
		
		this.tagData.alltagdata.catdata[fid].tdatas = tagval;
		this.tagData.alltagdata.catdata[fid].loading = false;
		this.tagData.alltagdata.catdata[fid].finish = data.finish;
	},
	handleClickLeftlogic(value){//标签右侧逻辑点击
		if(this.tagData.checkedsId.length){
			VuexStore.commit('SetParams',{key:'tagrelative',val:value});
			VuexStore.dispatch('handleHash');
		}
		this.tagData.tagrelative = value;
	},
	
	handleClickRightTag(val){//标签右侧点击
		if(val.length){
			var newVal = [];
			var keyword = '';
			var data = JSON.parse(JSON.stringify(this.tagData.alltagdata.catdata[this.tagData.checkedsFid].tdatas));
			for(var i in data){
				var item = data[i];
				if(val.indexOf(item.tid)>-1){
					newVal.push(item.tagname);
				}
				if(val==data[i].tid){
					keyword = item.tagname;
				}
			}
			VuexStore.commit('SetParams',{key:'tag',val:val.join(',')});
			VuexStore.commit('SetParams',{key:'tagrelative',val:this.tagData.tagrelative});
			VuexStore.dispatch('handleHash');
			this.tagData.checkedstxt = newVal.join(',');
			var self = this;
			if(val.indexOf(val)>-1 && keyword){
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
		var findex = this.tagData.checkedsId.indexOf(tid);
		if(findex>-1){
			this.tagData.checkedsId.splice(findex,1);
		}else{
			this.tagData.checkedsId.push(tid);
		}
		if(this.modelParamsTag[cid].value.length){
			var arr = [];
			var texts = [];
			for(var x in this.modelParamsTag[cid].data){
				var val = this.modelParamsTag[cid].data[x];
				if(this.modelParamsTag[cid].value.indexOf(val.tid)>-1){
					texts.push(val.tagname);
				}
			}
			this.modelParamsTag[cid].text = texts.join(',');
			VuexStore.commit('SetParams',{key:'tag',val:this.tagData.checkedsId.join(',')});
			VuexStore.dispatch('handleHash');
		}else{
			this.handleClickDelete('grouptag',cid);
		}
	},
	handleRightTagSearch(val){//标签右侧搜索
		$('.scrollbarTag .el-scrollbar__wrap')[0].scrollTop = 0;
		var tagfid = this.tagData.checkedsFid;
		this.tagData.alltagdata.catdata[tagfid].finish = false;
		this.tagData.alltagdata.catdata[tagfid].page = 1;
		this.tagData.alltagdata.catdata[tagfid].loading = true;
		var data = this.GetScreenData('tag');

		var alltagdata = data.alltagdata;
		var tagval = [];
		for(var g in alltagdata){
			tagval.push({
				num:alltagdata[g].num,
				tagname:alltagdata[g].tagname,
				tid:parseInt(g),
			})
		}
		this.tagData.alltagdata.catdata[tagfid].finish = data.finish;
		this.tagData.alltagdata.catdata[tagfid].tdatas = tagval;
		this.tagData.alltagdata.catdata[tagfid].loading = false;
	},
	handleRightGroupTagSearch(cid,index){//标签组右侧搜索
		var keyword =  this.modelParamsTag[cid].search;
		this.$refs['scrollbarTag'+cid][0].wrap.scrollTop = 0;
		this.modelParamsTag[cid].finish = false;
		this.modelParamsTag[cid].page = 1;
		this.modelParamsTag[cid].loading = true;
		var data = this.GetScreenData('grouptag',cid);
		var str = {
			index:index,
			val:true
		};
		VuexStore.commit('SetParamsTagDataLoading',str);
		this.modelParamsTag[cid].data = [];
		var arr = [];index
		var alltagdata = JSON.parse(JSON.stringify(data.alltagdata));
		for(var g in alltagdata){
			var tstr = {
				num:alltagdata[g].num,
				tagname:alltagdata[g].tagname,
				tid:parseInt(g),
			}
			arr.push(tstr);
		}
		this.modelParamsTag[cid].data = arr;
		this.modelParamsTag[cid].finish = data.finish;
		this.modelParamsTag[cid].loading = false;
		var str = {
			index:index,
			val:false
		};
		VuexStore.commit('SetParamsTagDataLoading',str);

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
	handleShowPopover(type,index){
		if(type == 'grouptag'){
			var str = {
				index:index,
				val:true
			};
			VuexStore.commit('SetParamsTagDataLoading',str);
		}else if(type == 'tag'){
			this.tagData.loading = true;
		}else{
			this[type].loading = true;
		}
		
	},
	handleHideafterPopover(type){
		if(type == 'tag'){
			this.tagData.alltagdata.catdata = {};
		}
	},

	handleShowafterPopover(type,cid,index,newdata){//弹窗显示
		var self = this;
		if(type == 'tag' && !$.isEmptyObject(this.tagData.alltagdata.catdata) && self.tagData.alltagdata.catdata[self.tagData.checkedsFid]){
			self.tagData.alltagdata.catdata[self.tagData.checkedsFid].page = 1;
		}
		if(!newdata){
			if(type == 'grouptag'){
				self.modelParamsTag[cid].page = 1;
				var data = this.GetScreenData(type,cid);
			}else{
				var data = this.GetScreenData(type);
			}
		}else{
			var data = newdata;
		}
		if(!data){
			if(this.$refs.ScreenPopoverRef && this.$refs.ScreenPopoverRef.length){
				for(var s in this.$refs.ScreenPopoverRef){
					if(this.$refs.ScreenPopoverRef[s].showPopper){
						this.$refs.ScreenPopoverRef[s].showPopper = false;
					}
				}
			}
			this.$message.error('获取数据失败');
			return false;
		}
		switch(type){
			case 'grouptag'://标签弹窗显示
				if(this.modelParamsTag[cid]){
					this.modelParamsTag[cid].data = [];
					var arr = [];
					var alltagdata = JSON.parse(JSON.stringify(data.alltagdata));
					for(var g in alltagdata){
						var tstr = {
							num:alltagdata[g].num,
							tagname:alltagdata[g].tagname,
							tid:parseInt(g),
						}
						arr.push(tstr);
					}
					this.modelParamsTag[cid].data = arr;
					this.modelParamsTag[cid].finish = data.finish;
				}
			break;
			case 'tag'://标签弹窗显示
				var alltagdata = JSON.parse(JSON.stringify(data.alltagdata));
				var catdata = JSON.parse(JSON.stringify(data.catdata));
				var fcatdata = {};
				var tagval = [];
				for(var g in alltagdata){
					tagval.push({
						num:alltagdata[g].num,
						tagname:alltagdata[g].tagname,
						tid:parseInt(g),
					})
				}
				fcatdata['all'] = {
					catname:'全部',
					num :'',
					page:1,
					loading:true,
					valloading:false,
					finish:self.tagData.checkedsFid == 'all'?data.finish:false,
					tdatas:self.tagData.checkedsFid == 'all'?tagval:[],
				};
				if(catdata){
					for(var t in catdata){
						fcatdata[catdata[t].cid]= {
							catname:catdata[t].catname,
							num :catdata[t].num,
							page:1,
							loading:true,
							valloading:false,
							finish:self.tagData.checkedsFid == catdata[t].cid?data.finish:false,
							tdatas: self.tagData.checkedsFid == catdata[t].cid?tagval:[],
						}
					}
					this.GetScreenDatanum();
				}
				this.tagData.alltagdata.catdata = fcatdata;
				self.tagData.alltagdata.catdata[self.tagData.checkedsFid].loading = false;
				this.handleScrollbarTag();
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
		if(!newdata){
			if(type == 'grouptag'){
				var str = {
					index:index,
					val:false
				};
				VuexStore.commit('SetParamsTagDataLoading',str);
				this.handleScrollbarTag(cid);
			}else if(type == 'tag'){
				this.tagData.loading = false;
			}else{
				this[type].loading = false;
			}
		}
		
	},
	handleScrollbarTag(cid){
		var self = this;
		if(cid){
			$(this.$refs['scrollbarTag'+cid][0].wrap).unbind('scroll');
			$(this.$refs['scrollbarTag'+cid][0].wrap).bind('scroll',function(){
				var loading = self.modelParamsTag[cid].loading;
				var finish =  self.modelParamsTag[cid].finish;
				if(!finish && !loading && this.scrollHeight-100 < $(this).scrollTop()+this.clientHeight){
					self.modelParamsTag[cid].loading = true;
					self.modelParamsTag[cid].page += 1;
					var json = self.GetScreenData('grouptag',cid);
					var alltagdata = JSON.parse(JSON.stringify(json.alltagdata));
					for(var g in alltagdata){
						self.modelParamsTag[cid].data.push({
							num:alltagdata[g].num,
							tagname:alltagdata[g].tagname,
							tid:parseInt(g),
						})
					}
					self.modelParamsTag[cid].loading = false;
					self.modelParamsTag[cid].finish = json.finish;
					
				}
			});
		}else{
			if(this.$refs.scrollbarTag && this.$refs.scrollbarTag.length){
				$('.scrollbarTag .el-scrollbar__wrap')[0].scrollTop = 0;
				$('.scrollbarTag .el-scrollbar__wrap').unbind('scroll');
				$('.scrollbarTag .el-scrollbar__wrap').bind('scroll',function(){
					var valloading = self.tagData.alltagdata.catdata[self.tagData.checkedsFid].valloading;
					var finish = self.tagData.alltagdata.catdata[self.tagData.checkedsFid].finish;
					if(!finish && !valloading && this.scrollHeight-100 < $(this).scrollTop()+this.clientHeight){
						self.tagData.alltagdata.catdata[self.tagData.checkedsFid].valloading = true;
						self.tagData.alltagdata.catdata[self.tagData.checkedsFid].page += 1;
						var json = self.GetScreenData('tag');
						var alltagdata = JSON.parse(JSON.stringify(json.alltagdata));
						for(var g in alltagdata){
							self.tagData.alltagdata.catdata[self.tagData.checkedsFid].tdatas.push({
								num:alltagdata[g].num,
								tagname:alltagdata[g].tagname,
								tid:parseInt(g),
							})
						}
						self.tagData.alltagdata.catdata[self.tagData.checkedsFid].valloading = false;
						self.tagData.alltagdata.catdata[self.tagData.checkedsFid].finish = json.finish;
						
					}
				});
			}
		}
		
	},
	handleClickDelete(type,cid){//清除标签选项
		var self = this;
		// if(this.$refs.ScreenPopoverRef && this.$refs.ScreenPopoverRef.length){
		// 	for(var s in this.$refs.ScreenPopoverRef){
		// 		if(this.$refs.ScreenPopoverRef[s].showPopper){
		// 			this.$refs.ScreenPopoverRef[s].showPopper = false;
		// 		}
		// 	}
		// }
		if(type == 'empty'){
			this.FirstLoad = true;
			if(this.keyword){
				this.keyword = '';
				this.popoverSearch.keywordList = [];
				VuexStore.commit('SetimgParameter', {key:'keyword',val:''});
			}
			if(this.classify.text){
				this.classify.text = '';
				if(this.$refs.tree && this.$refs.tree.length){
					this.$refs.tree[0].setCheckedKeys([]);
				}
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
					height:this.ext.height,
					data:this.ext.data,
					loading:true
				};
				VuexStore.commit('SetParams',{key:'ext',val:''});
			}
			
			if(this.shape.txt){
				this.shape = {
					shape:[],
					width:this.shape.width,
					height:this.shape.height,
					txt:'',
					data:this.shape.data,
					loading:true
				};
				VuexStore.commit('SetParams',{key:'shape',val:''});
				VuexStore.commit('SetParams',{key:'shapesize',val:''});
			}
			
			if(this.GetParams.grade){
				this.grade = {
					grade:[],
					data:this.grade.data,
					loading:true
				};
				VuexStore.commit('SetParams',{key:'grade',val:''});
			}
			
			if(this.GetParams.btime){
				this.btime = {
					btime:'',
					datelinepicker:[],
					data:this.btime.data,
					loading:true
				};
				VuexStore.commit('SetParams',{key:'btime',val:''});
			}
			
			if(this.GetParams.dateline){
				this.dateline = {
					dateline:'',
					datelinepicker:[],
					data:this.dateline.data,
					loading:true
				};
				VuexStore.commit('SetParams',{key:'dateline',val:''});
			}
			
			if(this.GetParams.mtime){
				this.mtime = {
					mtime:'',
					datelinepicker:[],
					data:this.mtime.data,
					loading:true
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
				// if(this.tagData.checkedsFid == 'all'){
				// 	var data = JSON.parse(JSON.stringify(this.tagData.alltagdata.alltagdata));
				// 	this.tagData.Rightdata = data;
				// }else{
					// var data = JSON.parse(JSON.stringify(this.tagData.alltagdata.catdata[this.tagData.checkedsFid].tdatas));
					
					// this.tagData.Rightdata = data;
				// }
			}
			if(!$.isEmptyObject(this.modelParamsTag)){
				for(var t in this.modelParamsTag){
					this.modelParamsTag[t].search = '';
					this.modelParamsTag[t].text = '';
					this.modelParamsTag[t].value = [];
				}
				this.tagData.checkedsId = [];
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
					
					// if(this.tagData.checkedsFid == 'all'){
					// 	var data = JSON.parse(JSON.stringify(this.tagData.alltagdata.alltagdata));
					// 	this.tagData.Rightdata = data;
					// }else{
					// 	var data = JSON.parse(JSON.stringify(this.tagData.alltagdata.catdata[this.tagData.checkedsFid].tdatas));
					// 	this.tagData.Rightdata = data;
					// }
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
					if(this.$refs.tree && this.$refs.tree.length){
						this.$refs.tree[0].setCheckedKeys([]);
					}
					
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
						height:this.ext.height,
						data:this.ext.data,
						loading:false
					};
					VuexStore.commit('SetParams',{key:'ext',val:''});
				break;
				case 'shape':
					this.shape = {
						shape:[],
						width:this.shape.width,
						height:this.shape.height,
						txt:'',
						data:this.shape.data,
						loading:false
					};
					VuexStore.commit('SetParams',{key:'shape',val:''});
					VuexStore.commit('SetParams',{key:'shapesize',val:''});
				break;
				case 'grade':
					this.grade = {
						grade:[],
						data:this.grade.data,
						loading:false
					};
					VuexStore.commit('SetParams',{key:'grade',val:''});
				break;
				case 'btime':
					this.btime = {
						btime:'',
						datelinepicker:[],
						data:this.btime.data,
						loading:false
					};
					VuexStore.commit('SetParams',{key:'btime',val:''});
				break;
				case 'mtime':
					this.mtime = {
						mtime:'',
						datelinepicker:[],
						data:this.mtime.data,
						loading:false
					};
					VuexStore.commit('SetParams',{key:'mtime',val:''});
				break;
				case 'dateline':
					this.dateline = {
						dateline:'',
						datelinepicker:[],
						data:this.dateline.data,
						loading:false
					};
					VuexStore.commit('SetParams',{key:'dateline',val:''});
				break;
				case 'grouptag':
					var tags = [];
					for(var t in this.modelParamsTag){
						if(t!=cid){
							tags.push.apply(tags,this.modelParamsTag[t].value);
						}
					}
					VuexStore.commit('SetParams',{key:'tag',val:tags.join(',')});
					this.modelParamsTag[cid].search = '';
					this.modelParamsTag[cid].text = '';
					this.modelParamsTag[cid].value = [];
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
			str['skey']='tag';
			str['cid']=cid;
			str['page'] = self.modelParamsTag[cid].page;
			
			if(self.modelParamsTag[cid].search){
				str['tagkeyword'] = self.modelParamsTag[cid].search;
			}
			
		}
		if(type == 'tag'){
			if(self.tagData.checkedsFid == 'all'){
				str['cid'] =0;
			}else{
				str['cid'] = self.tagData.checkedsFid;
			}
			if(self.tagData.alltagdata.catdata[self.tagData.checkedsFid]){
				str['page'] = self.tagData.alltagdata.catdata[self.tagData.checkedsFid].page;
			}
			if(self.tagData.search){
				str['tagkeyword'] = self.tagData.search;
			}
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
				vtag.push.apply(vtag,this.modelParamsTag[t].value);
			}
		}
		if(vtag.length){
			str['tag'] = vtag.join(',');
		}
		$.ajax({
			'url' : MOD_URL+'&op=ajax&operation=searchmenu_num',
			'type' : "post",
			'data' : str,
			'async' : false,
			'dataType': "json",
			'success' : function(data) {
				param = data;
			}
		});
		return param;
	},
	GetScreenDatanum(){
		var self = this;
		var type = 'tag';
		var str = {
			skey:'tag'
		};

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
				vtag.push.apply(vtag,this.modelParamsTag[t].value);
			}
		}
		if(vtag.length){
			str['tag'] = vtag.join(',');
		}
		$.ajax({
			'url' : MOD_URL+'&op=ajax&operation=search_menu',
			'type' : "post",
			'data' : str,
			'dataType': "json",
			'success' : function(data) {
				if(data.catdata){
					var catdata = data.catdata;
					for(var g in catdata){
						var item = catdata[g];
						if(parseInt(item.cid) == 0){
							self.tagData.alltagdata.catdata['all']['num'] = item.num;
						}else if(parseInt(item.cid) == -1){
							self.tagData.alltagdata.catdata[-1]['num'] = item.num;
						}else{
							self.tagData.alltagdata.catdata[item.cid]['num'] = item.num;
						}
					}
				}
			}
		});
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