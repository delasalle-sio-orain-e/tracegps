<?php
namespace api\services;


use DOMDocument;
use modele\Outils;

// connexion du serveur web à la base MySQL
use modele\DAO;
$dao = new DAO();

// Récupération des données transmises
$pseudo = ( empty($this->request['pseudo'])) ? "" : $this->request['pseudo'];
$mdpSha1 = ( empty($this->request['mdp'])) ? "" : $this->request['mdp'];
$idTrace = ( empty($this->request['idTrace'])) ? "" : $this->request['idTrace'];
$lang = ( empty($this->request['lang'])) ? "" : $this->request['lang'];

// "xml" par défaut si le paramètre lang est absent ou incorrect
if ($lang != "json") $lang = "xml";

$latrace = null;

// La méthode HTTP utilisée doit être GET
if ($this->getMethodeRequete() != "GET")
{	$msg = "Erreur : méthode HTTP incorrecte.";
    $code_reponse = 406;
}
else {
    
    // Les paramètres doivent être présents
    if ( $pseudo == "" || $mdpSha1 == "" || $idTrace=="" )
    {
        $msg = "Erreur : données incomplètes.";
        $code_reponse = 400;
    }
    else
    {
        if ( $dao->getNiveauConnexion($pseudo, $mdpSha1) == 0 ) {
        $msg = "Erreur : authentification incorrecte.";
        $code_reponse = 401;
        }
        else 
        {
            
            $latrace = $dao->getUneTrace($idTrace);
            
            if ( $latrace == null ) {
                $msg = "Erreur : parcours inexistant.";
                $code_reponse = 401;
            }
            else{                
                $idUtilisateur =  $dao->getUnUtilisateur($pseudo)->getId();
                $idUtilisateurTrace =  $dao->getUneTrace($idTrace)->getIdUtilisateur();
                                
                $traceAutorise =  $dao->getLesTracesAutorisees( $idUtilisateur);
                $nbTraceAutoriser = sizeof($traceAutorise);
                
                $autoriser = false;
                if($nbTraceAutoriser >= 0){
                    while($nbTraceAutoriser > 0){
                       if($traceAutorise[$nbTraceAutoriser-1]->getIdUtilisateur() == $idUtilisateurTrace ){
                            $autoriser = true;
                            $nbTraceAutoriser = 0;
                        }
                        $nbTraceAutoriser = $nbTraceAutoriser -1;
                    }
                }
                
                if ($idUtilisateur != $idUtilisateurTrace && $autoriser == false  ) {
                    $msg = "Erreur : vous n'êtes pas autorisé par le propriétaire du parcours.";
                    $code_reponse = 401;
                }
                
              
                else{
                    $msg = "Données de la trace demandée.";
                    $code_reponse = 200;
                    $lesPoint = $dao->getLesPointsDeTrace($idTrace);
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
    $donnees = creerFluxXML($msg, $latrace,$lesPoint);
}
else {
    $content_type = "application/json; charset=utf-8";      // indique le format Json pour la réponse
    $donnees = creerFluxJSON($msg, $latrace,$lesPoint);
}

// envoi de la réponse HTTP
$this->envoyerReponse($code_reponse, $content_type, $donnees);

// fin du programme (pour ne pas enchainer sur les 2 fonctions qui suivent)
exit;
// ================================================================================================

// création du flux XML en sortie
function creerFluxXML($msg, $laTrace,$lesPoint)
{
    /* Exemple de code XML
     <?xml version="1.0" encoding="UTF-8"?>
     <!--Service web GetLesParcoursDunUtilisateur - BTS SIO - Lycée De La Salle - Rennes-->
     <data>
     <reponse>2 trace(s) pour l'utilisateur callisto</reponse>
     <donnees>
     <lesTraces>
     <trace>
     <id>2</id>
     <dateHeureDebut>2018-01-19 13:08:48</dateHeureDebut>
     <terminee>1</terminee>
     <dateHeureFin>2018-01-19 13:11:48</dateHeureFin>
     <distance>1.2</distance>
     <idUtilisateur>2</idUtilisateur>
     </trace>
     <trace>
     <id>1</id>
     <dateHeureDebut>2018-01-19 13:08:48</dateHeureDebut>
     <terminee>0</terminee>
     <distance>0.5</distance>
     <idUtilisateur>2</idUtilisateur>
     </trace>
     </lesTraces>
     </donnees>
     </data>
     */
    
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
    
    // traitement des utilisateurs
    if ($laTrace) {
        // place l'élément 'donnees' dans l'élément 'data'
        $elt_donnees = $doc->createElement('donnees');
        $elt_data->appendChild($elt_donnees);
        
        
            // crée un élément vide 'trace'
            $elt_trace = $doc->createElement('trace');
            // place l'élément 'utilisateur' dans l'élément 'lesUtilisateurs'
            $elt_donnees->appendChild($elt_trace);
            
            // crée les éléments enfants de l'élément 'utilisateur'
            $elt_id = $doc->createElement('id', $laTrace->getId());
            $elt_trace->appendChild($elt_id);
            
            $elt_dateHeureDebut = $doc->createElement('dateHeureDebut', $laTrace->getDateHeureDebut());
            $elt_trace->appendChild($elt_dateHeureDebut);
            
            $elt_terminee = $doc->createElement('terminee', $laTrace->getTerminee());
            $elt_trace->appendChild($elt_terminee);
            
            if ($laTrace->getDateHeureFin() != null)
            {   $elt_dateHeureFin = $doc->createElement('dateHeureFin', $laTrace->getDateHeureFin());
            $elt_trace->appendChild($elt_dateHeureFin);
            }
            
            
            $elt_distance = $doc->createElement('distance', round($laTrace->getDistanceTotale(),1));
            $elt_trace->appendChild($elt_distance);
            
            $elt_idUtilisateur = $doc->createElement('idUtilisateur', $laTrace->getIdUtilisateur());
            $elt_trace->appendChild($elt_idUtilisateur);
            
            
            $elt_lesPoints = $doc->createElement('lesPoints');
            $elt_donnees->appendChild($elt_lesPoints);
            
            foreach($lesPoint as $unPoint){
                
                $elt_point = $doc->createElement('point');
                $elt_lesPoints->appendChild($elt_point);
                
                $elt_id = $doc->createElement('id', $unPoint->getId());
                $elt_point->appendChild($elt_id);
                
                $elt_latitude = $doc->createElement('latitude', $unPoint->getLatitude());
                $elt_point->appendChild($elt_latitude );
                
                $elt_longitude = $doc->createElement('longitude', $unPoint->getLongitude());
                $elt_point->appendChild($elt_longitude );
                
                $elt_altitude = $doc->createElement('altitude', $unPoint->getaltitude());
                $elt_point->appendChild($elt_altitude);
                
                $elt_DateHeure = $doc->createElement('dateHeure', $unPoint->getDateHeure());
                $elt_point->appendChild($elt_DateHeure);
                
                $elt_RythmeCardio = $doc->createElement('RythmeCardio', $unPoint->getRythmeCardio());
                $elt_point->appendChild($elt_RythmeCardio);
            }
            
            
    }
    // Mise en forme finale
    $doc->formatOutput = true;
    
    // renvoie le contenu XML
    return $doc->saveXML();
}
    
// ================================================================================================

// création du flux JSON en sortie
function creerFluxJSON($msg, $latrace,$lesPoint)
{
    if (!$latrace) {
        // construction de l'élément "data"
        $elt_data = ["reponse" => $msg];
    }
    else {
        // construction d'un tableau contenant les utilisateurs
        $lesObjetsDuTableau = array();
        
            $unObjetTrace = array();
            $unObjetTrace["id"] = $latrace->getId();
            $unObjetTrace["dateHeureDebut"] = $latrace->getDateHeureDebut();
            $unObjetTrace["terminee"] = $latrace->getTerminee();
            if ($latrace->getTerminee() == 1)
            {   $unObjetTrace["dateHeureFin"] = $latrace->getDateHeureFin();
            }
            $unObjetTrace["distance"] = round($latrace->getDistanceTotale(),1);
            $unObjetTrace["idUtilisateur"] = $latrace->getIdUtilisateur();
            
            $lesObjetsDuTableau[] = $unObjetTrace;
            
            $lesObjetsDuTableauPoint = array();
            $unObjetPoint = array();
            foreach($lesPoint as $unPoint){
                
                $elt_point = [
                    "id" => $unPoint->getId(),
                    "latitude" => $unPoint->getLatitude(), 
                    "longitude" => $unPoint->getLongitude(),
                    "altitude" => $unPoint->getaltitude(),
                    "dateHeure" => $unPoint->getDateHeure(),
                    "RythmeCardio" => $unPoint->getRythmeCardio()];
                $lesObjetsDuTableauPoint[] =  $elt_point;
            }
           
        // construction de l'élément "lesTraces"
        $elt_trace =  $lesObjetsDuTableau;
        
        $elt_Point = $lesObjetsDuTableauPoint;
        // construction de l'élément "data"
        $elt_data = ["reponse" => $msg, "donnees" => ["trace" => $elt_trace, "lesPoints" => $elt_Point]];
    }
    
    // construction de la racine
    $elt_racine = ["data" => $elt_data];
    
    // retourne le contenu JSON (l'option JSON_PRETTY_PRINT gère les sauts de ligne et l'indentation)
    return json_encode($elt_racine, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

// ================================================================================================
?>