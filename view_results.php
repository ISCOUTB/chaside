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
    require_capability('block/chaside:viewreports', $context);
}

$PAGE->set_url('/blocks/chaside/view_results.php', array('courseid' => $courseid, 'blockid' => $blockid, 'userid' => $userid));
$PAGE->set_title(get_string('your_results', 'block_chaside'));
$PAGE->set_heading($course->fullname);
$PAGE->set_context($context);

// Obtener los resultados del usuario
$response = $DB->get_record('block_chaside_responses', array(
    'userid' => $userid,
    'courseid' => $courseid
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

// Generate official CHASIDE results
$facade = new ChasideFacade();
$response_array = (array) $response;

// Get user information
$user = $DB->get_record('user', array('id' => $userid));
$meta = array(
    'nombre' => fullname($user),
    'curso' => $course->shortname,
    'fecha_aplicacion' => date('Y-m-d', $response->timemodified),
    'version_instrumento' => 'CHASIDE v1.0'
);

$results = $facade->generate_results_json($response_array, $meta);

echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('your_results', 'block_chaside'));

// Mostrar información del usuario si es administrador viendo resultados de otro usuario
if ($userid != $USER->id) {
    echo html_writer::tag('h3', fullname($user));
    echo html_writer::tag('p', get_string('completion_date_label', 'block_chaside') . ' ' . userdate($response->timemodified));
}

// Executive Summary Section
echo html_writer::start_tag('div', array('class' => 'chaside-executive-summary mb-4'));
echo html_writer::tag('h3', get_string('executive_summary', 'block_chaside'));

// Top areas display
echo html_writer::start_tag('div', array('class' => 'row mb-3'));

if ($results['resumen_ejecutivo']['top1']) {
    $top1 = $results['resumen_ejecutivo']['top1'];
    echo html_writer::start_tag('div', array('class' => 'col-md-6'));
    echo html_writer::start_tag('div', array('class' => 'card border-primary'));
    echo html_writer::start_tag('div', array('class' => 'card-header bg-primary text-white'));
    echo html_writer::tag('h4', '🥇 ' . get_string('your_top_area', 'block_chaside'), array('class' => 'mb-0'));
    echo html_writer::end_tag('div');
    echo html_writer::start_tag('div', array('class' => 'card-body'));
    echo html_writer::tag('h5', $top1['label'], array('class' => 'card-title'));
    echo html_writer::tag('p', 'Puntuación Total: ' . $top1['total'] . '/14 (' . $top1['pct_total'] . '%)', array('class' => 'card-text'));
    echo html_writer::tag('p', 'Intereses: ' . $top1['i'] . '/10 | Aptitudes: ' . $top1['a'] . '/4', array('class' => 'card-text small text-muted'));
    
    // Progress bar for top1
    echo html_writer::start_tag('div', array('class' => 'progress mb-2', 'style' => 'height: 20px;'));
    echo html_writer::tag('div', $top1['pct_total'] . '%', array(
        'class' => 'progress-bar bg-primary',
        'style' => 'width: ' . $top1['pct_total'] . '%;',
        'role' => 'progressbar'
    ));
    echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');
}

if ($results['resumen_ejecutivo']['top2']) {
    $top2 = $results['resumen_ejecutivo']['top2'];
    echo html_writer::start_tag('div', array('class' => 'col-md-6'));
    echo html_writer::start_tag('div', array('class' => 'card border-secondary'));
    echo html_writer::start_tag('div', array('class' => 'card-header bg-secondary text-white'));
    echo html_writer::tag('h4', '🥈 Segunda Área Principal', array('class' => 'mb-0'));
    echo html_writer::end_tag('div');
    echo html_writer::start_tag('div', array('class' => 'card-body'));
    echo html_writer::tag('h5', $top2['label'], array('class' => 'card-title'));
    echo html_writer::tag('p', 'Puntuación Total: ' . $top2['total'] . '/14 (' . $top2['pct_total'] . '%)', array('class' => 'card-text'));
    echo html_writer::tag('p', 'Intereses: ' . $top2['i'] . '/10 | Aptitudes: ' . $top2['a'] . '/4', array('class' => 'card-text small text-muted'));
    
    // Progress bar for top2
    echo html_writer::start_tag('div', array('class' => 'progress mb-2', 'style' => 'height: 20px;'));
    echo html_writer::tag('div', $top2['pct_total'] . '%', array(
        'class' => 'progress-bar bg-secondary',
        'style' => 'width: ' . $top2['pct_total'] . '%;',
        'role' => 'progressbar'
    ));
    echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');
}

echo html_writer::end_tag('div'); // row

// Quick reading
if (!empty($results['resumen_ejecutivo']['lectura_rapida'])) {
    echo html_writer::start_tag('div', array('class' => 'alert alert-info'));
    echo html_writer::tag('h5', get_string('quick_reading', 'block_chaside'));
    echo html_writer::tag('p', $results['resumen_ejecutivo']['lectura_rapida'], array('class' => 'mb-0'));
    echo html_writer::end_tag('div');
}

// Gap alerts
if (!empty($results['resumen_ejecutivo']['alertas_brecha'])) {
    echo html_writer::start_tag('div', array('class' => 'alert alert-warning'));
    echo html_writer::tag('h5', get_string('gap_alerts', 'block_chaside'));
    foreach ($results['resumen_ejecutivo']['alertas_brecha'] as $alert) {
        echo html_writer::tag('span', $alert['area'] . ': ' . $alert['tipo'], array('class' => 'badge badge-warning mr-2'));
    }
    echo html_writer::end_tag('div');
}

echo html_writer::end_tag('div'); // executive summary

// Detailed Results Table
echo html_writer::start_tag('div', array('class' => 'chaside-detailed-table mb-4'));
echo html_writer::tag('h3', get_string('detailed_table', 'block_chaside'));

echo html_writer::start_tag('div', array('class' => 'table-responsive'));
echo html_writer::start_tag('table', array('class' => 'table table-striped table-bordered'));
echo html_writer::start_tag('thead', array('class' => 'thead-dark'));
echo html_writer::start_tag('tr');
echo html_writer::tag('th', 'Área', array('scope' => 'col'));
echo html_writer::tag('th', get_string('interests', 'block_chaside') . ' (0-10)', array('scope' => 'col'));
echo html_writer::tag('th', get_string('aptitudes', 'block_chaside') . ' (0-4)', array('scope' => 'col'));
echo html_writer::tag('th', get_string('total', 'block_chaside') . ' (0-14)', array('scope' => 'col'));
echo html_writer::tag('th', get_string('percentage', 'block_chaside'), array('scope' => 'col'));
echo html_writer::tag('th', get_string('level', 'block_chaside'), array('scope' => 'col'));
echo html_writer::tag('th', get_string('gap', 'block_chaside'), array('scope' => 'col'));
echo html_writer::tag('th', get_string('interpretation', 'block_chaside'), array('scope' => 'col'));
echo html_writer::end_tag('tr');
echo html_writer::end_tag('thead');

echo html_writer::start_tag('tbody');

foreach ($results['tabla_principal'] as $row) {
    // Determine row styling based on top areas
    $row_class = '';
    $row_style = '';
    if ($results['resumen_ejecutivo']['top1'] && $row['area'] == $results['resumen_ejecutivo']['top1']['area']) {
        $row_class = 'table-primary';
        $row_style = 'border-left: 4px solid #007bff;';
    } elseif ($results['resumen_ejecutivo']['top2'] && $row['area'] == $results['resumen_ejecutivo']['top2']['area']) {
        $row_class = 'table-secondary';
        $row_style = 'border-left: 4px solid #6c757d;';
    }
    
    echo html_writer::start_tag('tr', array('class' => $row_class, 'style' => $row_style));
    echo html_writer::tag('td', html_writer::tag('strong', $row['label']));
    echo html_writer::tag('td', $row['interes']['score'] . ' (' . $row['interes']['pct'] . '%)');
    echo html_writer::tag('td', $row['aptitud']['score'] . ' (' . $row['aptitud']['pct'] . '%)');
    echo html_writer::tag('td', html_writer::tag('strong', $row['total']['score'] . ' (' . $row['total']['pct'] . '%)'));
    
    // Level with badge
    $level_class = 'badge-secondary';
    switch ($row['nivel']) {
        case get_string('level_alto', 'block_chaside'):
            $level_class = 'badge-success';
            break;
        case get_string('level_medio', 'block_chaside'):
            $level_class = 'badge-primary';
            break;
        case get_string('level_emergente', 'block_chaside'):
            $level_class = 'badge-warning';
            break;
        case get_string('level_bajo', 'block_chaside'):
            $level_class = 'badge-secondary';
            break;
    }
    echo html_writer::tag('td', html_writer::tag('span', $row['nivel'], array('class' => 'badge ' . $level_class)));
    
    // Gap with badge
    $gap_class = 'badge-light';
    if ($row['brecha'] == get_string('gap_interest_higher', 'block_chaside')) {
        $gap_class = 'badge-info';
    } elseif ($row['brecha'] == get_string('gap_aptitude_higher', 'block_chaside')) {
        $gap_class = 'badge-success';
    }
    echo html_writer::tag('td', html_writer::tag('span', $row['brecha'], array('class' => 'badge ' . $gap_class)));
    
    echo html_writer::tag('td', html_writer::tag('small', $row['interpretacion_breve']));
    echo html_writer::end_tag('tr');
}

echo html_writer::end_tag('tbody');
echo html_writer::end_tag('table');
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');

// Recommendations Section
echo html_writer::start_tag('div', array('class' => 'chaside-recommendations mb-4'));
echo html_writer::tag('h3', get_string('recommendations', 'block_chaside'));
echo html_writer::start_tag('div', array('class' => 'alert alert-info'));
echo html_writer::start_tag('ul', array('class' => 'mb-0'));
foreach ($results['recomendaciones'] as $recommendation) {
    echo html_writer::tag('li', $recommendation);
}
echo html_writer::end_tag('ul');
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');

// Guidance Note
echo html_writer::start_tag('div', array('class' => 'chaside-guidance mb-4'));
echo html_writer::tag('h4', get_string('guidance_note', 'block_chaside'));
echo html_writer::start_tag('div', array('class' => 'alert alert-secondary'));
echo html_writer::tag('p', $results['apendice_opcional']['nota'], array('class' => 'mb-0'));
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');

// Visual Chart Section (Simple bar chart with CSS)
echo html_writer::start_tag('div', array('class' => 'chaside-visual-chart mb-4'));
echo html_writer::tag('h3', 'Gráfico de Puntuaciones Totales');

$colors = array(
    'C' => '#FF6B6B',
    'H' => '#4ECDC4', 
    'A' => '#45B7D1',
    'S' => '#96CEB4',
    'I' => '#FFEAA7',
    'D' => '#DDA0DD',
    'E' => '#98D8C8'
);

foreach ($results['tabla_principal'] as $row) {
    $percentage = $row['total']['pct'];
    $color = $colors[$row['area']];
    
    echo html_writer::start_tag('div', array('class' => 'score-item mb-3'));
    echo html_writer::tag('label', $row['label'] . ': ' . $row['total']['score'] . '/14 (' . $percentage . '%)', array('class' => 'score-label'));
    echo html_writer::start_tag('div', array('class' => 'progress', 'style' => 'height: 25px;'));
    echo html_writer::tag('div', $percentage . '%', array(
        'class' => 'progress-bar text-dark',
        'role' => 'progressbar',
        'style' => "width: {$percentage}%; background-color: {$color};",
        'aria-valuenow' => $percentage,
        'aria-valuemin' => '0',
        'aria-valuemax' => '100'
    ));
    echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');
}

echo html_writer::end_tag('div');

// Navigation buttons
echo html_writer::start_tag('div', array('class' => 'mt-4 text-center'));
echo html_writer::link(
    new moodle_url('/course/view.php', array('id' => $courseid)),
    get_string('back_to_course', 'block_chaside'),
    array('class' => 'btn btn-secondary me-2')
);

// Export button for admins/teachers
if (has_capability('block/chaside:viewreports', $context)) {
    echo html_writer::link(
        new moodle_url('/blocks/chaside/admin_view.php', array('courseid' => $courseid, 'blockid' => $blockid)),
        'Ver Panel Administrativo',
        array('class' => 'btn btn-primary')
    );
}
echo html_writer::end_tag('div');

// Enhanced CSS for better presentation
echo html_writer::start_tag('style');
echo '
.chaside-executive-summary .card {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s;
}

.chaside-executive-summary .card:hover {
    transform: translateY(-2px);
}

.chaside-detailed-table table {
    font-size: 0.9rem;
}

.chaside-detailed-table .badge {
    font-size: 0.75rem;
}

.chaside-visual-chart .score-item {
    margin-bottom: 1rem;
}

.chaside-visual-chart .score-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: bold;
    color: #333;
}

.chaside-visual-chart .progress {
    height: 25px;
    border-radius: 5px;
}

.chaside-visual-chart .progress-bar {
    font-weight: bold;
    line-height: 25px;
    border-radius: 5px;
}

.table-primary {
    background-color: rgba(0, 123, 255, 0.1) !important;
}

.table-secondary {
    background-color: rgba(108, 117, 125, 0.1) !important;
}

.alert {
    border-radius: 8px;
}

@media print {
    .btn, .chaside-visual-chart {
        display: none !important;
    }
    
    .table {
        font-size: 0.8rem;
    }
}
';
echo html_writer::end_tag('style');

echo $OUTPUT->footer();
