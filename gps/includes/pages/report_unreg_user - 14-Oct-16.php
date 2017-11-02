<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Status Since Last Active</title>
<script language="javascript" src="includes/pages/jquery-1.9.1.js"></script>
<script language="javascript">

	$( document ).ready(function() {
    	document.getElementById('checkdate').focus();
	});
	
	function getStatus(obj)
	{
		var days = obj[obj.selectedIndex].value;
	//	alert("Hello "+days);
		$("#lastActiveStatus").load("includes/pages/status_since_last_active.php",{checkdate:days});
	}
	
	function gohome()
	{
		//alert("Go home");
		$(location).attr('href', 'http://jantv.in/gps/')
	}

	function goBack() {
		window.history.back();
	}
	
	function popupRegisterWindow(event,id,mobileno,txtUserName) // obj,messagebox,messagediv,mobileno,name)
	{
//alert(id+" "+mobileno+"   - "+txtUserName);
/*		var message = document.getElementById(obj).value;
*/		//alert(message);

		document.getElementById("registermessagediv").innerHTML = "";
		document.getElementById("txtUserName").value = "";
		document.getElementById('mobilenopos').innerHTML = "";		 

		document.getElementById("unreg_userid").value = id;
		document.getElementById("hiddenMobileNo").value = mobileno;		
		document.getElementById('mobilenopos').innerHTML = mobileno;
		
		var x = event.clientX;
		var y = event.clientY;

		//alert("HELLO x = "+x+"  y = "+y);
		var d = document.getElementById('registerPopup');
		d.style.position = "absolute";
		d.style.left = (x-330)+'px';
		d.style.top= (y-135)+'px';
		d.style.display='block'; 
	}	
	
	function CloseRegisterPopup()
	{
		var d = document.getElementById('registerPopup');
		d.style.display='none'; 		
	}	
		
	function registerit()
	{
		//alert(id+" "+mobileno);
		//alert("manoj");
		var id = document.getElementById('unreg_userid').value;	
		var userName = document.getElementById('txtUserName').value;
		var mobileno = document.getElementById('hiddenMobileNo').value;		
	//	alert("----"+mobileno);
		var numpat = "/^[0-9|+]\d{0,5}$/";
		
		var lenmob = mobileno.length;
		
		var lenuser = (userName.trim()).length;
		
		//var useridRegex = /^[0-9]\d{9,14}$/;/^[0-9|+]\d{9,14}$/;
		var errcount = 0;
		var msg = "";
		
		if(mobileno!="")
		{
			if(isNaN(mobileno))
			{
				++errcount;
				msg+=errcount+". Mobile No should have digits only.\n";
			}
			if(lenmob !=10)
			{
				++errcount;
				msg+=errcount+". Mobile No should have 10 digits.\n";
			}
		}
		else
		{
			++errcount;
			msg+=errcount+". Mobile No can't be empty.\n";				
		}
		
		if(lenuser == 0)
		{
			++errcount;
			msg+=errcount+". User Name can't be empty.\n";
		}
			
		if(errcount!=0)
		{
			alert(msg);
		}
		else
		{
			//alert("OK");
			$("#registermessagediv").load("includes/pages/registerit.php", {id:id, userentry:mobileno,userName:userName}, function(){
				$("#loadUsers").load("includes/pages/getUnregUsers.php");
				$("#registerPopup").fadeOut(5000);			
			});
		}			
	}
</script>
<style>
body{
	font-family:Arial, Helvetica, sans-serif;
	background: url(background.jpg);
	margin:0 auto;
	width:100%;
}
a:link {
	color: #666;
	font-weight: bold;
	text-decoration:none;
}
a:visited {
	color: #666;
	font-weight:bold;
	text-decoration:none;
}
a:active,
a:hover {
	color: #bd5a35;
	text-decoration:underline;
}


/*
Table Style - This is what you want
------------------------------------------------------------------ */
table a:link {
	color: #666;
	font-weight: bold;
	text-decoration:none;
}
table a:visited {
	color: #999999;
	font-weight:bold;
	text-decoration:none;
}
table a:active,
table a:hover {
	color: #bd5a35;
	text-decoration:underline;
}
table {
	font-family:Arial, Helvetica, sans-serif;
	color:#222;
	font-size:12px;
	/*text-shadow: 1px 1px 0px #eee;*/
	background:#eaebec;
	margin:20px;
	border:#ccc 1px solid;

	-moz-border-radius:3px;
	-webkit-border-radius:3px;
	border-radius:3px;
	border-collapse:collapse;
	-moz-box-shadow: 0 1px 2px #d1d1d1;
	-webkit-box-shadow: 0 1px 2px #d1d1d1;
	box-shadow: 0 1px 2px #d1d1d1;
}
table th {
	padding:8px 8px 12px 10px;
	border-top:1px solid #fafafa;
	border-bottom:1px solid #e0e0e0;
	color:#4387fd;
	font-size:14px;
	letter-spacing:0px;
	background: #babafe;
	background: -webkit-gradient(linear, left top, left bottom, from(#fdfdff), to(#c1c1ff));
	background: -moz-linear-gradient(top,  #cbcbcb,  #ebebeb);
/*    background: -webkit-linear-gradient(top,#4387fd,#4683ea); */
}
table th:first-child{
	text-align: left;
	padding-left:20px;
}
table tr:first-child th:first-child{
	-moz-border-radius-topleft:3px;
	-webkit-border-top-left-radius:3px;
	border-top-left-radius:3px;
}
table tr:first-child th:last-child{
	-moz-border-radius-topright:3px;
	-webkit-border-top-right-radius:3px;
	border-top-right-radius:3px;
}
table tr{
	text-align: center;
	padding-left:20px;
}
table tr td:first-child{
	text-align: left;
	padding-left:20px;
	border-left: 0;
}
table tr td {
	padding:6px;
	border-top: 1px solid #ffffff;
	border-bottom:1px solid #e0e0e0;
	border-left: 1px solid #e0e0e0;
	
/*	background: #fafafa;
	background: -webkit-gradient(linear, left top, left bottom, from(#fbfbfb), to(#fafafa));
	background: -moz-linear-gradient(top,  #fbfbfb,  #fafafa);*/
}
table tr.even td{
/*	background: #f6f6f6;
	background: -webkit-gradient(linear, left top, left bottom, from(#f8f8f8), to(#f6f6f6));
	background: -moz-linear-gradient(top,  #f8f8f8,  #f6f6f6);
*/}
table tr:last-child td{
	border-bottom:0;
}
table tr:last-child td:first-child{
	-moz-border-radius-bottomleft:3px;
	-webkit-border-bottom-left-radius:3px;
	border-bottom-left-radius:3px;
}
table tr:last-child td:last-child{
	-moz-border-radius-bottomright:3px;
	-webkit-border-bottom-right-radius:3px;
	border-bottom-right-radius:3px;
}
table tr:hover td{
	background: #e2e2e2;
	color:#FFFFFF;
	font-size:12px;
	background: -webkit-gradient(linear, left top, left bottom, from(#777799), to(#aaaaff));
	background: -moz-linear-gradient(top,  #f2f2f2,  #f0f0f0);	
}
#statustd
{

}

#statustd:hover
{
	background-color:transparent;
}

.clickable
{

	color:blue;
	cursor:pointer;
}


#leftside
{
	position:absolute;	
	height:auto;
	width:55%;
	margin-left:1.5%;
}
#map_canvas
{
	top:20px;
	left:58%;
	position:fixed;
	height:460px;
	width:40%;
}
#pageheading
{
	color:#4387fd;
	font-size:16px;
	padding-left:18px;
}

#header_link
{
	cursor:pointer;
}
#td_back
{
	cursor:pointer;
	color:#0000FF;
}
#td_back:hover {
	background: #f00;
	color:#FFFFFF;
	font-size:16px;
	background: -webkit-gradient(linear, left top, left bottom, from(#aa4466), to(#ff99aa));
	background: -moz-linear-gradient(top,  #f24466,  #ff99aa);	
	
}
.registermessage
{
	background-color:#FFFF66;
	color:#990000;
	font-size:14px;
	text-align:center;
}
</style>
<style>
#txtUserName
{
	background-color:#FCFDFF;
	color:#303030;
	width:180px;
	border:1px solid #aaaaaa;
	height:26px;
	padding:2px;
	margin-left:5px;
	border-radius:4px;
	box-shadow:0px 0px 5px #999999 inset;
}
#registerPopup{
    width: 290px;
    min-height: 115px;
	height:auto;
	padding:5px;
    margin: 5px;
    border: 2px outset #3333FF;
    float: left;
	z-index:2;
	background-color:#FFFF99;
	color:#3333FF;
	border-radius:6px;
  }
  .starthidden {
    display: none;
  }

  .btn
  {
	background: #00f;
	color:#FFFFFF;
	font-size:14px;
	background: -webkit-gradient(linear, left top, left bottom, from(#4466aa), to(#99aaff));
	background: -moz-linear-gradient(top,  #7799f2,  #99aaff);	  	
	border-radius:4px;
	padding:2px;	
	margin-top:8px;
  }
  
 #mobilenopos
 {
 	font-size:14px;
	font-family:Verdana, Arial, Helvetica, sans-serif;	
	color:#663300;
	height:26px;
	margin-left:3px;
	padding:2px;
 } 
  </style>
  
  </head>

<body>
 <table width="98%" border="1" bgcolor="#F00FFF" style="margin-top:0px;">
 	<tr><td id="header_link" height="60px;" style="background-image:url(includes/pages/logo.jpg); background-repeat:no-repeat"  onclick='gohome()'></td>
		<td colspan="3" height="60px;" style="background-image:url(includes/pages/appname.jpg); background-repeat:no-repeat" ></td>

	</tr>
 	<tr  height="22px;">
		<td width="80%" id="pageheading" align="center" >Unregister User Report Logged in to System</td>
		<td id="td_back" width="6%"><a onclick="goBack()">BACK</a></td>
	</tr>
<!--	<tr>	
		<td colspan="2" width="100%" id="registermessage"></td>
	</tr>-->
</table>	
<div id="loadUsers">
<?php include("getUnregUsers.php"); ?>
</div>

<div id="registerPopup" style="display:none;">	
	<div class="registermessage" id="registermessagediv"></div>
	<p>Mobile No&nbsp;&nbsp; : <span id="mobilenopos"></span></p>  
	User Name : <input type="text" id="txtUserName"  name="txtUserName" value="" />
	<input type="hidden"  name="unreg_userid" id="unreg_userid"  />
	<input type="hidden"  name="hiddenMobileNo" id="hiddenMobileNo"  />
	<center><input class="btn" type="button"  id="regbtn" name="regbtn" value="Register User" onclick="registerit()" />
			<input class="btn" type="button"  id="close" name="close" value="Close" onclick="CloseRegisterPopup()" />
	</center>
</div>
</body>
</html>
