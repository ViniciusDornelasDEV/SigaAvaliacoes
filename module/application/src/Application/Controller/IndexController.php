<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Authentication\AuthenticationService;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;

class IndexController extends BaseController
{
    public function indexAction()
    {   
    	/*$serviceCampo = $this->getServiceLocator()->get('Campo');
    	$empresas = $this->getServiceLocator()->get('Empresa')->fetchAll()->toArray();
    	$sql = '';
        for ($i=344; $i < 359; $i++) { 
        	$campo = $serviceCampo->getRecord($i);
        	foreach ($empresas as $empresa) {
        		$sql .= 'INSERT INTO tb_campo_empresa (aba, campo, empresa, categoria_questao, aparecer, obrigatorio, label) VALUES
					(3, '.$i.', '.$empresa['id'].', 65, "S", "S", "'.$campo['label'].'");<br>';
        	}
        }*/
    	
    	return new ViewModel(array());
    }
}
