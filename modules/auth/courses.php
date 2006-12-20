<?php
/*
=============================================================================
GUnet e-Class 2.0
E-learning and Course Management Program
================================================================================
Copyright(c) 2003-2006  Greek Universities Network - GUnet
A full copyright notice can be read in "/info/copyright.txt".

Authors:     Costas Tsibanis <k.tsibanis@noc.uoa.gr>
Yannis Exidaridis <jexi@noc.uoa.gr>
Alexandros Diamantidis <adia@noc.uoa.gr>

For a full list of contributors, see "credits.txt".

This program is a free software under the terms of the GNU
(General Public License) as published by the Free Software
Foundation. See the GNU License for more details.
The full license can be read in "license.txt".

Contact address: GUnet Asynchronous Teleteaching Group,
Network Operations Center, University of Athens,
Panepistimiopolis Ilissia, 15784, Athens, Greece
eMail: eclassadmin@gunet.gr
==============================================================================
*/

/**
 * Lesson enrollment component
 * 
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * 
 * @abstract This component shows a list of courses available for registration
 *
 */
$require_login = TRUE;
$langFiles = array('registration', 'opencours');

include '../../include/baseTheme.php';

$nameTools = $langOtherCourses;

$tool_content = "";

$icons = array(
2 => "<img src=\"../../template/classic/img/OpenCourse.gif\" alt=\"\">",
1 => "<img src=\"../../template/classic/img/Registration.gif\" alt=\"\">",
0 => "<img src=\"../../template/classic/img/ClosedCourse.gif\" alt=\"\">"
);

if (isset($_REQUEST['fc'])) {
	$_SESSION['fc_memo'] = $_REQUEST['fc'];
}

if (!isset($_REQUEST['fc']) && isset($_SESSION['fc_memo'])) {
	$fc = $_SESSION['fc_memo'];
}

if (isset($_POST["submit"])) {
	if (isset($changeCourse) && is_array($changeCourse)) {
		// check if user tries to unregister from restricted course
		foreach ($changeCourse as $key => $value) {
			if (!isset($selectCourse[$key]) and is_restricted($value)) {
				$tool_content .= "$m[unsub] $value ";
			}
		}
		foreach ($changeCourse as $value) {
			db_query("DELETE FROM cours_user WHERE statut <> 1
				AND statut <> 10 AND user_id = '$uid' AND code_cours = '$value'");
		}
	}
	
	$errorExists = false;
	if (isset($selectCourse) and is_array($selectCourse)) {
		
		while (list($key,$contenu) = each ($selectCourse)) {
			$sqlcheckpassword = mysql_query("SELECT password FROM cours WHERE code='".$contenu."'");
			$myrow = mysql_fetch_array($sqlcheckpassword);
			if ($myrow['password']!="" && $myrow['password']!=$$contenu) {
				$errorExists = true;
				//				$tool_content .= "<p>".$langWrongPassCourse." ".$contenu."</p>";
			} else {
				$sqlInsertCourse =
				"INSERT INTO `cours_user`
						(`code_cours`, `user_id`, `statut`, `role`)
						VALUES ('".$contenu."', '".$uid."', '5', ' ')"; 
				mysql_query($sqlInsertCourse) ;
				if (mysql_errno() > 0) echo mysql_errno().": ".mysql_error()."<br>";
			}
		}
	}
	if (!$errorExists)	{

		$tool_content .="
	<table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"success\">
							<p><b>$langIsReg</b></p>
							<p><a href=\"../../index.php\">$langHome</a></p>
						</td>
					</tr>
				</tbody>
			</table>";
	} else {

		$tool_content .="
	<table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"caution\">
							<p><b>$langWrongPassCourse $contenu</b></p>
							<p><a href=\"../../index.php\">$langHome</a></p>
						</td>
					</tr>
				</tbody>
			</table>";
	}
}
else
{

	// check if user requested a specific faculte
	if (isset( $_GET['fc'] ) ) {
		// get faculte name from db
		$fac = getfacfromfc( $_GET['fc'] );
		$facid = $_GET['fc'];
	} else {
		// get faculte name from user's department column
		$facid = getfacfromuid($uid);
		$fac = getfacnamefromfacid($facid);
	}

	if ($facid==0) {

		$tool_content .= "
		<table>
		<thead>
		<tr>
		<th>
		".$m['department']."
 		</th><th> $langAvCourses
 		</th></tr></thead>
 		<tbody>
 	";

		$tool_content .= collapsed_facultes_vert(0);
		$tool_content .= "</tbody></table";
	} else {
		// department exists
		$nameTools = $opencours;
		$navigation[] = array ("url"=>"courses.php", "name"=> $langOtherCourses);
		$tool_content .= "<p><b>".$m['department'].": </b>".$fac." (<a href=\"$_SERVER[PHP_SELF]?fc=0\">$langOtherDepartments</a>)</p>";

		$tool_content .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"post\">";
		$formend = "<br/>
				<input type=\"submit\" name=\"submit\" value=\"$langSubscribe\">
			";

		$numofcourses = getdepnumcourses($facid);

		// display all the facultes collapsed
		//		$tool_content .= "<tr><td><b>".$langCoursesLabel."</b></td</tr><tr><td>".collapsed_facultes_horiz($facid)."</td></tr>";
		if ( $numofcourses > 0 ) {
			$tool_content .= expanded_faculte($facid, $uid);
		} else {
			$tool_content .= "<p>$langNoLessonsAvailable</p>";
		}

	} // end of else (department exists)

	if (isset($formend) && $numofcourses>0)
	$tool_content .= $formend;
}

draw($tool_content,1,'admin');

function getfacfromfc( $dep_id) {
	$dep_id = intval( $dep_id);

	$fac = mysql_fetch_row(mysql_query(
	"SELECT name FROM faculte WHERE id = '$dep_id'"));
	if ( isset($fac[0] ) )
	return $fac[0];
	else
	return 0;
}

function getfacfromuid($uid) {
	$res = mysql_fetch_row(mysql_query(
	"SELECT id
	FROM faculte,user
	WHERE user.user_id = '$uid'
		AND faculte.id = user.department"));
	if ( isset($res[0]) )
	return $res[0];
	else
	return 0;
}

function getfacnamefromfacid($facid) {
	$res = mysql_fetch_row(mysql_query("SELECT name FROM faculte WHERE id='".$facid."'"));
	if ( isset($res[0]) )
	return $res[0];
	else
	return "";
}

function getdepnumcourses($facid) {
	$res = mysql_fetch_row(mysql_query(
	"SELECT count(*)
	FROM cours_faculte
	WHERE facid='$facid'" ));
	return $res[0];
}


function expanded_faculte($facid, $uid) {
	global $m, $icons, $langTitular, $langBegin, $mysqlMainDb;
	global $langRegistration;
	$retString = "";

	// build a list of  course follow  by  user.
	$sqlListOfCoursesOfUser = "
	SELECT 
		code_cours cc,
		statut ss
	FROM `$mysqlMainDb`.cours_user
	WHERE user_id = ".$uid;

	$listOfCoursesOfUser = mysql_query($sqlListOfCoursesOfUser);

	// build array of user's courses
	while ($rowMyCourses = mysql_fetch_array($listOfCoursesOfUser)) {
		$myCourses[$rowMyCourses["cc"]]["subscribed"]= TRUE;
		$myCourses[$rowMyCourses["cc"]]["statut"]= $rowMyCourses["ss"];
	}

	// get the different course types available for this faculte
	$typesresult = mysql_query(
	"SELECT DISTINCT cours.type types
		FROM cours 
		WHERE cours.faculteid = '$facid' 
		ORDER BY cours.type");

	// count the number of different types
	$numoftypes = mysql_num_rows($typesresult);
	// output the nav bar only if we have more than 1 types of courses
	if ( $numoftypes > 1) {
		//		$retString .= "<font class=\"courses\">";
		$retString .= "<div id=\"operations_container\">
						<ul id=\"opslist\">";
		$counter = 1;
		while ($typesArray = mysql_fetch_array($typesresult)) {
			$t = $typesArray['types'];
			// make the plural version of type (eg pres, posts, etc)
			// this is for fetching the proper translations
			// just concatenate the s char in the end of the string
			$ts = $t."s";
			//type the seperator in front of the types except the 1st
			//			if ($counter != 1) $retString .= " | ";S
			$retString .= "<li><a href=\"#".$t."\">".$m["$ts"]."</a></li>";
			$counter++;
		}
		$retString .=  "</ul></div>";
		//		$retString .= "</font>";
	}

	// now output the legend
	$retString .= "<table width=\"99%\"><thead><tr><th>".$m['legend'].":</th><td>
	".$icons[2]	." ".$m['legopen']." </td><td> ".$icons[1]
	." ".$m['legrestricted']
	."</td><td>"
	.$icons[0]
	." ".$m['legclosed']."</td></tr></thead></table><br/>";

	// changed this foreach statement a bit
	// this way we sort by the course types
	// then we just select visible
	// and finally we do the secondary sort by course title and but teacher's name
	foreach (array("pre" => $m['pres'],
	"post" => $m['posts'],
	"other" => $m['others']) as $type => $message) {
		$result=mysql_query("SELECT
						cours.code k,
						cours.fake_code c,
						cours.intitule i,
						cours.visible visible,
						cours.titulaires t,
						cours.password p
			        FROM cours_faculte, cours
			        WHERE cours.code = cours_faculte.code
							      AND cours.type = '$type'
                		AND cours_faculte.facid='$facid'
		                ORDER BY cours.intitule, cours.titulaires");

		if (mysql_num_rows($result) == 0) {
			continue;
		}

		// We changed the style a bit here and we output types as the title
		//		$retString .= "<tr><td><b><a name=\"$type\" class=\"largeorange\">$message</a>:</b></td></tr>\n";
		$retString .= "
			<table width = \"99%\">
			<thead>
			<tr>
				<th colspan=\"5\"><a name=\"$type\">$message</th>
			</tr>
			<tr>
				<th>".$m['type']."</th>
				<th>".$m['name']."</th>
				<th>".$m['code']."</th>
				<th>".$m['prof']."</th>
				<th>$langRegistration</th>
			</tr>
			
			";
		$rowCounter = 0;
		while ($mycours = mysql_fetch_array($result)) {
			// changed the variable because of the previous change in the select argument
			if ($mycours['visible'] == 2) {
				$codelink = "<td>".$mycours['c']."</td><td><a href='../../courses/$mycours[k]/' target=\"blank\">$mycours[i]</a></td>";
			} else {
				$codelink = "<td>".$mycours['c']."</td><td>".$mycours['i']."</td>";
			}

			// output each course as a table for beautifying reasons
			//			$retString .= "<tr><td>";

			// show the necessary access icon
			foreach ( $icons as $visible => $image) {
				if ( $visible == $mycours['visible'] ) {
					$retString .= "<tr><td>$image</td>";
				}
			}
			if ($mycours["visible"]==0 && !isset ($myCourses[$mycours["k"]]["subscribed"])) {
				$contactprof = $m['mailprof']."<a href=\"contactprof.php?fc=".$facid."&cc=".$mycours['k']."\">".$m['here']."</a>";
				$retString .= $codelink;
			} else {
				$retString .= $codelink;
			}

			if ($mycours["visible"]>0 && (isset ($myCourses[$mycours["k"]]["subscribed"]) || !isset ($myCourses[$mycours["k"]]["subscribed"]))) {
				$retString .= "<input type='hidden' name='changeCourse[]' value='$mycours[k]'>\n";
				@$retString .= "<td>".$mycours['t']."</td>";
				
			} elseif ($mycours["visible"]== 0 && isset ($myCourses[$mycours["k"]]["subscribed"])) {
				$retString .= "<td>".$mycours['t']."</td>";
				
			} else {
				$retString .= "<td>$mycours[t]</td><td>".$contactprof."</td>";
				
			}

			if (isset ($myCourses[$mycours["k"]]["subscribed"])) {

				if ($myCourses[$mycours["k"]]["statut"]!=1) {
					if($mycours["visible"]==0) $disabled = "disabled";
					else $disabled = "";
					$retString .= "<td><input type='checkbox' name='selectCourse[]' value='$mycours[k]' checked $disabled></td>";
					if ($mycours['p']!="" && $mycours['visible'] == 1) {
						$requirepassword = $m['code'].": <input type=\"password\" name=\"".$mycours['k']."\" value=\"".$mycours['p']."\">";

					}
					else {

						$requirepassword = "";
					}

				} else {

					$retString .= "<td>[$langTitular]</td>";
				}
				$retString .= "</tr>";
			}
			else {

				if ($mycours['p']!="" && $mycours['visible'] == 1) {

					$requirepassword = $m['code'].": <input type=\"password\" name=\"".$mycours['k']."\">";
				} else {

					$requirepassword = "";
				}
				if ($mycours["visible"]>0  || isset ($myCourses[$mycours["k"]]["subscribed"])) {

					$retString .= "<td><input type='checkbox' name='selectCourse[]' value='$mycours[k]'> $requirepassword</td>";
				}
			}

			$rowCounter++;

			if ($rowCounter%15==0) {
				$retString .= "
				<tr>
				<th>".$m['type']."</th>
				<th>".$m['name']."</th>
				<th>".$m['code']."</th>
				<th>".$m['prof']."</th>
				<th>$langRegistration</th>
			</tr>
				";

			}


		}
		//		$requirepassword = "";
		$retString .= "</thead></table>";
		// output a top href link if necessary
		if ( $numoftypes > 1)
		$retString .= "<p><a href=\"#top\">".$langBegin."</a></p>";

		// that's it!
		// upatras.gr patch end
	}

	return $retString;
}

function collapsed_facultes_vert($facid) {

	global $avlesson, $avlessons;
	$retString = "";

	$result = mysql_query(
	"SELECT DISTINCT cours.faculte f, faculte.id id
		FROM cours, faculte 
		WHERE faculte.id = cours.faculteid
			AND faculte.id <> '$facid'
		ORDER BY cours.faculte");

	while ($fac = mysql_fetch_array($result)) {
		$retString .= "<tr><td>";
		$retString .= "<a href=\"?fc=$fac[id]\"><b>$fac[f]</b></a></td>";

		$n = mysql_query("SELECT COUNT(*) FROM cours
			WHERE cours.faculteid='$fac[id]'");
		$r = mysql_fetch_array($n);
		$retString .= " <td>$r[0]</td>";
		$retString .= "</tr>";
	}

	return $retString;
}

function collapsed_facultes_horiz($facid) {
	$retString = "";

	$result = mysql_query(
	"SELECT DISTINCT faculte.id id, faculte.name f
		FROM faculte 
		ORDER BY name");
	$counter = 1;
	while ($facs = mysql_fetch_array($result)) {
		if ($counter != 1) $retString .= "<font class=\"small\"> | </font>";
		if ($facs['id'] != $facid)
		$codelink = "<a href=\"?fc=$facs[id]\" class=\"small\">$facs[f]</a>";
		else
		$codelink = "<font class=\"small\">$facs[f]</font>";

		$retString .= $codelink;
		$counter++;
	}

	return $retString;
}

function is_restricted($course)
{
	$res = mysql_fetch_row(db_query("SELECT visible FROM cours
		WHERE code = ".quote($course)));
	if ($res[0] == 0) {
		return TRUE;
	} else {
		return FALSE;
	}
}

?>
