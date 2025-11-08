<?php

namespace martingouthier\onsie\repository;

use martingouthier\onsie\exceptions\DatabaseConnectionException;
use PDO;

class OnsieRepository
{
    private PDO $pdo;
    private static ?OnsieRepository $instance = null;
    private static array $config = [];

    private function __construct(array $conf)
    {
        $this->pdo = new PDO($conf['dsn'], $conf['user'], $conf['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }

    public static function getInstance(): OnsieRepository
    {
        if (is_null(self::$instance)) {
            self::$instance = new OnsieRepository(self::$config);
        }
        return self::$instance;
    }

    public static function setConfig(string $file): void
    {
        $conf = parse_ini_file($file);
        if ($conf === false) {
            throw new DatabaseConnectionException("Erreur : impossible de lire le fichier de configuration");
        }
        $dsn = "{$conf['driver']}:host={$conf['host']};dbname={$conf['database']}";
        self::$config = ['dsn' => $dsn, 'user' => $conf['username'], 'pass' => $conf['password']];
        try {
            $connexionBdd = new OnsieRepository(self::$config);
        } catch (\PDOException) {
            throw new DatabaseConnectionException("Erreur : connexion à la base de données refusée");
        }
    }

    // Methodes permettant d'implémenter les fonctionnalités demandées

    /**
     * Nom du chercheur sous forme "Prenom Nom"
     * @param int $idChercheur
     * @return string
     */
    public function getNomChercheur(int $idChercheur) : string {
        $requete = "SELECT PrenomChercheur, NomChercheur FROM chercheur WHERE numcher = ?;";
        $statm = $this->pdo->prepare($requete);
        $statm->execute([$idChercheur]);
        $nom = $statm->fetch();
        return "$nom[0] $nom[1]";
    }

    /**
     * Liste des données d'articles écrits par un chercheur
     * @param int $idChercheur
     * @return array
     */
    public function getArticlesEcrits(int $idChercheur) : array{
        $requete = <<<END
                    SELECT * FROM article
                    INNER JOIN ecrire ON ecrire.NUMART = article.NUMART
                    WHERE ecrire.NUMCHER = ?;
                    END;
        $statm = $this->pdo->prepare($requete);
        $statm->execute([$idChercheur]);
        $listeTitre = [];
        while ($donnee = $statm->fetch()){
            $listeTitre[] = $donnee;
        }
        return $listeTitre;
    }

    /**
     * Liste des identifiants des co-auteurs d'un auteur)
     * @param int $idChercheur
     * @return array
     */
    public function getListeCoAuteurs(int $idChercheur) : array{
        $requete = <<<END
                    SELECT ecrire.NUMCHER FROM chercheur
                    INNER JOIN ecrire ON ecrire.NUMCHER = chercheur.NUMCHER
                    WHERE ecrire.NUMART IN ( SELECT numart FROM ecrire WHERE numcher = ? ) and chercheur.NUMCHER != ?;
                    END;
        $statm = $this->pdo->prepare($requete);
        $statm->bindParam(1,$idChercheur);
        $statm->bindParam(2,$idChercheur);
        $statm->execute();
        $listeAuteurs = [];
        while ($donnee = $statm->fetch()){
            $listeAuteurs[] = $donnee;
        }
        return $listeAuteurs;
    }

    /**
     * Liste des chercheurs et de leurs laboratoires associés
     * @return array
     */
    public function getListeTravailleursLabo() :array {
        $requete = <<<END
        SELECT NOMLABO, travailler.NUMCHER FROM travailler
        INNER JOIN laboratoire ON laboratoire.NUMLABO = travailler.NUMLABO
        ORDER BY travailler.NUMCHER;
        END;
        $statm = $this->pdo->query($requete);
        $listeChercheurs = [];
        $chercheur = [];
        $listeLabo = [];
        $donnees = $statm->fetch();
        $chercheur[] = $donnees[1];
        $listeLabo[] = $donnees[0];

         while ($donnees = $statm->fetch()){
             // Si l'id du chercheur est différent
             if ($chercheur[0] !== $donnees[1]) {
                 // On emboite les tableaux
                 $chercheur[] = $listeLabo;
                 $listeChercheurs[] = $chercheur;
                 // On créé de nouveaux tableaux
                 $chercheur = [];
                 $chercheur[] = $donnees[1];
                 $listeLabo = [];
             }
              $listeLabo[] = $donnees[0];
         }
         // On ajoute les dernières données à la fin
         $chercheur[] = $listeLabo;
         $listeChercheurs[] = $chercheur;
         return $listeChercheurs;

    }

    /**
     * Liste des identifiants d'auteurs ayant donnée un nombre de note fournis
     * @param int $nbrNotes
     * @return array
     */
    public function getNbrNotesDonnees(int $nbrNotes) : array {
        $requete = <<<END
        SELECT chercheur.numCher
        FROM chercheur
        LEFT JOIN annoter ON chercheur.NUMCHER = annoter.NUMCHER
        GROUP BY chercheur.NUMCHER
        HAVING count(annoter.NUMCHER) = ?;
        END;
        $statm = $this->pdo->prepare($requete);
        $statm->execute([$nbrNotes]);
        $listeChercheurs = [];
        while ($donnees = $statm->fetch()){
            $listeChercheurs[] = $donnees[0];
        }
        return $listeChercheurs;
    }

    /**
     * Tableau [NbrNotesReçus, MoyenneNotes] d'un chercheur
     * @param int $idChercheur
     * @return array
     */
    public function getMoyenneNotes(int $idChercheur) : array{
        $requete = <<<END
        SELECT count(*), AVG(note) FROM noter
        WHERE NUMCHER = ?;
        END;
        $statm = $this->pdo->prepare($requete);
        $statm->bindParam(1,$idChercheur);
        $statm->execute();
        return $statm->fetch();
    }

    /**
     * Statistiques des chercheurs d'un laboratoire précis, trié par le nombre d'article publié
     * par un chercheur
     * @param int $idLabo
     * @return array
     */
    public function getStatsLabo(int $idLabo) : array {
        $requete = <<<END
        SELECT travailler.NUMCHER, count(distinct article.NUMART) as nbrArticle, count(note), avg(note) FROM travailler
        LEFT JOIN chercheur ON travailler.NUMCHER = chercheur.NUMCHER
        LEFT JOIN ecrire ON ecrire.NUMCHER = chercheur.NUMCHER
        LEFT JOIN article ON article.NUMART = ecrire.NUMART
        LEFT JOIN noter ON noter.NUMART = article.NUMART
        WHERE NumLabo = ?
        GROUP BY travailler.NUMCHER,chercheur.NOMCHERCHEUR
        ORDER BY nbrArticle DESC, chercheur.NOMCHERCHEUR;
        END;
        $statm = $this->pdo->prepare($requete);
        $statm->bindParam(1,$idLabo);
        $statm->execute();
        $listeStats = [];
        while ($donnees = $statm->fetch()){
            $listeStats[] = $donnees;
        }
        return $listeStats;
    }

    /**
     * Verification si la note maximale d'un article a été donnée par un chercheur
     * travaillant dans le même laboratoire qu'un des auteurs de l'article
     * @param int $idArticle
     * @return bool
     */
    public function verifNoteMaxi(int $idArticle) : bool {
        $requete = <<<END
        SELECT count(*)
        FROM noter
        -- Recherche de la note maximale d'un article
        WHERE NUMART = ? AND note = (SELECT max(note) FROM noter WHERE numart = ?)
        -- Comparaison du chercheur ayant attribué la note
        AND NUMCHER IN ( SELECT chercheur.NUMCHER
                         -- Recherche des chercheurs travaillant dans un laboratoire
                            FROM chercheur
                            INNER JOIN travailler ON chercheur.NUMCHER = travailler.NUMCHER
                            WHERE travailler.NUMLABO IN (
                                -- Recherche des laboratoires en fonction des co-auteurs
                                SELECT NUMLABO
                                FROM travailler
                                WHERE NUMCHER IN (
                                    -- Recherche des co-auteurs de l'article
                                    SELECT NUMCHER
                                    FROM ecrire
                                    WHERE NUMART = ?
                                )
                            )
        
        );
        END;

        $statm = $this->pdo->prepare($requete);
        $statm->execute([$idArticle,$idArticle,$idArticle]);
        // 1 article trouvé = la règle n'est pas respectée
        return (intval($statm->fetch()[0]) === 0);
    }

    // Methodes permettant l'affichage ou l'appel aux methodes ci-dessus

    /**
     * Liste des chercheurs dans la BDD
     * @return array
     */
    public function getChercheurs() : array {
        $requete = "SELECT NUMCHER,NOMCHERCHEUR,PRENOMCHERCHEUR FROM chercheur ORDER BY PRENOMCHERCHEUR,NOMCHERCHEUR;";
        $statm = $this->pdo->query($requete);
        $tab = [];
        while ($donnee = $statm->fetch()){
            $tab[] = $donnee;
        }
        return $tab;
    }

    /**
     * Liste des labos dans la BDD
     * @return array
     */
    public function getLabos() : array {
        $requete = "SELECT NUMLABO,NOMLABO FROM laboratoire ORDER BY NOMLABO;";
        $statm = $this->pdo->query($requete);
        $tab = [];
        while ($donnee = $statm->fetch()){
            $tab[] = $donnee;
        }
        return $tab;
    }

    /**
     * Nom du laboratoire spécifié
     * @param int $id_labo
     * @return string
     */
    public function getNomLabo(int $id_labo) : string{
        $requete = "SELECT NOMLABO FROM laboratoire WHERE NUMLABO = ?";
        $statm = $this->pdo->prepare($requete);
        $statm->execute([$id_labo]);
        return $statm->fetch()[0];
    }

    /**
     * Liste des numéros et titres d'articles
     * @return array
     */
    public function getTitres() : array {
        $requete = "SELECT NUMART, TITRE FROM article ORDER BY TITRE;";
        $statm = $this->pdo->query($requete);
        $tab = [];
        while ($donnee = $statm->fetch()){
            $tab[] = $donnee;
        }
        return $tab;
    }

    /**
     * Titre d'un article spécifié
     * @param int $idArticle
     * @return string
     */
    public function getTitre(int $idArticle) : string {
        $requete = "SELECT TITRE FROM article WHERE NUMART = ?;";
        $statm = $this->pdo->prepare($requete);
        $statm->execute([$idArticle]);
        return $statm->fetch()[0];
    }

}