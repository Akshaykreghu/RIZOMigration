<script>
var	table;
$(document).ready( function(){
	
	table = $('#tbl_dept').dataTable( {
			"processing": true,
			"serverSide": true,
			"ajax": livesite+"Department/listdepartments",
			 "columnDefs": [
            {
                // The `data` parameter refers to the data for the cell (defined by the
                // `data` option, which defaults to the column being worked with, in
                // this case `data: 0`.
                "render": function ( data, type, row ) {
                    return '<a href="#" onclick="createDepartment('+row[0]+')">'+data+'</a>' ;
                },
                "targets": 1
            },
           
        ]
		});
	
});
function createDepartment(id)
{
	$("#deptModel").load(livesite+"Department/newdepartment?id="+id).dialog({ width: 500,height:400 });
}
function cancelDepartmentSave(reload)
{
	
	//table.ajax.url( livesite+"dashboard/listunits").load();
	if(reload == 0){
		
		$("#deptModel").dialog( "close");
		$("#deptModel").html("")
	}else
	{
		
	
		$("#deptModel").dialog( "close");
			$("#deptModel").html("")
		$('#tbl_dept').DataTable().ajax.reload();
	}
}
</script>
<div class="toolbar"><a href="#" onclick="createDepartment(0)" >New</a></div>
<table id="tbl_dept" class="display" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th></th>
								<th>Department Code</th>
								<th>Department Name</th>
							
							</tr>
						</thead>
</table>

<div id="deptModel"></div>