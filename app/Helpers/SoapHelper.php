<?php

namespace App\Helpers;

class SoapHelper
{
    public static function parseSoapRequest($xml, $fields)
    {
        $parsedData = [];
        foreach($fields as $field) 
        {
            $node = $xml->xpath("//{$field}");
            $parsedData[$field] = isset($node[0]) ? (string)$node[0] : null;
        }

        return $parsedData;
    }

    public static function soapSuccessResponse($responseName, $data)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">';
        $xml .= '<SOAP-ENV:Body>';
        $xml .= "<{$responseName}>";

        // Fungsi rekursif untuk data bersarang
        $xml .= self::arrayToXml($data);

        $xml .= "</{$responseName}>";
        $xml .= '</SOAP-ENV:Body>';
        $xml .= '</SOAP-ENV:Envelope>';

        return $xml;
    }

    private static function arrayToXml($data)
    {
        $xml = '';
        foreach($data as $key => $value) 
        {
            if(is_array($value)) 
            {
                $xml .= "<{$key}>" . self::arrayToXml($value) . "</{$key}>";
            }else 
            {
                $xml .= "<{$key}>{$value}</{$key}>";
            }
        }

        return $xml;
    }

    public static function soapFaultResponse($faultCode, $faultString)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' .
            '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">' .
            '<SOAP-ENV:Body>' .
            '<SOAP-ENV:Fault>' .
            '<faultcode>' . $faultCode . '</faultcode>' .
            '<faultstring>' . $faultString . '</faultstring>' .
            '</SOAP-ENV:Fault>' .
            '</SOAP-ENV:Body>' .
            '</SOAP-ENV:Envelope>';

        $responseCode = ($faultCode == 'Client') ? 400 : 500;

        return response()->make($xml, $responseCode, ['Content-Type' => 'application/soap+xml']);
    }

}