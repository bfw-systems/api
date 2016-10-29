<?php
/**
 * Fichier de configuration du module bfw-api
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @package bfw-api
 * @version 1.0
 */

//*** API configs ***

/**
 * L'url sur laquelle l'api doit réagir
 * Exemple : '/api/'
 * 
 * @todo : Gestion des sous-domaines
 */
$apiUrl = '';

/**
 * Le fichier contenant la class qui doit réagir à l'api
 * Le chemin est relatif à partir du dossier controllers
 * 
 * Exemple : 'api.php' pour le fichier /controller/api.php
 */
$apiFile = '';

/**
 * Le nom de la classe (avec le namespace) où est l'api
 * 
 * Exemple : \API
 * Exemple : \myProject\API
 */
$apiClass = '';

//*** API configs *** 
