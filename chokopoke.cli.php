<?php

require_once('lib/CliColor/CliColor.php');
require_once('datas/AskPokeApi.php');
require_once('lib/utils/FormatString.php');

$cli_color = new CliColors();
$langues_disponibles = ["fr", "en",];

echo shell_exec("convert images/logo_fr.png jpg:- | jp2a --color - ");
echo "\n";
echo "\n";

echo $cli_color->getColoredString(" First, select your language [" .implode('/',$langues_disponibles). "] / D'abord, sélectionnez votre langue [". implode('/',$langues_disponibles) ."] : ", "purple", "yellow") . "\n\n"; 
while (!in_array($langue_choisie = trim(fgets(STDIN)), $langues_disponibles)) {
    echo "Well... [en] or [fr]. Don't be shy. \n";
    echo "Si vous arrivez à lire ce que j'écris, je vous conseille de taper [fr]. \n";
}

$poke_api = new PokeApi($langue_choisie);

// Stockage des chaines de traduction
$i = 0;
if (($trad = fopen('lang/chokopoke.csv','r')) !== false) {
    while ($ligne = fgetcsv($trad, 1000 , ';')) {
        $i++;
        if ($i == 1) {
            foreach ($ligne as $num => $colonne){
                if ($colonne == $poke_api->getLangue()){
                    $num_col = $num;
                    continue;
                }
            }
            continue;
        }
        $tab_trad[$ligne[0]] = $ligne[$num_col] ;
    }
}

// Description du script
echo $cli_color->getColoredString(" " . $tab_trad['hello'] . " ", "purple", "yellow") . "\n\n";
echo $tab_trad['script_description'] . ".\n";

// les Types selectionnables
$liste = $cli_color->getColoredString($tab_trad['choosing_type'] , "yellow").  "\n";

if (count($liste_des_types = $poke_api->getListeTypes()) == 0) {
    $poke_api->setListeTypes();
    $liste_des_types = $poke_api->getListeTypes();
}

foreach ($liste_des_types as $type_name => $type_code){
    $liste .= $type_name . "\n";
}
echo $liste;

// Choix du ou des types
echo $cli_color->getColoredString($tab_trad['instruction_type'], "yellow");

while (strlen($choix = trim(fgets(STDIN))) === 0) {
    echo $tab_trad['choosing_type_error_1'] . "\n";
    echo $tab_trad['choosing_type_error_2'] . "\n";
}

// Recherche des pokemons
echo $cli_color->getColoredString($tab_trad['waiting'] , "purple", "yellow").  "\n";

$poke_api->demandeParType(explode(' ', $choix));
$poke_api->setPokemon($tab_trad['trouve']);
$liste_des_stats = $poke_api->getListeStats();

if (count($selection_pokemon = $poke_api->getPokemon()) > 0 )
{
    echo $cli_color->getColoredString("\n" . $tab_trad['finishing'] , "purple",  "yellow").  "\n";
    echo $cli_color->getColoredString($tab_trad['see_first'] , "yellow").  "\n";
    while (trim(fgets(STDIN)) != "chokopoke") {
        echo $tab_trad['see_first_error'].  "\n";
    }
    echo FormatString::formatPokemon(current($selection_pokemon), $langue_choisie, $liste_des_stats, $liste_des_types);
    echo "\n";
    echo "\n";

    $i = 0;
    foreach ($selection_pokemon as $pokemon) {
        $i++;
        if ($i == 1) {
            continue;
        }
        echo $cli_color->getColoredString($tab_trad['see_next'] , "yellow").  "\n";
        while (trim(fgets(STDIN)) == "") {
            echo $tab_trad['see_next_error'] . "\n";
        }
        echo FormatString::formatPokemon($pokemon, $langue_choisie, $liste_des_stats, $liste_des_types);
        echo "\n";
        echo "\n";
        $i++;
    }
    unset($i);

    echo $cli_color->getColoredString($tab_trad['empty_pokeball'] , "purple", "yellow").  "\n";
    echo "\n";

    echo shell_exec("figlet -t " . $tab_trad['bye'] . " !");
    echo "\n";
    echo "\n";
} 
else { // Pas de pokemon
    echo $cli_color->getColoredString("\n" . $tab_trad['no_pokemon'] , "purple",  "yellow").  "\n";
    exit;
}





