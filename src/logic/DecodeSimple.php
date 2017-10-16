<?php

/**
 * Created by PhpStorm.
 * User: Sisas
 * Date: 21/02/2017
 * Time: 11:59
 */
class DecodeSimple implements Decode{


    public function decode_connection_msg(string $msg): DecodeConMsg {// "login*1234" "login*user&&root"
        $msg_array = explode("*", trim($msg));
        if($msg_array[0]==="login"){
            $response = array("type"=>null, "val"=>null);

            $user_disp = explode("&&", trim($msg_array[1]));
            if(      count($user_disp)==1){ //login dispositivo
                if(preg_match("/^.*$/", $user_disp[0])){ //  "/^\d{4}$/"
                    $response["type"] = DecodeConMsg::DISP;
                    $response["val"] = $user_disp[0];
                }else{
                    $response["type"] = DecodeConMsg::ERR;
                    $response["val"] = "Tentativo di login dispositivo non riuscito. ID dispositivo non conforme: ".$user_disp[0];
                }
            }else if(count($user_disp)>1){  //login utente
                if($user_disp[0]==="user"){
                    $response["type"] = DecodeConMsg::USER;
                    $response["val"] = $user_disp[1];
                }else{
                    $response["type"] = DecodeConMsg::ERR;
                    $response["val"] = "Tentativo di login non conforme allo standard: ".$msg;
                }
            }else{
                $response["type"] = DecodeConMsg::ERR;
                $response["val"] = "Attenzione! Tentativo di login non conforme allo standard: ".$msg;
            }
        }else{
            $response["type"] = DecodeConMsg::ERR;
            $response["val"] = "Rifiutata tentata connessione. Messaggio di registrazione non conforme!";
        }
        return new DecodeConMsg($response["type"], $response["val"]);
    }

    public function decodeCmd(string $string_cmd): DecodeCmd{//es:  "example_cmd@@id_disp" il comando deve essere impartito al dispositivo id_disp
        if($string_cmd==null)
            return null;
        $msg_array = explode("@@", trim($string_cmd));//il comando verrà poi convertito in id_comando@@example_cmd e impilato nella coda di comandi del dispositivo id_disp
        if(count($msg_array)==2)
            return new DecodeCmd($msg_array[0], $msg_array[1]);
        else
            throw new Exception("Stringa non decodificabile in comando!");
    }

    public function decodeResponse(string $response_msg): DecodeResponse{//es:  "id_msg@@response" la risposta deve essere data allo user del messaggio id_msg
        if($response_msg==null)
            return null;
        $msg_array = explode("@@", trim($response_msg));
        if(count($msg_array)==2)
            return new DecodeResponse($msg_array[0], $msg_array[1]);
        else
            throw new Exception("Stringa non decodificabile in risposta!");
    }

    public function isResponse(string $msg): bool{
        if($msg==null)
            return false;
        return (preg_match("/^\\d\\d\\d\\d/", $msg)==1?true:false);
    }

}