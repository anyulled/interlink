<?php

require_once 'db.php';

/**
 * Clase para paginar registros
 * @author Anyul Rivas
 */
class paginacion extends Misc {

    public $maxRows;
    /**
     * consulta sql
     * @var String
     */
    public $query;
    /**
     * Registros paginados
     * @var mixed
     */
    public $registros;
    public $totalRows;
    public $currentPage;
    public $localhost;
    public $db;
    private $debug;
    private $pageNum;
    private $totalPages;

    function __construct($debug = false) {
        set_error_handler("Misc::error_handler");
        $this->db = new db();
        $this->debug = $debug;
    }

    /**
     * Realiza el paginado de una consulta
     * @param String $query consulta sql
     * @param Integer $MaxRows elementos por pagina
     */
    public function paginar($query, $MaxRows = 10) {
        $this->maxRows = $MaxRows;
        $this->query = $query;
        if ($this->query != "") {
            $this->currentPage = $_SERVER["PHP_SELF"];
            $this->pageNum = 0;
            $this->totalRows = 0;
            if (isset($_GET['pageNum'])) {
                $this->pageNum = $_GET['pageNum'];
            }
            $this->startRow = $this->pageNum * $this->maxRows;

            if (isset($_GET['order']) && isset($_GET['dir'])) {
                $this->query.=" order by {$_GET['order']} {$_GET['dir']}";
            } else {
                $this->query .=" order by 1 asc";
            }

            $this->limit = sprintf("%s LIMIT %d, %d", $this->query, $this->startRow, $this->maxRows);

            $this->registros_limit = $this->db->dame_query($this->limit);
            $this->registros = $this->registros_limit['data'];
            if (isset($_GET['totalRows'])) {
                $this->totalRows = $_GET['totalRows'];
            } else {
                $this->registrosTotales = $this->db->dame_query($this->query);
                if ($this->registrosTotales['suceed']) {
                    $this->totalRows = $this->registrosTotales['stats']['affected_rows'];
                }
            }
            if ($this->maxRows > 0) {
                $this->totalPages = ceil($this->totalRows / $this->maxRows) - 1;
            }

            $this->queryString = "";
            if (!empty($_SERVER['QUERY_STRING'])) {
                $this->params = explode("&", $_SERVER['QUERY_STRING']);
                $newParams = array();
                foreach ($this->params as $param) {
                    if (stristr($param, "pageNum") == false &&
                            stristr($param, "totalRows") == false) {
                        array_push($newParams, $param);
                    }
                }
                if (count($newParams) != 0) {
                    $this->queryString = "&" . htmlentities(implode("&", $newParams));
                }
            }
            $this->queryString = sprintf("&totalRows=%d%s", $this->totalRows, $this->queryString);
        } else {
            echo "<h1>El query está vacío,</h1>";
        }
    }

    /**
     * Muestra el paginado en una tabla
     * @param Integer $cellpadding espaciado entre las celdas y su contenido
     * @param Integer $cellspacing espaciado de las celdas de la tabla
     * @param Integer $width ancho de la tabla
     * @param Integer $border borde de la tabla
     */
    public function mostrar_paginado($cellpadding = 0, $cellspacing = 0, $width = 100, $border = 0) {
        $numero_pagina = $this->pageNum + 1;
        $total_paginas = $this->totalPages + 1;
        // <editor-fold defaultstate="collapsed" desc="Links">
        $primero = " <a href='" . $this->currentPage . "?pageNum=0" . $this->queryString . "'>&laquo; Primero </a>";
        $anterior = " <a href='" . $this->currentPage . "?pageNum=" . max(0, $this->pageNum - 1) . $this->queryString . "'>&lsaquo; Anterior </a> ";
        $siguiente = " <a href='" . $this->currentPage . "?pageNum=" . min($this->totalPages, $this->pageNum + 1) . $this->queryString . "'> Siguiente &rsaquo;</a> ";
        $ultimo = " <a href='" . $this->currentPage . "?pageNum=" . $this->totalPages . $this->queryString . "'>&Uacute;ltimo &raquo;</a>";
        // </editor-fold>
        // <editor-fold defaultstate="collapsed" desc="tabla">
        $html = "<table class='full no_border' border='" . $border . "' cellpadding='" . $cellpadding . "' cellspacing='" . $cellspacing . "' width='" . $width . "%'>

    <tr>
        <td>";
        if ($this->pageNum > 0) {
            $html.= $primero;
        }
        $html.="</td>
        <td>";
        if ($this->pageNum > 0) {
            $html.=$anterior;
        }
        $html.="</td> 
        <td> Página " . $numero_pagina . " de " . $total_paginas . " </td>
        <td>";
        if ($this->pageNum < $this->totalPages) {
            $html.= $siguiente;
        }
        $html.="</td>
        <td>";
        if ($this->pageNum < $this->totalPages) {
            $html.= $ultimo;
        }
        $html.="</td>
    </tr>
</table>";
        // </editor-fold>

        if (count($this->registros) > 0) {
            echo $html;
        }
    }

    /**
     * Muestra el paginado como una lista de elementos.
     * @param boolean $mostrar_paginas especifica si se muestran los links hacia las paginas en especifico o si se muestra un indicador de página actual y paginas totales
     */
    public function mostrar_paginado_lista($mostrar_paginas = true) {
        $numero_pagina = $this->pageNum + 1;
        $total_paginas = $this->totalPages + 1;
        // <editor-fold defaultstate="collapsed" desc="Links">
        $primero = " <a href='" . $this->currentPage . "?pageNum=0" . $this->queryString . "'>&laquo; Primero </a>";
        $anterior = " <a href='" . $this->currentPage . "?pageNum=" . max(0, $this->pageNum - 1) . $this->queryString . "'>&lsaquo; Anterior </a> ";
        $siguiente = " <a href='" . $this->currentPage . "?pageNum=" . min($this->totalPages, $this->pageNum + 1) . $this->queryString . "'> Siguiente &rsaquo;</a> ";
        $ultimo = " <a href='" . $this->currentPage . "?pageNum=" . $this->totalPages . $this->queryString . "'>&Uacute;ltimo &raquo;</a>";
        // </editor-fold>
        // <editor-fold defaultstate="collapsed" desc="lista">
        $html = "<div class='pagination'><ul>";
        if ($this->pageNum > 0) {
            $html.="<li>";
            $html.= $primero;
            $html.="</li>";
        }
        if ($this->pageNum > 0) {
            $html.="<li>";
            $html.=$anterior;
            $html.="</li>";
        }
        if (!$mostrar_paginas) {
            $html.="<li class='disabled'><a href='#'> Página " . $numero_pagina . " de " . $total_paginas . " </a></li>";
        } else {
            for ($i = 1; $i <= $total_paginas; $i++) {
                $status = ($numero_pagina == $i) ? "disabled" : "";
                $link = $this->currentPage . "?pageNum=" . ($i - 1) . $this->queryString;
                $html.="<li class='" . $status . "'><a href='$link'>" . $i . " </a></li>";
            }
        }
        if ($this->pageNum < $this->totalPages) {
            $html.="<li>";
            $html.= $siguiente;
            $html.="</li>";
        }
        if ($this->pageNum < $this->totalPages) {
            $html.="<li>";
            $html.= $ultimo;
            $html.="</li>";
        }
        $html.="</ul></div>";
        // </editor-fold>

        if (count($this->registros) > 0) {
            return $html;
        }
    }

    public function __destruct() {
        unset($this->db);
        restore_error_handler();
    }

}
