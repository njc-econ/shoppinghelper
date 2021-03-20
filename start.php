<?php
  session_start();

  if (!isset($_SESSION['user_id'])){
    header('Location: index.php');
    require 'logout.php';
  }
  if ( isset($_POST['logout']) ) {
      session_destroy();
      header('Location: index.php');
      return;
  }

 ?>

 <!DOCTYPE html>
 <html lang="en" dir="ltr">
   <head>
     <meta charset="utf-8">
     <title></title>
   </head>
   <body>
    <h1>Welcome <?php echo $_SESSION['name']?>!</h1>
    <?php if (isset($_SESSION['error'])){echo $_SESSION['error']; unset($_SESSION['error']);} ?>
    <h2>Your recipes</h2>


    <h2>Recommended Recipes</h2>


    <h2><a href="shoppinglist.php">Your Shopping List</a></h2>

    <h2><a href="recipeinput.php">Create New Recipe</a></h2>

    <a href="logout.php">Logout</a>
   </body>
 </html>
