<style>
	#shiftpolicygrouptable_wrapper .DTTT.btn-group ,#shiftpolicytable_wrapper .DTTT.btn-group{
		padding-left: 5px;
	}
	/* edited by bindu 12-12-25 */
	.heading {
		display: flex;
		flex-direction: row;
		align-items: end;
		justify-content: space-between;
	}

    .home {
        background-color: #ffffffff;
        border-radius: 50px;
        padding: 2px 15px;
        color: #1e516e !important;
        /* margin-right: 15px; */
        color: white;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        border: #1e516e 1px solid;
    }
/* edited by bindu 12-12-25 end*/
</style>
<!--<section class="content-header">
	<h1 style="text-align:left; font-size: 3em;"> Shift Policy </h1>
</section>-->
<!-- /* edited by bindu 12-12-25 */ -->
<section class="content-header heading">
	<h1 class="text-primary-18">Shift Timings & Rules</h1>
	
	  <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>

</section>

<hr style="margin-top: 8px; margin-bottom: -2px; margin-right: 15px; margin-left: 15px;">
<!-- /* edited by bindu 12-12-25 end*/ -->
<section class="content">
	<div class="row">
		<div class="col-md-3">
                    <ul id="shiftpolicygrouptable" title="Shift Policy Groups" lines="true" style="width:100%; min-height:200px; height:auto">
                       </ul>
<!--			<div class="box box-solid">
				<div class="box-header with-border" style="border-top: 2px solid #0081c2 ">
					<h3 class="box-title">Shift Policy Group</h3>
					<div class="box-tools"></div>
				</div>
				<div class="box-body no-padding">
					<table id="shiftpolicygrouptable" class="table table-hover table-striped" cellspacing="0" width="100%">
						
					</table>
				</div> /.box-body 
			</div>-->

		</div><!-- /.col -->
		<div class="col-md-9">
			<div class="box box-primary" style="box-shadow:11px 11px 12px 12px rgba(0,0,0,0.1);">
				<!-- /.box-header -->
				<div class="box-body no-padding" id="shiftContainer">
					
					<!--
					<div class="table-responsive mailbox-messages">

					</div>-->
					<!-- /.mail-box-messages -->
				</div><!-- /.box-body -->

			</div><!-- /. box -->
		</div><!-- /.col -->
	</div><!-- /.row -->
</section><!-- /.content -->

<script>
    function loader(loader){
        $('#'+loader).html('<li class="fa fa-spinner fa-spin" style="margin: 50%;font-size: -webkit-xxx-large;"></li>');           
    }
    function stoploader(loader){
        $('#'+loader).html('');           
    }
    function pauseloader(cont){
        $('#'+cont).find('li').removeClass('fa-spin');
    }
	jQuery(document).ready(function() {
		
	loader('shiftContainer');	
     
		
     $("#shiftContainer").load(livesite+"DayTimeProcedure/form")
     
     

$('#shiftpolicygrouptable').datagrid({
				url:livesite+"DayTimeProcedure/listpolicies",
				pagination:true,
				singleSelect:true,
                                rownumbers: true,
                              
				onSelect:function(index,row){
								
				if (row){
                                    loader('shiftContainer');
                                $("#shiftContainer").load(livesite+'DayTimeProcedure/lists?id=' + row.day_time_seq)


				}
				},
				toolbar: [{
				text:'New',
				iconCls:'icon-add',
                                
				handler: function(){
                                loader('shiftContainer');
				$("#shiftContainer").load(livesite+"DayTimeProcedure/form")
                                reloadTable('shiftpolicygrouptable');
				}
				},'-',{
				iconCls: 'icon-remove',
				text:'Remove',
				handler: function(){
			
				var row = $('#shiftpolicygrouptable').datagrid('getSelected');
			
				if (row){
			
				if (confirm("Are you sure want to delete ")) {
				var str_ids = row.day_time_seq;
			
				$.ajax({
                                url : livesite+"DayTimeProcedure/delete",
				data : {
				ids : str_ids
				},
				success : function(response) {
                                var response =  $.parseJSON(response);
                                    if(response.success == true){
                                                    $.notify(response.msg,{
                                                                    type: 'success',
                                                                    allow_dismiss: true

                                                            });
                                                          $("#shiftContainer").load(livesite+"DayTimeProcedure/form")  
                                    }
                                    else
                                        {
                                            $.notify(response.msg,{
                                                                    type: 'danger',
                                                                    allow_dismiss: true

                                                            });
                                        }
				reloadTable('shiftpolicygrouptable')
				},
                                error: function(response)  {
                                   
                                        $.notify($.parseJSON(response).msg,{
                                        type: 'danger',
                                        allow_dismiss: false
                                    });
                                    } 
				});
			
				}
			
				}else{
                                    alert("Please select a row");
                                }
			
				}
				}],
				fitColumns:true,
				pageList:[2,5,10,50,100],
				columns:[[
				{field:'day_time_desc',title:'Name',width:"100%",sortable:true},
				]],
                             onLoadSuccess: function () {
                                              $("#shiftContainer").load(livesite+"DayTimeProcedure/form")  
                                        }
                            
				});
				


	}); 
	/* edited by bindu 19-02-26 */
   $(".home").on("click", function () {

    $("#container").isLoading({
        text: "Loading",
        position: "overlay",
    });

    let url = "";
    var userGroup = <?php echo json_encode($this->Session->read('user_group')); ?>

    if (userGroup == "1") {
        url = livesite + "CompanySetup/index";
    } 
    else if (userGroup == "2") {
        url = livesite + "EmployeeMenu/addon";
    }

    $("#container").load(url, function () {
        isDashboardShown = false;
    });

});

	/* edited by bindu 19-02-26 */
</script>