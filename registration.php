<?php
  require_once "pdo.php";

  session_start();

  $storesurname='';
  $storeforename='';
  $storeemail='';

  if (isset($_SESSION['reginputs'])){
    $storesurname=$_SESSION['reginputs']['surname'];
    $storeforename=$_SESSION['reginputs']['forename'];
    $storeemail=$_SESSION['reginputs']['email'];
    unset($_SESSION['reginputs']);
  }

  if ( isset($_POST['surname']) && isset($_POST['forename']) && isset($_POST['email']) && isset($_POST['password'])){

    $_SESSION['reginputs'] = array(
      "surname" => $_POST['surname'],
      "forename" => $_POST['forename'],
      "email" => $_POST['email'],
      "preferred_lang" => $_POST['lang']
    );


    // input validation
    // if email already exists in database don't accept
    // if password is too short, don't accept

    // email address without @
    if (strpos($_POST['email'],'@') === false){
      //$message = "Email must have an at-sign (@)";
      //error_log("Login fail ".$_POST['email']." $message")
      $_SESSION['error'] = 'Invalid e-mail address. Please provide valid address.';
      header("Location: registration.php");
      return;
    }

    // passwords don't match
    if ($_POST['password'] !== $_POST['confirm_password']){
      $_SESSION['error'] = 'Passwords did not match. Please try again.';
      header("Location: registration.php");
      return;
    }

    // inputs too short
    // names
    if (strlen($_POST['surname']) < 1 || strlen($_POST['forename']) < 1){
      $_SESSION['error'] = 'Surname and First Name are both required fields.';
      header("Location: registration.php");
      return;
    }

    // password
    if (strlen($_POST['password']) < 8){
      $_SESSION['error'] = 'Password must have at least 8 characters';
      header("Location: registration.php");
      return;
    }

    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $pdo -> prepare($sql);
    $stmt -> execute(array(
      ':email' => $_POST['email']
    ));

    $result = $stmt -> fetchAll(PDO::FETCH_ASSOC);
    if (count($result) > 0){
      $_SESSION['error'] = 'Account already registered to e-mail address. Please login.';
      header("Location: index.php");
      return;
    }

    require_once('utilsValidation.php');
    $lang_id = validateLanguage('Location: index.php', $pdo);


    $sql = "INSERT INTO users (surname, forename, email, password, preferred_lang) VALUES (:surname, :forename, :email, :password, :lang_id)";
    $stmt = $pdo -> prepare($sql);
    $stmt -> execute(array(
      ':surname' => $_POST['surname'],
      ':forename' => $_POST['forename'],
      ':email' => $_POST['email'],
      ':password' => password_hash($_POST['password'], PASSWORD_BCRYPT),
      ':lang_id' => $lang_id
    ));

    unset($_SESSION['reginputs']);
    header('Location: index.php');
    return;
    /*

    */

  } else

?>
<!DOCTYPE HTML>
<html>
  <head>
    <title>Groceries made Easy: User Registration</title>
    <?php require_once("headerscript.php") ?>
  </head>

<header>
  
</header>


<body>
  <h3>Register</h3>
  <p class="erroroutput"><?php
    if (isset($_SESSION['error'])){
      echo $_SESSION['error'];
      unset($_SESSION['error']);
    }
  ?></p>
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
      <input type="email" name="email" required value="<?= htmlentities($storeemail)?>">
    </p>
    <p>
      <label for "password">Password</label>
      <input type="password" name="password" required>
    </p>
    <p>
      <label for "confirm_password">Confirm Password</label>
      <input type="password" name="confirm_password" required>
    </p>

    <label for "preferredlang">Preferred Shopping List Language:</label>
    <select class="lang" name="lang" id="preferredlang" required>
        <option value="de">German</option>
        <option value="en">English</option>
        <option value="es">Spanish</option>
    </select>

    <p>
      <input type="submit" name="submit" value="Submit">
    </p>
  </form>
  <p>Already have an account? <a href="index.php">Login here</a>.</p>
</body>
</html>
