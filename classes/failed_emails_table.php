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
 * Sql table implementation for report_failedemails.
 *
 * @package   report_failedemails
 * @copyright 2023 Krishna Mohan Prasad <kmp.moodle@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_failedemails;

defined('MOODLE_INTERNAL') || die;
use moodle_url;
use table_sql;
use stdClass;

require_once($CFG->libdir . "/tablelib.php");

/**
 * Table failed_emails class for displaying email failures.
 */
class failed_emails_table extends table_sql {

    /** @var string download parameter name */
    protected $downloadparamname = 'download';

    /** @var integer to count current row no */
    private $scounter = 1;

    /**
     * Sets up the failed_emails_table parameters
     * @param string $uniqueid     unique id of table
     * @param string|moodle_url $baseurl      base url of the table
     */
    public function __construct($uniqueid, $baseurl) {
        parent::__construct($uniqueid);
        $this->define_baseurl($baseurl);

        $headcols = [
            's_no' => 'S.No.',
            'affected_user' => get_string('affected_user', 'report_failedemails'),
            'subject' => get_string('email_subject', 'report_failedemails'),
            'message' => get_string('email_message', 'report_failedemails'),
            'timecreated' => get_string('date'),
        ];

        $this->define_columns(array_keys($headcols));
        $this->define_headers(array_values($headcols));

        // Allow pagination.
        $this->pageable(true);
        // Allow downloading.
        $name = format_string(get_string('failed_emails_report', 'report_failedemails'));
        $this->is_downloadable(true);
        $this->is_downloading(
            optional_param($this->downloadparamname, 0, PARAM_ALPHA),
            $name,
            get_string('pluginname', 'report_failedemails')
        );

        // Allow sorting.
        $this->sortable(true);
        $this->no_sorting('s_no');
        $this->no_sorting('subject');
        $this->no_sorting('message');

        list($fields, $from, $where, $whereparams) = $this->get_sql_fragments();

        list($countsql, $countsqlparams) = $this->get_count_sql();

        $this->set_sql($fields, $from, $where, $whereparams);

        $this->set_count_sql($countsql, $countsqlparams);
    }

    /**
     * Generate serial no column
     * @param  stdClass $row row data object
     * @return int      returns row no
     */
    public function col_s_no($row): int {
        $count = ($this->currpage * $this->pagesize) + $this->scounter;
        $this->scounter = $this->scounter + 1;
        return $count;
    }

    /**
     * Generates affected user column
     * @param  stdClass $row row data object
     * @return string|action_link user profile name or link
     */
    public function col_affected_user($row) {
        global $OUTPUT;
        $affecteduser = \core_user::get_user($row->relateduserid);
        $userfullname = $row->affected_user;
        if (!$this->is_downloading()) {
            if (user_can_view_profile($affecteduser)) {
                $url = new moodle_url('/user/profile.php', ['id' => $row->relateduserid]);
                $profilelink = $OUTPUT->action_link($url, $userfullname, null, ['target' => '_blank']);
                return $profilelink;
            }
        }

        return $userfullname;
    }

    /**
     * Generates subject column
     * @param  stdClass $row row data object
     * @return string mail subject
     */
    public function col_subject($row) {
        $otherinfo = $row->other;
        $otherinfoarr = json_decode($otherinfo);

        return $otherinfoarr->subject;
    }

    /**
     * Generated message colum
     * @param  stdClass $row row data object
     * @return string mail message
     */
    public function col_message($row) {
        $otherinfo = $row->other;
        $otherinfoarr = json_decode($otherinfo);

        return $otherinfoarr->message;
    }

    /**
     * Generates timecreated column
     * @param  stdClass $row row data object
     * @return string the formatted date/time.
     */
    public function col_timecreated($row) {
        return userdate($row->timecreated);
    }

    private function get_sql_fragments() {
        global $DB;

        list($filtercondition, $filterparams) = $this->make_filter_condition_and_params($filterparams);

        $fields = 'lssl.id, lssl.relateduserid, lssl.other, lssl.timecreated,'. $DB->sql_concat('u.firstname', "'  '", 'u.lastname') .'AS affected_user';
        $from = '{logstore_standard_log} AS lssl
        JOIN {user} u ON u.id = lssl.relateduserid';
        return [$fields, $from, $filtercondition, $filterparams];
    }

    private function get_count_sql() {
        list($filtercondition, $filterparams) = $this->make_filter_condition_and_params($filterparams);
        $fields = 'SELECT COUNT(lssl.id) ';
        $from = ' FROM {logstore_standard_log} lssl
        JOIN {user} u ON u.id = lssl.relateduserid ';
        return  [$fields. $from. " WHERE ".$filtercondition, $filterparams];
    }

    /**
     * To make a filter for events of email failure
     * @return array containing where condtion and parameters
     */
    private function make_filter_condition_and_params() {
        global $DB;
        $params = [];
        $where = $DB->sql_equal('eventname', ':eventname');
        $params['eventname'] = '\core\event\email_failed';
        return [$where, $params];
    }

    /**
     * Generates total count of failed emails
     * @return int count of failed emails
     */
    private function get_total_count() {
        global $DB;
        list($countsql, $countsqlparams) = $this->get_count_sql();
        $totalcount = $DB->count_records_sql($countsql, $countsqlparams);
        return $totalcount;
    }

    /**
     * [download description]
     * @return [type] [description]
     */
    public function download() {
        \core\session\manager::write_close();
        $this->out($this->get_total_count(), false);
        exit;
    }
}
