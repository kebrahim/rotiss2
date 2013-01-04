<?php
  require_once 'dao/userDao.php';
  require_once 'util/sessions.php';

  $continueUrl = isset($_REQUEST['continue']) ? $_REQUEST['continue'] : null;

  if (isset($_POST['login'])) {
    $user = UserDao::getUserByUsernamePassword($_POST["username"], $_POST["password"]);
    if ($user == null) {
      $incorrectLogin = true;
    } else {
      // login and redirect to continue URL
      // TODO handle 'remember me' checkbox
      SessionUtil::loginAndRedirect($user, $continueUrl);
    }
  } else {
  	$incorrectLogin = false;
  }
?>

<!DOCTYPE html>
<html>
<?php
  require_once 'util/layout.php';
  LayoutUtil::displayHeadTag(null, true);
?>

<body>

<?php
  require_once 'util/layout.php';

  LayoutUtil::displayHeader();
  echo "<div class='row-fluid headrow'>";

  // logo
  echo "<div class='span12 center' id='loginbox'>
          <img src='img/rotiss-logotype.png' width='480' />";

  if ($incorrectLogin) {
    echo "<div class=\"alert alert-error\">
            <button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
            <strong>Sorry!</strong> That is an incorrect username or password. Please try again!
          </div>";
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
                    name='login'>Sign in</button>";
  if ($continueUrl != null) {
    echo "<input type='hidden' name='continue' value='$continueUrl'>";
  }
  echo "  </form>
        </div>";

  echo "</div>"; // row-fluid
  echo "</div>"; // container
  echo "</div>"; // wrap
?>

</body>
</html>
