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
 * @file portfolio.php
 * @brief This component creates the content of the start page when the user is logged in
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 */

$require_login = true;

$require_help = true;
$helpTopic = 'Portfolio';

include '../include/baseTheme.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/lib/fileUploadLib.inc.php';

$nameTools = $langWelcomeToPortfolio;

ModalBoxHelper::loadModalBox();

if(!empty($langLanguageCode)){
    load_js('bootstrap-calendar-master/js/language/'.$langLanguageCode.'.js');
}
load_js('bootstrap-calendar-master/js/calendar.js');
load_js('bootstrap-calendar-master/components/underscore/underscore-min.js');
load_js('datatables');

$head_content .= "
<link rel='stylesheet' type='text/css' href='{$urlAppend}js/bootstrap-calendar-master/css/calendar_small.css' />"
."<script type='text/javascript'>
jQuery(document).ready(function() {
  jQuery('#portfolio_lessons').dataTable({
    'bLengthChange': false,
    'bSort' : false,
    'oLanguage': {
           'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
           'sZeroRecords':  '".$langNoResult."',
           'sInfo':         '$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults',
           'sInfoEmpty':    '$langDisplayed 0 $langTill 0 $langFrom2 0 $langResults2',
           'sInfoFiltered': '',
           'sInfoPostFix':  '',
           'sSearch':       '".$langSearch."',
           'sUrl':          '',
           'oPaginate': {
               'sFirst':    '&laquo;',
               'sPrevious': '&lsaquo;',
               'sNext':     '&rsaquo;',
               'sLast':     '&raquo;'
           }
       }    
  });
  jQuery('.panel_content').hide();
   jQuery('.panel_content_open').show();
  jQuery('.panel_title').click(function()
  {
    var mypanel = $(this).next();
    mypanel.slideToggle(100);
    if($(this).hasClass('active')) {
    $(this).removeClass('active');
    } else {
    $(this).addClass('active');
    }
  });"
  .'var calendar = $("#bootstrapcalendar").calendar({
                    tmpl_path: "'.$urlAppend.'js/bootstrap-calendar-master/tmpls/",
                    events_source: "'.$urlAppend.'main/calendar_data.php",
                    language: "'.$langLanguageCode.'",
                    views: {year:{enable: 0}, week:{enable: 0}, day:{enable: 0}},
                    onAfterViewLoad: function(view) {
                                $("#current-month").text(this.getTitle());
                                $(".btn-group button").removeClass("active");
                                $("button[data-calendar-view=\'" + view + "\']").addClass("active");
                                }
        });
        
        $(".btn-group button[data-calendar-nav]").each(function() {
            var $this = $(this);
            $this.click(function() {
                calendar.navigate($this.data("calendar-nav"));
            });
        });

        $(".btn-group button[data-calendar-view]").each(function() {
            var $this = $(this);
            $this.click(function() {
                calendar.view($this.data("calendar-view"));
            });
        });'
."});
".
'function show_month(day,month,year){
    $.get("calendar_data.php",{caltype:"small", day:day, month: month, year: year}, function(data){$("#smallcal").html(data);});    
}
</script>';

require_once 'perso.php';

$tool_content = "
<div class='row margin-top-fat'>
        <div class='col-md-7'>
                <h5 class='content-title'>{%LANG_MY_PERSO_LESSONS%}</h5>
                <div class='panel'>
                        {%LESSON_CONTENT%}                        
                </div>
        </div>

        <div class='col-md-5'>
        <div class='row'>
        <div class='col-md-12'>
                <h5 class='content-title'>{%LANG_MY_PERSONAL_CALENDAR%}</h5>
                <div class='panel padding'>
                        {%PERSONAL_CALENDAR_CONTENT%}
                </div>
        </div></div>
        ";
        if ($user_announcements) {
            $tool_content .= "
                <div class='row'>
        <div class='col-md-12'>
                <h5 class='content-title'>{%LANG_MY_PERSO_ANNOUNCEMENTS%}</h5>
                <div class='panel'>
                        <ul class='tablelist panel'>
                        $user_announcements 
                        </ul>
                </div>
                </div>
        </div></div>";
}

$tool_content .= "
</div>
<div class='row'>
    <div class='col-md-12'>
        <div class='panel'>
            <div class='panel-body'>
                <div class='row'>
                    <div class='col-sm-3'>
                            <div><img src='" . user_icon($uid, IMAGESIZE_LARGE) . "' style='width:150px;' class='img-circle' alt='Circular Image'></div>
                    </div>
                    <div class='col-sm-9'>
                        <div> 
                                Test area:
                                <div class='btn-group'>
                                        <button type='button' class='btn btn-default dropdown-toggle' data-toggle='dropdown'>
                                            Πληροφορίες Μαθήματος
                                            <span class='caret'></span>
                                        </button>

                                        
                                        
                                        

                                        <ul class='dropdown-menu' role='menu'>
                                            <li><a href='#''>Επιλογή 1</a></li>
                                            <li><a href='#''>Επιλογή 2/a></li>
                                            <li><a href='#''>Επιλογή 3</a></li>
                                            <li class='divider'></li>
                                            <li><a href='#''>Επιλογή 4</a></li>
                                        </ul>
                                </div>


                                <div class='tbl'>
                                    TEST
                                </div>


                                <canvas id='canvas' height='150' width='600'></canvas>

                        </div>
                    </div> 
                </div>
            </div>
        </div>
    </div>
</div>
";
draw($tool_content, 1, null, $head_content, null, null, $perso_tool_content);
