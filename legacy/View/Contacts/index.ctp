<h1 class="page-header">Contacts</h1>
<div >
    <table id="contacttable">
        
        
    </table>
</div>
<script>
    $(document).ready(function(){
        
      $('#contacttable').datagrid({
            url:livesite+'Contacts/listcontacts',
            rownumbers:true,
            title:"Contacts",
            fitColumns:true,
            singleSelect:true,
            autoRowHeight:false,
            pagination:true,
            pageSize:10,
            toolbar: [{
		iconCls: 'icon-add',
                text:"New",
		handler: function(){
                    //location.href='contacts/addeditcontacts';
                    var url='contacts/addeditcontacts';
                            $('#modalDiv').load(url,function(){
                                $('#modalDiv').modal('show');
                            });
                }
            },'-',{
                    iconCls: 'icon-edit',
                    text:"Edit",
                    handler: function(){
                        var row = $('#contacttable').datagrid('getSelected');
                        if (row){
                            var contact_id = row.contact_id;
                            //location.href='contacts/addeditcontacts/'+contact_id;
                            var url='contacts/addeditcontacts/'+contact_id;
                            $('#modalDiv').load(url,function(){
                                $('#modalDiv').modal('show');
                            });
                        }
                    }
            },'-',{
                iconCls: 'icon-remove',
		  text:'Remove',
		handler: function(){
              var row = $('#contacttable').datagrid('getSelected');
                        if (row) {
                              var r=confirm("Do You Want  To Remove The Selected Item")
                                    if(r==true){
                                          
                            var contact_id = row.contact_id;
                            $.ajax({
                                url:'contacts/deletecontacts/' + contact_id,
                                success: function(resp){
                                    $('#contacttable').datagrid('reload');
                                    $.notify($.parseJSON(resp).msg,{
                                        type: 'success',
                                        allow_dismiss: false
                                    });
                                }
                            });
                        }
                        else
                        {
                            alert("canceled");
                        }
                        }
                        }
                    }
            
        ],
            columns:[[
                {field:'company_name',title:'Company Name',width:100,sortable:true,order:'asc'},
                {field:'email',title:'Email',width:100,sortable:true,order:'asc'},
                {field:'phone',title:'Phone Number',width:100,sortable:true,order:'asc'},
                {field:'first_name',title:'First Name',width:100,sortable:true,order:'asc'},
                {field:'address',title:'Address',width:100,sortable:true,order:'asc'},
                {field:'city',title:'City',width:100,sortable:true,order:'asc'},
                {field:'state',title:'State',width:100,sortable:true,order:'asc'},
                {field:'pincode',title:'Pincode',width:100,sortable:true,order:'asc'},
                {field:'bank_branch',title:'Bank Branch',width:100,sortable:true,order:'asc'},
               
                
                {field:'relationship',title:'Relationship',width:100,sortable:true,order:'asc'},
            ]]
        });
    });

</script>