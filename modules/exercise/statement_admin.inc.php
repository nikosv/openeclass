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

load_js('bootstrap-slider');
$head_content .= " 
<script>
$(function() {
    var diffArray = ['$langQuestionVeryEasy', '$langQuestionEasy', '$langQuestionModerate', '$langQuestionDifficult', '$langQuestionVeryDifficult']
    $('#questionDifficulty').slider({
	formatter: function(value) {
		return diffArray[value-1]+' ('+value+')';
	}    
    });
    $('input[name=answerType]').click(hideGrade);
    $('input#free_text_selector').click(showGrade);
    function hideGrade(){
        $('input[name=questionGrade]').prop('disabled', true).closest('div.form-group').addClass('hide');    
    }
    function showGrade(){
        $('input[name=questionGrade]').prop('disabled', false).closest('div.form-group').removeClass('hide');    
    }    
 });
</script>
 ";
// the question form has been submitted
if (isset($_POST['submitQuestion'])) {
    $v = new Valitron\Validator($_POST);
    $v->rule('required', ['questionName']);
    $v->labels(array(
        'questionName' => "$langTheField $langQuestion"
    ));
    if($v->validate()) {
        $questionName = trim($questionName);
        $questionDescription = purify($questionDescription);
        // no name given
        if (empty($questionName)) {
            $msgErr = $langGiveQuestion;
        }
        if (isset($_GET['modifyQuestion'])) {
            $objQuestion->read($_GET['modifyQuestion']);
        }
        $objQuestion->updateTitle($questionName);
        $objQuestion->updateDescription($questionDescription);
        $objQuestion->updateType($answerType);
        $objQuestion->updateDifficulty($difficulty);

        //If grade field set (only in Free text questions)
        if (isset($questionGrade)) {
            $objQuestion->updateWeighting($questionGrade);
        }
        (isset($exerciseId)) ? $objQuestion->save($exerciseId) : $objQuestion->save();
        $questionId = $objQuestion->selectId();
        // upload or delete picture
        if (isset($_POST['deletePicture'])) {
            $objQuestion->removePicture();
        } elseif (isset($_FILES['imageUpload']) && is_uploaded_file($_FILES['imageUpload']['tmp_name'])) {

            require_once 'include/lib/fileUploadLib.inc.php';
            validateUploadedFile($_FILES['imageUpload']['name'], 2);

            $type = $_FILES['imageUpload']['type'];
            if (!$objQuestion->uploadPicture($_FILES['imageUpload']['tmp_name'], $type)) {
                $tool_content .= "<div class='alert alert-danger'>$langInvalidPicture</div>";
            }
        }
        if (isset($exerciseId)) {
            // adds the question ID into the question list of the Exercise object
            if ($objExercise->addToList($questionId)) {
                $objExercise->save();
                $nbrQuestions++;
            }
        }
        //if the answer type is free text (which means doesn't have predefined answers) 
        //redirects to either pool or edit exercise page
        //else it redirect to modifyanswers page in order to add answers to question
        if ($answerType == FREE_TEXT) {
            $redirect_url = (isset($exerciseId)) ? "modules/exercise/admin.php?course=$course_code&exerciseId=$exerciseId" : "modules/exercise/question_pool.php?course=$course_code";
        } else {
            $redirect_url = "modules/exercise/admin.php?course=$course_code".((isset($exerciseId))? "&exerciseId=$exerciseId" : "")."&modifyAnswers=$questionId";
        }
        redirect_to_home_page($redirect_url);
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        $new_or_modif = isset($_GET['modifyQuestion']) ? "&modifyQuestion=$_GET[modifyQuestion]" : "&newQuestion=yes";
        $exercise_or_pool = isset($_GET['exerciseId']) ? "&exerciseId=$_GET[exerciseId]" : "";
        redirect_to_home_page("modules/exercise/admin.php?course={$course_code}{$exercise_or_pool}{$new_or_modif}");
    }
} else {
// if we don't come here after having cancelled the warning message "used in several exercises"
    if (!isset($buttonBack)) {
        $questionName = $objQuestion->selectTitle();
        $questionDescription = $objQuestion->selectDescription();
        $answerType = $objQuestion->selectType();
        $difficulty = $objQuestion->selectDifficulty();
        $questionWeight = $objQuestion->selectWeighting();
    }
}
if (isset($_GET['newQuestion']) || isset($_GET['modifyQuestion'])) {
    $questionId = $objQuestion->selectId();
    // is picture set ?
    $okPicture = file_exists($picturePath . '/quiz-' . $questionId) ? true : false;
    // if there is an error message
    if (!empty($msgErr)) {
        $tool_content .= "<div class='alert alert-danger'>$msgErr</div>\n";
    }

    
    if (isset($_GET['newQuestion'])){
        $form_submit_action = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;".((isset($exerciseId))? "exerciseId=$exerciseId" : "")."&amp;newQuestion=" . urlencode($_GET['newQuestion']);
        $link_back = isset($exerciseId) ? "admin.php?course=$course_code&exerciseId=$exerciseId" : "question_pool.php?course=$course_code";
        
    } else {
        $form_submit_action = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;".((isset($exerciseId))? "exerciseId=$exerciseId" : "")."&amp;modifyQuestion=" . urlencode($_GET['modifyQuestion']);
        $link_back = "admin.php?course=$course_code".(isset($exerciseId) ? "&exerciseId=$exerciseId" : "").(isset($_GET['newQuestion']) ? "&editQuestion=$_GET[newQuestion]" : "&editQuestion=$_GET[modifyQuestion]");
    }  
    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => $link_back,
            'icon' => 'fa-reply',
            'level' => 'primary-label'
        )
    ));
    
    $tool_content .= "<div class='form-wrapper'><form class='form-horizontal' role='form' enctype='multipart/form-data' method='post' action='$form_submit_action'>";
    $tool_content .= "
            <div class='form-group ".(Session::getError('questionName') ? "has-error" : "")."'>
                <label for='questionName' class='col-sm-2 control-label'>$langQuestion:</label>
                <div class='col-sm-10'>
                  <input name='questionName' type='text' class='form-control' id='questionName' placeholder='$langQuestion' value='" . q($questionName) . "'>
                  <span class='help-block'>".Session::getError('questionName')."</span>
                </div>
            </div>
            <div class='form-group'>
                <label for='questionDescription' class='col-sm-2 control-label'>$langQuestionDescription:</label>
                <div class='col-sm-10'>
                  ". rich_text_editor('questionDescription', 4, 50, $questionDescription) ."
                </div>
            </div>
            <div class='form-group'>
                <label for='questionDifficulty' class='col-sm-2 control-label'>Βαθμός δυσκολίας:</label>
                <div class='col-sm-10'>
                    <input id='questionDifficulty' name='difficulty' data-slider-id='ex1Slider' type='text' data-slider-min='1' data-slider-max='5' data-slider-step='1' data-slider-value='$difficulty'/>
                </div>
            </div>            
            <div class='form-group'>
                <label for='imageUpload' class='col-sm-2 control-label'>".(($okPicture) ? $langReplacePicture : $langAddPicture).":</label>
                <div class='col-sm-10'>
                  ".(($okPicture) ? "<img src='../../$picturePath/quiz-$questionId'><br><br>" : "")."
                  <input type='file'  name='imageUpload' id='imageUpload'> 
                </div>
            </div>";
    if ($okPicture) {
        $tool_content .= "
            <div class='form-group'>
		<label class='col-sm-2 control-label'>$langDeletePicture:</label>
                <div class='col-sm-10'>            
                    <div class='checkbox'>
                      <label>    
                        <input type='checkbox' name='deletePicture' value='1' ".(isset($_POST['deletePicture'])? "checked":"").">
                      </label>
                    </div>
                </div>
            </div>";
    }
$tool_content .= "<div class='form-group'>
                <label class='col-sm-2 control-label'>$langAnswerType:</label>
                <div class='col-sm-10'>            
                    <div class='radio'>
                      <label>
                        <input type='radio' name='answerType' value='1' ". (($answerType == UNIQUE_ANSWER) ? "checked" : "") .">
                        $langUniqueSelect
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='answerType' value='2' ". (($answerType == MULTIPLE_ANSWER) ? "checked" : "") .">
                       $langMultipleSelect
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='answerType' value='3' ". (($answerType == FILL_IN_BLANKS) ? "checked" : "") .">
                       $langFillBlanks
                      </label>
                    </div>                       
                    <div class='radio'>
                      <label>
                        <input type='radio' name='answerType' value='4' ". (($answerType == MATCHING) ? "checked" : "") .">
                       $langMatching
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='answerType' value='5' ". (($answerType == TRUE_FALSE) ? "checked" : "") .">
                       $langTrueFalse
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input type='radio' id='free_text_selector' name='answerType' value='6' ". (($answerType == FREE_TEXT) ? "checked" : "") .">
                       $langFreeText
                      </label>
                    </div>                       
                </div>
            </div>
            <div class='form-group ".(($answerType != 6) ? "hide": "")."'>
                <label for='questionGrade' class='col-sm-2 control-label'>$m[grade]:</label>
                <div class='col-sm-10'>
                  <input name='questionGrade' type='text' class='form-control' id='questionGrade' placeholder='$m[grade]' value='$questionWeight'".(($answerType != 6) ? " disabled": "").">
                </div>
            </div>
            <div class='row'>
                <div class='col-sm-10 col-sm-offset-2 '>            
                    <input type='submit' class='btn btn-primary' name='submitQuestion' value='$langOk'>
                    <a href='$link_back' class='btn btn-default'>$langCancel</a>      
                </div>
            </div>
          </fieldset>
	</form>
    </div>    
    ";
}
