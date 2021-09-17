var ImgRowGrid = {
	ImgRowGridInit(type){
		var self = this;
		if(type == 'append') {
			self.ImgRowGridCompute(type);
		}else if(type == 'refresh'){
			
			self.ImgRowGridCompute();
		}else{
			self.ImgRowGridCompute();
		}
	},
	ImgRowGridCompute(type){
		var self = this;
		var Margin = self.RowgridParam.Margin
		if(type == 'append'){
			// self.RowgridParam.perpage += self.GetimgParameter.perpage;
		}else{
			self.RowgridParam.maxNewtop = 0;
			self.RowgridParam.maxNewLeft =  0;
			self.RowgridParam.rowElems =  [];
			self.RowgridParam.rowWidth =  0;
			self.RowgridParam.perpage = 0;
			self.SurplusImgdatas = [];
			var OtherHeight = 0
			if(self.InformationShow.indexOf('name')>-1 || self.InformationShow.indexOf('extension')>-1){
				OtherHeight += 45;
			}
			if(self.InformationShow.indexOf('other')>-1){
				OtherHeight += 20;
			}
			self.RowgridParam.OtherHeight = OtherHeight;
		}
		
		var arr = {};
		if(Object.keys(self.SurplusImgdatas).length){
			for(var s in self.SurplusImgdatas){
				arr[s] = JSON.parse(JSON.stringify(self.SurplusImgdatas[s]));
			}
			self.RowgridParam.rowWidth = 0;
			self.RowgridParam.rowElems = [];
			self.RowgridParam.maxNewLeft =  0;
		}
		
		for (var i = self.RowgridParam.perpage; i < self.imgdatas.length; ++i) {
			var str = JSON.parse(JSON.stringify(self.imgdatas[i]));
			var rid = str.rid;
			arr[rid] = str;
			var w = parseInt(arr[rid]['width']);
			var h = parseInt(arr[rid]['height']);
			var r = w / h;
			var r1 = h / w;
			if (h > 360) {
				h = 360;
				w = r * h;
			} else if (w < 360) {
				w = 360;
				h = 360;
			}
			arr[rid]['pwidth'] = w;
			arr[rid]['pheight'] = h;
		}

		
		var containerWidth = $('.rowGrids-box').width();
		
		
		var count = Object.keys(arr).length-Object.keys(self.SurplusImgdatas).length;
		
		self.RowgridParam.perpage += count;
		for(var index in arr){
			var item = arr[index];
			self.RowgridParam.rowWidth += item['pwidth'];
			self.RowgridParam.rowElems.push(index);
			var fdiiff = self.RowgridParam.rowWidth + Margin * (self.RowgridParam.rowElems.length - 1);
			var diff = fdiiff - containerWidth;//多出来的宽度
			var nrOfElems = self.RowgridParam.rowElems.length;//一行的个数
			if (fdiiff > containerWidth){
				var widthDiff = 0,maxNewHeight = 0;
				for(var rowElemIndex in self.RowgridParam.rowElems){
					var rid = self.RowgridParam.rowElems[rowElemIndex];
					// if(arr[rid]){
						var rowElemWidth = arr[rid]['pwidth'];//获取一行每个都宽度
						var rowElemHeight = arr[rid]['pheight'];//获取一行每个都宽度
					// }else{
						
					// 	var rowElemWidth = self.SurplusImgdatas[rid]['pwidth'];//获取一行每个都宽度
					// 	var rowElemHeight = self.SurplusImgdatas[rid]['pheight'];//获取一行每个都宽度
					// }
					var newWidth = rowElemWidth - (rowElemWidth / self.RowgridParam.rowWidth) * diff;//新宽度
					var newHeight = Math.round(rowElemHeight * (newWidth / rowElemWidth));//新高度
					if (widthDiff + 1 - newWidth % 1 >= 0.5) {
						widthDiff -= newWidth % 1;
						newWidth = Math.floor(newWidth);
					} else {
						widthDiff += 1 - newWidth % 1;
						newWidth = Math.ceil(newWidth);
					}
					if (newHeight > maxNewHeight){
						maxNewHeight = newHeight;
					}
					arr[rid]['pwidth'] = newWidth;
					arr[rid]['pheight'] = maxNewHeight;
					arr[rid]['ptop'] = self.RowgridParam.maxNewtop;
					arr[rid]['pbottom'] = self.RowgridParam.maxNewtop+maxNewHeight+self.RowgridParam.OtherHeight;
					if(rowElemIndex > 0){
						arr[rid]['pleft'] = self.RowgridParam.maxNewLeft;
					}else{
						arr[rid]['pleft'] = 0;
					}
					self.RowgridParam.maxNewLeft += newWidth + Margin;
					
				}
				for(var frowElemIndex in self.RowgridParam.rowElems){
					var rowElem = self.RowgridParam.rowElems[frowElemIndex];
					var height = arr[rowElem]['pheight'];//获取一行每个都宽度
					if(height<maxNewHeight){
						// arr[rowElem]['pbottom'] = self.RowgridParam.maxNewtop+maxNewHeight+self.RowgridParam.OtherHeight;
						arr[rowElem]['pbottom'] += maxNewHeight-height;
						arr[rowElem]['pheight'] = maxNewHeight;
					}
				}
				
				self.RowgridParam.maxNewtop += maxNewHeight + Margin +self.RowgridParam.OtherHeight
				self.RowgridParam.rowElems = [];
				self.RowgridParam.rowWidth = 0;
				self.RowgridParam.maxNewLeft = 0;
			}
			
		}
		var surplusheight = 0;
		self.SurplusImgdatas = {};
		if (self.RowgridParam.rowElems.length) {
			var maxNewHeight = 0;
			for(var f in self.RowgridParam.rowElems){
				var rowElem = self.RowgridParam.rowElems[f];
				var rowElemWidth = arr[rowElem].pwidth;//获取一行每个都宽度
				var rowElemHeight = arr[rowElem].pheight;//获取一行每个都宽度
				arr[rowElem]['pwidth'] = rowElemWidth;
				arr[rowElem]['pheight'] = rowElemHeight;
				arr[rowElem]['ptop'] = self.RowgridParam.maxNewtop;
				arr[rowElem]['pbottom'] = self.RowgridParam.maxNewtop+rowElemHeight+self.RowgridParam.OtherHeight;
				if (rowElemHeight > maxNewHeight){
					maxNewHeight = rowElemHeight;
				}
				if(f > 0){
					arr[rowElem]['pleft'] = self.RowgridParam.maxNewLeft;
				}else{
					arr[rowElem]['pleft'] = 0;
				}
				
				self.RowgridParam.maxNewLeft += rowElemWidth + Margin;
			}
			for(var ff in self.RowgridParam.rowElems){
				var rowElem = self.RowgridParam.rowElems[ff];
				var height = arr[rowElem]['pheight'];//获取一行每个都宽度
				if(height<maxNewHeight){
					arr[rowElem]['pbottom'] += maxNewHeight-height;
					arr[rowElem]['pheight'] = maxNewHeight;
				}
				self.SurplusImgdatas[rowElem] = arr[rowElem];
			}
			surplusheight = maxNewHeight+self.RowgridParam.OtherHeight+Margin;
			self.RowgridParam.maxNewLeft = 0;
		}
		
		$('.rowGrids-box').height(self.RowgridParam.maxNewtop+40+surplusheight);
		if(self.GetScrollAppend){
			for(var st in arr){
				if(self.StoreImgdatas[st]){
					self.StoreImgdatas[st] = JSON.parse(JSON.stringify(arr[st]));
					delete arr[st];
				}
			}
			var StoreImgdatas = JSON.parse(JSON.stringify(self.StoreImgdatas));
			var result = $.extend(StoreImgdatas, arr);
			self.StoreImgdatas = result;
		}else{
			self.StoreImgdatas = arr;
		}
		
		self.ImgRowGridhandleScroll();
	},
	ImgRowGridhandleScroll(){
		var self = this;
		var H = jQuery('.body_scroll>.el-scrollbar__wrap')[0].clientHeight;//获取可视区域高度
		var S = jQuery('.body_scroll>.el-scrollbar__wrap')[0].scrollTop;
		self.NewImgdatas = {};
		for(var o in self.StoreImgdatas){
			var item = JSON.parse(JSON.stringify(self.StoreImgdatas[o]));
			if ((H+S) >= item.ptop && S<item.pbottom) {
				self.NewImgdatas[item.rid] = item;
			}
		}
		
	},
};