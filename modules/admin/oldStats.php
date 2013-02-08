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



/*
===========================================================================
    admin/oldStats.php
    @last update: 23-09-2006
    @authors list: ophelia neofytou
==============================================================================
    @Description:  Shows statistics older than two months that concern the number of visits
        on the platform for a time period.
        Note: Information for old statistics is taken from table 'loginout_summary' where
        cummulative monthly data are stored.

==============================================================================
*/
// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = TRUE;
// Include baseTheme
require_once '../../include/baseTheme.php';
// Define $nameTools
$nameTools = $langOldStats;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);

$tool_content .= "
  <div id='operations_container'>
    <ul id='opslist'>
      <li><a href='stateclass.php'>".$langPlatformGenStats."</a></li>
      <li><a href='platformStats.php?first='>".$langVisitsStats."</a></li>
      <li><a href='visitsCourseStats.php?first='>".$langVisitsCourseStats."</a></li>
      <li><a href='monthlyReport.php'>".$langMonthlyReport."</a></li>
    </ul>
  </div>";


// move data from table 'loginout' to 'loginout_summary' if older than eight months
require_once 'modules/admin/summarizeLogins.php';

require_once 'include/jscalendar/calendar.php';

$lang = ($language == 'el')? 'el': 'en';
$jscalendar = new DHTML_Calendar($urlServer.'include/jscalendar/', $lang, 'calendar-blue2', false);
$head_content = $jscalendar->get_load_files_code();


//$min_w is the min date in 'loginout'. Statistics older than $min_w will be shown.
$query = "SELECT MIN(`when`) as min_when FROM loginout";
$result = db_query($query, $mysqlMainDb);
while ($row = mysql_fetch_assoc($result)) {
    $min_when = strtotime($row['min_when']);
}
$min_w = date("d-m-Y", $min_when);


    $tool_content .= '
    <div class="info">'.sprintf($langOldStatsLoginsExpl, get_config('actions_expire_interval')).'</div>';

    /*****************************************
      start making chart
     *******************************************/
     require_once 'modules/graphics/plotter.php';

     //default values for chart
     $usage_defaults = array (
            'u_date_start' => strftime('%Y-%m-%d', strtotime('now -4 month')),
            'u_date_end' => strftime('%Y-%m-%d', strtotime('now -1 month')),
      );

     foreach ($usage_defaults as $key => $val) {
         if (!isset($_POST[$key])) {
             $$key = $val;
         } else {
             $$key = q($_POST[$key]);
         }
     }

    $date_fmt = '%Y-%m-%d';
    $u_date_start = mysql_real_escape_string($u_date_start);
    $u_date_end = mysql_real_escape_string($u_date_end);
    $date_where = " (start_date BETWEEN '$u_date_start 00:00:00' AND '$u_date_end 23:59:59') ";
    $query = "SELECT MONTH(start_date) AS month, YEAR(start_date) AS year, SUM(login_sum) AS visits
                        FROM loginout_summary
                        WHERE $date_where
                        GROUP BY MONTH(start_date)";

    $result = db_query($query);

    if (mysql_num_rows($result) > 0) {
        $chart = new Plotter();
        $chart->setTitle($langOldStats);

        //add points to chart
        while ($row = mysql_fetch_assoc($result)) {
            $mont = $langMonths[$row['month']];
            $chart->growWithPoint($mont . " - " . $row['year'], $row['visits']);
        }
        mysql_free_result($result);
        $tool_content .= "<p>" . $langVisits . "</p>\n" . $chart->plot($langNoStatistics);
    }
    $tool_content .= '<br />';

    /********************************************************
       Start making the form for choosing start and end date
    ********************************************************/
    $start_cal = $jscalendar->make_input_field(
           array('showsTime'      => false,
                 'showOthers'     => true,
                 'ifFormat'       => '%Y-%m-%d',
                 'timeFormat'     => '24'),
           array('style'       => '',
                 'name'        => 'u_date_start',
                 'value'       => $u_date_start));

    $end_cal = $jscalendar->make_input_field(
           array('showsTime'      => false,
                 'showOthers'     => true,
                 'ifFormat'       => '%Y-%m-%d',
                 'timeFormat'     => '24'),
           array('style'       => '',
                 'name'        => 'u_date_end',
                 'value'       => $u_date_end));


    $tool_content .= '<form method="post">
    <table width="100%" class="tbl">
    <tr>
      <th width="150" class="left">'.$langStartDate.':</th>
      <td>'."$start_cal".'</td>
    </tr>
    <tr>
      <th class="left">'.$langEndDate.':</th>
      <td>'."$end_cal".'</td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td class="right"><input type="submit" name="btnUsage" value="'.$langSubmit.'"></td>
    </tr>
    </table>
    </form>';

draw($tool_content, 3, null, $head_content);
