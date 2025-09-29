<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/moodleblock.class.php');

class block_chaside extends block_base {
    
    public function init() {
        $this->title = get_string('pluginname', 'block_chaside');
    }
    
    public function get_content() {
        global $USER, $COURSE, $OUTPUT, $DB;
        
        if ($this->content !== null) {
            return $this->content;
        }
        
        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';
        
        if (!isloggedin() || empty($COURSE->id)) {
            return $this->content;
        }

        $context = context_course::instance($COURSE->id);
        
        // Check if user can manage responses (teacher/admin)
        if (has_capability('block/chaside:manage_responses', $context)) {
            // Teacher/Admin view: enhanced management interface
            ob_start();
            $this->show_management_summary();
            $this->content->text = ob_get_clean();
        } else if (has_capability('block/chaside:take_test', $context)) {
            // Student view: check if test is completed
            $response = $DB->get_record('block_chaside_responses', array(
                'userid' => $USER->id,
                'courseid' => $COURSE->id
            ));
            
            if ($response && $response->is_completed) {
                // Show results directly in the block
                ob_start();
                $this->show_student_results($response);
                $this->content->text = ob_get_clean();
            } else {
                // Show enhanced test invitation with progress if applicable
                ob_start();
                $this->show_test_invitation($response);
                $this->content->text = ob_get_clean();
            }
        }

        // Add link to admin dashboard for teachers/admins.
        if (has_capability('block/chaside:viewreports', $context)) {
            $adminurl = new moodle_url('/blocks/chaside/admin_view.php', ['courseid' => $this->page->course->id, 'blockid' => $this->instance->id]);
            $this->content->footer = html_writer::div(
                html_writer::link($adminurl, get_string('admin_dashboard', 'block_chaside'), [
                    'class' => 'btn btn-primary btn-sm btn-block',
                    'title' => get_string('admin_dashboard', 'block_chaside')
                ]),
                'text-center mt-2'
            );
        }
        
        return $this->content;
    }
    
    private function show_student_results($response) {
        global $COURSE, $USER;
        
        // Convert stdClass object to array for the facade
        $response_array = (array) $response;
        
        // Generate results using new official format
        $facade = new ChasideFacade();
        $meta = array(
            'nombre' => fullname($USER),
            'curso' => $COURSE->shortname,
            'fecha_aplicacion' => date('Y-m-d', $response->timemodified),
            'version_instrumento' => 'CHASIDE v1.0'
        );
        
        $results = $facade->generate_results_json($response_array, $meta);
        
        // Completion date
        $completion_date = '';
        if (isset($response->timemodified) && $response->timemodified > 0) {
            $completion_date = userdate($response->timemodified, get_string('strftimedatefullshort'));
        } else {
            $completion_date = get_string('date_not_available', 'block_chaside');
        }
        
        echo '<div class="chaside-results-block">';
        
        // Header with success icon
        echo '<div class="chaside-header text-center mb-3">';
        echo '<i class="fa fa-check-circle text-success" style="font-size: 1.5em;"></i>';
        echo '<h6 class="mt-2 mb-1">' . get_string('test_completed', 'block_chaside') . '</h6>';
        echo '<small class="text-muted">' . $completion_date . '</small>';
        echo '</div>';
        
        // Executive Summary
        echo '<div class="chaside-executive-summary mb-3">';
        echo '<h6 class="mb-2">' . get_string('executive_summary', 'block_chaside') . '</h6>';
        
        // Top areas
        if ($results['resumen_ejecutivo']['top1']) {
            $top1 = $results['resumen_ejecutivo']['top1'];
            echo '<div class="card border-primary mb-2" style="border-left: 4px solid #007bff !important;">';
            echo '<div class="card-body p-2">';
            echo '<div class="d-flex justify-content-between align-items-center">';
            echo '<div>';
            echo '<strong>1. ' . $top1['label'] . '</strong><br>';
            echo '<small class="text-muted">' . $top1['pct_total'] . '% (' . $top1['total'] . '/14)</small>';
            echo '</div>';
            echo '<div class="text-right">';
            echo '<small>I:' . $top1['i'] . ' A:' . $top1['a'] . '</small>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        
        if ($results['resumen_ejecutivo']['top2']) {
            $top2 = $results['resumen_ejecutivo']['top2'];
            echo '<div class="card border-secondary mb-2">';
            echo '<div class="card-body p-2">';
            echo '<div class="d-flex justify-content-between align-items-center">';
            echo '<div>';
            echo '<strong>2. ' . $top2['label'] . '</strong><br>';
            echo '<small class="text-muted">' . $top2['pct_total'] . '% (' . $top2['total'] . '/14)</small>';
            echo '</div>';
            echo '<div class="text-right">';
            echo '<small>I:' . $top2['i'] . ' A:' . $top2['a'] . '</small>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
        
        // Gap alerts (if any)
        if (!empty($results['resumen_ejecutivo']['alertas_brecha'])) {
            echo '<div class="chaside-gap-alerts mb-3">';
            echo '<h6 class="mb-2">' . get_string('gap_alerts', 'block_chaside') . '</h6>';
            foreach ($results['resumen_ejecutivo']['alertas_brecha'] as $alert) {
                $badge_class = 'badge-warning';
                if ($alert['tipo'] == get_string('gap_interest_higher', 'block_chaside')) {
                    $badge_class = 'badge-info';
                } elseif ($alert['tipo'] == get_string('gap_aptitude_higher', 'block_chaside')) {
                    $badge_class = 'badge-success';
                }
                echo '<span class="badge ' . $badge_class . ' mr-1">' . $alert['area'] . ': ' . $alert['tipo'] . '</span>';
            }
            echo '</div>';
        }
        
        // Quick recommendations
        echo '<div class="chaside-recommendations mb-3">';
        echo '<h6 class="mb-2">' . get_string('recommendations', 'block_chaside') . '</h6>';
        echo '<ul class="list-unstyled">';
        $rec_count = 0;
        foreach ($results['recomendaciones'] as $recommendation) {
            if ($rec_count >= 2) break; // Show only first 2 in block view
            echo '<li class="small mb-1"><i class="fa fa-arrow-right text-primary"></i> ' . $recommendation . '</li>';
            $rec_count++;
        }
        echo '</ul>';
        echo '</div>';
        
        // Action buttons
        echo '<div class="chaside-actions text-center">';
        $url = new moodle_url('/blocks/chaside/view_results.php', array(
            'courseid' => $COURSE->id,
            'blockid' => $this->instance->id
        ));
        echo '<a href="' . $url . '" class="btn btn-primary btn-sm">';
        echo '<i class="fa fa-chart-bar"></i> ' . get_string('view_detailed_results', 'block_chaside');
        echo '</a>';
        echo '</div>';
        
        echo '</div>';
    }
    
    private function show_test_invitation($response) {
        global $COURSE;
        
        echo '<div class="chaside-invitation-block">';
        
        // Header with icon
        echo '<div class="chaside-header text-center mb-3">';
        echo '<i class="fa fa-compass text-primary" style="font-size: 2em;"></i>';
        echo '<h6 class="mt-2 mb-1">' . get_string('vocational_orientation', 'block_chaside') . '</h6>';
        echo '<small class="text-muted">' . get_string('discover_your_interests', 'block_chaside') . '</small>';
        echo '</div>';
        
        if ($response) {
            // Show progress for started test
            $answered_count = 0;
            for ($i = 1; $i <= 98; $i++) {
                if (isset($response->{"q{$i}"}) && $response->{"q{$i}"} !== null) {
                    $answered_count++;
                }
            }
            $progress_percentage = ($answered_count / 98) * 100;
            
            echo '<div class="chaside-progress mb-3">';
            echo '<div class="d-flex justify-content-between align-items-center mb-2">';
            echo '<span class="small font-weight-bold">' . get_string('your_progress', 'block_chaside') . '</span>';
            echo '<span class="small text-muted">' . $answered_count . '/98</span>';
            echo '</div>';
            echo '<div class="progress mb-2" style="height: 8px;">';
            echo '<div class="progress-bar bg-success" style="width: ' . $progress_percentage . '%"></div>';
            echo '</div>';
            echo '<small class="text-muted">' . number_format($progress_percentage, 1) . '% ' . get_string('completed', 'block_chaside') . '</small>';
            echo '</div>';
            
            $button_text = get_string('continue_test', 'block_chaside');
            $button_icon = 'fa-play';
            $button_class = 'btn-success';
        } else {
            // Show test description for new test
            echo '<div class="chaside-description mb-3">';
            echo '<div class="card border-info">';
            echo '<div class="card-body p-3">';
            echo '<h6 class="card-title">';
            echo '<i class="fa fa-info-circle text-info"></i> ';
            echo get_string('what_is_chaside', 'block_chaside');
            echo '</h6>';
            echo '<p class="card-text small mb-2">' . get_string('chaside_description', 'block_chaside') . '</p>';
            echo '<ul class="list-unstyled small mb-0">';
            echo '<li><i class="fa fa-check text-success"></i> ' . get_string('feature_98_questions', 'block_chaside') . '</li>';
            echo '<li><i class="fa fa-check text-success"></i> ' . get_string('feature_7_areas', 'block_chaside') . '</li>';
            echo '<li><i class="fa fa-check text-success"></i> ' . get_string('feature_instant_results', 'block_chaside') . '</li>';
            echo '</ul>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            
            $button_text = get_string('start_test', 'block_chaside');
            $button_icon = 'fa-rocket';
            $button_class = 'btn-primary';
        }
        
        // Action button
        echo '<div class="chaside-actions text-center">';
        $url = new moodle_url('/blocks/chaside/view.php', array(
            'courseid' => $COURSE->id,
            'blockid' => $this->instance->id
        ));
        echo '<a href="' . $url . '" class="btn ' . $button_class . ' btn-block">';
        echo '<i class="fa ' . $button_icon . '"></i> ' . $button_text;
        echo '</a>';
        echo '</div>';
        
        echo '</div>';
        
        // Add custom CSS
        echo '<style>
        .chaside-invitation-block {
            padding: 15px;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        .chaside-header i {
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .chaside-progress {
            background: white;
            padding: 12px;
            border-radius: 5px;
            border: 1px solid #e9ecef;
        }
        .chaside-description .card {
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .chaside-actions .btn {
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            font-weight: 500;
        }
        .chaside-actions .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        </style>';
        
        echo '</div>';
        
        // Add custom CSS
        echo '<style>
        .chaside-results-block {
            padding: 15px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        .chaside-header i {
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .chaside-top-area .card {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }
        .chaside-top-area .card:hover {
            transform: translateY(-2px);
        }
        .chaside-other-areas {
            background: white;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #e9ecef;
        }
        .chaside-actions .btn {
            box-shadow: 0 2px 4px rgba(0,123,255,0.2);
        }
        </style>';
        
        echo '</div>';
    }
    
    public function applicable_formats() {
        return array('course' => true, 'my' => true);
    }
    
    public function has_config() {
        return false;
    }
    
    private function show_management_summary() {
        global $COURSE, $DB;
        
        // Get statistics
        $context = context_course::instance($COURSE->id);
        $enrolled_students = get_enrolled_users($context, 'block/chaside:take_test');
        $total_enrolled = count($enrolled_students);
        
        $responses = $DB->get_records('block_chaside_responses', array('courseid' => $COURSE->id));
        $completed_responses = array_filter($responses, function($r) { return $r->is_completed; });
        $total_completed = count($completed_responses);
        $total_in_progress = count($responses) - $total_completed;
        
        $completion_rate = $total_enrolled > 0 ? ($total_completed / $total_enrolled) * 100 : 0;
        
        echo '<div class="chaside-management-block">';
        
        // Header
        echo '<div class="chaside-header text-center mb-3">';
        echo '<i class="fa fa-chart-line text-success" style="font-size: 1.5em;"></i>';
        echo '<h6 class="mt-2 mb-1">' . get_string('management_title', 'block_chaside') . '</h6>';
        echo '<small class="text-muted">' . get_string('course_overview', 'block_chaside') . '</small>';
        echo '</div>';
        
        // Quick stats
        echo '<div class="chaside-stats mb-3">';
        echo '<div class="row text-center">';
        
        // Completion rate
        echo '<div class="col-4">';
        echo '<div class="stat-card">';
        echo '<div class="stat-number text-success">' . number_format($completion_rate, 1) . '%</div>';
        echo '<div class="stat-label">' . get_string('completion_rate', 'block_chaside') . '</div>';
        echo '</div>';
        echo '</div>';
        
        // Completed tests
        echo '<div class="col-4">';
        echo '<div class="stat-card">';
        echo '<div class="stat-number text-primary">' . $total_completed . '</div>';
        echo '<div class="stat-label">' . get_string('completed', 'block_chaside') . '</div>';
        echo '</div>';
        echo '</div>';
        
        // In progress
        echo '<div class="col-4">';
        echo '<div class="stat-card">';
        echo '<div class="stat-number text-warning">' . $total_in_progress . '</div>';
        echo '<div class="stat-label">' . get_string('in_progress', 'block_chaside') . '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
        
        // Progress bar
        echo '<div class="chaside-progress-overview mb-3">';
        echo '<div class="progress" style="height: 10px;">';
        echo '<div class="progress-bar bg-success" style="width: ' . ($completion_rate) . '%"></div>';
        echo '</div>';
        echo '<small class="text-muted">' . $total_completed . ' ' . get_string('of', 'block_chaside') . ' ' . $total_enrolled . ' ' . get_string('students_completed', 'block_chaside') . '</small>';
        echo '</div>';
        
        // Recent activity (if any)
        if ($total_completed > 0) {
            $recent_responses = $DB->get_records('block_chaside_responses', 
                array('courseid' => $COURSE->id, 'is_completed' => 1), 
                'timemodified DESC', '*', 0, 3);
                
            echo '<div class="chaside-recent mb-3">';
            echo '<h6 class="mb-2">' . get_string('recent_completions', 'block_chaside') . '</h6>';
            foreach ($recent_responses as $response) {
                $user = $DB->get_record('user', array('id' => $response->userid));
                echo '<div class="d-flex justify-content-between align-items-center mb-1">';
                echo '<span class="small">' . fullname($user) . '</span>';
                echo '<span class="badge badge-success small">' . userdate($response->timemodified, '%d/%m') . '</span>';
                echo '</div>';
            }
            echo '</div>';
        }
        
        
        echo '</div>';
        
        // Add custom CSS
        echo '<style>
        .chaside-management-block {
            padding: 15px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e3f2fd 100%);
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        .stat-card {
            padding: 8px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .stat-number {
            font-size: 1.2em;
            font-weight: bold;
        }
        .stat-label {
            font-size: 0.75em;
            color: #6c757d;
        }
        .chaside-recent {
            background: white;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #e9ecef;
        }
        .chaside-actions .btn {
            box-shadow: 0 2px 4px rgba(0,123,255,0.2);
        }
        </style>';
    }
}

class ChasideFacade {
    
    public function get_question_mapping() {
        // Mapeo oficial del test CHASIDE - INTERESES (70 preguntas) y APTITUDES (28 preguntas)
        return array(
            // INTERESES - C: [1, 12, 20, 53, 64, 71, 78, 85, 91, 98]
            1 => 'C', 12 => 'C', 20 => 'C', 53 => 'C', 64 => 'C', 71 => 'C', 78 => 'C', 85 => 'C', 91 => 'C', 98 => 'C',
            // INTERESES - H: [9, 25, 34, 41, 56, 67, 74, 80, 89, 95]
            9 => 'H', 25 => 'H', 34 => 'H', 41 => 'H', 56 => 'H', 67 => 'H', 74 => 'H', 80 => 'H', 89 => 'H', 95 => 'H',
            // INTERESES - A: [3, 11, 21, 28, 36, 45, 50, 57, 81, 96]
            3 => 'A', 11 => 'A', 21 => 'A', 28 => 'A', 36 => 'A', 45 => 'A', 50 => 'A', 57 => 'A', 81 => 'A', 96 => 'A',
            // INTERESES - S: [8, 16, 23, 33, 44, 52, 62, 70, 87, 92]
            8 => 'S', 16 => 'S', 23 => 'S', 33 => 'S', 44 => 'S', 52 => 'S', 62 => 'S', 70 => 'S', 87 => 'S', 92 => 'S',
            // INTERESES - I: [6, 19, 27, 38, 47, 54, 60, 75, 83, 97]
            6 => 'I', 19 => 'I', 27 => 'I', 38 => 'I', 47 => 'I', 54 => 'I', 60 => 'I', 75 => 'I', 83 => 'I', 97 => 'I',
            // INTERESES - D: [5, 14, 24, 31, 37, 48, 58, 65, 73, 84]
            5 => 'D', 14 => 'D', 24 => 'D', 31 => 'D', 37 => 'D', 48 => 'D', 58 => 'D', 65 => 'D', 73 => 'D', 84 => 'D',
            // INTERESES - E: [17, 32, 35, 42, 49, 61, 68, 77, 88, 93]
            17 => 'E', 32 => 'E', 35 => 'E', 42 => 'E', 49 => 'E', 61 => 'E', 68 => 'E', 77 => 'E', 88 => 'E', 93 => 'E',
            
            // APTITUDES - C: [2, 15, 46, 51]
            2 => 'C', 15 => 'C', 46 => 'C', 51 => 'C',
            // APTITUDES - H: [30, 63, 72, 86]
            30 => 'H', 63 => 'H', 72 => 'H', 86 => 'H',
            // APTITUDES - A: [22, 39, 76, 82]
            22 => 'A', 39 => 'A', 76 => 'A', 82 => 'A',
            // APTITUDES - S: [4, 29, 40, 69]
            4 => 'S', 29 => 'S', 40 => 'S', 69 => 'S',
            // APTITUDES - I: [10, 26, 59, 90]
            10 => 'I', 26 => 'I', 59 => 'I', 90 => 'I',
            // APTITUDES - D: [13, 18, 43, 66]
            13 => 'D', 18 => 'D', 43 => 'D', 66 => 'D',
            // APTITUDES - E: [7, 55, 79, 94]
            7 => 'E', 55 => 'E', 79 => 'E', 94 => 'E'
        );
    }
    
    public function calculate_scores($responses) {
        $mapping = $this->get_question_mapping();
        $scores = array('C' => 0, 'H' => 0, 'A' => 0, 'S' => 0, 'I' => 0, 'D' => 0, 'E' => 0);
        
        for ($i = 1; $i <= 98; $i++) {
            if (isset($responses["q{$i}"]) && $responses["q{$i}"] == 1) {
                $area = $mapping[$i];
                $scores[$area]++;
            }
        }
        
        return $scores;
    }
    
    public function get_top_areas($scores, $limit = 7) {
        arsort($scores);
        $top_areas = array();
        $count = 0;
        foreach ($scores as $area => $score) {
            if ($count >= $limit) break;
            $top_areas[] = array('area' => $area, 'score' => $score);
            $count++;
        }
        return $top_areas;
    }
    
    public function get_area_descriptions() {
        return array(
            'C' => get_string('desc_c', 'block_chaside'),
            'H' => get_string('desc_h', 'block_chaside'),
            'A' => get_string('desc_a', 'block_chaside'),
            'S' => get_string('desc_s', 'block_chaside'),
            'I' => get_string('desc_i', 'block_chaside'),
            'D' => get_string('desc_d', 'block_chaside'),
            'E' => get_string('desc_e', 'block_chaside')
        );
    }
    
    /**
     * Calculate detailed scores separating interests and aptitudes
     */
    public function calculate_detailed_scores($responses) {
        $mapping = $this->get_question_mapping();
        
        // Initialize scores
        $scores = array();
        foreach (['C', 'H', 'A', 'S', 'I', 'D', 'E'] as $area) {
            $scores[$area] = array(
                'interes_score' => 0,
                'aptitud_score' => 0
            );
        }
        
        // Interest questions (70 total)
        $interest_questions = array(
            'C' => [1, 12, 20, 53, 64, 71, 78, 85, 91, 98],
            'H' => [9, 25, 34, 41, 56, 67, 74, 80, 89, 95],
            'A' => [3, 11, 21, 28, 36, 45, 50, 57, 81, 96],
            'S' => [8, 16, 23, 33, 44, 52, 62, 70, 87, 92],
            'I' => [6, 19, 27, 38, 47, 54, 60, 75, 83, 97],
            'D' => [5, 14, 24, 31, 37, 48, 58, 65, 73, 84],
            'E' => [17, 32, 35, 42, 49, 61, 68, 77, 88, 93]
        );
        
        // Aptitude questions (28 total)
        $aptitude_questions = array(
            'C' => [2, 15, 46, 51],
            'H' => [30, 63, 72, 86],
            'A' => [22, 39, 76, 82],
            'S' => [4, 29, 40, 69],
            'I' => [10, 26, 59, 90],
            'D' => [13, 18, 43, 66],
            'E' => [7, 55, 79, 94]
        );
        
        // Count interest scores
        foreach ($interest_questions as $area => $questions) {
            foreach ($questions as $q) {
                if (isset($responses["q{$q}"]) && $responses["q{$q}"] == 1) {
                    $scores[$area]['interes_score']++;
                }
            }
        }
        
        // Count aptitude scores
        foreach ($aptitude_questions as $area => $questions) {
            foreach ($questions as $q) {
                if (isset($responses["q{$q}"]) && $responses["q{$q}"] == 1) {
                    $scores[$area]['aptitud_score']++;
                }
            }
        }
        
        return $scores;
    }
    
    /**
     * Calculate percentages for interests, aptitudes and total
     */
    public function calculate_percentages($scores) {
        $percentages = array();
        
        foreach ($scores as $area => $area_scores) {
            $total_score = $area_scores['interes_score'] + $area_scores['aptitud_score'];
            
            $percentages[$area] = array(
                'pct_interes' => round(100 * $area_scores['interes_score'] / 10, 1), // Max 10 interest questions per area
                'pct_aptitud' => round(100 * $area_scores['aptitud_score'] / 4, 1),   // Max 4 aptitude questions per area
                'pct_total' => round(100 * $total_score / 14, 1)                      // Max 14 total per area
            );
        }
        
        return $percentages;
    }
    
    /**
     * Determine levels based on total percentage
     */
    public function determine_levels($percentages) {
        $levels = array();
        
        foreach ($percentages as $area => $pcts) {
            $pct_total = $pcts['pct_total'];
            
            if ($pct_total >= 80.0) {
                $levels[$area] = 'level_alto';
            } elseif ($pct_total >= 60.0) {
                $levels[$area] = 'level_medio';
            } elseif ($pct_total >= 40.0) {
                $levels[$area] = 'level_emergente';
            } else {
                $levels[$area] = 'level_bajo';
            }
        }
        
        return $levels;
    }
    
    /**
     * Detect interest-aptitude gaps
     */
    public function detect_gaps($percentages) {
        $gaps = array();
        $threshold = 20.0; // 20 percentage points
        
        foreach ($percentages as $area => $pcts) {
            $diff = $pcts['pct_interes'] - $pcts['pct_aptitud'];
            
            if ($diff >= $threshold) {
                $gaps[$area] = 'gap_interest_higher';
            } elseif ($diff <= -$threshold) {
                $gaps[$area] = 'gap_aptitude_higher';
            } else {
                $gaps[$area] = 'gap_balanced';
            }
        }
        
        return $gaps;
    }
    
    /**
     * Get top areas with official CHASIDE tiebreaker rules
     */
    public function get_top_areas_v2($scores, $limit = 2) {
        $areas_with_totals = array();
        
        foreach ($scores as $area => $area_scores) {
            $total = $area_scores['interes_score'] + $area_scores['aptitud_score'];
            $areas_with_totals[] = array(
                'area' => $area,
                'total_score' => $total,
                'interes_score' => $area_scores['interes_score'],
                'aptitud_score' => $area_scores['aptitud_score'],
                'gap' => abs($area_scores['interes_score'] - $area_scores['aptitud_score'])
            );
        }
        
        // Sort with tiebreaker rules:
        // 1. Higher total_score
        // 2. Higher aptitud_score
        // 3. Lower gap (|interes - aptitud|)
        // 4. Alphabetical by area
        usort($areas_with_totals, function($a, $b) {
            if ($a['total_score'] != $b['total_score']) {
                return $b['total_score'] - $a['total_score'];
            }
            if ($a['aptitud_score'] != $b['aptitud_score']) {
                return $b['aptitud_score'] - $a['aptitud_score'];
            }
            if ($a['gap'] != $b['gap']) {
                return $a['gap'] - $b['gap'];
            }
            return strcmp($a['area'], $b['area']);
        });
        
        return array_slice($areas_with_totals, 0, $limit);
    }
    
    /**
     * Generate complete results JSON according to official CHASIDE format
     */
    public function generate_results_json($responses, $meta = array()) {
        // Calculate detailed scores
        $detailed_scores = $this->calculate_detailed_scores($responses);
        $percentages = $this->calculate_percentages($detailed_scores);
        $levels = $this->determine_levels($percentages);
        $gaps = $this->detect_gaps($percentages);
        $top_areas = $this->get_top_areas_v2($detailed_scores, 2);
        
        // Area labels
        $labels = array(
            'C' => get_string('area_c', 'block_chaside'),
            'H' => get_string('area_h', 'block_chaside'),
            'A' => get_string('area_a', 'block_chaside'),
            'S' => get_string('area_s', 'block_chaside'),
            'I' => get_string('area_i', 'block_chaside'),
            'D' => get_string('area_d', 'block_chaside'),
            'E' => get_string('area_e', 'block_chaside')
        );
        
        // Build executive summary
        $top1 = !empty($top_areas) ? $top_areas[0] : null;
        $top2 = count($top_areas) > 1 ? $top_areas[1] : null;
        
        $quick_reading = '';
        $gap_alerts = array();
        
        if ($top1) {
            $quick_reading = "Mayor fortaleza en " . $labels[$top1['area']];
            if ($top2) {
                $quick_reading .= " y " . $labels[$top2['area']];
            }
        }
        
        // Detect significant gaps for alerts
        foreach ($gaps as $area => $gap_type) {
            if ($gap_type != 'gap_balanced') {
                $gap_alerts[] = array(
                    'area' => $area,
                    'tipo' => get_string($gap_type, 'block_chaside')
                );
            }
        }
        
        // Build main table
        $main_table = array();
        foreach (['C', 'H', 'A', 'S', 'I', 'D', 'E'] as $area) {
            $main_table[] = array(
                'area' => $area,
                'label' => $labels[$area],
                'interes' => array(
                    'score' => $detailed_scores[$area]['interes_score'],
                    'pct' => $percentages[$area]['pct_interes']
                ),
                'aptitud' => array(
                    'score' => $detailed_scores[$area]['aptitud_score'],
                    'pct' => $percentages[$area]['pct_aptitud']
                ),
                'total' => array(
                    'score' => $detailed_scores[$area]['interes_score'] + $detailed_scores[$area]['aptitud_score'],
                    'pct' => $percentages[$area]['pct_total']
                ),
                'nivel' => get_string($levels[$area], 'block_chaside'),
                'brecha' => get_string($gaps[$area], 'block_chaside'),
                'interpretacion_breve' => get_string('desc_' . strtolower($area), 'block_chaside')
            );
        }
        
        // Generate recommendations
        $recommendations = array(
            get_string('rec_prioritize_top', 'block_chaside')
        );
        
        if (!empty($gap_alerts)) {
            foreach ($gap_alerts as $alert) {
                if ($alert['tipo'] == get_string('gap_interest_higher', 'block_chaside')) {
                    $recommendations[] = get_string('rec_interest_higher', 'block_chaside');
                } elseif ($alert['tipo'] == get_string('gap_aptitude_higher', 'block_chaside')) {
                    $recommendations[] = get_string('rec_aptitude_higher', 'block_chaside');
                }
            }
        } else {
            $recommendations[] = get_string('rec_balanced_development', 'block_chaside');
        }
        
        $recommendations[] = get_string('rec_explore_combinations', 'block_chaside');
        
        // Build final result
        $result = array(
            'meta' => $meta,
            'resumen_ejecutivo' => array(
                'top1' => $top1 ? array(
                    'area' => $top1['area'],
                    'label' => $labels[$top1['area']],
                    'total' => $top1['total_score'],
                    'pct_total' => $percentages[$top1['area']]['pct_total'],
                    'i' => $top1['interes_score'],
                    'a' => $top1['aptitud_score']
                ) : null,
                'top2' => $top2 ? array(
                    'area' => $top2['area'],
                    'label' => $labels[$top2['area']],
                    'total' => $top2['total_score'],
                    'pct_total' => $percentages[$top2['area']]['pct_total'],
                    'i' => $top2['interes_score'],
                    'a' => $top2['aptitud_score']
                ) : null,
                'lectura_rapida' => $quick_reading,
                'alertas_brecha' => $gap_alerts
            ),
            'tabla_principal' => $main_table,
            'recomendaciones' => $recommendations,
            'apendice_opcional' => array(
                'nota' => get_string('orientation_note', 'block_chaside')
            )
        );
        
        return $result;
    }
}
