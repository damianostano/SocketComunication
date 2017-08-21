<?php

/**
 * Created by PhpStorm.
 * User: Sisas
 * Date: 21/02/2017
 * Time: 12:25
 */

class DecodeConMsg{

    const ERR  = "E";
    const USER = "U";
    const DISP = "D";

    private $type;
    private $val;

    /**
     * DecodeResponse constructor.
     * @param $type
     * @param $val
     */
    public function __construct(string $type, string $val){
        if($type==null)
            throw new Exception("Non si può creare una DecodeResponse senza type!");
        if($val==null)
            throw new Exception("Non si può creare una DecodeResponse senza val!");
        $this->type = $type;
        $this->val = $val;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }


    /**
     * @return string
     */
    public function getVal(): string
    {
        return $this->val;
    }


    public function isERR() : bool {
        return $this->getType()===DecodeConMsg::ERR;
    }
    public function isUSER() : bool {
        return $this->getType()===DecodeConMsg::USER;
    }
    public function isDISP() : bool {
        return $this->getType()===DecodeConMsg::DISP;
    }


}