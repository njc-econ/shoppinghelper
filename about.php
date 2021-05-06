<?php
  require_once "pdo.php";
  session_start();


?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Groceries made Easy: About</title>
    <?php require_once("headerscript.php") ?>
  </head>

  <header>
    <?php
      if (isset($_SESSION['user_id'])){
        require_once("headerIn.html");
      } else {
        require_once("headerOut.html");
      }
     ?>
  </header>
  <body>
    <p>Welcome to your grocery helper! The aim of your grocery helper is to make it easy to shop for the meals you want to enjoy.</p>

    <p>I, your site creator, enjoys cooking and would ideally cook a varied set of meals, only rarely repeating dishes from one week to the next.
    My problem was always laziness when it came to go shopping, I would quickly fill out my shopping list with ingredients from a small list of dishes that I remembered.
    This site was created to solve this problem.</p>

    <p>
      Upload your favourite recipes once and then with one click you can add all the ingredients of your recipe to your shopping list.
    </p>

    <p>The site was created as a hobby project, if you would like to use it, feel free but be aware it will be stable for a while and then change a lot all at
    once without warning when I have time to work on it.</p>

    <p>If you would like to contribute to making this site better please contact me via the github page for the  <a href="https://github.com/njc-econ/shoppinghelper/">site</a>.
    </p>
  </body>
</html>
