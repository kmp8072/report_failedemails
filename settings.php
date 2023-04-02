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
 * Settings and links
 *
 * @package   report_failedemails
 * @copyright 2023 Krishna Mohan Prasad <kmp.moodle@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;


$ADMIN->add('reports', new admin_category('report_failedemails', get_string('pluginname', 'report_failedemails')));

$ADMIN->add('report_failedemails', new admin_externalpage('reportfailedemails', get_string('failed_emails_report', 'report_failedemails'),
        $CFG->wwwroot."/report/failedemails/index.php", 'report/failedemails:view'));

$settingspage = new admin_settingpage('settings_report_failedemails', new lang_string('failed_emails_settings', 'report_failedemails'));

$settingspage->add(new admin_setting_configtext(
        'report_failedemails/itemsperpage',
        new lang_string('failed_mails_per_page', 'report_failedemails'),
        new lang_string('failed_mails_per_page_desc', 'report_failedemails'),
        10, PARAM_INT
));

$ADMIN->add('report_failedemails', $settingspage);


// No report settings.
$settings = null;
