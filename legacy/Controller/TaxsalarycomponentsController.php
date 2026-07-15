<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<?php

/**
 * Static content controller.
 *
 * This file will render views from views/pages/
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
App::uses('AppController', 'Controller');
ini_set("display_errors", 0);

App::uses('ConnectionManager', 'Model');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class TaxsalarycomponentsController extends AppController {

    /**
     * Controller name
     *
     * @var string
     */
    public $name = 'Taxsalarycomponents';

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('Taxsalarycomponents', 'SalaryHeadItems');
    public $components = array('DatatablesManagement');
    /*
     * Dashboard landing view
     */

    public function index() {

        $this->layout = null;
                $this->Taxsalarycomponents->useDbConfig = $this->Session->read('ds');
        $this->SalaryHeadItems->useDbConfig = $this->Session->read('ds');
        $taxcomponents = $this->Taxsalarycomponents->find("all");
        $this->set('taxcomponents',$taxcomponents);
       $heads = $this->SalaryHeadItems->find("all",array('conditions'=>"value = 'Y' "));
        $this->set('heads',$heads);
    }
    public function save()
    {
        $this->Taxsalarycomponents->useDbConfig = $this->Session->read('ds');
        $this->autoRender =  FALSE ;
        
        $arr_requestdata = $this->request->data;
        $count1 = count($arr_requestdata['pkey']);
        $pkey1 = 0;
        $data = array();
        for($i = 0;$i <  $count1 ; $i++)
        {
          $data['tax_salary_components_pkey'] = $arr_requestdata['pkey'][$i];
          $data['salary_head_item_Fkey'] = $arr_requestdata['components'][$i];
          $data['upper_limit'] = $arr_requestdata['Limit'][$i];
          $this->Taxsalarycomponents->save($data);
        }
        
        
    }
}
