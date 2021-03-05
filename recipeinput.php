<?php
  require_once "pdo.php"
  session_start();

  if (!isset($_SESSION['name'])){
    header('Location: index.php');
  }

?>




<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Groceries made Easy: Add new recipe</title>
  </head>
  <body>
    <h1>Your Recipe</h1>

  <form method="post">
    <p>
      <h2>Recipe Title</h2>
      <input type="text" name="recipetitle" value="" size=50px>
    </p>
  </form>

  </body>
</html>
