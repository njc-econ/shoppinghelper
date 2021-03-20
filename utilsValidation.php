<?php
require_once('pdo.php');

function validateIngredients($redirectAdd) {
  if (!isset($_POST['noIngreds_1']) || $_POST['noIngreds_1']==0){
    $_SESSION['error'] = 'What is a recipe without ingredients? Please add ingredients.';
    header($redirectAdd);
    return;
  }
  $noIngredients = $_POST['noIngreds_1'];
  for ($i = 0; $i < $noIngredients; $i++){
      if (!$_POST['quantity'.$i]=="" && !is_numeric($_POST['quantity'.$i])){
        $_SESSION['error'] = 'Input quantities must be left blank or be numeric';
        header($redirectAdd);
        return;
      }

      $measures = array(
        "", "g", "kg", "ml", "l"
      );
      if (!in_array($_POST['measure'.$i],array('','kg','g','ml','l'))){
        $_SESSION['error'] = 'Invalid ingredient measure provided';
        header($redirectAdd);
        return;
      }

      if (is_numeric($_POST['ingredient'.$i])){
        $_SESSION['error'] = 'Invalid ingredient name';
        header($redirectAdd);
        return;
      }

      if (strlen($_POST['ingredient'.$i]) < 1){
        $_SESSION['error'] = 'Ingredient should not be left blank.';
        header($redirectAdd);
        return;
      }

      if (strlen($_POST['ingredient'.$i]) > 100){
        $_SESSION['error'] = 'Ingredient name too long. Please abbreviate. Max 100 characters.';
        header($redirectAdd);
        return;
      }
    }
  }

  function validateSteps($redirectAdd)  {
    if (!isset($_POST['noSteps_1']) || !is_numeric($_POST['noSteps_1'])) {
      $_SESSION['error'] = "Invalid input. Please don't play with the code. No of recipe steps has to be numeric";
      header($redirectAdd);
      return;
    }
    $noSteps = $_POST['noSteps_1'];

    for ($i = 0; $i < $noSteps; $i++){
      if (strlen($_POST['stepTitle'.$i])<1 && strlen($_POST['stepText'.$i])<1) {
        $_SESSION['error'] = "Invalid input. Please input some text to describe how you cook this dish.";
        header($redirectAdd);
        return;
      }
    }

  }

  function validateLanguage ($redirectAdd, $pdo){

    if (!isset($_POST['lang'])){
      $_SESSION['error'] = "No language input provided.";
      header($redirectAdd);
      return;
    }

    // collect the id for the language

    $stmt = $pdo -> prepare('SELECT lang_id FROM languages WHERE lang_short = :lang_short');
    $stmt -> execute(array(
      ':lang_short' => $_POST['lang']
    ));

    $row = $stmt -> fetch(PDO::FETCH_ASSOC);

    if ($row === false || $row === 0){
      $_SESSION['error'] = "Erroneous language code received.";
      header($redirectAdd);
      return;
    }

    return $row['lang_id'];
  }
 ?>
