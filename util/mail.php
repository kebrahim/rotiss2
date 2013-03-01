<?php

require_once 'commonUtil.php';
CommonUtil::requireFileIn('/../dao/', 'changelogDao.php');
CommonUtil::requireFileIn('/../dao/', 'teamDao.php');
CommonUtil::requireFileIn('/../dao/', 'userDao.php');

/**
 * Handles automatic e-mailing functionality
 */
class MailUtil {

  /**
   * Sends an email to the users of the team(s) affected by the specified change, and cc's the
   * admin users.
   */
  public static function sendChangeEmail(Changelog $change) {
    $subject = "St. Pete's Rotiss Transaction Report - " . $change->getType() . " for " .
        $change->getTeam()->getAbbreviation();

    // Format change into email message
    $message = "<table border>" .
               MailUtil::getTableHeader() .
               MailUtil::getTableRow($change) .
               "</table>";

    MailUtil::sendMailToUsers($subject, $message, UserDao::getUsersByTeamId($change->getTeamId()),
        UserDao::getAdminUsers());
  }

  /**
   * Sends an email to the users of the specified team, including details of all of the specified
   * changes, and cc's the admin users.
   */
  public static function sendChangesEmailToTeam($changes, Team $team) {
    $subject = "St. Pete's Rotiss Transaction Report - Contracts for " . $team->getAbbreviation();
    $message = "<table border>" . MailUtil::getTableHeader();

    $teamIds = array();
    $teamIds[] = $team->getId();
    foreach ($changes as $change) {
      $message .= MailUtil::getTableRow($change);

      // if secondary team is populated, also email them
      if ($change->getSecondaryTeam() != null) {
        $teamIds[] = $change->getSecondaryTeamId();
      }
    }
    $message .= "</table>";
    MailUtil::sendMailToUsers($subject, $message,
        UserDao::getUsersByTeamIds(array_unique($teamIds)), UserDao::getAdminUsers());
  }

  /**
   * Sends an email to the users of the specified teams, including details of the trade that
   * occurred between the two teams.
   */
  public static function sendTradeEmail($changes, Team $team1, Team $team2) {
    $subject = "St. Pete's Rotiss Transaction Report - Trade between " . $team1->getAbbreviation() .
        " and " . $team2->getAbbreviation();

    $message = "<table border>" .
        MailUtil::getTableHeader() .
        MailUtil::getTableRowForTrade($changes[0], true, false) .   // change1
        MailUtil::getTableRowForTrade($changes[1], false, true) .   // change2
        "</table>";

    MailUtil::sendMailToUsers($subject, $message,
        array_merge(UserDao::getUsersByTeamId($team1->getId()),
            UserDao::getUsersByTeamId($team2->getId())),
        UserDao::getAdminUsers());
  }

  private static function getTableHeader() {
    return "<thead><tr>
              <th>Date/Time</th>
              <th>User</th>
              <th>Type</th>
              <th>Team</th>
              <th>Details</th>
            </tr></thead>";
  }

  private static function getTableRow($change) {
    return MailUtil::getTableRowForTrade($change, false, false);
  }

  private static function getTableRowForTrade($change, $isFirstTrade, $isSecondTrade) {
    $message =  "<tr>";
    if (!$isSecondTrade) {
      $message .=
          "<td rowspan=" . ($isFirstTrade ? "2" : "1") . ">" . $change->getTimestamp() . "</td>
           <td rowspan=" . ($isFirstTrade ? "2" : "1") . ">" . $change->getUser()->getFullName() .
           "</td>
           <td rowspan=" . ($isFirstTrade ? "2" : "1") . ">" . $change->getType() . "</td>";
    }
    $message .= "<td>" . $change->getTeam()->getAbbreviation() . "</td>
                 <td>" . $change->getEmailDetails() . "</td>
                 </tr>";
    return $message;
  }

  /**
   * Sends an email to all users with the specified keeper results for the specified year.
   */
  public static function sendKeepersEmail($keeperMsg, $year) {
    MailUtil::sendMailToAllUsers("Keeper Results $year", $keeperMsg);
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

  	// TODO remove this
  	// MailUtil::displayMail($to, $subject, $message, $headers);
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

// Utility methods
if (array_key_exists("changeid", $_REQUEST)) {
  MailUtil::sendChangeEmail(ChangelogDao::getChangeById($_REQUEST["changeid"]));
} else if (array_key_exists("teamid", $_REQUEST)) {
  MailUtil::sendChangesEmailToTeam(ChangelogDao::getChangesByTeam($_REQUEST["teamid"]),
      TeamDao::getTeamById($_REQUEST["teamid"]));
} else if (array_key_exists("tradeid", $_REQUEST)) {
  $changes = ChangelogDao::getChangesByChangeId($_REQUEST["tradeid"]);
  MailUtil::sendTradeEmail($changes, $changes[0]->getTeam(), $changes[0]->getSecondaryTeam());
}

?>
