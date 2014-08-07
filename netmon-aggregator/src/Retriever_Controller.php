<?php
	require_once('../config/base_config.php');
	
	
	
	
	//get defined retrievers from database whose intervall is matching the current time
	$crawl_id=floor((time()-FIRST_CRAWL_TIME)/CRAWL_DURATION);
	
	//initiate retrievers in order of their priority and only if they are in the correct interval
	try {
		$stmt = DB::getInstance()->prepare("	SELECT 
								Retrievers.id AS retriever_id,priority,class_name 
							FROM 
								Retrievers
							WHERE 
                                                                ? % Retrievers.interval = 0
							ORDER BY 
								priority ASC
							");
		$stmt->execute(array($crawl_id));
		$rows = $stmt->fetch_all(PDO::FETCH_ASSOC);
	} catch(PDOException $e) {
		echo $e->getMessage();
	}
	
	foreach($rows AS $row){
		require_once(ROOT_DIR.'/retrieving/retrievers'.$row['class_name'].'.class.php');
		$retriever_list[]=new $row['class_name']($row['retriever_id']);
		
		$retriever_list[count(retriever_list)-1].retrieve();
	}
	//start retrieval
	
	//filter double entries
	
	
	
	
	
	
	
	//insert into mysql
	
	store_retrieval($Router_List);
	public function store_retrieval($rl){		
			try {
				$stmt = DB::getInstance()->prepare("	SELECT DISTINCT 
										COLUMN_NAME AS name,COLUMNS.TABLE_NAME AS steadiness
									FROM 
										TABLES,COLUMNS
									WHERE
										TABLES.TABLE_SCHEMA='netmon_ng' AND
										COLUMNS.TABLE_NAME=TABLES.TABLE_NAME AND
										TABLES.TABLE_NAME LIKE 'Router_Status_S%'
									ORDER BY 
										steadiness, name
									");
				$stmt->execute(array($crawl_id));
				$attributes = $stmt->fetch_all(PDO::FETCH_ASSOC);
				$tables=array();
				foreach($attributes as $a){
					$table_columns[]=$a['name'];
					$tables[$a['steadiness']][]=$a['name'];
				}
				
				$table_columns=implode(",",$table_columns);
				
				$steadinesses=array_keys($tables)
				$tables_froms=implode(",",$steadinesses);
				
				for($i=0;$i<count($tables)-1;$i++){
					$steadinesses[$i]=$steadinesses[$i].".router_id = ".$steadinesses[$i+1].".router_id";
				}
				
				$table_wheres=implode(" AND ",$steadinesses);
				
				//TODO: integrate only last retrieval/crawl
				try {
					$stmt = DB::getInstance()->prepare("	SELECT 
											".$table_columns." 
										FROM 
											".$table_froms."
										WHERE 
											".$table_wheres."
										");
					$stmt->execute(array($crawl_id));
					$attributes = $stmt->fetch_all(PDO::FETCH_ASSOC);
				
				
				
				
				
				
				
				try {
				
				
				} catch(PDOException $e) {
					die $e->getMessage();
				}
				
				
			} catch(PDOException $e) {
				die $e->getMessage();
			}
		}


?>