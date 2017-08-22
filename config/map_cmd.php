<?php
$map_cmd = array(
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
            '||mw' => "scrivi sensibilità radar",
            '**ur' => "leggi configurazione di sistema",),
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
            '||lr' => "voltaggio batteria",),
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
            '||lr' => "volt_batteria",),
    ),
    'pmv' => array(
        'logic' => "CompactLogic",
        'cmd' => array(),
        'heder' => array(),
        'campo' => array(),
    ),
    'pedestrian' => array(
        'logic' => "CompactLogic",
        'cmd' => array(),
        'heder' => array(),
        'campo' => array(),
    ),
);




