<?php

namespace martingouthier\onsie\dispatch;

use martingouthier\onsie\action\AnnotationAction;
use martingouthier\onsie\action\CheckRuleAction;
use martingouthier\onsie\action\DefaultAction;
use martingouthier\onsie\action\ListeArticlesAction;
use martingouthier\onsie\action\ListeCoAuteursAction;
use martingouthier\onsie\action\ListeLaboratoireAction;
use martingouthier\onsie\action\NotesAction;
use martingouthier\onsie\action\StatsAction;

class Dispatcher {
    private string $action;

    public function __construct() {
        $this->action = $_GET['action'] ?? "default";
    }

    public function run(): void {
        $a = match ($this->action) {
            "display-publications" => new ListeArticlesAction(),
            "display-coauteurs" => new ListeCoAuteursAction(),
            "display-laboratoires" => new ListeLaboratoireAction(),
            "display-annotations" => new AnnotationAction(),
            "display-note" => new NotesAction(),
            "display-stats" => new StatsAction(),
            "check-rule" => new CheckRuleAction(),
            default => new DefaultAction(),
        };
        $this->renderPage($a->execute());
    }

    private function renderPage(string $html): void {
        $pageComplete = <<<END
            <!DOCTYPE html>
            <html lang="fr" style="font-family:Arial,serif">
                <head>
                    <meta charset="UTF-8">
                    <title>Onsie</title>
                    <link rel="stylesheet" href="css/style.css">
                </head>
            
                <body>
                    <h1>Onsie</h1>
                    <p>Application de gestion de publications et revues scientifiques</p>
                    <hr>
                           
                    <h2>Menu principal</h2>
                    <ul id="menu">
                        <li><a href="?action=accueil">Accueil</a></li>
                        <li><a href="?action=display-publications">Liste des écrits d'un chercheur</a></li>
                        <li><a href="?action=display-coauteurs">Liste des co-auteurs d'un chercheur</a></li>
                        <li><a href="?action=display-laboratoires">Liste des laboratoires de chaque chercheur</a></li>
                        <li><a href="?action=display-annotations">Chercheurs ayant annoté un certain nombre de publications</a></li>
                        <li><a href="?action=display-note">Moyenne des notes donnée par un chercheur</a></li>
                        <li><a href="?action=display-stats">Statistiques d'un laboratoire</a></li>
                        <li><a href="?action=check-rule">Vérification de la règle d'attribution d'une note</a></li>
                    </ul>
            
                    <hr>
            
                    <div>
                        $html
                    </div>
                </body>
            </html>
        END;

        echo $pageComplete;
    }

}
