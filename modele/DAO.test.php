<?php
namespace modele;
// Projet TraceGPS
// fichier : modele/DAO.test.php
// Rôle : test de la classe DAO.class.php
// Dernière mise à jour : 7/7/2021 par dPlanchet
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Test de la classe DAO</title>
	<style type="text/css">body {font-family: Arial, Helvetica, sans-serif; font-size: small;}</style>
</head>
<body>

<?php
    // connexion du serveur web à la base MySQL
    include_once ('DAO.php');
    //include_once ('_DAO.mysql.class.php');
    $dao = new DAO();
    
    // test de la méthode getNiveauConnexion ----------------------------------------------------------
    // modifié par dP le 12/8/2018
    echo "<h3>Test de getNiveauConnexion : </h3>";
    $niveau = $dao->getNiveauConnexion("admin", sha1("mdpadmin"));
    echo "<p>Niveau de ('admin', 'mdpadmin') : " . $niveau . "</br>";
    
    $niveau = $dao->getNiveauConnexion("europa", sha1("mdputilisateur"));
    echo "<p>Niveau de ('europa', 'mdputilisateur') : " . $niveau . "</br>";
    
    $niveau = $dao->getNiveauConnexion("europa", sha1("123456"));
    echo "<p>Niveau de ('europa', '123456') : " . $niveau . "</br>";
    
    $niveau = $dao->getNiveauConnexion("toto", sha1("mdputilisateur"));
    echo "<p>Niveau de ('toto', 'mdputilisateur') : " . $niveau . "</br>";
    
    // test de la méthode existePseudoUtilisateur -----------------------------------------------------
    // modifié par dP le 12/8/2018
    echo "<h3>Test de existePseudoUtilisateur : </h3>";
    if ($dao->existePseudoUtilisateur("admin")) $existe = "oui"; else $existe = "non";
    echo "<p>Existence de l'utilisateur 'admin' : <b>" . $existe . "</b><br>";
    if ($dao->existePseudoUtilisateur("europa")) $existe = "oui"; else $existe = "non";
    echo "Existence de l'utilisateur 'europa' : <b>" . $existe . "</b></br>";
    if ($dao->existePseudoUtilisateur("toto")) $existe = "oui"; else $existe = "non";
    echo "Existence de l'utilisateur 'toto' : <b>" . $existe . "</b></p>";
    
    // test de la méthode existeAdrMailUtilisateur ----------------------------------------------------
    // modifié par dP le 12/8/2021
    echo "<h3>Test de existeAdrMailUtilisateur : </h3>";
    if ($dao->existeAdrMailUtilisateur("admin@gmail.com")) $existe = "oui"; else $existe = "non";
    echo "<p>Existence de l'utilisateur 'admin@gmail.com' : <b>" . $existe . "</b><br>";
    if ($dao->existeAdrMailUtilisateur("delasalle.sio.eleves@gmail.com")) $existe = "oui"; else $existe = "non";
    echo "Existence de l'utilisateur 'delasalle.sio.eleves@gmail.com' : <b>" . $existe . "</b></br>";
    
    // test de la méthode getUnUtilisateur -----------------------------------------------------------
    // modifié par dP le 12/8/2018
    echo "<h3>Test de getUnUtilisateur : </h3>";
    $unUtilisateur = $dao->getUnUtilisateur("admin");
    if ($unUtilisateur) {
        echo "<p>L'utilisateur admin existe : <br>" . $unUtilisateur->toString() . "</p>";
    }
    else {
        echo "<p>L'utilisateur admin n'existe pas !</p>";
    }
    $unUtilisateur = $dao->getUnUtilisateur("europa");
    if ($unUtilisateur) {
        echo "<p>L'utilisateur europa existe : <br>" . $unUtilisateur->toString() . "</p>";
    }
    else {
        echo "<p>L'utilisateur europa n'existe pas !</p>";
    }
    $unUtilisateur = $dao->getUnUtilisateur("admon");
    if ($unUtilisateur) {
        echo "<p>L'utilisateur admon existe : <br>" . $unUtilisateur->toString() . "</p>";
    }
    else {
        echo "<p>L'utilisateur admon n'existe pas !</p>";
    }
    
    // test de la méthode getTousLesUtilisateurs ------------------------------------------------------
    // modifié par dP le 12/8/2018
    echo "<h3>Test de getTousLesUtilisateurs : </h3>";
    $lesUtilisateurs = $dao->getTousLesUtilisateurs();
    $nbReponses = sizeof($lesUtilisateurs);
    echo "<p>Nombre d'utilisateurs : " . $nbReponses . "</p>";
    // affichage des utilisateurs
    foreach ($lesUtilisateurs as $unUtilisateur)
    {	echo ($unUtilisateur->toString());
        echo ('<br>');
    }
    
    // test de la méthode getTLesUtilisateursAutorisant ------------------------------------------------------
    echo "<h3>Test de getLesUtilisateursAutorisant(idUtilisateur) : </h3>";
    $lesUtilisateurs = $dao->getLesUtilisateursAutorisant(4);
    $nbReponses = sizeof($lesUtilisateurs);
    echo "<p>Nombre d'utilisateurs autorisant l'utilisateur 4 à voir leur parcours :" . $nbReponses . "</p>";
    //affichage des utilisateurs
    foreach ($lesUtilisateurs as $unUtilisateur) {
        echo ($unUtilisateur->toString());
        echo ('<br>');
    }
    
    // test de la méthode getLesUtilisateursAutorises -------------------------------------------------
    // modifié par dP le 13/8/2021
    echo "<h3>Test de getLesUtilisateursAutorises(idUtilisateur) : </h3>";
    $lesUtilisateurs = $dao->getLesUtilisateursAutorises(2);
    $nbReponses = sizeof($lesUtilisateurs);
    echo "<p>Nombre d'utilisateurs autorisés par l'utilisateur 2 : " . $nbReponses . "</p>";
    // affichage des utilisateurs
    foreach ($lesUtilisateurs as $unUtilisateur)
    { echo ($unUtilisateur->toString());
    echo ('<br>');
    }
    
    // test de la méthode creerUnUtilisateur ----------------------------------------------------------
    // modifié par dP le 12/8/2018
    echo "<h3>Test de creerUnUtilisateur : </h3>";
    $unUtilisateur = new Utilisateur(0, "toto", "mdputilisateur", "delasalle.sio.xxxx@gmail.com", "5566778899", 1, date('Y-m-d H:i:s', time()), 0, null);
    $ok = $dao->creerUnUtilisateur($unUtilisateur);
    if ($ok)
    {   echo "<p>Utilisateur bien enregistré !</p>";
        echo $unUtilisateur->toString();
    }
    else {
        echo "<p>Echec lors de l'enregistrement de l'utilisateur !</p>";
    }
    
    // test de la méthode modifierMdpUtilisateur ------------------------------------------------------
    // modifié par dP le 12/8/2018
    echo "<h3>Test de modifierMdpUtilisateur : </h3>";
    $unUtilisateur = $dao->getUnUtilisateur("toto");
    if ($unUtilisateur) {
        echo "<p>Ancien mot de passe de l'utilisateur toto : <b>" . $unUtilisateur->getMdpSha1() . "</b><br>";
        $dao->modifierMdpUtilisateur("toto", "mdpadmin");
        $unUtilisateur = $dao->getUnUtilisateur("toto");
        echo "Nouveau mot de passe de l'utilisateur toto : <b>" . $unUtilisateur->getMdpSha1() . "</b><br>";
        
        $niveauDeConnexion = $dao->getNiveauConnexion('toto', sha1('mdputilisateur'));
        echo "Niveau de connexion de ('toto', 'mdputilisateur') : <b>" . $niveauDeConnexion . "</b><br>";
        
        $niveauDeConnexion = $dao->getNiveauConnexion('toto', sha1('mdpadmin'));
        echo "Niveau de connexion de ('toto', 'mdpadmin') : <b>" . $niveauDeConnexion . "</b></p>";
    }
    else {
        echo "<p>L'utilisateur toto n'existe pas !</p>";
    }
    
    // test de la méthode envoyerMdp ------------------------------------------------------------------
    // modifié par dP le 12/8/2021
    echo "<h3>Test de envoyerMdp : </h3>";
    // pour ce test, une adresse mail que vous pouvez consulter
    $unUtilisateur = new Utilisateur(0, "toto", "mdputilisateur", "delasalle.sio.xxxxx@gmail.com", "5566778899", 2,
    date('Y-m-d H:i:s', time()), 0, null);
    $ok = $dao->creerUnUtilisateur($unUtilisateur);
    $dao->modifierMdpUtilisateur("toto", "mdpadmin");
    $ok = $dao->envoyerMdp("toto", "mdpadmin");
    if ($ok) {
        echo "<p>Mail bien envoyé !</p>";
    }
    else {
        echo "<p>Echec lors de l'envoi du mail !</p>";
    }
    // supprimer le compte créé
    $ok = $dao->supprimerUnUtilisateur("toto");
    if ($ok) {
        echo "<p>Utilisateur toto bien supprimé !</p>";
    }
    else {
        echo "<p>Echec lors de la suppression de l'utilisateur toto !</p>";
    }
        
    // test de la méthode autoriseAConsulter ----------------------------------------------------------
    // modifié par dP le 13/8/2021
    echo "<h3>Test de autoriseAConsulter : </h3>";
    if ($dao->autoriseAConsulter(2, 3)) $autorise = "oui"; else $autorise = "non";
    echo "<p>L'utilisateur 2 autorise l'utilisateur 3 : <b>" . $autorise . "</b><br>";
    if ($dao->autoriseAConsulter(3, 2)) $autorise = "oui"; else $autorise = "non";
    echo "<p>L'utilisateur 3 autorise l'utilisateur 2 : <b>" . $autorise . "</b><br>";
    
    // test de la méthode creerUneAutorisation ---------------------------------------------------------
    // modifié par dP le 13/8/2021
    echo "<h3>Test de creerUneAutorisation : </h3>";
    if ($dao->creerUneAutorisation(2, 1)) $ok = "oui"; else $ok = "non";
    echo "<p>La création de l'autorisation de l'utilisateur 2 vers l'utilisateur 1 a réussi : <b>" . $ok . "</b><br>";
    // la même autorisation ne peut pas être enregistrée 2 fois
    if ($dao->creerUneAutorisation(2, 1)) $ok = "oui"; else $ok = "non";
    echo "<p>La création de l'autorisation de l'utilisateur 2 vers l'utilisateur 1 a réussi : <b>" . $ok . "</b><br>";
    
    // test de la méthode supprimerUneAutorisation ----------------------------------------------------
    // modifié par dP le 13/8/2021
    echo "<h3>Test de supprimerUneAutorisation : </h3>";
    // on crée une autorisation
    if ($dao->creerUneAutorisation(2, 1)) $ok = "oui"; else $ok = "non";
    echo "<p>La création de l'autorisation de l'utilisateur 2 vers l'utilisateur 1 a réussi : <b>" . $ok . "</b><br>";
    // puis on la supprime
    if ($dao->supprimerUneAutorisation(2, 1)) $ok = "oui"; else $ok = "non";
    echo "<p>La suppression de l'autorisation de l'utilisateur 2 vers l'utilisateur 1 a réussi : <b>" . $ok . "</b><br>";
    
    // test de la méthode getLesPointsDeTrace ---------------------------------------------------------
    // modifié par dP le 13/8/2021
    echo "<h3>Test de getLesPointsDeTrace : </h3>";
    $lesPoints = $dao->getLesPointsDeTrace(1);
    $nbPoints = sizeof($lesPoints);
    echo "<p>Nombre de points de la trace 1 : " . $nbPoints . "</p>";
    // affichage des points
    foreach ($lesPoints as $unPoint)
    { echo ($unPoint->toString());
    echo ('<br>');
    }
    
    // test de la méthode getUneTrace -----------------------------------------------------------------
    // modifié par dP le 14/8/2021
    echo "<h3>Test de getUneTrace : </h3>";
    $uneTrace = $dao->getUneTrace(2);
    if ($uneTrace) {
        echo "<p>La trace 2 existe : <br>" . $uneTrace->toString() . "</p>";
    }
    else {
        echo "<p>La trace 2 n'existe pas !</p>";
    }
    $uneTrace = $dao->getUneTrace(100);
    if ($uneTrace) {
        echo "<p>La trace 100 existe : <br>" . $uneTrace->toString() . "</p>";
    }
    else {
        echo "<p>La trace 100 n'existe pas !</p>";
    }
    
    // test de la méthode getToutesLesTraces ----------------------------------------------------------
    // modifié par dP le 14/8/2021
    echo "<h3>Test de getToutesLesTraces : </h3>";
    $lesTraces = $dao->getToutesLesTraces();
    $nbReponses = sizeof($lesTraces);
    echo "<p>Nombre de traces : " . $nbReponses . "</p>";
    // affichage des traces
    foreach ($lesTraces as $uneTrace)
    { echo ($uneTrace->toString());
    echo ('<br>');
    }
    
    // test de la méthode getLesTraces($idUtilisateur) ------------------------------------------------
    // modifié par dP le 14/8/2021
    echo "<h3>Test de getLesTraces(idUtilisateur) : </h3>";
    $lesTraces = $dao->getLesTraces(2);
    $nbReponses = sizeof($lesTraces);
    echo "<p>Nombre de traces de l'utilisateur 2 : " . $nbReponses . "</p>";
    // affichage des traces
    foreach ($lesTraces as $uneTrace)
    { echo ($uneTrace->toString());
    echo ('<br>');
    }
    
    // test de la méthode getLesTracesAutorisees($idUtilisateur) --------------------------------------
    // modifié par dP le 14/8/2021
    echo "<h3>Test de getLesTracesAutorisees(idUtilisateur) : </h3>";
    $lesTraces = $dao->getLesTracesAutorisees(2);
    $nbReponses = sizeof($lesTraces);
    echo "<p>Nombre de traces autorisées à l'utilisateur 2 : " . $nbReponses . "</p>";
    // affichage des traces
    foreach ($lesTraces as $uneTrace)
    { echo ($uneTrace->toString());
    echo ('<br>');
    }
    $lesTraces = $dao->getLesTracesAutorisees(3);
    $nbReponses = sizeof($lesTraces);
    echo "<p>Nombre de traces autorisées à l'utilisateur 3 : " . $nbReponses . "</p>";
    // affichage des traces
    foreach ($lesTraces as $uneTrace)
    { echo ($uneTrace->toString());
    echo ('<br>');
    }
    
    // test de la méthode creerUneTrace ----------------------------------------------------------
    // modifié par dP le 14/8/2021
    echo "<h3>Test de creerUneTrace : </h3>";
    $trace1 = new Trace(0, "2017-12-18 14:00:00", "2017-12-18 14:10:00", true, 3);
    $ok = $dao->creerUneTrace($trace1);
    if ($ok) {
        echo "<p>Trace bien enregistrée !</p>";
        echo $trace1->toString();
    }
    else {
        echo "<p>Echec lors de l'enregistrement de la trace !</p>";
    }
    $trace2 = new Trace(0, date('Y-m-d H:i:s', time()), null, false, 3);
    $ok = $dao->creerUneTrace($trace2);
    if ($ok) {
        echo "<p>Trace bien enregistrée !</p>";
        echo $trace2->toString();
    }
    else {
        echo "<p>Echec lors de l'enregistrement de la trace !</p>";
    }
    
    // test des méthodes terminerUneTrace --------------------------------------
    // modifié par dP le 15/8/2021
    echo "<h3>Test de terminerUneTrace : </h3>";
    // on choisit une trace non terminée
    $unIdTrace = 3;
    // on l'affiche
    $laTrace = $dao->getUneTrace($unIdTrace);
    echo "<h4>l'objet laTrace avant l'appel de la méthode terminerUneTrace : </h4>";
    echo ($laTrace->toString());
    echo ('<br>');
    // on la termine
    $dao->terminerUneTrace($unIdTrace);
    // et on l'affiche à nouveau
    $laTrace = $dao->getUneTrace($unIdTrace);
    echo "<h4>l'objet laTrace après l'appel de la méthode terminerUneTrace : </h4>";
    echo ($laTrace->toString());
    echo ('<br>');
    
    // test de la méthode supprimerUneTrace -----------------------------------------------------------
    // modifié par dP le 15/8/2021
    echo "<h3>Test de supprimerUneTrace : </h3>";
    $ok = $dao->supprimerUneTrace(22);
    if ($ok) {
        echo "<p>Trace bien supprimée !</p>";
    }
    else {
        echo "<p>Echec lors de la suppression de la trace !</p>";
    }
    
    // test des méthodes creerUnPointDeTrace et terminerUneTrace --------------------------------------
    // modifié par dP le 15/8/2021
    echo "<h3>Test de terminerUneTrace : </h3>";
    // on choisit une trace non terminée
    $unIdTrace = 3;
    // on l'affiche
    $laTrace = $dao->getUneTrace($unIdTrace);
    echo "<h4>l'objet laTrace avant l'appel de la méthode terminerUneTrace : </h4>";
    echo ($laTrace->toString());
    echo ('<br>');
    // on la termine
    $dao->terminerUneTrace($unIdTrace);
    // et on l'affiche à nouveau
    $laTrace = $dao->getUneTrace($unIdTrace);
    echo "<h4>l'objet laTrace après l'appel de la méthode terminerUneTrace : </h4>";
    echo ($laTrace->toString());
    echo ('<br>');
    
    // test de la méthode creerUnPointDeTrace ---------------------------------------------------------
    // modifié par dP le 13/8/2021
    echo "<h3>Test de creerUnPointDeTrace : </h3>";
    // on affiche d'abord le nombre de points (5) de la trace 1
    $lesPoints = $dao->getLesPointsDeTrace(1);
    $nbPoints = sizeof($lesPoints);
    echo "<p>Nombre de points de la trace 1 : " . $nbPoints . "</p>";
    // on crée un sixième point et on l'ajoute à la trace 1
    $unIdTrace = 1;
    $unID = 6;
    $uneLatitude = 48.20;
    $uneLongitude = -1.55;
    $uneAltitude = 50;
    $uneDateHeure = date('Y-m-d H:i:s', time());
    $unRythmeCardio = 80;
    $unTempsCumule = 0;
    $uneDistanceCumulee = 0;
    $uneVitesse = 15;
    $unPoint = new PointDeTrace($unIdTrace, $unID, $uneLatitude, $uneLongitude, $uneAltitude, $uneDateHeure,
        $unRythmeCardio, $unTempsCumule, $uneDistanceCumulee, $uneVitesse);
    $ok = $dao->creerUnPointDeTrace($unPoint);
    // on affiche à nouveau le nombre de points (6) de la trace 1
    $lesPoints = $dao->getLesPointsDeTrace(1);
    $nbPoints = sizeof($lesPoints);
    echo "<p>Nombre de points de la trace 1 : " . $nbPoints . "</p>";
    echo ('<br>');
    
    // test de la méthode supprimerUnUtilisateur ------------------------------------------------------
    // modifié par dP le 12/8/2018
    echo "<h3>Test de supprimerUnUtilisateur : </h3>";
    $ok = $dao->supprimerUnUtilisateur("toto");
    if ($ok) {
        echo "<p>Utilisateur toto bien supprimé !</p>";
    }
    else {
        echo "<p>Echec lors de la suppression de l'utilisateur toto !</p>";
    }
    $ok = $dao->supprimerUnUtilisateur("toto");
    if ($ok) {
        echo "<p>Utilisateur toto bien supprimé !</p>";
    }
    else {
        echo "<p>Echec lors de la suppression de l'utilisateur toto !</p>";
    }

// ferme la connexion à MySQL :
unset($dao);
?>

</body>
</html>