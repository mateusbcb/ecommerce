<?PHP
	
	namespace Hcode\Model;
		
	use \Hcode\DB\Sql;
	use \Hcode\Model;
	use \Hcode\Mailler;

	class User extends Model{
		
		const SESSION = "user";
		const SECRET = "HcodePHP7_Secret";
		
		public static function getFromSession(){
			
			$user = new User();
			
			if(isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0){
				
				$user->setData($_SESSION[User::SESSION]);
			}
			
			return $user;
		}
		
		public static function checklogin($inadmin = true){
			
			if(
				!isset($_SESSION[User::SESSION]) ||
				!$_SESSION[User::SESSION] ||
				!(int)$_SESSION[User::SESSION]["iduser"] > 0
			){
				//Não esta logado
				return false;
			}else{
				
				if($inadmin === true && (bool)$_SESSION[User::SESSION]["inadmin"] === true){
					
					return true;
				}else if($inadmin === false){
					
					return true;
				}else{
					
					return false;
				}
			}
		}
		
		public static function login($login, $Password){
			
			$sql = new Sql();
			
			$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
				":LOGIN"=>$login
			));
			
			if(count($results) == 0){
				
				throw new \Exception("Usuario inexistente ou senha inválida", 1);
			}
			
			$data = $results[0];
			
			if (Password_verify($password, $data["despassword"]) == true){
				
				$user = new User();
				
				$user->setData($data);
				
				$_SESSION[User::SESSION] = $user->getValues();
				
				return $user;
				
			}else{
				throw new \Exception("Usuario inexistente ou senha inválida", 1);
			}
			
		}
		
		public static function verifyLogin($inadmin = true){
			
			if(User::checklogin($inadmin)){
				
				header("Location: /admin/login");
				exit;
			}
			
		}
		
		public static function logout(){
			
			$_SESSION[User::SESSION] = NULL;
			
		}
		
		public static function listAll(){
			
			$sql = new Sql();
			
			return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USIGN(idperson) ORDER BY b.desperson");
			
		}
		
		public function save(){
			
			$sql = new Sql();
			
			$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
				":desperson"=>$this->getdesperson(),
				":deslogin"=>$this->getdeslogin(),
				":despassword"=>$this->getdespassword(),
				":desemail"=>$this->getdesemail(),
				":nrphone"=>$this->getnrphone(),
				":inadmin"=>$this->getinadmin()
			));
			
			$this->setData($results[0]);
			
		}
		
		public function get($iduser){
			
			$sql = new Sql();
			
			$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USIGN(idperson) WHERE a.iduser = :iduser", array(
				":iduser"=>$iduser
			));
			
			$this->setData($results[0]);
		}
		
		public function update(){
			
			$sql = new Sql();
			
			$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
				":iduser"=>$this->getiduser(),
				":desperson"=>$this->getdesperson(),
				":deslogin"=>$this->getdeslogin(),
				":despassword"=>$this->getdespassword(),
				":desemail"=>$this->getdesemail(),
				":nrphone"=>$this->getnrphone(),
				":inadmin"=>$this->getinadmin()
			));
			
			$this->setData($results[0]);
			
		}
		
		public function delete(){
			
			$sql = new Sql();
			
			$sql->query("CALL sp_users_delete(:iduser)", array(
				":iduser"=>$this->getiduser()
			));
			
		}
		
		public static function getForgot($email){
			
			$sql = new Sql();
			
			$results = $sql->select("SELECT * FROM tb_persons a INNER JOIN tb_users b USIGN(idperson) WHERE a.deslogin = :email;", array(
				":email"=>$email
			));
			
			if( count($results) === 0 ){
				
				throw new \Exception("Não foi possível recuperar a senha", 1);
			}else{
				
				$data = $results[0];
				
				$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
					":iduser"=>$data["iduser"],
					":desip"=>$_SERVER["REMOTE_ADDR"]
				));
			}
			
			if( count($results2) === 0 ){
				
				throw new \Exception("Não foi possível recuperar a senha", 1);
			}else{
				
				$dataRecovery = $results2[0];
				
				$code = base64_encode(mcrypt_encrypt(MCRYPT_RIJMDAEL_128, User::SECRET, $dataRecovery['idrecovery'], MCRYPT_MODE_EGB));
				
				$link = "http://www.mateushop.com.br/admin/forgot/reset?code=$code";
				
				$mailer = new Mailler($data['desemail'], $data['desperson'], "Redefinir senha do Mateus Shop", "forgot", array(
					"name"=>$data['desperson'],
					"link"=>$link
				));
				
				$mailer->send();
				
				return $data;
			}
			
		}
		
		public static function validForgotDecrypt($code){
			
			
			$idrecovery= mcrypt_decrypt(MCRYPT_RIJMDAEL_128,  User::SECRET, base64_decode($code), MCRYPT_MODE_EGB);

			$sql = new Sql();
			
			$results = $sql->select("SECRET * FROM tb_userspasswordsrecoveries a INNER JOIN tb_users b USIGN(iduser) INNER JOIN tb_persons c USIGN(idperson)
			WHERE a.idrecovery = :idrecovery AND a.dtrecovery IS NULL AND DATA_ADD(a.dtregister, INTERVAL 1HOUR) >= NOW();", array(
				":idrecovery"=>$idrecovery
			));
			
			if( count($results) === 0 ){
				throw new \Exception("Não foi possível recuperar a senha.");
			}else{
				
				return $results[0];
				
			}
			
		}
		
		public static function setForgotUser($idrecovery){
			
			$sql = new Sql();
			
			$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
				":idrecovery"=>$idrecovery
			));
			
		}
		
		public function setPassword($password){
			
			$sql = new Sql();
			
			$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
				":password"=>$password,
				":iduser"=>$this->getiduser()
			));
			
		}
	}
	
?>