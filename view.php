<?php
// This file is part of Moodle - http://moodle.org/

require_once('../../config.php');
require_once('block_chaside.php');

$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);
$page = optional_param('page', 1, PARAM_INT);
$scroll_to_question = optional_param('scroll', '', PARAM_RAW); // Can be question number, 'finish', or 'highlight_first'

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$context = context_course::instance($courseid);

require_login($course);
require_capability('block/chaside:take_test', $context);

// Redirect teachers/admins to management page
if (has_capability('block/chaside:manage_responses', $context)) {
    $manage_url = new moodle_url('/blocks/chaside/manage.php', array('courseid' => $courseid, 'blockid' => $blockid));
    redirect($manage_url, get_string('teachers_redirect_message', 'block_chaside'));
}

// If scroll parameter is a question number, calculate the correct page
$questions_per_page = 10;
if ($scroll_to_question && is_numeric($scroll_to_question)) {
    $question_num = (int)$scroll_to_question;
    $calculated_page = ceil($question_num / $questions_per_page);
    if ($calculated_page != $page) {
        // Redirect to correct page with scroll parameter
        redirect(new moodle_url('/blocks/chaside/view.php', array(
            'courseid' => $courseid,
            'blockid' => $blockid,
            'page' => $calculated_page,
            'scroll' => $scroll_to_question
        )));
    }
}

$PAGE->set_url('/blocks/chaside/view.php', array('courseid' => $courseid, 'blockid' => $blockid, 'page' => $page));
$PAGE->set_title(get_string('test_title', 'block_chaside'));
$PAGE->set_heading($course->fullname);
$PAGE->set_context($context);

// Inicializar el facade
$facade = new ChasideFacade();

// Verificar si ya existe una respuesta del usuario (en cualquier curso)
$existing_response = $DB->get_record('block_chaside_responses', array(
    'userid' => $USER->id
));

// Procesar envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_sesskey();
    
    // Si el test ya está completado, NO permitir modificaciones
    if ($existing_response && $existing_response->is_completed) {
        $results_url = new moodle_url('/blocks/chaside/view_results.php', array('courseid' => $courseid, 'blockid' => $blockid));
        redirect($results_url, get_string('test_already_completed', 'block_chaside'), null, \core\output\notification::NOTIFY_WARNING);
    }
    
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
        if (isset($existing_response->timemodified)) $data['timemodified'] = $existing_response->timemodified;
        if (isset($existing_response->timecreated)) $data['timecreated'] = $existing_response->timecreated;
    } else {
        $data['timecreated'] = time();
        $data['is_completed'] = 0;
    }
    
    // Actualizar solo las respuestas de la página actual
    $questions_per_page = 10;
    $start_question = ($page - 1) * $questions_per_page + 1;
    $end_question = min($page * $questions_per_page, 98);
    
    // Recopilar TODAS las respuestas enviadas en el formulario (no solo las de la página actual)
    // Esto permite guardar progreso parcial
    for ($i = 1; $i <= 98; $i++) {
        $response = optional_param("q{$i}", null, PARAM_INT);
        if ($response !== null) {
            $data["q{$i}"] = $response;
        }
    }
    
    // Validar que todas las preguntas de la página actual estén respondidas (solo para navegación)
    $current_page_complete = true;
    $missing_questions_current_page = array();
    
    for ($i = $start_question; $i <= $end_question; $i++) {
        $response = optional_param("q{$i}", null, PARAM_INT);
        if ($response !== null) {
            $data["q{$i}"] = $response;
        } else {
            // Verificar si ya existe una respuesta previa para esta pregunta
            if (!$existing_response || !isset($existing_response->{"q{$i}"}) || $existing_response->{"q{$i}"} === null) {
                $current_page_complete = false;
                $missing_questions_current_page[] = $i;
            }
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
    
    // Determinar total de páginas
    $total_pages = ceil(98 / $questions_per_page);
    
    // Procesar según la acción del botón
    switch ($action) {
        case 'autosave':
            // Silent auto-save - no validation, no redirect
            if ($existing_response) {
                $DB->update_record('block_chaside_responses', $data);
            } else {
                try {
                    $DB->insert_record('block_chaside_responses', $data);
                } catch (dml_exception $e) {
                    // Race condition: another request inserted the record
                    $current_record = $DB->get_record('block_chaside_responses', array('userid' => $USER->id));
                    if ($current_record) {
                        $data['id'] = $current_record->id;
                        $DB->update_record('block_chaside_responses', $data);
                    } else {
                        throw $e;
                    }
                }
            }
            // Return JSON response for AJAX
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
            
        case 'previous':
            // Ir a página anterior - siempre permite (guarda automáticamente)
            if ($existing_response) {
                $DB->update_record('block_chaside_responses', $data);
            } else {
                try {
                    $DB->insert_record('block_chaside_responses', $data);
                } catch (dml_exception $e) {
                    // Race condition: another request inserted the record
                    $current_record = $DB->get_record('block_chaside_responses', array('userid' => $USER->id));
                    if ($current_record) {
                        $data['id'] = $current_record->id;
                        $DB->update_record('block_chaside_responses', $data);
                    } else {
                        throw $e;
                    }
                }
            }
            
            if ($page > 1) {
                $redirect_url = new moodle_url('/blocks/chaside/view.php', array('courseid' => $courseid, 'blockid' => $blockid, 'page' => $page - 1));
                redirect($redirect_url, get_string('progress_saved', 'block_chaside'), null, \core\output\notification::NOTIFY_SUCCESS);
            }
            break;
            
        case 'next':
            // Ir a página siguiente - solo si la página actual está completa
            if ($page < $total_pages) {
                if (!$current_page_complete) {
                    $message = get_string('complete_current_page', 'block_chaside') . ' (' . count($missing_questions_current_page) . ' ' . get_string('questions_unanswered', 'block_chaside') . ')';
                    redirect($PAGE->url, $message, null, \core\output\notification::NOTIFY_ERROR);
                } else {
                    // Guardar progreso antes de navegar
                    if ($existing_response) {
                        $DB->update_record('block_chaside_responses', $data);
                    } else {
                        try {
                            $DB->insert_record('block_chaside_responses', $data);
                        } catch (dml_exception $e) {
                            // Race condition: another request inserted the record
                            $current_record = $DB->get_record('block_chaside_responses', array('userid' => $USER->id));
                            if ($current_record) {
                                $data['id'] = $current_record->id;
                                $DB->update_record('block_chaside_responses', $data);
                            } else {
                                throw $e;
                            }
                        }
                    }
                    
                    $redirect_url = new moodle_url('/blocks/chaside/view.php', array('courseid' => $courseid, 'blockid' => $blockid, 'page' => $page + 1, 'scroll' => 'highlight_first'));
                    redirect($redirect_url, get_string('progress_saved', 'block_chaside'), null, \core\output\notification::NOTIFY_SUCCESS);
                }
            }
            break;
            
        case 'finish':
            // SECURITY: Validate ALL 98 questions are answered before finishing
            $all_questions_answered = true;
            $missing_questions = array();
            
            for ($i = 1; $i <= 98; $i++) {
                if (!isset($data["q{$i}"]) || $data["q{$i}"] === null) {
                    $all_questions_answered = false;
                    $missing_questions[] = $i;
                }
            }
            
            if ($all_questions_answered) {
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
                $data['timemodified'] = time();
                
                // Actualizar con las puntuaciones finales
                $DB->update_record('block_chaside_responses', $data);
                
                $course_url = new moodle_url('/course/view.php', array('id' => $courseid));
                redirect($course_url, get_string('test_completed_success', 'block_chaside'), null, \core\output\notification::NOTIFY_SUCCESS);
            } else {
                // Find first unanswered question and redirect to that page
                $first_unanswered = $missing_questions[0];
                $redirect_page = ceil($first_unanswered / $questions_per_page);
                
                $message = get_string('all_questions_must_be_answered', 'block_chaside') . ' (' . count($missing_questions) . ' ' . get_string('questions_remaining', 'block_chaside') . ')';
                $redirect_url = new moodle_url('/blocks/chaside/view.php', 
                               array('courseid' => $courseid, 'blockid' => $blockid, 'page' => $redirect_page));
                redirect($redirect_url, $message, null, \core\output\notification::NOTIFY_ERROR);
            }
            break;
    }
}

// Si el test ya está completado, redirigir a resultados (NO permitir retomar)
if ($existing_response && $existing_response->is_completed) {
    $results_url = new moodle_url('/blocks/chaside/view_results.php', array('courseid' => $courseid, 'blockid' => $blockid));
    redirect($results_url, get_string('test_already_completed', 'block_chaside'), null, \core\output\notification::NOTIFY_INFO);
}

echo $OUTPUT->header();

// Configuración de paginación
$questions_per_page = 10;
$total_pages = ceil(98 / $questions_per_page);
$start_question = ($page - 1) * $questions_per_page + 1;
$end_question = min($page * $questions_per_page, 98);

// SECURITY: Validate that user cannot skip pages without completing previous ones
if ($existing_response && $page > 1) {
    // Check all questions from page 1 to current page - 1
    $max_allowed_page = 1;
    
    for ($p = 1; $p < $page; $p++) {
        $page_start = ($p - 1) * $questions_per_page + 1;
        $page_end = min($p * $questions_per_page, 98);
        $page_complete = true;
        
        for ($i = $page_start; $i <= $page_end; $i++) {
            $field = "q{$i}";
            if (!isset($existing_response->$field) || $existing_response->$field === null) {
                $page_complete = false;
                break;
            }
        }
        
        if ($page_complete) {
            $max_allowed_page = $p + 1;
        } else {
            break;
        }
    }
    
    // If trying to access a page beyond allowed, redirect to max allowed
    if ($page > $max_allowed_page) {
        redirect(new moodle_url('/blocks/chaside/view.php', 
                 array('courseid' => $courseid, 'blockid' => $blockid, 'page' => $max_allowed_page)),
                 get_string('complete_previous_pages', 'block_chaside'),
                 null, \core\output\notification::NOTIFY_WARNING);
    }
}

echo html_writer::tag('h2', get_string('test_title', 'block_chaside'));
echo html_writer::tag('p', get_string('test_description', 'block_chaside'));

// Info box sobre preguntas obligatorias (similar a learning_style)
echo '<div style="background-color: #e3f2fd; border-left: 4px solid #2196F3; padding: 12px 16px; margin-bottom: 20px; border-radius: 4px;">';
echo '<strong>' . get_string('note', 'block_chaside') . ':</strong> ';
echo get_string('all_questions_required', 'block_chaside');
echo '</div>';


// Formulario
echo html_writer::start_tag('form', array('method' => 'post', 'action' => '', 'id' => 'chasideTestForm'));

// Agregar token de seguridad CSRF
echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));

for ($i = $start_question; $i <= $end_question; $i++) {
    $question_text = get_string("q{$i}", 'block_chaside');
    $current_value = '';
    
    if ($existing_response && isset($existing_response->{"q{$i}"})) {
        $current_value = $existing_response->{"q{$i}"};
    }
    
    // Contenedor principal de la pregunta con borde y espaciado
    echo html_writer::start_tag('div', array('class' => 'card mb-3 shadow-sm', 'id' => "question-{$i}", 'data-question' => $i));
    echo html_writer::start_tag('div', array('class' => 'card-body'));
    
    // Texto de la pregunta
    echo html_writer::start_tag('div', array('class' => 'row align-items-center'));
    echo html_writer::start_tag('div', array('class' => 'col-md-8'));
    echo html_writer::tag('h6', 
        $question_text, 
        array('class' => 'mb-3 question-text')
    );
    echo html_writer::end_tag('div');
    
    // Opciones de respuesta organizadas
    echo html_writer::start_tag('div', array('class' => 'col-md-4'));
    echo html_writer::start_tag('div', array('class' => 'btn-group w-100', 'role' => 'group', 'aria-label' => 'Respuesta'));
    
    // Opción SÍ
    $yes_classes = 'btn btn-outline-primary flex-fill radio-btn chaside-btn-yes';
    if ($current_value === '1') {
        $yes_classes = 'btn btn-primary flex-fill radio-btn chaside-btn-yes active';
    }
    echo html_writer::start_tag('label', array('class' => $yes_classes, 'for' => "q{$i}_yes"));
    echo html_writer::empty_tag('input', array(
        'type' => 'radio',
        'name' => "q{$i}",
        'value' => '1',
        'id' => "q{$i}_yes",
        // Keep input visually hidden but still usable for form submission and native toggling.
        'style' => 'position: absolute; opacity: 0;',
        'checked' => ($current_value === '1') ? 'checked' : null
    ));
    echo html_writer::tag('i', '', array('class' => 'fa fa-check me-1'));
    echo get_string('yes', 'block_chaside');
    echo html_writer::end_tag('label');
    
    // Opción NO
    $no_classes = 'btn btn-outline-secondary flex-fill radio-btn chaside-btn-no';
    if ($current_value === '0') {
        $no_classes = 'btn btn-secondary flex-fill radio-btn chaside-btn-no active';
    }
    echo html_writer::start_tag('label', array('class' => $no_classes, 'for' => "q{$i}_no"));
    echo html_writer::empty_tag('input', array(
        'type' => 'radio',
        'name' => "q{$i}",
        'value' => '0',
        'id' => "q{$i}_no",
        'style' => 'position: absolute; opacity: 0;',
        'checked' => ($current_value === '0') ? 'checked' : null
    ));
    echo html_writer::tag('i', '', array('class' => 'fa fa-times me-1'));
    echo get_string('no', 'block_chaside');
    echo html_writer::end_tag('label');
    
    echo html_writer::end_tag('div'); // btn-group
    echo html_writer::end_tag('div'); // col-md-4
    echo html_writer::end_tag('div'); // row
    echo html_writer::end_tag('div'); // card-body
    echo html_writer::end_tag('div'); // card
}

// Navegación
echo html_writer::start_tag('div', array('class' => 'mt-4 d-flex justify-content-between align-items-center'));

// Columna izquierda: Botón anterior
echo html_writer::start_tag('div');
if ($page > 1) {
    echo html_writer::tag('button', 
        '<i class="fa fa-arrow-left me-2"></i>' . get_string('btn_previous', 'block_chaside'),
        array(
            'type' => 'submit',
            'name' => 'action',
            'value' => 'previous',
            'class' => 'btn btn-secondary'
        )
    );
}
echo html_writer::end_tag('div');

// Columna derecha: Botones de acción
echo html_writer::start_tag('div', array('class' => 'd-flex gap-2'));

if ($page < $total_pages) {
    echo html_writer::tag('button',
        get_string('btn_next', 'block_chaside') . '<i class="fa fa-arrow-right ms-2"></i>',
        array(
            'type' => 'submit',
            'name' => 'action',
            'value' => 'next',
            'class' => 'btn btn-primary'
        )
    );
} else {
    echo html_writer::tag('button',
        '<i class="fa fa-check-circle me-2"></i>' . get_string('btn_finish', 'block_chaside'),
        array(
            'type' => 'submit',
            'name' => 'action',
            'value' => 'finish',
            'class' => 'btn btn-success'
        )
    );
}
echo html_writer::end_tag('div');

echo html_writer::end_tag('div');
echo html_writer::end_tag('form');

// Agregar estilos CSS personalizados
echo html_writer::start_tag('style');
echo "
/* Colores del bloque CHASIDE (morado/purple) */
:root {
    --chaside-primary: #673ab7;
    --chaside-primary-dark: #5e35b1;
    --chaside-primary-darker: #512da8;
    --chaside-secondary: #b39ddb;
    --chaside-light: #ede7f6;
}

body#page-blocks-chaside-view .question-text {
    font-weight: 500 !important;
    color: #212529 !important;
    line-height: 1.4 !important;
}

body#page-blocks-chaside-view .card {
    border: 1px solid #e9ecef !important;
    transition: all 0.2s ease-in-out !important;
}

/* Estilo para preguntas sin responder después de intentar avanzar */
body#page-blocks-chaside-view .card.unanswered {
    border: 2px solid #d32f2f !important;
    background-color: #ffebee !important;
}

body#page-blocks-chaside-view .card.unanswered .question-text {
    color: #d32f2f !important;
}

body#page-blocks-chaside-view .card:hover {
    border-color: var(--chaside-primary) !important;
    box-shadow: 0 4px 8px rgba(103, 58, 183, 0.1) !important;
}

body#page-blocks-chaside-view .btn-group label {
    cursor: pointer !important;
    transition: all 0.2s ease-in-out !important;
    font-weight: 500 !important;
    padding: 8px 16px !important;
}

body#page-blocks-chaside-view .btn-group label:hover {
    transform: translateY(-1px) !important;
}

/* Botón Sí - Color primario del bloque (morado) */
body#page-blocks-chaside-view .chaside-btn-yes.btn-outline-primary {
    border-color: var(--chaside-primary) !important;
    color: var(--chaside-primary) !important;
    background-color: #ffffff !important;
}

body#page-blocks-chaside-view .chaside-btn-yes.btn-outline-primary:hover {
    background: var(--chaside-light) !important;
    color: var(--chaside-primary) !important;
}

body#page-blocks-chaside-view .chaside-btn-yes.btn-primary,
body#page-blocks-chaside-view .chaside-btn-yes.btn-primary.active {
    background: linear-gradient(135deg, var(--chaside-primary) 0%, var(--chaside-primary-dark) 100%) !important;
    border-color: var(--chaside-primary) !important;
    color: #ffffff !important;
    box-shadow: 0 2px 6px rgba(103, 58, 183, 0.3) !important;
}

/* Botón No - Lila suave (color secundario del bloque) */
body#page-blocks-chaside-view .chaside-btn-no.btn-outline-secondary {
    border-color: var(--chaside-secondary) !important;
    color: var(--chaside-secondary) !important;
    background-color: #ffffff !important;
}

body#page-blocks-chaside-view .chaside-btn-no.btn-outline-secondary:hover {
    background: var(--chaside-light) !important;
    color: var(--chaside-secondary) !important;
}

body#page-blocks-chaside-view .chaside-btn-no.btn-secondary,
body#page-blocks-chaside-view .chaside-btn-no.btn-secondary.active {
    background: linear-gradient(135deg, var(--chaside-secondary) 0%, #9575cd 100%) !important;
    border-color: var(--chaside-secondary) !important;
    color: #ffffff !important;
    box-shadow: 0 2px 6px rgba(179, 157, 219, 0.3) !important;
}

body#page-blocks-chaside-view .radio-btn {
    position: relative !important;
}

body#page-blocks-chaside-view .radio-btn input[type='radio'] {
    position: absolute !important;
    opacity: 0 !important;
    width: 0 !important;
    height: 0 !important;
}

body#page-blocks-chaside-view .badge {
    font-size: 0.85em !important;
    min-width: 24px !important;
    text-align: center !important;
    color: #ffffff !important;
    background-color: var(--chaside-primary) !important;
}

/* Botones de navegación con colores del bloque - MÁS ESPECÍFICOS */
body#page-blocks-chaside-view form button.btn.btn-secondary,
body#page-blocks-chaside-view form button.btn-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%) !important;
    border-color: #6c757d !important;
    color: #ffffff !important;
}

body#page-blocks-chaside-view form button.btn.btn-secondary:hover,
body#page-blocks-chaside-view form button.btn-secondary:hover {
    background: linear-gradient(135deg, #5a6268 0%, #545b62 100%) !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3) !important;
    color: #ffffff !important;
    border-color: #6c757d !important;
}

/* Card highlight when all questions answered */
body#page-blocks-chaside-view .card.all-answered-highlight {
    border: 3px solid #28a745 !important;
    background-color: #d4edda !important;
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3) !important;
}

body#page-blocks-chaside-view form button.btn.btn-primary,
body#page-blocks-chaside-view form button.btn-primary {
    background: linear-gradient(135deg, var(--chaside-primary) 0%, var(--chaside-primary-dark) 100%) !important;
    border-color: var(--chaside-primary) !important;
    color: #ffffff !important;
}

body#page-blocks-chaside-view form button.btn.btn-primary:hover,
body#page-blocks-chaside-view form button.btn-primary:hover {
    background: linear-gradient(135deg, var(--chaside-primary-dark) 0%, var(--chaside-primary-darker) 100%) !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 8px rgba(103, 58, 183, 0.3) !important;
    color: #ffffff !important;
    border-color: var(--chaside-primary) !important;
}

body#page-blocks-chaside-view form button.btn.btn-success,
body#page-blocks-chaside-view form button.btn-success {
    background: linear-gradient(135deg, #28a745 0%, #218838 100%) !important;
    border-color: #28a745 !important;
    color: #ffffff !important;
}

body#page-blocks-chaside-view form button.btn.btn-success:hover,
body#page-blocks-chaside-view form button.btn-success:hover {
    background: linear-gradient(135deg, #218838 0%, #1e7e34 100%) !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3) !important;
    color: #ffffff !important;
    border-color: #28a745 !important;
}

@media (max-width: 768px) {
    body#page-blocks-chaside-view .btn-group {
        flex-direction: column !important;
        width: 100% !important;
    }
    
    body#page-blocks-chaside-view .btn-group label {
        margin-bottom: 5px !important;
        border-radius: 4px !important;
    }
    
    body#page-blocks-chaside-view .col-md-8, 
    body#page-blocks-chaside-view .col-md-4 {
        margin-bottom: 15px !important;
    }
}

body#page-blocks-chaside-view .question-text {
    margin-bottom: 0 !important;
}

body#page-blocks-chaside-view .fa {
    font-size: 0.9em !important;
}
";
echo html_writer::end_tag('style');

// JavaScript para validación en tiempo real y manejo de botones
echo html_writer::start_tag('script');
echo "
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('chasideTestForm');
    if (!form) {
        return;
    }
    const radioInputs = form.querySelectorAll('input[type=\"radio\"]');
    let formAttempted = false;
    let autoSaveTimer = null;
    
    // Auto-save functionality (silent)
    function autoSaveProgress() {
        // Create FormData from current form
        const formData = new FormData(form);
        formData.set('action', 'autosave');
        
        // Use fetch to save in background
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        }).catch(error => {
            // Silent fail - don't interrupt user experience
            console.log('Auto-save error:', error);
        });
    }
    
    // Función para actualizar el estado visual de los botones
    function updateButtonStates(questionName) {
        const yesLabel = form.querySelector('label[for=\"' + questionName + '_yes\"]');
        const noLabel = form.querySelector('label[for=\"' + questionName + '_no\"]');
        const yesInput = form.querySelector('#' + questionName + '_yes');
        const noInput = form.querySelector('#' + questionName + '_no');
        
        if (yesInput && yesInput.checked) {
            yesLabel.className = 'btn btn-primary flex-fill radio-btn chaside-btn-yes active';
            noLabel.className = 'btn btn-outline-secondary flex-fill radio-btn chaside-btn-no';
        } else if (noInput && noInput.checked) {
            noLabel.className = 'btn btn-secondary flex-fill radio-btn chaside-btn-no active';
            yesLabel.className = 'btn btn-outline-primary flex-fill radio-btn chaside-btn-yes';
        } else {
            yesLabel.className = 'btn btn-outline-primary flex-fill radio-btn chaside-btn-yes';
            noLabel.className = 'btn btn-outline-secondary flex-fill radio-btn chaside-btn-no';
        }
        
        // Remover clase de no respondida si se responde
        if (formAttempted && (yesInput.checked || noInput.checked)) {
            const card = yesInput.closest('.card');
            if (card) {
                card.classList.remove('unanswered');
            }
        }
        
        // Trigger auto-save after answer change
        if (autoSaveTimer) {
            clearTimeout(autoSaveTimer);
        }
        autoSaveTimer = setTimeout(autoSaveProgress, 2000); // Save 2 seconds after last change
    }
    
    // Manejo robusto de clicks (captura) para evitar que el tema bloquee la interacción.
    // Similar al enfoque de personality_test: delegación + actualización del valor.
    document.addEventListener('click', function(e) {
        const label = e.target.closest('label.radio-btn');
        if (!label || !form.contains(label)) {
            return;
        }

        // Obtener el input asociado (por contenido o por atributo for).
        let input = label.querySelector('input[type=\"radio\"]');
        if (!input) {
            const forId = label.getAttribute('for');
            if (forId) {
                input = document.getElementById(forId);
            }
        }

        if (!input) {
            return;
        }

        // Marcar y refrescar UI.
        input.checked = true;
        updateButtonStates(input.name);
        validateCurrentPage();

        // Auto-guardar después de 2s.
        if (autoSaveTimer) {
            clearTimeout(autoSaveTimer);
        }
        autoSaveTimer = setTimeout(autoSaveProgress, 2000);
    }, true);
    
    const saveBtn = form.querySelector('button[value=\"save\"]');
    const previousBtn = form.querySelector('button[value=\"previous\"]');
    const nextBtn = form.querySelector('button[value=\"next\"]');
    const finishBtn = form.querySelector('button[value=\"finish\"]');
    
    function validateCurrentPage() {
        const questions = {};
        
        // Obtener todas las preguntas de la página actual
        radioInputs.forEach(function(input) {
            const questionName = input.name;
            if (!questions[questionName]) {
                questions[questionName] = false;
            }
            if (input.checked) {
                questions[questionName] = true;
            }
        });
        
        // Verificar si todas las preguntas están respondidas
        const allAnswered = Object.values(questions).every(function(answered) {
            return answered === true;
        });
        
        return allAnswered;
    }
    
    // Agregar event listeners a todos los radio buttons directamente
    radioInputs.forEach(function(input) {
        input.addEventListener('change', function(e) {
            updateButtonStates(this.name);
            validateCurrentPage();
        });
    });

    // Inicializar estados visuales al cargar (por si el tema modifica clases).
    const seenQuestions = new Set();
    radioInputs.forEach(function(input) {
        if (!seenQuestions.has(input.name)) {
            seenQuestions.add(input.name);
            updateButtonStates(input.name);
        }
    });
    
    // Prevenir envío del formulario para los botones que requieren validación
    form.addEventListener('submit', function(e) {
        // Obtener el botón que se presionó
        const submitter = e.submitter;
        const action = submitter ? submitter.value : 'save';
        
        // Validar para botones 'next' y 'finish'
        if (action !== 'next' && action !== 'finish') {
            // Permitir envío sin validación para save, previous
            return true;
        }
        
        formAttempted = true;
        
        if (!validateCurrentPage()) {
            e.preventDefault();
            
            // Marcar visualmente las preguntas sin responder
            const questions = {};
            radioInputs.forEach(function(input) {
                const questionName = input.name;
                if (!questions[questionName]) {
                    questions[questionName] = false;
                }
                if (input.checked) {
                    questions[questionName] = true;
                }
            });
            
            // Primero agregar clase 'unanswered' a TODAS las tarjetas sin respuesta
            Object.keys(questions).forEach(function(questionName) {
                if (!questions[questionName]) {
                    const input = form.querySelector('input[name=\"' + questionName + '\"]');
                    if (input) {
                        const card = input.closest('.card');
                        if (card) {
                            card.classList.add('unanswered');
                        }
                    }
                }
            });
            
            // Luego hacer scroll a la primera pregunta sin responder
            const firstUnanswered = document.querySelector('.card.unanswered');
            if (firstUnanswered) {
                firstUnanswered.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            
            return false;
        }
    });
});

// Auto-scroll to first unanswered question with green highlight
const scrollToQuestion = " . json_encode($scroll_to_question) . ";
if (scrollToQuestion && scrollToQuestion !== '' && scrollToQuestion !== '0') {
    if (scrollToQuestion === 'finish') {
        // Scroll to finish button when all questions answered
        setTimeout(function() {
            const finishBtn = document.querySelector('button[value=\"finish\"]');
            if (finishBtn) {
                finishBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Add green pulsing highlight to the button
                finishBtn.style.boxShadow = '0 0 20px rgba(40, 167, 69, 0.8)';
                finishBtn.style.transition = 'all 0.3s ease';
                
                // Remove highlight after 5 seconds
                setTimeout(function() {
                    finishBtn.style.boxShadow = '';
                }, 5000);
            }
        }, 300);
    } else if (scrollToQuestion === 'highlight_first') {
        // Highlight and scroll to first question on page (when navigating with next/previous buttons)
        setTimeout(function() {
            const firstCard = document.querySelector('.card');
            if (firstCard) {
                // Store original styles
                const originalBorder = firstCard.style.border;
                const originalBackground = firstCard.style.backgroundColor;
                const originalBoxShadow = firstCard.style.boxShadow;
                
                // Apply green highlight
                firstCard.style.setProperty('border', '2px solid #28a745', 'important');
                firstCard.style.setProperty('background-color', '#d4edda', 'important');
                firstCard.style.setProperty('box-shadow', '0 4px 8px rgba(40, 167, 69, 0.3)', 'important');
                firstCard.style.transition = 'all 0.3s ease';
                
                // Scroll to it
                firstCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Remove highlight after 5 seconds
                setTimeout(function() {
                    firstCard.style.border = originalBorder;
                    firstCard.style.backgroundColor = originalBackground;
                    firstCard.style.boxShadow = originalBoxShadow;
                }, 5000);
            }
        }, 300);
    } else {
        // Scroll to specific question with green highlight
        setTimeout(function() {
            const questionNum = parseInt(scrollToQuestion);
            const questionCard = document.getElementById('question-' + questionNum);
            
            if (questionCard) {
                // Store original styles
                const originalBorder = questionCard.style.border;
                const originalBackground = questionCard.style.backgroundColor;
                const originalBoxShadow = questionCard.style.boxShadow;
                
                // Apply green highlight
                questionCard.style.setProperty('border', '2px solid #28a745', 'important');
                questionCard.style.setProperty('background-color', '#d4edda', 'important');
                questionCard.style.setProperty('box-shadow', '0 4px 8px rgba(40, 167, 69, 0.3)', 'important');
                questionCard.style.transition = 'all 0.3s ease';
                
                // Scroll to it
                questionCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Remove highlight after 5 seconds
                setTimeout(function() {
                    questionCard.style.border = originalBorder;
                    questionCard.style.backgroundColor = originalBackground;
                    questionCard.style.boxShadow = originalBoxShadow;
                }, 5000);
            }
        }, 300);
    }
}

// Track unsaved changes
window.formChanged = false;
window.originalValues = {};

// Store original values when page loads
const allRadios = document.querySelectorAll('input[type=\"radio\"]');
allRadios.forEach(function(radio) {
    if (radio.checked) {
        window.originalValues[radio.name] = radio.value;
    }
});

// Use event delegation on document to catch all radio changes
document.addEventListener('change', function(e) {
    if (e.target.type === 'radio' && e.target.name.startsWith('q')) {
        const origValue = window.originalValues[e.target.name];
        if (origValue === undefined || origValue !== e.target.value) {
            window.formChanged = true;
        } else {
            // Check if ALL values match original (in case they changed back)
            let hasChanges = false;
            document.querySelectorAll('input[type=\"radio\"]:checked').forEach(function(r) {
                if (window.originalValues[r.name] !== r.value) {
                    hasChanges = true;
                }
            });
            window.formChanged = hasChanges;
        }
    }
});

// Auto-save handles persistence, no need for beforeunload warning
";
echo html_writer::end_tag('script');

echo $OUTPUT->footer();
