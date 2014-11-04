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

/**
 * @file listcours.php
 * @brief display list of courses
 */
$require_departmentmanage_user = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $tree = new Hierarchy();
    $course = new Course();
    $user = new User();

    // A search has been submitted
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $searchurl = "&search=yes";
    $searchtitle = isset($_GET['formsearchtitle']) ? $_GET['formsearchtitle'] : '';
    $searchcode = isset($_GET['formsearchcode']) ? $_GET['formsearchcode'] : '';
    $searchtype = isset($_GET['formsearchtype']) ? intval($_GET['formsearchtype']) : '-1';
    $searchfaculte = isset($_GET['formsearchfaculte']) ? intval($_GET['formsearchfaculte']) : '';
    // pagination
    $limit = intval($_GET['iDisplayLength']);
    $offset = intval($_GET['iDisplayStart']);

    // Search for courses
    $query = '';
    $terms = array();
    if (!empty($searchtitle)) {
        $query .= ' AND title LIKE ?s';
        $terms[] = '%' . $searchtitle . '%';
    }
    if (!empty($searchcode)) {
        $query .= ' AND (course.code LIKE ?s OR public_code ?s)';
        $terms[] = '%' . $searchcode . '%';
        $terms[] = '%' . $searchcode . '%';
    }
    if ($searchtype != "-1") {
        $query .= ' AND visible = ?d';
        $terms[] = $searchtype;
    }
    if ($searchfaculte) {
        $subs = $tree->buildSubtrees(array($searchfaculte));
        $ids = 0;
        foreach ($subs as $key => $id) {
            $terms[] = $id;
            $ids++;
        }
        $query .= ' AND hierarchy.id IN (' . implode(', ', array_fill(0, $ids, '?d')) . ')';
    }
    if (isset($_GET['reg_flag']) and ! empty($_GET['date'])) {
        $query .= ' AND created ' . (($_GET['reg_flag'] == 1) ? '>=' : '<=') . ' ?s';
        $date_created_at = DateTime::createFromFormat("d-m-Y H:i", $_GET['date']);
        $terms[] = $date_created_at->format("Y-m-d H:i:s");
    }

    // Datatables internal search
    $filter_terms = array();
    if (!empty($_GET['sSearch'])) {
        $filter_query = ' AND (title LIKE ?s OR prof_names LIKE ?s)';
        $filter_terms[] = '%' . $_GET['sSearch'] . '%';
        $filter_terms[] = '%' . $_GET['sSearch'] . '%';
    } else {
        $filter_query = '';
    }

    $query .= (isDepartmentAdmin()) ? ' AND course_department.department IN (' . implode(', ', $user->getDepartmentIds($uid)) . ') ' : '';

    // sorting
    $extra_query = "ORDER BY course.title " .
            ($_GET['sSortDir_0'] == 'desc' ? 'DESC' : '');
    // pagination
    if ($limit > 0) {
        $extra_query .= " LIMIT ?d, ?d";
        $extra_terms = array($offset, $limit);
    } else {
        $extra_terms = array();
    }

    $sql = Database::get()->queryArray("SELECT DISTINCT course.code, course.title, course.prof_names, course.visible, course.id
                               FROM course, course_department, hierarchy
                              WHERE course.id = course_department.course
                                AND hierarchy.id = course_department.department
                                    $query $filter_query $extra_query", $terms, $filter_terms, $extra_terms);
    $all_results = Database::get()->querySingle("SELECT COUNT(*) as total FROM course, course_department, hierarchy
                                                WHERE course.id = course_department.course
                                                AND hierarchy.id = course_department.department
                                                $query", $terms)->total;
    $filtered_results = Database::get()->querySingle("SELECT COUNT(*) as total FROM course, course_department, hierarchy
                                                WHERE course.id = course_department.course
                                                AND hierarchy.id = course_department.department
                                                $query $filter_query", $terms, $filter_terms)->total;

    $data['iTotalRecords'] = $all_results;
    $data['iTotalDisplayRecords'] = $filtered_results;

    $data['aaData'] = array();

    foreach ($sql as $logs) {
        $course_title = "<a href='{$urlServer}courses/" . $logs->code . "/'><b>" . q($logs->title) . "</b>
                        </a> (" . q($logs->code) . ")<br /><i>" . q($logs->prof_names) . "";
        // Define course type
        switch ($logs->visible) {
            case COURSE_CLOSED:
                $icon = 'lock_closed';
                $title = $langClosedCourse;
                break;
            case COURSE_REGISTRATION:
                $icon = 'lock_registration';
                $title = $langRegCourse;
                break;
            case COURSE_OPEN:
                $icon = 'lock_open';
                $title = $langOpenCourse;
                break;
            case COURSE_INACTIVE:
                $icon = 'lock_inactive';
                $title = $langInactiveCourse;
                break;
        }

        $departments = $course->getDepartmentIds($logs->id);
        $i = 1;
        $dep = '';
        foreach ($departments as $department) {
            $br = ($i < count($departments)) ? '<br/>' : '';
            $dep .= $tree->getFullPath($department) . $br;
            $i++;
        }

        // Add links to course users, delete course and course edit
        $icon_content = icon('fa-user', $langUsers, "listusers.php?c=$logs->id") . "&nbsp;";
        if (!isDepartmentAdmin()) {
            $icon_content .= icon('fa-list', $langUsersLog, "../usage/displaylog.php?c=$logs->id&amp;from_admin=TRUE") . "&nbsp;";
        }
        $icon_content .= icon('fa-edit', $langEdit, "editcours.php?c=$logs->code") . "&nbsp;";
        $icon_content .= icon('fa-times', $langDelete, "delcours.php?c=$logs->id");

        $data['aaData'][] = array(
            '0' => $course_title,
            '1' => icon($icon, $title),
            '2' => $dep,
            '3' => $icon_content
        );
    }
    echo json_encode($data);
    exit();
}

load_js('tools.js');
load_js('datatables');
load_js('datatables_filtering_delay');
$head_content .= "<script type='text/javascript'>
        $(document).ready(function() {
            $('#course_results_table').dataTable ({
                'bProcessing': true,
                'bServerSide': true,
                'sAjaxSource': '$_SERVER[REQUEST_URI]',
                'aLengthMenu': [
                   [10, 15, 20 , -1],
                   [10, 15, 20, '$langAllOfThem'] // change per page values here
                ],
                'sPaginationType': 'full_numbers',
                'bAutoWidth': false,
                'aoColumns': [
                    {'bSortable' : true, 'sWidth': '50%' },
                    {'bSortable' : false, 'sClass': 'center' },
                    {'bSortable' : false, 'sWidth': '25%' },
                    {'bSortable' : false },
                ],
                'oLanguage': {
                   'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
                   'sZeroRecords':  '" . $langNoResult . "',
                   'sInfo':         '$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults',
                   'sInfoEmpty':    '$langDisplayed 0 $langTill 0 $langFrom2 0 $langResults2',
                   'sInfoFiltered': '',
                   'sInfoPostFix':  '',
                   'sSearch':       '" . $langSearch . "',
                   'sUrl':          '',
                   'oPaginate': {
                       'sFirst':    '&laquo;',
                       'sPrevious': '&lsaquo;',
                       'sNext':     '&rsaquo;',
                       'sLast':     '&raquo;'
                   }
               }
            }).fnSetFilteringDelay(1000);
            $('.dataTables_filter input').attr('placeholder', '$langTitle, $langTeacher');
        });
        </script>";


$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'searchcours.php', 'name' => $langSearchCourses);
$nameTools = $langListCours;

// Display Actions Toolbar
$tool_content .= "<div id='operations_container'>" .
        action_bar(array(
            array('title' => $langAllCourses,
                'url' => "$_SERVER[SCRIPT_NAME]?formsearchtitle=&amp;formsearchcode=&amp;formsearchtype=-1&amp;reg_flag=1&amp;date=&amp;formsearchfaculte=0&amp;search_submit=$langSearch",
                'icon' => 'fa-search',
                'level' => 'primary-label'),
        )) .
        "</div>";

$width = (!isDepartmentAdmin()) ? 100 : 80;
// Construct course list table
$tool_content .= "<table id='course_results_table' class='display'>
    <thead>
    <tr>
    <th align='left'>$langCourseCode</th>
    <th>$langGroupAccess</th>
    <th width='260' align='left'>$langFaculty</th>
    <th width='$width'>$langActions</th>
    </tr></thead>";

$tool_content .= "<tbody></tbody></table>";
$tool_content .= "<div align='center' style='margin-top: 60px; margin-bottom:10px;'>";
$tool_content .= action_bar(array(
    array('title' => $langReturnSearch,
        'url' => "searchcours.php",
        'icon' => 'fa-reply',
        'level' => 'primary-label')));
$tool_content .= "</div>";

draw($tool_content, 3, null, $head_content);
