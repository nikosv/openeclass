<?php  
//��������� ��� ��������� ��� �� ������������ �� baseTheme
$require_current_course = TRUE;
$langFiles = 'conference';
$require_help = TRUE;
$helpTopic = 'User';
include '../../include/baseTheme.php';


$nameTools = "conference";


//HEADER
$head_content='


<meta http-equiv="refresh" content="400; url=\''.$_SERVER['PHP_SELF'].'\'">

<script type="text/javascript" src="js/prototype-1.4.0.js"></script>
<script>

var video_div="";
function prepare_message()
{
	    var pars = "chatLine="+escape(document.chatForm.msg.value);
	    var target = "chat";
	    var url = "refresh_chat.php3";
	    var myAjax = new Ajax.Updater(target, url, {method: "get", parameters: pars});
        document.chatForm.msg.value = "";
        document.chatForm.msg.focus();

        return false;
}


function init_student()
	{
	    var url = "refresh_chat.php3";
	    var target = "chat";
	    var myAjax = new Ajax.Updater(target, url);
	}
function init_teacher()
	{
	    var url = "refresh_chat.php3";
	    var target = "chat";
	    var myAjax = new Ajax.Updater(target, url);
	}
function refresh_student()
	{




var set_video = function(t) {
	if(video_div!=t.responseText)
	{	video_div=t.responseText;
		document.getElementById("video").innerHTML=t.responseText;


		var set_netmeeting_number = function(t) {
			NetMeeting.CallTo(t.responseText);

		}


	  	new Ajax.Request("pass_parameters.php3", {method:"post", postBody:"variable=netmeeting_number", onSuccess:set_netmeeting_number, onFailure:errFunc});
		


	}

    }
var set_presantation = function(t) {

    if(t.responseText!=document.getElementById("presantation_window").src)
    	{
    	document.getElementById("presantation_window").src=t.responseText;
    	}
    }
var errFunc = function(t) {
    alert("Error " + t.status + " -- " + t.statusText);
}
	  new Ajax.Request("pass_parameters.php3", {method:"post", postBody:"variable=video", onSuccess:set_video, onFailure:errFunc});

    	  new Ajax.Request("pass_parameters.php3", {method:"post", postBody:"variable=presantation", onSuccess:set_presantation, onFailure:errFunc});



	    var url = "refresh_chat.php3";
	    var target = "chat";
	    var myAjax = new Ajax.Updater(target, url);
	}
function refresh_teacher()
	{  
	    var url = "refresh_chat.php3";
	    var target = "chat";
	    var myAjax = new Ajax.Updater(target, url);
	}

function play_video()
	{
var player;
var video_type;

var video_type_object=document.forms["video_form"].elements["video_type"];

for(var i = 0; i < video_type_object.length; i++)

{

if(video_type_object[i].checked) {
			video_type=video_type_object[i].value;
		}

	}

if(video_type=="video")
{
var video_url=document.getElementById("video_URL").value;
player="<OBJECT id=\'VIDEO\' width=\'149\' height=\'149\' \
	CLASSID=\'CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6\'\
	type=\'application/x-oleobject\'>\
	<PARAM NAME=\'URL\' VALUE=\'"+video_url+"\'>\
	<PARAM NAME=\'SendPlayStateChangeEvents\' VALUE=\'True\'>\
	<PARAM NAME=\'AutoStart\' VALUE=\'True\'>\
	<PARAM name=\'uiMode\' value=\'none\'>\
	<PARAM name=\'PlayCount\' value=\'9999\'>\
</OBJECT>";


}

if(video_type=="netmeeting")
{

player="<object ID=\'NetMeeting\' CLASSID=\'CLSID:3E9BAF2D-7A79-11d2-9334-0000F875AE17\'>\
<PARAM NAME =\'MODE\' VALUE =\'RemoteOnly\'>\
</object>";


}

document.getElementById("video").innerHTML=player;

if(video_type=="netmeeting")
{
var netmeeting_number="00302993333";
NetMeeting.CallTo(netmeeting_number);
}


if(video_type=="netmeeting")
{
	new Ajax.Request("pass_parameters.php3", {method:"post", postBody:"video_div="+player+"&netmeeting_number="+netmeeting_number});

}
if(video_type=="video")
{
	new Ajax.Request("pass_parameters.php3", {method:"post", postBody:"video_div="+document.getElementById("video").innerHTML});
}


return false;


	}


function show_presantation()
	{
var presantation_url=document.getElementById("Presantation_URL").value;
document.getElementById("presantation_window").src=presantation_url;
new Ajax.Request("pass_parameters.php3", {method:"post", postBody:"presantation_URL="+presantation_url});
return false;
	
	}

var pe;
if (pe) pe.stop();
';

if ($is_adminOfCourse) {
	$head_content.='pe = new PeriodicalExecuter(refresh_teacher, 5);';
}
else{
	$head_content.='pe = new PeriodicalExecuter(refresh_student, 5);';
}


$head_content.='




</script>
';

//END HEADERS

//BODY



if ($is_adminOfCourse) {
$body_action='onload=init_teacher();';
}
else
{
$body_action='onload=init_student();';
}

//END BODY


//CONTENT
$tool_content = "";//initialise $tool_content



$tool_content.=
'
<table >
<tr valign="top"><td width="150">
	<div id="video"  style="height: 150px;width: 150px;border:groove;">
	</div>


';

if ($is_adminOfCourse) {
$tool_content.='
<form id="video_form" onSubmit = "return play_video();">
<BR>'.$Video_URL.'<BR>
<table>
<tr>
<td>
    <label>
      <input type="radio" name="video_type" id="video_type1" value="netmeeting" />
      <br>netmeeting</label>
</td>
<td>
    <label>
      <input type="radio" name="video_type" id="video_type2" value="video" />
<br>video</label>
</td>
</tr>
</table>
    <br />
  
<input type="text" id="Video_URL" size="20"><input type="submit" value=" Play ">
  </label>

</form>
<form id="Presantation_form" onSubmit = "return show_presantation();">
<BR>'.$Presantation_URL.'<BR>
<input type="text" id="Presentation_URL" name="Presantation_URL" size="20">
<input type="submit" value="Go">
</form>
';

}

$tool_content.='
	</TD>
	<TD>


	<div id="presantation" style="height: 500px;width: 700px;border:groove;" >
	<iframe name="presantation_window" id="presantation_window" width="100%" height="100%" src="http://www.auth.gr">
	</iframe>

	</div>
	</TD></TR>
	<TR >
	<TD colspan=2>

	<div align="center" >
		<div align="left" id="chat" style="position: relative;height: 60px;width: 616px; overflow: auto;">
		</div>

		<form name = "chatForm" action = "conference.php3#bottom" method = "get" target = "conference" onSubmit = "return prepare_message();">

		<div align="center"  style="position: relative; width:750px">
			<input type="text" name="msg" size="80">
			<input type="hidden" name="chatLine">
			<input type="submit" value=" >> ">
			</form><br>
';
		if ($is_adminOfCourse) {
			$tool_content.=' 
        		<a href="conference.php3?reset=true" target="conference">'.$langWash.'</a> |
        		<a href="conference.php3?store=true" target="conference">'.$langSave.'</a>
			';
 		}
		$tool_content.='

		</div>
	</div>

	</TD></TR>
	</TABLE>
';


//END CONTENT





draw($tool_content, 2, 'user', $head_content,$body_action);
?>
