<?php

/**
 * This component provides compatibility between the dataTables jQuery plugin and CakePHP 2
 * @author chris
 * @package DataTableComponent
 * @link http://www.datatables.net/release-datatables/examples/server_side/server_side.html parts of code borrowed from dataTables example
 * @since version 1.1.1
Copyright (c) 2013 Chris Nizzardini

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
 */
class DataTableComponent extends Component{
    
    private $model;
    private $controller;
    private $times = array();
    public $conditionsByValidate = 0;
    public $emptyElements = 0;
    public $fields = array();
    public $mDataProp = false;
    
    public function __construct(){
        
    }
    
    public function initialize(Controller $controller){
        $this->controller = $controller;
        $modelName = $this->controller->modelClass;
        $this->model = $this->controller->{$modelName};
    }
    
/**
 * returns dataTables compatible array - just json_encode the resulting aray
 * @param object $controller optional
 * @param object $model optional
 * @return array
 */
    public function getData($model=null,$columns){
        
        /**
         * it is no longer necessary to pass in a controller or model
         * this is handled in the initialize method
         * $controller is disregarded.
         * $model is only necessary if you are using a model from a different controller such as if you are in 
         * a CustomerController but your method is displaying data from an OrdersModel.
         */
        
        $this->setTimes('Pre','start','Preproccessing of conditions');
        
        if($model != null){  
            if(is_string($model)){
                $this->model = $this->controller->{$model};
            }
            else{
                $this->model = $model;
                unset($model);
            }
        }

        $conditions = isset($this->controller->datatable['conditions']) ? $this->controller->datatable['conditions'] : null;

        $isFiltered = false;
        
        if( !empty($conditions) ){
            $isFiltered = true;
        }
        
        if(isset($this->controller->request->query)){
            $httpGet = $this->controller->request->query;
        }
        
        // check for ORDER BY in GET request
        if(isset($httpGet) && isset($httpGet['order']) && count($httpGet['order']) ){
            $orderBy = $this->getOrderByStatements($columns);
            if(!empty($orderBy)){
                $this->controller->datatable = array_merge($this->controller->datatable, array('order'=>$orderBy));
            }
        }
        
        // check for WHERE statement in GET request
        if(isset($httpGet) && !empty($httpGet['search'])){
            $conditions = $this->getWhereConditions($columns);

                $this->controller->datatable = array_merge_recursive($this->controller->datatable, array('conditions'=>array('AND'=>$conditions)));
          
            $isFiltered = true;
        }
   //     debug($this->controller->datatable);
        // @todo avoid multiple queries for finding count, maybe look into "SQL CALC FOUND ROWS"
        // get full count
        $this->model->recursive = -1;
        $total = $this->model->find('count');
        $parameters = $this->controller->datatable;
        
        if($isFiltered){
        	/*
			 * unset for fetching count
			 * by santhosh
			 */
        	$params	=	$parameters;
			unset($params['fields']);
            $filteredTotal = $this->model->find('count',$params);
        }
        
        // set sql limits
        if( isset($this->controller->request->query['start']) && $this->controller->request->query['length'] != '-1' ){
            $start = $this->controller->request->query['start'];
            $length = $this->controller->request->query['length'];
            $parameters['limit'] = $length;
            $parameters['offset'] = $start;
        }
        
	//	debug($parameters);
		
        // execute sql select
        $dbdata = $this->model->find('all', $parameters);
		
		
	//	debug($dbdata);
		$data  = array();
		
		/*$result_arr = array();
		foreach ($dbdata as $sub_arr) $result_arr = array_merge($result_arr, $sub_arr);
		$result_arr = array_unique($result_arr);*/
		//var_dump($dbdata);
		$i = 0;
		foreach ($dbdata as $key => $value) {
			
			
			
		//$data = 	array_merge($data,array_values($value));
			
			foreach ($value as $key2 => $value2){
							
								foreach ($value2 as $key3 => $value3){
										
									$data[$i][$key3] = $value3;
									
								}
							
							
						}
			$i++; 
			
		}
		
		//debug($data);
        // dataTables compatible array
        $response = array(
            'draw' => isset($this->controller->request->query['draw']) ? intval($this->controller->request->query['draw']) : 1,
            'recordsTotal' => $total,
            'recordsFiltered' => $isFiltered === true ? $filteredTotal : $total,
            'data' => $data
        );
        
        // return data
       
        return $response;
    }
    
/**
 * returns sql order by string after converting dataTables GET request into Cake style order by
 * @param void
 * @return string
 */
    private function getOrderByStatements($columns){
      
      
        
      
		
			$orderBy = array();
			if ( isset($this->controller->request->query['order']) && count($this->controller->request->query['order']) ) {
		
			$dtColumns = $this->pluck( $columns, 'dt' );

			for ( $i=0, $ien=count($this->controller->request->query['order']) ; $i<$ien ; $i++ ) {
				// Convert the column index into the column data property
				$columnIdx = intval($this->controller->request->query['order'][$i]['column']);
				$requestColumn = $this->controller->request->query['columns'][$columnIdx];

				$columnIdx = array_search( $requestColumn['data'], $dtColumns );
				$column = $columns[ $columnIdx ];

				if ( $requestColumn['orderable'] == 'true' ) {
					$dir = $this->controller->request->query['order'][$i]['dir'] === 'asc' ?
						'ASC' :
						'DESC';

					$orderBy[] = '`'.$column['db'].'` '.$dir;
				}
			}

			
		}
  return $orderBy;
    }

/**
 * returns sql conditions array after converting dataTables GET request into Cake style conditions
 * will only search on fields with bSearchable set to true (which is the default value for bSearchable)
 * @param void
 * @return array
 */
    private function getWhereConditions($columns){
        
      
		
		
		
		//----
		
		   $conditions = array();
        
		
		$globalSearch = array();
		$columnSearch = array();
		$dtColumns = $this->pluck( $columns, 'dt' );

		if ( isset($this->controller->request->query['search']) && $this->controller->request->query['search']['value'] != '' ) {
			$str = $this->controller->request->query['search']['value'];

			for ( $i=0, $ien=count($this->controller->request->query['columns']) ; $i<$ien ; $i++ ) {
				$requestColumn = $this->controller->request->query['columns'][$i];
				$columnIdx = array_search( $requestColumn['data'], $dtColumns );
				$column = $columns[ $columnIdx ];

				if ( $requestColumn['searchable'] == 'true' ) {
					$binding =  '%'.$str.'%';
					$globalSearch[] = array(
                                $column['db']." LIKE " => $binding
                            ); 
				}
			}
		}

		// Individual column filtering
		for ( $i=0, $ien=count($this->controller->request->query['columns']) ; $i<$ien ; $i++ ) {
			$requestColumn = $this->controller->request->query['columns'][$i];
			$columnIdx = array_search( $requestColumn['data'], $dtColumns );
			$column = $columns[ $columnIdx ];

			$str = $requestColumn['search']['value'];

			if ( $requestColumn['searchable'] == 'true' &&
			 $str != '' ) {
					$binding =  '%'.$str.'%';
				    $columnSearch[] = array(
                                $column['db']." LIKE " => $binding
                            ); 
			}
		}

		
		if ( count( $globalSearch ) ) {
			
			 $conditions['OR'] = $globalSearch;
		}

		if ( count( $columnSearch ) ) {
			
			 $conditions = array_merge_recursive($conditions,$columnSearch);
			
		}

		

		return $conditions;
    }
    
/**
 * looks through the models validate array to determine to create conditions based on datatype, returns condition array. 
 * to enable this set $this->DataTable->conditionsByValidate = 1.
 * @param string $field
 * @return array
 */    
    private function conditionByDataType($field){
        foreach($this->model->validate[$field] as $rule => $j){
            switch($rule){
                case 'boolean':
                case 'numeric':
                case 'naturalNumber':
                    $condition = array($field => $this->controller->request->query['sSearch']);
                    break;
            }
        }
        return $condition;
    }
    
/**
 * finds data recursively and returns a flattened key => value pair array 
 * second parameter is not required and only used in callbacks to self
 * @param array $data
 * @param string $key
 * @return array
 */
    private function getDataRecursively($data,$key=null){
        $fields = array();

        // note: the chr() function is used to produce the arrays index to make sorting via ksort() easier.
        
        // loop through cake query result
        foreach($data as $x => $i){
            // go recursive
            if(is_array($i)){
                //if(!array_key_exists($x,$this->model->hasMany)){
                    $fields = array_merge($fields,$this->getDataRecursively($i,$x));
                //}
            }
            // check if component was given fields explicitely
            else if( !empty($this->fields) ){
                if(in_array("$key.$x", $this->fields)){
                    $index = array_search("$key.$x",$this->fields);
                    //echo "$key.$x = $index = $i \n";
                    // index needs to be a string so array_merge handles it properly
                    $fields[chr($index)] = "$i";
                }
                else{
                    //echo "$key.$x (NOT FOUND) \n";
                }
            }
            // dimension is not multi-dimensionable so add to $fields
            else if(isset($this->controller->datatable['fields'])){
                if(in_array("$key.$x", $this->controller->datatable['fields'])){
                    $index = array_search("$key.$x", $this->controller->datatable['fields']);
                    // index needs to be a string so array_merge handles it properly
                    $fields[chr($index)] = "$i";
                }
            }
            // will try to include all results but this will likely not work for you
            else{
                $fields["$key.$x "] = "$i";
            }
        }
        ksort($fields);
        //var_dump($fields);
        return $fields;
    }
    
/**
 * setTimes method - adds to timer of settings[timed] = true
 * @param string $key
 * @param string $action (start or stop)
 * @param string $desc (optional)
 * @param string $line
 */    
    private function setTimes($key,$action,$desc=''){
        if(isset($this->settings) && isset($this->settings['timer']) && $this->settings['timer'] == true){
            $this->times[$key][$action] = array(
                'action' => $action,
                'time' => microtime(true),
                'description' => $desc
            );
        }
    }
    
/**
 * getTimes method - returns an array of the components benchmarks
 * @return type
 */    
    public function getTimes(){
        $times = array();
        $componentStart = 0;
        foreach($this->times as $x => $i){
            $start = $end = $desc = $startLine = $endLine = 0;
            foreach($i as $j){
                if($j['action'] == 'start'){
                    $start = $j['time'];
                    $desc = $j['description'];
                    if($componentStart == 0){
                        $componentStart = $start;
                    }
                }
                else if($j['action'] == 'stop'){
                    $end = $j['time'];
                }
                if($start > 0 && $end > 0){
                    $times[$x] = array(
                        'description' => $desc,
                        'time' => round(($end - $start),4),
                    );
                }
            }
        }
        
        if(isset($this->settings) && isset($this->settings['timer']) && $this->settings['timer'] == true){
            $times['TOTAL'] = array(
                'time' => round(($end - $componentStart),4)
            );
        }
        
        return $times;
    }




function pluck ( $a, $prop )
	{
		$out = array();

		for ( $i=0, $len=count($a) ; $i<$len ; $i++ ) {
			$out[] = $a[$i][$prop];
		}

		return $out;
	}
}