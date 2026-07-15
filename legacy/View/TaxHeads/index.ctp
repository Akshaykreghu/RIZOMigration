<style>
	/* Edited by bindu 24-10-2025 */
 
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

    /* End */
</style>
<section class="content-header heading">
      
        <!-- /* edited by bindu 22-08-25 */ -->
    <h1 class="text-primary-18">Tax Heads</h1>
  <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>
  
   
</section>
<hr style="margin-top: 8px;margin-bottom: -2px;margin-right: 15px;margin-left: 15px;">  
<!-- end -->
        <!-- Main content -->
        <section class="content">
 <div class="row">
                                <div class="col-sm-3">
                            <ul id="sheads" title="Tax Types" lines="true" style="width:100%; height:370px; background-color:white;"></ul>

                                </div>
                                <div class="col-sm-4">
                                
                                    <ul id="shitems"  title="Tax Heads" lines="true" style="width:100%; height:370px; background-color:white;"></ul>
                                </div>
                                <div class="col-sm-5">
                                 
                                    <ul id="shitems1"  title="Tax Head Details" lines="true" style="width:100%; height:370px; background-color:white;"></ul>
                                </div>
                            </div>
     	



</section>
<script>
	$(document).ready(function(){
			$('#sheads').datalist({
                            
                            toolbar: [{
				text:'New',
				iconCls:'icon-add',
				handler: function(){
			
				showModalForm(livesite+'TaxHeads/form')
                                        
				}
				},
                                {
				text:'Edit',
				iconCls:'icon-edit',
				handler: function(){
			              // alert('hlo');
					var headrow = $('#sheads').datalist('getSelected');
                                        if(headrow){
                                        showModalForm(livesite+'TaxHeads/form?id=' + headrow.key)
                                    }
                                    else{
                                        alert('please select any row');
                                    }
                                        
				}
				},
                                 {
				text:'Delete',
				iconCls:'icon-remove',
				handler: function(){
			        var headrow = $('#sheads').datalist('getSelected');
                                //alert(headrow.key);
				//showModalForm(livesite+'SalaryHeads/DeleteHead/'+ headrow.key)
                                if(headrow){
                                    if (confirm("Are you sure want to delete ")) {
                                	$.ajax({
								url : livesite+'TaxHeads/Deletetaxtype/'+ headrow.key,
								data : {
								tax_id:headrow.key
								},
							success : function(response) {
					 				$.notify($.parseJSON(response).msg,{
											type: 'success',
											allow_dismiss: true,
                                                                                        
																									
										});
							}
							});     closeModal('sheads');  
                                                        closeModal('shitems1');
                                                    }
                                                    }
                                                    else{
                                                        alert("Please select any row");
                                                    }
                                        
				}
				}
                                
                            ],
		    url: livesite+'TaxHeads/getTaxType',
		  onSelect:function(index,row){ closeModal('shitems1');
								var id = row.key; 
                                                              //alert(id);
								$('#shitems').datalist('load',{
								id:id
								});
				},
		    line:true
		});	
		
		
		$('#shitems').datalist({
			toolbar: [{
				text:'New',
				iconCls:'icon-add',
				handler: function(){
                                    //var rows = $('#shitems').datalist('getChecked');
					var headrow = $('#sheads').datalist('getSelected');
                                        //alert(headrow.key);
                                    if(headrow.key!=""){
				 showModalForm(livesite+'TaxHeads/form_items/' + headrow.key+'/0')}
                                                                              	}
				},
                                  
                {
		    text:'Edit',
            iconCls:'icon-edit',
			handler: function(){
				var rows = $('#shitems').datalist('getSelected');
			        var headrow = $('#sheads').datalist('getSelected');
		                                        //alert(headrow.key);
                                                        if(rows){
                                    if(headrow.key!=""){
				 showModalForm(livesite+'TaxHeads/form_items/' + headrow.key+'/'+rows.key)
			
        }}
    else{
        alert("please select any row");
    }
			
		}},
                {
				text:'Delete',
				iconCls:'icon-remove',
				handler: function(){
			        var headrow = $('#shitems').datalist('getSelected');
                                //alert(headrow.key);
				//showModalForm(livesite+'SalaryHeads/DeleteHead/'+ headrow.key)
                                if(headrow){
                                    if (confirm("Are you sure want to delete ")) {
                                	$.ajax({
								url : livesite+'TaxHeads/Deletetaxhead/'+ headrow.key,
								data : {
								tax_id:headrow.key
								},
							success : function(response) {
					 				$.notify($.parseJSON(response).msg,{
											type: 'success',
											allow_dismiss: true,
                                                                                        
																									
										});
							}
							});     closeModal('sheads');  
                                                        closeModal('shitems'); 
                                                        }
                                                    else{
                                                        alert("Please select any row");
                                                    }
                                        
				}
				}
                            }
	],
		   
		    url: livesite+'TaxHeads/getTaxHead',
		  onSelect:function(index,row){
								var id = row.key; 
                                                              //alert(id);
								$('#shitems1').datalist('load',{
								id:id
								});
				},
		    line:true
		});	
                	$('#shitems1').datagrid({
                            
                            
                            
                          toolbar: [{
				text:'New',
				iconCls:'icon-add',
				handler: function(){
                                    var rows = $('#shitems').datalist('getSelected');
					//var headrow = $('#sheads').datalist('getSelected');
                                        //alert(headrow.key);
                                    if(rows.key!=""){
				 showModalForm(livesite+'TaxHeads/add_details/' + rows.key+'/0/')}
                                                                              	}
				},
                                {
				text:'Edit',
				iconCls:'icon-edit',
				handler: function(){
                                    var rows = $('#shitems').datalist('getSelected');
					var headrow = $('#shitems1').datalist('getSelected');
                                        //alert(headrow.key);
                                        if(headrow){
                                    if(rows.key!=""){
				 showModalForm(livesite+'TaxHeads/add_details/' + rows.key+'/'+headrow.key)}
                         }
                         else{
                             alert("please select any row ");
                         }
                                                                              	}
				},
                                  
                {
		    text:'Save',
            iconCls:'icon-save',
			handler: function(){
				var rows = $('#shitems1').datalist('getChecked');
					var headrow = $('#sheads').datalist('getSelected');
				console.log(rows)
								if (rows){
													 var str_ids = "";
                    	 for(var i=0;i<rows.length;i++){
                    	 	var data = rows[i];
									 if(str_ids == ""){
										 str_ids += data.key;
									 }else
									 {
										 str_ids += ","+data.key;
									 }
                    	 }
			
				$.ajax({
								url : livesite+"TaxHeads/saveTaxHead",
								data : {
								tax_head_item : str_ids,
								tax_head:headrow.key
								},
							success : function(response) { //alert('sucess');
					 				$.notify($.parseJSON(response).msg,{
											type: 'success',
											allow_dismiss: true
																									
										});
							}
							}); closeModal('shitems1');  
			
			}
			
			
			
			
		}
	}],
		    url: livesite+'TaxHeads/getTaxHeaddetails',
		    checkOnSelect:true,
		    singleSelect:false,
		  		    onUncheck:function(i,rows){
                                      var headrow = $('#shitems').datalist('getSelected');
						console.log(rows)						

                                        console.log(rows)
								if (rows){
					
				$.ajax({
								url : livesite+"TaxHeads/removeTaxHead",
								data : {
								tax_head_item : rows.key,
								tax_head:headrow.key
								},
							success : function(response) {
					 				$.notify($.parseJSON(response).msg,{
											type: 'success',
											allow_dismiss: true
																									
										}); closeModal('shitems1');  
							}
							});
			
			}
			
			
			
		    },
		    onLoadSuccess:function(){
		    $('#shitems1').datalist('acceptChanges');	
		    } ,
                    fitColumns:true,
                    columns:[
				[
				     {checkbox:true,field:'key'},	
			              {field:'text',title:'Description',width:"30%"},
			              {field:'text1',title:'Description first',width:"50%"},
                                      {field:'text2',title:'Limit',width:"40%"}
			              
				]
				] 
                            
                            
                            
                        });
	})
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

