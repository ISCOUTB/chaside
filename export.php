<?php
// This file is part of Moodle - http://moodle.org/

require_once('../../config.php');
require_once('block_chaside.php');

$courseid = required_param('courseid', PARAM_INT);
$format = required_param('format', PARAM_ALPHA);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$context = context_course::instance($courseid);

require_login($course);
require_capability('block/chaside:manage_responses', $context);

// Get all completed responses for the course
$responses = $DB->get_records_sql("
    SELECT cr.*, u.firstname, u.lastname, u.email
    FROM {block_chaside_responses} cr
    JOIN {user} u ON cr.userid = u.id
    WHERE cr.courseid = ? AND cr.is_completed = 1
    ORDER BY cr.timemodified DESC
", array($courseid));

if (empty($responses)) {
    redirect(new moodle_url('/blocks/chaside/manage.php', array('courseid' => $courseid)), 
             get_string('no_responses_yet', 'block_chaside'), null, 'error');
}

$facade = new ChasideFacade();
$export_data = array();

foreach ($responses as $response) {
    // Convert stdClass to array for the facade
    $response_array = (array) $response;
    $scores = $facade->calculate_scores($response_array);
    $top_areas = $facade->get_top_areas($scores, 3);
    
    $export_data[] = array(
        'student_id' => $response->userid,
        'student_name' => $response->firstname . ' ' . $response->lastname,
        'student_email' => $response->email,
        'completion_date' => date('Y-m-d H:i:s', $response->timemodified),
        'scientific_score' => $scores['C'],
        'humanistic_score' => $scores['H'],
        'artistic_score' => $scores['A'],
        'social_score' => $scores['S'],
        'entrepreneurial_score' => $scores['I'],
        'outdoor_score' => $scores['D'],
        'executive_score' => $scores['E'],
        'top_area_1' => isset($top_areas[0]) ? $top_areas[0]['area'] : '',
        'top_area_1_score' => isset($top_areas[0]) ? $top_areas[0]['score'] : '',
        'top_area_2' => isset($top_areas[1]) ? $top_areas[1]['area'] : '',
        'top_area_2_score' => isset($top_areas[1]) ? $top_areas[1]['score'] : '',
        'top_area_3' => isset($top_areas[2]) ? $top_areas[2]['area'] : '',
        'top_area_3_score' => isset($top_areas[2]) ? $top_areas[2]['score'] : ''
    );
}

$filename = 'chaside_results_course_' . $courseid . '_' . date('Y-m-d');

if ($format == 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $fp = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Add translated headers
    $headers = array(
        get_string('export_student_id', 'block_chaside'),
        get_string('export_student_name', 'block_chaside'),
        get_string('export_student_email', 'block_chaside'),
        get_string('export_completion_date', 'block_chaside'),
        get_string('export_scientific_score', 'block_chaside'),
        get_string('export_humanistic_score', 'block_chaside'),
        get_string('export_artistic_score', 'block_chaside'),
        get_string('export_social_score', 'block_chaside'),
        get_string('export_entrepreneurial_score', 'block_chaside'),
        get_string('export_outdoor_score', 'block_chaside'),
        get_string('export_executive_score', 'block_chaside'),
        get_string('export_top_area', 'block_chaside') . ' 1',
        get_string('export_top_area', 'block_chaside') . ' 1 ' . get_string('export_score', 'block_chaside'),
        get_string('export_top_area', 'block_chaside') . ' 2', 
        get_string('export_top_area', 'block_chaside') . ' 2 ' . get_string('export_score', 'block_chaside'),
        get_string('export_top_area', 'block_chaside') . ' 3',
        get_string('export_top_area', 'block_chaside') . ' 3 ' . get_string('export_score', 'block_chaside')
    );
    fputcsv($fp, $headers);
    
    // Add data
    foreach ($export_data as $row) {
        fputcsv($fp, $row);
    }
    
    fclose($fp);
    exit;
    
} elseif ($format == 'json') {
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.json"');
    echo json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
    
} else {
    print_error('invalidformat', 'block_chaside');
}