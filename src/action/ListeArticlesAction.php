<?php

namespace martingouthier\onsie\action;

use martingouthier\onsie\repository\OnsieRepository;

class ListeArticlesAction extends Action
{

    public function GET(): string
    {
        $html = <<<HTML
                <h1>Recherche des articles d'un auteur</h1>
                <form method='post' action=?action=display-publications>
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
        $articles = $pdo->getArticlesEcrits($id_auteur);
        $nom = $pdo->getNomChercheur($id_auteur);
        $html = <<<HTML
                <h1>Liste des articles écrits par $nom</h1>
                <ul>
                HTML;

        foreach ($articles as $article){
            $html .= "<li>$article[1]</li>";
        }
        $html .= "</ul>";
        return $html;
    }
}