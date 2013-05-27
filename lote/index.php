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
        case 'listar':
            $registros = new paginacion();
            $registros->paginar("select lote.ID, usuario.LOGIN USUARIO, proceso.NOMBRE PROCESO, lote.FECHA, lote.DESCRIPCION from lote
inner join usuario on lote.USUARIO_ID = usuario.ID
inner join proceso on proceso.ID = lote.PROCESO_ID");
            $variables['lotes'] = $registros->registros;
            $variables['paginacion'] = $registros->mostrar_paginado_lista(true);
            $pagina = "lote/listado.html.twig";
            break;
        default:
            $pagina = "lote/listado.html.twig";
            break;
    }
}
echo $twig->render($pagina,$variables);
?>