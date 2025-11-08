<?php

namespace martingouthier\onsie\action;


use martingouthier\onsie\repository\OnsieRepository;

class AnnotationAction extends Action
{

    public function GET(): string
    {
        return <<<HTML
                <h1>Recherche des auteurs ayant annoté un certain nombre d'articles</h1>
                <form method='post' action=?action=display-annotations>
                <label for="nombre">Choississez un nombre d'article</label>
                <input type="text" name="nombre" id="nombre" required>
                <button type="submit">Valider</button>
                HTML;
    }

    public function POST(): string
    {
        $nbr = $_POST["nombre"];
        if (($nbr != 0&&!filter_var($nbr,FILTER_SANITIZE_NUMBER_INT))||$nbr < 0){
            return "<p>Erreur : veuillez saisir un nombre entier positif</p>".$this->GET();
        } else {
            $plr = ($nbr < 2) ? "" :"s";
            $html = "<h1>Liste des chercheurs ayant annoté $nbr article$plr</h1><ul>";
            $repo = OnsieRepository::getInstance();
            $listeChercheur = $repo->getNbrNotesDonnees($nbr);
            foreach ($listeChercheur as $id_chercheur){
                $html .= "<li>{$repo->getNomChercheur(intval($id_chercheur))}</li>";
            }
            $html .= "</ul>";
            return $html;
        }
    }
}