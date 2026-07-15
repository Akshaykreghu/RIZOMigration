<style>
    table.dataTable{width:100%;margin:0 auto;clear:both;border-collapse:separate;border-spacing:0}
    table.dataTable thead th,table.dataTable tfoot th{font-weight:bold}table.dataTable thead th,table.dataTable thead td{padding:10px 
                           18px;border-bottom:1px solid #111}table.dataTable thead th:active,table.dataTable
    thead td:active{outline:none}table.dataTable tfoot th,table.dataTable tfoot td{padding:10px 18px 6px 18px;border-top:1px solid #111}
    table.dataTable thead .sorting,table.dataTable thead .sorting_asc,table.dataTable thead .sorting_desc{cursor:pointer;*cursor:hand}
    table.dataTable thead .sorting,table.dataTable thead .sorting_asc,table.dataTable thead .sorting_desc,
    table.dataTable thead .sorting_asc_disabled,table.dataTable thead 
    .sorting_desc_disabled{background-repeat:no-repeat;background-position:center right}
    table.dataTable thead .sorting{background-image:url("../images/sort_both.png")}
    table.dataTable thead .sorting_asc{background-image:url("../images/sort_asc.png")}
    table.dataTable thead .sorting_desc{background-image:url("../images/sort_desc.png")}
    table.dataTable thead .sorting_asc_disabled{background-image:url("../images/sort_asc_disabled.png")}
    table.dataTable thead .sorting_desc_disabled{background-image:url("../images/sort_desc_disabled.png")}
    table.dataTable tbody tr{background-color:#ffffff}table.dataTable tbody tr.selected{background-color:#B0BED9}
    table.dataTable tbody th,table.dataTable tbody td{padding:8px 10px}table.dataTable.row-border tbody th,
    table.dataTable.row-border tbody td,table.dataTable.display tbody th,table.dataTable.display tbody 
    td{border-top:1px solid #ddd}table.dataTable.row-border tbody tr:first-child th,table.dataTable.row-border 
    tbody tr:first-child td,table.dataTable.display tbody tr:first-child th,table.dataTable.display tbody
    tr:first-child td{border-top:none}table.dataTable.cell-border tbody th,table.dataTable.cell-border tbody 
    td{border-top:1px solid #ddd;border-right:1px solid #ddd}table.dataTable.cell-border tbody tr 
    th:first-child,table.dataTable.cell-border tbody tr td:first-child{border-left:1px solid #ddd}
    table.dataTable.cell-border tbody tr:first-child th,table.dataTable.cell-border tbody
    tr:first-child td{border-top:none}table.dataTable.stripe tbody tr.odd,table.dataTable.display 
    tbody tr.odd{background-color:#f9f9f9}table.dataTable.stripe tbody 
    tr.odd.selected,table.dataTable.display tbody tr.odd.selected{background-color:#acbad4}
    table.dataTable.hover tbody tr:hover,table.dataTable.display tbody tr:hover{background-color:#f6f6f6}table.dataTable.hover tbody 
    tr:hover.selected,table.dataTable.display tbody 
    tr:hover.selected{background-color:#aab7d1}
    table.dataTable.order-column tbody tr>.sorting_1,table.dataTable.order-column tbody tr>.sorting_2,table.dataTable.order-column 
    tbody tr>.sorting_3,table.dataTable.display tbody tr>.sorting_1,table.dataTable.display tbody tr>.sorting_2,table.dataTable.display 
    tbody tr>.sorting_3{background-color:#fafafa}table.dataTable.order-column tbody tr.selected>.sorting_1,table.dataTable.order-column 
    tbody tr.selected>.sorting_2,table.dataTable.order-column tbody tr.selected>.sorting_3,table.dataTable.display 
    tbody tr.selected>.sorting_1,table.dataTable.display tbody tr.selected>.sorting_2,table.dataTable.display tbody 
    tr.selected>.sorting_3{background-color:#acbad5}table.dataTable.display tbody tr.odd>.sorting_1,table.dataTable.order-column.stripe 
    tbody tr.odd>.sorting_1{background-color:#f1f1f1}table.dataTable.display tbody tr.odd>.sorting_2,table.dataTable.order-column.stripe 
    tbody tr.odd>.sorting_2{background-color:#f3f3f3}table.dataTable.display tbody tr.odd>.sorting_3,table.dataTable.order-column.stripe 
    tbody tr.odd>.sorting_3{background-color:whitesmoke}table.dataTable.display tbody tr.odd.selected>.sorting_1,
    table.dataTable.order-column.stripe tbody tr.odd.selected>.sorting_1{background-color:#a6b4cd}table.dataTable.display 
    tbody tr.odd.selected>.sorting_2,table.dataTable.order-column.stripe tbody tr.odd.selected>.sorting_2{background-color:#a8b5cf}
    table.dataTable.display tbody tr.odd.selected>.sorting_3,table.dataTable.order-column.stripe 
    tbody tr.odd.selected>.sorting_3{background-color:#a9b7d1}table.dataTable.display 
    tbody tr.even>.sorting_1,table.dataTable.order-column.stripe tbody tr.even>.sorting_1{background-color:#fafafa}table.dataTable.display 
    tbody tr.even>.sorting_2,table.dataTable.order-column.stripe tbody tr.even>.sorting_2{background-color:#fcfcfc}table.dataTable.display 
    tbody tr.even>.sorting_3,table.dataTable.order-column.stripe tbody tr.even>.sorting_3{background-color:#fefefe}table.dataTable.display 
    tbody tr.even.selected>.sorting_1,table.dataTable.order-column.stripe tbody tr.even.selected>.sorting_1{background-color:#acbad5}
    table.dataTable.display tbody tr.even.selected>.sorting_2,table.dataTable.order-column.stripe tbody 
    tr.even.selected>.sorting_2{background-color:#aebcd6}table.dataTable.display tbody tr.even.selected>.sorting_3,
    table.dataTable.order-column.stripe tbody tr.even.selected>.sorting_3{background-color:#afbdd8}table.dataTable.display 
    tbody tr:hover>.sorting_1,table.dataTable.order-column.hover tbody tr:hover>.sorting_1{background-color:#eaeaea}
    table.dataTable.display tbody tr:hover>.sorting_2,table.dataTable.order-column.hover tbody tr:hover>.sorting_2{background-color:#ececec}
    table.dataTable.display tbody tr:hover>.sorting_3,table.dataTable.order-column.hover tbody tr:hover>.sorting_3{background-color:#efefef}
    table.dataTable.display tbody tr:hover.selected>.sorting_1,table.dataTable.order-column.hover tbody tr:hover.selected>.sorting_1
    {background-color:#a2aec7}table.dataTable.display tbody tr:hover.selected>.sorting_2,table.dataTable.order-column.hover tbody 
    tr:hover.selected>.sorting_2{background-color:#a3b0c9}table.dataTable.display tbody
    tr:hover.selected>.sorting_3,table.dataTable.order-column.hover tbody tr:hover.selected>.sorting_3{background-color:#a5b2cb}
    table.dataTable.no-footer{border-bottom:1px solid #111}table.dataTable.nowrap th,table.dataTable.nowrap td{white-space:nowrap}
    table.dataTable.compact thead th,table.dataTable.compact thead td{padding:4px 17px 4px 4px}
    table.dataTable.compact tfoot th,table.dataTable.compact tfoot td{padding:4px}table.dataTable.compact 
    tbody th,table.dataTable.compact tbody td{padding:4px}table.dataTable th.dt-left,table.dataTable td.dt-left{text-align:left}
    table.dataTable th.dt-center,table.dataTable td.dt-center,table.dataTable td.dataTables_empty{text-align:center}table.dataTable 
   </style>
    
    <script type="text/javascript">
        function changeReportType(obj)
  {
      
        var reporttype = $(obj).val();
        //$('#div-reportcriterias').load(livesite+'salaryReports/changereporttype/'+reporttype);
        //alert(reporttype);
        $('#ss').val(reporttype);
  }
  
function showdetails()
{
var empPkey = $('#empsetuptaxation #emp_pkey').val();
 var url = livesite+'Employee/addqualification/'+empPkey;
        
        var container = $("#modalDetailForm #modaldetails-content")
container.load(url, function() {
            $("#modalDetailForm").modal('show');
        });
}


function showhistory()
{
var empPkey = $('#empsetuptaxation #emp_pkey').val();
 var url = livesite+'Employee/history/'+empPkey;
        
        var container = $("#modalDetailForm #modaldetails-content")
container.load(url, function() {
            $("#modalDetailForm").modal('show');
        });
}
    $(document).ready(function() {


 var selected = [];
         
         $('#example tbody').on('click', 'tr', function () {
        var id = table.row(this).data().branch_code;
        var index = $.inArray(id, selected);
 
        if ( index === -1 ) {
            selected.push( id );
        } else {
            selected.splice( index, 1 );
        }
        
        $(this).toggleClass('selected');
    } );
    
  function changeReportType(type)
  {
      var reporttype = $(obj).val();
        var reportname = $("#"+obj.id+" option:selected").text();
        $('#current_reporttype').html(reportname);
        $('#div-reportcriterias').load(livesite+'salaryReports/changereporttype/'+reporttype);
        alert(reporttype);
  }

 var selected1 = [];
         
         $('#example1 tbody').on('click', 'tr', function () {
        var id = table1.row(this).data().desig_code;
        var index = $.inArray(id, selected1);
 
        if ( index === -1 ) {
            selected1.push( id );
        } else {
            selected1.splice( index, 1 );
        }

        $(this).toggleClass('selected');
    } );
 

    var selected2 = [];
         
         $('#example12 tbody').on('click', 'tr', function () {
        var id = table12.row(this).data().emp_pkey;
        var index = $.inArray(id, selected2);
 
        if ( index === -1 ) {
            selected2.push( id );
        } else {
            selected2.splice( index, 1 );
        }

        $(this).toggleClass('selected');
    } );
        
$('#selected').click( function () {
    
    
    if(selected != '' || selected1 != ''  || selected2 != '' )
        {
        
                  
                            
         
        var container = $("#largeModalFormsalary #largeModalForm-contentsalary");
        var reports = $('#ss').val();
        var urd = 'SalaryReport/'+reports;
        if(reports != '')
        {
        var url = livesite+'SalaryReport/'+reports;

  container.load(url,{ branch:selected , Designation: selected1,Employees:selected2 }, function() {
            $("#largeModalFormsalary").modal('show')
        });
    }
    else
    {
        alert("choose any report ");
    }
    
      
        
	
              
}
else
{
alert("Please select any row first");
}
    } );
    

 var table12 = $('#example12').DataTable({
            "paging": false,
            scrollY:        '40vh',
        scrollCollapse: true,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "ajax": livesite + "ConfigReport/Employees",
            "columns": [
            
            { "data": "Name" }
        ]
        });
  
       

 var table1 = $('#example1').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "ajax": livesite + "ConfigReport/Designation",
            "columns": [
            
            { "data": "desig_name" }
        ]
        });
        
        
        
        
 var table = $('#example').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "ajax": livesite + "ConfigReport/Branches",
            "columns": [
            
            { "data": "branch_name" }
        ]
        });
    
        //Ends
    });
  
    
</script>
<section class="content">
    <div class="row">
<div class="col-md-12">
<div class="box box-header">
    <h2>Salary Reports</h2> <p>Control user access to different menus for different level of employees.</p>
       <div class="box-body" id="div-reportcriterias">
                     <div class="col-md-12">
                     <select id="filterby_reporttype" name="filterby_reporttype" class="form-control" onchange="changeReportType(this);" >
                         <option value="">Choose any report type</option>       
                         <?php
                                foreach ($arr_reporttypes as $key => $value) {
                                    echo '<option value="' . $key . '">' . $value . '</option>';
                                }
                                ?>
                            </select>
            </div>
                </div>
  
    
    
</div>
 <div id="load" class="box box-body" style="margin:10px 0">
       <div class="col-md-4">
          <table class="table table-bordered display" id="example">
                            <thead>
                              <tr>
                                
                             <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->
                                 
                                 
                                  
                                 
                                <th>Branches</th>
                                
                              </tr>
                            </thead>
                           
                            <tbody>
                           
                            </tbody>
                            </table>
          </div>
     <div class="col-md-4">
          <table class="table table-bordered display" id="example1">
                            <thead>
                              <tr>
                                
                             <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->
                                 
                                 
                                  
                                  
                                <th>Designations</th>
                                
                              </tr>
                            </thead>
                           
                            <tbody>
                           
                            </tbody>
                            </table>
          </div>
     
     <div class="col-md-4">
          <table class="table table-bordered display" id="example12">
                            <thead>
                              <tr>
                                
                             <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->
                                 
                                 
                                  
                                 
                                  
                                <th>Names</th>
                                
                              </tr>
                            </thead>
                           
                            <tbody>
                           
                            </tbody>
                            </table>
          </div>
     
     
     
    </div> 
    <input type="hidden" id="ss" value="" class="btn btn-primary">
    <input type="button" value="Generate" id="selected" class="btn btn-success pull-right">
</div>
        
    </div>
</section>
<div id="largeModalFormsalary" class="modal fade">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="largeModalForm-contentsalary" style="
    width: 148%;
    margin-left: -213px;
">
              
            </div>
        </div>
    </div>


