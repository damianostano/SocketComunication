<?php

/**
 * Created by PhpStorm.
 * User: Sisas
 * Date: 21/02/2017
 * Time: 11:16
 */

interface Decode{

    const CMD = 0;
    const ID_DISPO = 1;

    const ID_MSG = 0;
    const RESPONSE = 1;

    /**
     * Decodifica il messaggio per fare la connessione con gli user e i Dispositivi
     * @param string $msg
     * @return DecodeConMsg
     */
    public function decode_connection_msg(string $msg) : DecodeConMsg;

    /**
     * Decodifica il comando in arrivo per spacchettarlo in comando e id_dispositivo a cui inviarlo
     * @param string $string_cmd
     * @return DecodeCmd
     * @throws Exception if operation fail
     */
    public function decodeCmd(string $string_cmd) : DecodeCmd;

    /**
     * Decodifica la risposta in arrivo per spacchettarla in id_messaggio e messaggio_di_risposta
     * @param string $response_msg
     * @return array
     * @throws Exception if operation fail
     */
    public function decodeResponse(string $response_msg) : DecodeResponse;

    /**
     * Per capire se un messaggio dal dispositivo è una risposta o altro
     * @param string $msg
     * @return bool true se è una risposta
     */
    public function isResponse(string $msg): bool;

}