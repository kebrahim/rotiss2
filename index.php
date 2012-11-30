<!DOCTYPE html>
<html>
<head>
<title>St Pete's Rotiss</title>
<link href='css/bootstrap.css' rel='stylesheet' type='text/css'>
<link href='css/stpetes.css' rel='stylesheet' type='text/css'>
</head>

<body>

<?php
  require_once 'dao/userDao.php';
  require_once 'util/layout.php';
  require_once 'util/sessions.php';

  LayoutUtil::displayHeader();
  echo "<div class='row-fluid headrow'>";

  // logo
  echo "<div class='span12 center'>
          <img src='img/rotiss2.jpg' width='360' />
          <hr/>";

  if (isset($_POST['login'])) {
    $user = UserDao::getUserByUsernamePassword($_POST["username"], $_POST["password"]);
    if ($user == null) {
      echo "<div class=\"alert alert-error\">
              <button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
              <strong>Sorry!</strong> That is an incorrect username or password. Please try again!
            </div>";
    } else {
      // add user information to session
      // TODO handle 'remember me' checkbox
      // TODO handle login and redirect to URL
      SessionUtil::loginAndRedirect($user);
    }
  }

  // sign-in
  echo "  <form class=\"form-signin\" action='index.php' method='post'>
            <h2 class=\"form-signin-heading\">Please sign in</h2>
            <input type=\"text\" name='username' class=\"input-block-level\"
                   placeholder=\"Username\" required>
            <input type=\"password\" name='password' class=\"input-block-level\"
                   placeholder=\"Password\" required>
            <label class=\"checkbox\">
              <input type=\"checkbox\" value=\"remember-me\"> Remember me
            </label>
            <button class=\"btn btn-large btn-primary\" type=\"submit\"
                    name='login'>Sign in</button>
          </form>
        </div>";

  echo "</div>"; // row-fluid

  // footer
  LayoutUtil::displayFooter();
?>

</body>
</html>
