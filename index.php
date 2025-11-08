<?php


session_start();

use martingouthier\onsie\dispatch\Dispatcher;
use martingouthier\onsie\exceptions\DatabaseConnectionException;
use martingouthier\onsie\repository\OnsieRepository;

require_once "vendor/autoload.php";

try {
    OnsieRepository::setConfig(__DIR__ . '/config/db.ini');
    $dispatcher = new Dispatcher();
    $dispatcher->run();
} catch (DatabaseConnectionException $e) {
    $pageComplete = <<<END
            <!DOCTYPE html>
            <html lang="fr" style="font-family:Arial,serif">
                <head>
                    <meta charset="UTF-8">
                    <title>Onsie</title>
                </head>
            
                <body>
                    <h1>Onsie</h1>
                    <p>Application de gestion de publications et revues scientifiques</p>
            
                    <hr>
            
                    <div>
                        <p>{$e->getMessage()}</p>
                    </div>
                </body>
            </html>
        END;
    echo($pageComplete);
}
