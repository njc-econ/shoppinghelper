<?php
  session_start();

  if (!isset($_SESSION['user_id'])){
    header('Location: index.php');
    require 'logout.php';
  }

 ?>

 <!DOCTYPE html>
 <html lang="en" dir="ltr">
    <?php require_once("headerscript.php") ?>
    <head>
     <meta charset="utf-8">
     <title></title>
   </head>

   <header>
     <?php require_once("headerIn.html") ?>
   </header>


   <body>
    <h1>Welcome <?php echo $_SESSION['name']?>!</h1>
    <?php if (isset($_SESSION['error'])){echo $_SESSION['error']; unset($_SESSION['error']);} ?>
    <h2>Your recipes</h2>
    <div class="recipeList" id="yourRecipes">


    </div>

    <h2>Recommended Recipes</h2>


    <h2><a href="shoppinglist.php">Your Shopping List</a></h2>

    <h2><a href="recipeinput.php">Create New Recipe</a></h2>

    <a href="logout.php">Logout</a>
   </body>
   <a href="recipe.php?recipe_id="></a>
 </html>

 <script type="text/javascript">
 $.getJSON("yourrecipes.php",function(data){
   for (i in data){
     $('#yourRecipes').append('<a href="recipe.php?recipe_id='+data[i].recipe_id+'">'+data[i].title+'</a><br>');
   };
 });





    $(document).ready(
      function(){

      }
    )
 </script>
