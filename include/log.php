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

define('LOG_INSERT', 1);
define('LOG_MODIFY', 2);
define('LOG_DELETE', 3);

class Log {
        // log users actions
        public static function record($module_id, $action_type, $details) {
                
                global $course_id;
                                
                db_query("INSERT INTO log SET 
                                user_id = $_SESSION[uid],
                                course_id = $course_id,
                                module_id = $module_id,
                                details = ".quote(serialize($details)).",
                                action_type = $action_type,
                                ts = NOW(),
                                ip = '$_SERVER[SERVER_ADDR]'");
                return;
        }
        
        // display users actions 
        public function display($course_id, $user_id, $module_id, $logtype, $date_from, $date_now) {
                 
                global $tool_content, $langDate, $langUser, $langAction, $langDetail;
                
                $q1 = $q2 = $q3 = '';
                if ($user_id != -1) {
                        $q1 = "AND user_id = $user_id";
                }
                if ($module_id != -1) {
                        $q2 = "AND module_id = $module_id";
                }
                if ($logtype != 0) {
                        $q3 = "AND action_type = $logtype";
                }                                                           
                $sql = db_query("SELECT user_id, details, action_type, ts FROM log
                                        WHERE course_id = $course_id $q1 $q2 $q3 
                                        AND ts BETWEEN '$date_from' AND '$date_now'");
                $tool_content .= "<table class='tbl'><tr>";
                $tool_content .= "<th>$langDate</th><th>$langUser</th><th>$langAction</th><th>$langDetail</th>";
                $tool_content .= "</tr>";
                while ($r = mysql_fetch_array($sql)) {
                        $tool_content .= "<tr>";
                        $tool_content .= "<td>".nice_format($r['ts'], true)."</td>";               
                        $tool_content .= "<td>".display_user($r['user_id'])."</td>";
                        $tool_content .= "<td>".$this->get_action_names($r['action_type'])."</td>";
                        $tool_content .= "<td>".$this->action_details($module_id, $r['action_type'], $r['details'])."</td>";
                        $tool_content .= "</tr>";
                }
                $tool_content .= "</table>";         
                return;
        }
 
        private function action_details($module_id, $action_type, $details) {
                                                         
                switch ($module_id) {
                        case MODULE_ID_ANNOUNCE: $content = $this->announcement_action_details($action_type, $details);
                                break;
                        }
                return $content;
        }
        
        private function announcement_action_details($action_type, $details) {
                
                global $langAnnAdd, $langWithTitle, $langWithContent, $langAnd,
                        $langThe, $langAnnouncement, $langWithID, $langAnnDel;
                
                $details = unserialize($details);
                
                switch ($action_type) {
                        case LOG_INSERT: 
                                $content = "$langAnnAdd $langWithID ".$details['id'].",
                                                  $langWithTitle '".$details['title'].
                                                "' $langAnd $langWithContent '".$details['content']."'.";
                                break;
                        case LOG_MODIFY: break;
                        case LOG_DELETE: 
                                $content = "$langAnnDel $langWithID ".$details['id'].".";
                                break;
                }
                return $content;
        }
        
        // return the real action names
        private function get_action_names($action_type) {
                
                global $langInsert, $langModify, $langDelete, $langUnknownAction;
                
                switch ($action_type) {
                        case LOG_INSERT: return $langInsert;
                        case LOG_MODIFY: return $langModify;
                        case LOG_DELETE: return $langDelete;
                        default: return $langUnknownAction;
                }
        }
}