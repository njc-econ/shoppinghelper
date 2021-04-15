<?php
require_once "pdo.php";
session_start();

if (!isset($_SESSION['user_id'])){
  header('Location: index.php');
}

if (!isset($_GET['recipe_id']) || !is_numeric($_GET['recipe_id'])){
  $_SESSION['error'] = 'Invalid recipe given';
  header('Location: start.php');
  return;
}

// get the recipe out of the database

$stmt = $pdo -> prepare("SELECT recipeHead.*, recipeType.recipeType_nameEng FROM recipeHead LEFT JOIN recipeType ON recipeHead.recipeType_id = recipeType.recipeType_id WHERE recipe_id = :recipe_id ");

$stmt -> execute(array(
  ':recipe_id' =>  $_GET['recipe_id']
));

$recipehead = $stmt -> fetch(PDO::FETCH_ASSOC);

if ($recipehead === false){
  $_SESSION['error'] = 'Recipe not found in database';
  header('Location: start.php');
  return;
}

if ($recipehead['private']===1 && $row['user_id']!==$_SESSION['user_id']) {
  $_SESSION['error'] = 'Only the creator can view the selected recipe.';
  header('Location: start.php');
  return;
}

// prepare recipe characteristics
if ($recipehead['vegetarian'] > 0) {
  $vegetarian = '<div class="recipechar" id="veggie">Vegetarian</div>';
} else {
  $vegetarian = '';
}
if ($recipehead['vegan']>0){
  $vegan = '<div class="recipechar" id="vegan">Vegan</div>';
} else {
  $vegan = '';
}
if ($recipehead['vegan']>0){
  $glutenfree = '<div class="recipechar" id="glutenfree">Gluten-free</div>';
} else {
  $glutenfree = '';
}

$serves = $recipehead['numserved'];
$type = $recipehead['recipeType_nameEng'];

// get the ingredients out of the database
$stmt = $pdo -> prepare('SELECT recipe_id, recipeIngredients.ingredient_id, quantity, measure, name FROM recipeIngredients JOIN ingredients ON recipeIngredients.ingredient_id = ingredients.ingredient_id WHERE recipe_id = :recipe_id ORDER BY input_rank');
$stmt -> execute(array(
  ':recipe_id' =>  $_GET['recipe_id']
));

$recipeIngredients = $stmt -> fetchAll(PDO::FETCH_ASSOC);

// get the name of the image from the database
$stmt = $pdo -> prepare('SELECT filename FROM images WHERE recipe_id = :recipe_id AND image_rank = 0;');
$stmt -> execute(array(
  ':recipe_id' =>  $_GET['recipe_id']
));

$imageName = $stmt -> fetch(PDO::FETCH_ASSOC);
//echo $imageName['filename'];
if ($imageName != 0){
  $imageLoc = "img/recipes/".$imageName['filename'];
} else {
  $imageLoc = "img/recipes/"."default.jpg"; // is a default image, need to find a suitable image
}

// get the instructions out of the database
$stmt = $pdo -> prepare('SELECT * FROM recipeSteps WHERE recipe_id = :recipe_id');
$stmt -> execute(array(
  ':recipe_id' =>  $_GET['recipe_id']
));
$recipeSteps = $stmt -> fetchAll(PDO::FETCH_ASSOC);

// prepare the html for the tables for the recipe

$ingredienttext = '<ul>';
for ($i=0; $i < count($recipeIngredients); $i++){
  $addspace = $recipeIngredients[$i]['quantity']==='' && $recipeIngredients[$i]['measure']==='' ? '' : ' ';
  $ingredienttext = $ingredienttext.'<li>'.$recipeIngredients[$i]['quantity'].$recipeIngredients[$i]['measure'].$addspace.htmlentities($recipeIngredients[$i]['name']).'</li>';
}
$ingredienttext = $ingredienttext.'</ul>';

$steptext = '';
for ($i=0; $i < count($recipeSteps); $i++){
  $steptext = $steptext.'<h5>Step '.($i+1).': '.htmlentities($recipeSteps[$i]['stepTitle']).'</h5>';
  $steptext = $steptext.'<p>'.htmlentities($recipeSteps[$i]['stepText']).'</p>';
}

// check if the recipe is already in the users shopping list
$stmt = $pdo -> prepare('SELECT COUNT(*) AS noIngredients FROM recipeShopping WHERE user_id = :user_id AND sourcerecipe_id = :recipe_id');
$stmt -> execute(array(
  ':user_id' => $_SESSION['user_id'],
  ':recipe_id' => $_GET['recipe_id']
));
$row = $stmt -> fetch(PDO::FETCH_ASSOC);
if ($row['noIngredients'] > 0){
  $buttonText = 'Remove from Shopping List';
  $onList = 1;
} else {
  $buttonText = 'Add to Shopping List';
  $onList = 0;
}

?>

<!DOCTYPE html>
<html dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Groceries made Easy: <?= $recipehead['title']?></title>
    <?php require_once("headerscript.php") ?>
  </head>

  <header>
    <?php require_once("headerIn.html") ?>
  </header>

  <body>
    <!-- title -->
    <h2 class="recipeTitle"><?= htmlentities($recipehead['title']) ?></h2>

    <!-- image -->
    <figure>
      <img src="<?= $imageLoc ?>" alt="Cooked Dish" class="recipeHeadIMG">
    </figure>



    <!-- Characteristics -->

    <div id="recipeCharacteristics">
      <div id="recipetype" class="recipechar"><?= $type ?></div>
      <div id="noserved" class="recipechar">Serves <?= $serves ?></div>

      <?= $vegan ?>
      <?= $vegetarian ?>
      <?= $glutenfree ?>

    </div>

    <!-- Ingredients -->
    <br>
    <h4>Ingredients</h4>
    <?= $ingredienttext ?>

    <!-- how to cook -->
    <?= $steptext ?>
    <!-- buttons to other pages -->
    <button type="button" name="button" id="shopList" value=<?= $_GET['recipe_id']?> ><?= $buttonText?> </button>

  </body>
</html>

<script type="text/javascript">


  $(document).ready(
    function(){
      onList = <?= $onList ?>;

      $('#shopList').click(
        function (event){
          event.preventDefault();
          if (onList === 0){
            $.post("recipeshopadd.php?recipe_id="+$('#shopList').val(), function(data){
              if (data==="1"){
                $('#shopList').text("Remove from Shopping List");
                onList = 1;
              }
            });
          }
          if (onList === 1){
            $.post("recipeshopremove.php?recipe_id="+$('#shopList').val(), function(data){
              if (data==="1"){
                $('#shopList').text("Add to Shopping List");
                onList = 0
              }
            });
          }

        }
      );
    }

  );
</script>
