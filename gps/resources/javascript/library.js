var net=new Object();

net.READY_STATE_UNINITIALIZED=0;
net.READY_STATE_LOADING=1;
net.READY_STATE_LOADED=2;
net.READY_STATE_INTERACTIVE=3;
net.READY_STATE_COMPLETE=4;

/*--- content loader object for cross-browser requests ---*/
net.ContentLoader = function(url,onload,onerror,method,params,contentType){
	this.req = null;
	this.onload=onload;
	this.onerror=(onerror) ? onerror : this.defaultError;
	this.loadXMLDoc(url,method,params,contentType);
}

net.ContentLoader.prototype = {
loadXMLDoc:function(url,method,params,contentType) {

		if (!method) {
			method = "GET";
		}

		if (!contentType && method == "POST") {
			contentType = 'application/x-www-form-urlencoded';
		}

		if (window.XMLHttpRequest) {
			this.req = new XMLHttpRequest();
		} else if (window.ActiveXObject) {
			this.req = new ActiveXObject("Microsoft.XMLHTTP");
		}

		if (this.req) {
			try {
				var loader = this;
				this.req.onreadystatechange = function() {
					loader.onReadyState.call(loader);
				}
				this.req.open(method, url, true);
				if (contentType) {
					this.req.setRequestHeader('Content-Type', contentType);
				}
				this.req.send(params);
			} catch (err) {
				this.onerror.call(this);
			}
		}

	},

	onReadyState:function() {

		var req = this.req;
		var ready = req.readyState;

		if (ready > 1) {

			if (ready == net.READY_STATE_COMPLETE) {
				var httpStatus = req.status;
				if (httpStatus == 200 || httpStatus == 0) {
					//alert(this.req.responseText);
					this.onload.call(this);
				}else{
					this.onerror.call(this);
				}
			}
		}

	},

	defaultError:function() {

		alert("error fetching data!"
			+ "\n\nreadyState:" + this.req.readyState
			+ "\nstatus: " + this.req.status
			+ "\nheaders: " + this.req.getAllResponseHeaders()
			+ "\ndata:\n" + this.req.responseText
		);

	}

}

//Get element by id shorthand
function $(element) {
	var elementobj = document.getElementById(element);
	return elementobj;
}

//Get last index in array
function getlastindex(array) {
	//Get last index
	var lastindex = undefined;
	for (var i in array) {
		lastindex = i;
	}
	return lastindex;
}

//List variables in array
function listelement(ele) {
	var data = "";
	for (i in ele) {
		data += i + " => " + ele[i] + "<br />";
	}
	alert(data);
}

//In array
function inarray(array, value) {
	for (var i=0; i < array.length; i++) {
		if (array[i] === value) {
			return true;
		}
	}
	return false
}

//Get selected radio id
function radiogetselected(radioname) {

	var chkboxgrpele = document.getElementsByName(radioname);
	var chkboxtotal = chkboxgrpele.length;
	for (i=0; i<chkboxtotal; i++) {
		if (chkboxgrpele[i].checked == true) {
			return chkboxgrpele[i].value;
		}
	}
	return false;

}

//Select specified radio by value
function radioselect(radioname, radiovalue) {

	var chkboxgrpele = document.getElementsByName(radioname);
	var chkboxtotal = chkboxgrpele.length;
	for (i=0; i<chkboxtotal; i++) {
		if (chkboxgrpele[i].value == radiovalue) {
			chkboxgrpele[i].checked = true;
			return true;
		}
	}

	return false;

}

//Popup
function popupwindow(url, target, width, height) {
	newwindow = window.open(url, target, 'height=' + height + ',width=' + width + ', toolbar=0, scrollbars=1, location=0, status=0, menubar=0, resizable=1');
	if (window.focus) { newwindow.focus(); }
	return false;
}

function popuptandc(url) {
	popupwindow(url, "_blank", 600, 400);
}
