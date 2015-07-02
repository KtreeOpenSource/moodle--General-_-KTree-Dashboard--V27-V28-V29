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
require_once($CFG->dirroot . '/local/dashboard/lib.php');
redirect_if_major_upgrade_required();
$edit   = optional_param('edit', null, PARAM_BOOL);
require_login();
$strmymoodle = 'Dashboard';
if (isguestuser()) {
    if (empty($CFG->allowguestmymoodle)) {
        redirect(new moodle_url('/', array('redirect' => 0)));
    }
    $userid = null; 
    $USER->editing = $edit = 0;
    $context = context_system::instance();
    $PAGE->set_blocks_editing_capability('moodle/dashboard:configsyspages');
    $header = "$SITE->shortname: $strmymoodle (GUEST)";
} else {
    $userid = $USER->id;
    $context = context_user::instance($USER->id);
    $PAGE->set_blocks_editing_capability('moodle/my:manageblocks');
    $header = "$SITE->shortname: $strmymoodle";
}
if (!$currentpage->userid) {
    $context = context_system::instance();
}
$params = array();
$PAGE->set_context($context);
$PAGE->set_url('/local/dashboard/index.php', $params);
$PAGE->set_pagelayout('mydashboard');
$PAGE->set_pagetype('my-index');
$PAGE->blocks->add_region('content');
$PAGE->set_subpage($currentpage->id);
$PAGE->set_title($header);
$PAGE->set_heading($header);
if (!isguestuser()) {
    if (get_home_page() != HOMEPAGE_MY) {
        if (optional_param('setdefaulthome', false, PARAM_BOOL)) {
            set_user_preference('user_home_page_preference', HOMEPAGE_MY);
        } else if (!empty($CFG->defaulthomepage) && $CFG->defaulthomepage == HOMEPAGE_USER) {
            $PAGE->settingsnav->get('usercurrentsettings')->add(get_string('makethismyhome'), new moodle_url('/my/', array('setdefaulthome' => true)), navigation_node::TYPE_SETTING);
        }
    }
}
if ($PAGE->user_allowed_editing()) {
    if ($edit !== null) {
        $USER->editing = $edit;
        if (!$currentpage->userid && $edit) {
            if (!$currentpage = my_copy_page($USER->id, MY_PAGE_PRIVATE)) {
                print_error('mymoodlesetup');
            }
            $context = context_user::instance($USER->id);
            $PAGE->set_context($context);
            $PAGE->set_subpage($currentpage->id);
        }
    } else {
        if ($currentpage->userid) {
            if (!empty($USER->editing)) {
                $edit = 1;
            } else {
                $edit = 0;
            }
        } else {
            $USER->editing = $edit = 0;
        }
    }
    $params = array('edit' => !$edit);
    if (!$currentpage->userid) {
        $editstring = get_string('updatemymoodleon');
        $params['edit'] = 1;
    } else if (empty($edit)) {
        $editstring = get_string('updatemymoodleon');
    } else {
        $editstring = get_string('updatemymoodleoff');
    }
    $url = new moodle_url("$CFG->wwwroot/local/dashboard/index.php", $params);
    $button = $OUTPUT->single_button($url, $editstring);
    $PAGE->set_button($button);

} else {
    $USER->editing = $edit = 0;
}
if ($currentpage->userid == 0) {
    $CFG->blockmanagerclass = 'my_syspage_block_manager';
}
echo $OUTPUT->header();
$enrolresults = enrol_get_my_courses('summary', 'visible DESC,sortorder ASC');
$coursesarray = array();
foreach ($enrolresults as $enrol) {
    $coursesarray[] =  $enrol->id;
}
$courses = implode(",", $coursesarray);
$flag = 0;
if ($courses != null) {
    $moddetails = $DB->get_records_sql("select id ,name from {modules}");
    $table = "<table style='width: 70%; ' align='center'><tr><th> Course Name</th><th> Module name</th></tr>";
    foreach ($moddetails as $modkey => $modval) {
        $assign = $DB->get_records_sql("select a.name as name,a.course as courseid,cm.id as id,cs.fullname as cfname from {course} as cs join {".$modval->name."} as a on cs.id=a.course join {course_modules} as cm on a.course= cm.course and a.id=cm.instance where cm.module=$modval->id and a.course in($courses)");
        if ($assign != null) {
            $flag = 1;
            $table.= "<tr><td colspan=2 align=center><b>".$modval->name."</b></td></tr>";
            foreach ($assign as $name => $val) {
                $table.= '<tr><td><a class="dashboardlink" href="'.$CFG->wwwroot.'/course/view.php?id='.$val->courseid.'">'.$val->cfname.'</a></td>';
                $table.= '<td><a class="dashboardlink" href="'.$CFG->wwwroot.'/mod/'.$modval->name.'/view.php?id='.$val->id.'">'.$val->name.'</a></td></tr>';
            }
        }
    }
    $table.= "</table>";
}
if ($flag == 0) {
    echo "Sorry no activities are available.";
} else {
    echo $table;
}
echo $OUTPUT->footer();
