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
        
        if (!has_capability('block/chaside:take_test', $this->context)) {
            return $this->content;
        }
        
        // Check if user has already taken the test
        $response = $DB->get_record('block_chaside_responses', array(
            'userid' => $USER->id,
            'courseid' => $COURSE->id
        ));
        
        if ($response && $response->is_completed) {
            // Show results link
            $url = new moodle_url('/blocks/chaside/view_results.php', array(
                'courseid' => $COURSE->id,
                'blockid' => $this->instance->id
            ));
            $this->content->text = html_writer::link($url, get_string('view_results', 'block_chaside'));
        } else {
            // Show test link
            $url = new moodle_url('/blocks/chaside/view.php', array(
                'courseid' => $COURSE->id,
                'blockid' => $this->instance->id
            ));
            $link_text = $response ? get_string('continue_test', 'block_chaside') : get_string('start_test', 'block_chaside');
            $this->content->text = html_writer::link($url, $link_text);
        }
        
        return $this->content;
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
    
    public function get_top_areas($scores) {
        arsort($scores);
        $top_areas = array();
        foreach ($scores as $area => $score) {
            $top_areas[] = array('area' => $area, 'score' => $score);
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
}
