<?php
include_once 'includes/database.php';

class Admin
	{
    	function verify_login_admin()
			{
					global $config,$conn;
					
					if($_SESSION['ADMINID'] != "" && is_numeric($_SESSION['ADMINID']) && $_SESSION['ADMINUSERNAME'] != "" && $_SESSION['ADMINPASSWORD'] != "")	{
						$query = "SELECT * FROM administrators 
							WHERE username='".mysql_real_escape_string($_SESSION['ADMINUSERNAME'])."' 
								AND password='".mysql_real_escape_string($_SESSION['ADMINPASSWORD'])."' 
								AND ADMINID='".mysql_real_escape_string($_SESSION['ADMINID'])."'";
						$executequery=$conn->execute($query);
						
						if(mysql_affected_rows()==1)	{
						
						}
						else 	{
							header("location:$config[adminurl]/index.php");
							exit;
						}
						
					}
					else 	{
						header("location:$config[adminurl]/index.php");
						exit;
					}
			}
    }
?>