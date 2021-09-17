(function($) {
	$.fn.rowGrid = function(options) {
		return this.each(function() {
			$this = $(this);
			if(options == 'appended-first') {
				options = $this.data('grid-options');
				var $firstRow = $this.find('.' + options.firstItemClass).first();
				var items = $firstRow.prevAll(options.itemSelector);
				layout(this, options, items);
			}else if(options === 'appended') {
				options = $this.data('grid-options');
				var $lastRow = $this.children('.' + options.lastRowClass);
				var items = $lastRow.nextAll(options.itemSelector).add($lastRow);
				layout(this, options, items);
			}else if(options == 'refresh') {
				options = $this.data('grid-options');
				layout(this, options);
				if(options.resize) {
					$(window).off('resize.rowGrid').on('resize.rowGrid', {
						container: this
					}, function(event) {
						layout(event.data.container, options);
					});
				}
			} else {
				options = $.extend({}, $.fn.rowGrid.defaults, options);
				$this.data('grid-options', options);
				layout(this, options);
		
				if(options.resize) {
					$(window).on('resize.rowGrid', {
						container: this
					}, function(event) {
		
						layout(event.data.container, options);
					});
				}
			}
		});
	};

	$.fn.rowGrid.defaults = {
		
		minMargin: 10,
		maxMargin: 10,
		resize: true,
		lastRowClass: 'last-row',
		firstItemClass: 'null',
		imgNumber: 0,
	};

	function layout(container, options, items) {
		var rowWidth = 0,
			oldimgNumber = 0,
			rowElems = [],
			items = jQuery.makeArray(items || container.querySelectorAll(options.itemSelector)),
			itemsSize = items.length;
		// read
		var containerBoundingRect = container.getBoundingClientRect();
		var containerWidth = Math.floor(containerBoundingRect.right - containerBoundingRect.left) - parseFloat($(
			container).css('padding-left')) - parseFloat($(container).css('padding-right'));
		var itemAttrs = [];
		var theImage, w, h;
		for (var i = 0; i < itemsSize; ++i) {
			theImage = $(items[i]).get(0); //.getElementsByTagName('img')[1];
			if (!theImage) {
				items.splice(i, 1);
				--i;
				--itemsSize;
				continue;
			}
			var item = $(items[i]);
			w = parseInt(item.data('width'));
			h = parseInt(item.data('height'));
			if (w < 360 || h < 360) {
				item.addClass('resize');
			}
			var r = w / h;
			var r1 = h / w;
			if (h > 360) {
				h = 360;
				w = r * h;
			} else if (w < 360) {
				w = 360;
				h = 360;
			}
			if (w <= 360 || h <= 360) {
				// item.find('.el-image__inner').addClass('resize');
			}
			itemAttrs[i] = {
				width: w,
				height: h
			};
		}
		itemsSize = items.length;
		for (var index = 0; index < itemsSize; ++index) {
			
			if (items[index].classList) {
				items[index].classList.remove(options.firstItemClass);
				items[index].classList.remove(options.lastRowClass);
			} else {
				// IE <10
				items[index].className = items[index].className.replace(new RegExp('(^|\\b)' + options.firstItemClass + '|' + options.lastRowClass + '(\\b|$)', 'gi'), ' ');
			}
			rowWidth += itemAttrs[index].width;
			rowElems.push(items[index]);
			// check if it is the last element
			if (index === itemsSize - 1) {
				
				for (var rowElemIndex = 0; rowElemIndex < rowElems.length; rowElemIndex++) {
					// if first element in row
					if (rowElemIndex === 0) {
						oldimgNumber++;
						rowElems[rowElemIndex].className += ' ' + options.lastRowClass;
					}
					if (options.imgNumber > 0) {
						if (oldimgNumber > options.imgNumber) {
							rowElems[rowElemIndex].classList.add('hide')
						} else {
							rowElems[rowElemIndex].classList.remove('hide')
						}

					}
					rowElems[rowElemIndex].style.cssText =
						'width: ' + itemAttrs[index + parseInt(rowElemIndex) - rowElems.length + 1].width + 'px;' +
						'height: ' + itemAttrs[index + parseInt(rowElemIndex) - rowElems.length + 1].height + 'px;';
					$(rowElems[rowElemIndex]).closest('.Icoblock-box').css({
						'width': itemAttrs[index + parseInt(rowElemIndex) - rowElems.length + 1].width + 'px',
						'margin-right':((rowElemIndex < rowElems.length - 1) ? options.minMargin : 0) + 'px'
					})
					// rowElems[rowElemIndex].parentNode.style.cssText =
					// 	'width: ' + newWidth + 'px;' +
					// 	'margin-right:' + Math.floor((rowElemIndex < rowElems.length - 1) ? rowMargin : 0) + 'px';
				}
			}
			
			// check whether width of row is too high
			if (rowWidth + options.maxMargin * (rowElems.length - 1) > containerWidth) {
				var diff = rowWidth + options.maxMargin * (rowElems.length - 1) - containerWidth;
				var nrOfElems = rowElems.length;
				// change margin
				var maxSave = (options.maxMargin - options.minMargin) * (nrOfElems - 1);
				if (maxSave < diff) {
					var rowMargin = options.minMargin;
					diff -= (options.maxMargin - options.minMargin) * (nrOfElems - 1);
				} else {
					var rowMargin = options.maxMargin - diff / (nrOfElems - 1);
					diff = 0;
				}
				var rowElem,
					widthDiff = 0,
					maxNewHeight = 0,
					newHeights = [];
				for (var rowElemIndex = 0; rowElemIndex < rowElems.length; rowElemIndex++) {
					rowElem = rowElems[rowElemIndex];
					var rowElemWidth = itemAttrs[index + parseInt(rowElemIndex) - rowElems.length + 1].width;
					var newWidth = rowElemWidth - (rowElemWidth / rowWidth) * diff;
					var newHeight = Math.round(itemAttrs[index + parseInt(rowElemIndex) - rowElems.length + 1].height * (newWidth / rowElemWidth));
					if (widthDiff + 1 - newWidth % 1 >= 0.5) {
						widthDiff -= newWidth % 1;
						newWidth = Math.floor(newWidth);
					} else {
						widthDiff += 1 - newWidth % 1;
						newWidth = Math.ceil(newWidth);
					}
					newHeights.push(newHeight);
					
					if (newHeight > maxNewHeight) maxNewHeight = newHeight;
					rowElem.style.cssText =
						'width: ' + newWidth + 'px;' +
						'height: ' + newHeight + 'px;';
					$(rowElems[rowElemIndex]).closest('.Icoblock-box').css({
						'width': newWidth+'px',
						'margin-right':Math.floor((rowElemIndex < rowElems.length - 1) ? rowMargin : 0) + 'px'
					})
					// rowElems[rowElemIndex].parentNode.style.cssText =
					// 	'width: ' + newWidth + 'px;' +
					// 	'margin-right:' + Math.floor((rowElemIndex < rowElems.length - 1) ? rowMargin : 0) + 'px';
					
					if (rowElemIndex === 0) {
						oldimgNumber++;
						rowElem.className += ' ' + options.firstItemClass;
					}
					rowElem.classList.add('floor'+(oldimgNumber+1))
					if (options.imgNumber > 0) {
						if (oldimgNumber > options.imgNumber) {
							rowElem.classList.add('hide')
						} else {
							rowElem.classList.remove('hide')
						}

					}
					
				}
				for (var j = 0; j < newHeights.length; j++) {
					if (newHeights[j] < maxNewHeight) rowElems[j].style.height = maxNewHeight + 'px';
				}
				rowElems = [];
				rowWidth = 0;
			}
		}
	}
})(jQuery);
