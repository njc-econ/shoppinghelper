<?php
  require_once 'pdo.php';
  session_start();

  $message = false;
  $messagecolor = 'red';

  if (isset($_POST['loginSubmit'])) {
    if (isset($_POST['emailLogin']) && isset($_POST['passLogin'])){
      //$pass = hash('md5',$salt.$_POST['passLogin']);
      if (strlen($_POST['emailLogin']) < 1 || strlen($_POST['passLogin']) < 1){
        $message = "Email and password are required";
        error_log("Login fail ".$_POST['who']." $message");
      } else if (strpos($_POST['emailLogin'],'@') === false){
        $message = "Email must have an at-sign (@)";
        error_log("Login fail ".$_POST['emailLogin']." $message");
      }

      if ($message === false){
        $stmt = $pdo -> prepare('SELECT id, forename, email, password FROM users WHERE email = :email;');
        $statementOutput = $stmt -> execute( array(
          ':email' => $_POST['emailLogin']));
        $result = $stmt -> fetch(PDO::FETCH_ASSOC);
        if ($result){
          if (password_verify($_POST['passLogin'], $result['password'])) {
            $_SESSION["userid"] = $result['id'];
            $_SESSION["name"] = $result['forename'];
            header('Location: start.php');
          } else {
            $message = "Entered password is not correct.";
          }
        } else {
          $message = "No user exists with that email address.";
        }

        //foreach ($pdo -> query($stmt) as $row){
          //print_r($row);
        //  $tableinput = $tableinput."<li>".$row['year']." ".$row['make']." / ".$row['mileage']."</li>";
        //}
      }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Groceries made Easy</title>
  </head>
  <body>

    <h2>Sign in:</h2>
    <form class="login" method="post">
      <label for="emailLogin">Email</label><input type="email" name="emailLogin" id="emailLogin" required>
      <label for="passLogin">Password</label><input type="password" name="passLogin" id="passLogin" required>
      <input type="submit" name="loginSubmit" value="Login">
    </form>
    <?php if ($message !== false){echo '<p style="color: '.$messagecolor.';">'.$message.'</p>';} ?>
  <p>Don't have an account? <a href="registration.php">Sign-up here</a>.</p>
  </body>
</html>
