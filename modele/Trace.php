<?php
// Projet TraceGPS
// fichier : modele/Trace.class.php
// Rôle : la classe Trace représente une trace ou un parcours
// Dernière mise à jour : 9/7/2021 par dP
include_once ('PointDeTrace.php');
class Trace
{
    // ------------------------------------------------------------------------------------------------------
    // ---------------------------------- Attributs privés de la classe -------------------------------------
    // ------------------------------------------------------------------------------------------------------
    
    private $id; // identifiant de la trace
    private $dateHeureDebut; // date et heure de début
    private $dateHeureFin; // date et heure de fin
    private $terminee; // true si la trace est terminée, false sinon
    private $idUtilisateur; // identifiant de l'utilisateur ayant créé la trace
    private $lesPointsDeTrace; // la collection (array) des objets PointDeTrace formant la trace
    
    // ------------------------------------------------------------------------------------------------------
    // ----------------------------------------- Constructeur -----------------------------------------------
    // ------------------------------------------------------------------------------------------------------
    
    public function __construct($unId, $uneDateHeureDebut, $uneDateHeureFin, $terminee, $unIdUtilisateur) {
        $this->id = $unId;
        $this->dateHeureDebut = $uneDateHeureDebut;
        $this->dateHeureFin = $uneDateHeureFin;
        $this->terminee = $terminee;
        $this->idUtilisateur = $unIdUtilisateur;
        $this->lesPointsDeTrace = array();
    }
    
    // ------------------------------------------------------------------------------------------------------
    // ---------------------------------------- Getters et Setters ------------------------------------------
    // ------------------------------------------------------------------------------------------------------
    
    public function getId() {return $this->id;}
    public function setId($unId) {$this->id = $unId;}
    
    public function getDateHeureDebut() {return $this->dateHeureDebut;}
    public function setDateHeureDebut($uneDateHeureDebut) {$this->dateHeureDebut = $uneDateHeureDebut;}
    public function getDateHeureFin() {return $this->dateHeureFin;}
    public function setDateHeureFin($uneDateHeureFin) {$this->dateHeureFin= $uneDateHeureFin;}
    
    public function getTerminee() {return $this->terminee;}
    public function setTerminee($terminee) {$this->terminee = $terminee;}
    
    public function getIdUtilisateur() {return $this->idUtilisateur;}
    public function setIdUtilisateur($unIdUtilisateur) {$this->idUtilisateur = $unIdUtilisateur;}
    public function getLesPointsDeTrace() {return $this->lesPointsDeTrace;}
    public function setLesPointsDeTrace($lesPointsDeTrace) {$this->lesPointsDeTrace = $lesPointsDeTrace;}
    
    public function getNombrePoints() {
        $nbrePoints = sizeof($this->lesPointsDeTrace);
        return $nbrePoints;
    }
    
    public function getCentre(){
        if (sizeof($this->lesPointsDeTrace) == 0) return null;
        
        $premierPoint = $this->lesPointsDeTrace[0];
        $latitudeMini = $premierPoint->getLatitude();
        $latitudeMaxi = $premierPoint->getLatitude();
        $longitudeMini = $premierPoint->getLongitude();
        $longitudeMaxi = $premierPoint->getLongitude();
        for ($i = 0; $i < sizeof($this->lesPointsDeTrace) ; $i++) {
            $prochainPoint = $this->lesPointsDeTrace[$i];
            if ($latitudeMini > $prochainPoint->getLatitude()) {
                $latitudeMini = $prochainPoint->getLatitude();
            }
            if ($latitudeMaxi < $prochainPoint->getLatitude()) {
                $latitudeMaxi = $prochainPoint->getLatitude();
            }
            if ($longitudeMini > $prochainPoint->getLongitude()) {
                $longitudeMini = $prochainPoint->getLongitude();
            }
            if ($longitudeMaxi < $prochainPoint->getLongitude()) {
                $longitudeMaxi = $prochainPoint->getLongitude();
            }
        }
        $latitudeMoyenne = ($latitudeMini + $latitudeMaxi) / 2;
        $longitudeMoyenne = ($longitudeMini + $longitudeMaxi) / 2;
        
        return new Point($latitudeMoyenne, $longitudeMoyenne, 0);
    }
    
    public function getDenivele(){
        if (sizeof($this->lesPointsDeTrace) == 0) return 0;
        
        $premierPoint = $this->lesPointsDeTrace[0];
        $altitudeMini = $premierPoint->getAltitude();
        $altitudeMaxi = $premierPoint->getAltitude();
        for ($i = 0; $i < sizeof($this->lesPointsDeTrace) ; $i++) {
            $prochainPoint = $this->lesPointsDeTrace[$i];
            if ($altitudeMini > $prochainPoint->getAltitude()){
                $altitudeMini = $prochainPoint->getAltitude();
            }
            if ($altitudeMaxi < $prochainPoint->getAltitude()){
                $altitudeMaxi = $prochainPoint->getAltitude();
            }
        }
        $ecartAltitude = $altitudeMaxi - $altitudeMini;
        return intval($ecartAltitude);
    }
    
    public function getDureeEnSecondes(){
        if (sizeof($this->lesPointsDeTrace) == 0) return 0;
        
        $heureDebut = new DateTime($this->lesPointsDeTrace[0]->getDateHeure());
        $heureFin = new DateTime($this->lesPointsDeTrace[sizeof($this->lesPointsDeTrace) - 1]->getDateHeure());
        
        $diff = $heureFin->diff($heureDebut);
        
        return $diff->s + ($diff->i * 60) + ($diff->h * 3600);
    }
    
    
    public function getDureeTotale() {
        $tempsCumuleEnSeconde = $this->getDureeEnSecondes();
        
        $heures = floor($tempsCumuleEnSeconde / 3600);
        $minutes = floor(($tempsCumuleEnSeconde % 3600) / 60);
        $secondes = $tempsCumuleEnSeconde % 60;
        
        return sprintf("%02d:%02d:%02d", $heures, $minutes, $secondes);
    }
    
    public function getDistanceTotale(){
        if (sizeof($this->lesPointsDeTrace) == 0) return 0;
        
        return $this->lesPointsDeTrace[sizeof($this->lesPointsDeTrace) - 1]->getDistanceCumulee();
    }
    
    public function getDenivelePositif() {
        $delivele = 0;
        $nbPoints = sizeof($this->lesPointsDeTrace);
        
        for ($i = 0; $i < $nbPoints - 1; $i++) {
            $altitude1 = $this->lesPointsDeTrace[$i]->getAltitude();
            
            // Vérification que l'indice $i+1 est valide
            if ($i + 1 < $nbPoints) {
                $altitude2 = $this->lesPointsDeTrace[$i+1]->getAltitude();
                
                if ($altitude1 < $altitude2) {
                    $delivele += $altitude2 - $altitude1;
                }
            }
        }
        
        return $delivele;
    }
    
    
    public function getDeniveleNegatif() {
        $delivele = 0;
        $nbPoints = sizeof($this->lesPointsDeTrace);
        
        for ($i = 0; $i < $nbPoints - 1; $i++) {
            $altitude1 = $this->lesPointsDeTrace[$i]->getAltitude();
            
            // Vérification que l'indice $i+1 est valide
            if ($i + 1 < $nbPoints) {
                $altitude2 = $this->lesPointsDeTrace[$i+1]->getAltitude();
                
                if ($altitude1 > $altitude2) {
                    $delivele += $altitude1 - $altitude2;
                }
            }
        }
        
        return $delivele;
    }
    
    
    public function getVitesseMoyenne() {
        if (sizeof($this->lesPointsDeTrace) == 0) return 0;
        
        $distanceTotal = $this->getDistanceTotale();
        $dureeEnSecondes = $this->getDureeEnSecondes();
        
        return  $distanceTotal / ($dureeEnSecondes / 3600);
    }
    
    public function ajouterPoint(PointDeTrace $point) {
        
        $temps = 0;
        $distance = 0;
        $vitesse = 0;
        
        if (sizeof($this->lesPointsDeTrace) != 0){
            
            $dernierPoint = $this->lesPointsDeTrace[count($this->lesPointsDeTrace) - 1];
            $distanceEntrePoints = Point::getDistance($point,$dernierPoint);
            $tempsCumuleDernierPoint = strtotime($point->getDateHeure()) - strtotime($dernierPoint->getDateHeure());
            
            $temps = $dernierPoint->getTempsCumule() + $tempsCumuleDernierPoint;
            $distance = (double) $dernierPoint->getDistanceCumulee() + $distanceEntrePoints;
            $vitesse = 0;
            if($this->getDureeEnSecondes() > 0)
                $vitesse = $distanceEntrePoints / ($this->getDureeEnSecondes() / 3600);
        }
        
        $point->setTempsCumule($temps);
        $point->setDistanceCumulee($distance);
        $point->setVitesse($vitesse);
        
        $this->lesPointsDeTrace[] = $point;
    }
    
    
    public function viderListePoints() {
        $this->lesPointsDeTrace = array();
    }
    
    // Fournit une chaine contenant toutes les données de l'objet
    public function toString() {
        $msg = "Id : " . $this->getId() . "<br>";
        $msg .= "Utilisateur : " . $this->getIdUtilisateur() . "<br>";
        if ($this->getDateHeureDebut() != null) {
            $msg .= "Heure de début : " . $this->getDateHeureDebut() . "<br>";
        }
        if ($this->getTerminee()) {
            $msg .= "Terminée : Oui <br>";
        }
        else {
            $msg .= "Terminée : Non <br>";
        }
        $msg .= "Nombre de points : " . $this->getNombrePoints() . "<br>";
        if ($this->getNombrePoints() > 0) {
            if ($this->getDateHeureFin() != null) {
                $msg .= "Heure de fin : " . $this->getDateHeureFin() . "<br>";
            }
            $msg .= "Durée en secondes : " . $this->getDureeEnSecondes() . "<br>";
            $msg .= "Durée totale : " . $this->getDureeTotale() . "<br>";
            $msg .= "Distance totale en Km : " . $this->getDistanceTotale() . "<br>";
            $msg .= "Dénivelé en m : " . $this->getDenivele() . "<br>";
            $msg .= "Dénivelé positif en m : " . $this->getDenivelePositif() . "<br>";
            $msg .= "Dénivelé négatif en m : " . $this->getDeniveleNegatif() . "<br>";
            $msg .= "Vitesse moyenne en Km/h : " . $this->getVitesseMoyenne() . "<br>";
            $msg .= "Centre du parcours : " . "<br>";
            $msg .= " - Latitude : " . $this->getCentre()->getLatitude() . "<br>";
            $msg .= " - Longitude : " . $this->getCentre()->getLongitude() . "<br>";
            $msg .= " - Altitude : " . $this->getCentre()->getAltitude() . "<br>";
        }
        return $msg;
    }
    
} // fin de la classe Trace
// ATTENTION : on ne met pas de balise de fin de script pour ne pas prendre le risque
// d'enregistrer d'espaces après la balise de fin de script !!!!!!!!!!!!