<!-- first -->
<ul class="nav">
  <li class="hidden-folded padder m-t m-b-sm text-muted text-xs">
    <span translate="aside.nav.HEADER">Navigation</span>
  </li>
  <li>
    <a ui-sref="app.dashboard">
      <i class="glyphicon glyphicon-stats icon text-primary-dker"></i>
      <span class="font-bold" translate="aside.nav.DASHBOARD">Dashboard</span>
    </a>
  </li>
  
  
  <?php
  
 foreach ($menu as $key => $m) {
     ?>
     
      <li>
    <a href class="auto">      
      <span class="pull-right text-muted">
        <i class="fa fa-fw fa-angle-right text"></i>
        <i class="fa fa-fw fa-angle-down text-active"></i>
      </span>
      
      <i class="glyphicon <?php echo $m["icon_cls"]; ?>"></i>
      <span><?php echo $m["menu_title"]; ?></span>
    </a>
    
   <?php if(isset($m["children"]) && count($m["children"]) >0) {
   	?>
   	 <ul class="nav nav-sub dk">
     <li class="nav-sub-header">
        <a href>
        <span><?php echo $m["menu_title"]; ?></span>
        </a>
      </li>
   	<?php
	foreach ($m["children"] as $key2 => $mchild) {
		
		
		
		
		?>
		 
		<li ui-sref-active="active">
        <a ui-sref="<?php echo $mchild["menu_url"]; ?>">
          <span><?php echo $mchild["menu_title"]; ?></span>
        </a>
      </li>
		<?php
		
	}
	?>
	</ul>
	<?php
   }?>
    
    
    
    
    </li>
     <?php
 }
  
   ?>
  
  
  
  
  
  <!--
  
    <li ng-class="{active:$state.includes('app.setup')}">
      <a href class="auto">      
        <span class="pull-right text-muted">
          <i class="fa fa-fw fa-angle-right text"></i>
          <i class="fa fa-fw fa-angle-down text-active"></i>
        </span>
        
        <i class="glyphicon glyphicon-th"></i>
        <span>Setup</span>
      </a>
      <ul class="nav nav-sub dk">
        <li class="nav-sub-header">
          <a href>
            <span>Setup</span>
          </a>
        </li>
         <li ui-sref-active="active">
          <a ui-sref="app.setup.companyinfo">
            <span>Company Info.</span>
          </a>
        </li>
        <li ui-sref-active="active">
          <a ui-sref="app.setup.salaryheads">
            <span>Salary Heads</span>
          </a>
        </li>
        <li ui-sref-active="active">
          <a ui-sref="app.setup.employee">
            <span>Employees</span>
          </a>
        </li>
             
      </ul>
    </li>
    <li ui-sref-active="active">
      <a ui-sref="app.calendar">
        <i class="glyphicon glyphicon-calendar icon text-info-dker"></i>
        <span class="font-bold" >Pay</span>
      </a>
    </li>
    <li ui-sref-active="active">
      <a ui-sref="app.mail.list">
        <b class="badge bg-info pull-right">9</b>
        <i class="glyphicon glyphicon-envelope icon text-info-lter"></i>
        <span class="font-bold" >Reports</span>
      </a>
    </li>
    <li ui-sref-active="active">
      <a ui-sref="app.widgets">
        <b class="badge bg-success dk pull-right">16</b>
        <i class="glyphicon glyphicon-th-large icon text-success"></i>
        <span class="font-bold" >Inform</span>
      </a>
    </li>
    <li class="line dk"></li>
  -->
  
  
</ul>
<!-- / third -->