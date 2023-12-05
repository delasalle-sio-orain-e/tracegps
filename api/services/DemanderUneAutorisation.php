<?php

namespace api\services;

use DOMDocument;
use modele\Outils;

// connexion du serveur web à la base MySQL
use modele\DAO;
$dao = new DAO();

// Récupération des données transmises
$pseudo = ( empty($this->request['pseudo'])) ? "" : $this->request['pseudo'];
$mdp = ( empty($this->request['mdp'])) ? "" : $this->request['mdp'];
$pseudoDestinataire = ( empty($this->request['pseudoDestinataire'])) ? "" : $this->request['pseudoDestinataire'];
$texteMessage = ( empty($this->request['texteMessage'])) ? "" : $this->request['texteMessage'];
$nomPrenom = ( empty($this->request['nomPrenom'])) ? "" : $this->request['nomPrenom'];
$lang = ( empty($this->request['lang'])) ? "" : $this->request['lang'];

// "xml" par défaut si le paramètre lang est absent ou incorrect
if ($lang != "json") $lang = "xml";

// La méthode HTTP utilisée doit être GET
if ($this->getMethodeRequete() != "GET")
{	$msg = "Erreur : méthode HTTP incorrecte.";
$code_reponse = 406;
}
else{
    // Les paramètres doivent être présents
    if ( $pseudo == "" || $mdp == "" || $pseudoDestinataire == "" || $nomPrenom == "" )
    {	$msg = "Erreur : données incomplètes.";
    $code_reponse = 400;
    }
    else{
        // test de l'authentification de l'utilisateur
        // la méthode getNiveauConnexion de la classe DAO retourne les valeurs 0 (non identifié) ou 1 (utilisateur) ou 2 (administrateur)
        $niveauConnexion = $dao->getNiveauConnexion($pseudo, $mdp);
        
        if ( $niveauConnexion == 0 )
        {
            $msg = "Erreur : authentification incorrecte.";
            $code_reponse = 401;
        }
        else{
            // test de l'authentification de l'utilisateur
            // la méthode getNiveauConnexion de la classe DAO retourne les valeurs 0 (non identifié) ou 1 (utilisateur) ou 2 (administrateur)
            $pseudoDestExiste = $dao->existePseudoUtilisateur($pseudoDestinataire);
            
            if ( $pseudoDestExiste == false )
            {
                $msg = "Erreur : pseudo utilisateur inexistant.";
                $code_reponse = 401;
            }
            else{
                $adrMailEmetteur = $dao->getUnUtilisateur($pseudo)->getAdrMail();
                $adrMailReceveur =$dao->getUnUtilisateur($pseudoDestinataire)->getAdrMail();
                $numTel = $dao->getUnUtilisateur($pseudo)->getNumTel();
                // envoi d'un mail d'acceptation à l'intéressé
                $sujetMail = "Votre demande d'autorisation à un utilisateur du système TraceGPS";
                $contenuMail = "Cher ou chère " . $pseudoDestinataire . "\n\n";
                $contenuMail = "Un utilisateur de TraceGPS cous demande l'authorisation de suivre votre parcourt \n\n";
                $contenuMail = "Voici les information le concernant  \n\n";
                $contenuMail .= "Son pseudo : " . $pseudo . " \n\n";
                $contenuMail .= "Son adresse mail : " . $adrMailEmetteur . " \n\n";
                $contenuMail .= "Son numero de telephone : " . $numTel . " \n\n";
                $contenuMail .= "Son nom et prenom : " . $nomPrenom . " \n\n";
                $contenuMail .= "\n\n Pour accepter la demande cliquer sur ce lien : \n" ;
                $contenuMail .= "http://localhost/ws-php-theo/tracegps/api/DemanderUneAutorisation.php?a=".$mdp."&b=".$pseudo."&c=".$pseudoDestinataire."&d=1\n\n";
                $contenuMail .= "\n\n Pour rejeter la demande cliquer sur ce lien : \n" ;
                $contenuMail .= "http://localhost/ws-php-theo/tracegps/api/DemanderUneAutorisation.php?a=".$mdp."&b=".$pseudo."&c=".$pseudoDestinataire."&d=0\n\n" ;
                
                
                $ok = Outils::envoyerMail($adrMailEmetteur, $sujetMail, $contenuMail, $adrMailReceveur);
                if ( ! $ok ) {
                    $msg = "Erreur : l'envoi du courriel au demandeur a rencontré un problème.";
                    $code_reponse = 500;
                }
                else {
                    $msg = "Autorisation enregistrée.<br>Le demandeur va recevoir un courriel de confirmation.";
                    $code_reponse = 200;
                }
            }
        }
    }
}
// ferme la connexion à MySQL :
unset($dao);

// création du flux en sortie
if ($lang == "xml") {
    $content_type = "application/xml; charset=utf-8";      // indique le format XML pour la réponse
    $donnees = creerFluxXML($msg);
}
else {
    $content_type = "application/json; charset=utf-8";      // indique le format Json pour la réponse
    $donnees = creerFluxJSON($msg);
}

// envoi de la réponse HTTP
$this->envoyerReponse($code_reponse, $content_type, $donnees);

// fin du programme (pour ne pas enchainer sur les 2 fonctions qui suivent)
exit;

// ================================================================================================

// création du flux XML en sortie
function creerFluxXML($msg)
{
    // crée une instance de DOMdocument (DOM : Document Object Model)
    $doc = new DOMDocument();
    
    // specifie la version et le type d'encodage
    $doc->version = '1.0';
    $doc->encoding = 'UTF-8';
    
    // crée un commentaire et l'encode en UTF-8
    $elt_commentaire = $doc->createComment('Service web GetTousLesUtilisateurs - BTS SIO - Lycée De La Salle - Rennes');
    // place ce commentaire à la racine du document XML
    $doc->appendChild($elt_commentaire);
    
    // crée l'élément 'data' à la racine du document XML
    $elt_data = $doc->createElement('data');
    $doc->appendChild($elt_data);
    
    // place l'élément 'reponse' dans l'élément 'data'
    $elt_reponse = $doc->createElement('reponse', $msg);
    $elt_data->appendChild($elt_reponse);
    
    
    // Mise en forme finale
    $doc->formatOutput = true;
    
    // renvoie le contenu XML
    return $doc->saveXML();
}

// ================================================================================================

// création du flux JSON en sortie
function creerFluxJSON($msg)
{
    
    // construction de l'élément "data"
    $elt_data = ["reponse" => $msg];
    
    
    // construction de la racine
    $elt_racine = ["data" => $elt_data];
    
    // retourne le contenu JSON (l'option JSON_PRETTY_PRINT gère les sauts de ligne et l'indentation)
    return json_encode($elt_racine, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

// ================================================================================================



?>