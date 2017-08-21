<?php

/**
 * Created by PhpStorm.
 * User: Sisas
 * Date: 28/02/2017
 * Time: 12:16
 */
class CmdDispo
{
//    private $start=null;
    private $comando=null;
//    private $valore=null;
    private $subCmd=null;
    private $param=null;
    private $end="\r";

    private $validateFunction=null;

    /**
     * CmdDispo constructor.
     * @param null $start
     * @param null $comando
     * @param null $azione
     * @param null $subCmd
     * @param null $param
     * @param string $end
     */
    public function __construct($comando, /*$start="||",*/$validateFunction=null ,$subCmd="", $param="", $end="\r"){
        if($comando===null || $comando===""){
            throw new Exception("Errore: comando non può essere vuoto!");
        }
        $this->comando = $comando;
//        $this->start = $start;
        $this->subCmd = $subCmd;
        $this->param = $param;
        $this->end = $end;
        if($validateFunction===null){
            $this->validateFunction = function($value){return true;};
        }else{
            $this->validateFunction = $validateFunction;
        }
    }

    /**
     * @return null|string
     */
//    public function getStart(): string{
//        return $this->start;
//    }

    /**
     * @return null
     */
    public function getComando(): string
    {
        return $this->comando;
    }

    /**
     * @return null
     */
    public function getSubCmd(): string
    {
        return $this->subCmd;
    }

    /**
     * @return null
     */
    public function getParam(): string
    {
        return $this->param;
    }

    /**
     * @return string
     */
    public function getEnd(): string
    {
        return $this->end;
    }



    public function buildStringCmd(string $value=null): string{
        if($value===null){
            $value="";
        }else{
            $validateFunction = ($this->validateFunction!=null?$this->validateFunction:function($value){return true;});
            $validate = $validateFunction($value);
            if($validate!==true)
                throw  new Exception("Errore di validazione parametro comando: ".$validate);
        }

        return  //$this->getStart().
                $this->getComando().
                $value.
                $this->getSubCmd().
                $this->getParam().
                $this->getEnd();
    }



}