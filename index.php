<?php

session_start();

$usuario = array("nombre" => "anyulled");
include 'includes/constants.php';
$variables = array();
if (isset($_SESSION['usuario'])) {
    array_push($variables, $_SESSION['usuario']);
}
echo $twig->render("index.html.twig", $variables);
if (DEBUG)
    var_dump("GET", $_GET, "POST", $_POST, "usuario", $_SESSION['usuario'], "FILES", $_FILES);
?>
