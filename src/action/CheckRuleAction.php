<?php

namespace martingouthier\onsie\action;


use martingouthier\onsie\repository\OnsieRepository;

class CheckRuleAction extends Action
{

    public function GET(): string
    {
        $html = <<<HTML
                <h1>Vérification de la règle de notations d'un article</h1>
                <form method='post' action=?action=check-rule>
                <label for="article">Selectionner l'article</label>
                <select id="article" name="article">
                HTML;
        $bdd = OnsieRepository::getInstance();
        $listeTitre = $bdd->getTitres();

        foreach ($listeTitre as $titre){
            $html .= "<option value=$titre[0]>$titre[1]</option>";
        }
        $html .= <<<HTML
                </select>
                <button type="submit">Valider</button>
                </form>
                HTML;
        return $html;   }


    public function POST(): string
    {
        $idArticle = $_POST["article"];
        $bdd = OnsieRepository::getInstance();
        $titre = $bdd->getTitre($idArticle);

        if ($bdd->verifNoteMaxi($idArticle)){
            $html = "<h2>Les notations de l'article \"$titre\" <b>respectent</b> la règle </h2>";
        } else {
            $html = "<h2>Les notations de l'article \"$titre\" <b>ne respectent pas</b> la règle</h2>";
        }
        return $html;
    }
}