<script>
var	table;
$(document).ready( function(){
	
	table = $('#tbl_sect').dataTable( {
			"processing": true,
			"serverSide": true,
			"ajax": livesite+"Section/listsection",
			 "columnDefs": [
            {
                // The `data` parameter refers to the data for the cell (defined by the
                // `data` option, which defaults to the column being worked with, in
                // this case `data: 0`.
                "render": function ( data, type, row ) {
                    return '<a href="#" onclick="createSection('+row[0]+')">'+data+'</a>' ;
                },
                "targets": 1
            },
           
        ]
		});
	
});
function createSection(id)
{
	$("#sectModel").load(livesite+"Section/newsection?id="+id).dialog({ width: 500,height:400 });
}
function cancelSectionSave(reload)
{
	
	//table.ajax.url( livesite+"dashboard/listunits").load();
	if(reload == 0){
		
		$("#sectModel").dialog( "close");
		$("#sectModel").html("")
	}else
	{
		
	
		$("#sectModel").dialog( "close");
			$("#sectModel").html("")
		$('#tbl_sect').DataTable().ajax.reload();
	}
}
</script>
<div class="toolbar"><a href="#" onclick="createSection(0)" >New</a></div>
<table id="tbl_sect" class="display" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th></th>
								<th>Section Code</th>
								<th>Section Name</th>
							
							</tr>
						</thead>
</table>

<div id="sectModel"></div> 