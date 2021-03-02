<?php
  $storesurname = isset($_POST['surname']) ? $_POST['surname'] : '';
  $storefirstname = isset($_POST['forename']) ? $_POST['forename'] : '';
  $storeemail = isset($_POST['email']) ? $_POST['email'] : '';
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
