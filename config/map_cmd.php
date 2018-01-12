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
        ),
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
        ),
        'r_cmd' => array(
            "volt_batteria" => '||lr',
            "corr_avanti"   => '||nr',
            "corr_dietro"   => '||or',
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
            '||jw' => "counting",
            '||ww' => "data sistema",
            '||vw' => "orario sistema",
            '||mr' => "amplificazione",
            '||nw' => "correzione avanti",
            '||ow' => "correzione dietro",
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
            '||jw' => "counting",
            '||ww' => "giorno",
            '||vw' => "ora",
            '||mw' => "amplificazione",
//            '||mr' => "amplificazione",
            '||nw' => "corr_avanti",
            '||ow' => "corr_dietro",
            '||lr' => "volt_batteria",
            '**vw' => "site",
            '**ww' => "point",),
    ),
    'pmv' => array(
        'logic' => "CompactLogic",
        'cmd' => array(),
        'w_cmd' => array(),
        'r_cmd' => array(),
        'heder' => array(),
        'campo' => array(),
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
        'campo' => array(),
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




