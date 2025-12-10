<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_block_chaside_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2025120901) {
        // Define index to be added to block_chaside_responses
        $table = new xmldb_table('block_chaside_responses');
        
        // Drop old non-unique index if exists
        $old_index = new xmldb_index('userid_courseid', XMLDB_INDEX_NOTUNIQUE, ['userid', 'courseid']);
        if ($dbman->index_exists($table, $old_index)) {
            $dbman->drop_index($table, $old_index);
        }
        
        // Add unique index on userid to prevent duplicate tests per user
        $index = new xmldb_index('userid_unique', XMLDB_INDEX_UNIQUE, ['userid']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // CHASIDE savepoint reached.
        upgrade_block_savepoint(true, 2025120901, 'chaside');
    }

    return true;
}
