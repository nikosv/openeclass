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
  usage/index.php
  @version $Id$
  @last update: 2006-12-27 by Evelthon Prodromou <eprodromou@upnet.gr>
  @authors list: Vangelis Haniotakis haniotak@ucnet.uoc.gr
  ==============================================================================
  @Description: Main script for the usage statistics module


  @todo: Nothing much; most functionality is already in form.php and results.php
  ==============================================================================
 */

$require_current_course = true;
$require_course_admin = true;
$require_help = true;
$helpTopic = 'Usage';
$require_login = true;
require_once '../../include/baseTheme.php';
require_once 'include/jscalendar/calendar.php';

$tool_content .= "
<div id='operations_container'>
  <ul id='opslist'>
    <li><a href='displaylog.php?course=$course_code'>$langUsersLog</a></li>
    <li><a href='favourite.php?course=$course_code&amp;first='>$langFavourite</a></li>
    <li><a href='userlogins.php?course=$course_code&amp;first='>$langUserLogins</a></li>
    <li><a href='userduration.php?course=$course_code'>$langUserDuration</a></li>
    <li><a href='../learnPath/detailsAll.php?course=$course_code&amp;from_stats=1'>$langLearningPaths</a></li>
    <li><a href='group.php?course=$course_code'>$langGroupUsage</a></li>
  </ul>
</div>\n";

$dateNow = date("d-m-Y / H:i:s", time());
$nameTools = $langUsage;
$local_style = '
    .month { font-weight : bold; color: #FFFFFF; background-color: #edecdf; padding-left: 15px; padding-right : 15px; }
    .content {position: relative; left: 25px; }';

$jscalendar = new DHTML_Calendar($urlServer . 'include/jscalendar/', $language, 'calendar-blue2', false);
$head_content = $jscalendar->get_load_files_code();
if (isset($_POST['u_analyze']) && isset($_POST['user_id']) && $_POST['user_id'] != -1) {
    require_once "analyze.php";
} else {
    $made_chart = true;
    ob_start();
    require_once "results.php";
    require_once "form.php";
}
add_units_navigation(true);
load_js('tools.js');
draw($tool_content, 2, null, $head_content);