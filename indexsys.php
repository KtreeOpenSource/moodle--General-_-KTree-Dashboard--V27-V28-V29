<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Local plugin "dashboard" - Settings
 *
 * @package    local_dashboard
 * @copyright   2015 Ktree Computer Solutions <opensource@ktree.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot . '/dashboard/lib.php');
require_once($CFG->libdir.'/adminlib.php');

$edit   = optional_param('edit', null, PARAM_BOOL);

require_login();

$context = context_system::instance();
require_capability('moodle/dashboard:configsyspages', $context);
$PAGE->set_blocks_editing_capability('moodle/dashboard:configsyspages');
$header = "$SITE->shortname: ".dashboard." (".get_string('mypage', 'admin').")";
$params = array();
$PAGE->set_url('/local/dashboard/indexsys.php', $params);
$PAGE->set_pagelayout('mydashboard');
$PAGE->set_pagetype('my-index');
$PAGE->set_context($context);
$PAGE->set_title($header);
$PAGE->set_heading($header);
$PAGE->blocks->add_region('content');
if (!$currentpage = my_get_page(null, MY_PAGE_PRIVATE)) {
    print_error('mymoodlesetup');
}
$PAGE->set_subpage($currentpage->id);
if ($PAGE->user_allowed_editing()) {
    if ($edit !== null) {
        $USER->editing = $edit;
    } else {
        if (!empty($USER->editing)) {
            $edit = 1;
        } else {
            $edit = 0;
        }
    }
    $params['edit'] = !$edit;

    if (empty($edit)) {
        $editstring = get_string('updatemymoodleon');
    } else {
        $editstring = get_string('updatemymoodleoff');
    }
    $url = new moodle_url("$CFG->wwwroot/local/dashboard/indexsys.php", $params);
    $button = $OUTPUT->single_button($url, $editstring);
    $PAGE->set_button($button);

} else {
    $USER->editing = $edit = 0;
}

echo $OUTPUT->header();

echo $OUTPUT->blocks_for_region('content');

echo $OUTPUT->footer();
