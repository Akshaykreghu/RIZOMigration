<?php foreach ($noti as $notifi)
                    { 
                        ?>
                  <li>
                      <ul class="sidebar-menu" id="menu">
                                  <li><a href="#" data-url="<?php echo $this->webroot . "LeaveRequest/employeeleaves" ?>"><i class="fa fa-users text-aqua"></i>You have a leave request from <?php echo $notifi['empdetails']['first_name'];?></a></li>
                              </ul>
                    
                  </li>
               <?php
                    }
                    ?>