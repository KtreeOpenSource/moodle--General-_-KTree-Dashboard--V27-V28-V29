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

define('MY_PAGE_PUBLIC', 0);
define('MY_PAGE_PRIVATE', 1);
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once("$CFG->libdir/blocklib.php");
global $DB, $CFG;
function dashboard_add_instance() {
    return true;
}
function local_dashboard_extends_navigation(global_navigation $navigation) {
    $perfornamce_url='/local/dashboard/index.php';
    if (isloggedin()) {
        $nodeForm = $navigation->add('Dashboard', $perfornamce_url);
    }
}
