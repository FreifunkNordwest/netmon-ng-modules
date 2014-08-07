<?php
error_reporting(E_ALL);
abstract class Retriever{
	protected $retriever_id;
	protected $retriever_type_id;
	protected $retriever_type_name;
	protected $specific_params;
	protected $name;
	protected $interval;
	protected $priority;
	protected $uses_generic_matches;
	protected $matches;

	abstract public function start_retrieval();
	abstract public function is_retrieval_finished();
	abstract public function get_data();
	
	
    	
	public function __construct($rid) {
		//load a default-profile at first
		
		$this->retriever_id=$rid;
		$this->fetchRetrieverPreferences();
		
		$this->fetchMatches();
	}
	
	protected function fetchMatches(){
		try {
			$stmt = DB::getInstance()->prepare("	SELECT 
									from_retriever, to_attribute
								FROM 
									(
									SELECT 
										max(belongs_to)	AS most_specific,
										max(generic) 	AS most_important
									FROM	
										Retriever_Matches
									WHERE
										(									(
										(belongs_to = 'all') OR 
										(belongs_to = 'retriever_type' 		AND belongs_to_id=':retriever_type_id') OR
										(belongs_to = 'single_retriever' 	AND belongs_to_id=':retriever_id')
										)AND(
										generic >= :uses_generic_matches
										)
									GROUP BY
										from_retriever
									) AS inner
								WHERE 
									belongs_to	= inner.most_specific AND
									generic		= inner.most_important									
								");
// 			//decide which matches the retriever should use
			switch($this->uses_generic_matches){
				case 'retriever_type': 	$umg=2;break;
				case 'single_retriever':$umg=3;break;
				case 'all': 
				default:		$umg=1;break;
			}
			$stmt->execute(array("uses_generic_matches"=>$uses_generic_matches,":retriever_id"=>$retriever_id,":retriever_type_id"=>$retriever_type_id));
			$rows = $stmt->fetch_all(PDO::FETCH_ASSOC);
			$this->matches = array();
			foreach($rows AS $r ){
				$this->matches[$r['from_retriever']]=$r['to_attribute'];
			}
		} catch(PDOException $e) {
			echo $e->getMessage();
		}
	}
	
	public function fetchRetrieverPreferences(){
		try {
			//get retrievers preferences from database
			$stmt = DB::getInstance()->prepare("	SELECT 
									Retrievers.name, retriever_type_id, Retriever_Types.name, uses_generic_matches, interval, description, specific_params, priority 
								FROM 
									Retrievers,Retriever_Types
								WHERE 
									Retrievers.retriever_type_id = Retriever_Types.id AND
									id=:retriever_id
									
								ORDER BY 
									
								");
			}
			$stmt->execute(array(":generic"=>$umg,":retriever_id"=>$umg,":retriever_type_id"=>$umg));
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			
			$this->uses_generic_matches=$row['uses_generic_matches'];
			$this->retriever_type_id=$row['retriever_type_id'];
			$this->retriever_type_name=$row['Retriever_Types.name'];
			$this->name=$row['Retrievers.name'];
			$this->interval=$row['interval'];
			$this->specific_params=$row['specific_params'];
			$this->priority=$row['priority'];
			
		} catch(PDOException $e) {
			echo $e->getMessage();
		}
	
	}
	


}



?>
