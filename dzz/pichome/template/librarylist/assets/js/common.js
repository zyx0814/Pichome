function debounce(fun, delay) {
	var time;
	return function(args) {
		var that = this;
		var _args = args;
		if (time) clearTimeout(time);
		time = setTimeout(function() {
			fun.call(that, _args)
		}, delay)
	}
};
function URLdecode(str) {
	var ret = "";
	for(var i=0;i<str.length;i++) {
		var chr = str.charAt(i);
		if(chr == "+") {
			ret += " ";
		}else if(chr=="%") {
			var asc = str.substring(i+1,i+3);
			if(parseInt("0x"+asc)>0x7f) {
				ret += decodeURI("%"+ str.substring(i+1,i+9));
				i += 8;
			}else {
				ret += String.fromCharCode(parseInt("0x"+asc));
				i += 2;
			}
		}else {
			ret += chr;
		}
	}
	return ret;
};
function handleSize(size) {
	if (!size)
		return "0K";
	var num = 1024.00; //byte
	if (size < num)
		return size + "B";
	if (size < Math.pow(num, 2))
		return (size / num).toFixed(2) + "K"; //kb
	if (size < Math.pow(num, 3))
		return (size / Math.pow(num, 2)).toFixed(2) + "M"; //M
	if (size < Math.pow(num, 4))
		return (size / Math.pow(num, 3)).toFixed(2) + "G"; //G
	return (size / Math.pow(num, 4)).toFixed(2) + "T"; //T
};
function GetDateVal(type){
	var str = '';
	var start = new Date();
	var end = new Date();
	switch(type){
		case __lang.filter_range_day:
		break;
		case __lang.filter_range_yesterday:
			end.setTime(end.getTime() - 3600 * 1000 * 24);
			start.setTime(start.getTime() - 3600 * 1000 * 24);
		break;
		case __lang.filter_range_week:
			start.setTime(start.getTime() - 3600 * 1000 * 24 * 6);
		break;
		case __lang.filter_range_month:
			start.setTime(start.getTime() - 3600 * 1000 * 24 * 29);
		break;
		case __lang.filter_range_month3:
			start.setTime(start.getTime() - 3600 * 1000 * 24 * 89);
		break;
		case __lang.filter_range_year:
			start.setTime(start.getTime() - 3600 * 1000 * 24 * 364);
		break;
	}
	str = start.getFullYear()+'-'+(start.getMonth()+1)+'-'+start.getDate()+'_'+end.getFullYear()+'-'+(end.getMonth()+1)+'-'+end.getDate();
	return str;
}

function CopyTxt(item,text){
	var input = document.createElement('input'); input.setAttribute('id', 'copyInput');
	input.setAttribute('value', text);
	document.getElementsByTagName('body')[0].appendChild(input);
	document.getElementById('copyInput').select();
	document.execCommand('copy')
	item.$message({
	  message: __lang.copy_clipboard,
	  type: 'success'
	});
	document.getElementById('copyInput').remove();
};
function setcookie(cookieName, cookieValue, seconds, path, domain, secure) {
	if(cookieValue == '' || seconds < 0) {
		cookieValue = '';
		seconds = -2592000;
	}
	if(seconds) {
		var expires = new Date();
		expires.setTime(expires.getTime() + seconds * 1000);
	}
	domain = !domain ? cookiedomain : domain;
	path = !path ? cookiepath : path;
	document.cookie = escape(cookiepre + cookieName) + '=' + escape(cookieValue)
		+ (expires ? '; expires=' + expires.toGMTString() : '')
		+ (path ? '; path=' + path : '/')
		+ (domain ? '; domain=' + domain : '')
		+ (secure ? '; secure' : '');
}