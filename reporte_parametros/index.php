<?php

require '../includes/constants.php';
$variables = array();
$pagina = "reporte_parametros/index.html.twig";

echo $twig->render($pagina, $variables);
?>