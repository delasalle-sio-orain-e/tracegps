<?php

namespace api\services;
// Projet TraceGPS - services web
// fichier :  api/services/ValiderDemandeAutorisation.php
// Dernière mise à jour : 3/7/2021 par dP

// Rôle : ce service web permet à un utilisateur destinataire d'accepter ou de rejeter une demande d'autorisation provenant d'un utilisateur demandeur
// il envoie un mail au demandeur avec la décision de l'utilisateur destinataire

// Le service web doit être appelé avec 4 paramètres obligatoires dont les noms sont volontairement non significatifs :
//    a : le mot de passe (hashé) de l'utilisateur destinataire de la demande ($mdpSha1)
//    b : le pseudo de l'utilisateur destinataire de la demande ($pseudoAutorisant)
//    c : le pseudo de l'utilisateur source de la demande ($pseudoAutorise)
//    d : la decision 1=oui, 0=non ($decision)

// Les paramètres doivent être passés par la méthode GET :
//     http://<hébergeur>/tracegps/api/ValiderDemandeAutorisation?a=13e3668bbee30b004380052b086457b014504b3e&b=oxygen&c=europa&d=1

// ces variables globales sont définies dans le fichier modele/parametres.php
global $ADR_MAIL_EMETTEUR, $ADR_SERVICE_WEB;

use modele\Outils;
use DOMDocument;

// connexion du serveur web à la base MySQL
use modele\DAO;
$dao = new DAO();

// Récupération des données transmises
$mdp = ( empty($this->request['mdp'])) ? "" : $this->request['mdp'];
$pseudo = ( empty($this->request['pseudo'])) ? "" : $this->request['pseudo'];
$pseudoARetirer = ( empty($this->request['pseudoARetirer'])) ? "" : $this->request['pseudoARetirer'];
$lang = ( empty($this->request['lang'])) ? "" : $this->request['lang'];
$texteMessage = ( empty($this->request['texteMessage'])) ? "" : $this->request['texteMessage'];


if ($lang != "json") $lang = "xml";

// La méthode HTTP utilisée doit être GET
if ($this->getMethodeRequete() != "GET")
{	$msg = "Erreur : méthode HTTP incorrecte.";
$code_reponse = 406;
}
else {
    // Les paramètres doivent être présents et corrects
    if ( $mdp == "" || $pseudo == "" || $pseudoARetirer == "")
    {	$msg = "Erreur : données incomplètes ou incorrectes.";
    $code_reponse = 400;
    }
    else
    {	// test de l'authentification de l'utilisateur
        // la méthode getNiveauConnexion de la classe DAO retourne les valeurs 0 (non identifié) ou 1 (utilisateur) ou 2 (administrateur)
        $niveauConnexion = $dao->getNiveauConnexion($pseudo, $mdp);
        
        if ( $niveauConnexion == 0 )
        {  $msg = "Erreur : authentification incorrecte.";
        $code_reponse = 401;
        }
        else
        {	
            $utilisateurDemandeur = $dao->getUnUtilisateur($pseudo);
            $utilisateurDestinataire = $dao->getUnUtilisateur($pseudoARetirer);
            
            // contrôle d'existence de pseudoAsupprimer
            if ($utilisateurDestinataire == null)
            {   
                $msg = "Erreur : pseudo utilisateur inexistant.";
                $code_reponse = 400;
            }
            else {
                $idAutorisant = $utilisateurDestinataire->getId();
                $idAutorise = $utilisateurDemandeur->getId();
                $bool= FALSE;
                $utilisateursAutorisants = $dao->getLesUtilisateursAutorisant($utilisateurDestinataire->getId());
                foreach($utilisateursAutorisants as $utilisateur){
                    if($utilisateur->getPseudo() == $pseudo){
                        $bool = TRUE;
                    }
                }
                if(!$bool){
                    
                    $msg = "Erreur : l'autorisation n'était pas accordée.";
                    $code_reponse = 400;
                    
                }else{
                    
                   $ok = $dao-> supprimerUneAutorisation($idAutorisant, $idAutorise);
                    
                    if(!$ok)
                    {
                        $msg= "Erreur : problème lors de la suppression de l'autorisation";
                        $code_reponse = 400;
                    }
                
                    else{
                        if($texteMessage != "" ) {
                            $adrMailDemandeur = $utilisateurDemandeur->getAdrMail();
                            // envoi d'un mail avec msg perso
                            $sujetMail = "Votre demande de suppression à un utilisateur du système TraceGPS";
                            $contenuMail = "Cher ou chère " . $pseudoARetirer . "\n\n";
                            $contenuMail .= "L'utilisateur" . $pseudo." dus système TRACEGPS vous retire l'autorisation de suivre ses parcours\n\n";
                            $contenuMail .= "Son message".$texteMessage."\n\n";
                            $contenuMail .= "Cordialement.\n\n";
                            $contenuMail .= "L'administrateur du système TraceGPS";
                            $ok = Outils::envoyerMail($adrMailDemandeur, $sujetMail, $contenuMail, $ADR_MAIL_EMETTEUR);
                            
                            if ( !$ok ) {
                                $msg = "Erreur : autorisation supprimée ; l'envoi du courriel de notification a rencontré un problème.";
                                $code_reponse = 500;
                            }else{
                                $msg = "Autorisation supprimée. ;" .$pseudoARetirer. " va recevoir un courriel de notification.";
                                $code_reponse = 200;
                            }
                        }else{
                            $msg = "Autorisation supprimée.";
                            $code_reponse = 200;
                        }
                    }
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
    $donnees = creerFluxXML ($msg);
}
else {
    $content_type = "application/json; charset=utf-8";      // indique le format Json pour la réponse
    $donnees = creerFluxJSON ($msg);
}

// envoi de la réponse HTTP
$this->envoyerReponse($code_reponse, $content_type, $donnees);

// fin du programme (pour ne pas enchainer sur les 2 fonctions qui suivent)
exit;

// ================================================================================================

// création du flux XML en sortie
function creerFluxXML($msg)
{
    /* Exemple de code XML
     <?xml version="1.0" encoding="UTF-8"?>
     <!--Service web ChangerDeMdp - BTS SIO - Lycée De La Salle - Rennes-->
     <data>
     <reponse>Erreur : authentification incorrecte.</reponse>
     </data>
     */
    
    // crée une instance de DOMdocument (DOM : Document Object Model)
    $doc = new DOMDocument();
    
    // specifie la version et le type d'encodage
    $doc->version = '1.0';
    $doc->encoding = 'UTF-8';
    
    // crée un commentaire et l'encode en UTF-8
    $elt_commentaire = $doc->createComment('Service web ChangerDeMdp - BTS SIO - Lycée De La Salle - Rennes');
    // place ce commentaire à la racine du document XML
    $doc->appendChild($elt_commentaire);
    
    // crée l'élément 'data' à la racine du document XML
    $elt_data = $doc->createElement('data');
    $doc->appendChild($elt_data);
    
    // place l'élément 'reponse' juste après l'élément 'data'
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
    /* Exemple de code JSON
     {
     "data": {
     "reponse": "Erreur : authentification incorrecte."
     }
     }
     */
    
    // construction de l'élément "data"
    $elt_data = ["reponse" => $msg];
    
    // construction de la racine
    $elt_racine = ["data" => $elt_data];
    
    // retourne le contenu JSON (l'option JSON_PRETTY_PRINT gère les sauts de ligne et l'indentation)
    return json_encode($elt_racine, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}