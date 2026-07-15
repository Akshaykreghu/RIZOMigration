<?php
class DataTablesManagementComponent extends Component {
    var $components = array('Session');
	var $controller;
	var $primary = array();
	
	function initialize(Controller $controller){
	    $this->controller = $controller;
	}
	//called after Controller::beforeFilter()
	function startup(Controller $controller) {
	}
	//called after Controller::beforeRender()
	function beforeRender(Controller $controller) {
	}
	//called after Controller::render()
	function shutdown(Controller $controller) {
	}
	//called before Controller::redirect()
	function beforeRedirect(Controller $controller, $url, $status=null, $exit=true) {
	}
	function redirectSomewhere($value) {
	// utilizing a controller method
	    $this->controller->redirect($value);
	}
	
	public function generateSQLConditionsForListing($arr_columns,$var_getRequest){
		//debug($var_getRequest); die();
		$sqlConditions	=	array();
		/*
         * Paging
        */
        $sLimit = "";
       $sOffset= "";
        if ( isset( $var_getRequest['iDisplayStart'] ) && $var_getRequest['iDisplayLength'] != '-1' )
        {
             $sOffset = " ".intval( $var_getRequest['iDisplayStart'] );
            $sLimit=" ".intval( $var_getRequest['iDisplayLength'] );   
        }
        //debug($sLimit);
         
        /*
         * Ordering
        */
        $sOrder = "";
        if ( isset( $var_getRequest['iSortCol_0'] ) )
        {
            $sOrder = "  ";
            for ( $i=0 ; $i<intval( $var_getRequest['iSortingCols'] ) ; $i++ )
            {
                if ( $var_getRequest[ 'bSortable_'.intval($var_getRequest['iSortCol_'.$i]) ] == "true" )
                {
                    $sOrder .= "".$arr_columns[ intval( $var_getRequest['iSortCol_'.$i] ) ]." ".
                        ($var_getRequest['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
                }
            }
         
            $sOrder = substr_replace( $sOrder, "", -2 );
            if ( $sOrder == "" )
            {
                $sOrder = "";
            }
        }
         
         
        /*
         * Filtering
        * NOTE this does not match the built-in DataTables filtering which does it
        * word by word on any field. It's possible to do here, but concerned about efficiency
        * on very large tables, and MySQL's regex functionality is very limited
        */
        $sWhere = "";
        if ( isset($var_getRequest['sSearch']) && $var_getRequest['sSearch'] != "" )
        {
            $sWhere = " (";
            for ( $i=0 ; $i<count($arr_columns) ; $i++ )
            {
                $sWhere .= "`".$arr_columns[$i]."` LIKE '%". addslashes($var_getRequest['sSearch']) ."%' OR ";
            }
            $sWhere = substr_replace( $sWhere, "", -3 );
            $sWhere .= ')';
        }
         
        /* Individual column filtering */
        for ( $i=0 ; $i<count($arr_columns) ; $i++ )
        {
            if ( isset($var_getRequest['bSearchable_'.$i]) && $var_getRequest['bSearchable_'.$i] == "true" && $var_getRequest['sSearch_'.$i] != '' )
            {
                if ( $sWhere == "" )
                {
                    $sWhere = " ";
                }
                else
                {
                    $sWhere .= " AND ";
                }
                $sWhere .= "`".$arr_columns[$i]."` LIKE '%".addslashes($var_getRequest['sSearch_'.$i])."%' ";
            }
        }
        
         $sqlConditions['sLimit']=$sLimit;
        $sqlConditions['sOffset']=$sOffset;
        $sqlConditions['sOrder']=$sOrder;
        $sqlConditions['sWhere']=$sWhere;
        return $sqlConditions;
	}
}