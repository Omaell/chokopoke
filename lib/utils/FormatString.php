<?php 



class FormatString 
{
    
    /**
     * Mise en page de la fiche du pokemon 
     * pour console
     * @static
     *  
     * @param array $pokemon 
     * détails du pokemon à afficher
     * 
     * @param string $langue 
     * langue choisie par l'utilisateur
     * 
     * @param array $liste_stats 
     * liste des traductions des stats
     * 
     * @param array $liste_types 
     * liste des traductions des types
     * 
     * @return string
     */
    public static function formatPokemon(array $pokemon, string $langue, array $liste_stats, array $liste_types) : string
    {
        $retour = shell_exec("curl -s " . $pokemon['image'] . " | convert - jpg:- | jp2a --color -");
        $retour .= "\n";
        $retour .= shell_exec("echo " . $pokemon['nom'] . " | iconv -f utf8 -t ascii//TRANSLIT | figlet");
        $retour .= "\n";
        if ($langue == 'fr') {
            $retour .= "Poids : " . $pokemon['poids'] . "\n";
            $retour .= "Taille : " . $pokemon['taille'] . "\n";
            $retour .= "Expérience de base : " . $pokemon['xp'];
        } 
        elseif ($langue == 'en') {
            $retour .= "Weight : " . $pokemon['poids'] . "\n";
            $retour .= "Size : " . $pokemon['taille'] . "\n";
            $retour .= "Base experience : " . $pokemon['xp'];
        } 
        $retour .= "\n Stats : ";
        foreach ($pokemon["stats"] as $stat) {
            $retour .= '- ';
            $retour .= $liste_stats[$stat["stat"]["name"]] . " : " . $stat['base_stat'];
            $retour .= ' -';
        }
        $retour .= "\n Types : ";
        $liste_types_2 = array_flip($liste_types);
        foreach ($pokemon["types"] as $type) {
            $retour .= '- ';
            $retour .= $liste_types_2[$type["type"]["name"]] ;
            $retour .= ' -';
        }
        $retour .= "\n Versions : ";
        foreach ($pokemon["versions"] as $version) {
            $retour .= '/ ';
            $retour .= $version ;
            $retour .= ' /';
        }
        $retour .= "\n";
        return $retour;
    }
}