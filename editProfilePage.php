<?php
  require_once 'util/sessions.php';
  SessionUtil::checkUserIsLoggedIn();
?>

<html>
<head>
<title>Rotiss.com - Edit profile</title>
<link href='css/style.css' rel='stylesheet' type='text/css'>
</head>

<body>
<?php
  require_once 'util/navigation.php';

  // Display header.
  NavigationUtil::printHeader(true, true, 0);

  $user = SessionUtil::getLoggedInUser();
  echo "<div class='bodyleft'>";

  /**
   * Validates the user fields specified in POST, and updates specified user with those fields if
   * everything is valid.
   */
  function validateUser(User &$user) {
    // first name & last name must be alphabetic
    $firstName = $_POST['firstName'];
    if (ctype_alpha($firstName)) {
      $user->setFirstName($firstName);
    } else {
      echo "<div class='error_msg_pad'>Invalid first name [must be alphabetic chars only]: "
            . $firstName . "</div>";
      return false;
    }
    $lastName = $_POST['lastName'];
    if (ctype_alpha($lastName)) {
      $user->setLastName($lastName);
    } else {
      echo "<div class='error_msg_pad'>Invalid last name [must be alphabetic chars only]: " .
            $lastName . "</div>";
      return false;
    }

    // email is validated client-side
    $user->setEmail($_POST['email']);

    // username is valid if it's alphanumeric
    $username = $_POST['username'];
    if (ctype_alnum($username)) {
      $user->setUsername($username);
    } else {
      echo "<div class='error_msg_pad'>Invalid username [must be alpha-numeric chars only]: "
            . $username . "</div>";
      return false;
    }

    // password
    if ((strlen($_POST['oldpass']) > 0) || (strlen($_POST['newpass']) > 0) ||
        (strlen($_POST['confnewpass']) > 0)) {
      if (validatePassword($user->getPassword())) {
        $user->setPassword($_POST['newpass']);
        echo "<div class='alert_msg_pad_top'>Password successfully changed!</div>";
      } else {
        return false;
      }
    }

    return true;
  }

  /**
   * Validates the password fields specified in POST and returns false if an error occurred.
   */
  function validatePassword($oldPassword) {
    if ($_POST['oldpass'] != $oldPassword) {
      echo "<div class='error_msg_pad'>Old password is incorrect!</div>";
      return false;
    }
    if ((strlen($_POST['newpass']) == 0) || !ctype_alnum($_POST['newpass'])) {
      echo "<div class='error_msg_pad'>New password is invalid; must be alphanumeric!</div>";
      return false;
    }
    if ($_POST['newpass'] != $_POST['confnewpass']) {
      echo "<div class='error_msg_pad'>Passwords do not match!</div>";
      return false;
    }
    return true;
  }

  if (isset($_POST['update'])) {
    if (validateUser($user)) {
      // update user
      UserDao::updateUser($user);
      echo "<div class='alert_msg_pad_top'>User successfully updated!</div>";
    }
  }

  echo "<h1>Edit " . $user->getFullName() . "'s Profile</h1>";
  echo "<form action='editProfilePage.php' method=post>";
  echo "<table>";

  // ID
  echo "<tr><td><strong>User Id:</strong></td><td>" . $user->getId() . "</td></tr>";

  // Name
  echo "<tr><td><label for='firstName'>Name:</label></td>
        <td><input type=text id='firstName' name='firstName' required placeholder='First Name'
            value='" . $user->getFirstName() . "' maxlength='20' size='25'> ";
  echo "<input type=text name='lastName' id='lastName' required placeholder='Last Name' value='" .
         $user->getLastName() . "' maxlength='20' size='25'></td></tr>";

  // Email
  echo "<tr><td><label for='email'>Email:</label></td>
            <td><input type=email id='email' name='email' required
                 value='" . $user->getEmail() . "' maxlength='45' size='25'></td></tr>";

  // Username
  echo "<tr><td><label for='username'>Username:</label></td>
         <td><input type=text id='username' name='username' required " .
             "value='" . $user->getUsername() . "' maxlength='20' size='25'></td>
        </tr>
      </table><br/>";

  // Password
  echo "<fieldset>
        <legend>Change Password</legend>
        <label for='oldpass' >Old password:</label><br/>
        <input type='password' name='oldpass' id='oldpass' maxlength='20' size='25' /><br/><br/>
        <label for='newpass' >New password:</label><br/>
        <input type='password' name='newpass' id='newpass' maxlength='20' size='25' /><br/><br/>
        <label for='confnewpass' >Confirm new password:</label><br/>
        <input type='password' name='confnewpass' id='confnewpass' maxlength='20' size='25' />
        </fieldset>";

  echo "<br/>";

  // Buttons
  echo "<input class='button' type=submit name='update' value='Update settings'>";

  echo "</form></div>";

  // Display footer.
  NavigationUtil::printFooter();
?>