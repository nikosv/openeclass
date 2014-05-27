<?php

/* ========================================================================
 * Open eClass 
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
 * ======================================================================== 
 */

class CourseXMLConfig {

    /**
     * Returns the path of a specific course's XML file.
     * 
     * @global string $webDir
     * @param  string $courseCode
     * @return string
     */
    public static function getCourseXMLPath($courseCode) {
        global $webDir;
        return $webDir . '/courses/' . $courseCode . '/courseMetadata.xml';
    }

    /**
     * Enumeration values for HTML Form fields.
     * @param  string $key
     * @return array
     */
    public static function getEnumerationValues($key) {
        $valArr = array(
            'course_level' => array('undergraduate' => $GLOBALS['langCMeta']['undergraduate'],
                'graduate' => $GLOBALS['langCMeta']['graduate'],
                'doctoral' => $GLOBALS['langCMeta']['doctoral']),
            'course_curriculumLevel' => array('undergraduate' => $GLOBALS['langCMeta']['undergraduate'],
                'graduate' => $GLOBALS['langCMeta']['graduate'],
                'doctoral' => $GLOBALS['langCMeta']['doctoral']),
            'course_yearOfStudy' => array('1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6'),
            'course_semester' => array('1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6',
                '7' => '7', '8' => '8', '9' => '9', '10' => '10', '11' => '11', '12' => '12'),
            'course_type' => array('compulsory' => $GLOBALS['langCMeta']['compulsory'],
                'optional' => $GLOBALS['langCMeta']['optional']),
            'course_format' => array('slides' => $GLOBALS['langCMeta']['slides'],
                'notes' => $GLOBALS['langCMeta']['notes'],
                'video lectures' => $GLOBALS['langCMeta']['video lectures'],
                'podcasts' => $GLOBALS['langCMeta']['podcasts'],
                'audio material' => $GLOBALS['langCMeta']['audio material'],
                'multimedia material' => $GLOBALS['langCMeta']['multimedia material'],
                'interactive exercises' => $GLOBALS['langCMeta']['interactive exercises'])
        );

        if (isset($valArr[$key])) {
            return $valArr[$key];
        } else {
            return array();
        }
    }

    /**
     * Link value for HTML Form labels.
     * 
     * @param  string $key
     * @return string
     */
    public static function getLinkedValue($key) {
        global $urlServer, $code_cours, $currentCourseLanguage;

        $infocours = $urlServer . 'modules/course_info/infocours.php?course=' . $code_cours;
        $coursedesc = $urlServer . 'modules/course_description/index.php?course=' . $code_cours;
        $coursehome = $urlServer . 'courses/' . $code_cours . '/index.php';
        $clang = langname_to_code($currentCourseLanguage);

        $valArr = array(
            'course_title_' . $clang => $infocours,
            'course_instructor_fullName_' . $clang => $infocours,
            'course_language' => $infocours,
            'course_keywords_' . $clang => $infocours,
            'course_unit_title_' . $clang => $coursehome,
            'course_unit_description_' . $clang => $coursehome,
            'course_numberOfUnits' => $coursehome,
            'course_license' => $infocours,
            'course_contents_' . $clang => $coursedesc,
            'course_objectives_' . $clang => $coursedesc,
            'course_literature_' . $clang => $coursedesc,
            'course_teachingMethod_' . $clang => $coursedesc,
            'course_assessmentMethod_' . $clang => $coursedesc,
            'course_prerequisites_' . $clang => $coursedesc
        );

        if (isset($valArr[$key])) {
            return $valArr[$key];
        } else {
            return null;
        }
    }

    /**
     * Provide the field name for multiplicity fields. 
     * 
     * @param  string      $field
     * @return string|null
     */
    public static function getMultipleFieldName($field) {
        $valArr = array(
            'course_instructor_photo' => 'photo'
        );

        if (isset($valArr[$field])) {
            return $valArr[$field];
        } else {
            return null;
        }
    }

    /**
     * XPaths to locate the parents of multiplicity fields.
     * 
     * @param  string      $field
     * @return string|null
     */
    public static function getMultipleFieldParentXPath($field) {
        $valArr = array(
            'course_instructor_photo' => '/n:course/n:instructor'
        );

        if (isset($valArr[$field])) {
            return $valArr[$field];
        } else {
            return null;
        }
    }

    /**
     * Array HTML Form fields.
     * @var array
     */
    public static $arrayFields = array(
        'course_unit_keywords'
    );

    /**
     * Binary HTML Form fields.
     * @var array
     */
    public static $binaryFields = array(
        'course_instructor_photo', 'course_coursePhoto'
    );

    /**
     * Boolean/dropdown HTML Form fields.
     * @var array
     */
    public static $booleanFields = array(
        'course_coTeaching', 'course_coTeachingColleagueOpensCourse',
        'course_coTeachingAutonomousDepartment', 'course_confirmCurriculum',
        'course_confirmVideolectures'
    );

    /**
     * UI Accordion End Break points.
     * @var array
     */
    public static $breakAccordionEndFields = array(
        'course_acknowledgments_en',
        'course_coTeachingDepartmentCreditHours',
        'course_kalliposURL'
    );

    /**
     * UI Accordion Start Break points.
     * @var array
     */
    public static $breakAccordionStartFields = array(
        'course_code_el',
        'course_instructor_moreInformation_el',
        'course_yearOfStudy'
    );

    /**
     * UI Tabs Break points.
     * @var array
     */
    public static $breakFields = array(
        'course_acknowledgments_en' => '2',
        'course_coTeachingDepartmentCreditHours' => '3',
        'course_kalliposURL' => '4'
    );

    /**
     * Enumeration HTML Form fields.
     * @var array
     */
    public static $enumerationFields = array(
        'course_level', 'course_curriculumLevel', 'course_yearOfStudy',
        'course_semester', 'course_type'
    );

    /**
     * Fields that should be hidden from the HTML Form.
     * @var array
     */
    public static $hiddenFields = array(
        'course_unit_material_notes', 'course_unit_material_slides',
        'course_unit_material_exercises', 'course_unit_material_multimedia_title',
        'course_unit_material_multimedia_speaker', 'course_unit_material_multimedia_subject',
        'course_unit_material_multimedia_description', 'course_unit_material_multimedia_keywords',
        'course_unit_material_multimedia_url', 'course_unit_material_other',
        'course_unit_material_digital_url', 'course_unit_material_digital_library',
        'course_confirmAMinusLevel', 'course_confirmALevel', 'course_confirmAPlusLevel',
        'course_lastLevelConfirmation', 'course_firstCreateDate', 'course_videolectures'
    );

    /**
     * Fields that should be hidden from anonymous users.
     * @var array
     */
    public static $hiddenFromAnonymousFields = array(
        'course_credits', 'course_structure', 'course_assessmentMethod', 'course_assignments'
    );

    /**
     * Integer HTML Form fields.
     * @var array
     */
    public static $integerFields = array(
        'course_credithours', 'course_coTeachingDepartmentCreditHours',
        'course_credits', 'course_numberOfUnits'
    );

    /**
     * Linked HTML Form labels.
     * @var array 
     */
    public static $linkedFields = array(
        'course_title', 'course_instructor_fullName',
        'course_language', 'course_keywords',
        'course_unit_title', 'course_unit_description',
        'course_numberOfUnits', 'course_license',
        'course_contents', 'course_objectives', 'course_literature',
        'course_teachingMethod', 'course_assessmentMethod',
        'course_prerequisites'
    );

    /**
     * Mandatory HTML Form fields.
     * @var array
     */
    public static $mandatoryFields = array(
        'course_instructor_firstName_el', 'course_instructor_firstName_en',
        'course_instructor_lastName_el', 'course_instructor_lastName_en',
        'course_instructor_fullName_el', 'course_instructor_fullName_en',
        'course_title_el', 'course_title_en',
        'course_level', 'course_url', 'course_license_el', 'course_license_en',
        'course_description_el', 'course_description_en',
        'course_contents_el', 'course_contents_en',
        'course_objectives_el', 'course_objectives_en',
        'course_prerequisites_el', 'course_prerequisites_en',
        'course_literature_el', 'course_literature_en',
        'course_thematic_el', 'course_thematic_en',
        'course_institution_el', 'course_institution_en',
        'course_department_el', 'course_department_en',
        'course_curriculumLevel',
        'course_confirmCurriculum', 'course_confirmVideolectures',
        'course_language_el', 'course_language_en'
    );

    /**
     * Multiple enumartion HTML Form fields.
     * @var array
     */
    public static $multiEnumerationFields = array(
        'course_format'
    );

    /**
     * Fields with multiplicity.
     * @var array
     */
    public static $multipleFields = array(
        'course_instructor_photo'
    );

    /**
     * Fields that should be readonly in the HTML Form.
     * @var array
     */
    public static $readOnlyFields = array(
        'course_instructor_fullName', 'course_title',
        'course_url', 'course_keywords', 'course_numberOfUnits',
        'course_unit_title', 'course_unit_description',
        'course_contents', 'course_objectives', 'course_literature',
        'course_teachingMethod', 'course_assessmentMethod',
        'course_prerequisites'
    );

    /**
     * MultiLang Fields that should be readonly in the HTML Form.
     * @var array
     */
    public static $readOnlyMultiLangFields = array(
        'course_language_el', 'course_language_en',
        'course_license_el', 'course_license_en'
    );

    /**
     * Textarea HTML Form fields.
     * @var array
     */
    public static $textareaFields = array(
        'course_instructor_moreInformation', 'course_instructor_cv',
        'course_targetGroup', 'course_description',
        'course_contents', 'course_objectives',
        'course_contentDevelopment', 'course_featuredBooks', 'course_structure',
        'course_teachingMethod', 'course_assessmentMethod',
        'course_prerequisites', 'course_literature',
        'course_recommendedComponents', 'course_assignments',
        'course_requirements', 'course_remarks', 'course_acknowledgments',
        'course_thematic', 'course_institutionDescription',
        'course_curriculumDescription', 'course_outcomes',
        'course_curriculumTargetGroup'
    );

}