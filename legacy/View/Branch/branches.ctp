<script>
var	table;
$(document).ready( function(){
	
	table = $('#tbl_units').dataTable( {
			"processing": true,
			"serverSide": true,
			"ajax": livesite+"Branch/listunits",
			 "columnDefs": [
            {
                // The `data` parameter refers to the data for the cell (defined by the
                // `data` option, which defaults to the column being worked with, in
                // this case `data: 0`.
                "render": function ( data, type, row ) {
                    return '<a href="#" onclick="createBranch('+row[0]+')">'+data+'</a>' ;
                },
                "targets": 1
            },
           
        ]
		});
	
});
function createBranch(id)
{
	$("#deptModel").load(livesite+"Branch/newbranch?id="+id).dialog({ width: 500,height:400 });
}
function cancelBrachSave(reload)
{
	
	//table.ajax.url( livesite+"dashboard/listunits").load();
	if(reload == 0){
		
		$("#deptModel").dialog( "close");
		$("#deptModel").html("")
	}else
	{
		
	
		$("#deptModel").dialog( "close");
			$("#deptModel").html("")
		$('#tbl_units').DataTable().ajax.reload();
	}
}
</script>
<div class="toolbar"><a href="#" onclick="createBranch(0)" >New</a></div>
<table id="tbl_units" class="display" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th></th>
								<th>Name</th>
								<th>Address</th>
								<th>City</th>
								<th>State</th>
								<th>Pincode</th>
								<th>Status</th>
							</tr>
						</thead>
</table>

<div id="deptModel"></div>