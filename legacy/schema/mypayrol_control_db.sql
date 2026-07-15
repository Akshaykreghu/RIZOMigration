-- Adminer 5.3.0 MySQL 5.7.44 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE DATABASE `mypayrol_control_db` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `mypayrol_control_db`;

DELIMITER ;;

DROP PROCEDURE IF EXISTS `add_emp_join_fkey_to_documents`;;
CREATE PROCEDURE `add_emp_join_fkey_to_documents` ()
BEGIN

    DECLARE done INT DEFAULT 0;
    DECLARE v_db_name VARCHAR(100);

    DECLARE cur CURSOR FOR
        SELECT cc.user_db
        FROM central_control cc
        JOIN information_schema.tables ist
            ON cc.user_db = ist.table_schema
        WHERE cc.user_db IS NOT NULL
            AND cc.user_db <> ''
            AND ist.table_name = 'documents'
            AND cc.user_db NOT LIKE '%_moved'
            AND cc.user_db NOT LIKE '%#blocked';

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN cur;

    read_loop: LOOP

        FETCH cur INTO v_db_name;

        IF done = 1 THEN
            LEAVE read_loop;
        END IF;

        -- Check if emp_join_fkey column exists
        SELECT COUNT(*) INTO @col_exists
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = v_db_name
            AND TABLE_NAME = 'documents'
            AND COLUMN_NAME = 'emp_join_fkey';

        -- Add column only if not exists
        IF @col_exists = 0 THEN

            SET @sql = CONCAT(
                'ALTER TABLE `',
                v_db_name,
                '`.`documents` ',
                'ADD COLUMN emp_join_fkey INT(11) NULL'
            );

            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;

        END IF;

    END LOOP;

    CLOSE cur;

END;;

DROP PROCEDURE IF EXISTS `alter_emp_advance_affected_month`;;
CREATE PROCEDURE `alter_emp_advance_affected_month` ()
BEGIN
    DECLARE done INT DEFAULT 0;
    DECLARE v_db_name VARCHAR(100);

    DECLARE cur CURSOR FOR
        SELECT cc.user_db
        FROM central_control cc
        JOIN information_schema.tables ist
            ON cc.user_db = ist.table_schema
        WHERE cc.user_db IS NOT NULL
            AND cc.user_db <> ''
            AND ist.table_name = 'emp_advance'
            AND cc.user_db NOT LIKE '%_moved'
            AND cc.user_db NOT LIKE '%#blocked'
            AND cc.company_code NOT IN (
            'ABSG',
            'VGFS',
            'VSFS',
            'DRRC',
            'DJIC',
            'AGNG',
            'AYRK',
            'SHRD',
            'SNRY',
            'GTRA',
            'SHYD',
            'VGNN',
            'SRTS'
            );

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN cur;

    read_loop: LOOP
        FETCH cur INTO v_db_name;
        IF done THEN
            LEAVE read_loop;
        END IF;

        -- Check if affected_month column exists
        SELECT COUNT(*) INTO @col_exists
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = v_db_name
            AND TABLE_NAME = 'emp_advance'
            AND COLUMN_NAME = 'affected_month';

        IF @col_exists > 0 THEN

            SET @sql = CONCAT(
                'ALTER TABLE `', v_db_name, '`.`emp_advance` ',
                'MODIFY COLUMN `affected_month` VARCHAR(7);'
            );

            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;

        END IF;

    END LOOP;

    CLOSE cur;

END;;

DROP PROCEDURE IF EXISTS `alter_salary_amount_decimal`;;
CREATE PROCEDURE `alter_salary_amount_decimal` ()
BEGIN
    DECLARE done INT DEFAULT 0;
    DECLARE v_db_name VARCHAR(100);

    DECLARE cur CURSOR FOR
        SELECT cc.user_db
        FROM central_control cc
        JOIN information_schema.tables ist
            ON cc.user_db = ist.table_schema
        WHERE cc.user_db IS NOT NULL
            AND cc.user_db <> ''
            AND cc.user_db NOT LIKE '%_moved'
            AND cc.user_db NOT LIKE '%#blocked'
            AND cc.company_code NOT IN (
                'HRBL','KWMT','AIMA','ESNP','MBCT','MRBS','STCL','VGNN','ABSG','VGFS','VSFS','DRRC','DJIC','AGNG','AYRK','SRTS','SHYD','GTRA'
            );

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN cur;

    read_loop: LOOP
        FETCH cur INTO v_db_name;
        IF done THEN
            LEAVE read_loop;
        END IF;

        -- emp_statutory_components table check
        SELECT COUNT(*) INTO @table_exists
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = v_db_name
        AND TABLE_NAME = 'emp_statutory_components';

        IF @table_exists > 0 THEN

            SET @sql = CONCAT(
                'ALTER TABLE `', v_db_name, '`.`emp_statutory_components` ',
                'CHANGE `salary_amount` `salary_amount` decimal(10,2) NULL AFTER `salary_rate2`;'
            );

            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;

        END IF;


        -- emp_salary_slip table check
        SELECT COUNT(*) INTO @table_exists
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = v_db_name
        AND TABLE_NAME = 'emp_salary_slip';

        IF @table_exists > 0 THEN

            SET @sql = CONCAT(
                'ALTER TABLE `', v_db_name, '`.`emp_salary_slip` ',
                'CHANGE `salary_amount` `salary_amount` decimal(10,2) NULL AFTER `salary_rate`;'
            );

            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;

        END IF;

    END LOOP;

    CLOSE cur;

END;;

DROP PROCEDURE IF EXISTS `company_statistics_prc`;;
CREATE PROCEDURE `company_statistics_prc` (IN `pmonth` varchar(20))
begin

declare vcompany_code varchar(20);
declare vcompany_name varchar(200);
declare vaddress varchar(500);
declare vuser_db varchar(100);
declare vAtt_Verified int;
declare vAtt_Not_Verified int;
declare vPay_Processed int;
declare vPay_Not_Processed int;
declare vTotal_Emp_Count int;
declare vEmp_Added int;
declare vEmp_Terminated int;
declare vexist_count int;

declare exit_loop boolean default false;

declare company_cur cursor for
	select company_code, company_name, address, user_db from central_control as cc
	join information_schema.tables as ist on cc.user_db = ist.table_schema
	where cc.user_db is not null and cc.user_db <> '' and ist.table_name = 'attendance_register'
	and cc.user_db not like '%_moved' and cc.user_db not like '%#blocked';
	
declare continue handler for not found set exit_loop = true;

open company_cur;
	start_loop: loop
		fetch company_cur into vcompany_code, vcompany_name, vaddress, vuser_db;
		
		if exit_loop then
			leave start_loop;
		end if;
		
		set @att_not_verified := 0;
		set @dyn_sql = concat('select count(*) into @att_not_verified from `', vuser_db,
						'`.`attendance_register` where company_code = ', quote(vcompany_code),
						' and month_year = ', quote(pmonth),
						' and isdelete = ''Y'' and record_status = 1');
		prepare stmt from @dyn_sql;
		execute stmt;
		deallocate prepare stmt;
		set vAtt_Not_Verified = ifnull(@att_not_verified, 0);
		
		set @att_verified := 0;
		set @dyn_sql = concat('select count(*) into @att_verified from `', vuser_db,
						'`.`attendance_register` where company_code = ', quote(vcompany_code),
						' and month_year = ', quote(pmonth),
						' and isdelete = ''N'' and record_status = 1');
		prepare stmt from @dyn_sql;
		execute stmt;
		deallocate prepare stmt;
		set vAtt_Verified = ifnull(@att_verified, 0);
		
		set @pay_processed := 0;
		set @dyn_sql = concat('select count(*) into @pay_processed from `', vuser_db,
						'`.`payroll_master` where month_year = ', quote(pmonth),
						' and action in (''Processed'',''Approved'')');
		prepare stmt from @dyn_sql;
		execute stmt;
		deallocate prepare stmt;
		set vPay_Processed = ifnull(@pay_processed, 0);
		
		set @pay_not_processed := 0;
		set @dyn_sql = concat('select count(*) into @pay_not_processed from `', vuser_db,
						'`.`payroll_master` where month_year = ', quote(pmonth),
						' and action is null');
		prepare stmt from @dyn_sql;
		execute stmt;
		deallocate prepare stmt;
		set vPay_Not_Processed = ifnull(@pay_not_processed, 0);
		
		set @total_emp_count := 0;
		set @dyn_sql = concat('select count(*) into @total_emp_count from `', vuser_db,
						'`.`emp_details` where status = 1');
		prepare stmt from @dyn_sql;
		execute stmt;
		deallocate prepare stmt;
		set vTotal_Emp_Count = ifnull(@total_emp_count, 0);
		
		set @emp_added := 0;
		set @dyn_sql = concat('select count(*) into @emp_added from `', vuser_db,
						'`.`emp_details` where status = 1 and creation_date like ''', pmonth, '%''');
		prepare stmt from @dyn_sql;
		execute stmt;
		deallocate prepare stmt;
		set vEmp_Added = ifnull(@emp_added, 0);
		
		set @emp_terminated := 0;
		set @dyn_sql = concat('select count(*) into @emp_terminated from `', vuser_db,
						'`.`emp_details` where status = 2 and emp_pkey in (select emp_fkey from `', vuser_db, '`.`termination` where status = 1 and last_approved_working_date like ''', pmonth, '%'')');
		prepare stmt from @dyn_sql;
		execute stmt;
		deallocate prepare stmt;
		set vEmp_Terminated = ifnull(@emp_terminated, 0);
		
		select count(*) into vexist_count from company_statistics where company_code = vcompany_code and month_year = pmonth and status = 1;
		
		if vexist_count = 0 then
			insert into company_statistics (company_code, company_name, address, month_year, Total_Emp_Count, Emp_Added, Emp_Terminated, Att_Verified, Att_Not_Verified, Pay_Processed, Pay_Not_Processed, created_by)
			values (vcompany_code, vcompany_name, vaddress, pmonth, vTotal_Emp_Count, vEmp_Added, vEmp_Terminated, vAtt_Verified, vAtt_Not_Verified, vPay_Processed, vPay_Not_Processed, 'Admin');
		else
			update company_statistics set Total_Emp_Count = vTotal_Emp_Count, Emp_Added = vEmp_Added, Emp_Terminated = vEmp_Terminated, Att_Verified = vAtt_Verified, Att_Not_Verified = vAtt_Not_Verified, Pay_Processed = vPay_Processed, Pay_Not_Processed = vPay_Not_Processed, modified_by = 'Admin'
			where company_code = vcompany_code and month_year = pmonth and status = 1;
		end if;
		
	end loop start_loop;
close company_cur;

end;;

DROP PROCEDURE IF EXISTS `CreateDatabasesAndUsers`;;
CREATE PROCEDURE `CreateDatabasesAndUsers` (IN `pstart` int, IN `pend` int)
BEGIN
    DECLARE i INT DEFAULT pstart;
    DECLARE db_name VARCHAR(50);
    DECLARE user_name VARCHAR(50);
    DECLARE company_code VARCHAR(10);
    DECLARE hashed_password VARCHAR(255);

    
    WHILE i <= pend DO
        
        SET db_name = CONCAT('mypayrol_mpm', i);
        SET user_name = CONCAT('mypayrol_mpm', i);
        SET company_code = CONCAT('T', i);
        SET hashed_password = 'ae227f52e3dbd764815da2e23056fc27d577421c'; 

        
        SET @sql_create_db = CONCAT('CREATE DATABASE ', db_name, ';');
        PREPARE stmt1 FROM @sql_create_db;
         EXECUTE stmt1;
        DEALLOCATE PREPARE stmt1;

        
        SET @sql_create_user = CONCAT('CREATE USER \'', user_name, '\'@\'127.0.0.1\' IDENTIFIED BY \'Localhost&*()\';');
        PREPARE stmt2 FROM @sql_create_user;
        EXECUTE stmt2;
        DEALLOCATE PREPARE stmt2;

        
        SET @sql_grant_privileges = CONCAT('GRANT ALL ON ', db_name, '.* TO \'', user_name, '\'@\'127.0.0.1\' WITH GRANT OPTION;');
        PREPARE stmt3 FROM @sql_grant_privileges;
        EXECUTE stmt3;
        DEALLOCATE PREPARE stmt3;

        
        SET @sql_insert_central_control = CONCAT('INSERT INTO `central_control` (`control_pkey`, `company_code`, `company_name`, `Address`, `Admin_name`, `user_db`, `user_pwd`, `created_date`, `start_date_effective`, `end_date_effective`, `product`, `active`, `custom_message`, `redirect_url`, `country_code`, `currency_code`, `punch_type`, `attr1`, `attr2`, `attr3`, `attr4`, `attr5`, `attr6`, `attr7`, `trial_status`, `subdomain`, `third_party`, `web_url`, `api_url`, `biometric_url`) VALUES (', i, ', \'', company_code, '\', \'Edittrial\', \'\', \'', user_name, '\', \'', user_name, '\', \'Localhost&*()\', \'2024-07-24 00:00:00\', \'2024-07-24\', \'2030-02-12\', \'localhost90\', \'active\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\',\'A\', NULL, NULL, \'https://v1.mypayrollmaster.online/\', \'https://v1.mypayrollmaster.online/api/v1/\', NULL);');
        PREPARE stmt4 FROM @sql_insert_central_control;

        EXECUTE stmt4;
        DEALLOCATE PREPARE stmt4;

        
        SET @sql_insert_user_credentials = CONCAT('INSERT INTO `user_credentials` (`control_fkey`, `company_code`, `user_id`, `password`, `access_allowed`, `start_date`, `end_date`, `first_name`, `last_name`, `middle_name`, `email`, `phone`, `reset_login_flag`, `locked`, `attr1`, `attr2`, `avatar`) VALUES (', i, ', \'', company_code, '\', \'support', i, '\', \'', hashed_password, '\', \'y\', \'2024-07-24\', \'2030-02-12\', \'support\', \'', i, '\', \'\', \'support@greatleap.tech\', 2147483647, \'N\', \'\', \'\', \'\', NULL), (', i, ', \'', company_code, '\', \'trialadmin', i, '\', \'', hashed_password, '\', \'y\', \'2023-01-12\', \'2030-02-12\', \'\', \'Admin\', \'\', \'support@greatleap.tech\', 0, \'Y\', \'0\', \'\', \'\', NULL);');
        PREPARE stmt5 FROM @sql_insert_user_credentials;

        EXECUTE stmt5;
        DEALLOCATE PREPARE stmt5;

        
        SET i = i + 1;
    END WHILE;
END;;

DROP PROCEDURE IF EXISTS `emp_id_maping_exist_prc`;;
CREATE PROCEDURE `emp_id_maping_exist_prc` (IN `pCompany_code` varchar(20))
begin

declare vemp_pkey int;
declare vfirst_name varchar(50);
declare vemp_device_startid int;
declare vDeviceId int;
DECLARE exit_loop BOOLEAN default FALSE;

declare rec_count int;
declare vdevice_count int ;
declare vemp_co_id int;
declare vEmpNo int;
declare vid_mapped int;

declare cur cursor for
select distinct emp_fkey,lcase(emp_name),id_mapped from emp_id_maping_exist where Company_code = pCompany_code and status='N';
DECLARE CONTINUE HANDLER FOR NOT FOUND SET exit_loop = TRUE;


open cur;
start_loop: loop
    fetch cur into vemp_pkey,vfirst_name,vid_mapped;
IF exit_loop THEN
        LEAVE start_loop;
     END IF;
 if vid_mapped is not null then
 update emp_device_comp_branch set emp_device_id=vid_mapped where emp_fkey=vemp_pkey and company_code= pCompany_code ; 
 update emp_id_maping_exist set status='Y' where emp_fkey=vemp_pkey;
 end if;
  end loop;
close cur;





end;;

DROP PROCEDURE IF EXISTS `emp_id_maping_prc`;;
CREATE PROCEDURE `emp_id_maping_prc` (IN `pCompany_code` varchar(30), OUT `perr_msg` varchar(100))
begin

declare vemp_pkey int;
declare vfirst_name varchar(50);
declare vemp_device_startid int;
declare vDeviceId int;
DECLARE exit_loop BOOLEAN default FALSE;

declare rec_count int;
declare vdevice_count int ;
declare vemp_co_id int;
declare vEmpNo int;

declare cur cursor for
select distinct emp_fkey,lcase(emp_name) from emp_device_comp_branch where Company_code = pCompany_code ;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET exit_loop = TRUE;


open cur;
start_loop: loop
    fetch cur into vemp_pkey,vfirst_name;
IF exit_loop THEN
        LEAVE start_loop;
     END IF;
 select EmpNo into vEmpNo
 from emp_id_maping where concat(lcase(FirstName),' ',lcase(LastName))	=vfirst_name and EmpNo is not null ;
 if vEmpNo is not null then
 update emp_device_comp_branch set emp_device_id=vEmpNo where emp_fkey=vemp_pkey and company_code= pCompany_code ; 
 update emp_id_maping set status='Y' where EmpNo=vEmpNo ;
 end if;
  end loop;
close cur;




end;;

DROP PROCEDURE IF EXISTS `estern_username_update`;;
CREATE PROCEDURE `estern_username_update` (IN `pemp_pkey` int)
begin

declare vemp_pkey int;
declare vfirst_name varchar(50);
declare vemp_device_startid int;
declare vDeviceId int;
DECLARE exit_loop BOOLEAN default FALSE;

declare rec_count int;
declare vdevice_count int ;
declare vemp_co_id int;
declare vemp_company_id varchar(100);
declare vusername varchar(100);
declare vemp_branch varchar(30);
declare pcompany_code varchar(30);
DECLARE vemail varchar(100);


declare cur cursor for
select distinct emp_fkey,trim(emp_company_id),concat(substr(emp_branch,1,4),emp_company_id) username ,emp_branch
                                     from emp_proff where emp_fkey=pemp_pkey 
									 order by 1;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET exit_loop = TRUE;

open cur;
start_loop: loop
    fetch cur into vemp_pkey,vemp_company_id,vusername,vemp_branch;
IF exit_loop THEN
        LEAVE start_loop;
     END IF;
   select company_code,email,first_name into pcompany_code,vemail,vfirst_name from emp_details where emp_pkey=vemp_pkey;
 select count(*) into rec_count from mypayrol_control_db.emp_device_comp_branch where Company_code=pcompany_code and 
      branch_code=vemp_branch and emp_fkey=vemp_pkey;
 select ifnull(DeviceId,1000) into vDeviceId  from mypayrol_control_db.devices where company_code= pcompany_code limit 1;
 if vDeviceId is null THEN
  set vDeviceId=1000;
  end if;
if rec_count=0 then

   insert into mypayrol_control_db.emp_device_comp_branch(deviceid,emp_device_id,emp_name,Company_code,branch_code,emp_id,emp_username,emp_fkey,email ) 
         values (vDeviceId,vemp_company_id,vfirst_name,pcompany_code,vemp_branch,vemp_company_id,vusername,vemp_pkey,vemail);  
 		 
   update emp_details set emp_id=vemp_company_id where emp_pkey=vemp_pkey;
   update  user_credentials set user_id=vusername where emp_fkey=vemp_pkey ;
 else
  update emp_details set emp_id=vemp_company_id where emp_pkey=vemp_pkey;
   update  user_credentials set user_id=vusername where emp_fkey=vemp_pkey ;
   update mypayrol_control_db.emp_device_comp_branch set emp_device_id=vemp_company_id,emp_id=vemp_company_id,
   emp_username=vusername,email=vemail
            where emp_fkey=vemp_pkey and Company_code=pcompany_code ;
 end if;
  end loop;
close cur;




end;;

DROP PROCEDURE IF EXISTS `generateDatabase`;;
CREATE PROCEDURE `generateDatabase` (IN `pstartfrom` int(11), IN `pendto` int(11))
NO SQL
begin
  declare i int;
   declare vaccount int default 0;
  declare vcreate_db varchar(4000) default ''; 
  declare vcreate_user varchar(4000) default ''; 
  declare vgrant varchar(4000) default '';
   declare vinsert1 varchar(4000) default '';
    declare vinsert2 varchar(4000) default '';
	 declare vinsert3 varchar(4000) default '';


set i=pstartfrom;

	WHILE i <= pendto DO
     SET @vcreate_db = CONCAT('create database mypayrol_mpm',i); 
 PREPARE stmt FROM @vcreate_db;
 
 insert into test(field1) values(@vcreate_db);
 
 
     SET @vcreate_user = CONCAT('create user ','''mypayrol_mpm''',i,'@''''localhost'''' IDENTIFIED BY ''''Localhost&*()'''); 
 PREPARE stmt FROM @vcreate_user;
 
  insert into test(field2) values(@vcreate_user);
 
 
      SET @vgrant = CONCAT('GRANT ALL ON mypayrol_mpm',i,'.* TO ''''mypayrol_mpm',i, '''@''localhost'' IDENTIFIED BY ''''Localhost&*()'' WITH GRANT OPTION'); 
 PREPARE stmt FROM @vgrant;
 
  insert into test(field3) values(@vgrant);
 DEALLOCATE PREPARE stmt;
 
PREPARE stmt FROM @vinsert1;
 

 DEALLOCATE PREPARE stmt;
 
		select i + 1 into i;
		END WHILE;


end;;

DROP PROCEDURE IF EXISTS `generate_deployment_sql`;;
CREATE PROCEDURE `generate_deployment_sql` ()
begin
    declare done int default false;
    declare db_name varchar(255);
    
    declare db_cursor cursor for
        select user_db from central_control as cc
        join information_schema.tables as ist on cc.user_db = ist.table_schema
        where cc.user_db is not null and cc.user_db <> '' and ist.table_name = 'device_attandance'
        and cc.user_db not like '%_moved' and cc.user_db not like '%#blocked';
    
    declare continue handler for not found set done = true;
    
    drop temporary table if exists deployment_commands;
    create temporary table deployment_commands (
        step_order int auto_increment primary key,
        db_name varchar(255),
        sql_command text
    );
    
    open db_cursor;
    
    read_loop: loop
        fetch db_cursor into db_name;
        if done then
            leave read_loop;
        end if;
        
        insert into deployment_commands (db_name, sql_command) values
        (db_name, concat('use `', db_name, '`;')),
        (db_name, concat('drop function if exists `device_logs_iteration_fn`;')),
        (db_name, 'delimiter ;;'),
        (db_name, 'create function `device_logs_iteration_fn` (`pemp_id` varchar(20), `pyear_month` date) returns varchar(100) character set ''latin1'' modifies sql data deterministic begin create temporary table temp_device_attandance as select * from device_attandance where emp_id = pemp_id and date_format(LOGDATE, ''%Y-%m-%d'') between att_start_end_fn(pyear_month,1) and att_start_end_fn(pyear_month,2); delete from device_attandance where emp_id = pemp_id and date_format(LOGDATE, ''%Y-%m-%d'') between att_start_end_fn(pyear_month,1) and att_start_end_fn(pyear_month,2); insert into device_attandance (company_code, branch_code, DEVICELOGID, DOWNLOADDATE, DEVICEID, device_USERID, emp_id, LOGDATE, DIRECTION, ATTDIRECTION, C1, C2, C3, C4, C5, C6, C7, WORKCODE, status) select company_code, branch_code, DEVICELOGID, DOWNLOADDATE, DEVICEID, device_USERID, emp_id, LOGDATE, DIRECTION, ATTDIRECTION, C1, C2, C3, C4, C5, C6, C7, WORKCODE, status from temp_device_attandance order by LOGDATE; drop temporary table if exists temp_device_attandance; return ''success''; end;;'),
        (db_name, 'delimiter ;');
        
    end loop;
    
    close db_cursor;
    
    select sql_command from deployment_commands order by step_order;
end;;

DROP PROCEDURE IF EXISTS `inactive_db_audit_prc`;;
CREATE PROCEDURE `inactive_db_audit_prc` ()
begin
  declare done int default false;
  declare db_name varchar(255);
  declare comp_code varchar(255);
  declare comp_name varchar(255);
  declare record_count int;
  declare cur cursor for 
    select company_code, company_name, user_db 
    from central_control as cc
    join information_schema.tables as ist on cc.user_db = ist.table_schema
    where cc.user_db is not null 
    and cc.user_db <> '' 
    and ist.table_name = 'report_audit'
    and cc.user_db not like '%_moved' 
    and cc.user_db not like '%#blocked'
	and cc.start_date_effective < date_sub(current_date, interval 6 month);
  declare continue handler for not found set done = true;
  
  drop temporary table if exists temp_inactive_dbs;
  create temporary table temp_inactive_dbs (
    company_code varchar(255),
    company_name varchar(255),
    database_name varchar(255),
    rec_count   int    
  );
  
  open cur;
  read_loop: loop
    fetch cur into comp_code, comp_name, db_name;
    if done then
      leave read_loop;
    end if;
    
   -- set @sql = concat('select count(*) into @record_count from ', db_name, '.report_audit where status = 1 and creation_date >= date_sub(current_date, interval 6 month)');
  set @sql = concat('select count(*) into @record_count from ', db_name, '.device_attandance where LOGDATE < current_date-90 ');
    prepare stmt from @sql;
    execute stmt;
    deallocate prepare stmt;
    
    if @record_count > 0 then
      insert into temp_inactive_dbs values (comp_code, comp_name, db_name,@record_count);
    end if;
  end loop;
  close cur;
  
  select * from temp_inactive_dbs;
  drop temporary table temp_inactive_dbs;
end;;

DROP PROCEDURE IF EXISTS `insert_reportcriterias_all`;;
CREATE PROCEDURE `insert_reportcriterias_all` ()
BEGIN
    DECLARE done INT DEFAULT 0;
    DECLARE v_db_name VARCHAR(100);

    DECLARE cur CURSOR FOR
        SELECT cc.user_db
        FROM central_control cc
        JOIN information_schema.tables ist
            ON cc.user_db = ist.table_schema
        WHERE cc.user_db IS NOT NULL
            AND cc.user_db <> ''
            AND ist.table_name = 'reportcriterias'
            AND cc.user_db NOT LIKE '%_moved'
            AND cc.user_db NOT LIKE '%#blocked';

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN cur;

    read_loop: LOOP
        FETCH cur INTO v_db_name;
        IF done THEN
            LEAVE read_loop;
        END IF;

        -- Insert Row 1 (if not exists)
        SET @sql1 = CONCAT(
            'INSERT INTO `', v_db_name, '`.`reportcriterias` ',
            '(reporttype, reportcriteria, reportcriteria_desc, reportcriteria_field, status) ',
            'SELECT ''Account'', ''Units'', ''belonging to a Branch'', ''emp_branch'', 1 ',
            'FROM DUAL WHERE NOT EXISTS (',
                'SELECT 1 FROM `', v_db_name, '`.`reportcriterias` ',
                'WHERE reporttype = ''Account'' ',
                'AND reportcriteria = ''Units'' ',
                'AND reportcriteria_field = ''emp_branch''',
            ');'
        );

        PREPARE stmt1 FROM @sql1;
        EXECUTE stmt1;
        DEALLOCATE PREPARE stmt1;

        -- Insert Row 2 (if not exists)
        SET @sql2 = CONCAT(
            'INSERT INTO `', v_db_name, '`.`reportcriterias` ',
            '(reporttype, reportcriteria, reportcriteria_desc, reportcriteria_field, status) ',
            'SELECT ''Account'', ''EmployeeDetails'', ''belonging to a Employee'', ''emp_pkey'', 1 ',
            'FROM DUAL WHERE NOT EXISTS (',
                'SELECT 1 FROM `', v_db_name, '`.`reportcriterias` ',
                'WHERE reporttype = ''Account'' ',
                'AND reportcriteria = ''EmployeeDetails'' ',
                'AND reportcriteria_field = ''emp_pkey''',
            ');'
        );

        PREPARE stmt2 FROM @sql2;
        EXECUTE stmt2;
        DEALLOCATE PREPARE stmt2;

    END LOOP;

    CLOSE cur;

END;;

DROP PROCEDURE IF EXISTS `insert_reportcriterias_lop`;;
CREATE PROCEDURE `insert_reportcriterias_lop` ()
BEGIN
    DECLARE done INT DEFAULT 0;
    DECLARE v_db_name VARCHAR(100);
    DECLARE cur CURSOR FOR
        SELECT cc.user_db
        FROM central_control cc
        JOIN information_schema.tables ist
            ON cc.user_db = ist.table_schema
        WHERE cc.user_db IS NOT NULL
            AND cc.user_db <> ''
            AND ist.table_name = 'reportcriterias'
            AND cc.user_db NOT LIKE '%_moved'
            AND cc.user_db NOT LIKE '%#blocked'
            AND cc.company_code NOT IN (
                'AIMA','VGFS','VSFS','ABSG','MBCT','DRRC','SRTS','MRBS',
                'KWMT','DJIC','HRBL','STCL','ESNP','AGNG','VGNN','AYRK',
                'SHYD','GTRA','GLET'
            );
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
    OPEN cur;
    read_loop: LOOP
        FETCH cur INTO v_db_name;
        IF done THEN
            LEAVE read_loop;
        END IF;

        SET @sql1 = CONCAT(
            'INSERT INTO `', v_db_name, '`.`reportcriterias` ',
            '(`reporttype`, `reportcriteria`, `reportcriteria_desc`, `reportcriteria_field`, `status`) ',
            'SELECT ''LOPReport'', ''EmployeeDetails'', ''Belonging to a Employee'', ''emp_pkey'', 1 ',
            'FROM DUAL WHERE NOT EXISTS (',
                'SELECT 1 FROM `', v_db_name, '`.`reportcriterias` ',
                'WHERE `reporttype` = ''LOPReport'' AND `reportcriteria` = ''EmployeeDetails''',
            ');'
        );
        PREPARE stmt FROM @sql1;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;

        SET @sql2 = CONCAT(
            'INSERT INTO `', v_db_name, '`.`reportcriterias` ',
            '(`reporttype`, `reportcriteria`, `reportcriteria_desc`, `reportcriteria_field`, `status`) ',
            'SELECT ''LOPReport'', ''Units'', ''Belonging to a Branch'', ''branch_code'', 1 ',
            'FROM DUAL WHERE NOT EXISTS (',
                'SELECT 1 FROM `', v_db_name, '`.`reportcriterias` ',
                'WHERE `reporttype` = ''LOPReport'' AND `reportcriteria` = ''Units''',
            ');'
        );
        PREPARE stmt FROM @sql2;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;

    END LOOP;
    CLOSE cur;
END;;

DROP PROCEDURE IF EXISTS `insert_reportcriteria_salarystructures`;;
CREATE PROCEDURE `insert_reportcriteria_salarystructures` ()
BEGIN
    DECLARE done INT DEFAULT 0;
    DECLARE v_db_name VARCHAR(100);

    DECLARE cur CURSOR FOR
        SELECT cc.user_db
        FROM central_control cc
        JOIN information_schema.tables ist
            ON cc.user_db = ist.table_schema
        WHERE cc.user_db IS NOT NULL
            AND cc.user_db <> ''
            AND ist.table_name = 'reportcriterias'
            AND cc.user_db NOT LIKE '%_moved'
            AND cc.user_db NOT LIKE '%#blocked'
            AND cc.company_code NOT IN (
                'HRBL','KWMT','AIMA','ESNP','MBCT','MRBS','STCL','VGNN','ABSG','VGFS','VSFS','DRRC','DJIC','AGNG','AYRK','SRTS','SHYD','GTRA'
            );

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN cur;

    read_loop: LOOP
        FETCH cur INTO v_db_name;
        IF done THEN
            LEAVE read_loop;
        END IF;

        -- Check if already exists
        SET @check_sql = CONCAT(
            'SELECT COUNT(*) INTO @exists FROM `', v_db_name, '`.`reportcriterias` ',
            'WHERE reporttype = ''salarystructures'' ',
            'AND reportcriteria = ''SalaryStructures'''
        );

        PREPARE stmt1 FROM @check_sql;
        EXECUTE stmt1;
        DEALLOCATE PREPARE stmt1;

        -- Insert only if not exists
        IF @exists = 0 THEN

            SET @insert_sql = CONCAT(
                'INSERT INTO `', v_db_name, '`.`reportcriterias` ',
                '(reporttype, reportcriteria, reportcriteria_desc, reportcriteria_field, status) VALUES ',
                '(''salarystructures'', ''SalaryStructures'', ',
                '''belonging to a Salary Structure'', ''structure_id'', 1)'
            );

            PREPARE stmt2 FROM @insert_sql;
            EXECUTE stmt2;
            DEALLOCATE PREPARE stmt2;

        END IF;

    END LOOP;

    CLOSE cur;

END;;

DROP PROCEDURE IF EXISTS `leave_end_process_parent_prc`;;
CREATE PROCEDURE `leave_end_process_parent_prc` ()
begin
	declare vcompany_code varchar(30);
	declare vcompany_name varchar(200);
	declare vuser_db varchar(100);
	
	declare exit_loop boolean default false;
	declare db_error boolean default false;
	
	declare company_cur cursor for
		select cd.company_code, cc.company_name, cc.user_db from central_control as cc join cron_dbs as cd on cc.company_code = cd.company_code
		where cc.user_db is not null and cc.user_db <> '' and cc.user_db not like '%_moved' and cc.user_db not like '%#blocked'
		and cd.start_date is not null and cd.start_date <> '0000-00-00' and current_date >= cd.start_date
		and (cd.end_date is null or cd.end_date = '0000-00-00' or current_date <= cd.end_date);
	
	declare continue handler for not found set exit_loop = true;
	declare continue handler for sqlexception
	begin
		set db_error = true;
	end;
	
	open company_cur;
	start_loop: loop
		fetch company_cur into vcompany_code, vcompany_name, vuser_db;
		
		if exit_loop then
			leave start_loop;
		end if;
		
		set db_error = false;
		set @pout = null;
		set @dyn_sql := concat('select ', vuser_db, '.`leave_end_process_fn`() into @pout');
		prepare stmt from @dyn_sql;
		execute stmt;
		deallocate prepare stmt;
		
		if db_error then
			insert into cron_log (company_code, company_name, status, remarks) values (vcompany_code, vcompany_name, 'failure', concat('leave end process - DB not found or function missing: ', vuser_db));
			iterate start_loop;
		end if;
		
		if @pout is null then
			set @pout = 'failure';
		end if;
		
		insert into cron_log (company_code, company_name, status, remarks) values (vcompany_code, vcompany_name, @pout, 'leave_end_process_fn automated execution');
	end loop start_loop;
	close company_cur;
end;;

DROP FUNCTION IF EXISTS `single_signon_fn`;;
CREATE FUNCTION `single_signon_fn` (`pstring` char(255), `pcompany_code` char(25), `pemp_coid` char(50), `pemp_email` varchar(50) CHARACTER SET 'utf8', `pkeyvalue` char(50)) RETURNS char(255) CHARACTER SET 'utf8' LANGUAGE SQL
READS SQL DATA
    DETERMINISTIC
BEGIN
declare vcount int;
declare vuser_db  varchar(30);
DECLARE vreturn varchar(255);
declare vemp_username varchar(100);
DECLARE vemp_pkey int;
   select count(*),user_db into vcount,vuser_db from central_control where company_code=pcompany_code 
        and concat(third_party,control_pkey)=pkeyvalue;
 
  if vcount>0 then 
   select count(*)vcount,emp_username into vcount,vemp_username  from emp_device_comp_branch  
     where email=ifnull(pemp_email,'1') and Company_code=pcompany_code and status=1;
    if  vcount>0 then
     set vreturn= CONCAT(vuser_db, '||', vemp_username );
    else
    set vreturn= '0';
    end if;
   ELSE
   set vreturn= vcount;
  end if;
 
INSERT INTO `sso_audit` (`string`, `company_code`, `emp_comp_id`, `emp_email`, `keyvalue`, `return_mesg`) VALUES
(pstring,	pcompany_code,	pemp_coid,	pemp_email,	pkeyvalue,vreturn);

return vreturn;
 
END;;

DROP FUNCTION IF EXISTS `trial_signup_fn`;;
CREATE FUNCTION `trial_signup_fn` (`ppayroll_signup_pkey` varchar(200)) RETURNS varchar(200) CHARACTER SET 'latin1' LANGUAGE SQL
READS SQL DATA
    DETERMINISTIC
begin

declare vuser_Password varchar(200);
declare		vcompany_code  varchar(20); 
declare		vcompany_name varchar(200); 
declare		vAddress	   varchar(500);
declare		vadmin_email	 varchar(50);
declare		vadmin_phone varchar(50);
declare		vadmin_name varchar(50);
declare     vadmin_password  varchar(100);
declare     vuser_db varchar(100);
declare 	vemail_count  int;
declare     vcompany_count  int;
declare     vdb_count  int;
declare     vcontrol_pkey int;


select count(*) into vemail_count from payroll_signup where admin_email = vadmin_email and admin_email not like '%forsightgroup%';

select count(*) into vcompany_count from central_control where company_code = vcompany_code;

select count(*) into vdb_count  from central_control where trial_status='A';

if vemail_count=0 and vcompany_count=0 and vdb_count!=0 then

		select company_code,	 
		company_name	,	 
		Address	 ,
		admin_email	 ,
		admin_phone ,
		admin_name ,admin_password into vcompany_code,	 
		vcompany_name	,	 
		vAddress	 ,
		vadmin_email	 ,
		vadmin_phone ,
		vadmin_name,vadmin_password from payroll_signup
		where payroll_signup_pkey=ppayroll_signup_pkey;

		set vadmin_password='ae227f52e3dbd764815da2e23056fc27d577421c';
select min(control_pkey) ,user_db  into vcontrol_pkey,vuser_db  from central_control where trial_status='A';
  
  update central_control set company_code=vcompany_code,  company_name=vcompany_name	  ,Address= vAddress,
        created_date=current_date,	start_date_effective  =current_date,trial_status='P',attr1=ppayroll_signup_pkey
    where control_pkey=vcontrol_pkey;

 update user_credentials set company_code= vcompany_code, user_id=vadmin_email  ,password=vadmin_password, 
 email=vadmin_email,   phone=vadmin_phone,  reset_login_flag='Y',  first_name=vadmin_name,start_date= current_date
 where control_fkey=vcontrol_pkey and ucase(user_id) like '%trialadmin%' ;

update user_credentials set company_code= vcompany_code, start_date= current_date
 where control_fkey=vcontrol_pkey and ucase(user_id) like '%support%' ;



    
  update payroll_signup set user_db= vuser_db ,trial_status='P' where payroll_signup_pkey=ppayroll_signup_pkey;

 set vuser_Password= 'Your Database Successfully Created and Please proceed with your email from forsightgroup';

else
if vemail_count!=0 then
 set vuser_Password='Email Already Exist!!!!! Please contact + 91 999 532 5909';
end if;

if  vcompany_count!=0 then
set vuser_Password='Company Code Already Exist!!!!! Please contact + 91 999 532 5909';
end if;

if  vdb_count =0 then
set vuser_Password='Can not Process right Now !!!!! Please contact + 91 999 532 5909';
end if;

end if;
RETURN  vuser_Password ;
END;;

DROP PROCEDURE IF EXISTS `update_doc_template_columns`;;
CREATE PROCEDURE `update_doc_template_columns` ()
BEGIN

    DECLARE done INT DEFAULT 0;
    DECLARE v_db_name VARCHAR(100);

    DECLARE cur CURSOR FOR
        SELECT cc.user_db
        FROM central_control cc
        JOIN information_schema.tables ist
            ON cc.user_db = ist.table_schema
        WHERE cc.user_db IS NOT NULL
            AND cc.user_db <> ''
            AND ist.table_name = 'doc_template'
            AND cc.user_db NOT LIKE '%_moved'
            AND cc.user_db NOT LIKE '%#blocked'
            AND cc.company_code NOT IN (
                'AIMA',
                'VGFS',
                'VSFS',
                'ABSG',
                'MBCT',
                'DRRC',
                'SRTS',
                'MRBS',
                'KWMT',
                'DJIC',
                'HRBL',
                'STCL',
                'ESNP',
                'AGNG',
                'VGNN',
                'AYRK'
            );

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN cur;

    read_loop: LOOP

        FETCH cur INTO v_db_name;

        IF done = 1 THEN
            LEAVE read_loop;
        END IF;

        -- Check header_image column
        SELECT COUNT(*) INTO @header_exists
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = v_db_name
            AND TABLE_NAME = 'doc_template'
            AND COLUMN_NAME = 'header_image';

        -- Check footer_image column
        SELECT COUNT(*) INTO @footer_exists
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = v_db_name
            AND TABLE_NAME = 'doc_template'
            AND COLUMN_NAME = 'footer_image';

        SET @alter_query = '';

        -- Add header_image if missing
        IF @header_exists = 0 THEN
            SET @alter_query = CONCAT(
                @alter_query,
                ' ADD COLUMN header_image VARCHAR(500) DEFAULT NULL'
            );
        END IF;

        -- Add footer_image if missing
        IF @footer_exists = 0 THEN

            IF @alter_query <> '' THEN
                SET @alter_query = CONCAT(@alter_query, ',');
            END IF;

            SET @alter_query = CONCAT(
                @alter_query,
                ' ADD COLUMN footer_image VARCHAR(500) DEFAULT NULL'
            );

        END IF;

        -- Execute ALTER TABLE
        IF @alter_query <> '' THEN

            SET @sql = CONCAT(
                'ALTER TABLE `',
                v_db_name,
                '`.`doc_template` ',
                @alter_query
            );

            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;

        END IF;

    END LOOP;

    CLOSE cur;

END;;

DROP PROCEDURE IF EXISTS `update_emp_details_status`;;
CREATE PROCEDURE `update_emp_details_status` ()
BEGIN
    DECLARE done INT DEFAULT 0;
    DECLARE v_db_name VARCHAR(100);

    DECLARE cur CURSOR FOR
        SELECT cc.user_db
        FROM central_control cc
        JOIN information_schema.tables ist
            ON cc.user_db = ist.table_schema
        WHERE cc.user_db IS NOT NULL
            AND cc.user_db <> ''
            AND ist.table_name = 'emp_details'
            AND cc.user_db NOT LIKE '%_moved'
            AND cc.user_db NOT LIKE '%#blocked'
            AND cc.company_code NOT IN (
            'AIMA',
            'VGFS',
            'VSFS',
            'ABSG',
            'MBCT',
            'DRRC',
            'SRTS',
            'MRBS',
            'KWMT',
            'DJIC',
            'HRBL',
            'STCL',
            'ESNP',
            'AGNG',
            'VGNN',
            'AYRK'
            );

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN cur;

    read_loop: LOOP
        FETCH cur INTO v_db_name;
        IF done THEN
            LEAVE read_loop;
        END IF;

        -- Check if status column exists
        SELECT COUNT(*) INTO @col_exists
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = v_db_name
            AND TABLE_NAME = 'emp_details'
            AND COLUMN_NAME = 'status';

        IF @col_exists > 0 THEN

            SET @sql = CONCAT(
                'UPDATE `', v_db_name, '`.`emp_details` ',
                'SET status = 2 WHERE status = 0;'
            );

            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;

        END IF;

    END LOOP;

    CLOSE cur;

END;;

DROP PROCEDURE IF EXISTS `update_income_tax_slab_2026`;;
CREATE PROCEDURE `update_income_tax_slab_2026` ()
begin
    declare done int default 0;
    declare v_db_name varchar(100);
    declare v_err_msg varchar(500);
    declare cur cursor for
        select cc.user_db
        from central_control cc
        join information_schema.tables ist
            on cc.user_db = ist.table_schema
        where cc.user_db is not null
        and cc.user_db <> ''
        and ist.table_name = 'income_tax_slab'
        and cc.user_db not like '%_moved'
        and cc.user_db not like '%#blocked';

    declare continue handler for not found set done = 1;

    open cur;
    read_loop: loop
        fetch cur into v_db_name;
        if done then
            leave read_loop;
        end if;

        select count(*) into @col_exists
        from information_schema.columns
        where table_schema = v_db_name
        and table_name = 'income_tax_slab'
        and column_name in ('creation_date', 'modified_date');

        if @col_exists = 2 then
            begin
                declare exit handler for sqlexception
                begin
                    get diagnostics condition 1 v_err_msg = message_text;
                    insert into `mypayrol_control_db`.`procedure_run_log`
                        (`procedure_name`, `db_name`, `status`, `error_message`)
                    values
                        ('update_income_tax_slab_2026', v_db_name, 'failed', concat('alter failed: ', v_err_msg));
                end;

                set @alter_sql = concat(
                    'alter table `', v_db_name, '`.`income_tax_slab` ',
                    'change `creation_date` `creation_date` datetime not null default current_timestamp after `created_by`, ',
                    'change `modified_date` `modified_date` datetime null on update current_timestamp after `modified_by`;'
                );
                prepare alter_stmt from @alter_sql;
                execute alter_stmt;
                deallocate prepare alter_stmt;
            end;
        end if;

        begin
            declare exit handler for sqlexception
            begin
                get diagnostics condition 1 v_err_msg = message_text;
                rollback;
                insert into `mypayrol_control_db`.`procedure_run_log`
                    (`procedure_name`, `db_name`, `status`, `error_message`)
                values
                    ('update_income_tax_slab_2026', v_db_name, 'failed', concat('dml failed: ', v_err_msg));
            end;

            start transaction;

            select count(*) into @slab_2025_exists
            from information_schema.tables
            where table_schema = v_db_name
            and table_name = 'income_tax_slab';

            if @slab_2025_exists > 0 then
                set @check_2025 = concat(
                    'select count(*) into @has_2025 from `', v_db_name, '`.`income_tax_slab` ',
                    'where fin_year = ''2025'' and regime = ''NEW'';'
                );
                prepare check_2025_stmt from @check_2025;
                execute check_2025_stmt;
                deallocate prepare check_2025_stmt;

                if @has_2025 > 0 then
                    set @upd_2025 = concat(
                        'update `', v_db_name, '`.`income_tax_slab` ',
                        'set end_date_effective = ''2026-03-31'' ',
                        'where regime = ''NEW'' and fin_year = ''2025'';'
                    );
                    prepare upd_2025_stmt from @upd_2025;
                    execute upd_2025_stmt;
                    deallocate prepare upd_2025_stmt;
                end if;

                set @check_2026 = concat(
                    'select count(*) into @has_2026 from `', v_db_name, '`.`income_tax_slab` ',
                    'where fin_year = ''2026'' and regime = ''NEW'';'
                );
                prepare check_2026_stmt from @check_2026;
                execute check_2026_stmt;
                deallocate prepare check_2026_stmt;

                if @has_2026 = 0 then
                    set @ins_2026 = concat(
                        'insert into `', v_db_name, '`.`income_tax_slab` ',
                        '(`fin_year`,`regime`,`salary_range_from`,`salary_range_to`,`tax_yearly_perc`,`std_deduction`,`surcharge_perc`,`rebate`,`cess_perc`,`start_date_effective`,`end_date_effective`,`created_by`,`status`) values ',
                        '(2026,''NEW'',0,400000,0,0,0,60000,0,''2026-04-01'',NULL,''Admin'',1),',
                        '(2026,''NEW'',400001,800000,5,0,0,60000,0,''2026-04-01'',NULL,''Admin'',1),',
                        '(2026,''NEW'',800001,1200000,10,0,0,60000,0,''2026-04-01'',NULL,''Admin'',1),',
                        '(2026,''NEW'',1200001,1600000,15,75000,0,0,4,''2026-04-01'',NULL,''Admin'',1),',
                        '(2026,''NEW'',1600001,2000000,20,75000,0,0,4,''2026-04-01'',NULL,''Admin'',1),',
                        '(2026,''NEW'',2000001,2400000,25,75000,0,0,4,''2026-04-01'',NULL,''Admin'',1),',
                        '(2026,''NEW'',2400001,5000000,30,75000,0,0,4,''2026-04-01'',NULL,''Admin'',1),',
                        '(2026,''NEW'',5000001,10000000,30,75000,10,0,4,''2026-04-01'',NULL,''Admin'',1),',
                        '(2026,''NEW'',10000001,20000000,30,75000,15,0,4,''2026-04-01'',NULL,''Admin'',1),',
                        '(2026,''NEW'',20000001,50000000,30,75000,25,0,4,''2026-04-01'',NULL,''Admin'',1),',
                        '(2026,''NEW'',50000001,2147483647,30,75000,25,0,4,''2026-04-01'',NULL,''Admin'',1);'
                    );
                    prepare ins_2026_stmt from @ins_2026;
                    execute ins_2026_stmt;
                    deallocate prepare ins_2026_stmt;
                end if;
            end if;

            commit;

            insert into `mypayrol_control_db`.`procedure_run_log`
                (`procedure_name`, `db_name`, `status`, `error_message`)
            values
                ('update_income_tax_slab_2026', v_db_name, 'success', null);
        end;

    end loop;
    close cur;
end;;

DELIMITER ;

SET NAMES utf8mb4;

CREATE TABLE `attendance` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `auditor_fkey` int(11) unsigned NOT NULL,
  `userid` varchar(30) NOT NULL,
  `company_code` varchar(30) NOT NULL,
  `time_check` datetime NOT NULL COMMENT 'Check In / Check Out Time',
  `in_out` varchar(10) NOT NULL COMMENT 'in / out',
  `location` varchar(500) NOT NULL,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  `uploaded_time` datetime NOT NULL COMMENT 'Sync Time With Server',
  `loc_source` varchar(50) NOT NULL,
  `stay_back` varchar(10) DEFAULT NULL,
  `punch_status` varchar(20) NOT NULL,
  `punch_queued_at` datetime(3) DEFAULT NULL,
  `punch_processed_at` datetime(3) DEFAULT NULL,
  `location_status` varchar(10) NOT NULL,
  `queue_sync_message` varchar(700) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `attendancelogs` (
  `ATTENDANCELOGID` int(10) NOT NULL AUTO_INCREMENT,
  `ATTENDANCEDATE` date NOT NULL,
  `EMPLOYEEID` varchar(20) NOT NULL,
  `BRANCH_CODE` varchar(25) DEFAULT NULL,
  `COMPANY_CODE` varchar(25) DEFAULT NULL,
  `INTIME` varchar(255) DEFAULT NULL,
  `INDEVICEID` varchar(255) DEFAULT NULL,
  `OUTTIME` varchar(255) DEFAULT NULL,
  `OUTDEVICEID` varchar(255) DEFAULT NULL,
  `DURATION` int(126) DEFAULT NULL,
  `LATEBY` int(10) DEFAULT NULL,
  `EARLYBY` int(10) DEFAULT NULL,
  `ISONLEAVE` int(10) DEFAULT NULL,
  `LEAVETYPEID` int(10) DEFAULT NULL,
  `LEAVETYPE` varchar(50) DEFAULT NULL,
  `LEAVEDURATION` int(126) DEFAULT NULL,
  `LEAVESTATUS` int(18) DEFAULT NULL,
  `LEAVEREMARKS` varchar(1000) DEFAULT NULL,
  `ISONSPECIALOFF` int(10) DEFAULT NULL,
  `SPECIALOFFTYPE` varchar(255) DEFAULT NULL,
  `SPECIALOFFREMARK` varchar(1000) DEFAULT NULL,
  `SPECIALOFFDURATION` int(10) DEFAULT NULL,
  `WEEKLYOFF` int(10) DEFAULT NULL,
  `HOLIDAY` int(10) DEFAULT NULL,
  `PUNCHRECORDS` mediumtext CHARACTER SET utf8,
  `PUNCHDIRECTIONS` varchar(500) DEFAULT NULL,
  `PUNCHDEVICESNAME` varchar(500) DEFAULT NULL,
  `SHIFTID` int(10) DEFAULT NULL,
  `PRESENT` int(126) DEFAULT NULL,
  `ABSENT` int(126) DEFAULT NULL,
  `DETAILEDSTATUS` varchar(500) DEFAULT NULL,
  `STATUS` varchar(255) DEFAULT NULL,
  `DETAILEDSTATUSCODE` varchar(500) DEFAULT NULL,
  `STATUSCODE` varchar(255) DEFAULT NULL,
  `P1STATUS` varchar(255) DEFAULT NULL,
  `P2STATUS` varchar(255) DEFAULT NULL,
  `P3STATUS` varchar(255) DEFAULT NULL,
  `OVERTIME` int(10) DEFAULT NULL,
  `OVERTIMEE` int(10) DEFAULT NULL,
  `MISSEDOUTPUNCH` int(10) DEFAULT NULL,
  `MISSEDINPUNCH` int(10) DEFAULT NULL,
  `REMARKS` varchar(1000) DEFAULT NULL,
  `ISONRESTRICTEDHOLIDAY` int(10) DEFAULT NULL,
  `ISONCOMPOFF` int(10) DEFAULT NULL,
  `ISPARTIALDAY` int(10) DEFAULT NULL,
  `REPORTPUNCHRECORDS` varchar(500) DEFAULT NULL,
  `SMSFLAG` int(10) DEFAULT NULL,
  `COMPOFF` int(10) DEFAULT NULL,
  `created_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ATTENDANCELOGID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `central_control` (
  `control_pkey` int(11) NOT NULL AUTO_INCREMENT,
  `company_code` varchar(20) NOT NULL,
  `company_name` varchar(200) NOT NULL,
  `Address` varchar(500) NOT NULL,
  `Admin_name` varchar(100) NOT NULL,
  `user_db` varchar(100) NOT NULL,
  `user_pwd` varchar(100) NOT NULL,
  `created_date` datetime NOT NULL,
  `start_date_effective` date NOT NULL,
  `end_date_effective` date NOT NULL,
  `product` varchar(200) NOT NULL,
  `active` varchar(10) NOT NULL,
  `custom_message` varchar(500) NOT NULL,
  `redirect_url` varchar(200) NOT NULL,
  `country_code` varchar(3) NOT NULL,
  `currency_code` varchar(3) NOT NULL,
  `punch_type` varchar(20) NOT NULL COMMENT 'device/manual',
  `attr1` varchar(200) NOT NULL,
  `attr2` varchar(200) NOT NULL,
  `attr3` varchar(200) NOT NULL,
  `attr4` varchar(200) NOT NULL,
  `attr5` varchar(200) NOT NULL,
  `attr6` varchar(200) NOT NULL,
  `attr7` varchar(200) NOT NULL,
  `trial_status` char(1) NOT NULL,
  `subdomain` varchar(30) DEFAULT NULL,
  `third_party` varchar(30) DEFAULT NULL,
  `web_url` varchar(400) DEFAULT 'https://login.mypayrollmaster.online/',
  `api_url` varchar(400) DEFAULT 'https://apps.office24.online/forsight/api/v1/',
  `app_url` varchar(400) DEFAULT 'https://v1.mypayrollmaster.online/api/v3/',
  `biometric_url` varchar(400) DEFAULT NULL,
  `plan` varchar(40) DEFAULT 'standerd',
  PRIMARY KEY (`control_pkey`),
  UNIQUE KEY `company_code` (`company_code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `central_control_bck29092025` (
  `control_pkey` int(11) NOT NULL DEFAULT '0',
  `company_code` varchar(20) NOT NULL,
  `company_name` varchar(200) NOT NULL,
  `Address` varchar(500) NOT NULL,
  `Admin_name` varchar(100) NOT NULL,
  `user_db` varchar(100) NOT NULL,
  `user_pwd` varchar(100) NOT NULL,
  `created_date` datetime NOT NULL,
  `start_date_effective` date NOT NULL,
  `end_date_effective` date NOT NULL,
  `product` varchar(200) NOT NULL,
  `active` varchar(10) NOT NULL,
  `custom_message` varchar(500) NOT NULL,
  `redirect_url` varchar(200) NOT NULL,
  `country_code` varchar(3) NOT NULL,
  `currency_code` varchar(3) NOT NULL,
  `punch_type` varchar(20) NOT NULL COMMENT 'device/manual',
  `attr1` varchar(200) NOT NULL,
  `attr2` varchar(200) NOT NULL,
  `attr3` varchar(200) NOT NULL,
  `attr4` varchar(200) NOT NULL,
  `attr5` varchar(200) NOT NULL,
  `attr6` varchar(200) NOT NULL,
  `attr7` varchar(200) NOT NULL,
  `trial_status` char(1) NOT NULL,
  `subdomain` varchar(30) DEFAULT NULL,
  `third_party` varchar(30) DEFAULT NULL,
  `web_url` varchar(400) DEFAULT 'https://login.mypayrollmaster.online/',
  `api_url` varchar(400) DEFAULT 'https://apps.office24.online/forsight/api/v1/',
  `biometric_url` varchar(400) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `company_addons` (
  `company_addon_id` int(11) NOT NULL AUTO_INCREMENT,
  `company_code` varchar(100) NOT NULL,
  `feature_id` int(11) NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`company_addon_id`),
  UNIQUE KEY `company_code` (`company_code`,`feature_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `company_branches` (
  `branch_seq` int(11) NOT NULL AUTO_INCREMENT,
  `company_code` varchar(20) NOT NULL,
  `branch_code` varchar(20) NOT NULL,
  `branch_name` varchar(200) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`branch_seq`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `company_branches_bck29092025` (
  `branch_seq` int(11) NOT NULL DEFAULT '0',
  `company_code` varchar(20) NOT NULL,
  `branch_code` varchar(20) NOT NULL,
  `branch_name` varchar(200) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `company_overrides` (
  `company_override_id` int(11) NOT NULL AUTO_INCREMENT,
  `company_code` varchar(100) NOT NULL,
  `type` enum('Plan','Addon') NOT NULL COMMENT 'Distinguishes between Plan and Feature',
  `item_id` int(11) NOT NULL COMMENT 'Matches plan_id or feature_id',
  `special_amount` decimal(10,2) NOT NULL COMMENT 'The base price for this company',
  `included_employees` int(11) DEFAULT NULL COMMENT 'Custom limit for this company',
  `extra_rate` decimal(10,2) DEFAULT NULL COMMENT 'Custom extra rate for this company',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`company_override_id`),
  UNIQUE KEY `company_code` (`company_code`,`type`,`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `control_back09092023` (
  `control_pkey` int(11) NOT NULL AUTO_INCREMENT,
  `company_code` varchar(20) NOT NULL,
  `company_name` varchar(200) NOT NULL,
  `Address` varchar(500) NOT NULL,
  `Admin_name` varchar(100) NOT NULL,
  `user_db` varchar(100) NOT NULL,
  `user_pwd` varchar(100) NOT NULL,
  `created_date` datetime NOT NULL,
  `start_date_effective` date NOT NULL,
  `end_date_effective` date NOT NULL,
  `product` varchar(200) NOT NULL,
  `active` varchar(10) NOT NULL,
  `custom_message` varchar(500) NOT NULL,
  `redirect_url` varchar(200) NOT NULL,
  `country_code` varchar(3) NOT NULL,
  `currency_code` varchar(3) NOT NULL,
  `punch_type` varchar(20) NOT NULL COMMENT 'device/manual',
  `attr1` varchar(200) NOT NULL,
  `attr2` varchar(200) NOT NULL,
  `attr3` varchar(200) NOT NULL,
  `attr4` varchar(200) NOT NULL,
  `attr5` varchar(200) NOT NULL,
  `attr6` varchar(200) NOT NULL,
  `attr7` varchar(200) NOT NULL,
  `trial_status` char(1) NOT NULL,
  `subdomain` varchar(30) DEFAULT NULL,
  `third_party` varchar(30) DEFAULT NULL,
  `web_url` varchar(400) DEFAULT 'https://login.mypayrollmaster.online/',
  `api_url` varchar(400) DEFAULT 'https://apps.office24.online/forsight/api/v1/',
  PRIMARY KEY (`control_pkey`),
  UNIQUE KEY `company_code` (`company_code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `countries` (
  `id_countries` int(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `iso_alpha2` varchar(2) DEFAULT NULL,
  `country_code` varchar(3) DEFAULT NULL,
  `iso_numeric` int(11) DEFAULT NULL,
  `currency_code` char(3) DEFAULT NULL,
  `currency_name` varchar(32) DEFAULT NULL,
  `currrency_symbol` varchar(3) DEFAULT NULL,
  `flag` varchar(6) DEFAULT NULL,
  `phone_code` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id_countries`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `countries_nationality` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_code` varchar(2) DEFAULT NULL,
  `country_name` varchar(100) DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `countries_only` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_code` varchar(2) NOT NULL DEFAULT '',
  `country_name` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `cron_dbs` (
  `cron_dbs_pkey` int(11) NOT NULL AUTO_INCREMENT,
  `company_code` varchar(20) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `remarks` text,
  PRIMARY KEY (`cron_dbs_pkey`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `cron_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_code` varchar(30) NOT NULL,
  `company_name` varchar(200) DEFAULT NULL,
  `run_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(50) DEFAULT NULL,
  `remarks` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `customervisit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_code` varchar(10) NOT NULL,
  `customer_visit_pkey` varchar(10) NOT NULL,
  `report_date` date NOT NULL,
  `api_hit_count` int(11) NOT NULL,
  `balance_count` int(11) NOT NULL,
  `created_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `customer_visit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mob_location_pkey` int(11) NOT NULL,
  `user_id` varchar(30) NOT NULL,
  `company_code` varchar(30) NOT NULL,
  `created_time` datetime NOT NULL,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  `location` varchar(100) NOT NULL,
  `uploaded_time` datetime NOT NULL,
  `stepinout` varchar(20) NOT NULL,
  `accuracy` int(11) NOT NULL,
  `customer_name` varchar(200) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `purpose` varchar(500) NOT NULL,
  `punch_status` varchar(40) NOT NULL,
  `punch_queued_at` datetime(3) DEFAULT NULL,
  `punch_processed_at` datetime(3) DEFAULT NULL,
  `location_status` varchar(40) NOT NULL,
  `queue_sync_message` varchar(700) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `customer_visit_09032026` (
  `id` int(11) NOT NULL DEFAULT '0',
  `company_code` varchar(10) NOT NULL,
  `customer_visit_pkey` varchar(10) NOT NULL,
  `report_date` date NOT NULL,
  `api_hit_count` int(11) NOT NULL,
  `balance_count` int(11) NOT NULL,
  `created_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `department` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dept_code` varchar(20) NOT NULL,
  `dept_name` varchar(100) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1' COMMENT '0 inactive 1 active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `designation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `desig_code` varchar(100) NOT NULL,
  `desig_name` varchar(100) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `devicelogs` (
  `DEVICELOGID` int(11) NOT NULL,
  `DOWNLOADDATE` datetime NOT NULL,
  `DEVICEID` int(11) NOT NULL,
  `USERID` varchar(100) NOT NULL,
  `LOGDATE` datetime NOT NULL,
  `DIRECTION` varchar(50) NOT NULL,
  `ATTDIRECTION` varchar(50) NOT NULL,
  `C1` varchar(100) NOT NULL,
  `C2` varchar(100) NOT NULL,
  `C3` varchar(100) NOT NULL,
  `C4` varchar(100) NOT NULL,
  `C5` varchar(100) NOT NULL,
  `C6` varchar(100) NOT NULL,
  `C7` varchar(100) NOT NULL,
  `WORKCODE` varchar(100) NOT NULL,
  UNIQUE KEY `DEVICEID` (`DEVICEID`,`USERID`,`LOGDATE`,`DIRECTION`,`C1`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DELIMITER ;;

CREATE TRIGGER `devicelogs_bi` BEFORE INSERT ON `devicelogs` FOR EACH ROW
begin
declare	vcompany_code varchar(20);
declare	vbranch_code varchar(20);
declare	vdbname  varchar(30);
declare	vempid varchar(30);
declare	vempid1 varchar(30);
declare vattandance_seq int;
declare vintime datetime;
   
   if new.DEVICEID in(3157,3162,3163,3158,3160,3166,3167,3168) then
      select distinct emp_id,company_code,branch_code into vempid ,vcompany_code,vbranch_code 
	  from mypayrol_control_db.emp_device_comp_branch 
   where emp_device_id=new.USERID and DEVICEID=new.DEVICEID limit 1;
  
  
   elseif new.DEVICEID in(3169,3165,3164) then 
   SELECT company_code, branch_code into vcompany_code,vbranch_code FROM mypayrol_control_db.devices where DeviceId=new.DEVICEID;
  
  select distinct emp_id into vempid from mypayrol_control_db.emp_device_comp_branch where company_code=vcompany_code and emp_device_id=new.USERID limit 1;
   
  else
  SELECT company_code, branch_code into vcompany_code,vbranch_code FROM mypayrol_control_db.devices where DeviceId=new.DEVICEID;
  
  select distinct emp_id into vempid from mypayrol_control_db.emp_device_comp_branch where company_code=vcompany_code and emp_ID=new.USERID;
 
  end if;
  if vcompany_code is null or vempid is null then  
     
	   INSERT INTO mypayrol_control_db.device_logs_error (`DEVICELOGID`, `DOWNLOADDATE`, `DEVICEID`, `USERID`, `LOGDATE`, `DIRECTION`, `ATTDIRECTION`, `C1`, `C2`, `C3`, `C4`, `C5`, `C6`, `C7`, `WORKCODE`) VALUES
   (new.DEVICELOGID,new.DOWNLOADDATE,new.DEVICEID,new.USERID,
       new.LOGDATE,new.DIRECTION,new.ATTDIRECTION,new.C1,vcompany_code,vempid,new.C4,new.C5,new.C6,new.C7,new.WORKCODE);
   end if;	  
	 
  
  if vempid is null then
      set vempid=new.USERID ;
  end if;	  

if vcompany_code='FORSS' then
insert into mypayrol_demo_db1.device_attandance(company_code,branch_code,DEVICELOGID,DOWNLOADDATE,DEVICEID,device_USERID,emp_id,LOGDATE,DIRECTION,
                                       ATTDIRECTION,C1,C2,C3,C4,C5,C6,C7,WORKCODE)
values (vcompany_code,vbranch_code,new.DEVICELOGID,new.DOWNLOADDATE,new.DEVICEID,new.USERID,vempid,
       new.LOGDATE,new.DIRECTION,new.ATTDIRECTION,new.C1,new.C2,new.C3,new.C4,new.C5,new.C6,new.C7,new.WORKCODE);

elseif vcompany_code='MRS' then
insert into mypayrol_marias_db.device_attandance(company_code,branch_code,DEVICELOGID,DOWNLOADDATE,DEVICEID,device_USERID,emp_id,LOGDATE,DIRECTION,
                                       ATTDIRECTION,C1,C2,C3,C4,C5,C6,C7,WORKCODE)
values (vcompany_code,vbranch_code,new.DEVICELOGID,new.DOWNLOADDATE,new.DEVICEID,new.USERID,vempid,
       new.LOGDATE,new.DIRECTION,new.ATTDIRECTION,new.C1,new.C2,new.C3,new.C4,new.C5,new.C6,new.C7,new.WORKCODE);
elseif vcompany_code='FORGS' then
select distinct emp_id into vempid from mypayrol_control_db.emp_device_comp_branch where company_code=vcompany_code and emp_device_id=new.USERID;
insert into mypayrol_forsight_db.device_attandance(company_code,branch_code,DEVICELOGID,DOWNLOADDATE,DEVICEID,device_USERID,emp_id,LOGDATE,DIRECTION, ATTDIRECTION,C1,C2,C3,C4,C5,C6,C7,WORKCODE)
values (vcompany_code,vbranch_code,new.DEVICELOGID,new.DOWNLOADDATE,new.DEVICEID,new.USERID,vempid,
       new.LOGDATE,new.DIRECTION,new.ATTDIRECTION,new.C1,new.C2,new.C3,new.C4,new.C5,new.C6,new.C7,new.WORKCODE);

insert into mypayrol_demo_db1.device_attandance(company_code,branch_code,DEVICELOGID,DOWNLOADDATE,DEVICEID,device_USERID,emp_id,LOGDATE,DIRECTION, ATTDIRECTION,C1,C2,C3,C4,C5,C6,C7,WORKCODE)
values ('DEMO','DEMO01',new.DEVICELOGID,new.DOWNLOADDATE,new.DEVICEID,new.USERID,vempid,
       new.LOGDATE,new.DIRECTION,new.ATTDIRECTION,new.C1,new.C2,new.C3,new.C4,new.C5,new.C6,new.C7,new.WORKCODE);

	   
elseif vcompany_code='DDP' then
insert into mypayrol_divoli_db.device_attandance(company_code,branch_code,DEVICELOGID,DOWNLOADDATE,DEVICEID,device_USERID,emp_id,LOGDATE,DIRECTION,
                                       ATTDIRECTION,C1,C2,C3,C4,C5,C6,C7,WORKCODE)
values (vcompany_code,vbranch_code,new.DEVICELOGID,new.DOWNLOADDATE,new.DEVICEID,new.USERID,vempid,
       new.LOGDATE,new.DIRECTION,new.ATTDIRECTION,new.C1,new.C2,new.C3,new.C4,new.C5,new.C6,new.C7,new.WORKCODE);
elseif vcompany_code='AMY' then
select distinct emp_id into vempid from mypayrol_control_db.emp_device_comp_branch where company_code=vcompany_code and emp_ID=new.USERID;
if vempid is null then
 select distinct emp_id into vempid from mypayrol_control_db.emp_device_comp_branch 
       where company_code=vcompany_code and emp_device_id=new.USERID and DEVICEID=new.DEVICEID limit 1 ;
end if;
insert into mypayrol_amy_db.device_attandance(company_code,branch_code,DEVICELOGID,DOWNLOADDATE,DEVICEID,device_USERID,emp_id,LOGDATE,DIRECTION,
                                       ATTDIRECTION,C1,C2,C3,C4,C5,C6,C7,WORKCODE)
values (vcompany_code,vbranch_code,new.DEVICELOGID,new.DOWNLOADDATE,new.DEVICEID,new.USERID,vempid,
       new.LOGDATE,new.DIRECTION,new.ATTDIRECTION,new.C1,new.C2,new.C3,new.C4,new.C5,new.C6,new.C7,new.WORKCODE);
	elseif vcompany_code='HEDG' then
insert into hedge_db.device_attandance(company_code,branch_code,DEVICELOGID,DOWNLOADDATE,DEVICEID,device_USERID,emp_id,LOGDATE,DIRECTION,
                                       ATTDIRECTION,C1,C2,C3,C4,C5,C6,C7,WORKCODE)
values (vcompany_code,vbranch_code,new.DEVICELOGID,new.DOWNLOADDATE,new.DEVICEID,new.USERID,vempid,
       new.LOGDATE,new.DIRECTION,new.ATTDIRECTION,new.C1,new.C2,new.C3,new.C4,new.C5,new.C6,new.C7,new.WORKCODE);
elseif vcompany_code='NCLR' then
insert into 	newclear_db.device_attandance(company_code,branch_code,DEVICELOGID,DOWNLOADDATE,DEVICEID,device_USERID,emp_id,LOGDATE,DIRECTION,
                                       ATTDIRECTION,C1,C2,C3,C4,C5,C6,C7,WORKCODE)
values (vcompany_code,vbranch_code,new.DEVICELOGID,new.DOWNLOADDATE,new.DEVICEID,new.USERID,vempid,
       new.LOGDATE,new.DIRECTION,new.ATTDIRECTION,new.C1,new.C2,new.C3,new.C4,new.C5,new.C6,new.C7,new.WORKCODE);
elseif vcompany_code='MEDT' then
insert into 	mypayrol_medtrust.device_attandance(company_code,branch_code,DEVICELOGID,DOWNLOADDATE,DEVICEID,device_USERID,emp_id,LOGDATE,DIRECTION,
                                       ATTDIRECTION,C1,C2,C3,C4,C5,C6,C7,WORKCODE)
values (vcompany_code,vbranch_code,new.DEVICELOGID,new.DOWNLOADDATE,new.DEVICEID,new.USERID,vempid,
       new.LOGDATE,new.DIRECTION,new.ATTDIRECTION,new.C1,new.C2,new.C3,new.C4,new.C5,new.C6,new.C7,new.WORKCODE);	


elseif vcompany_code='CREO' then    
insert into 	mypayrol_creohomes_db.device_attandance(company_code,branch_code,DEVICELOGID,DOWNLOADDATE,DEVICEID,device_USERID,emp_id,LOGDATE,DIRECTION,
                                       ATTDIRECTION,C1,C2,C3,C4,C5,C6,C7,WORKCODE)
values (vcompany_code,vbranch_code,new.DEVICELOGID,new.DOWNLOADDATE,new.DEVICEID,new.USERID,vempid,
       new.LOGDATE,new.DIRECTION,new.ATTDIRECTION,new.C1,new.C2,new.C3,new.C4,new.C5,new.C6,new.C7,new.WORKCODE); 

elseif vcompany_code='DRMS' then
insert into 	mypayrol_dreams.device_attandance(company_code,branch_code,DEVICELOGID,DOWNLOADDATE,DEVICEID,device_USERID,emp_id,LOGDATE,DIRECTION,
                                       ATTDIRECTION,C1,C2,C3,C4,C5,C6,C7,WORKCODE)
values (vcompany_code,vbranch_code,new.DEVICELOGID,new.DOWNLOADDATE,new.DEVICEID,new.USERID,vempid,
       new.LOGDATE,new.DIRECTION,new.ATTDIRECTION,new.C1,new.C2,new.C3,new.C4,new.C5,new.C6,new.C7,new.WORKCODE);

elseif vcompany_code='MECO' then
 select distinct emp_id into vempid from mypayrol_control_db.emp_device_comp_branch where company_code=vcompany_code and emp_device_id=new.USERID;

insert into 	mypayrol_mecotronics.device_attandance(company_code,branch_code,DEVICELOGID,DOWNLOADDATE,DEVICEID,device_USERID,emp_id,LOGDATE,DIRECTION,
                                       ATTDIRECTION,C1,C2,C3,C4,C5,C6,C7,WORKCODE)
values (vcompany_code,vbranch_code,new.DEVICELOGID,new.DOWNLOADDATE,new.DEVICEID,new.USERID,vempid,
       new.LOGDATE,new.DIRECTION,new.ATTDIRECTION,new.C1,new.C2,new.C3,new.C4,new.C5,new.C6,new.C7,new.WORKCODE);
	 
	 
elseif vcompany_code='TGPC' then
 select distinct emp_id into vempid from mypayrol_control_db.emp_device_comp_branch where company_code=vcompany_code and emp_device_id=new.USERID;

insert into 	 mypayrol_tgpolimers.device_attandance(company_code,branch_code,DEVICELOGID,DOWNLOADDATE,DEVICEID,device_USERID,emp_id,LOGDATE,DIRECTION,
                                       ATTDIRECTION,C1,C2,C3,C4,C5,C6,C7,WORKCODE)
values (vcompany_code,vbranch_code,new.DEVICELOGID,new.DOWNLOADDATE,new.DEVICEID,new.USERID,vempid,
       new.LOGDATE,new.DIRECTION,new.ATTDIRECTION,new.C1,new.C2,new.C3,new.C4,new.C5,new.C6,new.C7,new.WORKCODE);
	   
elseif vcompany_code='WLNS' then
 select distinct emp_id into vempid from mypayrol_control_db.emp_device_comp_branch where company_code=vcompany_code and emp_device_id=new.USERID;

insert into 	 mypayrol_mpm112.device_attandance(company_code,branch_code,DEVICELOGID,DOWNLOADDATE,DEVICEID,device_USERID,emp_id,LOGDATE,DIRECTION,
                                       ATTDIRECTION,C1,C2,C3,C4,C5,C6,C7,WORKCODE)
values (vcompany_code,vbranch_code,new.DEVICELOGID,new.DOWNLOADDATE,new.DEVICEID,new.USERID,vempid,
       new.LOGDATE,new.DIRECTION,new.ATTDIRECTION,new.C1,new.C2,new.C3,new.C4,new.C5,new.C6,new.C7,new.WORKCODE);	   
	   
	
elseif vcompany_code='MAXD' then
 select distinct emp_id into vempid from mypayrol_control_db.emp_device_comp_branch where company_code=vcompany_code and emp_device_id=new.USERID;

insert into mypayrol_mpm108.device_attandance(company_code,branch_code,DEVICELOGID,DOWNLOADDATE,DEVICEID,device_USERID,emp_id,LOGDATE,DIRECTION,
                                       ATTDIRECTION,C1,C2,C3,C4,C5,C6,C7,WORKCODE)
values (vcompany_code,vbranch_code,new.DEVICELOGID,new.DOWNLOADDATE,new.DEVICEID,new.USERID,vempid,
       new.LOGDATE,new.DIRECTION,new.ATTDIRECTION,new.C1,new.C2,new.C3,new.C4,new.C5,new.C6,new.C7,new.WORKCODE);

elseif vcompany_code='DRML' then
 select distinct emp_id into vempid from mypayrol_control_db.emp_device_comp_branch where company_code=vcompany_code and emp_device_id=new.USERID;

insert into mypayrol_mpm118.device_attandance(company_code,branch_code,DEVICELOGID,DOWNLOADDATE,DEVICEID,device_USERID,emp_id,LOGDATE,DIRECTION,
                                       ATTDIRECTION,C1,C2,C3,C4,C5,C6,C7,WORKCODE)
values (vcompany_code,vbranch_code,new.DEVICELOGID,new.DOWNLOADDATE,new.DEVICEID,new.USERID,vempid,
       new.LOGDATE,new.DIRECTION,new.ATTDIRECTION,new.C1,new.C2,new.C3,new.C4,new.C5,new.C6,new.C7,new.WORKCODE);

elseif vcompany_code='INFR' then
 select distinct emp_id into vempid from mypayrol_control_db.emp_device_comp_branch where company_code=vcompany_code and emp_device_id=new.USERID;

insert into mypayrol_mpm116.device_attandance(company_code,branch_code,DEVICELOGID,DOWNLOADDATE,DEVICEID,device_USERID,emp_id,LOGDATE,DIRECTION,
                                       ATTDIRECTION,C1,C2,C3,C4,C5,C6,C7,WORKCODE)
values (vcompany_code,vbranch_code,new.DEVICELOGID,new.DOWNLOADDATE,new.DEVICEID,new.USERID,vempid,
       new.LOGDATE,new.DIRECTION,new.ATTDIRECTION,new.C1,new.C2,new.C3,new.C4,new.C5,new.C6,new.C7,new.WORKCODE);

elseif vcompany_code='PRSH' then
 select distinct emp_id into vempid from mypayrol_control_db.emp_device_comp_branch where company_code=vcompany_code and emp_device_id=new.USERID;

insert into  mypayrol_mpm125.device_attandance(company_code,branch_code,DEVICELOGID,DOWNLOADDATE,DEVICEID,device_USERID,emp_id,LOGDATE,DIRECTION,
                                       ATTDIRECTION,C1,C2,C3,C4,C5,C6,C7,WORKCODE)
values (vcompany_code,vbranch_code,new.DEVICELOGID,new.DOWNLOADDATE,new.DEVICEID,new.USERID,vempid,
       new.LOGDATE,new.DIRECTION,new.ATTDIRECTION,new.C1,new.C2,new.C3,new.C4,new.C5,new.C6,new.C7,new.WORKCODE);
	   
elseif vcompany_code='SHIN' then
 select distinct emp_id into vempid from mypayrol_control_db.emp_device_comp_branch where company_code=vcompany_code and emp_device_id=new.USERID;

insert into  mypayrol_mpm164.device_attandance(company_code,branch_code,DEVICELOGID,DOWNLOADDATE,DEVICEID,device_USERID,emp_id,LOGDATE,DIRECTION,
                                       ATTDIRECTION,C1,C2,C3,C4,C5,C6,C7,WORKCODE)
values (vcompany_code,vbranch_code,new.DEVICELOGID,new.DOWNLOADDATE,new.DEVICEID,new.USERID,vempid,
       new.LOGDATE,new.DIRECTION,new.ATTDIRECTION,new.C1,new.C2,new.C3,new.C4,new.C5,new.C6,new.C7,new.WORKCODE);

elseif vcompany_code='MDGN' and vbranch_code='MDGN02' then
 select distinct emp_id into vempid from mypayrol_control_db.emp_device_comp_branch 
   where company_code=vcompany_code and branch_code=vbranch_code and emp_device_id=new.USERID;

insert into  mypayrol_mpm235.device_attandance(company_code,branch_code,DEVICELOGID,DOWNLOADDATE,DEVICEID,device_USERID,emp_id,LOGDATE,DIRECTION,
                                       ATTDIRECTION,C1,C2,C3,C4,C5,C6,C7,WORKCODE)
values (vcompany_code,vbranch_code,new.DEVICELOGID,new.DOWNLOADDATE,new.DEVICEID,new.USERID,vempid,
       new.LOGDATE,new.DIRECTION,new.ATTDIRECTION,new.C1,new.C2,new.C3,new.C4,new.C5,new.C6,new.C7,new.WORKCODE);

elseif vcompany_code='MDGN' and vbranch_code='MDGN01' then
 
insert into  mypayrol_mpm235.device_attandance(company_code,branch_code,DEVICELOGID,DOWNLOADDATE,DEVICEID,device_USERID,emp_id,LOGDATE,DIRECTION,
                                       ATTDIRECTION,C1,C2,C3,C4,C5,C6,C7,WORKCODE)
values (vcompany_code,vbranch_code,new.DEVICELOGID,new.DOWNLOADDATE,new.DEVICEID,new.USERID,vempid,
       new.LOGDATE,new.DIRECTION,new.ATTDIRECTION,new.C1,new.C2,new.C3,new.C4,new.C5,new.C6,new.C7,new.WORKCODE);	

elseif vcompany_code='MPCP'  then
 
insert into  mypayrol_mpm227.device_attandance(company_code,branch_code,DEVICELOGID,DOWNLOADDATE,DEVICEID,device_USERID,emp_id,LOGDATE,DIRECTION,
                                       ATTDIRECTION,C1,C2,C3,C4,C5,C6,C7,WORKCODE)
values (vcompany_code,vbranch_code,new.DEVICELOGID,new.DOWNLOADDATE,new.DEVICEID,new.USERID,vempid,
       new.LOGDATE,new.DIRECTION,new.ATTDIRECTION,new.C1,new.C2,new.C3,new.C4,new.C5,new.C6,new.C7,new.WORKCODE);	

elseif vcompany_code='GRNS' and vbranch_code='GRNS01' then
 

insert into  mypayrol_mpm233.device_attandance(company_code,branch_code,DEVICELOGID,DOWNLOADDATE,DEVICEID,device_USERID,emp_id,LOGDATE,DIRECTION,
                                       ATTDIRECTION,C1,C2,C3,C4,C5,C6,C7,WORKCODE)
values (vcompany_code,vbranch_code,new.DEVICELOGID,new.DOWNLOADDATE,new.DEVICEID,new.USERID,vempid,
       new.LOGDATE,new.DIRECTION,new.ATTDIRECTION,new.C1,new.C2,new.C3,new.C4,new.C5,new.C6,new.C7,new.WORKCODE);	
elseif vcompany_code='VSNT' and vbranch_code='VSNT01' then


insert into  mypayrol_mpm210.device_attandance(company_code,branch_code,DEVICELOGID,DOWNLOADDATE,DEVICEID,device_USERID,emp_id,LOGDATE,DIRECTION,
                                       ATTDIRECTION,C1,C2,C3,C4,C5,C6,C7,WORKCODE)
values (vcompany_code,vbranch_code,new.DEVICELOGID,new.DOWNLOADDATE,new.DEVICEID,new.USERID,vempid,
       new.LOGDATE,new.DIRECTION,new.ATTDIRECTION,new.C1,new.C2,new.C3,new.C4,new.C5,new.C6,new.C7,new.WORKCODE);	

elseif vcompany_code='BPHR' and vbranch_code='BPHR01' then
 

insert into  mypayrol_mpm234.device_attandance(company_code,branch_code,DEVICELOGID,DOWNLOADDATE,DEVICEID,device_USERID,emp_id,LOGDATE,DIRECTION,
                                       ATTDIRECTION,C1,C2,C3,C4,C5,C6,C7,WORKCODE)
values (vcompany_code,vbranch_code,new.DEVICELOGID,new.DOWNLOADDATE,new.DEVICEID,new.USERID,vempid,
       new.LOGDATE,new.DIRECTION,new.ATTDIRECTION,new.C1,new.C2,new.C3,new.C4,new.C5,new.C6,new.C7,new.WORKCODE); 

elseif vcompany_code='THDP'  then 

insert into  mypayrol_mpm237.device_attandance(company_code,branch_code,DEVICELOGID,DOWNLOADDATE,DEVICEID,device_USERID,emp_id,LOGDATE,DIRECTION,
                                       ATTDIRECTION,C1,C2,C3,C4,C5,C6,C7,WORKCODE)
values (vcompany_code,vbranch_code,new.DEVICELOGID,new.DOWNLOADDATE,new.DEVICEID,new.USERID,vempid,
       new.LOGDATE,new.DIRECTION,new.ATTDIRECTION,new.C1,new.C2,new.C3,new.C4,new.C5,new.C6,new.C7,new.WORKCODE); 

elseif vcompany_code='BRHM' then 

insert into  mypayrol_mpm244.device_attandance(company_code,branch_code,DEVICELOGID,DOWNLOADDATE,DEVICEID,device_USERID,emp_id,LOGDATE,DIRECTION,
                                       ATTDIRECTION,C1,C4,C5,C6,C7,WORKCODE)
values (vcompany_code,vbranch_code,new.DEVICELOGID,new.DOWNLOADDATE,new.DEVICEID,new.USERID,vempid,
       new.LOGDATE,new.DIRECTION,new.ATTDIRECTION,new.C1,new.C4,new.C5,new.C6,new.C7,new.WORKCODE);

elseif vcompany_code='MBCET' then 

insert into  mypayrol_mpm262.device_attandance(company_code,branch_code,DEVICELOGID,DOWNLOADDATE,DEVICEID,device_USERID,emp_id,LOGDATE,DIRECTION,
                                       ATTDIRECTION,C1,C4,C5,C6,C7,WORKCODE)
values (vcompany_code,vbranch_code,new.DEVICELOGID,new.DOWNLOADDATE,new.DEVICEID,new.USERID,vempid,
       new.LOGDATE,new.DIRECTION,new.ATTDIRECTION,new.C1,new.C4,new.C5,new.C6,new.C7,new.WORKCODE); 	   

end if;




end;;

DELIMITER ;

CREATE TABLE `devices` (
  `DeviceId` int(11) NOT NULL,
  `DeviceFName` varchar(255) NOT NULL,
  `DeviceSName` varchar(255) NOT NULL,
  `company_code` varchar(20) NOT NULL,
  `branch_code` varchar(20) NOT NULL,
  `DeviceDirection` varchar(255) DEFAULT NULL,
  `SerialNumber` varchar(255) DEFAULT NULL,
  `ConnectionType` varchar(255) DEFAULT NULL,
  `IpAddress` varchar(255) DEFAULT NULL,
  `BaudRate` varchar(255) DEFAULT NULL,
  `CommKey` varchar(255) DEFAULT NULL,
  `ComPort` varchar(255) DEFAULT NULL,
  `LastLogDownloadDate` datetime DEFAULT NULL,
  `C1` varchar(255) DEFAULT NULL,
  `C2` varchar(255) DEFAULT NULL,
  `C3` varchar(255) DEFAULT NULL,
  `C4` varchar(255) DEFAULT NULL,
  `C5` varchar(255) DEFAULT NULL,
  `C6` varchar(255) DEFAULT NULL,
  `C7` varchar(255) DEFAULT NULL,
  `TransactionStamp` varchar(255) DEFAULT NULL,
  `LastPing` datetime DEFAULT NULL,
  `DeviceType` varchar(255) DEFAULT NULL,
  `OpStamp` varchar(255) DEFAULT NULL,
  `DownLoadType` int(11) DEFAULT NULL,
  `Timezone` varchar(50) DEFAULT NULL,
  `DeviceLocation` varchar(50) DEFAULT NULL,
  `TimeOut` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `device_logs_error` (
  `DEVICELOGID` int(11) NOT NULL,
  `DOWNLOADDATE` datetime NOT NULL,
  `DEVICEID` int(11) NOT NULL,
  `USERID` varchar(100) DEFAULT NULL,
  `LOGDATE` datetime DEFAULT NULL,
  `DIRECTION` varchar(50) DEFAULT NULL,
  `ATTDIRECTION` varchar(50) DEFAULT NULL,
  `C1` varchar(100) DEFAULT NULL,
  `C2` varchar(100) DEFAULT NULL,
  `C3` varchar(100) DEFAULT NULL,
  `C4` varchar(100) DEFAULT NULL,
  `C5` varchar(100) DEFAULT NULL,
  `C6` varchar(100) DEFAULT NULL,
  `C7` varchar(100) DEFAULT NULL,
  `WORKCODE` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `device_logs_error_09032026` (
  `DEVICELOGID` int(11) NOT NULL,
  `DOWNLOADDATE` datetime NOT NULL,
  `DEVICEID` int(11) NOT NULL,
  `USERID` varchar(100) DEFAULT NULL,
  `LOGDATE` datetime DEFAULT NULL,
  `DIRECTION` varchar(50) DEFAULT NULL,
  `ATTDIRECTION` varchar(50) DEFAULT NULL,
  `C1` varchar(100) DEFAULT NULL,
  `C2` varchar(100) DEFAULT NULL,
  `C3` varchar(100) DEFAULT NULL,
  `C4` varchar(100) DEFAULT NULL,
  `C5` varchar(100) DEFAULT NULL,
  `C6` varchar(100) DEFAULT NULL,
  `C7` varchar(100) DEFAULT NULL,
  `WORKCODE` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `doc_template` (
  `template_pkey` int(11) NOT NULL AUTO_INCREMENT,
  `template_name` varchar(200) NOT NULL,
  `template_content` mediumtext NOT NULL,
  `placeholders` varchar(500) NOT NULL,
  `availability` varchar(10) NOT NULL DEFAULT '0',
  `editable` varchar(10) NOT NULL DEFAULT '0',
  `policy` varchar(10) NOT NULL DEFAULT '0',
  `created_by` varchar(30) NOT NULL,
  `creation_date` datetime NOT NULL,
  `modified_by` varchar(30) DEFAULT NULL,
  `modification_date` datetime DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`template_pkey`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `emp_device_comp_branch` (
  `emp_device_comp_branch_seq` int(11) NOT NULL AUTO_INCREMENT,
  `deviceid` int(11) NOT NULL,
  `emp_device_id` int(11) NOT NULL,
  `emp_name` varchar(200) NOT NULL,
  `Company_code` varchar(20) NOT NULL,
  `branch_code` varchar(20) NOT NULL,
  `emp_id` bigint(20) DEFAULT NULL,
  `emp_username` varchar(50) NOT NULL,
  `emp_fkey` int(11) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_by` varchar(100) DEFAULT NULL,
  `modified_date` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`emp_device_comp_branch_seq`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DELIMITER ;;

CREATE TRIGGER `set_emp_id` BEFORE INSERT ON `emp_device_comp_branch` FOR EACH ROW
begin
IF NEW.emp_id IS NULL THEN
        SET NEW.emp_id :=concat(NEW.deviceid,NEW.emp_device_id);
    
        SET NEW.emp_username := concat(NEW.company_code,NEW.emp_id);

    END IF   ;
    
   end;;

DELIMITER ;

CREATE TABLE `emp_device_comp_branch_bak` (
  `emp_device_comp_branch_seq` int(11) NOT NULL DEFAULT '0',
  `deviceid` int(11) NOT NULL,
  `emp_device_id` int(11) NOT NULL,
  `emp_name` varchar(200) NOT NULL,
  `Company_code` varchar(20) NOT NULL,
  `branch_code` varchar(20) NOT NULL,
  `emp_id` int(11) DEFAULT NULL,
  `emp_username` varchar(50) NOT NULL,
  `emp_fkey` int(11) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` varchar(100) DEFAULT NULL,
  `modified_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `emp_device_comp_branch_bak1` (
  `emp_device_comp_branch_seq` int(11) NOT NULL DEFAULT '0',
  `deviceid` int(11) NOT NULL,
  `emp_device_id` int(11) NOT NULL,
  `emp_name` varchar(200) NOT NULL,
  `Company_code` varchar(20) NOT NULL,
  `branch_code` varchar(20) NOT NULL,
  `emp_id` varchar(50) DEFAULT NULL,
  `emp_username` varchar(50) NOT NULL,
  `emp_fkey` int(11) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` varchar(100) DEFAULT NULL,
  `modified_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `emp_id_maping` (
  `FirstName` varchar(100) NOT NULL,
  `LastName` varchar(100) NOT NULL,
  `EmpNo` varchar(100) NOT NULL,
  `status` char(1) NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `emp_id_maping_exist` (
  `emp_fkey` int(11) NOT NULL,
  `company_code` varchar(30) NOT NULL,
  `branch_code` varchar(30) NOT NULL,
  `emp_name` varchar(200) NOT NULL,
  `emp_device_id` int(11) NOT NULL,
  `id_mapped` int(11) NOT NULL,
  `status` varchar(2) NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `features` (
  `feature_id` int(11) NOT NULL AUTO_INCREMENT,
  `display_order` int(11) NOT NULL DEFAULT '0',
  `feature_key` varchar(100) NOT NULL,
  `feature_name` varchar(150) NOT NULL,
  `feature_path` varchar(255) DEFAULT NULL,
  `description` varchar(3000) DEFAULT NULL,
  `is_common` tinyint(1) NOT NULL DEFAULT '0',
  `article` varchar(3000) NOT NULL,
  `report_list` varchar(3000) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `icon` varchar(50) DEFAULT NULL,
  `is_addon` tinyint(1) DEFAULT '0' COMMENT '1=Is Addon, 0=Regular Feature',
  `plan_id` int(11) DEFAULT NULL,
  `included_employees` int(11) DEFAULT '0',
  `extra_employee_price` decimal(10,2) DEFAULT '0.00',
  `base_addon_price` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`feature_id`),
  KEY `plan_id` (`plan_id`),
  CONSTRAINT `features_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `get_location` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company` varchar(50) NOT NULL,
  `emp_id` varchar(50) NOT NULL,
  `state` varchar(50) NOT NULL,
  `location` varchar(500) DEFAULT NULL,
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `holidays` (
  `HOLIDAYID` int(11) NOT NULL AUTO_INCREMENT,
  `HOLIDAY_GROUP_ID` int(11) NOT NULL,
  `HOLIDAYNAME` varchar(100) NOT NULL,
  `HOLIDAYDATE` date NOT NULL,
  `DESCRIPTION` varchar(255) NOT NULL,
  `HOLIDAYTYPE` varchar(50) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1' COMMENT '0;inactive,1 active',
  `Background` varchar(100) NOT NULL,
  `border` varchar(100) NOT NULL,
  PRIMARY KEY (`HOLIDAYID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `holiday_group` (
  `COMPANY_CODE` varchar(50) NOT NULL,
  `BRANCH_CODE` varchar(50) NOT NULL,
  `HOLIDAY_GROUP_ID` int(11) NOT NULL AUTO_INCREMENT,
  `HOLIDAY_GROUP_NAME` varchar(200) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1' COMMENT '0 inactive 1 active',
  PRIMARY KEY (`HOLIDAY_GROUP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `income_tax_slab` (
  `income_tax_slab_pkey` int(11) NOT NULL AUTO_INCREMENT,
  `fin_year` int(11) NOT NULL,
  `regime` varchar(20) DEFAULT NULL,
  `salary_range_from` int(11) NOT NULL,
  `salary_range_to` int(11) NOT NULL,
  `tax_yearly_perc` int(11) DEFAULT NULL,
  `std_deduction` float DEFAULT NULL,
  `surcharge_perc` float DEFAULT NULL,
  `rebate` float DEFAULT NULL,
  `cess_perc` float DEFAULT NULL,
  `start_date_effective` date DEFAULT NULL,
  `end_date_effective` date DEFAULT NULL,
  `created_by` varchar(30) NOT NULL,
  `creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_by` varchar(30) DEFAULT NULL,
  `modified_date` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `status` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`income_tax_slab_pkey`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `leavepolicy` (
  `LEAVEPOLICYID` int(11) NOT NULL AUTO_INCREMENT,
  `LEAVEPOLICY_GROUP_ID` int(11) NOT NULL,
  `salary_head_item_fkey` int(11) NOT NULL,
  `alloted_leave_forthe_year` float NOT NULL,
  `alloted_leave_forthe_month` float DEFAULT NULL,
  `CARRY_FORWARD_LIMIT` float DEFAULT NULL,
  `APPLICABLE_TO` varchar(50) DEFAULT NULL,
  `ALLOW_NEGETIVE` char(1) DEFAULT 'N',
  `IS_SANDWICH` char(1) DEFAULT 'N',
  `is_leave_encash` char(1) DEFAULT 'N',
  `leave_encash_limit` float DEFAULT NULL,
  `is_auto_credit` char(1) DEFAULT 'N',
  `REMARKS` varchar(500) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`LEAVEPOLICYID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `leavepolicy_group` (
  `COMPANY_CODE` varchar(50) NOT NULL,
  `BRANCH_CODE` varchar(50) NOT NULL,
  `LEAVEPOLICY_GROUP_ID` int(11) NOT NULL AUTO_INCREMENT,
  `LEAVEPOLICY_GROUP_NAME` varchar(200) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1' COMMENT '0 inactive 1 inactive',
  PRIMARY KEY (`LEAVEPOLICY_GROUP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `location` (
  `loc_pkey` int(11) NOT NULL AUTO_INCREMENT,
  `company_code` varchar(20) NOT NULL,
  `emp_id` varchar(20) NOT NULL,
  `state` varchar(20) NOT NULL,
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`loc_pkey`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `location_09032026` (
  `loc_pkey` int(11) NOT NULL DEFAULT '0',
  `company_code` varchar(20) NOT NULL,
  `emp_id` varchar(20) NOT NULL,
  `state` varchar(20) NOT NULL,
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `login_auditor` (
  `login_audit_pkey` int(11) NOT NULL AUTO_INCREMENT,
  `user_cred` varchar(40) NOT NULL,
  `user_ip` varchar(40) NOT NULL,
  `user_browser` varchar(100) NOT NULL,
  `company1` varchar(100) NOT NULL,
  `company2` varchar(100) NOT NULL,
  `auditor_time` varchar(100) NOT NULL DEFAULT 'CURRENT_TIMESTAMP',
  `auditor_type` varchar(100) NOT NULL COMMENT 'IN/OUT',
  `message` varchar(400) NOT NULL,
  `user_cred2` varchar(40) NOT NULL,
  PRIMARY KEY (`login_audit_pkey`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `log_issue_cntrldb` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(30) NOT NULL,
  `module` varchar(30) DEFAULT NULL,
  `company_code` varchar(30) DEFAULT NULL,
  `emp_pkey` int(11) DEFAULT NULL,
  `issue` varchar(3000) NOT NULL,
  `creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `master_auidt` (
  `master_auidt_pkey` int(11) NOT NULL AUTO_INCREMENT,
  `company_code` varchar(20) NOT NULL,
  `company_name` varchar(200) DEFAULT NULL,
  `branch_code` varchar(20) DEFAULT NULL,
  `branch_name` varchar(200) DEFAULT NULL,
  `module_name` varchar(200) DEFAULT NULL,
  `process_Name` varchar(100) DEFAULT NULL,
  `process_type` varchar(50) DEFAULT NULL,
  `process_month` varchar(50) DEFAULT NULL,
  `process_count` varchar(50) DEFAULT NULL,
  `remarks` varchar(100) DEFAULT NULL,
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modification_date` datetime DEFAULT NULL,
  PRIMARY KEY (`master_auidt_pkey`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `master_db` (
  `master_db_pkey` int(11) NOT NULL AUTO_INCREMENT,
  `Module` varchar(50) NOT NULL,
  `object_Name` varchar(100) NOT NULL,
  `object_type` varchar(50) NOT NULL,
  `object_script` blob NOT NULL,
  `object_active` char(1) NOT NULL DEFAULT 'Y',
  `extras` varchar(100) NOT NULL,
  PRIMARY KEY (`master_db_pkey`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `mob_user_msg_control` (
  `mob_user_msg_control_pkey` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(20) NOT NULL,
  `mpm_version` varchar(20) NOT NULL,
  `device_model` varchar(30) NOT NULL,
  `reminder_count` int(11) NOT NULL,
  `remarks` varchar(200) NOT NULL,
  `creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modification_date` datetime NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`mob_user_msg_control_pkey`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `mob_version_control` (
  `mob_version_control_pkey` int(11) NOT NULL AUTO_INCREMENT,
  `version` varchar(20) NOT NULL,
  `reminder_count` int(11) NOT NULL,
  `reminder_msg` varchar(200) DEFAULT NULL,
  `other_msg` varchar(200) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  `cretion_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`mob_version_control_pkey`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `mypayroll_bugs_logs` (
  `bug_pkey` int(120) NOT NULL AUTO_INCREMENT,
  `COMPANY_CODE` varchar(10) NOT NULL,
  `USER` varchar(30) NOT NULL,
  `title` varchar(500) NOT NULL,
  `bug_type` varchar(20) NOT NULL,
  `reason` varchar(500) NOT NULL,
  `description` varchar(2000) NOT NULL,
  `details` varchar(2000) NOT NULL,
  `user_ip` varchar(30) NOT NULL,
  `browser` varchar(30) NOT NULL,
  `bugs_time` datetime NOT NULL,
  `resolved` varchar(1) NOT NULL DEFAULT 'N',
  `notified` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`bug_pkey`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `myprojects_control` (
  `control_pkey` int(11) NOT NULL AUTO_INCREMENT,
  `company_code` varchar(20) NOT NULL,
  `company_name` varchar(200) NOT NULL,
  `Address` varchar(500) NOT NULL,
  `Admin_name` varchar(100) NOT NULL,
  `user_db` varchar(100) NOT NULL,
  `user_pwd` varchar(100) NOT NULL,
  `created_date` datetime NOT NULL,
  `start_date_effective` date NOT NULL,
  `end_date_effective` date NOT NULL,
  `product` varchar(200) NOT NULL,
  `active` varchar(10) NOT NULL,
  `custom_message` varchar(500) NOT NULL,
  `redirect_url` varchar(200) NOT NULL,
  `country_code` varchar(3) NOT NULL,
  `currency_code` varchar(3) NOT NULL,
  `punch_type` varchar(20) NOT NULL COMMENT 'device/manual',
  `attr1` varchar(200) NOT NULL,
  `attr2` varchar(200) NOT NULL,
  `attr3` varchar(200) NOT NULL,
  `attr4` varchar(200) NOT NULL,
  `attr5` varchar(200) NOT NULL,
  `attr6` varchar(200) NOT NULL,
  `attr7` varchar(200) NOT NULL,
  PRIMARY KEY (`control_pkey`),
  UNIQUE KEY `company_code` (`company_code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `mysales_control` (
  `control_pkey` int(11) NOT NULL AUTO_INCREMENT,
  `company_code` varchar(20) NOT NULL,
  `company_name` varchar(200) NOT NULL,
  `Address` varchar(500) NOT NULL,
  `Admin_name` varchar(100) NOT NULL,
  `user_db` varchar(100) NOT NULL,
  `user_pwd` varchar(100) NOT NULL,
  `created_date` datetime NOT NULL,
  `start_date_effective` date NOT NULL,
  `end_date_effective` date NOT NULL,
  `product` varchar(200) NOT NULL,
  `active` varchar(10) NOT NULL,
  `custom_message` varchar(500) NOT NULL,
  `redirect_url` varchar(200) NOT NULL,
  `country_code` varchar(3) NOT NULL,
  `currency_code` varchar(3) NOT NULL,
  `punch_type` varchar(20) NOT NULL COMMENT 'device/manual',
  `attr1` varchar(200) NOT NULL,
  `attr2` varchar(200) NOT NULL,
  `attr3` varchar(200) NOT NULL,
  `attr4` varchar(200) NOT NULL,
  `attr5` varchar(200) NOT NULL,
  `attr6` varchar(200) NOT NULL,
  `attr7` varchar(200) NOT NULL,
  PRIMARY KEY (`control_pkey`),
  UNIQUE KEY `company_code` (`company_code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `oauth2_authorization` (
  `id` varchar(100) NOT NULL,
  `registered_client_id` varchar(100) NOT NULL,
  `principal_name` varchar(200) NOT NULL,
  `authorization_grant_type` varchar(100) NOT NULL,
  `authorized_scopes` varchar(1000) DEFAULT NULL,
  `attributes` blob,
  `state` varchar(500) DEFAULT NULL,
  `authorization_code_value` blob,
  `authorization_code_issued_at` timestamp NULL DEFAULT NULL,
  `authorization_code_expires_at` timestamp NULL DEFAULT NULL,
  `authorization_code_metadata` blob,
  `access_token_value` blob,
  `access_token_issued_at` timestamp NULL DEFAULT NULL,
  `access_token_expires_at` timestamp NULL DEFAULT NULL,
  `access_token_metadata` blob,
  `access_token_type` varchar(100) DEFAULT NULL,
  `access_token_scopes` varchar(1000) DEFAULT NULL,
  `oidc_id_token_value` blob,
  `oidc_id_token_issued_at` timestamp NULL DEFAULT NULL,
  `oidc_id_token_expires_at` timestamp NULL DEFAULT NULL,
  `oidc_id_token_metadata` blob,
  `refresh_token_value` blob,
  `refresh_token_issued_at` timestamp NULL DEFAULT NULL,
  `refresh_token_expires_at` timestamp NULL DEFAULT NULL,
  `refresh_token_metadata` blob,
  `user_code_value` blob,
  `user_code_issued_at` timestamp NULL DEFAULT NULL,
  `user_code_expires_at` timestamp NULL DEFAULT NULL,
  `user_code_metadata` blob,
  `device_code_value` blob,
  `device_code_issued_at` timestamp NULL DEFAULT NULL,
  `device_code_expires_at` timestamp NULL DEFAULT NULL,
  `device_code_metadata` blob,
  `oidc_id_token_claims` blob,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `oauth2_registered_client` (
  `id` varchar(100) NOT NULL,
  `client_id` varchar(100) NOT NULL,
  `client_id_issued_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `client_secret` varchar(200) DEFAULT NULL,
  `client_secret_expires_at` timestamp NULL DEFAULT NULL,
  `client_name` varchar(200) NOT NULL,
  `client_authentication_methods` varchar(1000) NOT NULL,
  `authorization_grant_types` varchar(1000) NOT NULL,
  `redirect_uris` varchar(1000) DEFAULT NULL,
  `post_logout_redirect_uris` varchar(1000) DEFAULT NULL,
  `scopes` varchar(1000) NOT NULL,
  `client_settings` varchar(2000) NOT NULL,
  `token_settings` varchar(2000) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `payroll_online` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(50) NOT NULL DEFAULT '',
  `activity` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `member` varchar(5) NOT NULL DEFAULT 'n',
  `ip_address` varchar(25) NOT NULL DEFAULT '',
  `refurl` varchar(155) NOT NULL DEFAULT '',
  `user_agent` varchar(55) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `payroll_signup` (
  `payroll_signup_pkey` int(11) NOT NULL AUTO_INCREMENT,
  `company_code` varchar(20) NOT NULL,
  `company_name` varchar(200) NOT NULL,
  `Address` varchar(500) DEFAULT NULL,
  `Contact_Name` varchar(500) DEFAULT NULL,
  `Contact_Phone` varchar(500) DEFAULT NULL,
  `admin_email` varchar(500) NOT NULL,
  `admin_password` varchar(500) NOT NULL,
  `admin_phone` varchar(500) NOT NULL,
  `admin_name` varchar(100) NOT NULL,
  `signup_url` varchar(200) DEFAULT NULL,
  `signup_ip` varchar(200) DEFAULT NULL,
  `signup_browser` varchar(200) DEFAULT NULL,
  `free_trial_end_date` date DEFAULT NULL,
  `user_db` varchar(100) DEFAULT NULL,
  `user_pwd` varchar(100) DEFAULT NULL,
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `start_date_effective` date NOT NULL,
  `end_date_effective` date DEFAULT NULL,
  `product` varchar(200) DEFAULT NULL,
  `active` varchar(10) DEFAULT NULL,
  `custom_message` varchar(500) DEFAULT NULL,
  `redirect_url` varchar(200) DEFAULT NULL,
  `country_code` varchar(50) DEFAULT NULL,
  `currency_code` varchar(50) DEFAULT NULL,
  `punch_type` varchar(100) DEFAULT NULL COMMENT 'device/manual/Mob',
  `attr1` varchar(200) DEFAULT NULL,
  `attr2` varchar(200) DEFAULT NULL,
  `attr3` varchar(200) DEFAULT NULL,
  `attr4` varchar(200) DEFAULT NULL,
  `attr5` varchar(200) DEFAULT NULL,
  `attr6` varchar(200) DEFAULT NULL,
  `attr7` varchar(200) DEFAULT NULL,
  `trial_status` char(200) NOT NULL DEFAULT 'A',
  PRIMARY KEY (`payroll_signup_pkey`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `payroll_signup_bck29092025` (
  `payroll_signup_pkey` int(11) NOT NULL DEFAULT '0',
  `company_code` varchar(20) NOT NULL,
  `company_name` varchar(200) NOT NULL,
  `Address` varchar(500) DEFAULT NULL,
  `Contact_Name` varchar(500) DEFAULT NULL,
  `Contact_Phone` varchar(500) DEFAULT NULL,
  `admin_email` varchar(500) NOT NULL,
  `admin_password` varchar(500) NOT NULL,
  `admin_phone` varchar(500) NOT NULL,
  `admin_name` varchar(100) NOT NULL,
  `signup_url` varchar(200) DEFAULT NULL,
  `signup_ip` varchar(200) DEFAULT NULL,
  `signup_browser` varchar(200) DEFAULT NULL,
  `free_trial_end_date` date DEFAULT NULL,
  `user_db` varchar(100) DEFAULT NULL,
  `user_pwd` varchar(100) DEFAULT NULL,
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `start_date_effective` date NOT NULL,
  `end_date_effective` date DEFAULT NULL,
  `product` varchar(200) DEFAULT NULL,
  `active` varchar(10) DEFAULT NULL,
  `custom_message` varchar(500) DEFAULT NULL,
  `redirect_url` varchar(200) DEFAULT NULL,
  `country_code` varchar(50) DEFAULT NULL,
  `currency_code` varchar(50) DEFAULT NULL,
  `punch_type` varchar(100) DEFAULT NULL COMMENT 'device/manual/Mob',
  `attr1` varchar(200) DEFAULT NULL,
  `attr2` varchar(200) DEFAULT NULL,
  `attr3` varchar(200) DEFAULT NULL,
  `attr4` varchar(200) DEFAULT NULL,
  `attr5` varchar(200) DEFAULT NULL,
  `attr6` varchar(200) DEFAULT NULL,
  `attr7` varchar(200) DEFAULT NULL,
  `trial_status` char(200) NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `plans` (
  `plan_id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_name` varchar(100) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `grace_period` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `included_employees` int(11) DEFAULT '0' COMMENT 'Max employees included in base price',
  `extra_employee_price` decimal(10,2) DEFAULT '0.00' COMMENT 'Price per extra employee',
  PRIMARY KEY (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `plan_features` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_id` int(11) NOT NULL,
  `feature_id` int(11) NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_plan_feature` (`plan_id`,`feature_id`),
  KEY `fk_planfeatures_feature` (`feature_id`),
  CONSTRAINT `fk_planfeatures_feature` FOREIGN KEY (`feature_id`) REFERENCES `features` (`feature_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_planfeatures_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`plan_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `plan_history` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `purchased_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`history_id`),
  KEY `fk_plan_history_client` (`client_id`),
  KEY `fk_plan_history_plan` (`plan_id`),
  CONSTRAINT `fk_plan_history_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_plan_history_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`plan_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `plan_payment_history` (
  `payment_history_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(30) NOT NULL,
  `change_plan_id` int(11) NOT NULL,
  `razorpay_payment_id` varchar(100) NOT NULL,
  `razorpay_order_id` varchar(100) NOT NULL,
  `amount` float NOT NULL,
  `method` varchar(100) NOT NULL,
  `bank` varchar(100) DEFAULT NULL,
  `email` varchar(50) NOT NULL,
  `contact` varchar(50) NOT NULL,
  `plan_start_date` date DEFAULT NULL,
  `plan_end_date` date DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  `status` varchar(50) NOT NULL,
  `active` int(11) NOT NULL,
  `addons_json` text,
  `plan_price` decimal(10,2) DEFAULT '0.00',
  `addon_price` decimal(10,2) DEFAULT '0.00',
  `extra_charge` decimal(10,2) DEFAULT '0.00',
  `gst_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `upgrade_credit` decimal(10,2) DEFAULT NULL,
  `available_credit` float DEFAULT '0',
  `created_by` varchar(50) NOT NULL,
  `creation_date` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` varchar(50) DEFAULT NULL,
  `modification_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `grace_extension_days` int(11) DEFAULT NULL,
  PRIMARY KEY (`payment_history_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `ppl_online` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(50) NOT NULL DEFAULT '',
  `activity` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `member` varchar(5) NOT NULL DEFAULT 'n',
  `ip_address` varchar(25) NOT NULL DEFAULT '',
  `refurl` varchar(155) NOT NULL DEFAULT '',
  `user_agent` varchar(55) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `procedure_run_log` (
  `procedure_run_log_pkey` int(11) NOT NULL AUTO_INCREMENT,
  `procedure_name` varchar(100) NOT NULL,
  `db_name` varchar(100) NOT NULL,
  `status` enum('success','failed') NOT NULL,
  `error_message` varchar(500) DEFAULT NULL,
  `ran_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`procedure_run_log_pkey`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `projects_online` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(50) NOT NULL DEFAULT '',
  `activity` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `member` varchar(5) NOT NULL DEFAULT 'n',
  `ip_address` varchar(25) NOT NULL DEFAULT '',
  `refurl` varchar(155) NOT NULL DEFAULT '',
  `user_agent` varchar(55) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `registrations` (
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile_no` varchar(15) NOT NULL,
  `company_name` varchar(200) NOT NULL,
  `desired_username` varchar(200) NOT NULL,
  `desired_password` varchar(100) NOT NULL,
  `came_from` varchar(100) NOT NULL,
  `employee_count` varchar(100) NOT NULL,
  `comments` varchar(200) NOT NULL,
  `signup_date` date NOT NULL,
  `attr1` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `report_a_problem1` (
  `mob_report_pkey` int(11) NOT NULL DEFAULT '0',
  `report_type` varchar(480) NOT NULL DEFAULT '',
  `user_id` varchar(480) NOT NULL DEFAULT '',
  `remarks` text NOT NULL,
  `lat` varchar(100) DEFAULT NULL,
  `longs` varchar(100) DEFAULT NULL,
  `date_rep` datetime DEFAULT NULL,
  `rep_status` varchar(20) DEFAULT NULL,
  `status` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `salary_heads` (
  `head_pkey` int(11) NOT NULL AUTO_INCREMENT,
  `head_desc` varchar(200) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1' COMMENT '0 inactive 1 active',
  `head_operator` varchar(100) NOT NULL,
  `head_occurance` varchar(100) NOT NULL,
  `salary_head_order1` int(11) NOT NULL,
  PRIMARY KEY (`head_pkey`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `salary_head_items` (
  `salary_head_item_pkey` int(11) NOT NULL AUTO_INCREMENT,
  `head_fkey` int(11) NOT NULL,
  `item` varchar(200) NOT NULL,
  `item_type` varchar(10) NOT NULL COMMENT 'null=formula,fixed,limit',
  `item_value` varchar(100) DEFAULT NULL,
  `occurance` varchar(20) DEFAULT NULL COMMENT 'monthly,anually,bymonthly,daily,quarterly,halfearly,etc',
  `start_from` date DEFAULT NULL,
  `comments` text,
  `value` char(1) NOT NULL DEFAULT 'N' COMMENT 'N unchecked Y checked',
  `is_show_salslip` char(1) NOT NULL DEFAULT 'Y' COMMENT 'N unchecked Y checked',
  `item_part` varchar(30) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1' COMMENT '0 inactive 1 active',
  `salary_head_item_order1` int(11) NOT NULL,
  PRIMARY KEY (`salary_head_item_pkey`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `salary_structure` (
  `structure_id` int(11) NOT NULL AUTO_INCREMENT,
  `company_code` varchar(30) NOT NULL,
  `structure_name` varchar(200) NOT NULL,
  `prorate_code` varchar(25) NOT NULL,
  `prorate_desc` varchar(250) NOT NULL COMMENT 'Calendar,Working days,Fixed days ',
  `defined_structure_for` varchar(250) NOT NULL COMMENT 'monthly,anually,bymonthly,daily',
  `structure_eg_amt` int(11) NOT NULL,
  `structure_created_date` int(11) NOT NULL,
  `startdate_effective` date NOT NULL,
  `enddate_effective` date NOT NULL,
  `structure_active` int(11) NOT NULL,
  PRIMARY KEY (`structure_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `salary_structure_details` (
  `structure_det_id` int(11) NOT NULL AUTO_INCREMENT,
  `structure_id` int(11) NOT NULL COMMENT 'Primary key of  salary_structure table',
  `salary_head_item_fkey` int(11) NOT NULL COMMENT 'Primary key of salary_head_items',
  `structure_det_operator` varchar(30) NOT NULL,
  `structure_det_value` float NOT NULL,
  `structure_det_depends` float DEFAULT NULL,
  `structure_formula` varchar(1000) DEFAULT NULL,
  `structure_derived_perc` double DEFAULT NULL,
  `structure_det_calequation` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`structure_det_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `sso_audit` (
  `sso_audit_pkey` int(11) NOT NULL AUTO_INCREMENT,
  `string` varchar(1500) NOT NULL,
  `company_code` varchar(20) NOT NULL,
  `emp_comp_id` varchar(50) NOT NULL,
  `emp_email` varchar(100) NOT NULL,
  `keyvalue` varchar(100) NOT NULL,
  `creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `return_mesg` varchar(255) NOT NULL,
  PRIMARY KEY (`sso_audit_pkey`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `super_admin_access` (
  `super_admin_access_pkey` int(11) NOT NULL AUTO_INCREMENT,
  `subdomain` varchar(30) NOT NULL,
  `user_fkey` int(11) DEFAULT NULL,
  `control_fkey` int(11) DEFAULT NULL,
  `active` char(1) DEFAULT 'Y',
  `status` int(11) DEFAULT '1',
  PRIMARY KEY (`super_admin_access_pkey`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `test` (
  `field1` varchar(100) DEFAULT NULL,
  `field2` varchar(4000) DEFAULT NULL,
  `field3` varchar(100) DEFAULT NULL,
  `field4` varchar(100) DEFAULT NULL,
  `field5` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `test_device_attandance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` varchar(50) NOT NULL COMMENT 'The Biometric ID from the device',
  `LOGDATE` datetime NOT NULL COMMENT 'Exact Date and Time of Punch',
  `c1` varchar(10) DEFAULT 'IN' COMMENT 'Punch Status defaults to IN',
  `status` varchar(10) DEFAULT 'Y' COMMENT 'Processing Status',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_punch` (`emp_id`,`LOGDATE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `tenant` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_username` (`username`),
  KEY `idx_tenant` (`tenant`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `user_credentials` (
  `user_pkey` int(11) NOT NULL AUTO_INCREMENT,
  `control_fkey` int(11) NOT NULL DEFAULT '0',
  `company_code` varchar(20) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `access_allowed` varchar(1) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` int(11) NOT NULL,
  `reset_login_flag` varchar(100) NOT NULL,
  `locked` varchar(10) NOT NULL,
  `attr1` varchar(200) NOT NULL,
  `attr2` varchar(200) NOT NULL,
  `avatar` text,
  `plan_id` int(11) unsigned DEFAULT NULL,
  `credit_balance` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`user_pkey`,`control_fkey`),
  UNIQUE KEY `control_fkey_user_pkey` (`control_fkey`,`user_pkey`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `control_fkey` (`control_fkey`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `user_credentials_bck29092025` (
  `user_pkey` int(11) NOT NULL DEFAULT '0',
  `control_fkey` int(11) NOT NULL DEFAULT '0',
  `company_code` varchar(20) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `access_allowed` varchar(1) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` int(11) NOT NULL,
  `reset_login_flag` varchar(100) NOT NULL,
  `locked` varchar(10) NOT NULL,
  `attr1` varchar(200) NOT NULL,
  `attr2` varchar(200) NOT NULL,
  `avatar` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `working_day_time_procedures` (
  `day_time_seq` int(11) NOT NULL AUTO_INCREMENT,
  `day_time_desc` text NOT NULL,
  `Sunday` char(1) NOT NULL DEFAULT 'N',
  `Sunday_F` char(1) NOT NULL DEFAULT 'N',
  `Monday` char(1) NOT NULL DEFAULT 'N',
  `Monday_F` char(1) NOT NULL DEFAULT 'N',
  `Tuesday` char(1) NOT NULL DEFAULT 'N',
  `Tuesday_F` char(1) NOT NULL DEFAULT 'N',
  `Wednesday` char(1) NOT NULL DEFAULT 'N',
  `Wednesday_F` char(1) NOT NULL DEFAULT 'N',
  `Thursday` char(1) NOT NULL DEFAULT 'N',
  `Thursday_F` char(1) NOT NULL DEFAULT 'N',
  `Friday` char(1) NOT NULL DEFAULT 'N',
  `Friday_F` char(1) NOT NULL DEFAULT 'N',
  `Saturday` char(1) NOT NULL DEFAULT 'N',
  `Saturday_F` char(1) NOT NULL DEFAULT 'N',
  `on_dutty1` varchar(30) NOT NULL,
  `off_dutty1` varchar(30) NOT NULL,
  `working_time1` int(11) NOT NULL,
  `on_dutty2` varchar(30) NOT NULL,
  `off_dutty2` varchar(30) NOT NULL,
  `working_time2` varchar(30) NOT NULL,
  `on_dutty3` varchar(30) NOT NULL,
  `off_dutty3` varchar(30) NOT NULL,
  `working_time3` varchar(30) NOT NULL,
  `on_dutty4` varchar(30) NOT NULL,
  `off_dutty4` varchar(30) NOT NULL,
  `working_time4` varchar(30) NOT NULL,
  `minuts_calc_perday` int(11) NOT NULL,
  `minuts_aftr_on_dutty_cal_late` int(11) NOT NULL,
  `minuts_bfr_off_dutty_cal_early` int(11) NOT NULL,
  `min_cal_late_ifnoclockin` int(11) NOT NULL,
  `min_cal_leave_early_ifnoclockout` int(11) NOT NULL,
  `min_aftr_off_dutty_cal_ot` int(11) NOT NULL,
  `min_bfr_on_dutty_cal_ot` int(11) NOT NULL,
  `work_time_day_off_cal_ot` int(11) NOT NULL,
  `active` int(11) NOT NULL DEFAULT '1',
  `isnextday` int(11) NOT NULL DEFAULT '0',
  `shift_allowance` varchar(11) NOT NULL,
  `otcomponents` varchar(11) NOT NULL,
  `start_date_effective` date NOT NULL,
  `end_date_effective` date NOT NULL,
  `strict_monitorings` char(1) NOT NULL DEFAULT 'N',
  `minutes_per_half` int(11) NOT NULL,
  `is_multiple_days` varchar(2) NOT NULL DEFAULT 'N',
  `no_of_shift_days` float NOT NULL,
  `is_exception` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`day_time_seq`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- 2026-06-09 07:57:36 UTC
