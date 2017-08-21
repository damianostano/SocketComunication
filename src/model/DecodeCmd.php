<?php

/**
 * Created by PhpStorm.
 * User: Sisas
 * Date: 21/02/2017
 * Time: 11:54
 */
class DecodeCmd
{
    private $cmd;
    private $idDispo;

    /**
     * DecodeCmd constructor.
     * @param $cmd
     * @param $idDispo
     */
    public function __construct(string $cmd, string $idDispo){
        if($cmd==null)
            throw new Exception("Non si può creare un DecodeCmd senza cmd!");
        if($idDispo==null)
            throw new Exception("Non si può creare un DecodeCmd senza idDispo!");
        $this->cmd = $cmd;
        $this->idDispo = $idDispo;
    }


    /**
     * @return string
     */
    public function getCmd() : string
    {
        return $this->cmd;
    }

    /**
     * @return string
     */
    public function getIdDispo() : string
    {
        return $this->idDispo;
    }



}