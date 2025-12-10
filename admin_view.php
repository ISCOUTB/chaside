<?php
require_once('../../config.php');
require_once($CFG->libdir.'/tablelib.php');

$courseid = required_param('courseid', PARAM_INT);
$blockid = optional_param('blockid', 0, PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id);

require_login($course, false);
require_capability('block/chaside:viewreports', $context);

$PAGE->set_url('/blocks/chaside/admin_view.php', array('courseid' => $courseid));
$PAGE->set_pagelayout('incourse');
$PAGE->set_title(get_string('admin_dashboard', 'block_chaside'));
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add(get_string('pluginname', 'block_chaside'));
$PAGE->navbar->add(get_string('admin_dashboard', 'block_chaside'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('admin_dashboard', 'block_chaside'));

// Add description
echo $OUTPUT->box(
    get_string('admin_dashboard_description', 'block_chaside'),
    'generalbox'
);

// Add statistics section
echo '<div class="row mb-4">';
echo '<div class="col-12">';
echo '<h4>' . get_string('statistics', 'block_chaside') . '</h4>';
echo '</div>';
echo '</div>';

// Calculate statistics
$context = context_course::instance($courseid);
$enrolled_students = get_enrolled_users($context, 'block/chaside:take_test');
$total_enrolled = count($enrolled_students);

// Get enrolled students in this course
$enrolled_students = get_enrolled_users($context, 'block/chaside:take_test');
$enrolled_ids = array_keys($enrolled_students);
$total_enrolled = count($enrolled_students);

// Get responses only for enrolled students
$all_responses = array();
$completed_responses = array();
if (!empty($enrolled_ids)) {
    list($insql, $params) = $DB->get_in_or_equal($enrolled_ids, SQL_PARAMS_NAMED);
    $all_responses = $DB->get_records_select('block_chaside_responses', "userid $insql", $params);
    $completed_responses = array_filter($all_responses, function($r) { return $r->is_completed; });
}
$total_completed = count($completed_responses);
$total_in_progress = count($all_responses) - $total_completed;
$completion_rate = $total_enrolled > 0 ? ($total_completed / $total_enrolled) * 100 : 0;

// Statistics cards
echo '<div class="row mb-4">';

// Total enrolled
echo '<div class="col-md-3 col-sm-6 mb-3">';
echo '<div class="card border-primary">';
echo '<div class="card-body text-center">';
echo '<i class="fa fa-users text-primary" style="font-size: 2em;"></i>';
echo '<h3 class="mt-2 mb-1">' . $total_enrolled . '</h3>';
echo '<p class="text-muted mb-0">' . get_string('enrolled_students', 'block_chaside') . '</p>';
echo '</div>';
echo '</div>';
echo '</div>';

// Completed tests
echo '<div class="col-md-3 col-sm-6 mb-3">';
echo '<div class="card border-success">';
echo '<div class="card-body text-center">';
echo '<i class="fa fa-check-circle text-success" style="font-size: 2em;"></i>';
echo '<h3 class="mt-2 mb-1">' . $total_completed . '</h3>';
echo '<p class="text-muted mb-0">' . get_string('total_completed', 'block_chaside') . '</p>';
echo '</div>';
echo '</div>';
echo '</div>';

// In progress
echo '<div class="col-md-3 col-sm-6 mb-3">';
echo '<div class="card border-warning">';
echo '<div class="card-body text-center">';
echo '<i class="fa fa-clock text-warning" style="font-size: 2em;"></i>';
echo '<h3 class="mt-2 mb-1">' . $total_in_progress . '</h3>';
echo '<p class="text-muted mb-0">' . get_string('in_progress', 'block_chaside') . '</p>';
echo '</div>';
echo '</div>';
echo '</div>';

// Completion rate
echo '<div class="col-md-3 col-sm-6 mb-3">';
echo '<div class="card border-info">';
echo '<div class="card-body text-center">';
echo '<i class="fa fa-chart-pie text-info" style="font-size: 2em;"></i>';
echo '<h3 class="mt-2 mb-1">' . number_format($completion_rate, 1) . '%</h3>';
echo '<p class="text-muted mb-0">' . get_string('completion_rate', 'block_chaside') . '</p>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '</div>';

// Download CSV button (if there are completed responses)
if ($total_completed > 0) {
    $download_url = new moodle_url('/blocks/chaside/export.php', array('courseid' => $courseid, 'format' => 'csv'));
    echo '<div class="row mb-3">';
    echo '<div class="col-12">';
    echo '<a href="' . $download_url . '" class="btn btn-success">';
    echo '<i class="fa fa-download"></i> ' . get_string('download_all_results', 'block_chaside');
    echo '</a>';
    echo '</div>';
    echo '</div>';
}

// Area statistics (if there are completed responses)
if ($total_completed > 0) {
    echo '<div class="row mb-4">';
    echo '<div class="col-12">';
    echo '<h5>' . get_string('area_statistics', 'block_chaside') . '</h5>';
    echo '<div class="card">';
    echo '<div class="card-body">';
    
    // Calculate area preferences
    require_once('block_chaside.php');
    $facade = new ChasideFacade();
    $area_totals = array('C' => 0, 'H' => 0, 'A' => 0, 'S' => 0, 'I' => 0, 'D' => 0, 'E' => 0);
    $area_counts = array('C' => 0, 'H' => 0, 'A' => 0, 'S' => 0, 'I' => 0, 'D' => 0, 'E' => 0);
    
    foreach ($completed_responses as $response) {
        $response_array = (array) $response;
        $scores = $facade->calculate_scores($response_array);
        foreach ($scores as $area => $score) {
            $area_totals[$area] += $score;
            if ($score > 0) {
                $area_counts[$area]++;
            }
        }
    }
    
    echo '<div class="row">';
    foreach ($area_totals as $area => $total) {
        $area_name = get_string('area_' . strtolower($area), 'block_chaside');
        $avg_score = $area_counts[$area] > 0 ? $total / $area_counts[$area] : 0;
        $popularity = $total_completed > 0 ? ($area_counts[$area] / $total_completed) * 100 : 0;
        
        echo '<div class="col-md-6 col-lg-4 mb-3">';
        echo '<div class="border rounded p-3">';
        echo '<h6 class="mb-2">' . $area . ' - ' . $area_name . '</h6>';
        echo '<div class="mb-2">';
        echo '<small class="text-muted">' . get_string('average_score', 'block_chaside') . ':</small> ';
        echo '<strong>' . number_format($avg_score, 1) . '/14</strong>';
        echo '</div>';
        echo '<div class="progress mb-1" style="height: 8px;">';
        $progress_width = ($avg_score / 14) * 100;
        echo '<div class="progress-bar bg-primary" style="width: ' . $progress_width . '%"></div>';
        echo '</div>';
        echo '<small class="text-muted">' . number_format($popularity, 1) . '% ' . get_string('preference', 'block_chaside') . '</small>';
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';
    
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}

// Students table section
echo '<div class="row">';
echo '<div class="col-12">';
echo '<h5>' . get_string('student_responses', 'block_chaside') . '</h5>';
echo '</div>';
echo '</div>';

$table = new flexible_table('block_chaside_report');
$table->define_columns(array('user', 'timemodified', 'viewresults', 'actions'));
$table->define_headers(array(
    get_string('student', 'block_chaside'), 
    get_string('completiondate', 'block_chaside'), 
    get_string('viewresults', 'block_chaside'),
    get_string('actions', 'block_chaside')
));
$table->set_attribute('class', 'admintable');
$table->define_baseurl($PAGE->url);

$table->setup();

// Get responses for enrolled students who have completed the test (in any course)
$responses_to_display = array();
if (!empty($enrolled_ids)) {
    list($insql, $params) = $DB->get_in_or_equal($enrolled_ids, SQL_PARAMS_NAMED, 'user');
    $params['completed'] = 1;
    
    $sql = "SELECT * FROM {block_chaside_responses} 
            WHERE userid $insql AND is_completed = :completed";
    $responses_to_display = $DB->get_records_sql($sql, $params);
}

if ($responses_to_display) {
    foreach ($responses_to_display as $response) {
        $user = $DB->get_record('user', array('id' => $response->userid), 'id, firstname, lastname, picture, imagealt, firstnamephonetic, lastnamephonetic, middlename, alternatename, email');
        if (!$user) {
            continue;
        }

        $userpicture = new user_picture($user);
        $userpicture->size = 35;
        $usercell = $OUTPUT->user_picture($user, array('size' => 35, 'courseid' => $courseid)) . ' ' . fullname($user);

        $viewresultsurl = new moodle_url('/blocks/chaside/view_results.php', array(
            'courseid' => $courseid,
            'blockid' => $blockid,
            'userid' => $response->userid
        ));
        
        $viewresultsbutton = html_writer::link(
            $viewresultsurl, 
            $OUTPUT->pix_icon('i/report', get_string('viewresults', 'block_chaside')),
            array(
                'class' => 'btn btn-sm btn-outline-primary',
                'title' => get_string('viewresults', 'block_chaside')
            )
        );

        $deleteurl = new moodle_url('/blocks/chaside/delete_response.php', array(
            'id' => $response->id,
            'courseid' => $courseid,
            'sesskey' => sesskey()
        ));
        
        $deletebutton = html_writer::link(
            $deleteurl, 
            $OUTPUT->pix_icon('t/delete', get_string('deleteresponse', 'block_chaside')),
            array(
                'class' => 'btn btn-sm btn-outline-danger',
                'title' => get_string('deleteresponse', 'block_chaside'),
                'onclick' => 'return confirm("' . get_string('deleteresponseconfirm', 'block_chaside', fullname($user)) . '");'
            )
        );

        $row = array(
            $usercell,
            userdate($response->timemodified, get_string('strftimedatetimeshort')),
            $viewresultsbutton,
            $deletebutton
        );
        $table->add_data($row);
    }
    
    $table->print_html();
} else {
    echo $OUTPUT->notification(get_string('no_responses_found', 'block_chaside'), 'notifyinfo');
}

echo $OUTPUT->footer();
