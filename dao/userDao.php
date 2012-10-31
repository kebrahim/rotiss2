<?php

require_once 'commonDao.php';
CommonDao::requireFileIn('/../entity/', 'user.php');

class UserDao {
  /**
   * Returns all of the owners for the specified team ID.
   */
  public static function getUsersByTeamId($teamId) {
    CommonDao::connectToDb();
    $query = "select u.*
    	      from user u
              where u.team_id = $teamId";
    return UserDao::createUsersFromQuery($query);
  }

  /**
   * Returns the user with the specified username and password.
   */
  public static function getUserByUsernamePassword($username, $password) {
    CommonDao::connectToDb();
    $query = "select u.*
    	      from user u
              where u.username = '" . $username . "'
              and u.password = '" . $password . "'";
    return UserDao::createUserFromQuery($query);
  }

  /**
   * Returns the user with the specified email address.
   */
  public static function getUserByEmailAddress($email) {
    CommonDao::connectToDb();
    $query = "select u.*
    	      from user u
              where u.email = '" . $email . "'";
    return UserDao::createUserFromQuery($query);
  }

  private static function createUserFromQuery($query) {
    $userArray = UserDao::createUsersFromQuery($query);
    if (count($userArray) == 1) {
      return $userArray[0];
    }
    return null;
  }

  private static function createUsersFromQuery($query) {
    $res = mysql_query($query);
    $usersDb = array();
    if (mysql_)
    while($userDb = mysql_fetch_assoc($res)) {
      $usersDb[] = new User($userDb["user_id"], $userDb["username"], $userDb["password"],
          $userDb["first_name"], $userDb["last_name"], $userDb["email"], $userDb["team_id"],
          $userDb["is_admin"], $userDb["is_super_admin"]);
    }
    return $usersDb;
  }
}