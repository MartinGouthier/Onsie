<?php

namespace martingouthier\onsie\action;

use martingouthier\onsie\repository\OnsieRepository;

class ListeLaboratoireAction extends Action
{

    public function GET(): string
    {
        $repo = OnsieRepository::getInstance();
        $listeChercheurs = $repo->getListeTravailleursLabo();
        $html = "<h1>Liste des chercheurs et des laboratoires</h1><ul>";
        foreach($listeChercheurs as $chercheur){
            $nomAuteur = $repo->getNomChercheur((int)$chercheur[0]);
            $html .= "<li><h3>Laboratoire(s) de $nomAuteur</h3><ul>";
            foreach($chercheur[1] as $laboratoire){
                $html .= "<li>$laboratoire</li>";
            }
            $html .= "</ul></li>";
        }
        $html .= "</ul>";
        return $html;
    }

    public function POST(): string
    {
        return $this->GET();
    }
}