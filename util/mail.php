<?php

require_once 'commonUtil.php';
CommonUtil::requireFileIn('/../dao/', 'changelogDao.php');
CommonUtil::requireFileIn('/../dao/', 'userDao.php');

/**
 * Handles automatic e-mailing functionality
 */
class MailUtil {

  /**
   * Sends an email to the users of the team(s) affected by the specified change, and cc's the
   * admin users.
   */
  // TODO new functions to handle an array of changes for one team and for multiple teams
  public static function sendChangeEmail(Changelog $change) {
    $subject = "St. Pete's Rotiss Transaction - " . $change->getType() . " for " .
        $change->getTeam()->getName();

    // Format change into email message
    $message = "<table border>
                  <thead><tr>
                    <th>Date/Time</th>
                    <th>User</th>
                    <th>Type</th>
                    <th>Team</th>
                    <th>Details</th>
                  </tr></thead>
                  <tr>
                    <td>" . $change->getTimestamp() . "</td>
                    <td>" . $change->getUser()->getFullName() . "</td>
                    <td>" . $change->getType() . "</td>
                    <td>" . $change->getTeam()->getAbbreviation() . "</td>
                    <td>" . $change->getEmailDetails() . "</td>
                  </tr>
                </table>";

    // TODO send mail to secondary user [if exists]

    MailUtil::sendMailToUsers($subject, $message, UserDao::getUsersByTeamId($change->getTeamId()),
        UserDao::getAdminUsers());
  }

  /**
   * Sends the specified message w/ the specified subject to all of the users.
   */
  private static function sendMailToAllUsers($subject, $message) {
    MailUtil::sendMailToUsers($subject, $message, UserDao::getAllUsers(), null);
  }

  /**
   * Sends the specified message w/ the specified subject to the specified set of users.
   */
  private static function sendMailToUsers($subject, $message, $toUsers, $ccUsers) {
  	$to = MailUtil::getEmailAddresses($toUsers);

  	// set headers
  	$headers  = "From: St. Pete's Rotiss<noreply@rotiss.com>\r\n";
  	$headers .= 'MIME-Version: 1.0' . "\n";
  	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

  	if ($ccUsers != null) {
  	  $headers .= "CC: " . MailUtil::getEmailAddresses($ccUsers) . "\r\n";
  	}

  	MailUtil::displayMail($to, $subject, $message, $headers);
  	mail($to, $subject, $message, $headers);
  }

  /**
   * Returns a comma-separated list of email addresses from the array of users.
   */
  private static function getEmailAddresses($users) {
  	$emails = "";
  	$firstEmail = true;
  	foreach ($users as $user) {
  	  if ($firstEmail) {
  	  	$firstEmail = false;
  	  } else {
  	  	$emails .= ",";
  	  }
  	  $emails .= $user->getEmail();
  	}
  	return $emails;
  }

  /**
   * Utility method to display what will be mailed.
   */
  private static function displayMail($to, $subject, $message, $headers) {
    echo "<h1>To</h1>
          <p>$to</p>
          <h1>Subject</h1>
          <p>$subject</p>
          <h1>Message</h1>
          <p>$message</p>
          <h1>Headers</h1>
          <p>$headers</p>";
  }
}

// Utility method
if (array_key_exists("changeid", $_REQUEST)) {
  MailUtil::sendChangeEmail(ChangelogDao::getChangeById($_REQUEST["changeid"]));
}

?>
