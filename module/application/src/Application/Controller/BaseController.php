<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

abstract class BaseController extends AbstractActionController {
    protected $sessao = 'sessao';
    public function setarFiltro($form){
        $this->sessao = new Container();
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            
            if(isset($dados->limpar)){
                unset($this->sessao->parametros);
                $dados = false;
            }else{
                $this->sessao->parametros = $dados;
            }
        }

        if(isset($this->sessao->parametros)) {
            $form->setData($this->sessao->parametros);
        }

        return $form;
    }
    /**
     * Renders a HTML page.
     * 
     * @access public
     * @param array $data (default: array())
     * @return void
     */
    public function render(array $data = array()) {

        $view = new ViewModel($data);


        $this->layout()->identity = $this->getIdentity();

        $this->layout()->sessionIsValid = $this->sessionIsValid();

        $this->layout()->route = $this->getServiceLocator()
                ->get('router')
                ->match($this->getServiceLocator()->get('request'))
                ->getMatchedRouteName();

        if (isset($data['title']) && $data['title'])
            $this->layout()->title = $data['title'];

        if (isset($data['backLink']))
            $this->layout()->backLink = $data['backLink'];

        if (isset($data['actionLink']))
            $this->layout()->actionLink = $data['actionLink'];


        if (isset($data['template'])) {
            if ($data['template'] === false) {
                $view->setTerminal(true);
            } else {
                $view->setTemplate($data['template']);
            }
        }

        if (isset($data['ajax']) && $data['ajax'] === true) {
            $this->layout('layout/ajax');
        }

        if (isset($data['layout']) && $data['layout']) {
            $this->layout($data['layout']);
        }

        return $view;
    }

    /**
     * Will get a project and return it's variables to the view.
     * 
     * @access public
     * @param array $project (default: object())
     * @return void
     */
    public function renderProject($project) {
        
        $res = $this->getProjectFunding($project);        
        return $this->render($res);
    }
    
    protected function getProjectFunding($project) {
        
        $services = $this->getServiceLocator();
        $reward = $services->get('ProjectReward');
        $update = $services->get('ProjectUpdate');
        $payment = $services->get('Payment');

        $payments = $payment->getProjectSupporters($project);
        $funding = $payment->getTotalFunding($project);
        
        $updates = $update->getRecordsFromArray(array('project' => $project->id));
                
        $rewards = $reward->getRecordsFromArray(array('project' => $project->id));
        
        return array(
                    'project' => $project,
                    'updates' => $updates,
                    'rewards' => $rewards,
                    'backers' => ($payments) ? $payments->count() : false,
                    'funding' => $funding
        );
        
    }
        
    
    /**
     * Will render a CSV File
     * 
     * The first item of the results array must be the headers
     * 
     * @access 	public
     * @param 	mixed $results 
     * @return 	void
     */

    public function renderAsCSV($data, $filename = 'report.csv') {
        
        if($data) {
        
            //sets up the first item as the titles
            $keys = array ();        
            foreach($data[0] as $k=>$v) {
                $keys[] = $k;
            }  

            $results = array($keys);

            //adds the items to the array
            foreach($data as $item) {
                $results [] = $item;
            }
        
        } else {
            $results = array();
        }

        
        $view = new ViewModel();
        $view->setTemplate('download-csv')
                ->setVariable('results', $results)
                ->setTerminal(true);

        $output = $this->getServiceLocator()
                ->get('viewrenderer')
                ->render($view);

        $response = $this->getResponse();

        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Type', 'text/csv')
                ->addHeaderLine(
                        'Content-Disposition', sprintf("attachment; filename=\"%s\"", $filename)
                )
                ->addHeaderLine('Accept-Ranges', 'bytes')
                ->addHeaderLine('Content-Length', strlen($output));

        $response->setContent($output);

        return $response;
    }
    
    /**
     * Returns a project the user has right to edit
     * 
     * @access public
     * @param array $id (int)
     * @return void
     */
    public function getUserProject($id) {
        $services = $this->getServiceLocator();

        $projectManager = $services->get('Project');

        $project = $projectManager->getEntireProjectForUser(array('project.id'=> $id));
        
        if (!$project) {
            return $this->getResponse()->setStatusCode(404);
        } else {
            return $project;
        }        
        
    }

    /**
     * Get user information from session.
     * 
     * @access 	protected
     * @param 	mixed $property (default: null)
     * @return 	void
     */
    protected function getIdentity($property = null) {
        $storage = $this->getServiceLocator()->get('session');

        if (!$storage) {
            return false;
        }

        $data = $storage->read();

        if ($property && isset($data[$property])) {
            return $data[$property];
        }

        return $data;
    }

    /**
     * Returns TRUE if session is still valid (i.e. it hasn't expired).
     * 
     * @access public
     * @return void
     */
    public function sessionIsValid() {
        return time() <= $this->getIdentity('expiry');
    }

    /**
     * redirectToPrevious function.
     * 
     * @access protected
     * @return void
     */
    protected function redirectToPrevious() {
        $this->redirect()->toUrl($this->getPreviousUrl());
    }

    /**
     * getPreviousUrl function.
     * 
     * @access protected
     * @return void
     */
    protected function getPreviousUrl() {
        if (@$this->getRequest()->getHeader('Referer')) {
            return $this->getRequest()->getHeader('Referer')->getUri();
        } else {
            return BASE_URL;
        }
    }

    protected function getNewFileName($name, $prefix = false) {

        if (!$prefix)
            $prefix = time();

        return $prefix . '_' . preg_replace('/\s+/', '', basename($name));
    }

    protected function saveToS3($bucket, $src, $filename, $type) {

        $aws = $this->getServiceLocator()->get('aws');
        
        $s3 = $aws->get('s3');

        $result = $s3->putObject(array(
            'Bucket' => $bucket,
            'SourceFile' => $src,
            'Key' => $filename,
            'ContentType' => $type,
            'ACL'        => 'public-read'
        ));

        return $result;
    }

    protected function deleteFromS3($filename, $bucket) {

        $aws = $this->getServiceLocator()->get('aws');

        $s3 = $aws->get('s3');

        $result = $s3->deleteObject(array(
            'Bucket' => $bucket,
            'Key' => $filename
        ));

        return $result;
    }

     protected function processImages($folder, $data, $services) {
        //rename image
        $newName = $this->getNewFileName($data['imagem']['name']);
        
        //$newImagePath = $folder . '/' . $newName;
        //$file = $folder . '/' . $data['imagem']['name'];

        //$res - rename($file, $newImagePath);

        //upload original image to s3

        //resize the image and create thumbnails 

        $imagine = $services->get('ImageService');
        $image = $imagine->open('public/img/'.$folder.'/'.$data['imagem']['name']);


        //Large image
        $transformation = new \Imagine\Filter\Transformation();
        $transformation->thumbnail(new \Imagine\Image\Box(900, 675));
        $large = 'public/img/'.$folder.'/' . $newName;
        $transformation->apply($image)->save($large);
        $this->saveToS3('manequim-digital/'.$folder, $large, 'large_'.$newName, $data['imagem']['type']);

        //now the medium image
        $transformation = new \Imagine\Filter\Transformation();
        $transformation->thumbnail(new \Imagine\Image\Box(700, 500));
        $medium = 'public/img/'.$folder.'/' . $newName;
        $transformation->apply($image)->save($medium);

        //upload medium image to s3
        $this->saveToS3('manequim-digital/'.$folder, $medium, $newName, $data['imagem']['type']);

        //Job done, delete all those temporary images        
        $this->deleteImages($newName, $folder);

        return $newName;
    }

    protected function deleteImages($name, $folder) {
        if (file_exists('public/img/'.$folder.'/' . $name))
            unlink('public/img/'.$folder.'/' . $name);
    }

    public function getPeriodoAvaliacao($mes, $ano) {
        $mes = (int) $mes;
        return array('mes' => $mes, 'ano' => $ano);
    }

    public function getExtensao($name){
        $extensao = explode('.', $name);
        return $extensao[1];
    }

    public function uploadImagem($arquivos, $caminho, $dados){
        if(!file_exists($caminho)){
            mkdir($caminho);
        }
        

        foreach ($arquivos as $nomeArquivo => $arquivo) {
            if(!empty($arquivo['tmp_name'])){
                $extensao = $this->getExtensao($arquivo['name']);
                $nomeArquivoServer = 'arquivo'.date('dhsi');
                if(move_uploaded_file($arquivo['tmp_name'], $caminho.'/'.$nomeArquivoServer.'.'.$extensao)){
                    $dados[$nomeArquivo] = $caminho.'/'.$nomeArquivoServer.'.'.$extensao;
                }
            }
        }

        return $dados;
    }

    public function alfabeto(){
        $alfabeto = array(
                        1 => 'A',
                        2 => 'B',
                        3 => 'C',
                        4 => 'D',
                        5 => 'E',
                        6 => 'F',
                        7 => 'G',
                        8 => 'H',
                        9 => 'I',
                        10 => 'J',
                        11 => 'K',
                        12 => 'L',
                        13 => 'M',
                        14 => 'N',
                        15 => 'O',
                        16 => 'P',
                        17 => 'Q',
                        18 => 'R',
                        19 => 'S',
                        20 => 'T',
                        21 => 'U',
                        22 => 'V',
                        23 => 'W',
                        24 => 'X',
                        25 => 'Y',
                        26 => 'Z'

                    );
        $alfabeto2 = $alfabeto;
        foreach ($alfabeto as $letra) {
            $alfabeto2[] = 'A'.$letra;
        }

        return $alfabeto2;
    }

}
