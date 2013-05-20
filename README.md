moodle-local_blockusers
=======================
A plugin that allows the site administrator to block multiple users for a certain period by uploading a csv file containing the usernames of the users to be blocked.\n
Download zip from: https://github.com/innovg/moodle-local_blockusers/tree/master/blockusers\n
Unzip into the 'local' subfolder of your Moodle install.\n
Rename the new folder to blockusers.\n
You are required to add the following lines to moodle/login/index.php, for the plugin blocking mechanism to work:\n
$username = $frm->username;
  global $DB;
	$curTime = time();
	$queryString = "SELECT * FROM mdl_blockusers WHERE start_timestamp <= ".$curTime." && ".$curTime." <= stop_timestamp && username like '".$username."'";
	if($DB->record_exists_sql($queryString))
	{
		$errormsg = "You are not permitted to login";
		$errorcode = 3;
	}
	
\nVisit http://yoursite.com/admin to finish the installation.

\nThis plugin can be used in situations where you do not want a set of students to take an exam. You can insert their usernames in a csv file,
\nand upload that file. Set the time for which they are to be blocked.
\nE.g. of csv file contents: Ron.Smith,JAne.Jacob,John.Doe,Jane.Doe
