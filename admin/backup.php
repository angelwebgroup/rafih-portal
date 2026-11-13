<?php
//session_start();
//error_reporting(0);
//include_once("auth.php");
//include_once("db/conn.php");
//include_once("lib.php");
/* Get all the required files and functions */
//require(HESK_PATH . 'hesk_settings.inc.php');
//require(HESK_PATH . 'inc/common.inc.php');
//require(HESK_PATH . 'inc/admin_functions.inc.php');
//hesk_load_database_functions();
//hesk_session_start();
//hesk_dbConnect();
//hesk_isLoggedIn();
if($_POST['submit'])
{ 

	/* backup the db OR just a table */
	function backup_tables($host,$user,$pass,$name,$tables = '*')
	{ //echo $name;exit;
	 //$set_link = mysqli_connect($set['db_host_no_port'], $set['db_user'], $set['db_pass'], $set['db_name'], intval($set['db_port']) );
		$link = mysqli_connect($host,$user,$pass);
		mysqli_select_db($link,$name);
		
		//get all of the tables
		if($tables == '*')
		{
			$tables = array();
			$result = mysqli_query($link,'SHOW TABLES');
			while($row = mysqli_fetch_row($result))
			{
				$tables[] = $row[0];
			}
		}
		else
		{
			$tables = is_array($tables) ? $tables : explode(',',$tables);
		}
		
		//cycle through
		foreach($tables as $table)
		{
			$result = mysqli_query($link,'SELECT * FROM '.$table);
			$num_fields = mysqli_num_fields($result);
			
			//$return.= 'DROP TABLE '.$table.';';
			$row2 = mysqli_fetch_row(mysqli_query($link,'SHOW CREATE TABLE '.$table));
			$return.= "\n\n".$row2[1].";\n\n";
			
			for ($i = 0; $i < $num_fields; $i++) 
			{
				while($row = mysqli_fetch_row($result))
				{
					$return.= 'INSERT INTO '.$table.' VALUES(';
					for($j=0; $j<$num_fields; $j++) 
					{
						$row[$j] = addslashes($row[$j]);
						$row[$j] = preg_replace("/\n/","\\n",$row[$j]);
						if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
						if ($j<($num_fields-1)) { $return.= ','; }
					}
					$return.= ");\n";
				}
			}
			$return.="\n\n\n";
		}
		
		//save file
		$handle = fopen('backups/db-backup-'. date("d-M-Y").'_' . time().'-'.'.sql','w+');
		fwrite($handle,$return);
		fclose($handle);
		
		
	}
	
	backup_tables("a2nlmysql61plsk.secureserver.net:3306","hesk","Help-Desk@2023#","hesk");
	
	$file='backups/db-backup-'. date("d-M-Y").'_' . time().'-'.'.sql'; //file location 
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="'.basename($file).'"'); 
	header('Content-Length: ' . filesize($file));
	readfile($file);

}
//include("header.php");



?>
<form action="" method="post">
<input type="submit" name="submit" value="Click Here Take Database Backup Now!" class="txt-blk" />
</form>							  