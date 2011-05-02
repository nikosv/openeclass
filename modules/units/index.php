<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/

/*
Units display module	
*/
define('HIDE_TOOL_TITLE', 1);
$require_current_course = true;
$require_help = TRUE;
$helpTopic = 'AddCourseUnitscontent';
include '../../include/baseTheme.php';
include '../../include/lib/fileDisplayLib.inc.php';
include '../../include/action.php';
include 'functions.php';
include '../document/doc_init.php';

$action = new action();
$action->record('MODULE_ID_UNITS');

mysql_select_db($mysqlMainDb);

if (isset($_REQUEST['id'])) {
	$id = intval($_REQUEST['id']);
}
$lang_editor = langname_to_code($language);
load_js('tools.js');

if (isset($_REQUEST['edit_submit'])) {
        units_set_maxorder();
        $tool_content .= handle_unit_info_edit();
}

$form = process_actions();

if ($is_adminOfCourse) {
	$tool_content .= "&nbsp;<div id='operations_container'>
		<form name='resinsert' action='{$urlServer}modules/units/insert.php' method='get'><input type='hidden' name='course' value='$code_cours'/>
		<select name='type' onChange='document.resinsert.submit();'>
			<option>-- $langAdd --</option>
			<option value='doc'>$langInsertDoc</option>
			<option value='exercise'>$langInsertExercise</option>
			<option value='text'>$langInsertText</option>
			<option value='link'>$langInsertLink</option>
			<option value='lp'>$langLearningPath1</option>
			<option value='video'>$langInsertVideo</option>
			<option value='forum'>$langInsertForum</option>
			<option value='ebook'>$langInsertEBook</option>
			<option value='work'>$langInsertWork</option>
			<option value='wiki'>$langInsertWiki</option>
		</select>
		<input type='hidden' name='id' value='$id'>
		<input type='hidden' name='course' value='$code_cours'>
		</form>
		</div>".
		$form; 
}

if ($is_adminOfCourse) {
        $visibility_check = '';
} else {
        $visibility_check = "AND visibility='v'";
}
if (isset($id) and $id !== false) {
	$q = db_query("SELECT * FROM course_units
		                WHERE id = $id AND course_id=$cours_id " . $visibility_check);
} else {
	$q = false;
}
if (!$q or mysql_num_rows($q) == 0) {
        $nameTools = $langUnitUnknown;
	$tool_content .= "<p class='caution'>$langUnknownResType</p>";
        draw($tool_content, 2);
        exit;
}
$info = mysql_fetch_array($q);
$nameTools = htmlspecialchars($info['title']);
$comments = trim($info['comments']);

// Links for next/previous unit
foreach (array('previous', 'next') as $i) {
        if ($i == 'previous') {
                $op = '<=';
                $dir = 'DESC';
                $arrow1 = '« ';
                $arrow2 = '';
        } else {
                $op = '>=';
                $dir = '';
                $arrow1 = '';
                $arrow2 = ' »';
        }
        $q = db_query("SELECT id, title FROM course_units
                       WHERE course_id = $cours_id
                             AND id <> $id
                             AND `order` $op $info[order]
                             AND `order` >= 0
                             $visibility_check
                       ORDER BY `order` $dir
                       LIMIT 1");
        if ($q and mysql_num_rows($q) > 0) {
                list($q_id, $q_title) = mysql_fetch_row($q);
                $q_title = htmlspecialchars($q_title);
                $link[$i] = "<a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;id=$q_id'>$arrow1$q_title$arrow2</a>";
        } else {
                $link[$i] = '&nbsp;';
        }
}

if ($is_adminOfCourse) {
        $comment_edit_link = "<td valign='top' width='20'><a href='info.php?course=$code_cours&amp;edit=$id&amp;next=1'><img src='../../template/classic/img/edit.png' title='' alt='' /></a></td>";
        $units_class = 'tbl';
} else {
        $units_class = 'tbl';
        $comment_edit_link = '';
}

$tool_content .= "
    <table class='$units_class' width='99%'>
    <tr class='odd'>
      <td class='left'>" .  $link['previous'] . '</td>
      <td class="right">' .  $link['next'] . "</td>
    </tr>
    <tr>
      <td colspan='2' class='unit_title'>$nameTools</td>
    </tr>
    </table>\n";


if (!empty($comments)) {
        $tool_content .= "
    <table class='tbl' width='99%'>
    <tr class='even'>
      <td>$comments</td>
      $comment_edit_link
    </tr>
    </table>";
}

show_resources($id);

$tool_content .= '
  <form name="unitselect" action="' .  $urlServer . 'modules/units/" method="get"><input type="hidden" name="course" value="'.$code_cours.'"/>';
$tool_content .="
    <table width='99%' class='tbl'>
     <tr class='odd'>
       <td class='right'>".$langCourseUnits.":&nbsp;</td>
       <td width='50' class='right'>".
                 "<select name='id' onChange='document.unitselect.submit();'>";
$q = db_query("SELECT id, title FROM course_units
               WHERE course_id = $cours_id AND `order` > 0
                     $visibility_check
               ORDER BY `order`", $mysqlMainDb);
while ($info = mysql_fetch_array($q)) {
        $selected = ($info['id'] == $id)? ' selected="1" ': '';
        $tool_content .= "<option value='$info[id]'$selected>" .
                         htmlspecialchars(ellipsize($info['title'], 40)) .
                         '</option>';
}
$tool_content .= "</select>
       </td>
     </tr>
    </table>
 </form>";

draw($tool_content, 2, null, $head_content);

