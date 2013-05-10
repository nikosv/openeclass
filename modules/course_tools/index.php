<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

$require_current_course = true;
$require_course_admin = true;
$require_help = true;
$helpTopic = 'courseTools';
$require_login = true;

include '../../include/baseTheme.php';
require_once 'include/log.php';
require_once 'include/lib/fileUploadLib.inc.php';

$nameTools = $langToolManagement;
add_units_navigation(TRUE);

load_js('tools.js');
$head_content .= '<script type="text/javascript">var langEmptyGroupName = "' .
			 $langNoPgTitle . '";</script>';

if (isset($_GET['action'])) {
        $action = intval($_GET['action']);
}

if (isset($_REQUEST['toolStatus'])) {
        if (isset($_POST['toolStatActive'])) {
                $tool_stat_active = $_POST['toolStatActive'];
        }
        if (isset($tool_stat_active)) {
                $loopCount = count($tool_stat_active);
        } else {
                $loopCount = 0;
        }
        $i = 0;
        $publicTools = array();
        $tool_id = null;
        while ($i< $loopCount) {
                if (!isset($tool_id)) {
                        $tool_id = " (`module_id` = " . intval($tool_stat_active[$i]) .")" ;
                } else {
                        $tool_id .= " OR (`module_id` = " . intval($tool_stat_active[$i]) .")" ;
                }
                $i++;
        }
        //reset all tools
        db_query("UPDATE course_module SET `visible` = 0
                         WHERE course_id = $course_id");
        //and activate the ones the professor wants active, if any
        if ($loopCount > 0) {
                db_query("UPDATE course_module SET visible = 1
                                 WHERE $tool_id AND
                                 course_id = $course_id");
        }
}

if (isset($_POST['delete'])) {
        $delete = intval($_POST['delete']);
        $r = mysql_fetch_array(db_query("SELECT url, title, category FROM link WHERE `id` = $delete"));
        if($r['category'] == -2) { // if we want to delete html page also delete file
                $link = explode(" ", $r['url']);
                $path = substr($link[0], 6);
                $file2Delete = $webDir ."/". $path;
                unlink($file2Delete);
        }
        db_query("DELETE FROM link WHERE `id` = $delete");
        Log::record($course_id, MODULE_ID_TOOLADMIN, LOG_DELETE, array('id' => $delete,
                                                                        'link' => $r['url'],
                                                                        'name_link' => $r['title']));
        unset($sql);
        $tool_content .= "<p class='success'>$langLinkDeleted</p>";
}

/**
 * Add external link
 */
if (isset($_POST['submit'])) {                
        $link = isset($_POST['link'])?$_POST['link']:'';
        $name_link = isset($_POST['name_link'])?$_POST['name_link']:'';
        if ((trim($link) == 'http://') or (trim($link) == 'ftp://') or empty($link) or empty($name_link))  {
                $tool_content .= "<p class='caution'>$langInvalidLink<br />
                        <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=2'>$langHome</a></p><br />";
                draw($tool_content, 2, null, $head_content);
                exit();
        }
        
        db_query("INSERT INTO link (course_id, url, title, category)
                            VALUES (".$course_id.", ".quote($link).", ".quote($name_link).", -1)");
        $id = mysql_insert_id();
        $tool_content .= "<p class='success'>$langLinkAdded</p>";
        Log::record($course_id, MODULE_ID_TOOLADMIN, LOG_INSERT, array('id' => $id,
                                                                       'link' => $link,
                                                                       'name_link' => $name_link));
        
} elseif (isset($_GET['action'])) { // add external link
        $nameTools = $langAddExtLink;
        $navigation[]= array ('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langToolManagement);
        $helpTopic = 'Module';
        $tool_content .= "<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=true'>
            <fieldset>
            <legend>$langExplanation_4</legend>
            <table width='100%' class='tbl'>
            <tr>
              <th>$langLink:</th>
              <td><input type='text' name='link' size='50' value='http://'></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <th>$langLinkName:</th>
              <td><input type='Text' name='name_link' size='50'></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <th>&nbsp;</th>
              <td><input type='submit' name='submit' value='$langAdd'></td>
              <td>&nbsp;</td>
            </tr>
            </table>
            </fieldset>
          </form>";
        draw($tool_content, 2, null, $head_content);
        exit();
}

$toolArr = getSideMenu(2);

if (is_array($toolArr)) {
        for ($i = 0; $i <= 1; $i++){
                $toolSelection[$i] = '';
                $numOfTools = count($toolArr[$i][1]);
                for ($j = 0; $j < $numOfTools; $j++) {
                        $toolSelection[$i] .= "<option value='" . $toolArr[$i][4][$j] . "'>" .
                                              $toolArr[$i][1][$j] . "</option>\n";
                }
        }
}

$tool_content .= "
<div id='operations_container'>
  <ul id='opslist'>    
    <li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=true'>$langAddExtLink</a></li>
  </ul>
</div>";

$tool_content .= <<<tForm
<form name="courseTools" action="$_SERVER[SCRIPT_NAME]?course=$course_code" method="post" enctype="multipart/form-data">
<table class="tbl_border" width="100%">
<tr>
<th width="45%" class="center">$langInactiveTools</th>
<th width="10%" class="center">$langMove</th>
<th width="45%" class="center">$langActiveTools</th>
</tr>
<tr>
<td class="center">
<select name="toolStatInactive[]" id='inactive_box' size='17' multiple>\n$toolSelection[1]</select>
</td>
<td class="center">
<input type="button" onClick="move('inactive_box','active_box')" value="   >>   " /><br/>
<input type="button" onClick="move('active_box','inactive_box')" value="   <<   " />
</td>
<td class="center">
<select name="toolStatActive[]" id='active_box' size='17' multiple>\n$toolSelection[0]</select>
</td>
</tr>
<tr>
<td>&nbsp;</td>
<td class="center">
<input type=submit value="$langSubmitChanges" name="toolStatus" onClick="selectAll('active_box',true)" />
</td>
<td>&nbsp;</td>
</tr>
</table>
</form>
tForm;
// ------------------------------------------------
// display table to edit/delete external links
// ------------------------------------------------
$sql = db_query("SELECT id, title FROM link
                        WHERE category IN(-1,-2) AND
                        course_id = $course_id");
$tool_content .= "<br/>
<table class='tbl_alt' width='100%'>
<tr>
  <th>&nbsp;</th>
  <th colspan='2'>$langOperations</th>
</tr>
<tr>
  <th>&nbsp;</th>
  <th><div align='left'>$langTitle</div></th>
  <th width='20'>$langDelete</th>
</tr>";
$i = 0;
while ($externalLinks = mysql_fetch_array($sql)) {
        if ($i % 2 == 0) {
                $tool_content .= "<tr class='even'>\n";
        } else {
                $tool_content .= "<tr class='odd'>\n";
        }
        $tool_content .= "<th width='1'>
        <img src='$themeimg/external_link_on.png' title='$langTitle' /></th>
        <td class='left'>$externalLinks[title]</td>
        <td align='center'><form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
           <input type='hidden' name='delete' value='$externalLinks[id]' />
           <input type='image' src='$themeimg/delete.png' name='delete_button'
                  onClick=\"return confirmation('" .js_escape("$langConfirmDeleteLink {$externalLinks['title']}") ."');\" title='$langDelete' /></form></td>
     </tr>\n";
     $i++;
}
$tool_content .= "</table>\n";

draw($tool_content, 2, null, $head_content);