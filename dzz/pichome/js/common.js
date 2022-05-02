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

function GetDateVal(type){
	var str = '';
	var start = new Date();
	var end = new Date();
	switch(type){
		case '今日':
		break;
		case '昨日':
			end.setTime(end.getTime() - 3600 * 1000 * 24);
			start.setTime(start.getTime() - 3600 * 1000 * 24);
		break;
		case '最近7日':
			start.setTime(start.getTime() - 3600 * 1000 * 24 * 6);
		break;
		case '最近30日':
			start.setTime(start.getTime() - 3600 * 1000 * 24 * 29);
		break;
		case '最近90日':
			start.setTime(start.getTime() - 3600 * 1000 * 24 * 89);
		break;
		case '最近365日':
			start.setTime(start.getTime() - 3600 * 1000 * 24 * 364);
		break;
	}
	str = start.getFullYear()+'-'+(start.getMonth()+1)+'-'+start.getDate()+'_'+end.getFullYear()+'-'+(end.getMonth()+1)+'-'+end.getDate();
	return str;
}

function CopyTxt(item,text){
	console.log(111)
	var input = document.createElement('input'); input.setAttribute('id', 'copyInput');
	input.setAttribute('value', text);
	document.getElementsByTagName('body')[0].appendChild(input);
	document.getElementById('copyInput').select();
	document.execCommand('copy')
	item.$message({
	  message: '成功复制到剪切板',
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