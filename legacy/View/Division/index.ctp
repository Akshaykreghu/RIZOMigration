<script>
var	table;
$(document).ready( function(){
	
	table = $('#tbl_div').dataTable( {
			"processing": true,
			"serverSide": true,
			"ajax": livesite+"Division/listdivision",
			 "columnDefs": [
            {
                // The `data` parameter refers to the data for the cell (defined by the
                // `data` option, which defaults to the column being worked with, in
                // this case `data: 0`.
                "render": function ( data, type, row ) {
                    return '<a href="#" onclick="createDivision('+row[0]+')">'+data+'</a>' ;
                },
                "targets": 1
            },
           
        ]
		});
	
});
function createDivision(id)
{
	$("#divModel").load(livesite+"Division/newdivision?id="+id).dialog({ width: 500,height:400 });
}
function cancelDivisionSave(reload)
{
	
	//table.ajax.url( livesite+"dashboard/listunits").load();
	if(reload == 0){
		
		$("#divModel").dialog( "close");
		$("#divModel").html("")
	}else
	{
		
	
		$("#divModel").dialog( "close");
			$("#divModel").html("")
		$('#tbl_div').DataTable().ajax.reload();
	}
}
</script>
<div class="toolbar"><a href="#" onclick="createDivision(0)" >New</a></div>
<table id="tbl_div" class="display" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th></th>
								<th>Division Code</th>
								<th>Division Name</th>
							
							</tr>
						</thead>
</table>

<div id="divModel"></div>