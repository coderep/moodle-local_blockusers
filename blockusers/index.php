<?php
//This file is part of Moodle - http://moodle.org/.
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
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    local
 * @subpackage blockusers
 * @copyright 2013 Gayatri Venugopal
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * file index.php
 * index page to view block users page.
 */

require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once($CFG->libdir.'/csvlib.class.php');
require_once('changesettings_form.class.php');

session_start();
class users_form extends moodleform {

    public function definition() {
        global $CFG;
        $mform = $this->_form;
        $mform->addElement('filepicker', get_string('filename', 'local_blockusers'), get_string('file'), null,
                   array('maxbytes' => $maxbytes, 'accepted_types' => array('.txt','.csv')));
		$mform->addElement('submit', 'submitbutton', get_string('import', 'local_blockusers'));
    }
	
    function validation($data, $files) {
        return array();
    }
}

function insertIntoTable($users, $from, $to)
{
	global $DB;
	for($i=0; $i<count($users); $i++)
	{	
		$record = new stdClass();
		$record->start_timestamp = $from;
		$record->stop_timestamp = $to;
		$record->username = $users[$i];
		$DB->insert_record(get_string('tablename', 'local_blockusers'), $record); 
	}
}

function deletePastRecords()
{
	global $DB;
	$query = "stop_timestamp < ".time();
	$DB->delete_records_select(get_string('tablename', 'local_blockusers'), $query);
}

$PAGE->set_title(get_string('blockusers', 'local_blockusers'));
$PAGE->set_heading(get_string('blockusers', 'local_blockusers'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('blockusers', 'local_blockusers'));

$mform = new users_form();
if($mform->get_file_content(get_string('filename', 'local_blockusers')) != null) //true if file has been imported
{
	$content = $mform->get_file_content(get_string('filename', 'local_blockusers'));
	//save to moodledata
	$success = $mform->save_file(get_string('filename', 'local_blockusers'), $CFG->dataroot, false);
	$settingsform = new changesettings_form();
	//convert csv string to array
	$usernames = explode(",", $content);
	$index = 0;
	$users = array();
	$users[$index++] = array("","");
	foreach($usernames as $user)
	{
		$users[$index++] = array($index-1,$user);
	}
	//Display table and date-time selectors
	$settingsform->addElements($users);
	$settingsform->display();
}
else if(optional_param_array('blocktill', null, PARAM_TEXT) != null) //true if user clicked "Block Users"
{
	//retrieve parameters passed
	$fromArray = optional_param_array('blockfrom', null, PARAM_TEXT);
	$tillArray = optional_param_array('blocktill', null, PARAM_TEXT);
	$from = make_timestamp($fromArray['year'], $fromArray['month'], $fromArray['day'], $fromArray['hour'], $fromArray['minute']);
	$to = make_timestamp($tillArray['year'], $tillArray['month'], $tillArray['day'], $tillArray['hour'], $tillArray['minute']);
	
	if($to > $from)
	{
		//save to database
		insertIntoTable($_SESSION['content'],$from,$to);
		deletePastRecords();
		echo "<font color=\"green\">The specified users have been blocked from ".$fromArray['day']."-".$fromArray['month']."-".$fromArray['year']." ".$fromArray['hour'].":".$fromArray['minute']." to ".$tillArray['day']."-".$tillArray['month']."-".$tillArray['year']." ".$tillArray['hour'].":".$tillArray['minute']."</font>";
		$mform->display();
	}
	else
	{
		$usernames = $_SESSION['content'];
		$settingsform = new changesettings_form();
		$users = array();
		$users[0] = array('','');
		for($i=0; $i<count($usernames); $i++)
		{
			$users[$i+1] = array($i+1,$usernames[$i]);
		}
		//Display table and date-time selectors		
		$settingsform->addElements($users);
		$settingsform->display();
		echo "<center><font color=\"red\">Please select a valid duration!</font></center>";
	}
}
else
{
	$mform->display();
}
echo $OUTPUT->footer();

?>