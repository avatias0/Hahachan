<?php

/*
 * Phlat - FlatFiles PHP Interface
 * Phlat is a simple interface to work easily with flatfiles
 * Programmed by Federico Ramirez (aka fedekun) 
 * This class is licenced under the GPL licence
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
 
class phlat
{
	/*
	 * Private Fields
	 */
	private $tableName;
	private $tableFields = array();
	private $data;
	private $useGzip = true;	// Table will be smaller but take longer to load
	private $gzipLevel = 9;
	
	/**
	 * Create or load a table / database
	 * 
	 * You must use the tableFields parameter only for creating, not loading
	 * (If you created a DB but did not add anything, also, use the parameter)
	 * @return 
	 * @param string $tableName
	 * @param array $tableFields[optional]
	 * @param boolean $useGzip[optional]
	 * @param integer $gzipLevel[optional]
	 */
	function __construct($tableName, $tableFields=null, $useGzip=false, $gzipLevel=9)
	{
		$this->useGzip = $useGzip;
		$this->gzipLevel = $gzipLevel;
		
		if(file_exists($tableName))
		{
			if($this->useGzip)
				$this->data = unserialize(gzinflate(implode('', file($tableName))));
			else
				$this->data = unserialize(implode('', file($tableName)));
		}
		else
		{	// Create table if not exists
			$fp = fopen($tableName, 'w+');
			fclose($fp);
		}
		
		// Check for table fields
		if(!empty($tableFields))
			$this->tableFields = $tableFields;
		else if(!empty($this->data[0]['id']))
		{
				$tFields = array();
				foreach($this->data[0] as $f => $value)
					$tFields[] = $f;
				$this->tableFields = $tFields;
		}
		else
			error('The database has been created but there is no data yet, you must specify the parameter TableFields.', true);
		
		// All tables must have an ID
		if(!in_array('id', $this->tableFields))
			$this->tableFields[] = 'id';
		
		$this->tableName = $tableName;
	}
	
	/*
	 * Private Methods
	 */
	
	private function save()
	{
		if($fp = fopen($this->tableName, 'w+'))
		{
			if($this->useGzip)
				fwrite($fp, gzdeflate(serialize($this->data), $this->gzipLevel));
			else
				fwrite($fp, serialize($this->data));
			fclose($fp);
		}
		else
			$this->error('Could not save data.');
	}
	
	private function error($message, $die=false)
	{
		if(!$die)
			echo '<p style="font-family: Verdana; font-size: 11px; color:#333333;"><b>An error has occurred:</b> ' . $message . '</p>';
		else
			die('<p style="font-family: Verdana; font-size: 11px; color:#333333;"><b>A fatal error has occurred:</b> ' . $message . '</p>');
	}
	
	/*
	 * Public Methods
	 */
	
	/**
	 * Add data into the database
	 * 
	 * Adds an array to the data array, the array must be array(field=>value);
	 * If you dont want to add a field you can leave it empty
	 * "id" field is ignored.
	 * @return
	 * @param array $data
	 */
	public function add($data)
	{
		$tmpData = array();
		
		// Check for every field to handle empty ones
		foreach($this->tableFields as $field)
		{
			if(!empty($data[$field]))
				$tmpData[$field] = $data[$field];
			else
				$tmpData[$field] = null; // May change later?			
		}
		
		// Auto Increment ID
		$tmpData['id'] = $this->data[count($this->data)-1]['id'] + 1; // Latest id + 1
		
		$this->data[] = $tmpData;
		
		$this->save();
	}
	
	/**
	 * Get all data
	 * 
	 * Returns all data in an array
	 * @return array Complete data array
	 */
	public function get()
	{
		return $this->data;
	}
	
	/**
	 * Selects data from the dynamic index
	 * 
	 * Selects data using the dynamic index, faster
	 * @param integer $index
	 * @return array An array containing selected data
	 */
	public function select($index)
	{
		return $this->data[$index];
	}
	
	/**
	 * Select data whithin a range
	 * 
	 * Select data using the data array indexes, faster
	 * @return array An array containing selected data
	 * @param integer $start
	 * @param integer $end[optional]
	 */
	public function selectRange($start, $end=false)
	{
		$returnData = array();
		if(!$end)
		{
			for($i = $start; $i < count($this->data); $i++)
				if(!empty($this->data[$i])) $returnData[] = $this->data[$i];
			return $returnData;
		}
		else
		{
			for($i = $start; $i <= ($end > count($this->data) ? count($this->data) : $end); $i++)
				if(!empty($this->data[$i])) $returnData[] = $this->data[$i];
			return $returnData;
		} 
	}
	
	/**
	 * Select data within a range
	 * 
	 * Select data using the internal id, order ascendant
	 * @return array An array containing selected data
	 * @param integer $start
	 * @param integer $end[optional]
	 */
	public function selectRangeAt($start, $end=false)
	{
		$returnData = array();
		foreach($this->data as $data)
		{
			if(!$end)
			{
				if($data['id'] >= $start)
					$returnData[] = $data;
			}
			else
			{
				if($data['id'] >= $start && $data['id'] <= $end)
					$returnData[] = $data;
			}
		}
		return $returnData;
	}
	
	/**
	 * Select a single element
	 * 
	 * Select data by field value, for example, by id
	 * Not recommended if you want to select the latest 10 or first 10
	 * Use bulkSelect instead.
	 * @return array An array containing selected data
	 * @param string $field
	 * @param string $value
	 */
	public function selectAt($field, $value)
	{
		foreach($this->data as $data)
		{
			if($data[$field] == $value)
				return $data;
		}
		$this->error('Not found field <b>' . $field . '</b> with value <b>' . $value . '</b>');
	}
	
	/**
	 * Select all entries where field = value
	 * 
	 * Selects all entries where field = value, for example, reply_id = 1
	 * @return array An array containing selected entries 
	 * @param string $field
	 * @param string $value
	 * @param integer $limit[optional]
	 */
	public function selectWhereAt($field, $value, $limit=0)
	{
		if(empty($this->data)) return false;
		
		$tmpData = array();
		$i = 0;
		
		foreach($this->data as $data)
		{
			if($data[$field] == $value)
			{
				$tmpData[] = $data;
				$i++;
				if($i == $limit && $limit > 0)
					break;
			}
		}
		
		if($i>0) return $tmpData;
		else return false;
	}
	
	/**
	 * Select by field, ordered desc or asc
	 * 
	 * Select data by field name, and order it descendant or ascendant
	 * Can specify a limit to return from 0 to that limit
	 * And also an start to return frm start to limit
	 * @return array An array containing selected fields ordered
	 * @param string $field
	 * @param string $desc[optional]
	 * @param integer $limit[optional]
	 * @param integer $start[optional]
	 */
	public function selectByAt($field, $desc=false, $limit=0, $start=0)
	{
		if(!in_array($field, $this->tableFields))
			error('Field '.$field.' not in table ' . $this->tableName, true);
		if(empty($this->data[0]['id']))
			return array();
		
		$tmpData = array();
		
		foreach($this->data as $data)
			$tmpData[$data[$field]] = $data;
		
		ksort($tmpData);
		$tmpData = $desc ? array_reverse($tmpData) : $tmpData;
		if($limit > 0)
		{
			$ret = array();
			$i = 0;
			$limit += $start;
			foreach($tmpData as $entry)
			{
				if($i == $limit)
					break;
				if($i >= $start)
					$ret[] = $entry;
				$i++;
			}
			return $ret;
		}
		return $tmpData;
	}
	
	/**
	 * Select data using logic
	 * 
	 * For example, "select data where user=fedekun AND email=test@test.com" 
	 * For that to work, $select must be array('user'=>'fedekun', 'email'=>'test@test.com')  
	 * and $logic can be empty or 'AND'. If its empty, the default value is AND. Valid values are AND and OR. 
	 * Other values are ignored and treated as AND.<br />
	 * This is not very fast as it loops through all items in the data array, avoid if you can.
	 * @return array An array containing selected data
	 * @param array $select
	 * @param string $logic[optional]
	 */
	public function selectComplex($select, $logic='AND')
	{
		$found = false;
		$logic = (strtoupper($logic) == 'OR') ? '||' : '&&';
		$tmpData = array();
		
		// Generate string to be evaluated
		$evalString = '$found = ';
		foreach($select as $key=>$val)
			$evalString .= '$data["' . $key . '"] == "' . $val . '" ' . $logic . ' ';
		$evalString = substr($evalString, 0, strlen($evalString)-4);
		$evalString .= ';';
		
		foreach($this->data as $data)
		{
			eval($evalString);
			if($found)
			{
				$tmpData[] = $data;
				$found = false;
			}
		}
		
		return $tmpData;
	}
	
	/**
	 * Edit data with a given field value
	 * 
	 * <b>Cannot modify the id field!</b>
	 * @return 
	 * @param string $field
	 * @param string $value
	 * @param array $editWith
	 */
	public function editAt($field, $value, $editWith)
	{
		$found = false;
				
		for($i = 0; $i < count($this->data); $i++)
		{
			if($this->data[$i][$field] == $value)
			{
				foreach($this->tableFields as $f)
				{
					if(!empty($editWith[$f]) && $f != 'id')
						$this->data[$i][$f] = $editWith[$f];
					else if($f != 'id')
						$this->data[$i][$f] = null;
				}
				
				$this->save();
				$found = true;
				break;
			}
		}
		
		if(!$found)
			$this->error('Not found field <b>' . $field . '</b> with value <b>' . $value . '</b>');
	}
	
	/**
	 * Deletes data with a given field and value
	 * 
	 * For example delete entry with id = 10
	 * @return 
	 * @param string $field
	 * @param string $value
	 */ 
	public function deleteAt($field, $value)
	{
		$found = false;
		$tmpData = array();
		foreach($this->data as $data)
		{
			if($data[$field] == $value)
				$found = true;
			else
				$tmpData[] = $data;
		}
		$this->data = $tmpData;
		if($found)
			$this->save();
		else
			$this->error('Not found field <b>' . $field . '</b> with value <b>' . $value . '</b>');
	}
	
	/**
	 * Deletes data whithin a range
	 * 
	 * Deletes a range of data from the data array using the array indexes, <b>not the data ids</b>
	 * @return 
	 * @param integer $start
	 * @param integer $end
	 */
	public function deleteRange($start, $end=null)
	{
		$found = false;
		$tmpData = array();
		
		if(empty($end))
			$end = count($this->data)-1;
		
		for($i = 0; $i < count($this->data); $i++)
		{
			if($i >= $start && $i <= $end)
				$found = true;
			else
				$tmpData[] = $this->data[$i];
		}
		
		if($found)
		{
			$this->data = $tmpData;
			$this->save();
		}
	}
	
	/**
	 * Deletes data within a range
	 * 
	 * Delete a range of data using their ids
	 * It may take time if there are many entries, try using deleteRange if you can. 
	 * That will work only when you want to delete the last/first x entries thought.
	 * @return 
	 * @param integer $start
	 * @param integer $end
	 */
	public function deleteRangeAt($start, $end)
	{
		$found = false;
		$tmpData = array();
		foreach($this->data as $data)
		{
			if($data['id'] >= $start && $data['id'] <= $end)
				$found = true;
			else
				$tmpData[] = $data;
		}
		
		if($found)
		{
			$this->data = $tmpData;
			$this->save();
		}
	}
	
	/**
	 * Clear database
	 * 
	 * Deletes all data from the database, <b>including fields</b>.
	 * @return 
	 */
	public function clear()
	{
		$this->data = array();
		$this->save();
	}
	
	/**
	 * Get database size
	 * 
	 * Gets the number of entries in the "table"
	 * @return integer An integer representing the number of entries in the data array
	 */
	public function getSize()
	{
		return count($this->data);
	}
	
	/**
	 * Gets table fields
	 * 
	 * @return array An array containing the names of the fields
	 */
	public function getTableFields()
	{
		return $this->tableFields;
	}
	
	/**
	 * Gets the table name
	 * 
	 * @return string A string containing the name of the current table
	 */
	public function getTableName()
	{
		return $this->tableName;
	}
}
?>