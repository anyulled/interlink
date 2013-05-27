<?php

class GenerateReport {

//Properties
    private $_wsdlURL;
    private $_username;
    private $_password;
    private $_soapClient;
    private $_imageFolder;
    private $_reportPath;
    private $_reportName;
    private $_outputFormat;
    private $_parameterArray;

//Constructor
    public function __construct() {
        $config = parse_ini_file('config-jasper.ini', true);
        $this->_wsdlURL = str_replace("\\", "/", dirname(__FILE__)) . '/' . $config['Jasper']['wsdlURL'];
        $this->_username = $config['Jasper']['username'];
        $this->_password = $config['Jasper']['password'];
        $this->_imageFolder = str_replace("\\", "/", dirname(dirname(__FILE__))) . '/public/images/';
        try {
            $this->_soapClient = new SOAPClient(null, array(
                        "location" => $this->_wsdlURL,
                        "uri" => "urn:",
                        'login' => $this->_username,
                        'password' => $this->_password,
                        'trace' => 1,));
        } catch (Exception $e) {
            throw $e;
        }
    }

//Methods
    public function printReport($reportPath, $reportName, $outputFormat = "HTML", $parameterArray = "") {
        $this->_reportPath = $reportPath;
        $this->_reportName = $reportName;
        $this->_outputFormat = $outputFormat;
        $this->_parameterArray = $parameterArray;

        $requestXML = "<request operationName=\"runReport\">";
        $requestXML .= "<argument name=\"RUN_OUTPUT_FORMAT\">$outputFormat</argument>";
        $requestXML .= "<resourceDescriptor name=\"\" wsType=\"reportUnit\" uriString=\"$reportPath$reportName\" isNew=\"false\">";
        $requestXML .= "<label></label>";
        foreach ($parameterArray as $key => $value) {
            $requestXML .= "<parameter name=\"$key\"><![CDATA[$value]]></parameter>";
        }
        $requestXML .= "</resourceDescriptor></request>";
        $params = array("request" => $requestXML);

        $reportOutput = "";
        try {
            $response = $this->_soapClient->runReport($requestXML);
            $reportOutput = $this->parseResponseWithReportData(
                    $this->_soapClient->__getLastResponseHeaders(), $this->_soapClient->__getLastResponse(), $outputFormat
            );
        }//end of try
        catch (SoapFault $e) {
            if ($e->faultstring == 'looks like we got no XML document') {
                $reportOutput = $this->parseResponseWithReportData(
                        $this->_soapClient->__getLastResponseHeaders(), $this->_soapClient->__getLastResponse(), $outputFormat
                );
            }//end of if
            else {
                throw new Exception("Error Creating Report " . $e->faultstring);
            }//end of else
        }//end of catch
        return $reportOutput;
    }

//end of function

    private function parseResponseWithReportData($responseHeaders, $response, $outputFormat) {
        preg_match('/boundary="(.*?)"/', $responseHeaders, $matches);
        $boundary = $matches[1];
        $parts = explode($boundary, $response);
        $reportOutput = "";
        switch ($outputFormat) {
            case 'HTML':
                foreach ($parts as $part) {
                    if (strpos($part, "Content-Type: image/png") !== false) {
                        $start = strpos($part, "<") + 1;
                        $length = (strpos($part, ">") - $start);
                        $filename = substr($part, $start, $length) . '.png';
                        $file = fopen("$this->_imageFolder$filename", "wb");
                        $contentStart = strpos($part, "PNG") - 1;
                        $contentLength = (strpos($part, "--") - $contentStart) + 1;
                        $contents = substr($part, $contentStart, $contentLength);
                        fwrite($file, $contents);
                        fclose($file);
                    }
                    if (strpos($part, "Content-Type: image/gif") !== false) {
                        $start = strpos($part, "<") + 1;
                        $length = (strpos($part, ">") - $start);
                        $filename = substr($part, $start, $length) . '.gif';
                        $file = fopen("$this->_imageFolder$filename", "wb");
                        $contentStart = strpos($part, "GIF");
                        $contentLength = (strpos($part, "--") - $contentStart) + 1;
                        $contents = substr($part, $contentStart, $contentLength);
                        fwrite($file, $contents);
                        fclose($file);
                    }
                    if (strpos($part, "Content-Type: text/html") !== false) {
                        $contentStart = strpos($part, '<html>');
                        $contentLength = (strpos($part, '</html>') - $contentStart) + 7;
                        $reportOutput = substr($part, $contentStart, $contentLength);
                    }
                }//end of for each
                break;
            case 'PDF':
                foreach ($parts as $part) {
                    if (strpos($part, "Content-Type: application/pdf") !== false) {
                        $reportOutput = substr($part, strpos($part, '%PDF-'));
                        break;
                    }
                } //end of foreach
                break;
            case 'XLS':
                foreach ($parts as $part) {
                    if (strpos($part, "Content-Type: application/xls") !== false) {
                        $reportOutput = substr($part, (strpos($part, '<report>') + 9));
                        break;
                    }
                }
            case 'CSV':
                foreach ($parts as $part) {
                    if (strpos($part, "Content-Type: application/vnd.ms-excel") !== false) {
                        $contentStart = strpos($part, 'Content-Id: <report>') + 24;
                        $reportOutput = substr($part, $contentStart);
                        break;
                    }
                }
        }
        return $reportOutput;
    }

//end of function
}

//end of class
?>