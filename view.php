<?php
// This file is part of Moodle - http://moodle.org/

require_once('../../config.php');
require_once('block_chaside.php');

$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);
$page = optional_param('page', 1, PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$context = context_course::instance($courseid);

require_login($course);
require_capability('block/chaside:take_test', $context);

// Redirect teachers/admins to management page
if (has_capability('block/chaside:manage_responses', $context)) {
    $manage_url = new moodle_url('/blocks/chaside/manage.php', array('courseid' => $courseid, 'blockid' => $blockid));
    redirect($manage_url, get_string('teachers_redirect_message', 'block_chaside'));
}

$PAGE->set_url('/blocks/chaside/view.php', array('courseid' => $courseid, 'blockid' => $blockid, 'page' => $page));
$PAGE->set_title(get_string('test_title', 'block_chaside'));
$PAGE->set_heading($course->fullname);
$PAGE->set_context($context);

// Inicializar el facade
$facade = new ChasideFacade();

// Verificar si ya existe una respuesta del usuario
$existing_response = $DB->get_record('block_chaside_responses', array(
    'userid' => $USER->id,
    'courseid' => $courseid
));

// Procesar envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_sesskey();
    
    $action = optional_param('action', 'save', PARAM_ALPHA);
    
    // Preparar datos base
    $data = array(
        'userid' => $USER->id,
        'courseid' => $courseid,
        'timemodified' => time()
    );
    
    // Si existe una respuesta previa, mantener todos los datos existentes
    if ($existing_response) {
        $data['id'] = $existing_response->id;
        // Copiar todas las respuestas existentes
        for ($i = 1; $i <= 98; $i++) {
            if (isset($existing_response->{"q{$i}"})) {
                $data["q{$i}"] = $existing_response->{"q{$i}"};
            }
        }
        // Mantener puntuaciones existentes si las hay
        if (isset($existing_response->score_c)) $data['score_c'] = $existing_response->score_c;
        if (isset($existing_response->score_h)) $data['score_h'] = $existing_response->score_h;
        if (isset($existing_response->score_a)) $data['score_a'] = $existing_response->score_a;
        if (isset($existing_response->score_s)) $data['score_s'] = $existing_response->score_s;
        if (isset($existing_response->score_i)) $data['score_i'] = $existing_response->score_i;
        if (isset($existing_response->score_d)) $data['score_d'] = $existing_response->score_d;
        if (isset($existing_response->score_e)) $data['score_e'] = $existing_response->score_e;
        if (isset($existing_response->is_completed)) $data['is_completed'] = $existing_response->is_completed;
        if (isset($existing_response->timecompleted)) $data['timecompleted'] = $existing_response->timecompleted;
        if (isset($existing_response->timecreated)) $data['timecreated'] = $existing_response->timecreated;
    } else {
        $data['timecreated'] = time();
        $data['is_completed'] = 0;
    }
    
    // Actualizar solo las respuestas de la página actual
    $questions_per_page = 10;
    $start_question = ($page - 1) * $questions_per_page + 1;
    $end_question = min($page * $questions_per_page, 98);
    
    for ($i = $start_question; $i <= $end_question; $i++) {
        $response = optional_param("q{$i}", null, PARAM_INT);
        if ($response !== null) {
            $data["q{$i}"] = $response;
        }
    }
    
    // Verificar si TODO el test está completo (todas las 98 preguntas)
    $completed = true;
    for ($i = 1; $i <= 98; $i++) {
        if (!isset($data["q{$i}"]) || $data["q{$i}"] === null) {
            $completed = false;
            break;
        }
    }
    
    // Guardar datos independientemente de la acción
    if ($existing_response) {
        $DB->update_record('block_chaside_responses', $data);
    } else {
        $DB->insert_record('block_chaside_responses', $data);
    }
    
    // Determinar total de páginas
    $total_pages = ceil(98 / $questions_per_page);
    
    // Procesar según la acción del botón
    switch ($action) {
        case 'previous':
            if ($page > 1) {
                $redirect_url = new moodle_url('/blocks/chaside/view.php', array('courseid' => $courseid, 'blockid' => $blockid, 'page' => $page - 1));
                redirect($redirect_url, get_string('progress_saved', 'block_chaside'), null, \core\output\notification::NOTIFY_SUCCESS);
            }
            break;
            
        case 'next':
            if ($page < $total_pages) {
                $redirect_url = new moodle_url('/blocks/chaside/view.php', array('courseid' => $courseid, 'blockid' => $blockid, 'page' => $page + 1));
                redirect($redirect_url, get_string('progress_saved', 'block_chaside'), null, \core\output\notification::NOTIFY_SUCCESS);
            }
            break;
            
        case 'finish':
            if ($completed) {
                // Calcular puntuaciones solo cuando esté completamente terminado
                $scores = $facade->calculate_scores($data);
                $data['score_c'] = $scores['C'];
                $data['score_h'] = $scores['H'];
                $data['score_a'] = $scores['A'];
                $data['score_s'] = $scores['S'];
                $data['score_i'] = $scores['I'];
                $data['score_d'] = $scores['D'];
                $data['score_e'] = $scores['E'];
                $data['is_completed'] = 1;
                $data['timecompleted'] = time();
                
                // Actualizar con las puntuaciones finales
                $DB->update_record('block_chaside_responses', $data);
                
                $course_url = new moodle_url('/course/view.php', array('id' => $courseid));
                redirect($course_url, get_string('test_completed_success', 'block_chaside'), null, \core\output\notification::NOTIFY_SUCCESS);
            } else {
                $missing_questions = array();
                for ($i = 1; $i <= 98; $i++) {
                    if (!isset($data["q{$i}"]) || $data["q{$i}"] === null) {
                        $missing_questions[] = $i;
                    }
                }
                $message = get_string('complete_all_questions', 'block_chaside') . ' (' . count($missing_questions) . ' preguntas restantes)';
                redirect($PAGE->url, $message, null, \core\output\notification::NOTIFY_ERROR);
            }
            break;
            
        case 'save':
        default:
            // Solo guardar sin navegar
            redirect($PAGE->url, get_string('progress_saved', 'block_chaside'), null, \core\output\notification::NOTIFY_SUCCESS);
            break;
    }
}

echo $OUTPUT->header();

// Si el test ya está completado, mostrar enlace a resultados
if ($existing_response && $existing_response->is_completed) {
    echo html_writer::tag('div', get_string('test_completed', 'block_chaside'), array('class' => 'alert alert-success'));
    echo html_writer::link(
        new moodle_url('/blocks/chaside/view_results.php', array('courseid' => $courseid, 'blockid' => $blockid)),
        get_string('view_results', 'block_chaside'),
        array('class' => 'btn btn-primary')
    );
    echo $OUTPUT->footer();
    exit;
}

// Configuración de paginación
$questions_per_page = 10;
$total_pages = ceil(98 / $questions_per_page);
$start_question = ($page - 1) * $questions_per_page + 1;
$end_question = min($page * $questions_per_page, 98);

echo html_writer::tag('h2', get_string('test_title', 'block_chaside'));
echo html_writer::tag('p', get_string('test_description', 'block_chaside'));

// Barra de progreso
$progress_percentage = (($page - 1) * $questions_per_page + ($end_question - $start_question + 1)) / 98 * 100;
echo html_writer::start_tag('div', array('class' => 'progress mb-3'));
echo html_writer::tag('div', '', array(
    'class' => 'progress-bar',
    'role' => 'progressbar',
    'style' => "width: {$progress_percentage}%",
    'aria-valuenow' => $progress_percentage,
    'aria-valuemin' => '0',
    'aria-valuemax' => '100'
));
echo html_writer::end_tag('div');

echo html_writer::tag('p', get_string('question', 'block_chaside') . " {$start_question} " . get_string('of', 'block_chaside') . " 98");

// Formulario
echo html_writer::start_tag('form', array('method' => 'post', 'action' => ''));

// Agregar token de seguridad CSRF
echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));

for ($i = $start_question; $i <= $end_question; $i++) {
    $question_text = get_string("q{$i}", 'block_chaside');
    $current_value = '';
    
    if ($existing_response && isset($existing_response->{"q{$i}"})) {
        $current_value = $existing_response->{"q{$i}"};
    }
    
    echo html_writer::start_tag('div', array('class' => 'form-group mb-3'));
    echo html_writer::tag('label', "{$i}. {$question_text}", array('class' => 'form-label'));
    
    echo html_writer::start_tag('div', array('class' => 'form-check-inline'));
    echo html_writer::empty_tag('input', array(
        'type' => 'radio',
        'name' => "q{$i}",
        'value' => '1',
        'id' => "q{$i}_yes",
        'class' => 'form-check-input',
        'checked' => ($current_value === '1') ? 'checked' : null
    ));
    echo html_writer::tag('label', get_string('yes', 'block_chaside'), array('for' => "q{$i}_yes", 'class' => 'form-check-label ms-1'));
    echo html_writer::end_tag('div');
    
    echo html_writer::start_tag('div', array('class' => 'form-check-inline ms-3'));
    echo html_writer::empty_tag('input', array(
        'type' => 'radio',
        'name' => "q{$i}",
        'value' => '0',
        'id' => "q{$i}_no",
        'class' => 'form-check-input',
        'checked' => ($current_value === '0') ? 'checked' : null
    ));
    echo html_writer::tag('label', get_string('no', 'block_chaside'), array('for' => "q{$i}_no", 'class' => 'form-check-label ms-1'));
    echo html_writer::end_tag('div');
    
    echo html_writer::end_tag('div');
}

// Navegación
echo html_writer::start_tag('div', array('class' => 'mt-4 d-flex justify-content-between'));

// Columna izquierda: Botón anterior
echo html_writer::start_tag('div');
if ($page > 1) {
    echo html_writer::empty_tag('input', array(
        'type' => 'submit',
        'name' => 'action',
        'value' => 'previous',
        'class' => 'btn btn-secondary'
    ));
    echo html_writer::tag('span', ' ' . get_string('previous', 'block_chaside'), array('class' => 'ms-2'));
} else {
    echo html_writer::tag('span', '');
}
echo html_writer::end_tag('div');

// Columna derecha: Botones de acción
echo html_writer::start_tag('div');
echo html_writer::empty_tag('input', array(
    'type' => 'submit',
    'name' => 'action',
    'value' => 'save',
    'class' => 'btn btn-info me-2'
));
echo html_writer::tag('span', get_string('save_progress', 'block_chaside'), array('class' => 'me-2'));

if ($page < $total_pages) {
    echo html_writer::empty_tag('input', array(
        'type' => 'submit',
        'name' => 'action',
        'value' => 'next',
        'class' => 'btn btn-primary'
    ));
    echo html_writer::tag('span', ' ' . get_string('next', 'block_chaside'), array('class' => 'ms-2'));
} else {
    echo html_writer::empty_tag('input', array(
        'type' => 'submit',
        'name' => 'action',
        'value' => 'finish',
        'class' => 'btn btn-success'
    ));
    echo html_writer::tag('span', ' ' . get_string('finish', 'block_chaside'), array('class' => 'ms-2'));
}
echo html_writer::end_tag('div');

echo html_writer::end_tag('div');
echo html_writer::end_tag('form');

echo $OUTPUT->footer();
