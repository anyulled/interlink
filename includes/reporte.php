<?php

/**
 * Description of reporte
 *
 * @author Anyul Rivas
 */
class reporte extends db {

    const tabla = "reporte";
    const tabla_parametros_reporte = "reporte_parametros";
    const tabla_parametros = "parametro_reporte";
    const jasper_url = "http://localhost:8080/jasperserver/services/repository";
    const jasper_username = "jasperadmin";
    const jasper_password = "jasperadmin";

    function ver($id) {
        $result = array();
        $result['reporte'] = $this->select("*", self::tabla, array("id" => $id));
        $result['parametros'] = $this->dame_query("select parametro_reporte.nombre, reporte_parametros.multiple from reporte_parametros 
            inner join parametro_reporte on reporte_parametros.parametro_reporte_id = parametro_reporte.id 
            where reporte_parametros.reporte_id = $id");
        return $result;
    }
    function ver_reporte($ruta_reporte, $formato, $parametros){
        $cliente = new JasperClient(self::jasper_url, self::jasper_username, self::jasper_password);
        $resultado = $cliente->requestReport($ruta_reporte, $formato, $parametros);
        return $resultado;
    }

}

?>
