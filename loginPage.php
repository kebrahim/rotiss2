<?php session_start(); ?>
<html>
<head>
<title>Rotiss.com</title>
</head>

<style type="text/css">
html {height:100%;}
body {text-align:center;}
table {text-align:center;}
table.center {margin-left:auto; margin-right:auto;}
#column_container {padding:0; margin:0 0 0 50%; width:50%; float:right;}
#left_col {float:left; width:100%; margin-left:-100%; text-align:center;}
#left_col_inner {padding:10px;}
#right_col {float:right; width:100%; text-align:center;}
#right_col_inner {padding:10px;}
#placeholder_row {background-color:#E3F2F9;}
#vert_td {vertical-align:top;}
#error_msg {color:#FF0000; font-weight:bold;}
</style>

<script>
</script>

<body>
<?php
  require_once 'dao/userDao.php';

  echo "<form action='loginPage.php' method=post>";
  echo "<h1>Rotiss.com</h1>";
  echo "<h4>Sign in</h4>";

  if (isset($_POST['login'])) {
    $user = UserDao::getUserByUsernamePassword($_POST["user"], $_POST["pass"]);
    if ($user == null) {
      echo "<div id='error_msg'>Invalid username or password; please try again.<br/><br/></div>";
    } else {
      // TODO add user information to session

      // redirect to summary page
      $str = "Location: http://localhost/rotiss2/summaryPage.php?team_id=" .
          $user->getTeam()->getId();
      header ($str);
      exit;
    }
  }

  echo "<table class='center'>
        <tr>
          <td>Username:</td>
          <td><input type='text' name='user' placeholder='Enter username' required></td>
        </tr>
        <tr>
          <td>Password:</td>
          <td><input type='password' name='pass' placeholder='Enter password' required></td>
        </tr>
        </table>";
  echo "<br/><input type='submit' name='login' value='Sign in'><br/><br/>
        <a href='loginHelpPage.php'>Can't access your account?";
  // TODO add loginHelpPage.php
  echo "</form>";
?>
</body>
</html>