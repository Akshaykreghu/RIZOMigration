<h1 class="page-header">User Access</h1>
<div class="form-group">
        <label for="usr">User</label>
        <select id="usr" class="form-control" name="user_pkey" required="required">
            <?php foreach ($arr_menus as $value) { ?>
                <option value="<?php echo $value['EmployeeDetails']['emp_pkey']; ?>"><?php echo $value['EmployeeDetails']['first_name']; ?> <?php echo $value['EmployeeDetails']['last_name']; ?></option>
            <?php } ?>
        </select>
    </div>
<br>
    <table id="uaccess" >
    </table>
<script>
    //reload data grid with username start
    function reloadDatagrid(user_fkey)
   {
    $("#uaccess").datagrid('load',{
            user_fkey:user_fkey
        });
    }
    
    
    
    $(document).ready(function(){
        $('#uaccess').datagrid();
        var user_fkey=$('#usr').val();
        reloadDatagrid(user_fkey);       
        $('#usr').on('change',function(){
        var user_fkey=$(this).val();
        reloadDatagrid(user_fkey);
       });
       // end
      $('#uaccess').datagrid({
            url:'Useraccess/listuseraccess',
            rownumbers:true,
            title:"Useraccess",
            fitColumns:true,
            singleSelect:false,
            autoRowHeight:false,
            pagination:true,
            pageSize:10,
            columns:[[
                    {field:'menu_name',title:'Menu Name',width:100,sortable:true,order:'asc'},
                    {field:'parent',title:'Parent',width:100,sortable:true,order:'asc'},
                    {field:'accessallow',title:'Allow Access',width:100,sortable:true,order:'asc',
                        formatter: function(value,row,index){
                            var menu_id = row.menu_id;
                            if(row.accessallow == 'Y'){
                                return '<input type="checkbox" id="chk-useraccess-'+menu_id+'" checked="checked" />';
                            }else{
                                return '<input id="chk-useraccess-'+menu_id+'" type="checkbox" />';
                            }
			}
                    },
            ]],
            onCheck:function(index,row){
                var menu_id = row.menu_id;
                var user_pkey = $("#usr").val();
                
                var active = 'Y';
                if($('#chk-useraccess-'+menu_id).length != 0){
                    if($('#chk-useraccess-'+menu_id).prop('checked') == true){
                        active = 'Y';
                    }else{
                        active = 'N';
                    }
                }else{
                    return false;
                }
                
                var url = 'useraccess/saveuseraccess';
                $.ajax({
                    url:url,
                    type: 'post',
                    data: { 
                        menu_id : menu_id,
                        user_pkey:user_pkey,
                        active:active
                    },
                    success: function(resp){
                        $.notify($.parseJSON(resp).msg,{
                            type: 'success',
                            allow_dismiss: false
                        });
                    }
                });
            },
            onUncheck:function(index,row){
                var menu_id = row.menu_id;
                var user_pkey = $("#usr").val();                
                
                var active = 'N';
                if($('#chk-useraccess-'+menu_id).length != 0){
                    if($('#chk-useraccess-'+menu_id).prop('checked') == true){
                        active = 'Y';
                    }else{
                        active = 'N';
                    }
                }else{
                    return false;
                }
                
                var url = 'useraccess/saveuseraccess';
                $.ajax({
                    url:url,
                    type: 'post',
                    data: { 
                        menu_id : menu_id,
                        user_pkey:user_pkey,
                        active:active
                    },
                    success: function(resp){
                        $.notify($.parseJSON(resp).msg,{
                            type: 'success',
                            allow_dismiss: false
                        });
                    }
                });
            }
        });
    })

</script>