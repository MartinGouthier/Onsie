<?php

namespace martingouthier\onsie\action;

class DefaultAction extends Action
{

    public function GET(): string
    {
        return <<<HTML
            <h1>Bienvenue sur Onsie</h1>
            <p>Onsie est une application qui vous permet de consulter des documents scientifiques.</p>
            <p>Utilisez le menu pour accéder aux différentes fonctionnalités.</p>
        HTML;
    }

    public function POST(): string
    {
        // TODO: Implement POST() method.
    }
}