<?php
  require_once 'util/sessions.php';
  SessionUtil::logoutUserIfNotLoggedIn("editProfilePage.php");
?>

<!DOCTYPE html>
<html>

<?php
  require_once 'util/layout.php';
  LayoutUtil::displayHeadTag("Edit profile", true);
?>

<body>

<?php
    // Nav bar
  LayoutUtil::displayNavBar(true, LayoutUtil::EDIT_PROFILE_BUTTON);

  $user = SessionUtil::getLoggedInUser();
  echo "<div class='row-fluid'>
          <div class='span12 center'>";

  /**
   * Displays the specified error message.
   */
  function displayError($errorMsg) {
    echo "<br/><div class='alert alert-error'><strong>Error:</strong> $errorMsg</div>";
  }

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
      displayError("Invalid first name [must be alphabetic chars only]: ". $firstName);
      return false;
    }
    $lastName = $_POST['lastName'];
    if (ctype_alpha($lastName)) {
      $user->setLastName($lastName);
    } else {
      displayError("Invalid last name [must be alphabetic chars only]: " . $lastName);
      return false;
    }

    // email is validated client-side
    $user->setEmail($_POST['email']);

    // username is valid if it's alphanumeric
    $username = $_POST['username'];
    if (ctype_alnum($username)) {
      $user->setUsername($username);
    } else {
      displayError("Invalid username [must be alpha-numeric chars only]: " . $username);
      return false;
    }

    // password
    if ((strlen($_POST['oldpass']) > 0) || (strlen($_POST['newpass']) > 0) ||
        (strlen($_POST['confnewpass']) > 0)) {
      if (validatePassword($user->getPassword())) {
        $user->setPassword($_POST['newpass']);
        echo "<br/><div class='alert alert-success'>Password successfully changed!</div>";
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
      displayError("Old password is incorrect!");
      return false;
    }
    if ((strlen($_POST['newpass']) == 0) || !ctype_alnum($_POST['newpass'])) {
      displayError("New password is invalid; must be alphanumeric!");
      return false;
    }
    if ($_POST['newpass'] != $_POST['confnewpass']) {
      displayError("Passwords do not match!");
      return false;
    }
    return true;
  }

  if (isset($_POST['update'])) {
    if (validateUser($user)) {
      // update user
      UserDao::updateUser($user);
      echo "<br/><div class='alert alert-success'>User successfully updated!</div>";
    }
  }

  echo "    <h3>Edit " . $user->getFullName() . "'s Profile</h3>
          </div>
        </div>"; // row-fluid

  echo "<div class='row-fluid'>
          <div class='span12'><br/>";
  echo "<form action='editProfilePage.php' method=post>";
  echo "   <div class='row-fluid'>
              <div class='span7'>";
  echo "<fieldset>
        <legend>User Settings</legend>
  <table class='table vertmiddle table-striped table-condensed table-bordered'>";

  // ID
  echo "<tr><td><label>User Id:</label></td><td>" . $user->getId() . "</td></tr>";

  // Name
  echo "<tr><td><label for='firstName'>Name:</label></td>
        <td><input type=text id='firstName' name='firstName' required placeholder='First Name'
            value='" . $user->getFirstName() . "' maxlength='20' class='input-medium'> ";
  echo "<input type=text name='lastName' id='lastName' required placeholder='Last Name' value='" .
         $user->getLastName() . "' maxlength='20' class='input-medium'></td></tr>";

  // Email
  echo "<tr class='tdselect'><td><label for='email'>Email:</label></td>
            <td><input type=email id='email' name='email' required
                 value='" . $user->getEmail() . "' maxlength='45' class='input-large'></td></tr>";

  // Username
  echo "<tr><td><label for='username'>Username:</label></td>
         <td><input type=text id='username' name='username' required " .
             "value='" . $user->getUsername() . "' maxlength='20' class='input-large'></td>
        </tr>
      </table></fieldset>
      </div>"; // span7

  // Password
  echo "<div class='span5'>";
  echo "<fieldset>
        <legend>Change Password</legend>
        <table class='table vertmiddle table-striped table-condensed table-bordered'>
        <tr>
          <td><label for='oldpass'>Old password:</label></td>
          <td><input type='password' name='oldpass' id='oldpass' maxlength='20' class='input-medium'/></td>
        </tr>
        <tr>
          <td><label for='newpass'>New password:</label></td>
          <td><input type='password' name='newpass' id='newpass' maxlength='20'  class='input-medium'/></td>
        </tr>
        <tr>
          <td><label for='confnewpass'>Confirm password:</label></td>
          <td><input type='password' name='confnewpass' id='confnewpass' maxlength='20'  class='input-medium'/></td>
        </tr>
        </table>
        </fieldset><br/>";

  echo "</div>"; // span5
  echo "</div>"; // row-fluid

  // Buttons
  echo "<p class='center'>
          <button class=\"btn btn-primary\" name='update' type=\"submit\">Update settings</button>
          &nbsp&nbsp<button class=\"btn\" name='cancel' type=\"submit\">Cancel</button>
        </p>";

  echo "</form>";
  echo "</div>"; // span12
  echo "</div>"; // row-fluid

  // Display footer.
  LayoutUtil::displayFooter();
?>