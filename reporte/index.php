<?php

// <editor-fold defaultstate="collapsed" desc="inicio">
include '../includes/constants.php';
$usuario = new usuario();
$funcionalidad = new funcionalidad();
$usuario->confirmar_miembro();
$enlaces = $funcionalidad->funcionalidad_grupo($_SESSION['usuario']['ROL_ID']);
$pagina = "reporte/index.html.twig";
$perfiles_especiales = array(3, 5, 6);

$variables = array();
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="usuario">
if (isset($_SESSION['usuario'])) {
    $variables['usuario'] = $_SESSION['usuario'];
    $variables['funcionalidades'] = $enlaces['data'];
}
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Acciones GET">
if (isset($_GET['accion'])) {
    $accion = $_GET['accion'];
    switch ($accion) {
        case "listar":
            /*
             * todos los campos de reportes disponibles se cargan 
             * desde las funcionalidades del usuario
             */
            break;
        case "ver":
            $db = new db();
            $clase_reporte = new reporte();
            $reporte = $clase_reporte->ver($_GET['id']);
            if (count($reporte['reporte']['data']) > 0) {
                // <editor-fold defaultstate="collapsed" desc="Datos del reporte">
                $variables['titulo_reporte'] = $reporte['reporte']['data'][0]['nombre'];
                $variables['parametros_reporte'] = $reporte['parametros']['data'];
                foreach ($reporte['parametros']['data'] as $parametro) {
                    if ($parametro['nombre'] == "indicador") {
                        $indicadores = $db->select("*", "indicador_audiencia");
                        $variables['indicadores'] = $indicadores['data'];
                    }
                    if ($parametro['nombre'] == "señal") {
                        $seniales = $db->select("*", "tipo_tv");
                        $variables["seniales"] = $seniales['data'];
                    }
                    if ($parametro['nombre'] == "perfil") {
                        if ($_GET["id"] == "2") {//reporte de ámbito
                            $perfiles = $db->select("*", "indicador", array("categoria_id" => 17));
                        }else if(in_array($_GET["id"], $perfiles_especiales)){//categorias de perfiles especiales
						//$_GET["id"]=="6" ||$_GET["id"]=="3"||$_GET["id"]=="5"
                            $perfiles = $db->dame_query("select * from indicador where categoria_id in(20,21)");
                        }
                        else {
                            $perfiles = $db->select("*", "indicador");
                        }
                        $variables["perfiles"] = $perfiles['data'];
                    }
                    if ($parametro['nombre'] == "canal") {
                        $canales = $db->select("*", "canal");
                        $variables["canales"] = $canales['data'];
                    }
                }
                // </editor-fold>
            } else {
                $variables['tipomensaje'] = "alert-error";
                $variables['mensaje'] = "Reporte no encontrado.";
            }
            $pagina = "reporte/formulario.html.twig";
            break;
        default:
            break;
    }
}
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Acciones POST">
if (isset($_POST['accion'])) {
    switch ($_POST['accion']) {
        case "Ver Reporte":
            // <editor-fold defaultstate="collapsed" desc="init">
            $db = new db();
            $clase_reporte = new reporte();
            $reporte = $clase_reporte->ver($_GET['id']);
            $where = " where";
            $and = " and ";
            $count = 0;
            $parametros = array(); 
            // </editor-fold>
            if (count($reporte['reporte']['data']) > 0) {
                $datos_reporte = $reporte['reporte']['data'][0];
                $consulta = "select * from (" . $datos_reporte['query'] . ") as reporte ";
                if (isset($_POST['periodo_desde']) && $_POST["periodo_desde"] != "") {
                    $fecha_inicio = DateTime::createFromFormat("d/m/Y", $_POST["periodo_desde"]);
                    $parametros["FECHA_INI_MOSTRAR"] = $_POST["periodo_desde"];
                    $parametros["FECHA_INI"] = $fecha_inicio->format("Y-m-d");
                    //$variables['periodo_desde'] = $_POST['periodo_desde'];
                    $consulta.= ($count == 0) ? $where : $and;
                    $consulta.= " fecha >= '" . Misc::formatear_fecha_sql($_POST['periodo_desde']) . "'";
                    $count++;
                }
                if (isset($_POST['periodo_hasta']) && $_POST["periodo_hasta"] != "") {
                    $fecha_fin = DateTime::createFromFormat("d/m/Y", $_POST["periodo_hasta"]);
                    $parametros["FECHA_FIN"] = $fecha_fin->format("Y-m-d");
                    $parametros["FECHA_FIN_MOSTRAR"] = $_POST["periodo_hasta"];
                    //$variables['periodo_hasta'] = $_POST['periodo_hasta'];
                    $consulta.= ($count == 0) ? $where : $and;
                    $consulta.= " fecha <= '" . Misc::formatear_fecha_sql($_POST['periodo_hasta']) . "'";
                    $count++;
                }
                if (isset($_POST['canal']) && $_POST["canal"] != "") {
                    //$canal = $db->select("nombre", "canal", array("id" => $_POST['canal']));
                    //$variables['canal'] = $canal['data'][0]['nombre'];
                    $consulta.= ($count == 0) ? $where : $and;
                    if (is_array($_POST["canal"])) {
                        $parametros["CANALES"] = implode(",", $_POST["canal"]);
                        $consulta.= " canal_id in(" . $parametros["CANALES"] . ")";
                    } else {
                        $parametros["CANALES"] = $_POST["canal"];
                        $consulta.= " canal_id = " . $_POST['canal'];
                    }
                    $count++;
                }
                if (isset($_POST['indicador']) && $_POST["indicador"] != "") {
                    //$indicador_audiencia = $db->select("indicador", "indicador_audiencia", array("id" => $_POST['indicador']));
                    //$variables['indicador'] = $indicador_audiencia['data'][0]['nombre'];
                    if (is_array($_POST["indicador"])) {
                        $parametros["INDICADORES"] = implode(",", $_POST["indicador"]);
                    } else {
                        $parametros["INDICADORES"] = $_POST["indicador"];
                    }
//                    $consulta.= ($count == 0) ? $where : $and;
                    //$consulta.= " indicador_audiencia_id = " . $_POST['indicador'];
                    $count++;
                }
                if (isset($_POST['perfil']) && $_POST["perfil"] != "") {
                    //$indicador = $db->select("nombre", "indicador", array("id" => $_POST['perfil']));
                    //$variables['perfil'] = $indicador['data'][0]['nombre'];
                    $consulta.= ($count == 0) ? $where : $and;
                    if (is_array($_POST["perfil"])) {
                        $parametros["PERFILES"] = implode(",", $_POST["perfil"]);
                        $consulta.= " indicador_id in(" . $parametros["PERFILES"] . ")";
                    } else {
                        $parametros["PERFILES"] = $_POST["perfil"];
                        $consulta.= " indicador_id = " . $_POST['perfil'];
                    }
                    $count++;
                }
                if (isset($_POST['senial']) && $_POST["senial"] != "") {
                    //$senial = $db->select("nombre", "tipo_tv", array("id" => $_POST['senial']));
                    //$variables['senial'] = $senial['data'][0]['nombre'];
                    $consulta.= ($count == 0) ? $where : $and;
                    $consulta.= " tipotv_id = " . $_POST['senial'];
                    $count++;
                }
                $resultado = $db->dame_query($consulta);
//                if (count($parametros) > 0) {
                if ($resultado['suceed'] && count($resultado['data']) > 0) {
                    try {
//                        $report_params = array(
//                            'CANALES' => '3,4,7,18,20,21,5,38',
//                            'FECHA_INI_MOSTRAR' => '01-09-2011',
//                            'FECHA_FIN_MOSTRAR' => "01-09-2011",
//                            'FECHA_INI' => '2011-09-01',
//                            'FECHA_FIN' => '2011-09-01',
//                            'INDICADORES' => 1,
//                            'PERFILES' => '45,46,47'
//                        );
                        $jasper = $clase_reporte->ver_reporte($datos_reporte['ruta_jasper'], "PDF", $parametros);
                        header('Content-type: application/pdf');
                        echo $jasper;
                        die();
                    } catch (Exception $exc) {
                        $variables["mensaje"] = $exc->getMessage();
                    }
                    //$variables["consulta"] = $consulta;
                    //$variables['titulo_reporte'] = $datos_reporte['nombre'];
                    //$variables['datos'] = $resultado['data'];
                    //$pagina = "reporte/" . $datos_reporte['nombre_archivo'];
                } else {
                    $variables['tipomensaje'] = "alert-warning";
                    if (DEBUG) {
                        $variables["consulta"] = $consulta;
                    }
                    $variables['mensaje'] = "Este reporte no contiene datos para los parametros seleccionados";
                    $pagina = "reporte/formulario.html.twig";
                }
            }
            break;
        default:
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
