<?php
$map_cmd = array(
    'server' => array(
        'logic' => "ServerLogic",
        'cmd' => array(
            'quit'       => "spegni il server",
            'list_dispo' => "lista dei dispositivi",
            'list_user'  => "lista degli user",
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
            '||nr' => "leggi correzione corsia 1",
            '||or' => "leggi correzione corsia 2",
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
            "dir_indietro"      => '**mw',
            "counting"          => '||jw',
            "giorno"            => '||ww',
            "ora"               => '||vw',
            "amplificazione"    => '||mw',
            "site"              => '**vw',
            "point"             => '**ww',
        ),
        'r_cmd' => array(
            "volt_batteria" => '||lr',
            "corr_avanti"   => '||nr',
            "corr_dietro"   => '||or',
            "r_conf"        => '**ur',
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
            '||nr' => "correzione corsia 1",
            '||or' => "correzione corsia 2",
            '||lr' => "voltaggio batteria",
            '**vw' => "site",
            '**ww' => "point",
        ), //non c'è su cmd
        'campo' => array(
            'bios' => "bios",
            '**iw' => "id",
            '**nw' => "citta",
            '**jw' => "via",
            '**kw' => "km",
            '**lw' => "dir_avanti",
            '**mw' => "dir_indietro",
            '||jw' => "counting",
            '||ww' => "data",
            '||vw' => "ora",
            '||mr' => "amplificazione",
            '||nr' => "corr_corsia_1",
            '||or' => "corr_corsia_2",
            '||lr' => "volt_batteria",//non c'è su cmd
            '**vw' => "site",
            '**ww' => "point",),
    ),
    'pmv' => array(
        'logic' => "CompactLogic",
        'cmd' => array(),
        'ec_cmd' => array(),
        'heder' => array(),
        'campo' => array(),
    ),
    'pedestrian' => array(
        'logic' => "CompactLogic",
        'cmd' => array(),
        'ec_cmd' => array(),
        'heder' => array(),
        'campo' => array(),
    ),
);




