<?php
  require_once 'pdo.php';
  session_start();

  if (isset($_SESSION["user_id"])){
    header("Location: start.php");
    return;
  }

  if (isset($_POST['login'])) {
    if (isset($_POST['email']) && isset($_POST['pass'])){
      //$pass = hash('md5',$salt.$_POST['passLogin']);
      if (strlen($_POST['email']) < 1 || strlen($_POST['pass']) < 1){
        $msg = "Email and password are required";
        $_SESSION['error'] = $msg;
        error_log("Login fail:".$_POST['email']." - ".$msg);
        header("Location: index.php");
        return;
      }

      if (strpos($_POST['email'],'@') === false){
        $msg = "Email must have an at-sign (@)";
        $_SESSION['error'] = $msg;
        error_log("Login fail:".$_POST['email'].$msg);
        header("Location: index.php");
        return;
      }

      $stmt = $pdo -> prepare('SELECT user_id, forename, email, password FROM users WHERE email = :email;');
      $statementOutput = $stmt -> execute( array(
        ':email' => $_POST['email'])
      );
      $result = $stmt -> fetch(PDO::FETCH_ASSOC);
      if ($result){
        if (password_verify($_POST['pass'], $result['password'])) {
          $_SESSION["user_id"] = $result['user_id'];
          $_SESSION["name"] = $result['forename'];
          header('Location: start.php');
          return;
        }

        $_SESSION['error'] = "Entered password is not correct.";
        header('Location: index.php');
        return;

      }

      $_SESSION['error'] = "No user exists with that email address.";
      header('Location: index.php');
      return;
        //foreach ($pdo -> query($stmt) as $row){
          //print_r($row);
        //  $tableinput = $tableinput."<li>".$row['year']." ".$row['make']." / ".$row['mileage']."</li>";
        //}
    }
  }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Groceries made Easy</title>
    <?php require_once("headerscript.php") ?>
  </head>
  <body>

    <h2>Sign in:</h2>
    <p class="erroroutput">
      <?php
        if (isset($_SESSION['error'])){
          echo $_SESSION['error'];
          unset($_SESSION['error']);
        }
      ?>
    </p>
    <form class="login" method="post">
      <label for="emailLogin">Email</label><input type="email" name="email" id="emailLogin" required>
      <label for="passLogin">Password</label><input type="password" name="pass" id="passLogin" required>
      <input type="submit" name="login" value="Login">
    </form>
  <p>Don't have an account? <a href="registration.php">Sign-up here</a>.</p>
  </body>
</html>
