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




