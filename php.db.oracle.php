<?php

class phpDbOracle
{
	protected $user 	= 'USER';
	protected $pass 	= 'PASS';
	protected $instanc 	= 'DBINSTANCE';
	protected $c 		= false;

	// Connect to DB
	function ociConnect()
	{
		putenv("NLS_LANG=AMERICAN_AMERICA.WE8ISO8859P9");
		putenv("NLS_LANG=AMERICAN_AMERICA.AR8ISO8859P6");
		//putenv("NLS_DATE_FORMAT=yyyy-mm-dd");
		$this->c = oci_connect($this->user, $this->pass, $this->instanc);
		if (!$this->c)
		{
		   $m = oci_error();
		   echo $m['message'], "\n";
		   exit;
		}
		return $this->c;
	}

	// Disconnect from DB
	function ociDisconnect()
	{
		oci_close($this->c);
	}

	// get Number of Records
	function ociNumRows($tabel,$params)
	{
		$pq 	= getSQLQuery($params);
		$q 		= "SELECT COUNT(*) as COUNT FROM ".$tabel.' '.$pq;
		$stid 	= oci_parse($this->c, $q);
		$stid 	= ociBindAll($stid,$params);
		$rs 	= oci_execute($stid);
		$row 	= oci_fetch_assoc($stid);
		return $row['COUNT'];
	}

	// Bind All Values to Keys
	function ociBindAll($stid, $params)
	{
		foreach ($params as $param => $val)
			oci_bind_by_name($stid,$val[0],$val[1]);
		return $stid;
	}

	// SQL "SELECT [?] WHERE ...."
	function getSQLSelectFields($params)
	{
		$q = '';
		foreach ($params as $param => $val)
		{
			// Check Fields
			$val[0] = checkSelectType($val[0]);
			// Assign Value
			$q .= ' '.$val[0];
			if(end($params) !== $val)
	    		$q .= ',';
		}
		return $q;
	}

	// AND FIELD NOT IN [(?)]
	function getSQLItems($params)
	{
		$lastElement = end($params);
		foreach ($params as $par => $val)
		{
			// Add Values
			if(isset($fields)) $fields .= ",'".$val."'"; else $fields = "('".$val."'";

			// Close Statment
			if($val == $lastElement)
				if(isset($fields))
					$fields .= ")";
		}
		return $fields;
	}

	// SELECT * FROM TABLE [?]
	function getSQLQuery($params)
	{
		$i=0;
		$q='';
		foreach ($params as $param => $val)
		{
			if($i == 0) $kw = 'WHERE '; else $kw = 'AND';
			$q .= ' '.$kw.' '.$param.' = '.$val[0];
			$i++;
		}
		return $q;
	}

	// UPDATE TABLE SET [?]
	function getSQLUpdate($params)
	{
		$sql = '';
		$count = count($params)-1;
		$i = 0;
		foreach ($params as $key => $val)
		{
			// Check if Key is Date
			$val[0] = checkInsertType($val[0]);
			$sql .= $key.' = '.$val[0];
			if($i != $count) $sql .= ', ';
			$i++;
		}
		return $sql;
	}

	// INSERT INTO TABLE [?]
	function getSQLINSERT($params)
	{
		$lastElement = end($params);
		foreach ($params as $par => $val)
		{
			// Check if Key is Date
			$val[0] = checkInsertType($val[0]);
			// Add Values
			if(isset($fields)) $fields .= ','.$par; else $fields = "(".$par;
			if(isset($values)) $values .= ','.$val[0]; else $values = "(".$val[0];

			// Close Statment
			if($val == $lastElement)
			{
				if(isset($fields))
				{
					$fields .= ")";
					$values .= ")";
				} 
			}
		}
		return $fields." VALUES ".$values;
	}

	function genID()
	{
	    $rand1 	= date('jis');
	    $rand1 .=  substr(time().substr(microtime(),2,4),-2);
	    return $rand1;
	}
}
?>