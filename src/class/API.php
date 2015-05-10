<?php
/**
 * Classes gérant la base de l'api
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 1.0
 */

namespace BFWApi;

/**
 * Permet de gérer l'api
 * @package bfw-api
 */
class API implements \BFWApiInterface\IAPI
{
    /**
     * @var $_kernel L'instance du Kernel
     */
    protected $_kernel;
    
    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->_kernel = getKernel();
    }
    
    /**
     * Method appelé par défaut si aucune méthode n'est demandé
     * 
     * @param string $endRequest : La fin de requête http
     */
    public function index($endRequest)
    {
        
    }
}