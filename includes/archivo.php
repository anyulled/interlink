<?php

/**
 * Operaciones generales de archivo
 *
 * @author Anyul Rivas
 */
class archivo {
    /* Constantes de Archivo */

    const tipo_archivo_emisiones = 1;
    const tipo_archivo_totales = 2;
    const tipo_archivo_medias_horas = 3;

    private function limpiar_canal($canal) {
        $valores = array(" (np)", " (rp)", '"', "(R)", "(r)", "(G)");
        $result = trim(str_replace($valores, "", $canal));
        return $result;
    }

    private function limpiar_valores($valor) {
        $valores = array('n.a', '"');
        $result = trim(str_replace($valores, '', $valor));
        return $result;
    }

    private function formato_nro_sql($valor) {
        $result = str_replace(array(".", ","), array("", "."), $valor);
        return $result;
    }

    private function formatear_fecha_sql($fecha) {
        $date = DateTime::createFromFormat("d/m/Y", $fecha);
        return $date->format("Y-m-d");
    }

    private function formatea_fecha($fecha) {
        $result = str_replace(".", "/", $this->limpiar_valores($fecha));
        return $result;
    }

    /**
     * Carga el archivo de datos y lo formatea de acuerdo al tipo de archivo
     * @param array $data arreglo de datos con la ruta y el tipo de datos a cargar
     * @return array arreglo de datos formateado
     */
    function cargar_archivo($data) {
        // <editor-fold defaultstate="collapsed" desc="init">
        $ruta = $data["archivo"];
        $tipo_archivo = $data["tipo_archivo"];
        $lectura = false;
        $buffer = array();
        $canalTemp = "";
        $indicadorTemp = "";
        $fechaTemp = "";
        $horaInicioTemp = "";
        $horaFinTemp = "";
        $descripcionTemp = "";
        // </editor-fold>

        try {
            $archivo = new SplFileObject($ruta, "r");
        } catch (Exception $exc) {
            throw new Exception("No se pudo cargar el archivo.");
            echo $exc->getTraceAsString();
        }

        switch ($tipo_archivo) {
            case self::tipo_archivo_emisiones:
                if (isset($archivo)) {
                    foreach ($archivo as $linea) {
                        $valores = explode("|", $linea);
                        if ($this->limpiar_valores($valores[0]) == "Channel") {
                            // <editor-fold defaultstate="collapsed" desc="comprobar estructura de archivo">
                            if (
                                    $this->limpiar_valores($valores[0]) != "Channel" ||
                                    $this->limpiar_valores($valores[1]) != "Date" ||
                                    $this->limpiar_valores($valores[2]) != "Description" ||
                                    $this->limpiar_valores($valores[4]) != "Start time" ||
                                    $this->limpiar_valores($valores[5]) != "End time" ||
                                    $this->limpiar_valores($valores[8]) != "Target\Variable" ||
                                    $this->limpiar_valores($valores[9]) != "SHR %" ||
                                    $this->limpiar_valores($valores[10]) != "AMR" ||
                                    $this->limpiar_valores($valores[11]) != "AMR %" ||
                                    $this->limpiar_valores($valores[12]) != "RCH  [Not cons. - TH: 0min.]" ||
                                    $this->limpiar_valores($valores[13]) != "ATS"
                            ) {
                                throw new Exception("Estructura de archivo Inválida");
                            }
                            // </editor-fold>
                            $lectura = true;
                        }
                        if ($lectura) {
                            if (isset($valores[1])) {
                                // <editor-fold defaultstate="collapsed" desc="Canal">
                                if ($this->limpiar_canal($valores[0]) != "") {
                                    $valores[0] = $this->limpiar_canal($valores[0]);
                                    $canalTemp = $valores[0];
                                } else {
                                    $valores[0] = $canalTemp;
                                }
                                // </editor-fold>
                                // // <editor-fold defaultstate="collapsed" desc="Fecha">
                                if (isset($valores[1]) && $valores[1] != "") {
                                    $valores[1] = $this->formatea_fecha($valores[1]);
                                    $fechaTemp = $valores[1];
                                } else {
                                    $valores[1] = $fechaTemp;
                                }
                                // </editor-fold>
                                // <editor-fold defaultstate="collapsed" desc="descripcion programa">
                                if ($this->limpiar_canal($valores[2]) != "") {
                                    $valores[2] = $this->limpiar_canal($valores[2]);
                                    $descripcionTemp = $valores[2];
                                } else {
                                    $valores[2] = $descripcionTemp;
                                }// </editor-fold>
                                // <editor-fold defaultstate="collapsed" desc="hora inicio">
                                if ($this->limpiar_canal($valores[4]) != "") {
                                    $valores[4] = $this->limpiar_canal($valores[4]);
                                    $horaInicioTemp = $valores[4];
                                } else {
                                    $valores[4] = $horaInicioTemp;
                                }
                                // </editor-fold>
                                // <editor-fold defaultstate="collapsed" desc="hora fin">
                                if ($this->limpiar_canal($valores[5]) != "") {
                                    $valores[5] = $this->limpiar_canal($valores[5]);
                                    $horaFinTemp = $valores[5];
                                } else {
                                    $valores[5] = $horaFinTemp;
                                }
                                // </editor-fold>
                                // <editor-fold defaultstate="collapsed" desc="valores indicadores">
                                $valores[8] = $this->limpiar_canal($valores[8]);        // indicador
                                $valores[9] = $this->limpiar_valores($valores[9]);      //shr %
                                $valores[10] = $this->limpiar_valores($valores[10]);    //amr
                                $valores[11] = $this->limpiar_valores($valores[11]);    //amr %
                                $valores[12] = $this->limpiar_valores($valores[12]);    //rch
                                $valores[13] = $this->limpiar_valores($valores[13]);    //ats
                                // </editor-fold>
                                array_push($buffer, $valores);
                            }
                        }
                    }
                } else {
                    throw new Exception("No se pudo cargar el archivo");
                }
                break;
            case self::tipo_archivo_totales:
                if (isset($archivo)) {
                    foreach ($archivo as $linea) {
                        $valores = explode("|", $linea);
                        if ($this->limpiar_valores($valores[0]) == "Channel") {
                            // <editor-fold defaultstate="collapsed" desc="comprobar estructura de archivo">
                            if (
                                    $this->limpiar_valores($valores[0]) != 'Channel' ||
                                    $this->limpiar_valores($valores[1]) != 'Target' ||
                                    $this->limpiar_valores($valores[3]) != 'Date\Variable' ||
                                    $this->limpiar_valores($valores[4]) != 'SHR %' ||
                                    $this->limpiar_valores($valores[5]) != 'AMR' ||
                                    $this->limpiar_valores($valores[6]) != 'AMR %' ||
                                    $this->limpiar_valores($valores[7]) != 'RCH  [Not cons. - TH: 0min.]' ||
                                    $this->limpiar_valores($valores[8]) != 'ATS'
                            ) {
                                throw new Exception("Estructura de archivo Inválida");
                            }
                            // </editor-fold>

                            $lectura = true;
                        }
                        if ($lectura) {
                            /* Verificando que la linea está completa */
                            if (isset($valores[1])) {
                                // <editor-fold defaultstate="collapsed" desc="Canal">
                                if ($this->limpiar_canal($valores[0]) != "") {
                                    $valores[0] = $this->limpiar_canal($valores[0]);
                                    $canalTemp = $valores[0];
                                } else {
                                    $valores[0] = $canalTemp;
                                }
                                // </editor-fold>
                                // <editor-fold defaultstate="collapsed" desc="Indicador">
                                if ($this->limpiar_canal($valores[1]) != "") {
                                    $valores[1] = $this->limpiar_canal($valores[1]);
                                    $indicadorTemp = $valores[1];
                                } else {
                                    $valores[1] = $indicadorTemp;
                                }
                                // </editor-fold>
                                // <editor-fold defaultstate="collapsed" desc="Fecha">
                                if (isset($valores[3])) {
                                    $valores[3] = $this->formatea_fecha($valores[3]);
                                    $fechaTemp = $valores[3];
                                } else {
                                    $valores[3] = $fechaTemp;
                                }
                                // </editor-fold>
                                // <editor-fold defaultstate="collapsed" desc="Valores indicador">
                                $valores[4] = $this->limpiar_valores($valores[4]);
                                $valores[5] = $this->limpiar_valores($valores[5]);
                                $valores[6] = $this->limpiar_valores($valores[6]);
                                $valores[7] = $this->limpiar_valores($valores[7]);
                                $valores[8] = $this->limpiar_valores($valores[8]);
                                // </editor-fold>
                                array_push($buffer, $valores);
                            }
                        }
                    }
                    /* Elimino la fila de encabezado */
                } else {
                    throw new Exception("No se pudo cargar el archivo");
                }
                break;
            case self::tipo_archivo_medias_horas:
                if (isset($archivo)) {
                    foreach ($archivo as $linea) {
                        $valores = explode("|", $linea);
                        if ($this->limpiar_valores($valores[0]) == "Month") {
                            // <editor-fold defaultstate="collapsed" desc="comprobar estructura de archivo">
                            if (
                                    $this->limpiar_valores($valores[1]) != 'Date' ||
                                    $this->limpiar_valores($valores[2]) != 'Channel' ||
                                    $this->limpiar_valores($valores[3]) != 'Target' ||
                                    $this->limpiar_valores($valores[4]) != 'Day Part\Variable' ||
                                    $this->limpiar_valores($valores[5]) != 'SHR %' ||
                                    $this->limpiar_valores($valores[6]) != 'AMR' ||
                                    $this->limpiar_valores($valores[7]) != 'AMR %' ||
                                    $this->limpiar_valores($valores[8]) != 'RCH  [Not cons. - TH: 0min.]' ||
                                    $this->limpiar_valores($valores[9]) != 'ATS'
                            ) {
                                throw new Exception("Estructura de archivo Inválida");
                            }
                            // </editor-fold>

                            $lectura = true;
                        }
                        if ($lectura) {
                            /* Verificando que la linea está completa */
                            if (isset($valores[1])) {
                                // <editor-fold defaultstate="collapsed" desc="Fecha">
                                if (isset($valores[1]) && $valores[1] != "") {
                                    $valores[1] = $this->formatea_fecha($valores[1]);
                                    $fechaTemp = $valores[1];
                                } else {
                                    $valores[1] = $fechaTemp;
                                }
                                // </editor-fold>
                                // <editor-fold defaultstate="collapsed" desc="Canal">
                                if ($this->limpiar_canal($valores[2]) != "") {
                                    $valores[2] = $this->limpiar_canal($valores[2]);
                                    $canalTemp = $valores[2];
                                } else {
                                    $valores[2] = $canalTemp;
                                }
                                // </editor-fold>
                                // <editor-fold defaultstate="collapsed" desc="Indicador">
                                if ($this->limpiar_canal($valores[3]) != "") {
                                    $valores[3] = $this->limpiar_canal($valores[3]);
                                    $indicadorTemp = $valores[3];
                                } else {
                                    $valores[3] = $indicadorTemp;
                                }
                                // </editor-fold>
                                // <editor-fold defaultstate="collapsed" desc="Valores indicador">
                                $valores[4] = $this->limpiar_valores($valores[4]);
                                $valores[5] = $this->limpiar_valores($valores[5]);
                                $valores[6] = $this->limpiar_valores($valores[6]);
                                $valores[7] = $this->limpiar_valores($valores[7]);
                                $valores[8] = $this->limpiar_valores($valores[8]);
                                $valores[9] = $this->limpiar_valores($valores[9]);
                                // </editor-fold>
                                array_push($buffer, $valores);
                            }
                        }
                    }
                    /* Elimino la fila de encabezado */
                } else {
                    throw new Exception("No se pudo cargar el archivo");
                }
                break;
            default :
                throw new Exception("Operacion invalida");
                break;
        }
        /* Elimino encabezado de tabla */
        unset($buffer[0]);
        return $buffer;
    }

    /**
     * Inserta en tabla temporal los datos formateados de la carga
     * @param array $datos arreglo de datos formateado
     * @param integer $tipo_archivo tipo de archivo a cargar
     * @return mixed resultado de operación
     */
    function insertar_tabla_temporal($datos, $tipo_archivo) {
        $db = new db();
        $query = "";
        switch ($tipo_archivo) {
            case self::tipo_archivo_totales:
                $query.="insert into tmp_carga(canal, indicador, fecha, porcentaje_shr, amr, porcentaje_amr, rch, ats) values ";
                foreach ($datos as $fila) {
                    $sql[] = "('" . strtoupper($fila[0])
                            . "', '" . iconv("iso-8859-1", "UTF-8", strtoupper($fila[1]))
                            . "', '" . $this->formatear_fecha_sql($fila[3])
                            . "', '" . $this->formato_nro_sql($fila[4])
                            . "', '" . $this->formato_nro_sql($fila[5])
                            . "', '" . $this->formato_nro_sql($fila[6])
                            . "', '" . $this->formato_nro_sql($fila[7])
                            . "', '" . $this->formato_nro_sql($fila[8])
                            . "')";
                }
                $query.= implode(",", $sql);
                $result = $db->exec_query($query);
                break;
            case self::tipo_archivo_emisiones:
                $query.="insert into tmp_carga(canal, fecha, programa, hora_inicio, hora_fin, indicador, porcentaje_shr, amr, porcentaje_amr, rch, ats) values ";
                foreach ($datos as $fila) {
                    $sql[] = "('" . strtoupper($fila[0])
                            . "', '" . $this->formatear_fecha_sql($fila[1])
                            . "', '" . iconv("iso-8859-1", "UTF-8", strtoupper($fila[2]))
                            . "', '" . $this->formato_nro_sql($fila[4])
                            . "', '" . $this->formato_nro_sql($fila[5])
                            . "', '" . iconv("iso-8859-1", "UTF-8", strtoupper($fila[8]))
                            . "', '" . $this->formato_nro_sql($fila[9])
                            . "', '" . $this->formato_nro_sql($fila[10])
                            . "', '" . $this->formato_nro_sql($fila[11])
                            . "', '" . $this->formato_nro_sql($fila[12])
                            . "', '" . $this->formato_nro_sql($fila[13])
                            . "')";
                }
                $query.= implode(",", $sql);
                $result = $db->exec_query($query);
                break;
            case self::tipo_archivo_medias_horas:
                $query.="insert into tmp_carga(fecha, canal, indicador, hora_inicio, hora_fin, porcentaje_shr, amr, porcentaje_amr, rch, ats) values ";
                foreach ($datos as $fila) {
                    $hora = explode("-", $fila[4]);
                    $sql[] = "('" . $this->formatear_fecha_sql($fila[1])
                            . "', '" . iconv("iso-8859-1", "UTF-8", strtoupper($fila[2]))
                            . "', '" . iconv("iso-8859-1", "UTF-8", strtoupper($fila[3]))
                            . "', '" . $this->formato_nro_sql($hora[0])
                            . "', '" . $this->formato_nro_sql($hora[1])
                            . "', '" . $this->formato_nro_sql($fila[5])
                            . "', '" . $this->formato_nro_sql($fila[6])
                            . "', '" . $this->formato_nro_sql($fila[7])
                            . "', '" . $this->formato_nro_sql($fila[8])
                            . "', '" . $this->formato_nro_sql($fila[9])
                            . "')";
                }
                $query.= implode(",", $sql);
                $result = $db->exec_query($query);
                break;
            default:
                throw new Exception("Operación Inválida");
                break;
        }
        // <editor-fold defaultstate="collapsed" desc="procesado de tabla temporal y Lote">
        $usuario_id = $_SESSION["usuario"]["ID"];
        $result["sp"] = $db->exec_query("call carga_archivo($usuario_id,$tipo_archivo)");
        $lote = $db->dame_query("SELECT * FROM lote where usuario_id = $usuario_id and proceso_id = $tipo_archivo ORDER BY id DESC LIMIT 1");
        // </editor-fold>

        return $lote;
    }

}

?>
