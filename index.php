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
 * Failed emails report
 *
 * @package   report_failedemails
 * @copyright 2023 Krishna Mohan Prasad <kmp.moodle@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use report_failedemails\failed_emails_table;
require('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('reportfailedemails', '', null, '', array('pagelayout' => 'report'));

$sqlbaseurl = $PAGE->url;

$sqltable = new failed_emails_table('all_lps_table', $sqlbaseurl);

$itemsperpage = get_config('report_failedemails', 'itemsperpage');

$itemsperpage = $itemsperpage ? $itemsperpage : 10;

if (!$sqltable->is_downloading()) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('pluginname', 'report_failedemails'));
}

$sqltable->out($itemsperpage, true);

if (!$sqltable->is_downloading()) {
    echo $OUTPUT->footer();
}
