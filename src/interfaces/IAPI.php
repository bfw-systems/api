<?php
/**
 * Interfaces en rapport avec le système d'API
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 1.0
 */

namespace BFWApiInterface;

/**
 * Interface de la classe API
 * @package bfw-api
 */
interface IAPI
{
    /**
     * Method appelé par défaut si aucune méthode n'est demandé
     * 
     * @param string $endRequest : La fin de requête http
     */
    public function index($endRequest);
}