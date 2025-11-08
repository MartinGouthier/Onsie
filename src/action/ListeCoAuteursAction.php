<?php

namespace martingouthier\onsie\action;


use martingouthier\onsie\repository\OnsieRepository;

class ListeCoAuteursAction extends Action
{

    public function GET(): string
    {
        $html = <<<HTML
                <h1>Recherche des coauteurs d'un auteur</h1>
                <form method='post' action=?action=display-coauteurs>
                <label for="auteur">Selectionner l'auteur</label>
                <select id="auteur" name="auteur">
                HTML;
        $html .= Action::getHTMLlisteChercheur();
        $html .= <<<HTML
                </select>
                <button type="submit">Valider</button>
                </form>
                HTML;
        return $html;
    }

    public function POST(): string
    {
        $id_auteur = (int) $_POST['auteur'];
        $pdo = OnsieRepository::getInstance();
        $coAuteurs = $pdo->getListeCoAuteurs($id_auteur);
        $nom = $pdo->getNomChercheur($id_auteur);
        $html = <<<HTML
                <h1>Liste des auteurs ayant travaillé avec $nom</h1>
                <ul>
                HTML;

        foreach ($coAuteurs as $coAuteur){
            $nomCoAuteur = $pdo->getNomChercheur($coAuteur[0]);
            $html .= "<li>$nomCoAuteur</li>";
        }
        $html .= "</ul>";
        return $html;
    }
}