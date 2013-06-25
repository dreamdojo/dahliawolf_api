<?php

class DatabaseHelper
    {
    	public function __construct($databaseName = "")
        	{
				include($_SERVER['DOCUMENT_ROOT'] . '/classes/config.php');
				
				$this->host = $host;
				$this->database = $database;
				$this->username = $username;
				$this->password = $password;
		
				$this->openConnection();
				
        	}
			
		public function __destruct() 
			{
				unset ($this->conditions);
				unset ($this->data);
				unset ($this->columns);
				unset ($this->table);
				unset ($this->connection);
				unset ($this->password);
				unset ($this->username);
				unset ($this->database);
				unset ($this->host);
				$this->closeConnection();
			}
		
		public function returnTableName()
			{
				return $this->database;
			}
			
		private $host;
		private $database;
		private $username;
		private $password;
		private $connection;    
		private $table;
		private $columns;
		private $data;
		private $escaped_data;
		private $conditions;
		private $return_data;

		private function openConnection()
        	{
        		$this->connection = mysql_connect($this->host, $this->username, $this->password);
				// or $this->ErrorHandler(mysql_error());
        		mysql_select_db($this->database, $this->connection);
				// or	$this->ErrorHandler(mysql_error());
        	}
			
    	private function closeConnection()
        	{
        		if (isset($this->connection))
            		{
            			mysql_close($this->connection);
            		}
        	}

		private function insertData()
        	{
        		$columnString = $this->getColumnString();
        		$columnValues = $this->getValuesString();
        		$sql = "INSERT INTO $this->table ($columnString) VALUES ($columnValues) ";
        		mysql_query($sql) or die(mysql_error());
        		return mysql_insert_id();
        	}
			
    	private function updateData()
        	{
        		$updateString = $this->getUpdateString();
        		$sql = "UPDATE $this->table SET $updateString $this->conditions";
				mysql_query($sql) or die(mysql_error());
        	}
			
    	private function selectData()
        	{
        		$columnString = $this->getColumnString();
        		$sql = "SELECT $columnString FROM $this->table $this->conditions";
				//echo $sql."<br><br>-----<br><br>";
				$q = mysql_query($sql)  or die(mysql_error());
        		$this->return_data = $q;
        	}
			
    	private function deleteData()
        	{
        		$sql = "DELETE FROM $this->database.$this->table $this->conditions";
        		mysql_query($sql) or die(mysql_error());
        	}
			
    	private function getColumnString()
        	{
        		$columnString ="";
        		$columnCount = count($this->columns);
        
				for ($row = 0; $row < $columnCount; $row++)
            		{
            			if($columnString =="")
  							{
								//$columnString .= "`".$this->columns[$row]."`";
								$columnString .= $this->columns[$row];
							}
            			else
                			{
								//$columnString .= ", `". $this->columns[$row]."`";
								$columnString .= ", ". $this->columns[$row];
							}
            		}
        		return $columnString;
        	}
			
     	private function getValuesString()
        	{
        		$valuesString ="";
        		$columnCount = count($this->escaped_data);
        
				for ($row = 0; $row < $columnCount; $row++)
            		{
            			if($valuesString =="")
                			{
								$valuesString .= "'".$this->escaped_data[$row]."'";
							}
            			else
                			{
								$valuesString .= ", '". $this->escaped_data[$row]."'";
							}
            		}
				return $valuesString;
        	}
     
	 	private function getUpdateString()
        	{
        		$updateString ="";
        		$columnCount = count($this->columns);
				
				for ($row = 0; $row < $columnCount; $row++)
            		{
            			if($updateString =="")
                			{
								$updateString .= $this->columns[$row] ."= '".$this->escaped_data[$row]."'";
							}
            			else
                			{
								$updateString .= ", ".$this->columns[$row]." = '".$this->escaped_data[$row]."'";
							}
            		}
        
				return $updateString;
        	}
			
    	private function escapeData()
        	{
        		if (isset($this->escaped_data))
            		{
            			unset($this->escaped_data);
            		}
            	
				$count = count($this->data);
           
				for ($i = 0; $i < $count; $i++)
                	{
						$this->escaped_data[] = mysql_real_escape_string($this->data[$i]); 
                	}
        	}
    
		public function Insert($table, $columns, $data)
        	{
				$this->table = $table;
				$this->columns = $columns;
				$this->data = $data;
				$this->openConnection();
				$this->escapeData();
				$id = $this->insertData();
				$this->closeConnection();
				return $id;
        	}
    
		public function Update($table, $columns, $data, $conditions)
        	{
				$this->table = $table;
				$this->columns = $columns;
				$this->data = $data;
				$this->conditions = $conditions;
				$this->openConnection();
				$this->escapeData();       
				$this->updateData();
				$this->closeConnection();
				return "Data Updated";
        	}
			
    	public function Delete($table, $conditions)
        	{
				$this->table = $table;
				$this->conditions = $conditions;
				$this->openConnection();
				$this->deleteData();
				$this->closeConnection();
				return "Data Deleted";
        	}
			
    	public function Select($table, $columns, $conditions="")
        	{
				$this->table = $table;
				$this->columns = $columns;
				$this->conditions = $conditions;
				$this->openConnection();
				$this->selectData();
				$this->closeConnection();
				return $this->return_data;
        	}
			
    	public function createDatabase($databaseName)
        	{
				$this->setDBAdminCredentials();
				$this->openMasterConnection();
				$sql = "CREATE DATABASE `$databaseName`";
				mysql_query($sql);
        	}
			
    	public function dropDatabase($databaseName)
        	{
				$this->setDBAdminCredentials();
				$this->openMasterConnection();
				$sql = "DROP DATABASE `$databaseName`";
				mysql_query($sql);        
        	}
	
    	public function ConvertMySQLResultToArray($MySQLResult)
        	{
         		settype($retval,"array");
         		for($i=0; $i<mysql_numrows($MySQLResult); $i++)
            		{
            			for($j=0; $j<mysql_num_fields($MySQLResult); $j++)
                			{
                				$retval[$i][mysql_field_name($MySQLResult,$j)] = mysql_result($MySQLResult,$i,mysql_field_name($MySQLResult,$j));
								//echo $retval[$i][mysql_field_name($MySQLResult,$j)]." - ".mysql_field_name($MySQLResult,$j)."<br>";
                			}
            		}
				//echo "<pre>";
				//print_r($retval);
        		return $retval;        
        	}
		
		public function crud($tablesArray, $getFormVars, $dbQueryType, $where = "")
			{
				//echo "$tablesArray, $getFormVars, $dbQueryType";
				//print_r($getFormVars);
				$databaseName = $database; //PULLED FROM CONFIG
				
				//GRAB THE TABLE NAMES
				if($tablesArray == "INPOST")
					{
						$tablesArray = array();
			
						foreach($getFormVars AS $ker=>$ver)
							{
								if(preg_match("/@@@/", $ker))
									{
										$splitIntoArray = explode("@@@", $ker);
										$tbName = base64_decode($splitIntoArray[0]);
										$tablesArray[] = $tbName;
									}
							}
						$tablesArray = array_unique($tablesArray);
					}
				
				foreach($tablesArray AS $valz) 
					{
						$tableName = $valz;
						//CRUD
						$counter = 0;
						$fields = array();
						$valueArray = array();
						
						if($dbQueryType == "Select")
							{
								if(empty($getFormVars))
									{
										$getColumns = $this->showColumns($tableName);
										$fields_name = $getColumns[fields_name];
										$fields_type = $getColumns[fields_type];
										
										$fields = $fields_name;
									}
								else $fields = $getFormVars;
							}
						else
							{
								$getColumns = $this->showColumns($tableName);
								$fields_name = $getColumns[fields_name];
								$fields_type = $getColumns[fields_type];
							}
							
						
						if( ($dbQueryType == "Update") || ($dbQueryType == "Insert") )
							{
								foreach($fields_name AS $value)
									{
										$postValue = base64_encode($tableName)."@@@".$value;
										if($counter == 0) $primaryID = $fields_name[0];
										
										if($tableName == "users") 
											{ 
												$postID = base64_encode($tableName)."@@@id";
												$postEmail = base64_encode($tableName)."@@@email_address"; 
												$postPass = base64_encode($tableName)."@@@password"; 
												$postCPass = base64_encode($tableName)."@@@confirm_password"; 
											}
										
										$theType = explode("(", $fields_type[$counter]); //int, varchar, datetime, etc
										//echo "$value = $getFormVars[$value] -  $theType[0]<br>"; exit();
										//IF TRYING TO CHANGE EMAIL ADDRESS, CHECK FIRST AND THEN UPDATE
										if( ($getFormVars[old_email] != $getFormVars[$postEmail])  && ($tableName == "users") && ($value == "email_address") )
											{
												$user = new Users();
												$isValid = $user->checkUserExists($getFormVars[$postEmail]);
												if($isValid == 0)
													{
														//SET THE FIELDNAMES
														$fields[] = "email_address";
														
														//SET THE VALUE
														$valueArray[] = $getFormVars[$postEmail];
													}
												else
													{
														if($dbQueryType == "Update")
															{
																?>
																<script>
																	alert("This email address is already in use.  The rest of your changes will be updated, but if you want to change the email address, then please try using a different email address.");
																</script>
																<?
															}
														else
															{
																return "Duplicate Email";
															}
													}
											}
										//IF TRYING TO CHANGE PASSWORD, CHECK FIRST AND THEN UPDATE
										elseif( ($getFormVars[$postCPass] == $getFormVars[$postPass]) && ($getFormVars[$postPass] != "") && ($tableName == "users") && ($value == "password") ) 
											{
												$user = new Users();
												$passUpdate = $user->updatePassword($getFormVars[$postID], $getFormVars[$postPass]);
											}
										elseif( (($value == "password") || ($value == "ip_address") || ($value == "email_address") || ($value == "username") || ($value == "agree_terms") || ($value == "date_created")) && ($dbQueryType == "Update") ) 
											{
												
											}
										else
											{
												if($theType[0] == "datetime")
													{
														//ALWAYS UPDATE DATE EDITED
														$now = date('Y-m-d H:i:s');
														
														$fields[] = $value;
														$valueArray[] = $now;
													}
												elseif($theType[0] == "date")
													{
														$now = date('Y-m-d');
														
														$fields[] = $value;
														$valueArray[] = $now;
													}
												else
													{
														//SET THE FIELDNAMES
														$fields[] = $value;
														//echo "$postValue<br>";
														//SET THE VALUE
														$jax = str_replace("^",",",$getFormVars[$postValue]);
														$valueArray[] = $jax; 
													}
											}
									} //END IF UPDATE?SELECT
								
								$counter++;
							} //END FOREACH OF THE FIELDS
						
						$table = $tableName;
						$columns = $fields;
						$data = $valueArray;
						
						if($dbQueryType == "Update")
							{
								array_shift($columns);
								array_shift($data);
								$conditions = " WHERE $primaryID=$getFormVars[entityID]";
							}
						else if($dbQueryType == "Delete")
							{
								$conditions = " $where";
							}
						else if($dbQueryType == "Select")
							{
								$conditions = " $where";
							}
						else if($dbQueryType == "Insert")
							{
								//$conditions = " WHERE $primaryID=$getFormVars[entityID]";
							}
						/*
						echo "<pre>";
						print_r($columns);
						print_r($data);
						echo "$table, $columns, $data, $conditions";
						exit();
						*/					
						if($dbQueryType == "Update") 		$this->Update($table, $columns, $data, $conditions);
						else if($dbQueryType == "Delete") 	$this->Delete($table, $conditions);
						else if($dbQueryType == "Select") 	$newUserID = $this->Select($table, $columns, $conditions);
						else if($dbQueryType == "Insert")	
							{
								$newUserID = $this->Insert($table, $columns, $data);
								//echo "<meta http-equiv=\"refresh\" content=\"0; url = ".base64_decode($getFormVars[redirect])."\">";
								//exit();
							}
					}//END FOREACH TABLE ARRAYS
				//exit();
				return $newUserID;
			}
		
		public function showTables()
            {
				$databaseName = $this->database;
                //$this->setDBAdminCredentials();
                $this->openConnection();
                $sql = "SHOW TABLES FROM $databaseName";
				$show_tables = mysql_query($sql) or die(mysql_error());
				
				return $show_tables;
			}
		//GRAB ALL COLUMNS FROM DB
		public function showColumns($tableName, $excludeList = "", $includeList = "")
                    {
                            $databaseName = $this->database;
                            //$this->setDBAdminCredentials();
                            $this->openConnection();
                            $sql = "SHOW COLUMNS FROM $tableName FROM $databaseName";
                            //echo "<br>xxxx".$sql;
                            //print_r($includeList);
                            //$show_tables = mysql_list_fields($databaseName, $tableName) or die(mysql_error());
                            $show_tables = mysql_query($sql) or die(mysql_error());

                            //STORE COLUMNS AND TYPE IN AN ARRAY
                            $fields_name = array();
                            $fields_type = array();
                            $final_array = array();

                            while($fetchColumns = mysql_fetch_assoc($show_tables))
                                    {
                                        if(is_array($includeList))
                                            {
                                                if( in_array($tableName.".".$fetchColumns[Field],$includeList))
                                                    {
                                                            $fields_name[] = $fetchColumns[Field];
                                                            $fields_sqlname[] = $tableName.".".$fetchColumns[Field];
                                                            $fields_type[] = $fetchColumns[Type];
                                                    }
                                            }
                                        elseif(is_array($excludeList))
                                            {
                                                if(!(in_array($tableName.".".$fetchColumns[Field],$excludeList)))
                                                    {
                                                            $fields_name[] = $fetchColumns[Field];
                                                            $fields_sqlname[] = $tableName.".".$fetchColumns[Field];
                                                            $fields_type[] = $fetchColumns[Type];
                                                    }
                                            }
                                        else
                                            {
                                                $fields_name[] = $fetchColumns[Field];
                                                $fields_sqlname[] = $tableName.".".$fetchColumns[Field];
                                                $fields_type[] = $fetchColumns[Type];
                                            }
                                    }

                            $final_array[fields_name] = $fields_name;
                            $final_array[fields_sqlname] = $fields_sqlname;
                            $final_array[fields_type] = $fields_type;
                            //echo "<pre>"; print_r($final_array);
                            //exit();
                            return $final_array;
                        }
			
			
			
			
			
			
		
		//GRABS TABLE COLLECTS DATA
		public function getEntityInfo($entityTable, $primaryKey, $primaryID, $joinKey, $joinTable, $excludeList, $includeList)
			{

                //echo "$entityTable, $primaryKey, $primaryID, $joinKey, $joinTable, $excludeList, $includeList";
//
/*
//BAD QUERY
++++++++++++++++++++++++++++++
SELECT `tasks`.`id`,
           `tasks`.`task`,
           UNIX_TIMESTAMP(due) AS `time`,
           `lists`.`name` AS `list_name`,
           `lists`.`id` AS `list_id`
FROM `tasks`
INNER JOIN `lists` ON lists.id=tasks.list_id
WHERE (tasks.user_id='1' OR assigned='1')
     AND (tasks.done IS NULL)
     AND (tasks.due IS NOT NULL)
ORDER BY `tasks`.`due` ASC
*/
/*
GOOD QUERY OPTION 1
++++++++++++++++++++++++++++++
SELECT `tasks`.`id`,
           `tasks`.`task`,
           UNIX_TIMESTAMP(due) AS `time`,
           `lists`.`name` AS `list_name`,
           `lists`.`id` AS `list_id`
FROM `tasks`
INNER JOIN `lists` ON lists.id=tasks.list_id
WHERE (tasks.user_id='1' OR assigned='1')
   AND tasks.id IN
     (SELECT id
      FROM tasks
      WHERE (tasks.done IS NULL)
        AND (tasks.due IS NOT NULL)
     )
ORDER BY `tasks`.`due` ASC
*/

/*
GOOD QUERY OPTION 2
++++++++++++++++++++++++++++++
SELECT `tasks`.`id`,
            `tasks`.`task`,
            UNIX_TIMESTAMP(due) AS `time`,
            `lists`.`name` AS `list_name`,
            `lists`.`id` AS `list_id`
FROM `tasks`
INNER JOIN `lists` ON lists.id=tasks.list_id
WHERE (tasks.done IS NULL)
    AND (tasks.due IS NOT NULL)
    AND (tasks.user_id='1')
) UNION ALL (
 SELECT `tasks`.`id`,
            `tasks`.`task`,
            UNIX_TIMESTAMP(due) AS `time`,
            `lists`.`name` AS `list_name`,
            `lists`.`id` AS `list_id`
FROM `tasks`
INNER JOIN `lists` ON lists.id=tasks.list_id
WHERE (tasks.done IS NULL)
    AND (tasks.due IS NOT NULL)
    AND (assigned='1' AND tasks.user_id!='1')
) ORDER BY `time` ASC
*/			

/*
JOIN MULTIPLE TABLES
++++++++++++++++++++++++++++++
SELECT p.date, per.name, cc.number, p.value
FROM person AS per
JOIN inscription AS i ON per.id = i.person_id
JOIN pay AS p         ON i.id   = p.inscription_id
JOIN creditcard AS cc ON p.id   = cc.pay_id
*/

/*
JOIN ON MULTIPLE FIELDS/COLUMNS
++++++++++++++++++++++++++++++
SELECT 
    airline, flt_no, fairport, tairport, depart, arrive, fare
FROM 
    flights
INNER JOIN 
    airports from_port ON (from_port.code = flights.fairport)
INNER JOIN
    airports to_port ON (to_port.code = flights.tairport)
WHERE 
    from_port.code = '?' OR to_port.code = '?' OR airports.city='?'
*/
				$db = new DatabaseHelper();
				$returnArray = array();
				
				if(is_array($joinKey))
					{
                                            $jArray = array($primaryKey);
                                            $jTArray = array($entityTable);
                                            $prim = array();

                                            $keyCount = 0;
                                            foreach($joinKey AS $the_value)
                                                {
                                                    if(preg_match("/:/",$joinKey[$keyCount]))
                                                        {
                                                            //hospital_id:city_id
                                                            $jArrays = explode(":", $joinKey[$keyCount]);
                                                            foreach($jArrays AS $vi)
                                                                {
                                                                        $jArray[] = $vi;
                                                                }

                                                            //hospitals:cities
                                                            $jTArrays = explode(":", $joinTable[$keyCount]);
                                                            foreach($jTArrays AS $vit)
                                                                {
                                                                        $jTArray[] = $vit;
                                                                }
                                                        }
                                                    else
                                                        {
                                                            $jArray[] = $joinKey[$keyCount];
                                                            $jTArray[] = $joinTable[$keyCount];

                                                            $jArrr = explode("@",$joinKey[$keyCount]);
                                                            $prim[] = $jArrr[1];
                                                        }

                                                    $keyCount++;
                                                }

                                            $count2 = 1;
                                            $aCount = count($jArray);
                                            $bCount = $aCount - 1;
                                            $columnsql = array();
                                            $columns = array();

                                            //jArray (id/hospital_id) or (id/id@hospital_id:id@city_id,role)
                                            //jTArray (user/hospitals) or (user/hospitals/cities)
                                            for($i=0; $i<$aCount; $i++)
                                                {
                                                    $getColumns2 = $db->showColumns($jTArray[$i], $excludeList, $includeList);
                                                    $columns2 = $getColumns2[fields_name];
                                                    $columnsql2 = $getColumns2[fields_sqlname];
                                                    $columns_type2 = $getColumns2[fields_type];

                                                    //$jArr = explode("@",$jArray[$i]);
                                                    if($i<$bCount)
                                                        {
															$jArr2 = explode("@",$jArray[$count2]);
                                                            if(in_array($jArr2[1],$prim))
                                                                {
                                                                    //PRIMARY JOIN
                                                                    $conditions .= " INNER JOIN ".$jTArray[$count2]." ON ".$jTArray[$count2].".".$jArr2[0]."=".$entityTable.".".$jArr2[1];
																	
                                                                }
                                                            else
                                                                {
                                                                    //SUBJOINS
                                                                    $conditions .= " INNER JOIN ".$jTArray[$count2]." ON ".$jTArray[$count2].".".$jArr2[0]."=".$jTArray[$i].".".$jArr2[1];
                                                                }
                                                        }

                                                    $columnsql = array_merge($columnsql, $columnsql2);
                                                    $columns = array_merge($columns, $columns2);

                                                    $count2++;
                                                }

                                            /*FROM users
                                            INNER JOIN hospitals ON hospitals.id=users.hospital_id
                                            INNER JOIN cities ON cities.hospital_id=hospitals.city_id
                                            WHERE users.id=75

                                            FROM users
                                            INNER JOIN hospitals ON hospitals.id=users.hospital_id
                                            INNER JOIN cities ON cities.id=hospitals.city_id
                                            INNER JOIN user_roles ON user_roles.id=cities.role
                                            WHERE users.id=74
                                            */
                                            /*echo "<pre>";
                                            echo $conditions;
                                            print_r($columnsql);
                                            print_r($columns);
                                            exit();*/
								
					}
				else
					{
                                            $getColumns = $db->showColumns($entityTable, $excludeList, $includeList);
                                            $columns = $getColumns[fields_name];
                                            $columnsql = $getColumns[fields_sqlname];
                                            $columns_type = $getColumns[fields_type];
					}
				
				//PRIMARY
				if( ($primaryID > 0) && ($primaryID != "") ) $conditions .= " WHERE ".$entityTable.".".$primaryKey."=$primaryID";
				
                               /*
				print_r($columnsql);
				echo $conditions;
				exit();*/
				$query = $db->Select($entityTable, $columnsql, $conditions);
				while($result = mysql_fetch_array($query))
					{
						$hArray = array();
						
						$c = 0;
						foreach($columnsql AS $value)
							{
								$hArray[$value] = $result[$c];
								$c++;
							}
						
						$returnArray[] = $hArray;
					}
				
				//JOIN
					
					
				return $returnArray;
			}
			
			
			
			
		//GRABS TABLE COLLECTS DATA
		public function getEntitySQL($entityTable, $primaryKey, $primaryID, $joinKey, $joinTable, $excludeList, $includeList)
                    {
                        $db = new DatabaseHelper();
                        $returnArray = array();
						
						//echo "<pre>";
						//print_r($joinKey);

                        if( (is_array($joinKey)) && ($joinKey[0] != "") )
                            {
								//echo "<pre>";
								//print_r($joinKey);
								$jArray = array($primaryKey);
                                $jTArray = array($entityTable);
                                $prim = array();

                                $keyCount = 0;
                                foreach($joinKey AS $the_value)
                                    {
										//subjoins
                                        if(preg_match("/:/",$joinKey[$keyCount]))
                                            {
                                                //hospital_id:city_id
                                                $jArrays = explode(":", $joinKey[$keyCount]);
                                                foreach($jArrays AS $vi)
                                                    {
                                                        $jArray[] = $vi;
                                                    }

                                                //hospitals:cities
                                                $jTArrays = explode(":", $joinTable[$keyCount]);
                                                foreach($jTArrays AS $vit)
                                                    {
                                                        $jTArray[] = $vit;
                                                    }
                                            }
                                        else
                                            {
                                                $jArray[] = $joinKey[$keyCount];
                                                $jTArray[] = $joinTable[$keyCount];

                                                $jArrr = explode("@",$joinKey[$keyCount]);
                                                $prim[] = $jArrr[1];
                                            }

                                        $keyCount++;
                                    }

								//print_r($jArray);
								//print_r($jTArray);
								
                                $count1 = 0;
								$count2 = 1;
                                $aCount = count($jArray);
                                $bCount = $aCount - 1;
                                $columnsql = array();
                                $columns = array();

                                //jArray (id/hospital_id) or (id/id@hospital_id:id@city_id,role)
                                //jTArray (user/hospitals) or (user/hospitals/cities)
                                for($i=0; $i<$aCount; $i++)
                                    {
                                        //echo "sfsad $jTArray[$i], $excludeList, $includeList<br>";
                                        //print_r($includeList);
                                       
                                        //echo "<pre>";
                                        //print_r($includeList);

                                        $getColumns2 = $db->showColumns($jTArray[$i], $excludeList, $includeList);
                                        /*echo "<pre>";
                                        if($jTArray[$i] == "hospitals")
                                        {
                                            print_r($includeList);
                                        }*/
										//print_r($getColumns2);
                                        $columns2 = $getColumns2[fields_name];
                                        $columnsql2 = $getColumns2[fields_sqlname];
                                        $columns_type2 = $getColumns2[fields_type];

                                        //$jArr = explode("@",$jArray[$i]);
										/*echo "<pre>";
										print_r($jTArray);
										print_r($jArray);
										echo $jArray[3]."<br>";
										echo $count2;*/
                                        if($i<$bCount)
                                            {
                                                //echo "$jArray[$count2]<br>";
												$jArr2 = explode("@",$jArray[$count2]);
                                                //echo $jArr2[1]."<br>";
												//print_r($prim);
												//INNER JOIN procedures ON procedures.id=lead_tickets.procedure_id
                                                
												$conditions .= " INNER JOIN ".$jTArray[$count2]." ON ".$jArr2[0]."=".$jArr2[1];
												
												/*if(in_array($jArr2[1], $prim))
                                                    {
                                                        //PRIMARY JOIN
                                                        $conditions .= " INNER JOIN ".$jTArray[$count2]." ON ".$jTArray[$count2].".".$jArr2[0]."=".$entityTable.".".$jArr2[1];
														//echo "11";
                                                    }
                                                else
                                                    {
                                                        //SUBJOINS
                                                        //$conditions .= " INNER JOIN ".$jTArray[$count2]." ON ".$jTArray[$count2].".".$jArr2[0]."=".$jTArray[$count1].".".$jArr2[1];
														$conditions .= " INNER JOIN ".$jTArray[$count2]." ON ".$jArr2[0]."=".$jArr2[1];
														//echo $conditions."<br>";
														//INNER JOIN user_roles ON user_roles.id=hospitals.role
                                                    }*/
                                            }
										
                                        //print_r($columnsql);
                                        //print_r($columnsql2);
                                        //echo "xxx $columnsql, $columnsql2<BR>";
                                        $columnsql = array_merge($columnsql, $columnsql2);
                                        $columns = array_merge($columns, $columns2);

										$count1++;
                                        $count2++;
                                    }
								//echo $conditions."<br>";
                                /*FROM users
                                INNER JOIN hospitals ON hospitals.id=users.hospital_id
                                INNER JOIN cities ON cities.hospital_id=hospitals.city_id
                                WHERE users.id=75

                                FROM users
                                INNER JOIN hospitals ON hospitals.id=users.hospital_id
                                INNER JOIN cities ON cities.id=hospitals.city_id
                                INNER JOIN user_roles ON user_roles.id=cities.role
                                WHERE users.id=74
                                */
                                /*echo "<pre>";
                                echo $conditions;
                                print_r($columnsql);
                                print_r($columns);
                                exit();*/

                            }
                        else
                            {
                                $getColumns = $db->showColumns($entityTable, $excludeList, $includeList);
                                $columns = $getColumns[fields_name];
                                $columnsql = $getColumns[fields_sqlname];
                                $columns_type = $getColumns[fields_type];
                            }

                        //PRIMARY
                        if( ($primaryID > 0) && ($primaryID != "") ) $conditions .= " WHERE ".$entityTable.".".$primaryKey."=$primaryID";

                        $returnArray = array();
                        //print_r($columnsql);
						//echo "<br>".$columnsql."sdfs";
                        //echo "<Br>";
                       // echo $conditions."<br>";
                        $returnArray[columnsql] = $columnsql;
                        $returnArray[conditions] = $conditions;

                        return $returnArray;
                    }
		
		
		
		
		public function getEntityValue($tableName, $fieldName, $fieldIndentifier, $entityID)
                    {
                        $table = $tableName;
                        $columns = array($fieldName);
                        $conditions = " WHERE $fieldIndentifier=$entityID";
                        $query = $this->Select($table, $columns, $conditions);
                        $result = mysql_fetch_array($query);

                        return $result[$fieldName];
                    }
		
    	}
?>