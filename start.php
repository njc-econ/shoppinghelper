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
   </body>
 </html>
