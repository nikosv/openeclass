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


if (isset($_POST['token']))
{
    $require_mlogin = true;
    $require_noerrors = true;
    require_once ('minit.php');
    
    if (isset($_REQUEST['logout']))
    {
        require_once ('../../include/CAS/CAS.php');
        require_once ('../auth/auth.inc.php');

        if (isset($_SESSION['uid']))
            db_query("INSERT INTO loginout (loginout.id_user, loginout.ip, loginout.when, loginout.action)
                                    VALUES ($_SESSION[uid], '$_SERVER[REMOTE_ADDR]', NOW(), 'LOGOUT')");

        if (isset($_SESSION['cas_uname'])) // if we are CAS user
            define('CAS', true);

        foreach(array_keys($_SESSION) as $key)
            unset($_SESSION[$key]);

        session_destroy();

        if (defined('CAS')) {
            $cas = get_auth_settings(7);
            if (isset($cas['cas_ssout']) and intval($cas['cas_ssout']) === 1) {
                phpCAS::client(SAML_VERSION_1_1, $cas['cas_host'], intval($cas['cas_port']), $cas['cas_context'], FALSE);
                phpCAS::logoutWithRedirectService($urlServer);
            }
        }

        echo RESPONSE_OK;
        exit();
    }
    
    if (isset($_REQUEST['redirect']))
    {
        header('Location: '. urldecode($_REQUEST['redirect']));
        exit();
    }
    
    echo RESPONSE_OK;
    exit();
}


if (isset($_POST['uname']) && isset($_POST['pass']))
{
    $require_noerrors = true;
    require_once ('minit.php');
    require_once ('../../include/CAS/CAS.php');
    require_once ('../auth/auth.inc.php');
    
    $uname = autounquote(canonicalize_whitespace($_POST['uname']));
    $pass = autounquote($_POST['pass']);
    
    foreach(array_keys($_SESSION) as $key)
        unset($_SESSION[$key]);
    $_SESSION['user_perso_active'] = false;
    
    $sqlLogin = "SELECT user_id, nom, username, password, prenom, statut, email, perso, lang, verified_mail
                   FROM user 
                  WHERE username COLLATE utf8_bin = " . quote($uname);
    $result = db_query($sqlLogin);
    
    while ($myrow = mysql_fetch_assoc($result)) 
        $ok = login($myrow, $uname, $pass);
    
    if (isset($_SESSION['uid']) && $ok == 1) {
        db_query("INSERT INTO loginout (loginout.id_user, loginout.ip, loginout.when, loginout.action)
                                VALUES ($_SESSION[uid], '$_SERVER[REMOTE_ADDR]', NOW(), 'LOGIN')");
        
        set_session_mvars();
        echo session_id();
    } else
        echo RESPONSE_FAILED;

    exit();
}


function set_session_mvars()
{
    $status = array();
    
    $sql = "SELECT course.id course_id, course.code code, course.public_code,
                   course.title title, course.prof_names profs, course_user.statut statut
              FROM course JOIN course_user ON course.id = course_user.course_id
             WHERE course_user.user_id = ". $_SESSION['uid'] ."
          ORDER BY statut, course.title, course.prof_names";
    $sql2 = "SELECT course.id course_id, course.code code, course.public_code,
                    course.title title, course.prof_names profs, course_user.statut statut
               FROM course JOIN course_user ON course.id = course_user.course_id
              WHERE course_user.user_id = ". $_SESSION['uid'] ."
                AND course.visible != ".COURSE_INACTIVE."
           ORDER BY statut, course.title, course.prof_names";

    if ($_SESSION['statut'] == 1)
        $result = db_query($sql);

    if ($_SESSION['statut'] == 5)
        $result = db_query($sql2);

    if ($result and mysql_num_rows($result) > 0)
        while ($mycours = mysql_fetch_array($result))
            $status[$mycours['code']] = $mycours['statut'];

    $_SESSION['status'] = $status;
    $_SESSION['mobile'] = true;
    
    if ($GLOBALS['persoIsActive'] and $GLOBALS['userPerso'] == 'no')
        $_SESSION['user_perso_active'] = true;
}
