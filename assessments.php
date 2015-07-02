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
 * @copyright   2015 KTree Computer Solutions <opensource@ktree.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../config.php');
global $OUTPUT,$PAGE, $CFG, $DB;
require_login();
$title = 'Assessments';
?>
<style type="text/css">
    .breadcrumb{
        display ; none;
    }
</style>
<?php
$PAGE->set_url($CFG->dirroot.'/dashboard/assessments.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('my');
$PAGE->set_title($title);
$PAGE->set_heading('Assessment');
echo $OUTPUT->header('Assessment');
$PAGE->set_heading($title);
$admins = get_admins();
$isadmin = false;
foreach ($admins as $admin) {
    if ($USER->id == $admin->id) {
        $isadmin = true;
        break;
    }
}
$asgncount = 0;
$modules = array(1, 16, 13, 7, 22, 10, 14, 6, 4, 23, 9, 19, 21, 5, 18);
$tables = array('assign', 'quiz', 'lesson', 'feedback', 'workshop', 'glossary', 'lti', 'data', 'chat', 'certificate', 'forum', 'survey', 'wiki', 'choice', 'scorm');
$text = '';
$coursequery = "select id, shortname from {course} where format != 'site'";
$courserun = $DB->get_records_sql($coursequery);
if ($courserun != null) {
    foreach ($courseRun as $cind => $course) {
        if ($isadmin) {
            $link1 = $CFG->wwwroot.'/course/view.php?id='.$course->id;
        } else {
            if ($modulevalue == 18) {
                $modinfo = get_fast_modinfo($course);
                $mods = $modinfo->get_cms();
                $cmid = '';
                foreach ($mods as $iii => $jjj) {
                    $cmid = $iii;
                    break;
                }
                $context = get_context_instance(CONTEXT_MODULE, $cmid);
                $contextid = '';
                foreach ($context as $testi => $testv) {
                    $contextid = $testv;
                    break;
                }
                $link1 = $CFG->wwwroot.'/pluginfile.php/'.$contextid.'/mod_scorm/content/1/launch.html?pagename=assessments';
            } else {
                $link1 = $CFG->wwwroot.'/course/view.php?id='.$course->id;
            }
        }
        $tp = '<a class="dashboardlink" href="'.$link1.'">'.ucwords($course->shortname).'</a>';
        $asgntext = '<table><thead><tr><td><b>'.$tp.'</b></td></tr></thead><tbody>';
        foreach ($modules as $moduleindex => $modulevalue) {
            $assign = $DB->get_records_sql("select a.name as name,a.course as courseid,cm.id as id,cs.fullname as cfname from {course} as cs join {".$tables[$moduleindex]."} as a on cs.id=a.course join {course_modules} as cm on a.course= cm.course and a.id=cm.instance where cm.module=".$modulevalue." and a.course=".$course->id);
            $asgn = array();
            if (!empty($assign)) {
                foreach ($assign as $ci => $cj )
                {
                    $asgntext .= '<tr>';
                    $link = $CFG->wwwroot.'/mod/'.$tables[$moduleindex].'/view.php?id='.$cj->id;
                    $asgntext .= '<td><a class = "dashboardlink" href="'.$link.'"><label>'.ucwords($cj->name).'</label></a></td></tr>';
                    $asgncount++;
                }
            }
        }
        $asgntext .= "</tbody></table><br />";
        $text .= $asgntext;
    }
}
if ($asgncount == 0) {
    $asgntext .= "<table>";
    $asgntext .= '<tr><td><td class="black">No Assignments are available</td></tr>';
    $asgntext .= "</table>";
    $text .= $asgntext;
}
?>
<div style="width: 100%;">
<div style="width: 100%;" align="center"><h2 class="main">Assessments</h2></div>
<?php
echo $text;
echo $OUTPUT->footer();