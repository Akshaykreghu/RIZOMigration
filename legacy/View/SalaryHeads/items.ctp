
<div ng-controller="SalaryHeadListCtrl">
  <!-- header -->
  <!--
  <div class="wrapper bg-light lter b-b">
      <div class="btn-group pull-right">
        <button type="button" class="btn btn-sm btn-bg btn-default"><i class="fa fa-chevron-left"></i></button>
        <button type="button" class="btn btn-sm btn-bg btn-default"><i class="fa fa-chevron-right"></i></button>
      </div>
      <div class="btn-toolbar">
        <div class="btn-group dropdown">
          <button class="btn btn-default btn-sm btn-bg dropdown-toggle" data-toggle="dropdown">
            <span class="dropdown-label">Filter</span>                    
            <span class="caret"></span>
          </button>
          <ul class="dropdown-menu text-left text-sm">
            <li><a ui-sref="app.mail.list({fold:'unread'})">Unread</a></li>
            <li><a ui-sref="app.mail.list({fold:'starred'})">Starred</a></li>
          </ul>
        </div>
        <div class="btn-group">
          <button class="btn btn-sm btn-bg btn-default" data-toggle="tooltip" data-placement="bottom" data-title="Refresh" data-original-title="" title=""><i class="fa fa-refresh"></i></button>
        </div>
      </div>
    </div>-->
  
  <!-- / header -->

  <!-- list -->
   <form id="salaryHeadForm" name="salaryHeadForm" method="POST">
  
  <ul class="list-group list-group-lg no-radius m-b-none m-t-n-xxs">
 	<li ng-repeat="salaryhead in  salaryheadsitems" class="list-group-item clearfix b-l-3x">
 		<input type="checkbox" ng-checked="{{salaryhead.value == 'Y'}}" name="salaryHeadItem[]" class="salaryHeadItem" value="{{salaryhead.salary_head_item_pkey}}" />{{salaryhead.item}}<span>{{salaryhead.comments}}</span></li>
  <!--
      <?php
                  foreach (salaryHeads as $shikey => $shivalue) {
               if($shivalue['value'] === 'Y'){ ?>
                  <li><input type="checkbox" checked="checked" name="salaryHeadItem[]" value="<?php echo $shivalue['salary_head_item_pkey'] ?>" /><?php echo $shivalue['item'] ?><span><?php echo $shivalue['comments'] ?></span></li>
                  <?php } else{
                      ?>
                      <li><input type="checkbox"  name="salaryHeadItem[]" value="<?php echo $shivalue['salary_head_item_pkey'] ?>" /><?php echo $shivalue['item'] ?><span><?php echo $shivalue['comments'] ?></span></li>
                                                <?php
                                                                              }?>
              <?php }
                                                               ?>-->
  <!--
								<li ng-repeat="item in items "  class="list-group-item clearfix b-l-3x">
				  <input type="checkbox" />     {{item.title}}  
				 </li>-->
			 
  </ul>
  </form>
  <!-- / list -->
</div>