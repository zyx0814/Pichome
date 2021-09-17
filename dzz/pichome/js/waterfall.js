var ImgWaterfall = {
	WaterfallInit(type){
		var self = this;
		if(type == 'append'){
			self.WaterfallImgCompute(type);
		}else if(type == 'refresh'){
			self.WaterfallParam.imgArr = [];
			self.WaterfallImgCompute();
		}else{
			self.WaterfallImgCompute();
		}
	},
	WaterfallImgCompute(type){
		var self = this;
		if(type == 'append'){
			// self.WaterfallParam.perpage += self.GetimgParameter.perpage;
		}else{
			self.WaterfallParam.perpage = 0;
			var boxWidth = $('#waterfallCenter').width();
			self.WaterfallParam.columns = boxWidth/self.WaterfallParam.imgWidth;
			var OtherHeight = 0
			if(self.InformationShow.indexOf('name')>-1 || self.InformationShow.indexOf('extension')>-1){
				OtherHeight += 45;
			}
			if(self.InformationShow.indexOf('other')>-1){
				OtherHeight += 20;
			}
			self.WaterfallParam.OtherHeight = OtherHeight;
		}
		var arr = {};
		for (var o = self.WaterfallParam.perpage; o < self.imgdatas.length; o++){
			var rid = self.imgdatas[o]['rid'];
			arr[rid] = JSON.parse(JSON.stringify(self.imgdatas[o]));
			
			var h = 236/parseInt(arr[rid].width)*parseInt(arr[rid].height)>120?236/parseInt(arr[rid].width)*parseInt(arr[rid].height):120;
			arr[rid]['pheight'] = h;
			arr[rid]['pwidth'] = 252;
			
			if (o < self.WaterfallParam.columns) {
				arr[rid]['ptop'] = 0;
				arr[rid]['pleft'] = self.WaterfallParam.imgWidth*o;
				arr[rid]['pbottom'] = arr[rid]['ptop']+arr[rid]['pheight']+self.WaterfallParam.Margin+self.WaterfallParam.OtherHeight;
				var imgArr = JSON.parse(JSON.stringify(arr[rid]));
				self.WaterfallParam.imgArr.push(imgArr);
				
			}else{
				var minHeight = self.WaterfallParam.imgArr[0]['pbottom'];
				var index = 0;
				for (var j = 0; j < self.WaterfallParam.imgArr.length; j++) {
					if (minHeight > self.WaterfallParam.imgArr[j]['pbottom']) {
						minHeight = self.WaterfallParam.imgArr[j]['pbottom'];
						index = j;
					}
				}
				arr[rid]['ptop'] = JSON.parse(JSON.stringify(self.WaterfallParam.imgArr[index]['pbottom']));
				arr[rid]['pleft'] = JSON.parse(JSON.stringify(self.WaterfallParam.imgArr[index]['pleft']));
				arr[rid]['pbottom'] = arr[rid]['ptop']+arr[rid]['pheight']+self.WaterfallParam.Margin+self.WaterfallParam.OtherHeight;

				var imgArr = JSON.parse(JSON.stringify(arr[rid]));
				self.WaterfallParam.imgArr[index] = imgArr;
				
			}
		}
			
		var count = Object.keys(arr).length
		self.WaterfallParam.perpage += count;
		if(self.GetScrollAppend){
			var StoreImgdatas = JSON.parse(JSON.stringify(self.StoreImgdatas));
			var result = $.extend(StoreImgdatas, arr);
			self.StoreImgdatas = result;
		}else{
			self.StoreImgdatas = arr;
		}
	
		self.WaterfallhandleScroll();
		var fminHeight = self.WaterfallParam.imgArr[0]['pbottom'];
		for (var j = 0; j < self.WaterfallParam.imgArr.length; j++) {
			if (fminHeight < self.WaterfallParam.imgArr[j]['pbottom']) {
				fminHeight = self.WaterfallParam.imgArr[j]['pbottom'];
			}
		}
		self.WaterfallParam.boxHeight = fminHeight+40;
		$('#waterfallCenter').css({
			height:self.WaterfallParam.boxHeight+'px'
		});
		$('.imgmargin').css({
			height:self.WaterfallParam.boxHeight+'px'
		});
	},
	WaterfallhandleScroll(){
		var self = this;
		var H = jQuery('.body_scroll>.el-scrollbar__wrap')[0].clientHeight;//获取可视区域高度
		var S = jQuery('.body_scroll>.el-scrollbar__wrap')[0].scrollTop;
		// var data = JSON.parse(JSON.stringify(self.NewImgdatas));
		var arr = [];
		
		self.NewImgdatas = {};
		for(var o in self.StoreImgdatas){
			var item = JSON.parse(JSON.stringify(self.StoreImgdatas[o]));
			if ((H+S) >= item.ptop && S<item.pbottom) {
				self.NewImgdatas[item.rid] = item;
			}
		}
		
	},
};