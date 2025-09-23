<?php
// This file is part of Moodle - http://moodle.org/

require_once('../../config.php');
require_once('block_chaside.php');

$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);
$userid = optional_param('userid', $USER->id, PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$context = context_course::instance($courseid);

require_login($course);

// Verificar permisos
if ($userid != $USER->id) {
    require_capability('block/chaside:view_reports', $context);
}

$PAGE->set_url('/blocks/chaside/view_results.php', array('courseid' => $courseid, 'blockid' => $blockid, 'userid' => $userid));
$PAGE->set_title(get_string('your_results', 'block_chaside'));
$PAGE->set_heading($course->fullname);
$PAGE->set_context($context);

// Obtener los resultados del usuario
$response = $DB->get_record('block_chaside_responses', array(
    'userid' => $userid,
    'courseid' => $courseid,
    'is_completed' => 1
));

if (!$response) {
    echo $OUTPUT->header();
    echo html_writer::tag('div', get_string('test_not_found', 'block_chaside'), array('class' => 'alert alert-warning'));
    echo html_writer::link(
        new moodle_url('/blocks/chaside/view.php', array('courseid' => $courseid, 'blockid' => $blockid)),
        get_string('start_test', 'block_chaside'),
        array('class' => 'btn btn-primary')
    );
    echo $OUTPUT->footer();
    exit;
}

$facade = new ChasideFacade();
$scores = array(
    'C' => $response->score_c,
    'H' => $response->score_h,
    'A' => $response->score_a,
    'S' => $response->score_s,
    'I' => $response->score_i,
    'D' => $response->score_d,
    'E' => $response->score_e
);

$top_areas = $facade->get_top_areas($scores);
$area_descriptions = $facade->get_area_descriptions();

echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('your_results', 'block_chaside'));

// Mostrar información del usuario si es administrador viendo resultados de otro usuario
if ($userid != $USER->id) {
    $user = $DB->get_record('user', array('id' => $userid));
    echo html_writer::tag('h3', fullname($user));
    echo html_writer::tag('p', 'Completion Date: ' . userdate($response->timemodified));
}

// Gráfico de barras simple con las puntuaciones
echo html_writer::tag('h3', get_string('area_scores', 'block_chaside'));

echo html_writer::start_tag('div', array('class' => 'chaside-results'));

$max_score = max($scores);
$colors = array(
    'C' => '#FF6B6B',
    'H' => '#4ECDC4', 
    'A' => '#45B7D1',
    'S' => '#96CEB4',
    'I' => '#FFEAA7',
    'D' => '#DDA0DD',
    'E' => '#98D8C8'
);

foreach ($scores as $area => $score) {
    $percentage = $max_score > 0 ? ($score / $max_score) * 100 : 0;
    $area_name = get_string("area_{$area}", 'block_chaside');
    
    echo html_writer::start_tag('div', array('class' => 'score-item mb-3'));
    echo html_writer::tag('label', "{$area_name}: {$score}", array('class' => 'score-label'));
    echo html_writer::start_tag('div', array('class' => 'progress'));
    echo html_writer::tag('div', '', array(
        'class' => 'progress-bar',
        'role' => 'progressbar',
        'style' => "width: {$percentage}%; background-color: {$colors[$area]};",
        'aria-valuenow' => $percentage,
        'aria-valuemin' => '0',
        'aria-valuemax' => '100'
    ));
    echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');
}

echo html_writer::end_tag('div');

// Mostrar las áreas principales
echo html_writer::tag('h3', get_string('top_areas', 'block_chaside'));

echo html_writer::start_tag('div', array('class' => 'top-areas'));

for ($i = 0; $i < min(3, count($top_areas)); $i++) {
    $area = $top_areas[$i]['area'];
    $score = $top_areas[$i]['score'];
    $area_name = get_string("area_{$area}", 'block_chaside');
    $description = get_string("desc_{$area}", 'block_chaside');
    
    echo html_writer::start_tag('div', array('class' => 'card mb-3'));
    echo html_writer::start_tag('div', array('class' => 'card-header'));
    echo html_writer::tag('h4', "{$i + 1}. {$area_name} ({$score} puntos)", array('class' => 'card-title'));
    echo html_writer::end_tag('div');
    echo html_writer::start_tag('div', array('class' => 'card-body'));
    echo html_writer::tag('p', $description, array('class' => 'card-text'));
    echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');
}

echo html_writer::end_tag('div');

// Botón para volver al curso
echo html_writer::start_tag('div', array('class' => 'mt-4'));
echo html_writer::link(
    new moodle_url('/course/view.php', array('id' => $courseid)),
    get_string('back_to_course', 'block_chaside'),
    array('class' => 'btn btn-secondary me-2')
);
echo html_writer::end_tag('div');

// CSS personalizado para mejorar la presentación
echo html_writer::start_tag('style');
echo '
.chaside-results .score-item {
    margin-bottom: 1rem;
}

.chaside-results .score-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: bold;
}

.chaside-results .progress {
    height: 25px;
}

.top-areas .card {
    border-left: 5px solid #007bff;
}
';
echo html_writer::end_tag('style');

echo $OUTPUT->footer();
