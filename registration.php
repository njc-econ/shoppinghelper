<?php
  require_once "pdo.php"
  if ( isset($_POST['surname']) && isset($_POST['forename']) && isset($_POST['email']) && isset($_POST['password'])){
    $sql = "INSERT INTO users (surname, forename, email, password) VALUES (:surname, :forename, :email,:password)";
    $stmt = $pdo -> prepare($sql);
    $stmt -> execute(array(
      ':surname' => $_POST['surname'],
      ':forename' => $_POST['forename'],
      ':email' => $_POST['email'],
      ':password' => password_hash($_POST['password'], PASSWORD_BCRYPT);
    ));
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
      <input type="text" name="forename" required value="<?= htmlentities($storefirstname)?>">
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
</body>
</html>
