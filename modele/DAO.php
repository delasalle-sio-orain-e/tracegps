<?php
namespace modele;

use Exception;
// Projet TraceGPS
// fichier : modele/DAO.class.php   (DAO : Data Access Object)
// Rôle : fournit des méthodes d'accès à la bdd tracegps (projet TraceGPS) au moyen de l'objet \PDO
// modifié par dP le 12/8/2021

// liste des méthodes déjà développées (dans l'ordre d'apparition dans le fichier) :

// __construct() : le constructeur crée la connexion $cnx à la base de données
// __destruct() : le destructeur ferme la connexion $cnx à la base de données
// getNiveauConnexion($login, $mdp) : fournit le niveau (0, 1 ou 2) d'un utilisateur identifié par $login et $mdp
// existePseudoUtilisateur($pseudo) : fournit true si le pseudo $pseudo existe dans la table tracegps_utilisateurs, false sinon
// getUnUtilisateur($login) : fournit un objet Utilisateur à partir de $login (son pseudo ou son adresse mail)
// getTousLesUtilisateurs() : fournit la collection de tous les utilisateurs (de niveau 1)
// modifierMdpUtilisateur($login, $nouveauMdp) : enregistre le nouveau mot de passe $nouveauMdp de l'utilisateur $login daprès l'avoir hashé en SHA1
// supprimerUnUtilisateur($login) : supprime l'utilisateur $login (son pseudo ou son adresse mail) dans la bdd, ainsi que ses traces et ses autorisations
// envoyerMdp($login, $nouveauMdp) : envoie un mail à l'utilisateur $login avec son nouveau mot de passe $nouveauMdp

// liste des méthodes restant à développer :

// existeAdrMailUtilisateur($adrmail) : fournit true si l'adresse mail $adrMail existe dans la table tracegps_utilisateurs, false sinon
// getLesUtilisateursAutorises($idUtilisateur) : fournit la collection  des utilisateurs (de niveau 1) autorisés à suivre l'utilisateur $idUtilisateur
// getLesUtilisateursAutorisant($idUtilisateur) : fournit la collection  des utilisateurs (de niveau 1) autorisant l'utilisateur $idUtilisateur à voir leurs parcours
// autoriseAConsulter($idAutorisant, $idAutorise) : vérifie que l'utilisateur $idAutorisant) autorise l'utilisateur $idAutorise à consulter ses traces
// creerUneAutorisation($idAutorisant, $idAutorise) : enregistre l'autorisation ($idAutorisant, $idAutorise) dans la bdd
// supprimerUneAutorisation($idAutorisant, $idAutorise) : supprime l'autorisation ($idAutorisant, $idAutorise) dans la bdd
// getLesPointsDeTrace($idTrace) : fournit la collection des points de la trace $idTrace
// getUneTrace($idTrace) : fournit un objet Trace à partir de identifiant $idTrace
// getToutesLesTraces() : fournit la collection de toutes les traces
// getLesTraces($idUtilisateur) : fournit la collection des traces de l'utilisateur $idUtilisateur
// getLesTracesAutorisees($idUtilisateur) : fournit la collection des traces que l'utilisateur $idUtilisateur a le droit de consulter
// creerUneTrace(Trace $uneTrace) : enregistre la trace $uneTrace dans la bdd
// terminerUneTrace($idTrace) : enregistre la fin de la trace d'identifiant $idTrace dans la bdd ainsi que la date de fin
// supprimerUneTrace($idTrace) : supprime la trace d'identifiant $idTrace dans la bdd, ainsi que tous ses points
// creerUnPointDeTrace(PointDeTrace $unPointDeTrace) : enregistre le point $unPointDeTrace dans la bdd


// certaines méthodes nécessitent les classes suivantes :
include_once ('Utilisateur.php');
include_once ('Trace.php');
include_once ('PointDeTrace.php');
include_once ('Point.php');
include_once ('Outils.php');

// inclusion des paramètres de l'application
include_once ('parametres.php');

// début de la classe DAO (Data Access Object)
class DAO
{
    // ------------------------------------------------------------------------------------------------------
    // ---------------------------------- Membres privés de la classe ---------------------------------------
    // ------------------------------------------------------------------------------------------------------
    
    private $cnx;				// la connexion à la base de données
    
    // ------------------------------------------------------------------------------------------------------
    // ---------------------------------- Constructeur et destructeur ---------------------------------------
    // ------------------------------------------------------------------------------------------------------
    public function __construct() {
        global $PARAM_HOTE, $PARAM_PORT, $PARAM_BDD, $PARAM_USER, $PARAM_PWD;
        try
        {	$this->cnx = new \PDO("mysql:host=" . $PARAM_HOTE . ";port=" . $PARAM_PORT . ";dbname=" . $PARAM_BDD,
            $PARAM_USER,
            $PARAM_PWD);
        return true;
        }
        catch (Exception $ex)
        {	echo ("Echec de la connexion a la base de donnees <br>");
        echo ("Erreur numero : " . $ex->getCode() . "<br />" . "Description : " . $ex->getMessage() . "<br>");
        echo ("PARAM_HOTE = " . $PARAM_HOTE);
        return false;
        }
    }
    
    public function __destruct() {
        // ferme la connexion à MySQL :
        unset($this->cnx);
    }
    
    // ------------------------------------------------------------------------------------------------------
    // -------------------------------------- Méthodes d'instances ------------------------------------------
    // ------------------------------------------------------------------------------------------------------
    
    // fournit le niveau (0, 1 ou 2) d'un utilisateur identifié par $pseudo et $mdpSha1
    // cette fonction renvoie un entier :
    //     0 : authentification incorrecte
    //     1 : authentification correcte d'un utilisateur (pratiquant ou personne autorisée)
    //     2 : authentification correcte d'un administrateur
    // modifié par dP le 11/1/2018
    public function getNiveauConnexion($pseudo, $mdpSha1) {
        // préparation de la requête de recherche
        $txt_req = "Select niveau from tracegps_utilisateurs";
        $txt_req .= " where pseudo = :pseudo";
        $txt_req .= " and mdpSha1 = :mdpSha1";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requête et de ses paramètres
        $req->bindValue("pseudo", $pseudo, \PDO::PARAM_STR);
        $req->bindValue("mdpSha1", $mdpSha1, \PDO::PARAM_STR);
        // extraction des données
        $req->execute();
        $uneLigne = $req->fetch(\PDO::FETCH_OBJ);
        // traitement de la réponse
        $reponse = 0;
        if ($uneLigne) {
            $reponse = $uneLigne->niveau;
        }
        // libère les ressources du jeu de données
        $req->closeCursor();
        // fourniture de la réponse
        return $reponse;
    }
    
    
    // fournit true si le pseudo $pseudo existe dans la table tracegps_utilisateurs, false sinon
    // modifié par dP le 27/12/2017
    public function existePseudoUtilisateur($pseudo) {
        // préparation de la requête de recherche
        $txt_req = "Select count(*) from tracegps_utilisateurs where pseudo = :pseudo";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requête et de ses paramètres
        $req->bindValue("pseudo", $pseudo, \PDO::PARAM_STR);
        // exécution de la requête
        $req->execute();
        $nbReponses = $req->fetchColumn(0);
        // libère les ressources du jeu de données
        $req->closeCursor();
        
        // fourniture de la réponse
        if ($nbReponses == 0) {
            return false;
        }
        else {
            return true;
        }
    }
    
    public function existeAdrMailUtilisateur($adrMail)
    {
        // prÃ©paration de la requÃªte de recherche
        $txt_req = "Select adrMail from tracegps_utilisateurs where adrMail = :adrMail";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requÃªte et de ses paramÃ¨tres
        $req->bindValue("adrMail", $adrMail, \PDO::PARAM_STR);
        // exÃ©cution de la requÃªte
        $req->execute();
        $nbReponses = $req->fetchColumn(0);
        // libÃ¨re les ressources du jeu de donnÃ©es
        $req->closeCursor();
        
        // fourniture de la rÃ©ponse
        if ($nbReponses == 0) {
            return false;
        }
        else {
            return true;
        }
    }
    
    
    // fournit un objet Utilisateur à partir de son pseudo $pseudo
    // fournit la valeur null si le pseudo n'existe pas
    // modifié par dP le 9/1/2018
    public function getUnUtilisateur($pseudo) {
        // préparation de la requête de recherche
        $txt_req = "Select id, pseudo, mdpSha1, adrMail, numTel, niveau, dateCreation, nbTraces, dateDerniereTrace";
        $txt_req .= " from tracegps_vue_utilisateurs";
        $txt_req .= " where pseudo = :pseudo";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requête et de ses paramètres
        $req->bindValue("pseudo", $pseudo, \PDO::PARAM_STR);
        // extraction des données
        $req->execute();
        $uneLigne = $req->fetch(\PDO::FETCH_OBJ);
        // libère les ressources du jeu de données
        $req->closeCursor();
        
        // traitement de la réponse
        if ( ! $uneLigne) {
            return null;
        }
        else {
            // création d'un objet Utilisateur
            $unId = mb_convert_encoding($uneLigne->id, "UTF-8");
            $unPseudo = mb_convert_encoding($uneLigne->pseudo, "UTF-8");
            $unMdpSha1 = mb_convert_encoding($uneLigne->mdpSha1, "UTF-8");
            $uneAdrMail = mb_convert_encoding($uneLigne->adrMail, "UTF-8");
            $unNumTel = mb_convert_encoding($uneLigne->numTel, "UTF-8");
            $unNiveau = mb_convert_encoding($uneLigne->niveau, "UTF-8");
            $uneDateCreation = mb_convert_encoding($uneLigne->dateCreation, "UTF-8");
            $unNbTraces = mb_convert_encoding($uneLigne->nbTraces, "UTF-8");
            if (isset($uneLigne->dateDerniereTrace)) {
                $uneDateDerniereTrace = mb_convert_encoding($uneLigne->dateDerniereTrace, "UTF-8");
            } else {
                $uneDateDerniereTrace ="";
            }
            $unUtilisateur = new Utilisateur($unId, $unPseudo, $unMdpSha1, $uneAdrMail, $unNumTel, $unNiveau, $uneDateCreation, $unNbTraces, $uneDateDerniereTrace);
            return $unUtilisateur;
        }
    }
    
    
    // fournit la collection  de tous les utilisateurs (de niveau 1)
    // le résultat est fourni sous forme d'une collection d'objets Utilisateur
    // modifié par dP le 27/12/2017
    public function getTousLesUtilisateurs() {
        // préparation de la requête de recherche
        $txt_req = "Select id, pseudo, mdpSha1, adrMail, numTel, niveau, dateCreation, nbTraces, dateDerniereTrace";
        $txt_req .= " from tracegps_vue_utilisateurs";
        $txt_req .= " where niveau = 1";
        $txt_req .= " order by pseudo";
        
        $req = $this->cnx->prepare($txt_req);
        // extraction des données
        $req->execute();
        $uneLigne = $req->fetch(\PDO::FETCH_OBJ);
        
        // construction d'une collection d'objets Utilisateur
        $lesUtilisateurs = array();
        // tant qu'une ligne est trouvée :
        while ($uneLigne) {
            // création d'un objet Utilisateur
            $unId = mb_convert_encoding($uneLigne->id, "UTF-8");
            $unPseudo = mb_convert_encoding($uneLigne->pseudo, "UTF-8");
            $unMdpSha1 = mb_convert_encoding($uneLigne->mdpSha1, "UTF-8");
            $uneAdrMail = mb_convert_encoding($uneLigne->adrMail, "UTF-8");
            $unNumTel = mb_convert_encoding($uneLigne->numTel, "UTF-8");
            $unNiveau = mb_convert_encoding($uneLigne->niveau, "UTF-8");
            $uneDateCreation = mb_convert_encoding($uneLigne->dateCreation, "UTF-8");
            $unNbTraces = mb_convert_encoding($uneLigne->nbTraces, "UTF-8");
            if (isset($uneLigne->dateDerniereTrace)) {
                $uneDateDerniereTrace = mb_convert_encoding($uneLigne->dateDerniereTrace, "UTF-8");
            } else {
                $uneDateDerniereTrace ="";
            }
            
            $unUtilisateur = new Utilisateur($unId, $unPseudo, $unMdpSha1, $uneAdrMail, $unNumTel, $unNiveau, $uneDateCreation, $unNbTraces, $uneDateDerniereTrace);
            // ajout de l'utilisateur à la collection
            $lesUtilisateurs[] = $unUtilisateur;
            // extrait la ligne suivante
            $uneLigne = $req->fetch(\PDO::FETCH_OBJ);
        }
        // libère les ressources du jeu de données
        $req->closeCursor();
        // fourniture de la collection
        return $lesUtilisateurs;
    }
    
    public function getLesUtilisateursAutorisant($idUtilisateur) {
        // V2
        $collectionUtilisateur = array();
        
        $txt_req = "SELECT idAutorisant FROM tracegps_autorisations WHERE idAutorise = :idAutorise";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requête et de ses paramètres
        $req->bindValue("idAutorise", $idUtilisateur, \PDO::PARAM_INT);
        // exécution de la requête
        $req->execute();
        
        $lignes = $req->fetchAll(\PDO::FETCH_COLUMN);
        
        foreach($lignes as $unId)
        {
            $txt_req = "SELECT pseudo FROM tracegps_vue_utilisateurs WHERE niveau = 1 AND id = :id";
            $req = $this->cnx->prepare($txt_req);
            // liaison de la requête et de ses paramètres
            $req->bindValue("id", $unId, \PDO::PARAM_INT);
            // exécution de la requête
            $req->execute();
            
            $unPseudo = $req->fetch(\PDO::FETCH_OBJ);
            
            $pseudo = mb_convert_encoding($unPseudo->pseudo, "UTF-8");
            
            $unUtilisateur = $this->getUnUtilisateur($pseudo);
            
            $collectionUtilisateur[] = $unUtilisateur;
        }
        // libère les ressources du jeu de données
        $req->closeCursor();
        
        return $collectionUtilisateur;
    }
    
    public function getLesUtilisateursAutorises($idUtilisateur) {
        
        $lesUtilisateurs = array();
        
        // préparation de la requête de recherche
        $txt_req = "Select idAutorise from tracegps_autorisations where idAutorisant = :idAutorisant order by idAutorisant Desc";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requête et de ses paramètres
        $req->bindValue("idAutorisant", $idUtilisateur, \PDO::PARAM_INT);
        // extraction des données
        $req->execute();
        $lignes = $req->fetchAll(\PDO::FETCH_COLUMN);
        // libère les ressources du jeu de données
        
        
        foreach ($lignes as $unId){
            // préparation de la requête de recherche++
            
            $txt_req = "Select pseudo from tracegps_vue_utilisateurs where niveau = 1 and id = :id";
            $req = $this->cnx->prepare($txt_req);
            // liaison de la requête et de ses paramètres
            $req->bindValue("id", $unId, \PDO::PARAM_INT);
            // extraction des données
            $req->execute();
            $unPseudo = $req->fetch(\PDO::FETCH_OBJ);
            
            $pseudo = mb_convert_encoding($unPseudo->pseudo, "UTF-8");
            
            $unUtilisateur = $this->getUnUtilisateur($pseudo);
            
            // ajout de l'utilisateur à la collection
            $lesUtilisateurs[] = $unUtilisateur;
            
        }
        $req->closeCursor();
        
        return $lesUtilisateurs;
        
    }
    
    
    // enregistre l'utilisateur $unUtilisateur dans la bdd
    // fournit true si l'enregistrement s'est bien effectué, false sinon
    // met à jour l'objet $unUtilisateur avec l'id (auto_increment) attribué par le SGBD
    // modifié par dP le 9/1/2018
    public function creerUnUtilisateur($unUtilisateur) {
        // on teste si l'utilisateur existe déjà
        if ($this->existePseudoUtilisateur($unUtilisateur->getPseudo())) return false;
        
        // préparation de la requête
        $txt_req1 = "insert into tracegps_utilisateurs (pseudo, mdpSha1, adrMail, numTel, niveau, dateCreation)";
        $txt_req1 .= " values (:pseudo, :mdpSha1, :adrMail, :numTel, :niveau, :dateCreation)";
        $req1 = $this->cnx->prepare($txt_req1);
        // liaison de la requête et de ses paramètres
        $req1->bindValue("pseudo", mb_convert_encoding($unUtilisateur->getPseudo(), "ISO-8859-1"), \PDO::PARAM_STR);
        $req1->bindValue("mdpSha1", mb_convert_encoding(sha1($unUtilisateur->getMdpsha1()), "ISO-8859-1"), \PDO::PARAM_STR);
        $req1->bindValue("adrMail", mb_convert_encoding($unUtilisateur->getAdrmail(), "ISO-8859-1"), \PDO::PARAM_STR);
        $req1->bindValue("numTel", mb_convert_encoding($unUtilisateur->getNumTel(), "ISO-8859-1"), \PDO::PARAM_STR);
        $req1->bindValue("niveau", mb_convert_encoding($unUtilisateur->getNiveau(), "ISO-8859-1"), \PDO::PARAM_INT);
        $req1->bindValue("dateCreation", mb_convert_encoding($unUtilisateur->getDateCreation(), "ISO-8859-1"), \PDO::PARAM_STR);
        // exécution de la requête
        $ok = $req1->execute();
        // sortir en cas d'échec
        if ( ! $ok) { return false; }
        
        // recherche de l'identifiant (auto_increment) qui a été attribué à la trace
        $unId = $this->cnx->lastInsertId();
        $unUtilisateur->setId($unId);
        return true;
    }
    
    
    // enregistre le nouveau mot de passe $nouveauMdp de l'utilisateur $pseudo après l'avoir hashé en SHA1
    // fournit true si la modification s'est bien effectuée, false sinon
    // modifié par dP le 9/1/2018
    public function modifierMdpUtilisateur($pseudo, $nouveauMdp) {
        // préparation de la requête
        $txt_req = "update tracegps_utilisateurs set mdpSha1 = :nouveauMdp";
        $txt_req .= " where pseudo = :pseudo";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requête et de ses paramètres
        $req->bindValue("nouveauMdp", sha1($nouveauMdp), \PDO::PARAM_STR);
        $req->bindValue("pseudo", $pseudo, \PDO::PARAM_STR);
        // exécution de la requête
        $ok = $req->execute();
        return $ok;
    }
    
    // envoie un mail à l'utilisateur $pseudo avec son nouveau mot de passe $nouveauMdp
    // retourne true si envoi correct, false en cas de problème d'envoi
    // modifié par dP le 9/1/2018
    public function envoyerMdp($pseudo, $nouveauMdp) {
        global $ADR_MAIL_EMETTEUR;
        // si le pseudo n'est pas dans la table tracegps_utilisateurs :
        if ( $this->existePseudoUtilisateur($pseudo) == false ) return false;
        
        // recherche de l'adresse mail
        $adrMail = $this->getUnUtilisateur($pseudo)->getAdrMail();
        
        // envoie un mail à l'utilisateur avec son nouveau mot de passe
        $sujet = "Modification de votre mot de passe d'accès au service TraceGPS";
        $message = "Cher(chère) " . $pseudo . "\n\n";
        $message .= "Votre mot de passe d'accès au service service TraceGPS a été modifié.\n\n";
        $message .= "Votre nouveau mot de passe est : " . $nouveauMdp ;
        $ok = Outils::envoyerMail ($adrMail, $sujet, $message, $ADR_MAIL_EMETTEUR);
        return $ok;
    }
    
    // Le code restant à développer va être réparti entre les membres de l'équipe de développement.
    // Afin de limiter les conflits avec GitHub, il est décidé d'attribuer une zone de ce fichier à chaque développeur.
    // Développeur 1 : lignes 350 à 549
    // Développeur 2 : lignes 550 à 749
    // Développeur 3 : lignes 750 à 949
    // Développeur 4 : lignes 950 à 1150
    
    // Quelques conseils pour le travail collaboratif :
    // avant d'attaquer un cycle de développement (début de séance, nouvelle méthode, ...), faites un Pull pour récupérer 
    // la dernière version du fichier.
    // Après avoir testé et validé une méthode, faites un commit et un push pour transmettre cette version aux autres développeurs.
    
    
    public function autoriseAConsulter($idAutorisant, $idAutorise){
        // préparation de la requête de recherche
        $txt_req = "Select count(*) from tracegps_autorisations where idAutorisant = :idAutorisant and idAutorise = :idAutorise";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requête et de ses paramètres
        $req->bindValue("idAutorisant", $idAutorisant, \PDO::PARAM_INT);
        $req->bindValue("idAutorise", $idAutorise, \PDO::PARAM_INT);
        // exécution de la requête
        $req->execute();
        $autorise = $req->fetchColumn(0);
        // libère les ressources du jeu de données
        $req->closeCursor();
        
        // fourniture de la réponse
        if ($autorise == 0) {
            return false;
        }
        else {
            return true;
        }
        
    }
    
    public function creerUneAutorisation($idAutorisant, $idAutorise)
    {
        if ($this->autoriseAConsulter($idAutorisant,$idAutorise) == true) return false;
        
        // préparation de la requête
        $txt_req1 = "insert into tracegps_autorisations (idAutorisant,idAutorise)";
        $txt_req1 .= " values (:idAutorisant, :idAutorise)";
        $req1 = $this->cnx->prepare($txt_req1);
        // liaison de la requête et de ses paramètres
        $req1->bindValue("idAutorisant", $idAutorisant, \PDO::PARAM_STR);
        $req1->bindValue("idAutorise", $idAutorise, \PDO::PARAM_STR);
        // exécution de la requête
        $ok = $req1->execute();
        // sortir en cas d'échec
        if ( ! $ok) { return false; }
        return true;
    }
    
    public function supprimerUneAutorisation($idAutorisant, $idAutorise){
        // $creationAutorisation = $this->creeUneAutorisation($idAutorisant, $idAutorise);
        
        $txt_req = "DELETE FROM tracegps_autorisations WHERE idAutorisant = :idAutorisant AND idAutorise = :idAutorise";
        $req = $this->cnx->prepare($txt_req);
        
        $req->bindValue("idAutorise", $idAutorise, \PDO::PARAM_INT);
        
        $req->bindValue("idAutorisant", $idAutorisant, \PDO::PARAM_INT);
        
        // exécution de la requête
        $req->execute();
        
        $ok = $req->rowCount();
        // liaison de la requête et de ses paramètres
        //$req->bindValue("idAutorisant", $unId, \PDO::PARAM_INT);
        // exécution de la requête
        
        
        if ($ok > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function getLesPointsDeTrace($idTrace){
        
        // préparation de la requête de recherche
        $txt_req = "Select *";
        $txt_req .= " from tracegps_points";
        $txt_req .= " where idTrace = :idTrace";
        $txt_req .= " order by id";
        
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requête et de ses paramètres
        $req->bindValue("idTrace", $idTrace, \PDO::PARAM_INT);
        // extraction des données
        $req->execute();
        $uneLigne = $req->fetch(\PDO::FETCH_OBJ);
        
        // construction d'une collection d'objets Utilisateur
        $lesPointDeTrace = array();
        // tant qu'une ligne est trouvée :
        while ($uneLigne) {
            // création d'un objet Utilisateur
            $unIdTrace = mb_convert_encoding($uneLigne->idTrace, "UTF-8");
            $unId = mb_convert_encoding($uneLigne->id, "UTF-8");
            $uneLatitude = mb_convert_encoding($uneLigne->latitude, "UTF-8");
            $uneLongitude = mb_convert_encoding($uneLigne->longitude, "UTF-8");
            $uneAltitude = mb_convert_encoding($uneLigne->altitude, "UTF-8");
            $uneDateHeure = mb_convert_encoding($uneLigne->dateHeure, "UTF-8");
            $unRythmeCardio = mb_convert_encoding($uneLigne->rythmeCardio, "UTF-8");
            $unTempsCumule = 0;
            $uneDistanceCumulee =0;
            $uneVitesse = 0;
            
            $unPoint = new PointDeTrace($unIdTrace, $unId, $uneLatitude, $uneLongitude, $uneAltitude, $uneDateHeure, $unRythmeCardio, $unTempsCumule, $uneDistanceCumulee, $uneVitesse);
            // ajout de l'utilisateur à la collection
            $lesPointDeTrace[] = $unPoint;
            // extrait la ligne suivante
            $uneLigne = $req->fetch(\PDO::FETCH_OBJ);
        }
        // libère les ressources du jeu de données
        $req->closeCursor();
        // fourniture de la collection
        return $lesPointDeTrace;
        
    }
    
    public function getUneTrace($idTrace)
    {
        // préparation de la requête de recherche
        $txt_req = "SELECT id, dateDebut, dateFin, terminee, idUtilisateur FROM tracegps_traces WHERE id=:id";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requête et de ses paramètres
        $req->bindValue("id", $idTrace, \PDO::PARAM_INT);
        // extraction des données
        $req->execute();
        $laTrace = $req->fetch(\PDO::FETCH_OBJ);
        // traitement de la réponse
        if (!$laTrace) {
            return null;
        } else {
            $unId = mb_convert_encoding($laTrace->id, "UTF-8");
            $uneDateDebut = mb_convert_encoding($laTrace->dateDebut, "UTF-8");
            $uneDateFin = $laTrace->dateFin; // corrected case of dateFin
            $unIdUtilisateur = mb_convert_encoding($laTrace->idUtilisateur, "UTF-8");
            $terminee = mb_convert_encoding($laTrace->terminee, "UTF-8");
            
            $newTrace = new Trace($unId, $uneDateDebut, $uneDateFin, $terminee, $unIdUtilisateur);
            
            // Call getLesPointsDeTrace with the trace ID
            $LesPointsDeTrace = $this->getLesPointsDeTrace($unId);
            
            foreach ($LesPointsDeTrace as $points) {
                // Assuming $points is an object with a method getLesPointsDeTrace
                $newTrace->ajouterPoint($points);
                // Do something with $newTracePoints, maybe add them to $newTrace
                // ...
            }
            
            return $newTrace;
        }
    }
    
    public function getToutesLesTraces(){
        // préparation de la requête de recherche
        $txt_req = "Select *";
        $txt_req .= " from tracegps_traces";
        $txt_req .= " order by id";
        
        $req = $this->cnx->prepare($txt_req);
        
        // extraction des données
        $req->execute();
        $uneLigne = $req->fetch(\PDO::FETCH_OBJ);
        
        // construction d'une collection d'objets Utilisateur
        $trace = array();
        // tant qu'une ligne est trouvée :
        while ($uneLigne) {
            // création d'un objet Utilisateur
            $unId = mb_convert_encoding($uneLigne->id, "UTF-8");
            $uneDateHeureDebut = mb_convert_encoding($uneLigne->dateDebut, "UTF-8");
            if ($uneLigne->dateFin != NULL) {
                $uneDateHeureFin = mb_convert_encoding($uneLigne->dateFin, "UTF-8");
            }
            else {
                $uneDateHeureFin = NULL;
            }
            
            $terminee = mb_convert_encoding($uneLigne->terminee, "UTF-8");
            $unIdUtilisateur = mb_convert_encoding($uneLigne->idUtilisateur, "UTF-8");
            
            
            $uneTrace = new Trace($unId, $uneDateHeureDebut, $uneDateHeureFin, $terminee, $unIdUtilisateur);
            $uneTrace->setLesPointsDeTrace($this->getLesPointsDeTrace($unId));
            
            // ajout de la trace à la collection
            $trace[] = $uneTrace;
            // extrait la ligne suivante
            $uneLigne = $req->fetch(\PDO::FETCH_OBJ);
        }
        // libère les ressources du jeu de données
        $req->closeCursor();
        // fourniture de la collection
        return $trace;
    }
    
    public function getLesTraces($idUtilisateur){
        
        
        $txt_req = "SELECT * ";
        $txt_req .= "FROM tracegps_traces ";
        $txt_req .= "WHERE idUtilisateur = :idUtilisateur";
        
        $req = $this->cnx->prepare($txt_req);
        
        $req->bindValue("idUtilisateur", $idUtilisateur, \PDO::PARAM_INT);
        
        $req->execute();
        
        $uneLigne = $req->fetch(\PDO::FETCH_OBJ);
        
        $lesTraces = array();
        while($uneLigne) {
            $unIdTrace = $uneLigne->id;
            $uneDateHeureDebut = mb_convert_encoding($uneLigne->dateDebut,  "UTF-8");
            if ($uneLigne->dateFin != NULL) {
                $uneDateHeureFin = mb_convert_encoding($uneLigne->dateFin, "UTF-8");
            }
            else {
                $uneDateHeureFin = NULL;
            }
            $terminee = mb_convert_encoding($uneLigne->terminee,  "UTF-8");
            $unIdUtilisateur= mb_convert_encoding($uneLigne->idUtilisateur, "UTF-8");
            
            $uneTrace = new Trace($unIdTrace, $uneDateHeureDebut, $uneDateHeureFin, $terminee, $unIdUtilisateur);
            $uneTrace->setLesPointsDeTrace($this->getLesPointsDeTrace($unIdTrace));
            //constructeur de la classe Trace
            $lesTraces[] = $uneTrace;
            // extrait la ligne suivante
            $uneLigne = $req->fetch(\PDO::FETCH_OBJ);
        }
        
        $req->CloseCursor();
        
        return $lesTraces;
    }
    
    // fonction fournissant la collection des traces que l'id de l'utilisateur founit en paramètre a le droit de consulter
    public function getLesTracesAutorisees ($idUtilisateur) {
        
        // Déclaration des variables de type array
        $lesTraces = array();
        // $idAutorisant[] = $idUtilisateur;
        
        // récupérer des pseudos autorisant l'utilisateur fournit en paramètre de consulter leurs traces
        $lesAutorisants = $this->getLesUtilisateursAutorisant ($idUtilisateur);
        
        // déclaration d'une boucle qui va parcourir la liste des utilisateurs autorisants
        foreach ($lesAutorisants as $unAutorisant) {
            
            // Récupération de l'id des utilisateurs autorisants
            $unePersonneAutorisant = $unAutorisant->getId();
            
            // Création d'une requête qui va récupérer la trace créé par l'utilisateur autorisant fournit en paramètre
            $txt_req = "SELECT id, dateDebut, dateFin, terminee, idUtilisateur FROM tracegps_traces WHERE idUtilisateur = :idUtilisateur";
            $req = $this->cnx->prepare($txt_req);
            // liaison de la requête et de ses paramètres
            $req->bindValue("idUtilisateur", $unePersonneAutorisant, \PDO::PARAM_INT);
            // exécution de la requête
            $req->execute();
            
            $uneLigne = $req->fetch(\PDO::FETCH_OBJ);
            
            while ($uneLigne) {
                // création d'un objet Utilisateur
                $unId = mb_convert_encoding($uneLigne->id, "UTF-8");
                $uneDateHeureDebut = mb_convert_encoding($uneLigne->dateDebut, "UTF-8");
                if ($uneLigne->dateFin != NULL) {
                    $uneDateHeureFin = mb_convert_encoding($uneLigne->dateFin, "UTF-8");
                }
                else {
                    $uneDateHeureFin = NULL;
                }
                
                $traceTerminee = $uneLigne->terminee;
                
                if ($traceTerminee == 1) {
                    $terminee = true;
                }
                
                else {
                    $terminee = false;
                }
                
                $unIdUtilisateur = mb_convert_encoding($uneLigne->idUtilisateur, "UTF-8");
                
                
                $uneTrace = new Trace($unId, $uneDateHeureDebut, $uneDateHeureFin, $terminee, $unIdUtilisateur);
                $lesPointsDeTrace = $this->getLesPointsDeTrace($unId);
                
                foreach ($lesPointsDeTrace as $unPointDeTrace) {
                    $uneTrace->ajouterPoint($unPointDeTrace);
                }
                
                // ajout de la trace à la collection
                $lesTraces[] = $uneTrace;
                
                // extrait la ligne suivante
                $uneLigne = $req->fetch(\PDO::FETCH_OBJ);
            }
            // libère les ressources du jeu de données
            $req->closeCursor();
        }
        // fourniture de la collection
        return $lesTraces;
    }
    
    public function creerUneTrace($uneTrace){
        // on vérifie si la trace existe déjà
        //if ($this->existePseudoUtilisateur($unUtilisateur->getPseudo())) return false;
        
        $txt_req = "INSERT INTO tracegps_traces (dateDebut, dateFin, terminee, idUtilisateur) ";
        $txt_req .= "VALUES (:dateDebut, :dateFin, :terminee, :idUtilisateur) ";
        
        $req = $this->cnx->prepare($txt_req);
        
        $req->bindValue("dateDebut", mb_convert_encoding($uneTrace->getDateHeureDebut(), "ISO-8859-1"), \PDO::PARAM_STR);
        
        if ($uneTrace->getDateHeureFin() != NULL) {
            $req->bindValue("dateFin", mb_convert_encoding($uneTrace->getDateHeureFin(), "ISO-8859-1"), \PDO::PARAM_STR);
        }
        
        else {
            $req->bindValue("dateFin", $uneTrace->getDateHeureFin(), \PDO::PARAM_STR);
        }
        $req->bindValue("terminee", mb_convert_encoding($uneTrace->getTerminee(), "ISO-8859-1"), \PDO::PARAM_INT);
        $req->bindValue("idUtilisateur", mb_convert_encoding($uneTrace->getIdUtilisateur(), "ISO-8859-1"), \PDO::PARAM_INT);
        
        // exécution de la requête
        $ok = $req->execute();
        
        // sortir en cas d'échec
        if ( ! $ok) { return false; }
        
        // recherche de l'identifiant (auto_increment) qui a été attribué à la trace
        $unId = $this->cnx->lastInsertId();
        $uneTrace->setId($unId);
        return true;
    }
    
    public function terminerUneTrace($idTrace){
        
        $taille = sizeof($this->getLesPointsDeTrace($idTrace));
        
        if ($taille >0 ) {
            $dernierPoint = $this->getLesPointsDeTrace($idTrace)[$taille-1];
            $uneDateHeure = $dernierPoint->getDateHeure();
        }
        else {
            $uneDateHeure = date('Y-m-d h:i:s', time());
        }
        
        // préparation de la requête
        $txt_req = "update tracegps_traces set terminee = 1, dateFin = :dateFin where id = :id";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requête et de ses paramètres
        $req->bindValue("id", $idTrace, \PDO::PARAM_INT);
        $req->bindValue("dateFin", $uneDateHeure, \PDO::PARAM_STR);
        // exécution de la requête
        $ok = $req->execute();
        return $ok;
    }
    
    public function supprimerUneTrace($idTrace) {
        // préparation de la requête de supression
        $txt_req = "DELETE from tracegps_points  WHERE idTrace = :idTrace";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requête et de ses paramètres
        $req->bindValue("idTrace", $idTrace, \PDO::PARAM_INT);
        // exécution de la requête
        $req->execute();
        // préparation de la requête de supression
        $txt_req2 = "DELETE from tracegps_traces  WHERE id = :idTrace";
        $req2 = $this->cnx->prepare($txt_req2);
        $req2->bindValue("idTrace", $idTrace, \PDO::PARAM_INT);
        // exécution de la requête
        $req2->execute();
        // vérification du résultat
        $ok = $req2->rowCount();
        // libère les ressources du jeu de données
        $req->closeCursor();
        
        if ($ok > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    } 
    
    public function creerUnPointDeTrace (PointDeTrace $unPointDeTrace) {
        
        $txt_req = "INSERT INTO tracegps_points VALUES (:idTrace, :id, :latitude, :longitude, :altitude, :dateHeure, :rythmeCardio)";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requête et de ses paramètres
        $req->bindValue("idTrace", mb_convert_encoding($unPointDeTrace->getIdTrace(), "ISO-8859-1"), \PDO::PARAM_INT);
        $req->bindValue("id", mb_convert_encoding($unPointDeTrace->getId(), "ISO-8859-1"), \PDO::PARAM_INT);
        $req->bindValue("latitude", mb_convert_encoding($unPointDeTrace->getLatitude(), "ISO-8859-1"), \PDO::PARAM_INT);
        $req->bindValue("longitude", mb_convert_encoding($unPointDeTrace->getLongitude(), "ISO-8859-1"), \PDO::PARAM_INT);
        $req->bindValue("altitude", mb_convert_encoding($unPointDeTrace->getAltitude(), "ISO-8859-1"), \PDO::PARAM_INT);
        $req->bindValue("dateHeure", mb_convert_encoding($unPointDeTrace->getDateHeure(), "ISO-8859-1"), \PDO::PARAM_STR);
        $req->bindValue("rythmeCardio", mb_convert_encoding($unPointDeTrace->getRythmeCardio(), "ISO-8859-1"), \PDO::PARAM_INT);
        
        // extraction des données
        $req->execute();
        
        $IdTrace = $unPointDeTrace->getIdTrace();
        
        if ($unPointDeTrace->getId() == 1) {
            $dateDebut = $unPointDeTrace->getDateHeure();
            $txt_req = "UPDATE tracegps_traces SET dateDebut = :dateDebut WHERE id = :idTrace";
            $req = $this->cnx->prepare($txt_req);
            $req->bindValue("dateDebut", mb_convert_encoding($unPointDeTrace->getIdTrace(), "ISO-8859-1"), \PDO::PARAM_STR);
            $req->bindValue("idTrace", mb_convert_encoding($unPointDeTrace->getIdTrace(), "ISO-8859-1"), \PDO::PARAM_INT);
            // extraction des données
            $ok = $req->execute();
            
            if ( ! $ok) { return false; }
        }
        return true;
    }
    
    // supprime l'utilisateur $pseudo dans la bdd, ainsi que ses traces et ses autorisations
    // fournit true si l'effacement s'est bien effectué, false sinon
    // modifié par dP le 9/1/2018
    public function supprimerUnUtilisateur($pseudo) {
        $unUtilisateur = $this->getUnUtilisateur($pseudo);
        if ($unUtilisateur == null) {
            return false;
        }
        else {
            $idUtilisateur = $unUtilisateur->getId();
            
            // suppression des traces de l'utilisateur (et des points correspondants)
            $lesTraces = $this->getLesTraces($idUtilisateur);
            if($lesTraces != null)
            {
                foreach ($lesTraces as $uneTrace) {
                    $this->supprimerUneTrace($uneTrace->getId());
                }
            }
            // préparation de la requête de suppression des autorisations
            $txt_req1 = "delete from tracegps_autorisations" ;
            $txt_req1 .= " where idAutorisant = :idUtilisateur or idAutorise = :idUtilisateur";
            $req1 = $this->cnx->prepare($txt_req1);
            // liaison de la requête et de ses paramètres
            $req1->bindValue("idUtilisateur", mb_convert_encoding($idUtilisateur, "ISO-8859-1"), \PDO::PARAM_INT);
            // exécution de la requête
            $ok = $req1->execute();
            
            // préparation de la requête de suppression de l'utilisateur
            $txt_req2 = "delete from tracegps_utilisateurs" ;
            $txt_req2 .= " where pseudo = :pseudo";
            $req2 = $this->cnx->prepare($txt_req2);
            // liaison de la requête et de ses paramètres
            $req2->bindValue("pseudo", mb_convert_encoding($pseudo, "ISO-8859-1"), \PDO::PARAM_STR);
            // exécution de la requête
            $ok = $req2->execute();
            return $ok;
        }
    }
    
 
} // fin de la classe DAO

// ATTENTION : on ne met pas de balise de fin de script pour ne pas prendre le risque
// d'enregistrer d'espaces après la balise de fin de script !!!!!!!!!!!!