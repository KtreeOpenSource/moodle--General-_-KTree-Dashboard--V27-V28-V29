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

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot . '/local/dashboard/lib.php');
$PAGE->set_pagelayout('frontpage');
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
$params = array();
$PAGE->set_url('/local/dashboard/index.php', $params);
$PAGE->set_pagelayout('mydashboard');
$PAGE->set_pagetype('my-index');
$PAGE->set_context(context_system::instance());
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
echo $OUTPUT->header();
$ufields = "u.id,u.picture,u.firstname,u.lastname,u.imagealt,u.email,u.lastaccess";
$users = $DB->get_records_sql("SELECT $ufields, COUNT(m.useridfrom) AS count FROM {user} u, {message} m WHERE m.useridto = ? AND u.id = m.useridfrom AND m.notification = 0 GROUP BY $ufields", array($USER->id));
$text = '';	
if (!empty($users)) {
    $text .= '<table>';
    foreach ($users as $user) {
        $timeago = format_time(time() - $user->lastaccess);
        $text .= '<tr><td><div class="user"><a class="dashboardlink" href="'.$CFG->wwwroot.'/user/view.php?id='.$user->id.'&amp;course='.SITEID.'" title="'.$timeago.'">';
        $text .= fullname($user).'</a></div>';
        $link = '/message/index.php?usergroup=unread&id='.$user->id;
        $anchortagcontents = '<img class="iconsmall" src="'.$OUTPUT->pix_url('t/message') . '" alt="" />&nbsp;'.$user->count;
        $action = null;
        $anchortag = $OUTPUT->action_link($link, $anchortagcontents, $action);
        $text .= '<div class="message">'.$anchortag.'</div></td></tr>';
	}
    $text .= '</table>';
} else {
    $text = "<table><tr><td class='black'>";
    $text .= get_string('nomessages', 'message')."</td></tr>";
    $text .= '<tr><td><a class="dashboardlink" href="'.$CFG->wwwroot.'/message/index.php">Messages</a></td></tr>';
    $text .= "</table>";
}
$enrolresult = enrol_get_my_courses('summary', 'visible DESC,sortorder ASC');
$courses = array();
	foreach ($enrolresult as $enrol) {
        $courses[] = $DB->get_record('course', array('id'=>$enrol->id));
	}
$category = array();
$cours = array();
$shortnames = array();
if (!empty($courses)) {
    $coursecount = 0;
    foreach ($courses as $ci => $cj) {
        if ($coursecount < 6) {
            $modinfo = get_fast_modinfo($cj);
            $mods = $modinfo->get_cms();
            $category=$DB->get_record('course_categories', array('id'=>$cj->category));
            $cmid = '';
            foreach ($mods as $iii => $jjj) {
                $cmid = $iii;
				break;
            }
            $context = get_context_instance(CONTEXT_MODULE, $cmid);
			//Comment $context = context_course::instance(CONTEXT_MODULE, $cmid);.
            $contextid = '';
            foreach ($context as $testi => $testv) {
                $contextid = $testv;
				break;
            }
            $cours[$category->name][] = $cj->id.'||'.$cj->shortname.'||'.$contextid.'||'.$cj->format.'||'.$cmid;
            $shortnames[] = "'".$cj->shortname."'";
        }
        $coursecount++;	
    }
    $coursetext = "<table class='module-sec'>";
	
    foreach ($cours as $cname => $cval) {
        foreach ($cval as $vl => $cc) {
            $ts = explode("||", $cc);
            if ($isadmin) {
                $coursetext .='<tr><td><a class="dashboardlink" href="'.$CFG->wwwroot.'/course/view.php?id='.$ts[0].'">'.$ts[1].'</a></td>';
            } else {
                if (!strcmp(strtolower($ts[3]), 'scorm')) {
                    $coursetext .='<tr><td><a class="dashboardlink" href="'.$CFG->wwwroot.'/pluginfile.php/'.$ts[2].'/mod_scorm/content/1/launch.html?pagename=dashboard">'.$ts[1].'</a></td></tr>';
                } else {
                    $coursetext .='<tr><td><a class="dashboardlink" href="'.$CFG->wwwroot.'/course/view.php?id='.$ts[0].'">'.$ts[1].'</a></td>';
                }
            }
        $coursetext .= 	'</tr>';
		}	
	}	
	$coursetext .='</table>';
} else {
    $coursetext = "<table>";
    $coursetext .= '<tr><td>No courses are available</td></tr>';
    $coursetext .= '<tr><td><a class="dashboardlink" href="'.$CFG->wwwroot.'/course/index.php">Courses</a></td></tr>';
    $coursetext .= "</table>";
}
$blogquery = "select id, subject from {post} where module= 'blog' order by id desc limit 0,6";
$blogrun = $DB->get_records_sql($blogquery);
if (sizeof($blogrun) > 0) {
    $blogtext = "<table>";
    foreach ($blogrun as $bval) {
        $blogtext .= '<tr><td><a class="dashboardlink" href="'.$CFG->wwwroot.'/blog/index.php?entryid='.$bval->id.'">'.$bval->subject.'</a></td></tr>';
	}
    $blogtext .= '<tr><td><a class="dashboardlink" href="'.$CFG->wwwroot.'/blog/edit.php?action=add">Add a new entry</a></td></tr>';
    $blogtext .= "</table>";
} else {
    $blogtext = "<table>";
    $blogtext .= '<tr><td><a class="dashboardlink" href="'.$CFG->wwwroot.'/blog/index.php?userid='.$USER->id.'">View all of my entries</a></td></tr>';
    $blogtext .= '<tr><td><a class="dashboardlink" href="'.$CFG->wwwroot.'/blog/edit.php?action=add">Add a new entry</a></td></tr>';
    $blogtext .= "</table>";
}
$enrolresults = enrol_get_my_courses('summary', 'visible DESC,sortorder ASC');
$coursesarray = array();
foreach ($enrolresults as $enrol) {
	$coursesarray[] =  $enrol->id;
}
$courses= implode(",",$coursesarray);
if ($courses != null) {
    $assignmoduleid = $DB->get_record_sql("select id as id  from {modules} where name='assign'");
    $assign = $DB->get_records_sql("select a.name as name,a.course as courseid,cm.id as id,cs.fullname as cfname from {course} as cs join {assign} as a on cs.id=a.course join {course_modules} as cm on a.course= cm.course and a.id=cm.instance where cm.module=$assignmoduleid->id and a.course in($courses)");
    $quizmoduleid = $DB->get_record_sql("select id as id  from {modules} where name='quiz'");
    $quiz = $DB->get_records_sql("select a.name as name,a.course as courseid,cm.id as id,cs.fullname as cfname from {course} as cs join {quiz} as a on cs.id=a.course join {course_modules} as cm on a.course= cm.course and a.id=cm.instance where cm.module=$quizmoduleid->id and a.course in($courses)");
    $workshopmoduleid = $DB->get_record_sql("select id as id  from {modules} where name='workshop'");
    $workshop = $DB->get_records_sql("select a.name as name,a.course as courseid,cm.id as id,cs.fullname as cfname from {course} as cs join {workshop} as a on cs.id=a.course join {course_modules} as cm on a.course= cm.course and a.id=cm.instance where cm.module=$workshopmoduleid->id and a.course in($courses)");
}
$asgn = array();
$asgntext = "<table>";
if (!empty($assign)) {
    $asgncount = 0;
    foreach ($assign as $ci => $cj) {
        if ($asgncount < 2) {
            $asgntext .= '<tr>';
            $asgntext .= '<td><a class="dashboardlink" href="'.$CFG->wwwroot.'/mod/assign/view.php?id='.$cj->id.'">'.$cj->name.'</a></td></tr>';
        }
    $asgncount++;
    }
}
if (!empty($quiz)) {
    $asgncount = 0;
    foreach ($quiz as $ci => $cj) {
        if ($asgncount < 3) {
            $asgntext .= '<tr>';
            $asgntext .= '<td><a class="dashboardlink" href="'.$CFG->wwwroot.'/mod/quiz/view.php?id='.$cj->id.'">'.$cj->name.'</a></td></tr>';
        }
    $asgncount++;
    }
}
if (!empty($workshop)) {
    $asgncount = 0;
    foreach ($workshop as $ci => $cj) {
        if ($asgncount < 2) {
            $asgntext .= '<tr>';
            $asgntext .= '<td><a class="dashboardlink" href="'.$CFG->wwwroot.'/mod/workshop/view.php?id='.$cj->id.'">'.$cj->name.'</a></td></tr>';
        }
    $asgncount++;
    }
}
if (($workshop == null) && ($quiz == null) && ($assign== null)) {
    $asgntext .= '<tr><td><td class="black">No Assessments are available</td></tr></table>';
}
$asgntext .= "</table>";
?>
<link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:300' rel='stylesheet' type='text/css'>
<style>
	#region-main{
	float: right !important;	
		
	}
	}
	.black{
		color: #000000 !important;
	}
	
	body {
		font-family: ;
	}
	h1, h2 {
		text-align: center;
		font-weight: normal;
	}
	section {
		margin: ;
		width: 915px !important;
	}
	.class="dashboardlink", .class="dashboardlink":link, .class="dashboardlink":visited {
		color: #000000;
		text-decoration: none;
	}
	.class="dashboardlink":hover {
		text-decoration: underline;
	}
	.sortable {
		margin: auto;
		padding: 0;
		width: 800px;
		-webkit-touch-callout: none;
		-webkit-user-select: none;
		-khtml-user-select: none;
		-moz-user-select: none;
		-ms-user-select: none;
		user-select: none;
	}
	///////////////////////////////////////////
	.sortable.grid {
		overflow: hidden;
		margin:0px;
		width:102%;
		min-height: 450px;
	}
	.widgetli {
		-ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=100)";
		filter: alpha(opacity=100);
		opacity: 1;
		-webkit-transition: all 0.2s ease;
		-moz-transition: all 0.2s ease;
		-o-transition: all 0.2s ease;
		-ms-transition: all 0.2s ease;
		transition: all 0.2s ease;
		list-style: none;
		border-radius: 4px;
		margin: 5px;
		padding: 5px;
		height: 22px;
	}
.widgetli:hover {
		cursor: move;
	}
	.sortable .widgetli {
		border: 1px solid #E4DEDE;
		color: #fff;
		box-shadow: inset 0 1px 1px rgba(255,255,255,0.7);
	}
	.grid .widgetli {
		float: left;
		width: 370px;
		min-height: 250px;
		text-align: left;
		margin:0px;
		padding:0px;
		margin-right: 15px;
        margin-bottom: 10px;
	}
	#item1 .widget-head, #item2 .widget-head, #item3 .widget-head, #item4 .widget-head{
	    float: left;
        width: 100%;        
        border-top-left-radius: 4px;		
		border-top-right-radius:4px;
		moz-top-left-radius:4px;
		moz-top-right-radius:4px;
		-webkit-top-left-radius:4px;
		-webkit-top-right-radius:4px;
        height:16%;
		min-height: 16% !important;
	}
	
	#item1 .widget-head{
	    background-color: #f47936;
	}
	
	#item2 .widget-head{
	    background-color: #0094a0;
	}
	
	#item3 .widget-head, #item4 .widget-head{
	    background-color: #188ac6;
	}
	
	#item4 .widget-head{
	    background-color: #ef3e44;
	}
	.widget-head h3{
	    float: left;
        margin-left: 5px;
        margin-top:2%;
        margin-bottom:2%; font-family: georgia; font-weight: bold; font-size:16px;
	}
	
	.widget-head span{
	    margin-left:5px;
	    margin-top:2%;
	    margin-bottom:2%;
        float: right;
        padding-right: 16px;
	}
	
	.widget-head span a{
	    margin: 1em 0;
        color: #FFF !important;
        font-weight:bold;
	}
	.widget-content table{
		float:left;
	}
	.funcs .widgetli {
		border: 1px solid #eee;
	}
	#features .widgetli {
		background: none;
		border: none;
		color: black;
	}
	h2 span {
		color: #000000;
	}
	.disabled {
		opacity: 0.5;
	}

	.over {
		-webkit-box-shadow: 0 5px 10px rgba(0,0,0,0.2);
		box-shadow: 0 5px 10px rgba(0,0,0,0.2);

		-webkit-transform: scale(1.1);
		-moz-transform: scale(1.1);
		-o-transform: scale(1.1);
		-ms-transform: scale(1.1);
		transform: scale(1.1);
	}

	.moving {
		-webkit-transform: scale(0.9);
		-moz-transform: scale(0.9);
		-o-transform: scale(0.9);
		-ms-transform: scale(0.9);
		transform: scale(0.9);
		-ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=30)";
		filter: alpha(opacity=30);
		opacity: 0.3;
	}

	.drop {
		-webkit-animation: drop 1s ease;
		-moz-animation: drop 1s ease;
		-ms-animation: drop 1s ease;
		-o-animation: drop 1s ease;
		animation: drop 1s ease;
	}
/*
	@keyframes "drop" {
		0%, 100% {
			background: #FF9933;
			color: #fff;
		}
		50% {
			color: #FF9933;
			background: #fff;
		}

	}

	@-moz-keyframes drop {
		0%, 100% {
			background: #FF9933;
			color: #fff;
		}
		50% {
			color: #FF9933;
			background: #fff;
		}

	}

	@-webkit-keyframes "drop" {
		0%, 100% {
			background: #FF9933;
			color: #fff;
		}
		50% {
			color: #FF9933;
			background: #fff;
		}

	}

	@-ms-keyframes "drop" {
		0%, 100% {
			background: #FF9933;
			color: #fff;
		}
		50% {
			color: #FF9933;
			background: #fff;
		}

	}

	@-o-keyframes "drop" {
		0%, 100% {
			background: #FF9933;
			color: #fff;
		}
		50% {
			color: #FF9933;
			background: #fff;
		}

	}*/
	.block_course_overview{
		display: none;	
	}
	.breadcrumb{
		display: table;	
	}	
	.side-post-only #page-content #region-main-box #region-post-box {
    margin-left: -200px; 
}
.content-only #page-content #region-post-box {
    margin-left: -121px;
}
</style>
<section style="margin-left:center; margin-right:auto;">
    <ul class="sortable grid dashboard dashboard-g">
        <li id="item1" class="widgetli">
			<div class="widget-head" style="min-height: 16% !important;">
				<h3>My Modules</h3><span class="right"><a href="<?php if($USER->id == 2){echo $CFG->wwwroot.'/course/index.php';} else {echo $CFG->wwwroot.'/my/index.php';}?>">ALL</a></span>
			</div>
			<div class="widget-content">
				<p><?php echo $coursetext;?></p>
			</div>
		</li>			
		<li id="item2" class="widgetli">
			<div class="widget-head">
				<h3>My Assessments</h3><span class="right"><a href="<?php echo $CFG->wwwroot.'/local/dashboard/moduleactivities.php';?>">ALL</a></span>
			</div>
			<div class="widget-content">
				<p><?php echo $asgntext;?></p>
			</div>
		</li>
		<li id="item3" class="widgetli">
			<div class="widget-head">
				<h3>My Blogs</h3><span class="right"><a href="<?php echo $CFG->wwwroot.'/blog/index.php?courseid=1';?>">ALL</a></span>
			</div>
			<div class="widget-content">
				<p><?php echo $blogtext;?></p>
			</div>
		</li>
		<li id="item4" class="widgetli">
			<div class="widget-head">
				<h3>My Messages</h3><span class="right"><a href="<?php echo $CFG->wwwroot.'/message/index.php';?>">ALL</a></span>
			</div>
			<div class="widget-content">
				<p><?php echo $text;?></p>
			</div>
		</li>
		
		<!--<li id="item5">Item 5</li>
		<li id="item6">Item 6</li>-->
	</ul>
</section>
<script src="zepto.min.js"></script>
<script src="zepto.dragswap.js"></script>
<script>
$(function() {
	$('.sortable').dragswap({
		dropAnimation: true
	});
	$('.funcs').dragswap({
		dropAnimation: false,
		dropComplete: function() {
			var sortArray = $('.funcs').dragswap('toArray');
			$('#arrayResults').html('['+sortArray.join(',')+']');
			var sortJSON = $('.funcs').dragswap('toJSON');
			$('#jsonResults').html(sortJSON);
		}
	});
});
</script>
<?php
echo $OUTPUT->footer();