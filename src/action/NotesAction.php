<?php

namespace martingouthier\onsie\action;

use martingouthier\onsie\repository\OnsieRepository;

class NotesAction extends Action
{

    public function GET(): string
    {
        $html = <<<HTML
                <h1>Affichage de la note moyenne donnée par un auteur</h1>
                <form method='post' action=?action=display-note>
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
        $note = $pdo->getMoyenneNotes($id_auteur);
        $nom = $pdo->getNomChercheur($id_auteur);
        $nbrNote = $note[0];

        if ($nbrNote === 0)
            $html = "<h1>$nom n'a attribué aucune note</h1>";
        else {
            $noteMoyenne = (double)$note[1];
            $html = "<h1>$nom a noté $nbrNote publications pour une moyenne de $noteMoyenne/5</h1>";
        }
        return $html;
    }
}