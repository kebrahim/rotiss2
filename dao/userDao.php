<?php

require_once 'commonDao.php';
CommonDao::requireFileIn('/../entity/', 'user.php');

class UserDao {
  /**
   * Returns all of the owners for the specified team ID.
   */
  static function getUsersByTeamId($teamId) {
    CommonDao::connectToDb();
    $query = "select user_id, username, password, first_name, last_name, team_id, is_admin
    	      from user
              where team_id = $teamId";
    $db_users = mysql_query($query);

    $users = array();
    while ($db_user = mysql_fetch_row($db_users)) {
      $users[] = new User(
          $db_user[0], $db_user[1], $db_user[2], $db_user[3], $db_user[4],
          $db_user[5], $db_user[6]);
    }
    return $users;
  }
}