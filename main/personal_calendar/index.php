<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */


/*
 * Events Component
 *
 * @version 1.0
 * @abstract This component displays personal user events and offers several operations on them.
 * The user can:
 * 1. Add new personal events
 * 2. Delete personal events (one by one or all at once)
 * 3. Modify existing events
 * 4. Associate events with courses and course objects
 */

$require_login = true;
$require_help = TRUE;
$helpTopic = 'PersonalCalendar';

include '../../include/baseTheme.php';
$require_valid_uid = true;
require_once 'include/lib/textLib.inc.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/references.class.php';
require_once 'main/personal_calendar/calendar_events.class.php';


$dateNow = date("j-n-Y / H:i", time());
$datetoday = date("Y-n-j H:i", time());

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
}

load_js('tools.js');
load_js('jquery');
load_js('jquery-ui');
load_js('jquery-ui-timepicker-addon.min.js');

$head_content .= "<link rel='stylesheet' type='text/css' href='{$urlAppend}js/jquery-ui-timepicker-addon.min.css'>
<script type='text/javascript'>
$(function() {
$('input[name=startdate]').datetimepicker({
    dateFormat: 'yy-mm-dd', 
    timeFormat: 'hh:mm'
    });
$('input[name=enddate]').datepicker({
    dateFormat: 'yy-mm-dd'
    });
$('input[name=duration]').timepicker({ 
    timeFormat: 'H:mm',
    showDuration: true
    });
});

</script>";

//angela: Do we need recording of personal actions????
// The following is added for statistics purposes
//require_once 'include/action.php';

//$action = new action();
//$action->record(MODULE_ID_ANNOUNCE);

$nameTools = $langMyAgenda;

ModalBoxHelper::loadModalBox();
load_js('jquery');
load_js('tools.js');
load_js('references.js');
$head_content .= '<script type="text/javascript">var langEmptyGroupName = "' .
        $langEmptyEventTitle . '";</script>';

$eventNumber = Calendar_Events::count_user_events();

$displayForm = true;

/* submit form: new or updated event*/
if (isset($_POST['submitEvent'])) {
    
    $newTitle = $_POST['newTitle'];       
    $newContent = $_POST['newContent'];
    $refobjid = ($_POST['refobjid'] == "0")? $_POST['refcourse']:$_POST['refobjid'];
    $start = $_POST['startdate'];
    $duration = $_POST['duration'];
    if (!empty($_POST['id'])) { //existing event
        $id = intval($_POST['id']);
        Calendar_Events::update_event($id, $newTitle, $newContent, $refobjid);
        $message = "<p class='success'>$langEventModify</p>";
    } else { // new event 
        $recursion = array('unit' => $_POST['frequencyperiod'], 'repeat' => $_POST['frequencynumber'], 'end'=> $_POST['enddate']);
        $id = Calendar_Events::add_event($newTitle, $newContent, $start, $duration, $recursion, $refobjid);
        $message = "<p class='success'>$langEventAdd</p>";
    }    
} // end of if $submit

/* delete */
if (isset($_GET['delete'])) {
    $thisEventId = intval($_GET['delete']);
    Calendar_Events::delete_event($thisEventId);
    $message = "<p class='success'>$langEventDel</p>";
}

/* edit */
if (isset($_GET['modify'])) {
    $modify = intval($_GET['modify']);
    $event = Calendar_Events::get_event($modify);
    if ($event) {
        $eventToModify = $event->id;
        $contentToModify = $event->content;
        $titleToModify = q($event->title);
        $datetimeToModify = q($event->start);
        $durationToModify = q($event->duration);
        $gen_type_selected = $event->reference_obj_module;
        $course_selected = $event->reference_obj_course;
        $type_selected = $event->reference_obj_type;
        $object_selected = $event->reference_obj_id;
    }
}

if (isset($message) && $message) {
    $tool_content .= $message . "<br/>";
    $displayForm = false; //do not show form
}

/* display form */
if ($displayForm and (isset($_GET['addEvent']) or isset($_GET['modify']))) {
    $tool_content .= "
    <form method='post' action='$_SERVER[SCRIPT_NAME]' onsubmit=\"return checkrequired(this, 'antitle');\">
    <fieldset>
    <legend>$langEvent</legend>
    <table class='tbl' width='100%'>";
    if (isset($_GET['modify'])) {
        $langAdd = $nameTools = $langModifEvent;
    } else {
        $nameTools = $langAddEvent;
    }
    $navigation[] = array('url' => "index.php", 'name' => $langEvents);
    if (!isset($eventToModify))
        $eventToModify = "";
    if (!isset($contentToModify))
        $contentToModify = "";
    if (!isset($titleToModify))
        $titleToModify = "";
    if (!isset($datetimeToModify))
        $datetimeToModify = "";
    if (!isset($durationToModify))
        $durationToModify = "";
    if (!isset($gen_type_selected))
        $gen_type_selected = null;
    if (!isset($course_selected))
        $course_selected = null;
    if (!isset($type_selected))
        $type_selected = null;
    if (!isset($object_selected))
        $object_selected = null;
    
    $tool_content .= "
    <tr><th>$langEventTitle:</th></tr>
    <tr>
      <td><input type='text' name='newTitle' value='$titleToModify' size='50' /></td>
    </tr>
    <tr><th>$langEventBody:</th></tr>
    <tr>
      <td>" . rich_text_editor('newContent', 4, 20, $contentToModify) . "</td>
    </tr>
    <tr><th>$langDate:</th></tr>
    <tr>
        <td> <input type='text' name='startdate' value='$datetimeToModify'></td>
    </tr>
    <tr><th>$langDuration:</th></tr>
    <tr>
        <td><input type=\"text\" name=\"duration\" value='$durationToModify'></td>
    </tr>";
    if(!isset($_GET['modify'])){
        $tool_content .= "
        <tr><th>$langRepeat:</th></tr>
        <tr>
            <td> $langEvery: "
                . "<select name='frequencynumber'>"
                . "<option value=\"0\">$langSelectFromMenu</option>";
        for($i = 1;$i<10;$i++)
        {
            $tool_content .= "<option value=\"$i\">$i</option>";
        }
        $tool_content .= "</select>"
                . "<select name='frequencyperiod'> "
                . "<option>$langSelectFromMenu...</option>"
                . "<option value=\"D\">$langDays</option>"
                . "<option value=\"W\">$langWeeks</option>"
                . "<option value=\"M\">$langMonthsAbstract</option>"
                . "</select>"
                . " $langUntil: <input type='text' name='enddate' value=''></td>
        </tr>";
    }
    $tool_content .= "
    <tr><th>$langReferencedObject:</th></tr>
    <tr>
      <td>".
      References::build_object_referennce_fields($gen_type_selected, $course_selected, $type_selected, $object_selected)
   ."</td>
    </tr>
    <tr>
      <td class='right'><input type='submit' name='submitEvent' value='$langAdd' /></td>
    </tr>
    </table>
    <input type='hidden' name='id' value='$eventToModify' />
    </fieldset>
    </form>";
} else {
    /* display actions toolbar */
    $tool_content .= "
    <div id='operations_container'>
      <ul id='opslist'>
        <li><a href='$_SERVER[SCRIPT_NAME]?addEvent=1'>" . $langAddEvent . "</a></li>
        <li><a href='icalendar.php'>" . $langiCalExport . "</a></li>
      </ul>
    </div>";
}


/* display events */
$eventlist = isset($_GET['evid']) ? array(Calendar_Events::get_event(intval($_GET['evid']))) : Calendar_Events::get_user_events();

$day = (isset($_GET['day']))? intval($_GET['day']):null;
$month = (isset($_GET['month']))? intval($_GET['month']):null;
$year = (isset($_GET['year']))? intval($_GET['year']):null;
$tool_content .= Calendar_Events::calendar_view($day, $month, $year);

add_units_navigation(TRUE);

draw($tool_content, 1, null, $head_content);
