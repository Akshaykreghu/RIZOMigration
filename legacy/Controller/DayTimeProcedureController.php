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
class DayTimeProcedureController extends AppController
{

	/**
	 * Controller name
	 *
	 * @var string
	 */
	public $name = 'DayTimeProcedure';

	/**
	 * This controller does not use a model
	 *
	 * @var array
	 */
	public $uses = array('DayTimeProcedures', 'EmployeeProfessionalDetails', 'Menu', 'ShiftException');
	public function index()
	{
		$this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');
		//debug(	$this->CompanyContactInfo->find("all"));
		//edited by athira on 06-11-2025
		$plan = $this->DayTimeProcedures->query('SELECT plan FROM comp_contact_info');
		$plan = isset($plan['0']['comp_contact_info']['plan']) ? $plan['0']['comp_contact_info']['plan'] : '';
		$this->set('plan', $plan);
		$company_code = strtoupper($this->Session->read('company_code'));
		$this->set("company_code", $company_code);
		//edited by athira on 11-02-2026
		$restrictedCompanies = [
			'ABSG',
			'VGFS',
			'VSFS',
			'DRRC',
			'DJIC',
			'AGNG',
			'AYRK',
			'GTRA',
			'VGNN',
			'SHYD',
			'SRTS'
		];
		// if($company_code =='NRMY' || $company_code =='AELY' || $company_code =='GRNH' || $company_code =='ATNE'){
		if (!in_array($company_code, $restrictedCompanies)) {
			$this->render('index_nw');
		}
		//end
	}

	public function lists()
	{
		//edited by athira on 08-02-2025
		$this->Menu->useDbConfig = $this->Session->read('ds');
		$plan = $this->Menu->query('SELECT plan FROM comp_contact_info');
		$plan = $plan['0']['comp_contact_info']['plan'];
		$this->set('plan', $plan);
		//end 
		$this->layout = null;
		$proc_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
		$this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');
		$respdata = array();

		if ($proc_id) {
			$dayTimeProcedure  = $this->DayTimeProcedures->find("first", array("conditions" => array("day_time_seq" => $proc_id, "active" => "1")));
			//	debug($dayTimeProcedure);
			$respdata = $dayTimeProcedure['DayTimeProcedures'];
		}
		$is_exceptionArr = $this->DayTimeProcedures->query("SELECT is_exception FROM working_day_time_procedures Where day_time_seq='$proc_id' and active=1");
		$is_exception = $is_exceptionArr[0]['working_day_time_procedures']['is_exception'];
		$this->set('is_exception', $is_exception);

		$components = $this->DayTimeProcedures->query("SELECT * FROM `salary_head_items` left join salary_heads on (salary_heads.head_pkey = salary_head_items.head_fkey) WHERE head_occurance = 'VARIABLE' and lcase(value) = 'y'");
		$this->set("data", $respdata);
		$this->set("components", $components);
		//edited by athira on 06-11-2025
		$company_code = strtoupper($this->Session->read('company_code'));
		$this->set("company_code", $company_code);
		//edited by athira on 11-02-2026
		$restrictedCompanies = [
			'ABSG',
			'VGFS',
			'VSFS',
			'DRRC',
			'DJIC',
			'AGNG',
			'AYRK',
			'GTRA',
			'VGNN',
			'SHYD',
			'SRTS'
		];
		// if($company_code =='NRMY' || $company_code =='AELY' || $company_code =='GRNH' || $company_code =='ATNE'){
		if (!in_array($company_code, $restrictedCompanies)) {
			$this->render('lists_new');
		}
		//end
	}

	public function listpolicies()
	{

		$this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');
		$conditions['active'] = 1;
		$limit = $_REQUEST['rows'];
		$page = $_REQUEST['page'];

		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'day_time_desc';
		$order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';

		$ofst = ($page - 1) * $limit;

		$count = $this->DayTimeProcedures->find("count", array("conditions" => $conditions));
		$policies = $this->DayTimeProcedures->find("all", array("conditions" => $conditions, 'order' => array($sort => $order), 'limit' => intval($limit), 'offset' => intval($ofst)));


		$arr_policies  = array();
		$arr_policies["rows"] = array();
		foreach ($policies as $key => $value) {
			$arr_policies["rows"][$key] = $value["DayTimeProcedures"];
		}


		$arr_policies["total"] = $count;


		echo json_encode($arr_policies);


		$this->autoRender = FALSE;
	}

	public function listpoliciesforconfig()
	{

		$this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');
		$conditions['active'] = 1;
		//$count = $this -> DayTimeProcedures -> find("count",array("conditions"=>$conditions));
		$policies = $this->DayTimeProcedures->find("all", array("conditions" => $conditions));


		$arr_policies  = array();
		$arr_policies["rows"] = array();
		foreach ($policies as $key => $value) {
			$policy['id']   = $value["DayTimeProcedures"]['day_time_seq'];
			$policy['data'] =  array($value["DayTimeProcedures"]['day_time_desc']);
			$arr_policies["rows"][] = $policy;
		}




		echo json_encode($arr_policies);


		$this->autoRender = FALSE;
	}
	public function saveDayTimeProcedure()
	{
		$this->autoRender = FALSE;
		$this->layout = null;
		$company_code = strtoupper($this->Session->read('company_code'));
		$this->set("company_code", $company_code);
		//edited by athira on 06-11-2025
		// if($company_code =='NRMY' || $company_code =='AELY' || $company_code =='GRNH' || $company_code =='ATNE'){
		//edited by athira on 11-02-2026
		$restrictedCompanies = [
			'ABSG',
			'VGFS',
			'VSFS',
			'DRRC',
			'DJIC',
			'AGNG',
			'AYRK',
			'GTRA',
			'VGNN',
			'SHYD',
			'SRTS'
		];

		if (!in_array($company_code, $restrictedCompanies)) {
			$this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');
			$this->ShiftException->useDbConfig = $this->Session->read('ds');


			$arr_form_data	= $this->request->data;

			$data  = array();
			if ($this->checkshiftpolicyexists($arr_form_data['day_time_desc'], $arr_form_data['day_time_seq']) > 0) {
				return json_encode(array("success" => false, "msg" => "Shift Policy Exists "));
				//edited by athira on 06-11-2025
				exit;
				//end
			}

			$data['day_time_seq']  = ($arr_form_data['day_time_seq']) ? $arr_form_data['day_time_seq'] : 0;
			$data['day_time_desc'] = $arr_form_data['day_time_desc'];
			$data['Monday'] 	   = (isset($arr_form_data['Monday'])) ? "Y" : "N";
			$data['Tuesday'] 	   = (isset($arr_form_data['Tuesday'])) ? "Y" : "N";
			$data['Wednesday']     = (isset($arr_form_data['Wednesday'])) ? "Y" : "N";
			$data['Thursday'] 	   = (isset($arr_form_data['Thursday'])) ? "Y" : "N";
			$data['Friday'] 	   = (isset($arr_form_data['Friday'])) ? "Y" : "N";
			$data['Saturday']      = (isset($arr_form_data['Saturday'])) ? "Y" : "N";
			$data['include_break'] = isset($arr_form_data['include_break']) ? "Y" : "N";
			$data['strict_monitorings'] 	   = (isset($arr_form_data['someSwitchOption001'])) && $arr_form_data['someSwitchOption001'] == "Y" ? 'Y' : 'N';
			$data['Sunday']        = (isset($arr_form_data['Sunday'])) ? "Y" : "N";
			$data['Monday_F'] 	   = (isset($arr_form_data['Monday_F'])) ? "Y" : "N";
			$data['Tuesday_F'] 	   = (isset($arr_form_data['Tuesday_F'])) ? "Y" : "N";
			$data['Wednesday_F']   = (isset($arr_form_data['Wednesday_F'])) ? "Y" : "N";
			$data['Thursday_F']    = (isset($arr_form_data['Thursday_F'])) ? "Y" : "N";
			$data['Friday_F'] 	   = (isset($arr_form_data['Friday_F'])) ? "Y" : "N";
			$data['Saturday_F']    = (isset($arr_form_data['Saturday_F'])) ? "Y" : "N";
			$data['Sunday_F']      = (isset($arr_form_data['Sunday_F'])) ? "Y" : "N";
			$data['isnextday']     = isset($arr_form_data['nextday']) ? "1" : "0";
			$data['on_dutty1']     = $arr_form_data['on_dutty1'];
			$data['off_dutty1']    = $arr_form_data['off_dutty1'];
			$data['working_time1'] = $arr_form_data['working_time1'];
			// $data['working_time4'] = $arr_form_data['working_time4'];
			// $data['overtime_monitoring'] = isset($arr_form_data['overtime_monitoring']) ? $arr_form_data['overtime_monitoring'] : '';
			$data['working_time2'] = isset($arr_form_data['working_time2']) ? $arr_form_data['working_time2'] : "";
			//if($data['working_time2'] > 0){
			$data['on_dutty2']     =  isset($arr_form_data['on_dutty2']) ? $arr_form_data['on_dutty2'] : "";
			$data['off_dutty2']    = isset($arr_form_data['off_dutty2']) ? $arr_form_data['off_dutty2'] : "";
			//}
			$data['on_dutty4']     = isset($arr_form_data['in_duty4']) ? $arr_form_data['in_duty4'] : "";
			$data['off_dutty4']    = isset($arr_form_data['out_duty4']) ? $arr_form_data['out_duty4'] : "";

			$data['on_dutty3']     = isset($arr_form_data['on_dutty3']) ? $arr_form_data['on_dutty3'] : "";
			$data['off_dutty3']    = isset($arr_form_data['off_dutty3']) ? $arr_form_data['off_dutty3'] : "";
			$data['working_time3']    = isset($arr_form_data['working_time3']) ? $arr_form_data['working_time3'] : "";
			$data['max_out_before_next_in'] 	   = (isset($arr_form_data['max_out_before_next_in'])) ? "Y" : "N";

			$data['on_dutty6']     = isset($arr_form_data['on_dutty6']) ? $arr_form_data['on_dutty6'] : "";
			$data['off_dutty6']    = isset($arr_form_data['off_dutty6']) ? $arr_form_data['off_dutty6'] : "";
			$data['working_time6']    = isset($arr_form_data['working_time6']) ? $arr_form_data['working_time6'] : "";

			$data['on_dutty5']     = isset($arr_form_data['on_dutty5']) ? $arr_form_data['on_dutty5'] : "";
			$data['off_dutty5']    = isset($arr_form_data['off_dutty5']) ? $arr_form_data['off_dutty5'] : "";
			$data['working_time5']    = isset($arr_form_data['working_time5']) ? $arr_form_data['working_time5'] : "";

			if (isset($arr_form_data['weekday'])) {
				$data[$arr_form_data['weekday'] . '_F'] = isset($arr_form_data['off_occures']) ? $arr_form_data['off_occures'] : '';
				$data['working_time4_ex_day'] = isset($arr_form_data['working_time4_ex_day']) ? $arr_form_data['working_time4_ex_day'] : '';
				$data['working_time3_ex_day'] = isset($arr_form_data['working_time3_ex_day']) ? $arr_form_data['working_time3_ex_day'] : '';
				$data['working_time5_ex_day'] = isset($arr_form_data['working_time5_ex_day']) ? $arr_form_data['working_time5_ex_day'] : '';
				$data['working_time6_ex_day'] = isset($arr_form_data['working_time6_ex_day']) ? $arr_form_data['working_time6_ex_day'] : '';
			}


			// edited by athira on 05-05-2026
			$is_exception = isset($arr_form_data['is_exception']) ? $arr_form_data['is_exception'] : '0';
			$day_time_seq = ($arr_form_data['day_time_seq']) ? $arr_form_data['day_time_seq'] : 0;
			$userId = $this->Session->read('login_user_id');

			// Sync exception status based on master switch
			// if ($day_time_seq > 0) {
			//     if ($is_exception == '0') {
			//         $this->ShiftException->updateAll(
			//             ['ShiftException.status' => 2],
			//             ['ShiftException.modified_by' => $userId],
			//             ['ShiftException.shift_id' => $day_time_seq, 'ShiftException.status' => 1]
			//         );
			//     } else {
			//         $this->ShiftException->updateAll(
			//             ['ShiftException.status' => 1],
			//               ['ShiftException.modified_by' => $userId],
			//             ['ShiftException.shift_id' => $day_time_seq, 'ShiftException.status' => 2]
			//         );
			//     }
			// } else {
			//     // New shift - handle unsaved exceptions
			//     if ($is_exception == '0') {
			//         $this->ShiftException->updateAll(
			//             ['ShiftException.status' => 2],
			//             ['ShiftException.shift_id' => 0, 'ShiftException.created_by' => $userId, 'ShiftException.status' => 1]
			//         );
			//     } else {
			//         $this->ShiftException->updateAll(
			//             ['ShiftException.status' => 1],
			//             ['ShiftException.shift_id' => 0, 'ShiftException.created_by' => $userId, 'ShiftException.status' => 2]
			//         );
			//     }
			// }

			// Sync exception status based on master switch
			if ($day_time_seq > 0) {

				if ($is_exception == '0') {

					$this->ShiftException->updateAll(
						array(
							'ShiftException.status' => 2,
							'ShiftException.modified_by' => "'" . $userId . "'",
							'ShiftException.modification_date' => "'" . date('Y-m-d H:i:s') . "'"
						),
						array(
							'ShiftException.shift_id' => $day_time_seq,
							'ShiftException.status' => 1
						)
					);
				} else {

					$this->ShiftException->updateAll(
						array(
							'ShiftException.status' => 1,
							'ShiftException.modified_by' => "'" . $userId . "'",
							'ShiftException.modification_date' => "'" . date('Y-m-d H:i:s') . "'"
						),
						array(
							'ShiftException.shift_id' => $day_time_seq,
							'ShiftException.status' => 2
						)
					);
				}
			} else {

				// New shift - handle unsaved exceptions
				if ($is_exception == '0') {

					$this->ShiftException->updateAll(
						array(
							'ShiftException.status' => 2,
							'ShiftException.modified_by' => "'" . $userId . "'",
							'ShiftException.modification_date' => "'" . date('Y-m-d H:i:s') . "'"
						),
						array(
							'ShiftException.shift_id' => 0,
							'ShiftException.created_by' => $userId,
							'ShiftException.status' => 1
						)
					);
				} else {

					$this->ShiftException->updateAll(
						array(
							'ShiftException.status' => 1,
							'ShiftException.modified_by' => "'" . $userId . "'",
							'ShiftException.modification_date' => "'" . date('Y-m-d H:i:s') . "'"
						),
						array(
							'ShiftException.shift_id' => 0,
							'ShiftException.created_by' => $userId,
							'ShiftException.status' => 2
						)
					);
				}
			}

			$data['is_exception'] = $is_exception;

			// Backend safety check: if no active exceptions exist, force is_exception to 0
			if ($day_time_seq > 0) {
				$excCount = $this->ShiftException->find('count', array(
					'conditions' => array(
						'ShiftException.shift_id' => $day_time_seq,
						'ShiftException.status' => 1
					)
				));
				if ($excCount == 0) {
					$data['is_exception'] = '0';
				}
			}
			// ended by athira on 05-05-2026

			$data['minuts_calc_perday']  = $arr_form_data['minuts_calc_perday'];
			$data['minutes_per_half']  = $arr_form_data['minutes_per_half'];
			$data['minuts_aftr_on_dutty_cal_late'] = $arr_form_data['minuts_aftr_on_dutty_cal_late'];
			$data['minuts_bfr_off_dutty_cal_early'] = $arr_form_data['minuts_bfr_off_dutty_cal_early'];
			$data['min_cal_late_ifnoclockin'] = (isset($arr_form_data['min_cal_late_ifnoclockin'])) ? $arr_form_data['min_cal_late_ifnoclockin'] : 0;
			$data['min_cal_leave_early_ifnoclockout'] = (isset($arr_form_data['min_cal_leave_early_ifnoclockout'])) ? $arr_form_data['min_cal_leave_early_ifnoclockout'] : 0;
			$data['min_aftr_off_dutty_cal_ot'] = (isset($arr_form_data['min_aftr_off_dutty_cal_ot'])) ? $arr_form_data['min_aftr_off_dutty_cal_ot'] : 0;
			$data['min_bfr_on_dutty_cal_ot'] = (isset($arr_form_data['min_bfr_on_dutty_cal_ot'])) ? $arr_form_data['min_bfr_on_dutty_cal_ot'] : 0;
			$data['work_time_day_off_cal_ot'] = (isset($arr_form_data['work_time_day_off_cal_ot'])) ? $arr_form_data['work_time_day_off_cal_ot'] : 0;
			$data['day_time_proc_active'] = "Y";
			$data['shift_allowance'] = isset($arr_form_data['shift_allowance']) ? $arr_form_data['shift_allowance'] : "";
			$data['otcomponents'] = isset($arr_form_data['Otc']) ? $arr_form_data['Otc'] : "";
			$data['is_multiple_days'] = isset($arr_form_data['enablemutishift']) ? $arr_form_data['enablemutishift'] : "N";
			$data['no_of_shift_days'] = isset($arr_form_data['multishift']) ? $arr_form_data['multishift'] : "0";
			//edited by athira on 06-11-2025
			$data['max_out_time'] = isset($arr_form_data['max_out_time']) ? $arr_form_data['max_out_time'] : NULL;
			$data['max_in_time'] = isset($arr_form_data['max_in_time']) ? $arr_form_data['max_in_time'] : NULL;
			$data['overtime_eligibility'] = isset($arr_form_data['overtime_eligibility']) ? $arr_form_data['overtime_eligibility'] : "N";

			$data['include_break'] = isset($arr_form_data['include_break']) ? $arr_form_data['include_break'] : "N"; // Edited by Akshay on 12-8-2025
			// Edited by Akshay on 30-4-2026
			if ($data['min_aftr_off_dutty_cal_ot'] == 0 && $data['min_bfr_on_dutty_cal_ot'] == 0) {
				$data['ot_eligibility_threshold'] = 'Y';
			} else {
				$data['ot_eligibility_threshold'] = $arr_form_data['ot_eligibility_threshold'];
			}
			// End

			// Edited by Akshay on 11-5-2026
			// Check if actual changes exist
			$user_id = $this->Session->read("login_user_id"); //user id
			if (!empty($data['day_time_seq'])) {

				$existingData = $this->DayTimeProcedures->find('first', array(
					'conditions' => array(
						'DayTimeProcedures.day_time_seq' => $data['day_time_seq']
					),
					'recursive' => -1
				));

				if (!empty($existingData)) {

					$existing = $existingData['DayTimeProcedures'];

					$hasChanges = false;

					foreach ($data as $key => $value) {

						$newValue = ($value === null) ? '' : trim((string) $value);

						if (!isset($existing[$key])) {
							continue;
						}
						$oldValue = isset($existing[$key])
							? trim((string) $existing[$key])
							: '';
						// debug($key);
						// debug($newValue);
						// debug($oldValue);
						if ((empty($newValue) ? 0 : $newValue) != (empty($oldValue) ? 0 : $oldValue)) {
							$hasChanges = true;
							// debug('hasChanges');
							break;
						}
					}

					// Set only when actual change exists
					if ($hasChanges) {
						$data['modified_by'] = $user_id;
					}
				}
			} else {
				$data['created_by'] = $user_id;
			}
			// End

			// Edited by Akshay on 19-5-2026
			$data['first_in_last_punch'] = (isset($data['include_break']) && $data['include_break'] == 'Y') ?  (isset($arr_form_data['first_in_last_punch']) ? $arr_form_data['first_in_last_punch'] : 'N') : 'N';
			// End

			$this->DayTimeProcedures->save($data);
			$day_time_proc_id = $this->DayTimeProcedures->getLastInsertId();
			if (!empty($day_time_proc_id)) {
				$this->ShiftException->updateAll(
					['ShiftException.shift_id' => "'{$day_time_proc_id}'"],
					[
						'ShiftException.shift_id' => 0,
						'ShiftException.created_by' => $this->Session->read('login_user_id')
					]
				);
			}
			$resp = array();
			$resp["success"] = 1;
			$resp['day_time_seq'] = ($arr_form_data['day_time_seq']) ? $arr_form_data['day_time_seq'] : $day_time_proc_id;
			$resp['msg'] = "Shift Policy saved successfully";

			//end

			echo json_encode($resp);
		} else {
			$this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');

			$arr_form_data	= $this->request->data;
			$data  = array();
			if ($this->checkshiftpolicyexists($arr_form_data['day_time_desc'], $arr_form_data['day_time_seq']) > 0) {
				return json_encode(array("success" => false, "msg" => "Shift Policy Exists "));
			}

			$data['day_time_seq']  = ($arr_form_data['day_time_seq']) ? $arr_form_data['day_time_seq'] : 0;
			$data['day_time_desc'] = $arr_form_data['day_time_desc'];
			$data['Monday'] 	   = (isset($arr_form_data['Monday'])) ? "Y" : "N";
			$data['Tuesday'] 	   = (isset($arr_form_data['Tuesday'])) ? "Y" : "N";
			$data['Wednesday']     = (isset($arr_form_data['Wednesday'])) ? "Y" : "N";
			$data['Thursday'] 	   = (isset($arr_form_data['Thursday'])) ? "Y" : "N";
			$data['Friday'] 	   = (isset($arr_form_data['Friday'])) ? "Y" : "N";
			$data['Saturday']      = (isset($arr_form_data['Saturday'])) ? "Y" : "N";
			$data['strict_monitorings'] 	   = (isset($arr_form_data['someSwitchOption001'])) && $arr_form_data['someSwitchOption001'] == "Y" ? 'Y' : 'N';
			$data['Sunday']        = (isset($arr_form_data['Sunday'])) ? "Y" : "N";
			$data['Monday_F'] 	   = (isset($arr_form_data['Monday_F'])) ? "Y" : "N";
			$data['Tuesday_F'] 	   = (isset($arr_form_data['Tuesday_F'])) ? "Y" : "N";
			$data['Wednesday_F']     = (isset($arr_form_data['Wednesday_F'])) ? "Y" : "N";
			$data['Thursday_F'] 	   = (isset($arr_form_data['Thursday_F'])) ? "Y" : "N";
			$data['Friday_F'] 	   = (isset($arr_form_data['Friday_F'])) ? "Y" : "N";
			$data['Saturday_F']      = (isset($arr_form_data['Saturday_F'])) ? "Y" : "N";
			$data['Sunday_F']        = (isset($arr_form_data['Sunday_F'])) ? "Y" : "N";
			$data['isnextday']     = isset($arr_form_data['nextday']) ? "1" : "0";
			$data['on_dutty1']     = $arr_form_data['on_dutty1'];
			$data['off_dutty1']    = $arr_form_data['off_dutty1'];
			$data['working_time1'] = $arr_form_data['working_time1'];
			$data['working_time4'] = $arr_form_data['working_time4'];
			$data['on_dutty2']     =  isset($arr_form_data['on_dutty2']) ? $arr_form_data['on_dutty2'] : "";
			$data['off_dutty2']    = isset($arr_form_data['off_dutty2']) ? $arr_form_data['off_dutty2'] : "";
			$data['on_dutty4']     = isset($arr_form_data['in_duty4']) ? $arr_form_data['in_duty4'] : "";
			$data['off_dutty4']    = isset($arr_form_data['out_duty4']) ? $arr_form_data['out_duty4'] : "";
			if (isset($arr_form_data['weekday'])) {
				$data[$arr_form_data['weekday'] . '_F'] = isset($arr_form_data['off_occures']) ? $arr_form_data['off_occures'] : '';
			}
			$data['is_exception'] = isset($arr_form_data['is_exception']) ? $arr_form_data['is_exception'] : 0;
			$data['working_time2'] = isset($arr_form_data['working_time2']) ? $arr_form_data['working_time2'] : "";
			$data['minuts_calc_perday']  = $arr_form_data['minuts_calc_perday'];
			$data['minutes_per_half']  = $arr_form_data['minutes_per_half'];
			$data['minuts_aftr_on_dutty_cal_late'] = $arr_form_data['minuts_aftr_on_dutty_cal_late'];
			$data['minuts_bfr_off_dutty_cal_early'] = $arr_form_data['minuts_bfr_off_dutty_cal_early'];
			$data['min_cal_late_ifnoclockin'] = (isset($arr_form_data['min_cal_late_ifnoclockin'])) ? $arr_form_data['min_cal_late_ifnoclockin'] : 0;
			$data['min_cal_leave_early_ifnoclockout'] = (isset($arr_form_data['min_cal_leave_early_ifnoclockout'])) ? $arr_form_data['min_cal_leave_early_ifnoclockout'] : 0;
			$data['min_aftr_off_dutty_cal_ot'] = (isset($arr_form_data['min_aftr_off_dutty_cal_ot'])) ? $arr_form_data['min_aftr_off_dutty_cal_ot'] : 0;
			$data['min_bfr_on_dutty_cal_ot'] = (isset($arr_form_data['min_bfr_on_dutty_cal_ot'])) ? $arr_form_data['min_bfr_on_dutty_cal_ot'] : 0;
			$data['work_time_day_off_cal_ot'] = (isset($arr_form_data['work_time_day_off_cal_ot'])) ? $arr_form_data['work_time_day_off_cal_ot'] : 0;
			$data['day_time_proc_active'] = "Y";
			$data['shift_allowance'] = isset($arr_form_data['shift_allowance']) ? $arr_form_data['shift_allowance'] : "";
			$data['otcomponents'] = isset($arr_form_data['Otc']) ? $arr_form_data['Otc'] : "";
			$data['is_multiple_days'] = isset($arr_form_data['enablemutishift']) ? $arr_form_data['enablemutishift'] : "N";
			$data['no_of_shift_days'] = isset($arr_form_data['multishift']) ? $arr_form_data['multishift'] : "0";
			$data['max_out_time'] = isset($arr_form_data['max_out_time']) ? $arr_form_data['max_out_time'] : ""; // Edited by Akshay on 11-8-2025
			$this->DayTimeProcedures->save($data);
			$day_time_proc_id = $this->DayTimeProcedures->getLastInsertId();
			$resp = array();
			$resp["success"] = true;
			$resp['day_time_seq'] = ($arr_form_data['day_time_seq']) ? $arr_form_data['day_time_seq'] : $day_time_proc_id;
			$resp['msg'] = "Daytime procedure saved successfully";

			echo json_encode($resp);
		}
	}


	public function delete()
	{

		$this->autoRender = FALSE;
		$this->layout = null;

		$this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');
		$this->EmployeeProfessionalDetails->useDbConfig = $this->Session->read('ds');

		$arr_form_data	=	$this->request->data;


		$ar_id = explode(",", $_REQUEST['ids']);
		$data = array();

		//	$dt = DateTime::createFromFormat('m/d/Y', $arr_form_data['HOLIDAYDATE']);

		//$data['id'] = 0;
		$joins = array(
			array(
				'table' => 'emp_details',
				'alias' => 'EmployeeDetails',
				'type' => 'LEFT',
				'foreignKey' => false,
				'conditions' => array('EmployeeDetails.emp_pkey = EmployeeProfessionalDetails.emp_fkey')
			)
		);
		//$asiigned = $this->EmployeeProfessionalDetails->find("count", array("joins" => $joins, "conditions" => array('EmployeeProfessionalDetails.day_time_seq' => $ar_id, 'EmployeeDetails.status' => 1)));
		// Edited by Akshay on 19-6-2025
		$asiigned = $this->EmployeeProfessionalDetails->find("count", array(
			"joins" => $joins,
			"conditions" => array(
				'OR' => array(
					'EmployeeProfessionalDetails.day_time_seq' => $ar_id,
					'EmployeeProfessionalDetails.multishift' => $ar_id
				),
				'EmployeeDetails.status' => 1
			)
		));

		// End
		$resp = array();
		if ($asiigned > 0) {
			$resp["success"] = false;
			$resp["msg"] = "Shift policy cannot be deleted, remove employees under this shift";
		} else {
			$this->DayTimeProcedures->updateAll(array('DayTimeProcedures.active' => 0), array('DayTimeProcedures.day_time_seq' => $ar_id));
			$resp["success"] = true;
			$resp["msg"] = "Shift policy  deleted successfully";
		}
		echo json_encode($resp);
	}
	public function form()
	{
		//edited by athira on 08-02-2025
		$this->Menu->useDbConfig = $this->Session->read('ds');
		$plan = $this->Menu->query('SELECT plan FROM comp_contact_info');
		$plan = $plan['0']['comp_contact_info']['plan'];
		$this->set('plan', $plan);
		//end
		// $this->autoRender = FALSE;
		$this->layout = null;
		$proc_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
		$this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');
		$respdata = array();

		if ($proc_id) {
			$dayTimeProcedure  = $this->DayTimeProcedures->find("first", array("conditions" => array("day_time_seq" => $proc_id, "active" => "1")));
			//	debug($dayTimeProcedure);
			$respdata = $dayTimeProcedure['DayTimeProcedures'];
		}
		$components = $this->DayTimeProcedures->query("SELECT * FROM `salary_head_items` left join salary_heads on (salary_heads.head_pkey = salary_head_items.head_fkey) WHERE head_occurance = 'VARIABLE' and lcase(value) = 'y'");
		$this->set("data", $respdata);
		$this->set("components", $components);
		//	die;
		$company_code = strtoupper($this->Session->read('company_code'));
		$this->set("company_code", $company_code);
		//edited by athira on 06-11-2025
		// if($company_code =='NRMY' || $company_code =='AELY' || $company_code =='GRNH' || $company_code =='ATNE'){
		//edited by athira on 11-02-2026
		$restrictedCompanies = [
			'ABSG',
			'VGFS',
			'VSFS',
			'DRRC',
			'DJIC',
			'AGNG',
			'AYRK',
			'GTRA',
			'VGNN',
			'SHYD',
			'SRTS'
		];

		if (!in_array($company_code, $restrictedCompanies)) {
			$this->render('form_new');
		}
		//end

	}
	public function checkshiftpolicyexists($codecount = '', $day_time_seq = 0)
	{
		$this->autoRender = false;

		$arr_requestdata = $this->request->data;

		$day_time_desc = isset($arr_requestdata['day_time_desc']) ? $arr_requestdata['day_time_desc'] : $codecount;

		$int_shiftcount = 0;
		if ($day_time_desc != '') {
			$this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');
			$int_shiftcount = $this->DayTimeProcedures->find(
				"count",
				array(
					'conditions' => array('DayTimeProcedures.day_time_desc' => $day_time_desc, 'DayTimeProcedures.active' => 1, "DayTimeProcedures.day_time_seq != '$day_time_seq'")
				)
			);
		}
		return $int_shiftcount;
	}

	public function roaster()
	{

		$this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');
		$this->set("company_code", strtoupper($this->Session->read('company_code')));
	}

	//edited by athira on 06-11-2025

	public function deleteTempExceptions()
	{
		$this->autoRender = false;
		$this->layout = null;

		$this->ShiftException->useDbConfig = $this->Session->read('ds');
		$userId = $this->Session->read('login_user_id'); // logged-in user

		// 🧹 Delete only this user's unsaved exceptions (safer)
		$deleted = $this->ShiftException->query("
        DELETE FROM shift_exceptions
        WHERE shift_id = 0
        AND created_by = '{$userId}'
    ");

		echo json_encode([
			'status' => ($deleted ? 'success' : 'none'),
			'msg' => ($deleted ? 'Temporary exceptions deleted.' : 'No temporary exceptions found.')
		]);
	}

	public function saveException()
	{
		$this->autoRender = false;
		$this->ShiftException->useDbConfig = $this->Session->read('ds');
		$this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');
		$this->loadModel('ShiftException');

		if ($this->request->is('post')) {
			$data = $this->request->data;


			$shiftId = isset($data['day_time_seq']) ? $data['day_time_seq'] : null;
			$day = isset($data['day']) ? $data['day'] : null;
			$weekOffFlag = isset($data['week_off']) ? $data['week_off'] : null;      // Y or N (radio)
			$weekOffType = isset($data['week_off_type']) ? $data['week_off_type'] : null; // 1st/2nd/.../All

			// if (!$shiftId || !$day) {
			//     echo json_encode(array('status' => 'error', 'message' => 'Missing shift or day'));
			//     return;
			// }

			$dayName = $day; // Sunday, Monday etc.

			$dayCol = $dayName;        // ex = "Sunday"
			$flagCol = $dayName . '_F'; // ex = "Sunday_F"

			// Fetch DayTimeProcedure row
			$dayData = $this->DayTimeProcedures->find('first', [
				'conditions' => ['day_time_seq' => $shiftId]
			]);

			// Check if the day is ACTIVE & WORKING based on your logic
			$isWorking = (
				isset($dayData['DayTimeProcedures'][$dayCol]) &&
				isset($dayData['DayTimeProcedures'][$flagCol]) &&
				$dayData['DayTimeProcedures'][$dayCol] == 'Y' &&
				$dayData['DayTimeProcedures'][$flagCol] == 'N'
			);

			// Now block Week-Off = All
			if ($isWorking && $weekOffFlag == 'Y' && $weekOffType == 'A') {
				echo json_encode([
					'status' => 'error',
					'message' => "This day ($dayName) is set as a working day in shift calendar, so you cannot mark it Week Off for ALL."
				]);
				return;
			}


			// --- DUPLICATE CHECK for week count ---
			$existing = $this->ShiftException->find('all', array(
				'conditions' => array(
					'ShiftException.shift_id' => $shiftId,
					'ShiftException.ex_week_day' => $day,
					'ShiftException.status' => 1
				)
			));

			if (!empty($existing)) {
				foreach ($existing as $ex) {
					$existingWeek = $ex['ShiftException']['ex_week'];

					// If any existing 'All' exists, block any new entry
					if ($existingWeek == 'A') {
						echo json_encode(array(
							'status' => 'error',
							'message' => 'Week count "All" already exists for this shift and day. Cannot save other week counts.'
						));
						return;
					}

					// If trying to save 'All' but any other week exists, block
					if ($weekOffType == 'A') {
						echo json_encode(array(
							'status' => 'error',
							'message' => 'Cannot save "All" week count because other week counts already exist for this shift and day.'
						));
						return;
					}

					// If exact week already exists, block
					if ($existingWeek == $weekOffType) {
						echo json_encode(array(
							'status' => 'error',
							'message' => 'This week count already exists for this shift and day.'
						));
						return;
					}
				}
			}

			$created_by = $this->Session->read('login_user_id');

			// Step 2: Insert new record with status = 1
			$exceptionData = array(
				'ShiftException' => array(
					'shift_id'   => $shiftId,
					'ex_week_day' => $day,
					'in_time'    => isset($data['in_time']) ? $data['in_time'] : '00:00:00',
					'out_time'   => isset($data['out_time']) ? $data['out_time'] : '00:00:00',
					'duration'   => isset($data['duration']) ? $data['duration'] : 0,
					'week_off'   => $weekOffFlag,            // Y or N
					'ex_week'    => $weekOffType,            // 1st / 2nd / 3rd / All / etc.
					'full_day'   => isset($data['full_day']) ? $data['full_day'] : 0,
					'half_day'   => isset($data['half_day']) ? $data['half_day'] : 0,
					'created_by' => $created_by,
					'status'     => 1
				)
			);

			if ($this->ShiftException->save($exceptionData)) {
				// ✅ Update is_exception in DayTimeProcedures
				$this->DayTimeProcedures->updateAll(
					['DayTimeProcedures.is_exception' => "'1'"],
					['DayTimeProcedures.day_time_seq' => $shiftId]
				);
				echo json_encode(array('status' => 'success', 'message' => 'Exception saved successfully.'));
			} else {
				echo json_encode(array('status' => 'error', 'message' => 'Could not save exception.'));
			}
		} else {
			echo json_encode(array('status' => 'error', 'message' => 'Invalid request method.'));
		}
	}


	public function getExceptions()
	{
		$this->autoRender = false;
		$this->ShiftException->useDbConfig = $this->Session->read('ds');

		if ($this->request->is('ajax')) {
			$shiftId = $this->request->query('shift_id');
			$showInactive = $this->request->query('show_inactive'); // NEW

			$conditions = array(
				'ShiftException.shift_id' => $shiftId,
				'ShiftException.status'   => ($showInactive == '1') ? 2 : 1
			);

			$exceptions = $this->ShiftException->find('all', array(
				'conditions' => $conditions,
				'order'      => array('ShiftException.shift_exceptions_pkey DESC')
			));

			echo json_encode($exceptions);
		}
	}

	//end 14-05-2026


	public function deleteException()
	{
		$this->autoRender = false;
		$this->ShiftException->useDbConfig = $this->Session->read('ds');

		$id = $this->request->data('id');

		if ($id) {

			$record = $this->ShiftException->find('first', [
				'conditions' => [
					'ShiftException.shift_exceptions_pkey' => $id,
					'ShiftException.status' => 1
				]
			]);

			if (!empty($record)) {

				$userId = $this->Session->read('login_user_id');
				$currentDate = date('Y-m-d H:i:s');

				$this->ShiftException->query("
                UPDATE shift_exceptions 
                SET 
                    status = 0,
                    modified_by = '$userId',
                    modification_date = '$currentDate'
                WHERE shift_exceptions_pkey = '$id'
            ");

				echo json_encode(['success' => true]);
			} else {

				echo json_encode([
					'success' => false,
					'message' => 'Record not found'
				]);
			}
		} else {

			echo json_encode([
				'success' => false,
				'message' => 'Invalid ID'
			]);
		}
	}



	public function getDayStatus()
	{
		$this->autoRender = false;
		$this->DayTimeProcedures->useDbConfig = $this->Session->read('ds');

		$day = $this->request->query('day');     // e.g. 'Monday'
		$shift_id = $this->request->query('shift'); // e.g. 3

		if (!empty($day) && !empty($shift_id)) {
			$record = $this->DayTimeProcedures->find('first', [
				'fields' => [$day],
				'conditions' => [
					'active' => '1',
					'day_time_seq' => $shift_id
				]
			]);

			if (!empty($record)) {
				$value = $record['DayTimeProcedures'][$day]; // Y or N
				echo json_encode(['status' => $value]);
			} else {
				echo json_encode(['status' => '']);
			}
		} else {
			echo json_encode(['status' => '']);
		}
	}

	//end

	// edited by athira on 05-05-2026
	public function toggleExceptionStatus()
	{
		$this->autoRender = false;
		$this->ShiftException->useDbConfig = $this->Session->read('ds');
		$shiftId = $this->request->data('shift_id');
		$isChecked = $this->request->data('is_checked'); // 1 or 0
		$userId = $this->Session->read('login_user_id');

		if ($shiftId !== null) {
			$conditions = ['ShiftException.shift_id' => $shiftId];
			if ($shiftId == 0) {
				$conditions['ShiftException.created_by'] = $userId;
			}

			if ($isChecked == '1') {
				// Checked: Restore status 2 -> 1
				$this->ShiftException->updateAll(
					['ShiftException.status' => 1],
					array_merge($conditions, ['ShiftException.status' => 2])
				);
			} else {
				// Unchecked: Move status 1 -> 2
				$this->ShiftException->updateAll(
					['ShiftException.status' => 2],
					array_merge($conditions, ['ShiftException.status' => 1])
				);
			}
			echo json_encode(['success' => true]);
		} else {
			echo json_encode(['success' => false, 'message' => 'Invalid Shift ID']);
		}
	}
	// ended by athira on 05-05-2026

}
