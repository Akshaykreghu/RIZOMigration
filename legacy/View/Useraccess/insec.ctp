<?php

/* 
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/
?>
<script>
      $(document).ready(function(){
        // $("#tt").dxTreeView("expandAll");

       $('#tt').tree({
      
     onCheck: function(node){
         if(node._checked == false) // Edited by Akshay on 13-2-2025
         {
	      // 	alert(node.id);  // alert node text property when clicked
                
             
                var s = node.id;
          
                  var u = $('#emp_pkey').val();
                //   debug(s);
             //   alert(node.id+'not checked');
            $.ajax({
                                url:'userAccess/delete/' + u +'/'+s,
                                success: function(resp){
                                    $('#uaccess').datagrid('reload');
                                    // console.log($payro_priv); 
                                    $.notify("Removed Access Of "+node.text,{
                                        type: 'success',
                                        allow_dismiss: false
                                    });
                                }
                            });
            }
            else
            {
             //  	alert(node.id);  // alert node text property when clicked
                
                var s = node.id;
            
                  var u = $('#emp_pkey').val();
            $.ajax({
                                url:'userAccess/save/' + u +'/'+s,
                                success: function(resp){
                                //    console.log($payro_priv);
                                    $.notify("Access Allowed For "+node.text,{
                                        type: 'success',
                                        allow_dismiss: false
                                    });
                                }
                            });
            }
            // debug(u);
        }
}); 
/* To collapse default */
// $('#tt').tree('collapseAll');
$(function () {
    $('.tree li:has(ul)').addClass('parent_li').find(' > span').attr('title', 'Collapse this branch');
    // $('.tree li:has(ul)').addClass('parent_li').attr('title', 'Collapse this branch');
    //hide the child li elements
    $('.tree li ul > li>ul').hide();
    $('.tree li.parent_li > span').on('click', function (e) {
        var children = $(this).parent('li.parent_li').find(' > ul > li');
        if (children.is(":visible")) {
            children.hide('fast');
            // $(this).attr('title', 'Expand this branch').find(' > i').addClass('icon-plus-sign').removeClass('icon-minus-sign');
        } else {
            children.show('fast');
            // $(this).attr('title', 'Collapse this branch').find(' > i').addClass('icon-minus-sign').removeClass('icon-plus-sign');
        }
        e.stopPropagation();
    });
});
/* To collapse default */
 $('#admin').tree({
   onCheck: function(node){
         if(node.checked == true)
         {
           //   alert(node.id+'not checked');
           var s = 'id';
          
                  var u = $('#emp_pkey').val();
             //   alert(node.id+'not checked');
                         $.ajax({
                                url:'UserAccess/admin/' + u +'/'+s,
                                success: function(resp){
                                    $('#uaccess').datagrid('reload');
                                    $.notify("Removed Admin Access",{
                                        type: 'success',
                                        allow_dismiss: false
                                    });
                                }
                            });
            }
            else
            {
            //	alert(node.id);  // alert node text property when clicked
                
                 var s = 'ADMINS';
          
                  var u = $('#emp_pkey').val();
             //   alert(node.id+'not checked');
                         $.ajax({
                                url:'userAccess/admin/' + u +'/'+s,
                                success: function(resp){
                                    $('#uaccess').datagrid('reload');
                                    $.notify("Access Of Admin Granted",{
                                        type: 'success',
                                        allow_dismiss: false
                                    });
                                }
                            });
          
            }
        }
       
        
});  
 $('#admin2').tree({
   onCheck: function(node){
       //console.log(node);
         if(node.checked == true)
         {
              console.log(node);
           var s = 'id';
          
                  var u = $('#emp_pkey').val();
             //   alert(node.id+'not checked');
                         $.ajax({
                                url:'UserAccess/admin2/' + u +'/'+s,
                                success: function(resp){
                                    // console.log(resp);
                                       var message = resp.message;
                                      console.log(message);
                                    $('#uaccess').datagrid('reload');
                                    $.notify("Removed Admin Access",{
                                        type: 'success',
                                        allow_dismiss: false
                                    });
                                }
                            });
            }
            else
            {
            //	alert(node.id);  // alert node text property when clicked
                
                 var s = 'ADMINSS';
          
                 var u = $('#emp_pkey').val();
             //   alert(node.id+'not checked');
                         $.ajax({
                               url:'userAccess/admin2/' + u +'/'+s,
                                success: function(resp){
                                  // var message = resp.message;
        //console.log(resp); // Make sure you're receiving the correct value here
         // Check the checkbox if the message is '1' (assuming '1' represents true)
       
        // Check the checkbox if the message is '1' (assuming '1' represents true)
       // if (message === '1') {
         //   $('#ADMINSS').prop('checked', true);
        //} else {
       //     $('#ADMINSS').prop('checked', false);
      //  }
                                    //var message = resp.message;
                                   ///  console.log(message);
                                    $('#uaccess').datagrid('reload');
                                    $.notify("Access Of Admin Granted",{
                                        type: 'success',
                                       allow_dismiss: false
                                    });
                                }
                            });
          
            }
        }
       
        
});  

    })
    
    function adddefault(){
        var u = $('#emp_pkey').val();
         //   alert(node.id+'not checked');
         $.ajax({
            url:'userAccess/addDefault/' + u ,
            success: function(resp){
                var response = JSON.parse(resp);
                if(response.status === "success"){
                     $.notify("Menu Added ", {
                         type: 'success',
                         allow_dismiss: false
                     });
                     $('#load').load(livesite+'UserAccess/insec/'+u);
                        reloadDatagrid(u);
                    }else{
                        $.notify("Could not add Menu ", {
                         type: 'danger',
                         allow_dismiss: false
                     });
                    }
                 },
            error: function(resp){
                $.notify("Sorry there is some error ", {
                         type: 'danger',
                         allow_dismiss: false
                     });
                     
                 } 
             });
    }
    
    function resetdefault(){
        var u = $('#emp_pkey').val();
         //   alert(node.id+'not checked');
         $.ajax({
            url:'userAccess/resetDefault/' + u ,
            success: function(resp){
                var response = JSON.parse(resp);
                if(response.status === "success"){
                     $.notify("Menu Added ", {
                         type: 'success',
                         allow_dismiss: false
                     });
                     $('#load').load(livesite+'UserAccess/insec/'+u);
                        reloadDatagrid(u);
                    }else{
                        $.notify("Could not add Menu ", {
                         type: 'danger',
                         allow_dismiss: false
                     });
                    }
                 },
            error: function(resp){
                $.notify("Sorry there is some error ", {
                         type: 'danger',
                         allow_dismiss: false
                     });
                     
                 }     
             });
    }
    
    function removemenus(){
        var u = $('#emp_pkey').val();
         //   alert(node.id+'not checked');
         $.ajax({
            url:'userAccess/deletemens/' + u ,
            success: function(resp){
                $.notify("Removed All menu ", {
                         type: 'success',
                         allow_dismiss: false
                     });
                     $('#load').load(livesite+'UserAccess/insec/'+u);
                        reloadDatagrid(u);
                 }
             });
    }
    
</script>
<script>
    $(document).ready(function(){
    $(".tree-title br").remove(); // Remove <br> tags
    $(".tree-title").html(function(_, html) {
        return html.replace(/&nbsp;/g, ''); // Remove &nbsp; spaces
    });
});  
</script>  
    <!--<h2>User Menu</h2>-->
    <br><br>
    <div class="col-md-8">
    <div class="easyui-panel" style="padding:5px">
        <!-- <ul data-toggle="tooltip" title="Shows Dashboard of the team of this selected employee's hierarchy"  id="admin" class="easyui-tree" data-options="animate:true,checkbox:true,cascadeCheck:false,dnd:true">
            <li  <?php if(isset($fetchUser['0']['Useraccess']['active']) && $fetchUser['0']['Useraccess']['active'] == 'Y'){ echo "checked='true' " ; } ?> id="ADMINS"><span >My employee Dashboard</span> -->
            <!-- edited by bindhu 30-01-2026 -->
             <ul data-toggle="tooltip" title="Shows Dashboard of the team of this selected employee's hierarchy" id="admin" class="easyui-tree" data-options="animate:true,checkbox:true,cascadeCheck:false,dnd:true">
                <!-- end  edited by bindhu 30-01-2026 -->
            <li data-options="checked: <?= (isset($fetchUser['0']['Useraccess']['active']) && $fetchUser['0']['Useraccess']['active'] == 'Y') ? 'true' : 'false' ?>" id="ADMINS"><span>My employee Dashboard</span>
           <!--<li  <?php  ?> ><span >Inactive Salary</span>-->
                <!-- <ul>
                <li</li>
            </ul> -->
           
          </ul>
         <ul data-toggle="tooltip" title="Shows Dashboard of the team of this selected employee's hierarchy"  id="admin2" class="easyui-tree" data-options="animate:true,checkbox:true,cascadeCheck:false,dnd:true">
           <li <?php if(isset($payroUser[0]['emp_proff']['payro_priv']) && $payroUser[0]['emp_proff']['payro_priv'] == '1'){ echo "checked='true' " ; } ?> id="ADMINSS"><span >Inactivate Salary Data</span>
           </ul>
        <ul data-toggle="tooltip" title="Your employees will not have any menus by default, choose menus for him" id="tt" class="easyui-tree" data-options="animate:true,checkbox:true,cascadeCheck:false,dnd:true">
             <li id="All">
                <span>All Menu</span>
                <!-- <div id="test"> -->
                <ul id="test">
                    <?php foreach($arr_parent as $val)
                    {
                 //  debug($val);
                  
                  
                  
                   ?>
               <!-- <li data-options="state:'closed'" <?php //if($val['0']['active'] == 'Y' and $val['u']['status'] == '1'){ echo "checked='true' " ; } ?>   id="<?php echo $val['EmployeeMenu']['menu_id']; ?>">-->
                        
                    <li data-options="checked: <?= $val['0']['active'] == 'Y' ? 'true' : 'false'; ?>" <?php //if($val['0']['active'] == 'Y' and $val['u']['status'] == '1'){ echo "checked='true' " ; } ?>   id="<?php echo $val['EmployeeMenu']['menu_id']; ?>">
                        <span><?php echo $val['EmployeeMenu']['menu_name'] ?></span>
                        <ul>
                            <?php foreach($arr_child as $key => $value) 
                            {
                            if($value['EmployeeMenu']['parent_id'] == $val['EmployeeMenu']['menu_id'])
                            {
                            ?>
                            <?php //if($value['0']['active'] == 'Y'){ echo "checked='true' " ; } else { echo "checked='false' " ; } ?>  
                            <li data-options="checked: <?= $value['0']['active'] == 'Y' ? 'true' : 'false'; ?>"  id="<?php echo $value['EmployeeMenu']['menu_id']; ?>">
                               <!--  //changed ['menu_name'] to ['menu_title'] by nimisha on 14_5_19-->
                                <span><?php echo $value['EmployeeMenu']['menu_title']; ?></span>
                            </li>
                            <?php
                            }
                            }
                            ?>
                            </ul>
                    </li>
                    <?php
                   
                    }
                    ?>
                  <!--  <li><?php if($value['EmployeeMenu']['menu_id']) ?>
                        <span>Program Files</span>
                        <ul>
                            <li>Intel</li>
                            <li>Java</li>
                            <li>Microsoft Office</li>
                            <li>Games</li>
                        </ul>
                    </li>
                    <li>index.html</li>
                    <li>about.html</li>
                    <li>welcome.html</li>
                </ul>
            </li> -->
        </ul>
        <!-- </div> -->
    </div>
    </div>
<!--    <div class="col-md-4">
        <div class="col-md-12">
            <button onclick="adddefault();" class="btn btn-primary">Add Default Menus</button>
        </div>
        <div class="col-md-12">
            <button class="btn btn-success" onclick="resetdefault();">Reset To Default</button>
        </div>
        <div class="col-md-12">
            <button class="btn btn-danger" onclick="removemenus();">Remove All</button>
        </div>
        
    </div>-->
    
    
    <!--div class="col-md-10" style="margin:20px 0;">
           <div class="form-group">    <a href="#" class="btn btn-primary" onclick="getChecked()">GetChecked</a> 
 
        <input type="button" class="btn btn-primary" value="Save Changes">
        </div>
        </div-->
    
    
    
