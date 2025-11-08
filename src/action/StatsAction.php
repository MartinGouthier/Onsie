<?php

namespace martingouthier\onsie\action;

use martingouthier\onsie\repository\OnsieRepository;

class StatsAction extends Action
{

    public function GET(): string
    {
        $html = <<<HTML
                <h1>Affichage des statistiques de publications des auteurs d'un laboratoire</h1>
                <form method='post' action=?action=display-stats>
                <label for="labo">Selectionner le laboratoire</label>
                <select id="labo" name="labo">
                HTML;

        $bdd = OnsieRepository::getInstance();
        $listeLabo = $bdd->getLabos();
        foreach ($listeLabo as $labo){

            $html .= "<option value=$labo[0]>$labo[1]</option>";
        }

        $html .= <<<HTML
                </select>
                <button type="submit">Valider</button>
                </form>
                HTML;
        return $html;
    }

    public function POST(): string
    {
        $id_labo = (int) $_POST['labo'];
        $pdo = OnsieRepository::getInstance();
        $listeChercheurs = $pdo->getStatsLabo($id_labo);
        $nomLabo = $pdo->getNomLabo($id_labo);

        $html = <<<HTML
                <h1>Statistiques des chercheurs du laboratoire "$nomLabo"</h1>
                <ul>
                HTML;

        foreach ($listeChercheurs as $statsChercheur){
            $nom = $pdo->getNomChercheur($statsChercheur[0]);
            $html .= <<<HTML
                    <li>Statistiques de $nom
                    <ul>
                        <li>Nombre d'articles publié(s) : $statsChercheur[1]</li>
                    HTML;
            if ($statsChercheur[1] > 0){
                $html .= "<li>Nombre de notes reçue(s) : $statsChercheur[2]</li>";
                if ($statsChercheur[2] > 0){
                    $html .= "<li>Moyenne des notes reçue : $statsChercheur[3]</li>";
                }
            }
            $html .= "</ul><br>";
        }
        $html .= "</ul>";
        return $html;
    }
}