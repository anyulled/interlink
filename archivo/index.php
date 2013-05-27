<?php

// <editor-fold defaultstate="collapsed" desc="inicio">
include '../includes/constants.php';
$usuario = new usuario();
$funcionalidad = new funcionalidad();
$usuario->confirmar_miembro();
$enlaces = $funcionalidad->funcionalidad_grupo($_SESSION['usuario']['ROL_ID']);
$pagina = "archivo/index.html.twig";

$variables = array();
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="usuario">
if (isset($_SESSION['usuario'])) {
    $variables['usuario'] = $_SESSION['usuario'];
    $variables['funcionalidades'] = $enlaces['data'];
}
// </editor-fold

if (isset($_GET['accion'])) {
    $accion = $_GET['accion'];
    switch ($accion) {
        case 'cargar':
            $db = new db();
            $procesos = $db->select("*", "proceso");
            $variables["procesos"] = $procesos["data"];
            $pagina = "archivo/index.html.twig";
            break;
        default:
            $pagina = "archivo/index.html.twig";
            break;
    }
}
if (isset($_POST['accion'])) {
    switch ($_POST["accion"]) {
        case 'Cargar':
            $data = $_POST;
            unset($data['accion']);
            $data['archivo'] = $_FILES['archivo']['tmp_name'];
            $archivo = new archivo();
            try {
                $resultado = $archivo->cargar_archivo($data);
                if (count($resultado) > 0) {
                    $result_db = $archivo->insertar_tabla_temporal($resultado, $data["tipo_archivo"]);
                    IF (DEBUG) {
                        $variables["resultado"] = $resultado;
                    }
                } else {
                    throw new Exception("No se pudo procesar el archivo");
                }
                $variables["proceso"] = $_POST["tipo_archivo"];
                $variables["result_db"] = $result_db["data"][0]["DESCRIPCION"];
                $pagina = "archivo/resultado.html.twig";
            } catch (Exception $exc) {
                $variables["tipomensaje"] = "alert-error";
                $variables["mensaje"] = $exc->getMessage();
                $pagina = "archivo/index.html.twig";
                if (DEBUG) {
                    var_dump($exc->getTraceAsString());
                }
            }
            break;
    }
}
// <editor-fold defaultstate="collapsed" desc="TWIG">
echo $twig->render($pagina, $variables);
if (DEBUG)
    var_dump("GET", $_GET, "POST", $_POST, "usuario", $_SESSION['usuario'], "FILES", $_FILES);
// </editor-fold>
?>
