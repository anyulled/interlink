<?php

// <editor-fold defaultstate="collapsed" desc="inicio">
include '../includes/constants.php';
$usuario = new usuario();
$funcionalidad = new funcionalidad();
$usuario->confirmar_miembro();
$enlaces = $funcionalidad->funcionalidad_grupo($_SESSION['usuario']['ROL_ID']);
$variables = array();
$variables['get'] = $_GET;
$pagina = "usuario/index.html.twig";
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="usuario">
if (isset($_SESSION['usuario'])) {
    $variables['usuario'] = $_SESSION['usuario'];
    $variables['funcionalidades'] = $enlaces['data'];
}
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="GET">
if (isset($_GET['accion'])) {
    $accion = $_GET['accion'];
    switch ($accion) {
        case "crear":
            $rol = new db();
            $roles = $rol->dame_query("select * from rol");
            $variables['roles'] = $roles['data'];
            $pagina = "usuario/editar.html.twig";
            break;
        case "listar":
            $registros = new paginacion();
            $registros->paginar("select usuario.*, rol.NOMBRE ROL from usuario inner join rol on usuario.ROL_ID = rol.ID");
            $variables['registros'] = $registros->registros;
            $variables['paginacion'] = $registros->mostrar_paginado_lista(false);
            $pagina = "usuario/listado.html.twig";
            break;
        case "ver":
            $variables['modolectura'] = true;
            $rol = new db();
            $roles = $rol->dame_query("select * from rol");
            $variables['roles'] = $roles['data'];
            $id = (isset($_GET['id']) ? $_GET['id'] : $_SESSION['usuario']['ID']);
            $registro = $usuario->ver($id);
            if ($registro['suceed'] && count($registro['data']) > 0) {
                $variables['dato'] = $registro['data'][0];
            } else {
                $variables['mensaje'] = "No se pudo cargar el usuario.";
            }
            $pagina = "usuario/editar.html.twig";
            break;
        case "editar":
            $variables['modolectura'] = false;
            $rol = new db();
            $roles = $rol->dame_query("select * from rol");
            $variables['roles'] = $roles['data'];
            $id = (isset($_GET['id']) ? $_GET['id'] : $_SESSION['usuario']['ID']);
            $registro = $usuario->ver($id);
            if ($registro['suceed'] && count($registro['data']) > 0) {
                $variables['dato'] = $registro['data'][0];
            } else {
                $variables['mensaje'] = "No se pudo cargar el usuario.";
            }
            $pagina = "usuario/editar.html.twig";
            break;
        case "cerrar_sesion":
            $usuario->logout();
            break;
        default :
            $pagina = "usuario/index.html.twig";
    }
}
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="POST">
if (isset($_POST['accion'])) {
    switch ($_POST['accion']) {
        case "crear":
        case "Crear":
            $data = $_POST;
            unset($data['accion'], $data['id'], $data['repetir']);
            $resultado = $usuario->insertar($data);
            if ($resultado['suceed']) {
                $variables['tipomensaje'] = 'alert-success';
                $variables['mensaje'] = "Usuario agregado con exito.";
                $pagina = "usuario/index.html.twig";
            } else {
                $variables['tipomensaje'] = 'alert-error';
                $variables['mensaje'] = "No se pudo agregar el usuario.";
                $pagina = "usuario/editar.html.twig";
            }
            break;
        case "editar":
        case "Editar":
            $data = $_POST;
            unset($data['accion']);
            $resultado = $usuario->actualizar($_POST['id'], $data);
            if ($resultado['suceed']) {
                $variables['tipomensaje'] = 'alert-success';
                $variables['mensaje'] = "Usuario modificado con exito.";
                $pagina = "usuario/index.html.twig";
            } else {
                $variables['tipomensaje'] = 'alert-error';
                $variables['mensaje'] = "No se pudo modificar el usuario.";
                $pagina = "usuario/editar.html.twig";
            }
            break;
        case "Eliminar":
        case "eliminar":
            $data = $_POST['id'];
            $resultado = $usuario->borrar($_POST['id']);
            var_dump($resultado);
            if ($resultado['suceed']) {
                $variables['mensaje'] = "Usuario eliminado con exito.";
                $pagina = "usuario/index.html.twig";
            } else {
                $variables["tipomensaje"] = "alert-error";
                $variables['mensaje'] = "No se pudo eliminar el usuario.";
                $pagina = "usuario/index.html.twig";
            }
            break;
        default:
            unset($_POST['accion']);
            $resultado = $usuario->actualizar($_POST['id'], $_POST);
            break;
    }
}
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="TWIG">
echo $twig->render($pagina, $variables);
if (DEBUG)
    var_dump("GET", $_GET, "POST", $_POST, "usuario", $_SESSION['usuario'], "FILES", $_FILES);
// </editor-fold>
?>