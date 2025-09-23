<?php
// This file is part of Moodle - http://moodle.org/

require_once('../../config.php');
require_once($CFG->libdir.'/tablelib.php');
require_once('block_chaside.php');

$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$context = context_course::instance($courseid);

require_login($course);
require_capability('block/chaside:manage_responses', $context);

$PAGE->set_url('/blocks/chaside/manage.php', array('courseid' => $courseid, 'blockid' => $blockid));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('management_title', 'block_chaside'));
$PAGE->set_heading(get_string('management_title', 'block_chaside'));

echo $OUTPUT->header();

// Get all students enrolled in the course
$enrolled_students = get_enrolled_users($context, 'block/chaside:take_test');

// Get all responses for this course
$responses = $DB->get_records('block_chaside_responses', array('courseid' => $courseid));

// Calculate statistics
$total_enrolled = count($enrolled_students);
$total_completed = 0;
$total_in_progress = 0;
$completion_times = array();

foreach ($responses as $response) {
    if ($response->is_completed) {
        $total_completed++;
        if ($response->timecompleted > 0) {
            $completion_times[] = $response->timecompleted - $response->timemodified;
        }
    } else {
        $total_in_progress++;
    }
}

$participation_rate = $total_enrolled > 0 ? round(($total_completed / $total_enrolled) * 100, 1) : 0;
$avg_completion_time = !empty($completion_times) ? round(array_sum($completion_times) / count($completion_times) / 60, 1) : 0;

// Display statistics
echo '<div class="row mb-4">';
echo '<div class="col-md-12">';
echo '<h3>' . get_string('statistics', 'block_chaside') . '</h3>';
echo '<div class="row">';

echo '<div class="col-md-3">';
echo '<div class="card">';
echo '<div class="card-body text-center">';
echo '<h5 class="card-title">' . $total_enrolled . '</h5>';
echo '<p class="card-text">' . get_string('enrolled_students', 'block_chaside') . '</p>';
echo '</div></div></div>';

echo '<div class="col-md-3">';
echo '<div class="card">';
echo '<div class="card-body text-center">';
echo '<h5 class="card-title">' . $total_completed . '</h5>';
echo '<p class="card-text">' . get_string('total_completed', 'block_chaside') . '</p>';
echo '</div></div></div>';

echo '<div class="col-md-3">';
echo '<div class="card">';
echo '<div class="card-body text-center">';
echo '<h5 class="card-title">' . $participation_rate . '%</h5>';
echo '<p class="card-text">' . get_string('participation_rate', 'block_chaside') . '</p>';
echo '</div></div></div>';

echo '<div class="col-md-3">';
echo '<div class="card">';
echo '<div class="card-body text-center">';
echo '<h5 class="card-title">' . $avg_completion_time . ' min</h5>';
echo '<p class="card-text">' . get_string('avg_completion_time', 'block_chaside') . '</p>';
echo '</div></div></div>';

echo '</div></div></div>';

// Download button
if ($total_completed > 0) {
    $download_url = new moodle_url('/blocks/chaside/export.php', array('courseid' => $courseid, 'format' => 'csv'));
    echo '<div class="mb-3">';
    echo '<a href="' . $download_url . '" class="btn btn-success">' . get_string('download_all_results', 'block_chaside') . '</a>';
    echo '</div>';
}

// Students table
echo '<h3>' . get_string('student_responses', 'block_chaside') . '</h3>';

if (!empty($enrolled_students)) {
    $table = new html_table();
    $table->head = array(
        get_string('student_name', 'block_chaside'),
        get_string('completion_status', 'block_chaside'),
        get_string('response_date', 'block_chaside'),
        get_string('top_area', 'block_chaside'),
        get_string('view_details', 'block_chaside')
    );
    $table->data = array();

    $facade = new ChasideFacade();

    foreach ($enrolled_students as $student) {
        $response = isset($responses[$student->id]) ? $responses[$student->id] : null;
        
        $status = get_string('not_started', 'block_chaside');
        $date = '-';
        $top_area = '-';
        $view_link = '-';
        
        if ($response) {
            if ($response->is_completed) {
                $status = '<span class="badge badge-success">' . get_string('completed', 'block_chaside') . '</span>';
                $date = userdate($response->timecompleted);
                
                // Calculate top area
                $scores = $facade->calculate_scores($response);
                $top_areas = $facade->get_top_areas($scores, 1);
                if (!empty($top_areas)) {
                    $top_area = get_string('area_' . strtolower($top_areas[0]['area']), 'block_chaside');
                }
                
                // View details link
                $view_url = new moodle_url('/blocks/chaside/view_results.php', array(
                    'courseid' => $courseid,
                    'blockid' => $blockid,
                    'userid' => $student->id
                ));
                $view_link = html_writer::link($view_url, get_string('view_details', 'block_chaside'), array('class' => 'btn btn-sm btn-outline-primary'));
            } else {
                $status = '<span class="badge badge-warning">' . get_string('in_progress', 'block_chaside') . '</span>';
                $date = userdate($response->timemodified);
            }
        }
        
        $table->data[] = array(
            fullname($student),
            $status,
            $date,
            $top_area,
            $view_link
        );
    }
    
    echo html_writer::table($table);
} else {
    echo '<p>' . get_string('no_responses_yet', 'block_chaside') . '</p>';
}

echo $OUTPUT->footer();