<?php session_start(); ?>
<html>
<head>
<title>Rotiss.com</title>
</head>

<style type="text/css">
html {height:100%;}
table {text-align:center;}
table.center {margin-left:auto; margin-right:auto;}
label {font-weight:bold;}
fieldset {width: 250px;}
#column_container {padding:0; margin:0 0 0 50%; width:50%; float:right;}
#left_col {float:left; width:100%; margin-left:-100%; text-align:center;}
#left_col_inner {padding:10px;}
#right_col {float:right; width:100%; text-align:center;}
#right_col_inner {padding:10px;}
#placeholder_row {background-color:#E3F2F9;}
#vert_td {vertical-align:top;}
#error_msg {color:#FF0000; font-weight:bold;}
#logininfo {margin-left:6;}
</style>

<script>
</script>

<body>
  <form action='index.php' method=post>
  <img src='images/rotiss.jpg' width='240' />

<?php
  require_once 'dao/userDao.php';
  require_once 'util/sessions.php';

  if (isset($_POST['login'])) {
    $user = UserDao::getUserByUsernamePassword($_POST["username"], $_POST["password"]);
    if ($user == null) {
      echo "<div id='error_msg'>Invalid username or password; please try again.<br/><br/></div>";
    } else {
      // add user information to session
      SessionUtil::login($user);

      // TODO redirect to navigation page
      SessionUtil::redirectToUrl("http://localhost/rotiss2/summaryPage.php");
    }
  }

?>
  <div id='logininfo'>
  <fieldset >
    <legend>Sign in</legend>
    <input type='hidden' name='submitted' id='submitted' value='1'/>
    <label for='username' >Username:</label><br/>
    <input type='text' name='username' id='username'  maxlength="50" required /><br/><br/>
    <label for='password' >Password:</label><br/>
    <input type='password' name='password' id='password' maxlength="50" required /><br/><br/>
    <input type='submit' name='login' value='Sign in' />
  </fieldset><br/>
  <a href='loginHelpPage.php'>Can't access your account?</a><br/>
  </div>
  </form>
</body>
</html>