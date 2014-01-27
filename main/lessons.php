<?php

/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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
 * Personalised Lessons Component, eClass Personalised
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * @package eClass Personalised
 *
 * @abstract This component populates the lessons block on the user's personalised
 * interface. It is based on the diploma thesis of Evelthon Prodromou.
 *
 */

/*
 * Function getUserLessonInfo
 *
 * Creates content for the user's lesson block on the personalised interface
 * If type is 'html' it creates the interface html populated with data and
 * If type is 'data' it returns an array with all lesson data
 *
 * @param int $uid user id
 * @param string $type (data, html)
 * @return mixed content
 */

function getUserLessonInfo($uid, $type) {
    global $session;

    //	TODO: add the new fields for memory in the db
    if ($session->status == USER_STUDENT) {    
        $user_courses = "SELECT course.id course_id,
                                course.code code,
                                course.public_code,
                                course.title title,
                                course.prof_names professor,
                                course.lang,
                                course_user.status status,
                                user.announce_flag,
                                user.doc_flag,
                                user.forum_flag
                             FROM course, course_user, user
                             WHERE course.id = course_user.course_id AND
                                   course_user.user_id = $uid AND
                                   user.id = $uid AND
                                   course.visible != " . COURSE_INACTIVE . "
                             ORDER BY course.title, course.prof_names";
    } elseif ($session->status == USER_TEACHER) {            
        $user_courses = "SELECT course.id course_id,
                                course.code code,
                                course.public_code,
	                            course.title title,
                                course.prof_names professor,
	                            course.lang,
	                            course_user.status status,
	                            user.announce_flag,
                                user.doc_flag,
                                user.forum_flag
	                         FROM course, course_user, user
                             WHERE course.id = course_user.course_id AND
	                               course_user.user_id = $uid AND
	                               user.id = $uid
                             ORDER BY course.title, course.prof_names";
    }

    $lesson_titles = $lesson_publicCode = $lesson_id = $lesson_code = $lesson_professor = $lesson_status = array();
    $mysql_query_result = db_query($user_courses);
    $repeat_val = 0;
    //getting user's lesson info
    while ($mycourses = mysql_fetch_array($mysql_query_result)) {
        $lesson_id[$repeat_val] = $mycourses['course_id'];
        $lesson_titles[$repeat_val] = $mycourses['title'];
        $lesson_code[$repeat_val] = $mycourses['code'];
        $lesson_professor[$repeat_val] = $mycourses['professor'];
        $lesson_status[$repeat_val] = $mycourses['status'];
        $lesson_publicCode[$repeat_val] = $mycourses['public_code'];
        $repeat_val++;
    }

    $memory = "SELECT user.announce_flag, user.doc_flag, user.forum_flag
                      FROM user WHERE user.id = $uid";
    $memory_result = db_query($memory);
    while ($my_memory_result = mysql_fetch_row($memory_result)) {
        $lesson_announce_f = str_replace('-', ' ', $my_memory_result[0]);
        $lesson_doc_f = str_replace('-', ' ', $my_memory_result[1]);
        $lesson_forum_f = str_replace('-', ' ', $my_memory_result[2]);
    }
    $max_repeat_val = $repeat_val;
    $ret_val[0] = $max_repeat_val;
    $ret_val[1] = $lesson_titles;
    $ret_val[2] = $lesson_code;
    $ret_val[3] = $lesson_professor;
    $ret_val[4] = $lesson_status;
    $ret_val[5] = $lesson_announce_f;
    $ret_val[6] = $lesson_doc_f;
    $ret_val[7] = $lesson_forum_f;
    $ret_val[8] = $lesson_id;

    //check what sort of data should be returned
    if ($type == 'html') {
        return array($ret_val, htmlInterface($ret_val, $lesson_publicCode));
    } elseif ($type == 'data') {
        return $ret_val;
    }
}

/**
 * Function htmlInterface
 *
 * @param array $data
 * @param string $lesson_code (lesson's public code)
 * @return string HTML content for the documents block
 */
function htmlInterface($data, $lesson_code) {
    global $status, $urlServer, $langNotEnrolledToLessons, $langWelcomeProfPerso;
    global $langWelcomeStudPerso, $langWelcomeSelect;
    global $langUnregCourse, $langAdm, $uid, $themeimg;

    $lesson_content = '';
    if ($data[0] > 0) {
        $lesson_content .= "<table width='100%' class='tbl_lesson'>";
        for ($i = 0; $i < $data[0]; $i++) {
            $lesson_content .= "<tr>
			  <td align='left'><ul class='custom_list'><li>
			  <b><a href=\"${urlServer}courses/" . $data[2][$i] . "/\">" . q($data[1][$i]) . "</a> </b><span class='smaller'>(" . q($lesson_code[$i]) . ")</span>
			  <div class='smaller'>" . q($data[3][$i]) . "</div></li></ul></td>";
            $lesson_content .= "<td align='center'>";
            if ($data[4][$i] == USER_STUDENT) {
                $lesson_content .= "
				<a href='${urlServer}main/unregcours.php?cid=" . $data[8][$i] . "&amp;uid=" . $uid . "'>
				<img src='$themeimg/cunregister.png' title='$langUnregCourse' alt='$langUnregCourse'></a>";
            } elseif ($data[4][$i] == USER_TEACHER) {
                $lesson_content .= "
				<a href='${urlServer}modules/course_info/?from_home=true&amp;cid=" . $data[2][$i] . "'>
				<img src='$themeimg/tools.png' title='$langAdm' alt='$langAdm'></a>";
            }
            $lesson_content .= "</td></tr>";
        }
        $lesson_content .= "</table>";
    } else { // if we are not registered to courses
        $lesson_content .= "<p class='alert1'>$langNotEnrolledToLessons !</p><p><u>$langWelcomeSelect</u>:</p>";
        $lesson_content .= "<table width='100%'>";
        $lesson_content .= "<tr>";
        $lesson_content .= "<td align='left' width='10'><img src='$themeimg/arrow.png' alt='' /></td>";
        if ($status == USER_TEACHER) {
            $lesson_content .= "\n<td align='left'>$langWelcomeProfPerso</td>";
        } else {
            $lesson_content .= "\n<td align='left' >$langWelcomeStudPerso</td>";
        }
        $lesson_content .= "</tr>";
        $lesson_content .= "</table>";
    }
    return $lesson_content;
}