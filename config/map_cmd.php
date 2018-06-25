<?php
$map_cmd = array(
    'server' => array(
        'logic' => "ServerLogic",
        'cmd' => array(
            'quit'       => "spegni il server",
            'list_dispo' => "lista dei dispositivi",
            'list_user'  => "lista degli user",
            'logout_user'=> "logout di uno user",
            'WAIT'       => "dispositivo occupato",
            'READY'      => "dispositivo pronto",
            '.'          => "keepalive del dispositivo",
            'MAIL'       => "dispositivo richiede di mandare una mail al server",
        ),
        'r_cmd' => array(
            'quit'       => "quit",
            'list_dispo' => "list_dispo",
            'list_user'  => "list_user",
        ),
        'heder' => array(),
        'campo' => array(),
    ),
    'compact' => array(
        'logic' => "CompactLogic",
        'cmd' => array(
            '**iw' => "scrivi identificativo",
            '**nw' => "scrivi città",
            '**jw' => "scrivi via",
            '**kw' => "scrivi chilometro",
            '**lw' => "scrivi direzione avanti",
            '**mw' => "scrivi direzione indietro",
            '||jw' => "scrivi counting",
            '||ww' => "scrivi data sistema",
            '||vw' => "scrivi orario sistema",
            '||nr' => "leggi correzione avanti",
            '||or' => "leggi correzione dietro",
            '||nw' => "scrivi correzione avanti",
            '||ow' => "scrivi correzione dietro",
            '||mw' => "scrivi sensibilità radar",   //solo qui
            '**ur' => "leggi configurazione di sistema",//solo qui
            '||lr' => "leggi voltaggio batteria",
            '**vw' => "tag site per file xml",
            '**ww' => "tag point per file xml",
        ),    //tutti i comandi che verranno accettati
        'w_cmd' => array(
            "id_dispo"          => '**iw',
            "citta"             => '**nw',
            "via"               => '**jw',
            "rif_km"            => '**kw',
            "dir_avanti"        => '**lw',
            "dir_dietro"        => '**mw',
            "counting"          => '||jw',
            "giorno"            => '||ww',
            "ora"               => '||vw',
            'corr_avanti'       => "||nw",
            'corr_dietro'       => "||ow",
            "amplificazione"    => '||mw',
            "site"              => '**vw',
            "point"             => '**ww',
        ),  //tutti i comandi di scrittura
        'r_cmd' => array(
            "volt_batteria" => '||lr',
            "corr_avanti"   => '||nr',
            "corr_dietro"   => '||or',
            "config"        => '**ur',
        ),  //tutti i comandi di lettura
        'heder' => array(
            'bios' => "versione bios",
            '**iw' => "identificativo",
            '**nw' => "città",
            '**jw' => "via",
            '**kw' => "chilometro",
            '**lw' => "direzione avanti",
            '**mw' => "direzione indietro",
            '||jw' => "counting",
            '||ww' => "data sistema",
            '||vw' => "orario sistema",
            '||mr' => "amplificazione",
            '||nw' => "correzione avanti",
            '||ow' => "correzione dietro",
            '||lr' => "voltaggio batteria",
            '**vw' => "site",
            '**ww' => "point",
        ),  //heder del campo
        'campo' => array(
            'v_bios' => "v_bios",
            '**iw' => "id_dispo",
            '**nw' => "citta",
            '**jw' => "via",
            '**kw' => "rif_km",
            '**lw' => "dir_avanti",
            '**mw' => "dir_dietro",
            '||jw' => "counting",
            '||ww' => "giorno",
            '||vw' => "ora",
            '||mw' => "amplificazione",
            '||nw' => "corr_avanti",
            '||ow' => "corr_dietro",
            '||lr' => "volt_batteria",
            '**vw' => "site",
            '**ww' => "point",),  //i nomi dei campi del DB
    ),
    'boe' => array(
        'logic' => "BoeLogic",
        'cmd' => array(
            '**iw' => "scrivi identificativo",
            '**nw' => "scrivi città",
            '**jw' => "scrivi via",
            '**kw' => "scrivi chilometro",
            '**lw' => "scrivi direzione avanti",
            '**mw' => "scrivi direzione indietro",
            '||jw' => "scrivi n_corsie",
            '||ww' => "scrivi data sistema",
            '||vw' => "scrivi orario sistema",
            '||nr' => "leggi distanza boe F1",
            '||or' => "leggi distanza boe B1",
            '||nw' => "scrivi distanza boe F1",
            '||ow' => "scrivi distanza boe B1",
            '||mw' => "scrivi sensibilità radar",  //!!!!!!!!!!
            '**ur' => "leggi configurazione di sistema",//solo qui
            '||lr' => "leggi voltaggio batteria",
            '**vw' => "tag site per file xml",
            '**ww' => "tag point per file xml",
        ),
        'w_cmd' => array(
            "id_dispo"          => '**iw',
            "citta"             => '**nw',
            "via"               => '**jw',
            "rif_km"            => '**kw',
            "dir_avanti"        => '**lw',
            "dir_dietro"        => '**mw',
            "n_corsie"          => '||jw',
            "giorno"            => '||ww',
            "ora"               => '||vw',
            'distanza_boe_F1'       => "||nw",
            'distanza_boe_B1'       => "||ow",
//            "amplificazione"    => '||mw', //!!!!!!!!!!
            "site"              => '**vw',
            "point"             => '**ww',
        ),
        'r_cmd' => array(
            "volt_batteria" => '||lr',
            "distanza_boe_F1"   => '||nr',
            "distanza_boe_B1"   => '||or',
            "config"        => '**ur',
        ),
        'heder' => array(
            'bios' => "versione bios",
            '**iw' => "identificativo",
            '**nw' => "città",
            '**jw' => "via",
            '**kw' => "chilometro",
            '**lw' => "direzione avanti",
            '**mw' => "direzione indietro",
            '||jw' => "n_corsie",
            '||ww' => "data sistema",
            '||vw' => "orario sistema",
//            '||mr' => "amplificazione",//!!!!!!!!!!!
            '||nw' => "distanza boe F1",
            '||ow' => "distanza boe B1",
            '||lr' => "voltaggio batteria",
            '**vw' => "site",
            '**ww' => "point",
        ), //non c'è su cmd
        'campo' => array(
            'v_bios' => "v_bios",
            '**iw' => "id_dispo",
            '**nw' => "citta",
            '**jw' => "via",
            '**kw' => "rif_km",
            '**lw' => "dir_avanti",
            '**mw' => "dir_dietro",
            '||jw' => "n_corsie",
            '||ww' => "giorno",
            '||vw' => "ora",
//            '||mw' => "amplificazione",//TODO: da togliere
            '||nw' => "distanza_boe_F1",
            '||ow' => "distanza_boe_B1",
            '||lr' => "volt_batteria",
            '**vw' => "site",
            '**ww' => "point",
        ),
    ),
    'pmv' => array(
        'logic' => "PmvLogic",
        'cmd' => array(
            '*ura' => "leggi configurazione di sistema",//!!
            '*iw' => "scrivi identificativo",//max 8 char
            '*dw' => "scrivi data sistema",
            '*tw' => "scrivi orario sistema",

            '*ew' => "messaggi in sequenza max",
            '*hw' => "attivazione msg specifico solo se msg max=0",
            '*cw' => "num spazi separatori ultima riga",
            '*ww' => "ritardo scroll ultima riga",
            '*mw' => "orario spegnimento sistema",
            '*nw' => "orario accensione sistema",
            '*qwj' => "inserimento point",
            '*qwk' => "inserimento site",
            '*qwl' => "inserimento password wifi",
            '*qwh' => "inserimento luminosità minima",
            '*qwi' => "inserimento amplificazione luminosità",

            '*#' => "inserimento/modifica messaggio",
            '*gr99'=> "lettura di tutti i messaggi",
        ),
        'w_cmd' => array(
            "scrivi identificativo"                          => '*iw' ,
            "scrivi data sistema"                            => '*dw' ,
            "scrivi orario sistema"                          => '*tw' ,
            "messaggi in sequenza max"                       => '*ew' ,
            "attivazione msg specifico solo se msg max=0"    => '*hw' ,
            "num spazi separatori ultima riga"               => '*cw' ,
            "ritardo scroll ultima riga"                     => '*ww' ,
            "orario spegnimento sistema"                     => '*mw' ,
            "orario accensione sistema"                      => '*nw' ,
            "inserimento point"                              => '*qwj',
            "inserimento site"                               => '*qwk',
            "inserimento password wifi"                      => '*qwl',
            "inserimento luminosità minima"                  => '*qwh',
            "inserimento amplificazione luminosità"          => '*qwi',

            "inserimento/modifica messaggio"                 => '*#',
        ),
        'r_cmd' => array(
            "config"      => '*ura' ,
            "read_msgs"   => '*gr99' , //!!
            "read_msg"    => '*gr' ,   //Con il numero del messaggio legge solo quello
            //TODO: quali sono i campi da salvare per ogni msg?
        ),
        'heder' => array(),
        'campo' => array(   //i campi che arrivano da GENERAL SETTING
            'v_bios'        => "v_bios",//si può solo leggere
            '*dw'           => "giorno",
            '*tw'           => "ora",
            'volt_batteria' => "volt_batteria",
            '*ew'           => "max_sequenza_msg",
            "num_msg_attivo"=> "num_msg_attivo",
            '*cw'           => "num_blank_last_msg",
            'ritardo_scroll'=> "ritardo_scroll",
            '*iw'           => "id_dispo",
            '*mw'           => "ora_off_pmv",
            '*nw'           => "ora_on_pmv",
            '--.-'          => "temperatura",
            "num_colonne"   => "num_colonne",
            "num_righe"     => "num_righe",
            '*qwj'          => "point",
            '*qwk'          => "site",
            'wifi'          => "pwd_wifi",
            'min_l'         => "min_luminosita",
            'ampl_l'        => "ampl_luminosita",
        ),
    ),
    'apricancello' => array(
        'logic' => "ApricancelloLogic",
        'cmd' => array(
            '**ur' => "leggi configurazione di sistema",//solo qui
        ),
        'w_cmd' => array(),
        'r_cmd' => array(
            "config"        => '**ur',
        ),
        'heder' => array(),
        'campo' => array(
            'v_bios' => "v_bios"
        ),
    ),


    'pedestrian' => array(
        'logic' => "CompactLogic",
        'cmd' => array(),
        'w_cmd' => array(),
        'r_cmd' => array(),
        'heder' => array(),
        'campo' => array(),
    ),
);




