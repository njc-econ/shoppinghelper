<?php
  require_once "pdo.php";
  $storesurname = '';
  $storeforename = '';
  $storeemail = '';
  session_start();



  if ( isset($_POST['surname']) && isset($_POST['forename']) && isset($_POST['email']) && isset($_POST['password'])){
    // input validation




    $sql = "INSERT INTO users (surname, forename, email, password) VALUES (:surname, :forename, :email,:password)";
    $stmt = $pdo -> prepare($sql);
    $stmt -> execute(array(
      ':surname' => htmlentities($_POST['surname']),
      ':forename' => htmlentities($_POST['forename']),
      ':email' => htmlentities($_POST['email']),
      ':password' => password_hash($_POST['password'], PASSWORD_BCRYPT)
    ));
    $storesurname = $_POST['surname'];
    $storeforename = $_POST['forename'];
    $storeemail = $_POST['email'];
  }

?>
<!DOCTYPE HTML>
<html>
  <head>
    <title>User Registration</title>

  </head>

<body>
  <h3>Register</h3>
  <form action="" method="post">
    <p>
      <label for "surname">Surname</label>
      <input type="text" name="surname" required value="<?= htmlentities($storesurname) ?>">
    </p>
    <p>
      <label for "forename">First Name</label>
      <input type="text" name="forename" required value="<?= htmlentities($storeforename)?>">
    </p>
    <p>
      <label for "email">Email</label>
      <input type="email" name="email" requiredvalue="<?= htmlentities($storeemail)?>">
    </p>
    <p>
      <label for "password">Password</label>
      <input type="password" name="password" required>
    </p>
    <p>
      <label for "confirm_password">Confirm Password</label>
      <input type="password" name="confirm_password" required>
    </p>
    <p>
      <input type="submit" name="submit" value="Submit">
    </p>
  </form>
  <p>Already have an account? <a href="index.php">Sign-up here</a>.</p>
</body>
</html>
