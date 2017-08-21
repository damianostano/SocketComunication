<?php

/**
 * Created by PhpStorm.
 * User: Sisas
 * Date: 21/02/2017
 * Time: 11:55
 */
class DecodeResponse{

    private $idMsg;
    private $response;

    /**
     * DecodeResponse constructor.
     * @param $idMsg
     * @param $response
     */
    public function __construct(string $idMsg, string $response){
        if($idMsg==null)
            throw new Exception("Non si può creare una DecodeResponse senza idMsg!");
        if($response==null)
            throw new Exception("Non si può creare una DecodeResponse senza response!");
        $this->idMsg = $idMsg;
        $this->response = $response;
    }


    /**
     * @return string
     */
    public function getIdMsg() : string{
        return $this->idMsg;
    }

    /**
     * @return string
     */
    public function getResponse() : string{
        return $this->response;
    }



}