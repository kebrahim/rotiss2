<?php

require_once 'commonDao.php';
CommonDao::requireFileIn('/../entity/', 'user.php');

class UserDao {

  /**
   * Returns the user with the specified id
   */
  public static function getUserById($userId) {
    return UserDao::createUserFromQuery(
        "select u.*
         from user u
         where u.user_id = " . $userId);
  }

  /**
   * Returns all of the users for the specified team ID.
   */
  public static function getUsersByTeamId($teamId) {
    return UserDao::createUsersFromQuery(
        "select u.*
    	 from user u
         where u.team_id = $teamId
         and u.is_demo = '0'");
  }

  /**
   * Returns all of the users associated with any of the specified team IDs.
   */
  public static function getUsersByTeamIds($teamIds) {
    $query = "select u.*
      	      from user u
              where u.team_id in (";
    $first = true;
    foreach ($teamIds as $teamId) {
      if ($first) {
        $first = false;
      } else {
        $query .= ",";
      }
      $query .= $teamId;
    }
    $query .= ") and u.is_demo = '0'";
    return UserDao::createUsersFromQuery($query);
  }

  /**
   * Returns the user with the specified username and password.
   */
  public static function getUserByUsernamePassword($username, $password) {
    return UserDao::createUserFromQuery(
        "select u.*
    	 from user u
         where u.username = '" . $username . "'
         and u.password = '" . $password . "'");
  }

  /**
   * Returns the user with the specified email address.
   */
  public static function getUserByEmailAddress($email) {
    return UserDao::createUserFromQuery(
        "select u.*
    	 from user u
         where u.email = '" . $email . "'");
  }

  /**
   * Returns all of the non-demo users.
   */
  public static function getAllUsers() {
    return UserDao::createUsersFromQuery(
        "select u.*
        from user u
        where u.is_demo = '0'");
  }

  /**
   * Returns all of the admin users.
   */
  public static function getAdminUsers() {
    return UserDao::createUsersFromQuery(
        "select u.*
        from user u
        where u.is_admin = '1'");
  }

  private static function createUserFromQuery($query) {
    $userArray = UserDao::createUsersFromQuery($query);
    if (count($userArray) == 1) {
      return $userArray[0];
    }
    return null;
  }

  private static function createUsersFromQuery($query) {
    CommonDao::connectToDb();
    $res = mysql_query($query);
    $usersDb = array();
    while($userDb = mysql_fetch_assoc($res)) {
      $usersDb[] = UserDao::populateUser($userDb);
    }
    return $usersDb;
  }

  private static function populateUser($userDb) {
    return new User($userDb["user_id"], $userDb["username"], $userDb["password"],
        $userDb["first_name"], $userDb["last_name"], $userDb["email"], $userDb["team_id"],
        $userDb["is_admin"], $userDb["is_super_admin"], $userDb["is_demo"]);
  }

  /**
   * Updates the specified user in the 'user' table.
   */
  public static function updateUser(User $user) {
    CommonDao::connectToDb();
    $query = "update user set username = '" . $user->getUsername() . "',
                              password = '" . $user->getPassword() . "',
                              first_name = '" . $user->getFirstName() . "',
                              last_name = '" . $user->getLastName() . "',
                              email = '" . $user->getEmail() . "'
                          where user_id = " . $user->getId();
    $result = mysql_query($query) or die('Invalid query: ' . mysql_error());
  }
}