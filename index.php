<?php session_start(); ?>
<html>
<head>
<title>Rotiss.com</title>
<link href='css/style.css' rel='stylesheet' type='text/css'>
</head>

<style>
#logo {float:none; text-align:center;}
</style>
<body>

<?php
  require_once 'dao/userDao.php';
  require_once 'util/navigation.php';
  require_once 'util/sessions.php';

  NavigationUtil::printHeader(false, true, 0);
  echo "<div class='bodycenter'>";

  if (isset($_POST['login'])) {
    $user = UserDao::getUserByUsernamePassword($_POST["username"], $_POST["password"]);
    if ($user == null) {
      echo "<div class='error_msg_pad'>Invalid username or password; please try again.<br/></div>";
    } else {
      // add user information to session
      SessionUtil::loginAndRedirect($user);
    }
  }

?>
  <div id='logininfo'>
    <form action='index.php' method=post>
      <fieldset >
        <legend>Sign in</legend>
        <label for='username' >Username:</label><br/>
        <input type='text' name='username' id='username'  maxlength="20" size="25" required /><br/><br/>
        <label for='password' >Password:</label><br/>
        <input type='password' name='password' id='password' maxlength="20" size="25" required /><br/><br/>
        <div id='signinbutton'>
          <input type='submit' name='login' value='Sign in' />
        </div>
      </fieldset><br/>
      <a href='loginHelpPage.php'>Can't access your account?</a><br/>
    </form>
  </div></div>

<?php
  // footer
  NavigationUtil::printFooter();
?>

</body>
</html>