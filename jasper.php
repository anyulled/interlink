<?php

// <editor-fold defaultstate="collapsed" desc="jasper">
require 'includes/JasperClient.php';
//require 'includes/generateReport.php';
$jasper_url = "http://localhost:8080/jasperserver/services/repository";
$jasper_username = "jasperadmin";
$jasper_password = "jasperadmin";
$client = new JasperClient($jasper_url, $jasper_username, $jasper_password);
//$report_unit = "/reports/samples/SalesByMonth"; //reports/samples/SalesByMonth
$report_unit = "/reports/interlink/ReporteSocioDemografico"; //reports/samples/SalesByMonth
$report_format = "PDF";
$report_params = array(
    'CANALES' => '3,4,7,18,20,21,5,38',
    'FECHA_INI_MOSTRAR' => '01-09-2011',
    'FECHA_FIN_MOSTRAR' => "01-09-2011",
    'FECHA_INI' => '2011-09-01',
    'FECHA_FIN' => '2011-09-01',
    'INDICADORES' => 1,
    'PERFILES' => '45,46,47'
);
try {
    $result = $client->requestReport($report_unit, $report_format, $report_params);
    if ($report_format == "PDF") {
        header('Content-type: application/pdf');
    }
    echo $result;
} catch (Exception $exc) {
    echo $exc->getMessage();
    echo "<pre>";
    echo $exc->getTraceAsString();
    echo "</pre>";
}

// </editor-fold>
?>