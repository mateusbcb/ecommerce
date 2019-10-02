<?PHP
	
	namespace Hcode\Model;
		
	use \Hcode\DB\Sql;
	use \Hcode\Model;
	use \Hcode\Mailler;

	class Category extends Model{
		
		public static function listAll(){
			
			$sql = new Sql();
			
			return $sql->select("SELECT * FROM tb_categories ORDER BY descategor");
			
		}
	}
	
	public function save(){
			
		$sql = new Sql();
		
		$results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
			":idcategory"=>$this->getidcategory(),
			":descategory"=>$this->getdescategory()
		));
		
		$this->setData($results[0]);
			
	}
	
	public function get($idcategory){
		
		$sql = new Sql();
		
		$results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", array(
			":idcategory"=>$idcategory
		));
		
		$this->setData($results[0]);
		
	}
	
	public function delete(){
		
		$sql = new Sql();
		
		$sql->qury("DELETE FROM tb_categories WHERE idcategory = :idcategory", array(
			":idcategory"=>$this->getidcategory()
		));
		
	}
	
?>