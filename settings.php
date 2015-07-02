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

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
	$moderator = get_admin();
	$site = get_site();
	$name = 'Add/Edit Activity Log';
	$visiblename = 'Add/Edit dashboard Log';
	$url = '/local/dashboard/index.php';
	$req_capability = 'moodle/site:config';
	$hidden = false;
	$context = null;
	$settings = new admin_externalpage($name, $visiblename, $url, $req_capability, $hidden, $context);
	$ADMIN->add('localplugins', $settings);
}
