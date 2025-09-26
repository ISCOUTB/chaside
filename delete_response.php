<?php
require_once('../../config.php');

$id = required_param('id', PARAM_INT); // Response ID.
$courseid = required_param('courseid', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id);
$adminviewurl = new moodle_url('/blocks/chaside/admin_view.php', array('courseid' => $courseid));

require_login($course, false);
require_capability('block/chaside:viewreports', $context);

$response = $DB->get_record('block_chaside_responses', array('id' => $id), '*', MUST_EXIST);

$PAGE->set_url('/blocks/chaside/delete_response.php', array('id' => $id, 'courseid' => $courseid));
$PAGE->set_title(get_string('confirm_delete', 'block_chaside'));
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add(get_string('pluginname', 'block_chaside'), $adminviewurl);
$PAGE->navbar->add(get_string('admin_dashboard', 'block_chaside'), $adminviewurl);
$PAGE->navbar->add(get_string('confirm_delete', 'block_chaside'));

if ($confirm && confirm_sesskey()) {
    // Confirmed, proceed with deletion.
    $DB->delete_records('block_chaside_responses', array('id' => $id));
    redirect($adminviewurl, get_string('response_deleted_success', 'block_chaside'), 2);
}

// Show confirmation page.
echo $OUTPUT->header();

$user = $DB->get_record('user', array('id' => $response->userid), 
    'id, firstname, lastname, firstnamephonetic, lastnamephonetic, middlename, alternatename');
$message = get_string('deleteresponseconfirm', 'block_chaside', fullname($user));
$confirmurl = new moodle_url($PAGE->url, array('confirm' => 1));
$cancelurl = $adminviewurl;

echo $OUTPUT->confirm($message, $confirmurl, $cancelurl);

echo $OUTPUT->footer();
