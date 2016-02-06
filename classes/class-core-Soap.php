<?php
class HW_Soap{
    //all functions in one file
    public static function generate_xml($url=''){
        $xml='<?xml version="1.0"?>';
        $xml.='<definitions xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" xmlns:tns="urn:ksn" xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" xmlns="http://schemas.xmlsoap.org/wsdl/" targetNamespace="urn:ksn" debug="true">';
        $xml.='<types></types>';

        $data=array();	//array data of soap
        //get user defined functions
        $user_funcs=get_defined_functions();

        foreach($user_funcs['user'] as $func){
            //prepare message
            $soap='<message name="'.$func.'Request"/>';
            //$soap.='<part name="parameters" type="xsd:*"/>';
            //$soap.='</message>';
            $soap.='<message name="'.$func.'Response">';
            $soap.='<part name="return" type="xsd:*"/>';
            $soap.='</message>';
            $data['message'][]=$soap;	//add message for func
            //porttype
            $soap='<operation name="'.$func.'">';
            $soap.='<input message="tns:'.$func.'Request"/>';
            $soap.='<output message="tns:'.$func.'Response"/>';
            $soap.='</operation>';
            $data['portType'][]=$soap;	//add portType for func
            //binding
            $soap='<operation name="'.$func.'">';
            $soap.='<soap:operation soapAction="/'.$func.'" style="rpc"/>';
            $soap.='<input>';
            $soap.='<soap:body use="encoded" namespace="" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>';
            $soap.='</input>';
            $soap.='<output>';
            $soap.='<soap:body use="encoded" namespace="" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>';
            $soap.='</output>';
            $soap.='</operation>';
            $data['binding'][]=$soap;	//add binding for func
        }
        foreach($data as $key=>$val){
            switch($key){
                case 'portType': $xml.='<portType name="ksnPortType">'.join(PHP_EOL,$val).'</portType>';break;
                case 'binding':
                    $xml.='<binding name="ksnBinding" type="tns:ksnPortType"><soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http"/>'.join(PHP_EOL,$val).'</binding>';

                    break;
                default:$xml.=join(PHP_EOL,$val);
            }
        }
        //service element
        $xml.='<service name="ksn">';
        $xml.='<port name="ksnPort" binding="tns:ksnBinding">';
        $xml.='<soap:address location="'.str_replace('&','&amp;',$url).'"/>';//'..'
        $xml.='</port>';
        $xml.='</service>';
        $xml.='</definitions>';
        return $xml;
    }
}


?>