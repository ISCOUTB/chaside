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
        
        return $this->content;
    }
    
    private function show_student_results($response) {
        global $COURSE;
        
        // Convert stdClass object to array for the facade
        $response_array = (array) $response;
        
        // Calculate scores using the facade
        $facade = new ChasideFacade();
        $scores = $facade->calculate_scores($response_array);
        $top_areas = $facade->get_top_areas($scores, 3);
        
        // Completion date
        $completion_date = '';
        if (isset($response->timecompleted) && $response->timecompleted > 0) {
            $completion_date = userdate($response->timecompleted, get_string('strftimedatefullshort'));
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
        
        // Top area highlight
        if (!empty($top_areas)) {
            $top_area = $top_areas[0];
            $area_name = get_string('area_' . strtolower($top_area['area']), 'block_chaside');
            $area_desc = get_string('desc_' . strtolower($top_area['area']), 'block_chaside');
            
            echo '<div class="chaside-top-area mb-3">';
            echo '<div class="card border-primary" style="border-left: 4px solid #007bff !important;">';
            echo '<div class="card-body p-3">';
            echo '<h6 class="card-title mb-2">';
            echo '<i class="fa fa-star text-warning"></i> ';
            echo get_string('your_top_area', 'block_chaside');
            echo '</h6>';
            echo '<p class="card-text mb-2"><strong>' . $area_name . '</strong></p>';
            echo '<p class="card-text"><small class="text-muted">' . substr($area_desc, 0, 100) . '...</small></p>';
            echo '<div class="progress mt-2" style="height: 8px;">';
            $percentage = ($top_area['score'] / 14) * 100; // Max score per area is 14
            echo '<div class="progress-bar bg-primary" style="width: ' . $percentage . '%"></div>';
            echo '</div>';
            echo '<small class="text-muted">' . $top_area['score'] . '/14 ' . get_string('points', 'block_chaside') . '</small>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        
        // Quick summary of other areas
        echo '<div class="chaside-other-areas mb-3">';
        echo '<h6 class="mb-2">' . get_string('other_strong_areas', 'block_chaside') . '</h6>';
        for ($i = 1; $i < min(3, count($top_areas)); $i++) {
            $area = $top_areas[$i];
            $area_name = get_string('area_' . strtolower($area['area']), 'block_chaside');
            echo '<div class="d-flex justify-content-between align-items-center mb-1">';
            echo '<span class="small">' . ($i + 1) . '. ' . $area_name . '</span>';
            echo '<span class="badge badge-secondary">' . $area['score'] . '</span>';
            echo '</div>';
        }
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
}

class ChasideFacade {
    
    public function get_question_mapping() {
        return array(
            1 => 'C', 2 => 'H', 3 => 'A', 4 => 'S', 5 => 'I', 6 => 'D', 7 => 'E',
            8 => 'C', 9 => 'H', 10 => 'A', 11 => 'S', 12 => 'I', 13 => 'D', 14 => 'E',
            15 => 'C', 16 => 'H', 17 => 'A', 18 => 'S', 19 => 'I', 20 => 'D', 21 => 'E',
            22 => 'C', 23 => 'H', 24 => 'A', 25 => 'S', 26 => 'I', 27 => 'D', 28 => 'E',
            29 => 'C', 30 => 'H', 31 => 'A', 32 => 'S', 33 => 'I', 34 => 'D', 35 => 'E',
            36 => 'C', 37 => 'H', 38 => 'A', 39 => 'S', 40 => 'I', 41 => 'D', 42 => 'E',
            43 => 'C', 44 => 'H', 45 => 'A', 46 => 'S', 47 => 'I', 48 => 'D', 49 => 'E',
            50 => 'C', 51 => 'H', 52 => 'A', 53 => 'S', 54 => 'I', 55 => 'D', 56 => 'E',
            57 => 'C', 58 => 'H', 59 => 'A', 60 => 'S', 61 => 'I', 62 => 'D', 63 => 'E',
            64 => 'C', 65 => 'H', 66 => 'A', 67 => 'S', 68 => 'I', 69 => 'D', 70 => 'E',
            71 => 'C', 72 => 'H', 73 => 'A', 74 => 'S', 75 => 'I', 76 => 'D', 77 => 'E',
            78 => 'C', 79 => 'H', 80 => 'A', 81 => 'S', 82 => 'I', 83 => 'D', 84 => 'E',
            85 => 'C', 86 => 'H', 87 => 'A', 88 => 'S', 89 => 'I', 90 => 'D', 91 => 'E',
            92 => 'C', 93 => 'H', 94 => 'A', 95 => 'S', 96 => 'I', 97 => 'D', 98 => 'E'
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
                'timecompleted DESC', '*', 0, 3);
                
            echo '<div class="chaside-recent mb-3">';
            echo '<h6 class="mb-2">' . get_string('recent_completions', 'block_chaside') . '</h6>';
            foreach ($recent_responses as $response) {
                $user = $DB->get_record('user', array('id' => $response->userid));
                echo '<div class="d-flex justify-content-between align-items-center mb-1">';
                echo '<span class="small">' . fullname($user) . '</span>';
                echo '<span class="badge badge-success small">' . userdate($response->timecompleted, '%d/%m') . '</span>';
                echo '</div>';
            }
            echo '</div>';
        }
        
        // Action buttons
        echo '<div class="chaside-actions text-center">';
        $url = new moodle_url('/blocks/chaside/manage.php', array(
            'courseid' => $COURSE->id,
            'blockid' => $this->instance->id
        ));
        echo '<a href="' . $url . '" class="btn btn-primary btn-sm btn-block">';
        echo '<i class="fa fa-cog"></i> ' . get_string('manage_responses', 'block_chaside');
        echo '</a>';
        echo '</div>';
        
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
        
        echo '</div>';
    }
}
