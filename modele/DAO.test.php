<?php
namespace modele;
// Projet TraceGPS
// fichier : modele/DAO.test.php
// RÃ´le : test de la classe DAO.class.php
// DerniÃ¨re mise Ã  jour : 7/7/2021 par dPlanchet
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
// connexion du serveur web Ã  la base MySQL
include_once ('DAO.php');
//include_once ('_DAO.mysql.class.php');
$dao = new DAO();

/*
// test de la mÃ©thode getNiveauConnexion ----------------------------------------------------------
// modifiÃ© par dP le 12/8/2018
echo "<h3>Test de getNiveauConnexion : </h3>";
$niveau = $dao->getNiveauConnexion("admin", sha1("mdpadmin"));
echo "<p>Niveau de ('admin', 'mdpadmin') : " . $niveau . "</br>";

$niveau = $dao->getNiveauConnexion("europa", sha1("mdputilisateur"));
echo "<p>Niveau de ('europa', 'mdputilisateur') : " . $niveau . "</br>";

$niveau = $dao->getNiveauConnexion("europa", sha1("123456"));
echo "<p>Niveau de ('europa', '123456') : " . $niveau . "</br>";

$niveau = $dao->getNiveauConnexion("toto", sha1("mdputilisateur"));
echo "<p>Niveau de ('toto', 'mdputilisateur') : " . $niveau . "</br>";
*/


/*
// test de la mÃ©thode existePseudoUtilisateur -----------------------------------------------------
// modifiÃ© par dP le 12/8/2018
echo "<h3>Test de existePseudoUtilisateur : </h3>";
if ($dao->existePseudoUtilisateur("admin")) $existe = "oui"; else $existe = "non";
echo "<p>Existence de l'utilisateur 'admin' : <b>" . $existe . "</b><br>";
if ($dao->existePseudoUtilisateur("europa")) $existe = "oui"; else $existe = "non";
echo "Existence de l'utilisateur 'europa' : <b>" . $existe . "</b></br>";
if ($dao->existePseudoUtilisateur("toto")) $existe = "oui"; else $existe = "non";
echo "Existence de l'utilisateur 'toto' : <b>" . $existe . "</b></p>";
*/


/*
// test de la mÃ©thode getUnUtilisateur -----------------------------------------------------------
// modifiÃ© par dP le 12/8/2018
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
*/  


/*
// test de la mÃ©thode getTousLesUtilisateurs ------------------------------------------------------
// modifiÃ© par dP le 12/8/2018
echo "<h3>Test de getTousLesUtilisateurs : </h3>";
$lesUtilisateurs = $dao->getTousLesUtilisateurs();
$nbReponses = sizeof($lesUtilisateurs);
echo "<p>Nombre d'utilisateurs : " . $nbReponses . "</p>";
// affichage des utilisateurs
foreach ($lesUtilisateurs as $unUtilisateur)
{	echo ($unUtilisateur->toString());
    echo ('<br>');
}
*/


/*
// test de la mÃ©thode creerUnUtilisateur ----------------------------------------------------------
// modifiÃ© par dP le 12/8/2018
echo "<h3>Test de creerUnUtilisateur : </h3>";
$unUtilisateur = new Utilisateur(0, "toto", "mdputilisateur", "delasalle.sio.xxxx@gmail.com", "5566778899", 1, date('Y-m-d H:i:s', time()), 0, null);
$ok = $dao->creerUnUtilisateur($unUtilisateur);
if ($ok)
{   echo "<p>Utilisateur bien enregistrÃ© !</p>";
    echo $unUtilisateur->toString();
}
else {
    echo "<p>Echec lors de l'enregistrement de l'utilisateur !</p>";
}
*/


/*
// test de la mÃ©thode modifierMdpUtilisateur ------------------------------------------------------
// modifiÃ© par dP le 12/8/2018
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
*/


/*
// test de la mÃ©thode supprimerUnUtilisateur ------------------------------------------------------
// modifiÃ© par dP le 12/8/2018
echo "<h3>Test de supprimerUnUtilisateur : </h3>";
$ok = $dao->supprimerUnUtilisateur("toto");
if ($ok) {
    echo "<p>Utilisateur toto bien supprimÃ© !</p>";
}
else {
    echo "<p>Echec lors de la suppression de l'utilisateur toto !</p>";
}
$ok = $dao->supprimerUnUtilisateur("toto");
if ($ok) {
    echo "<p>Utilisateur toto bien supprimÃ© !</p>";
}
else {
    echo "<p>Echec lors de la suppression de l'utilisateur toto !</p>";
}
*/


/*
// test de la mÃ©thode envoyerMdp ------------------------------------------------------------------
// modifiÃ© par dP le 12/8/2018
echo "<h3>Test de envoyerMdp : </h3>";
// pour ce test, une adresse mail que vous pouvez consulter
$unUtilisateur = new Utilisateur(0, "toto", "mdputilisateur", "delasalle.sio.xxxxx@gmail.com", "5566778899", 2, date('Y-m-d H:i:s', time()), 0, null);
$ok = $dao->creerUnUtilisateur($unUtilisateur);
$dao->modifierMdpUtilisateur("toto", "mdpadmin");
$ok = $dao->envoyerMdp("toto", "mdpadmin");
if ($ok) {
    echo "<p>Mail bien envoyÃ© !</p>";
}
else {
    echo "<p>Echec lors de l'envoi du mail !</p>";
}
// supprimer le compte crÃ©Ã©
$ok = $dao->supprimerUnUtilisateur("toto");
if ($ok) {
    echo "<p>Utilisateur toto bien supprimÃ© !</p>";
}
else {
    echo "<p>Echec lors de la suppression de l'utilisateur toto !</p>";
}
*/


// Le code des tests restant Ã  dÃ©velopper va Ãªtre rÃ©parti entre les membres de l'Ã©quipe de dÃ©veloppement.
// Afin de limiter les conflits avec GitHub, il est dÃ©cidÃ© d'attribuer un fichier de test Ã  chaque dÃ©veloppeur.
// DÃ©veloppeur 1 : fichier DAO.test1.php
// DÃ©veloppeur 2 : fichier DAO.test2.php
// DÃ©veloppeur 3 : fichier DAO.test3.php
// DÃ©veloppeur 4 : fichier DAO.test4.php

// Quelques conseils pour le travail collaboratif :
// avant d'attaquer un cycle de dÃ©veloppement (dÃ©but de sÃ©ance, nouvelle mÃ©thode, ...), faites un Pull pour rÃ©cupÃ©rer
// la derniÃ¨re version du fichier.
// AprÃ¨s avoir testÃ© et validÃ© une mÃ©thode, faites un commit et un push pour transmettre cette version aux autres dÃ©veloppeurs.
// test de la mÃ©thode autoriseAConsulter ----------------------------------------------------------
// modifiÃ© par dP le 13/8/2021
echo "<h3>Test de autoriseAConsulter : </h3>";
if ($dao->autoriseAConsulter(2, 3)) $autorise = "oui"; else $autorise = "non";
echo "<p>L'utilisateur 2 autorise l'utilisateur 3 : <b>" . $autorise . "</b><br>";
if ($dao->autoriseAConsulter(3, 2)) $autorise = "oui"; else $autorise = "non";
echo "<p>L'utilisateur 3 autorise l'utilisateur 2 : <b>" . $autorise . "</b><br>";


// test de la mÃ©thode getLesPointsDeTrace ---------------------------------------------------------
// modifiÃ© par dP le 13/8/2021
echo "<h3>Test de getLesPointsDeTrace : </h3>";
$lesPoints = $dao->getLesPointsDeTrace(1);
$nbPoints = sizeof($lesPoints);
echo "<p>Nombre de points de la trace 1 : " . $nbPoints . "</p>";
// affichage des points
foreach ($lesPoints as $unPoint)
{ echo ($unPoint->toString());
echo ('<br>');
}

// test de la mÃ©thode getToutesLesTraces ----------------------------------------------------------
// modifiÃ© par dP le 14/8/2021
echo "<h3>Test de getToutesLesTraces : </h3>";
$lesTraces = $dao->getToutesLesTraces();
$nbReponses = sizeof($lesTraces);
echo "<p>Nombre de traces : " . $nbReponses . "</p>";
// affichage des traces
foreach ($lesTraces as $uneTrace)
{ echo ($uneTrace->toString());
echo ('<br>');
}

// test de la mÃ©thode supprimerUneTrace -----------------------------------------------------------
// modifiÃ© par dP le 15/8/2021
echo "<h3>Test de supprimerUneTrace : </h3>";
$ok = $dao->supprimerUneTrace(22);
if ($ok) {
    echo "<p>Trace bien supprimÃ©e !</p>";
}
else {
    echo "<p>Echec lors de la suppression de la trace !</p>";
}



//test de la méthode adrexisteMail

echo "<h3>Test de existeAdrMailUtilisateur : </h3>";
if ($dao->existeAdrMailUtilisateur("admin@gmail.com")) $existe = "oui"; else $existe = "non";
echo "<p>Existence de l'utilisateur 'admin@gmail.com' : <b>" . $existe . "</b><br>";
if ($dao->existeAdrMailUtilisateur("delasalle.sio.eleves@gmail.com")) $existe = "oui"; else $existe = "non";
echo "Existence de l'utilisateur 'delasalle.sio.eleves@gmail.com' : <b>" . $existe . "</b></br>";



// test de la méthode creerUneAutorisation ---------------------------------------------------------
// modifié par dP le 13/8/2021
echo "<h3>Test de creerUneAutorisation : </h3>";
if ($dao->creerUneAutorisation(2, 1)) $ok = "oui"; else $ok = "non";
echo "<p>La création de l'autorisation de l'utilisateur 2 vers l'utilisateur 1 a réussi : <b>" . $ok . "</b><br>";
// la même autorisation ne peut pas être enregistrée 2 fois
if ($dao->creerUneAutorisation(2, 1)) $ok = "oui"; else $ok = "non";
echo "<p>La création de l'autorisation de l'utilisateur 2 vers l'utilisateur 1 a réussi : <b>" . $ok . "</b><br>";



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



// ferme la connexion Ã  MySQL :
unset($dao);
?>

</body>
</html>