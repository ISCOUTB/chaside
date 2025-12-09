<?php
// This file is part of Moodle - http://moodle.org/

require_once('../../config.php');
require_once('block_chaside.php');

$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);
$page = optional_param('page', 1, PARAM_INT);
$scroll_to_question = optional_param('scroll', 0, PARAM_INT);

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

// Verificar si ya existe una respuesta del usuario (en cualquier curso)
$existing_response = $DB->get_record('block_chaside_responses', array(
    'userid' => $USER->id
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
            // Ir a página anterior - siempre permite (guarda automáticamente)
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
                    $redirect_url = new moodle_url('/blocks/chaside/view.php', array('courseid' => $courseid, 'blockid' => $blockid, 'page' => $page + 1));
                    redirect($redirect_url, get_string('progress_saved', 'block_chaside'), null, \core\output\notification::NOTIFY_SUCCESS);
                }
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
                $data['timemodified'] = time();
                
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
                $message = get_string('complete_all_questions', 'block_chaside') . ' (' . count($missing_questions) . ' ' . get_string('questions_remaining', 'block_chaside') . ')';
                redirect($PAGE->url, $message, null, \core\output\notification::NOTIFY_ERROR);
            }
            break;
            
        case 'save':
        default:
            // Guardar progreso sin validar - permite guardar parcialmente
            // Actualizar o insertar el registro
            if ($existing_response) {
                $DB->update_record('block_chaside_responses', $data);
            } else {
                $DB->insert_record('block_chaside_responses', $data);
            }
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

// Info box sobre preguntas obligatorias (similar a learning_style)
echo '<div style="background-color: #e3f2fd; border-left: 4px solid #2196F3; padding: 12px 16px; margin-bottom: 20px; border-radius: 4px;">';
echo '<strong>' . get_string('note', 'block_chaside') . ':</strong> ';
echo get_string('all_questions_required', 'block_chaside');
echo '</div>';

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
    
    // Contenedor principal de la pregunta con borde y espaciado
    echo html_writer::start_tag('div', array('class' => 'card mb-3 shadow-sm', 'id' => "question-{$i}", 'data-question' => $i));
    echo html_writer::start_tag('div', array('class' => 'card-body'));
    
    // Número y texto de la pregunta
    echo html_writer::start_tag('div', array('class' => 'row align-items-center'));
    echo html_writer::start_tag('div', array('class' => 'col-md-8'));
    echo html_writer::tag('h6', 
        html_writer::tag('span', $i, array('class' => 'badge bg-primary me-2')) . $question_text, 
        array('class' => 'mb-3 question-text')
    );
    echo html_writer::end_tag('div');
    
    // Opciones de respuesta organizadas
    echo html_writer::start_tag('div', array('class' => 'col-md-4'));
    echo html_writer::start_tag('div', array('class' => 'btn-group w-100', 'role' => 'group', 'aria-label' => 'Respuesta'));
    
    // Opción SÍ
    $yes_classes = 'btn btn-outline-success flex-fill radio-btn';
    if ($current_value === '1') {
        $yes_classes = 'btn btn-success flex-fill radio-btn active';
    }
    echo html_writer::start_tag('label', array('class' => $yes_classes, 'for' => "q{$i}_yes"));
    echo html_writer::empty_tag('input', array(
        'type' => 'radio',
        'name' => "q{$i}",
        'value' => '1',
        'id' => "q{$i}_yes",
        // Keep input visually hidden but still interactive so change events fire
        'style' => 'position: absolute; opacity: 0; pointer-events: none;',
        'checked' => ($current_value === '1') ? 'checked' : null
    ));
    echo html_writer::tag('i', '', array('class' => 'fa fa-check me-1'));
    echo get_string('yes', 'block_chaside');
    echo html_writer::end_tag('label');
    
    // Opción NO
    $no_classes = 'btn btn-outline-danger flex-fill radio-btn';
    if ($current_value === '0') {
        $no_classes = 'btn btn-danger flex-fill radio-btn active';
    }
    echo html_writer::start_tag('label', array('class' => $no_classes, 'for' => "q{$i}_no"));
    echo html_writer::empty_tag('input', array(
        'type' => 'radio',
        'name' => "q{$i}",
        'value' => '0',
        'id' => "q{$i}_no",
        'style' => 'position: absolute; opacity: 0; pointer-events: none;',
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
echo html_writer::tag('button',
    '<i class="fa fa-save me-2"></i>' . get_string('btn_save_progress', 'block_chaside'),
    array(
        'type' => 'submit',
        'name' => 'action',
        'value' => 'save',
        'class' => 'btn btn-info'
    )
);

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
.question-text {
    font-weight: 500;
    color: #212529;
    line-height: 1.4;
}

.card {
    border: 1px solid #e9ecef;
    transition: all 0.2s ease-in-out;
}

/* Estilo para preguntas sin responder después de intentar avanzar */
.card.unanswered {
    border: 2px solid #d32f2f !important;
    background-color: #ffebee !important;
}

.card.unanswered .question-text {
    color: #d32f2f !important;
}

.card:hover {
    border-color: #007bff;
    box-shadow: 0 4px 8px rgba(0,123,255,0.1) !important;
}

.btn-group label {
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    font-weight: 500;
    padding: 8px 16px;
}

.btn-group label:hover {
    transform: translateY(-1px);
}

.radio-btn {
    position: relative;
}

.radio-btn input[type='radio'] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.badge {
    font-size: 0.85em;
    min-width: 24px;
    text-align: center;
    color: #ffffff !important;
}

.progress {
    height: 8px;
    background-color: #f8f9fa;
    border-radius: 4px;
}

.progress-bar {
    background: linear-gradient(90deg, #007bff 0%, #28a745 100%);
    transition: width 0.3s ease;
}

@media (max-width: 768px) {
    .btn-group {
        flex-direction: column !important;
        width: 100% !important;
    }
    
    .btn-group label {
        margin-bottom: 5px;
        border-radius: 4px !important;
    }
    
    .col-md-8, .col-md-4 {
        margin-bottom: 15px;
    }
}

.question-text {
    margin-bottom: 0 !important;
}

.fa {
    font-size: 0.9em;
}
";
echo html_writer::end_tag('style');

// JavaScript para validación en tiempo real y manejo de botones
echo html_writer::start_tag('script');
echo "
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const radioInputs = form.querySelectorAll('input[type=\"radio\"]');
    let formAttempted = false;
    
    // Función para actualizar el estado visual de los botones
    function updateButtonStates(questionName) {
        const yesLabel = form.querySelector('label[for=\"' + questionName + '_yes\"]');
        const noLabel = form.querySelector('label[for=\"' + questionName + '_no\"]');
        const yesInput = form.querySelector('#' + questionName + '_yes');
        const noInput = form.querySelector('#' + questionName + '_no');
        
        if (yesInput && yesInput.checked) {
            yesLabel.className = yesLabel.className.replace('btn-outline-success', 'btn-success').replace(' active', '') + ' active';
            noLabel.className = noLabel.className.replace('btn-danger', 'btn-outline-danger').replace(' active', '');
        } else if (noInput && noInput.checked) {
            noLabel.className = noLabel.className.replace('btn-outline-danger', 'btn-danger').replace(' active', '') + ' active';
            yesLabel.className = yesLabel.className.replace('btn-success', 'btn-outline-success').replace(' active', '');
        } else {
            yesLabel.className = yesLabel.className.replace('btn-success', 'btn-outline-success').replace(' active', '');
            noLabel.className = noLabel.className.replace('btn-danger', 'btn-outline-danger').replace(' active', '');
        }
        
        // Remover clase de no respondida si se responde
        if (formAttempted && (yesInput.checked || noInput.checked)) {
            const card = yesInput.closest('.card');
            if (card) {
                card.classList.remove('unanswered');
            }
        }
    }
    
    // Agregar event listeners para manejar clicks en los botones
    const radioButtons = form.querySelectorAll('.radio-btn');
    radioButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const input = this.querySelector('input[type=\"radio\"]');
            if (input) {
                const wasChecked = input.checked;
                input.checked = true;
                updateButtonStates(input.name);
                validateCurrentPage();
                
                // Manually trigger change event if value changed (since programmatic change doesn't fire it)
                if (!wasChecked) {
                    const event = new Event('change', { bubbles: true });
                    input.dispatchEvent(event);
                }
            }
        });
    });
    const saveBtn = form.querySelector('input[value=\"save\"]');
    const previousBtn = form.querySelector('input[value=\"previous\"]');
    const nextBtn = form.querySelector('input[value=\"next\"]');
    const finishBtn = form.querySelector('input[value=\"finish\"]');
    
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
    
    // Agregar event listeners a todos los radio buttons
    radioInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            updateButtonStates(this.name);
            validateCurrentPage();
        });
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

// Auto-scroll to first unanswered question
const scrollToQuestion = " . json_encode($scroll_to_question) . ";
if (scrollToQuestion > 0) {
    const questionCard = document.getElementById('question-' + scrollToQuestion);
    if (questionCard) {
        setTimeout(function() {
            questionCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            questionCard.style.boxShadow = '0 0 15px rgba(13, 110, 253, 0.5)';
            setTimeout(function() {
                questionCard.style.boxShadow = '';
            }, 2000);
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

// Warn before leaving with unsaved changes
window.onbeforeunload = function(e) {
    if (window.formChanged) {
        const message = 'Tienes cambios sin guardar.';
        e.returnValue = message;
        return message;
    }
};

// Clear warning when form is submitted
const pageForm = document.querySelector('form');
if (pageForm) {
    pageForm.addEventListener('submit', function() {
        window.formChanged = false;
    });
}
";
echo html_writer::end_tag('script');

echo $OUTPUT->footer();
