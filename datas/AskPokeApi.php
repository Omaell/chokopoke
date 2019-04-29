<?php

class PokeApi 
{

    const URL_POKEAPI = "https://pokeapi.co/api/v2/";
    const POKEMAX = 20;

    /**
     * Langue renseignée par l'utilisateur
     * 'fr' par défaut
     *
     * @var string
     */
    private $langue = 'fr';
    
    /**
     * Liste des urls à appeler pour obtenir les pokemons selon leurs types
     *
     * @var array
     */
    private $liste_portes = array();

    /**
     * Listes des types existants
     *
     * @var array
     */
    private $liste_types = array();

    /**
     * Liste des pokemons retournés filtrés par type
     *
     * @var array
     */
    private $liste_pokemons = array();

    /**
     * Liste des pokemons + les détails et leurs caractéristiques
     *
     * @var array
     */
    private $pokemon = array();

    /**
     * Liste des noms des stats en fonction de la langue demandée
     *
     * @var array
     */
    private $liste_stats = array();

    /**
     * Liste des versions dans lesquelles apparaissent le pokemon
     *
     * @var array
     */
    private $liste_versions = array();

    public function setListeVersions(array $liste_versions) : void
    {
        foreach ($liste_versions as $version) {
            if (isset($version['url'])) {
                try {
                    $version_details = $this->appelCurl($version['url']);
                } catch (Exception $e) {
                    echo $e->getMessage(); die;
                }
                $this->liste_versions[$version['name']] = $this->getTraduction($version_details['names']);
            }
        }
    }

    public function getListeVersions() : array 
    {
        return  $this->liste_versions;
    }

    /**
     * Retourne la propriété liste_types
     *
     * @return array
     */
    public function getListeTypes() : array
    {
        return $this->liste_types;
    }

    /**
     * remplit la propriété $pokemon (array)
     * jusqu'à ce qu'il y ait POKEMAX entrées
     * 
     * @param string $trad
     * traduction de "trouvé" selon la langue choisie
     *
     * @return void
     */
    public function setPokemon(string $trad) : void 
    {
        foreach ($this->liste_pokemons as $name => $url) {
            $this->demandeParPokemon($url);
            echo str_pad($this->pokemon[$name]['nom'] . ' ' . $trad, rand(2, 100), '.',STR_PAD_BOTH);
            if (count($this->pokemon) === self::POKEMAX) break;
        }
    }

    /**
     * Retourne les $n premiers pokemons
     *
     * @param int $n
     * @return array
     */
    public function getPokemon($n = self::POKEMAX) : array
    {
        return array_slice($this->pokemon, 0, $n);
    }
    
    /**
     * Tableau qui stocke les urls à appeler pour récupérer les Types de pokemons
     *
     * @param array $valeurs
     * @return void
     */
    private function setPortes(array $valeurs = array()) : void
    {
        foreach ($valeurs as $valeur){
            if (array_key_exists($valeur, $this->liste_types)) {
                $this->liste_portes[] = self::URL_POKEAPI . 'type/' . $this->liste_types[$valeur] ;
            }
        }
    }

    /**
     * Remplit un tableau avec tous les noms des stats traduits dans la langue sélectionnée
     *
     * @param string $stat_id
     * @return void
     */
    private function setListeStats(string $stat_id) : void
    {
        if (!array_key_exists($stat_id, $this->liste_stats)) {
            try {
                $retour = $this->appelCurl(self::URL_POKEAPI . 'stat/' . $stat_id)['names'];
            }
            catch (Exception $e) {
                echo $e->getMessage();
            }
            $this->liste_stats[$stat_id] = $this->getTraduction($retour);
        }
    }

    public function getListeStats() : array
    {
        return $this->liste_stats;
    }

    /**
     * retourne la langue choisie par l'utilisateur
     *
     * @return string
     */
    public function getLangue() : string 
    {
        return $this->langue;
    }

    /**
     * Constructeur
     * 
     * mémorise la langue sélectionnée par l'utilisateur
     * appelle le WS pour retourner tous les types disponibles
     */
    function __construct(string $langue)
    {
        $this->langue = $langue;
    }

    /**
     * Recherche toutes les informations nécessaires du pokemon grâce à l'$url saisie
     * Remplit la propriété $pokemon
     *
     * @param string $url
     * @return void
     */
    private function demandeParPokemon(string $url) : void
    {
        try {
            $retour_poke = $this->appelCurl($url);
            $retour_spec = $this->appelCurl($retour_poke['species']['url']);
        }
        catch (Exception $e) {
            echo $e->getMessage(); die;
        }
        foreach ($retour_poke['stats'] as $value) {
            $this->setListeStats($value['stat']['name']);
        }
        foreach ($retour_poke['game_indices'] as $game_indices) {
            if (array_key_exists('version', $game_indices)) {
                $this->setListeVersions($game_indices);
            }
        }
        $this->pokemon[$retour_poke['name']] = array(
            'image' => $retour_poke['sprites']['front_default'],
            'stats' => $retour_poke['stats'],
            'types' => $retour_poke['types'],
            'nom' => $this->getTraduction($retour_spec['names']),
            'poids' => $retour_poke['weight'],
            'taille' => $retour_poke['height'],
            'xp' => $retour_poke['base_experience'],
            'versions' => $this->getListeVersions(),
                );
    }

    /**
     * Récupère la traduction d'un terme à partir du tableau de retour du WS
     *
     * @param array $retour
     * @return string
     */
    private function getTraduction(array $retour=array()) : string 
    {
        $traduction = '';
        foreach ($retour as $value) {
            if ($value['language']['name'] == $this->langue) {
                $traduction = $value['name'];
            }
        }

        return $traduction;
    }

    
    /**
     * Remplit le tableau des pokemons selon les types demandés
     *
     * @param array $types
     * @return void
     */
    public function demandeParType(array $types_choisis = array()) : void
    {
        if (count($types_choisis) > 0) {
            $i = 0;
            $tab_deja_trie = array();
            foreach ($types_choisis as $type_choisi) {
                $i++;
                if (key_exists($type_choisi,$this->liste_types)) {
                    try {
                        $retour = $this->appelCurl(self::URL_POKEAPI . 'type/' . $this->liste_types[$type_choisi]);
                    }
                    catch (Exception $e) {
                        echo $e->getMessage();
                    }
                    if (count($retour) > 0) {
                        $tab_a_trier = array();
                        foreach($retour['pokemon'] as $pokemon) {
                            if ($i == 1) {
                                $tab_deja_trie[$pokemon['pokemon']['name']] = $pokemon['pokemon']['url'];
                            } else {
                                $tab_a_trier[$pokemon['pokemon']['name']] = $pokemon['pokemon']['url'];
                            }
                        }
                        if ($i > 1) {
                            $tab_deja_trie = array_intersect_key($tab_deja_trie, $tab_a_trier);
                        }
                    }
                } else {
                    throw new Exception("ERREUR : Le type [" . $type_choisi . "] n'est pas autorisé\n");
                }
            }
            $this->liste_pokemons = $tab_deja_trie;
        } else {
            throw new Exception("ERREUR : Aucun Type de pokemon choisi\n");
        }
    }

    
    /**
     * Replit la propriété liste_types par les noms traduits des types selon la langue choisie
     *
     * @return void
     */
    public function setListeTypes() : void
    {
        try {
            $retour = $this->appelCurl(self::URL_POKEAPI . 'type/');
        } catch (Exception $e) {
            echo $e->getMessage();
            die;
        }
        foreach ($retour['results'] as $type) {
            try {
                $details_type = $this->appelCurl($type['url']);
            } catch (Exception $e) {
                echo $e->getMessage();
                die;
            }
            foreach ($details_type['names'] as $names_type) {
                if ($names_type['language']['name'] == $this->langue) {
                    $this->liste_types[$names_type['name']] = $type['name'];
                }
            }
        }
    }

    /**
     * Appel du WS PokeAPI
     *
     * @param string $url
     * @return array
     */
    private function appelCurl(string $url) : array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $retour = json_decode(curl_exec($ch), true) ;
        if (is_array($retour)) {
            return $retour;
        } else {
            throw new Exception("ERREUR : L'appel à la PokeAPI n'a pas fonctionné\n");
            return array();
        }        
    }


}