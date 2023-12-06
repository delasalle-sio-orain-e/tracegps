<?php 

// Projet TraceGPS - services web
// fichier : api/services/EnvoyerPosition.php
// Dernière mise à jour : Mercredi 29 novembre 2023 par Singuila MBAYE-AMADOU

//Rôle : ce service web permet à un utilisateur authentifié d'envoyer sa position.
//Paramètres à fournir :
//• pseudo : le pseudo de l'utilisateur
//• mdp : le mot de passe de l'utilisateur hashé en sha1
//• idTrace : l'id de la trace dont le point fera partie
//• dateHeure : la date et l'heure au point de passage (format 'Y-m-d H:i:s')
//• latitude : latitude du point de passage
//• longitude : longitude du point de passage
//• altitude : altitude du point de passage
//• rythmeCardio : rythme cardiaque au point de passage (ou 0 si le rythme n'est pas mesurable)
//• lang : le langage utilisé pour le flux de données ("xml" ou "json")
//Description du traitement :
//• Vérifier que les données transmises sont complètes
//• Vérifier l'authentification de l'utilisateur
//• Vérifier l'existence du numéro de trace
//• Vérifier que la trace appartient bien à l'utilisateur
//• Vérifier que la trace n'est pas encore terminée
//• Enregistrer le point dans la base de données
//• Retourner l'id du point

use modele\Outils;

// connexion du serveur web à la base MySQL
use modele\DAO;
use modele\PointDeTrace;
use modele\Trace;
use modele\Utilisateur;


$dao = new DAO();

// Récupération des données transmises
$pseudo = ( empty($this->request['pseudo'])) ? "" : $this->request['pseudo'];
$mdpSha1 = ( empty($this->request['mdp'])) ? "" : $this->request['mdp'];
$idTrace = ( empty($this->request['idTrace'])) ? "" : $this->request['idTrace'];
$dateHeure = ( empty($this->request['dateHeure'])) ? "" : $this->request['dateHeure'];
$latitude = ( empty($this->request['latitude'])) ? "" : $this->request['latitude'];
$longitude = ( empty($this->request['longitude'])) ? "" : $this->request['longitude'];
$altitude = ( empty($this->request['altitude'])) ? "" : $this->request['altitude'];
$rythmeCardio = ( empty($this->request['rythmeCardio'])) ? "" : $this->request['rythmeCardio'];
$lang = ( empty($this->request['lang'])) ? "" : $this->request['lang'];

//$unUtilisateur = $dao->getUnUtilisateur($pseudo);
$tempsCumule = 0;
$distanceCumulee =0;
$vitesse = 0;

$idPoint = null;
$uneTrace = null;

// "xml" par défaut si le paramètre lang est absent ou incorrect
if ($lang != "json") $lang = "xml";



// Vérifier si les valeurs sont complètes

if ($this->getMethodeRequete() != "GET")
{
    $msg = "Erreur : méthode HTTP incorrecte.";
    $code_reponse = 406;
}
else {
    // Les paramètres doivent être présents
    if ( $pseudo == "" || $mdpSha1 == "" || $idTrace == "" || $dateHeure == "" || $latitude == "" || $longitude == "" || $altitude == "" || $rythmeCardio == "")
    {
        $msg = "Erreur : données incomplètes.";
        $code_reponse = 400;
    }
    else
    {
        
        if ( $dao->getNiveauConnexion($pseudo, $mdpSha1) == 0 )
        {
            $msg = "Erreur : authentification incorrecte.";
            $code_reponse = 401; 
        }
        else
        {         
            if ($dao->getUneTrace($idTrace) == null)
            {
                $msg = "Erreur : le numéro de trace n'existe pas.";
                $code_reponse = 405;
                
            }
            else {
                $unUtilisateur = $dao->getUnUtilisateur($pseudo);
                $idUtilisateur = $unUtilisateur->getId();
                $lesTraces = $dao->getLesTraces($idUtilisateur);
                $ok=false;
                
                foreach ($lesTraces as $uneTrace){
                    
                    if ($uneTrace->getId() == $idTrace){
                        $ok = true;
                    }
                }
                if (!$ok) {
                    $msg = "Erreur : le numéro de trace ne correspond pas à cet utilisateur.";
                    $code_reponse = 405;
                    
                }
                else
                {
                    $uneTrace = $dao->getUneTrace($idTrace);
                    $terminee = $uneTrace->getTerminee();
                    if ($terminee != 0)
                    {
                        $msg = "Erreur : la trace est déjà terminée.";
                        $code_reponse = 405;
                        
                    }
                    else
                    {
                        $idNewPoint = $uneTrace->getNombrePoints();
                        $idNewPoint ++;
                        $point = new PointDeTrace($idTrace, $idNewPoint, $latitude, $longitude, $altitude, $dateHeure, $rythmeCardio, $tempsCumule, $distanceCumulee, $vitesse);
                        $ok = $dao->creerUnPointDeTrace($point);
                        
                        if (!$ok)
                        {
                            $msg = "Erreur : problème lors de l'enregistrement du point.";
                            $code_reponse = 405;
                            
                        }
                        else {
                            $msg = "Point cree";
                            $code_reponse = 200;
                        }
                        
                    }
                }
            }
            
            
            
            //$idUtilisateur = $dao->getUnUtilisateur($pseudo)->getId();
            // récupération des utilisateurs autorisant via la méthode getLesUtilisateursAutorisés de la classe DAO
            //$lesUtilisateurs = $dao->getLesUtilisateursAutorises($idUtilisateur);
            // mémorisation du nombre d'utilisateurs
            //$nbReponses = sizeof($lesUtilisateurs);
            //if ($nbReponses == 0)
            //{
            //    $msg = "Aucune autorisation accordée à " . $pseudo . ".";
            //    $code_reponse = 200;
            //}
            //else
            //{
            //    $msg = $nbReponses . " autorisation(s) accordée(s) à " . $pseudo . ".";
            //    $code_reponse = 200;
            //}
        }
    }
}

// ferme la connexion à MySQL :
unset($dao);

// création du flux en sortie
if ($lang == "xml") {
    $content_type = "application/xml; charset=utf-8";      // indique le format XML pour la réponse
    $donnees = creerFluxXML($msg, $uneTrace);
}
else {
    $content_type = "application/json; charset=utf-8";      // indique le format Json pour la réponse
    $donnees = creerFluxJSON($msg, $uneTrace);
}

$this->envoyerReponse($code_reponse, $content_type, $donnees);

exit;

// *--------*

function creerFluxXML($msg, $uneTrace)
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
    
    $elt_donnees = $doc->createElement('donnees');
    $elt_data->appendChild($elt_donnees);
    if($uneTrace)
    {
        $id = $uneTrace->getNombrePoints()+1;
        $elt_id = $doc->createElement('id', $id);
        $elt_donnees->appendChild($elt_id);
    }
   
    // Mise en forme finale
    $doc->formatOutput = true;
    
    // renvoie le contenu XML
    return $doc->saveXML();
}

function creerFluxJson($msg, $uneTrace)
{
    $elt_data = ["reponse" => $msg];
    
    if ($uneTrace) {
        $idTrace = $uneTrace->getNombrePoints() + 1;
        
        $elt_donnees = ["id" => $idTrace];
    } else {
        $elt_donnees = [];
    }
    
    // construction de la racine
    $elt_racine = ["data" => ["reponse" => $msg, "donnees" => $elt_donnees]];
    
    // retourne le contenu JSON (l'option JSON_PRETTY_PRINT gère les sauts de ligne et l'indentation)
    return json_encode($elt_racine, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}


?>