<?php
  session_start();

  if (!isset($_SESSION['name'])){
    header('Location: index.php');
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

    <h2>Your recipes</h2>


    <h2>Recommended Recipes</h2>


    <h2><a href="shoppinglist.php">Your Shopping List</a></h2>
   </body>
 </html>
