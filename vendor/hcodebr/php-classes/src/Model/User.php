<?PHP
	
	namespace Hcode\Model;
		
	use \Hcode\DB\Sql;
	use \Hcode\Model;
	use \Hcode\Mailler;

	class User extends Model{
		
		const SESSION = "user";
		const SECRET = "HcodePHP7_Secret";
		const ERROR ="UserError";
		const ERROR_REGISTER = "UserErrorRegister";
		const SUCESS = "UserSucess";
		
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
				
				$data['desperson'] = utf8_encode($data['desperson']);
				
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
				":deslogin"=>utf8_decode($this->getdeslogin()),
				":despassword"=>User::getPasswordHash($this->getdespassword()),
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
			
			$data = $results[0];
			
			$data['desperson'] = utf8_encode($data['desperson']);
			
			$this->setData($data);
		}
		
		public function update(){
			
			$sql = new Sql();
			
			$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
				":iduser"=>$this->getiduser(),
				":desperson"=>utf8_decode($this->getdesperson()),
				":deslogin"=>$this->getdeslogin(),
				":despassword"=>User::getPasswordHash($this->getdespassword()),
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
		
		public static function getForgot($email, $inadmin = true){
			
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
				$endereco = "http://www.mateushop.com.br";
				
				if($inadmin === true){
					$link = "$endereco/admin/forgot/reset?code=$code";
				}else{
					$link = "$endereco/forgot/reset?code=$code";
				}
				
				
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
		
		public static function setError($msg){
			
			$_SESSION[User::ERROR] = $msg;
		}
		
		public static function getError(){
			
			$msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : '';
			
			User::clearError();
			
			return $msg;
		}
		
		public static function clearError(){
			
			$_SESSION[User::ERROR] = NULL;
		}
		
		public static function setSucess($msg){
			
			$_SESSION[User::SUCESS] = $msg;
		}
		
		public static function getSucess(){
			
			$msg = (isset($_SESSION[User::SUCESS]) && $_SESSION[User::SUCESS]) ? $_SESSION[User::SUCESS] : '';
			
			User::clearSucess();
			
			return $msg;
		}
		
		public static function clearSucess(){
			
			$_SESSION[User::SUCESS] = NULL;
		}
		
		public static function setErrorRegister($msg){
			
			$_SESSION[User::ERROR_REGISTER] = $msg;
		}
		
		public static function getErrorRegister(){
			
			$msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : '';
			
			User::clearError();
			
			return $msg;
		}
		
		public static function clearErrorRegister(){
			
			$_SESSION[User::ERROR_REGISTER] = NULL;
		}
		
		public static function getPasswordHash($password){
			
			return password_hash($password, PASSWORD_DEFAULT, [
				'cost'=>12
			]);
		}
		
		public static function checkLoginExist($login){
			
			$sql = new Sql();
			
			$results =$sql->select("SELECT * FROM tb_users WHERE deslogin = :deslogin", [
				':deslogin'=>$login
			]);
			
			return (count($results) > 0);
		}

		public function getOrder(){

			$sql = new Sql();

            $results = $sql->select("SELECT * FROM tb_orders a 
                INNER JOIN tb_ordersstatus b USING(idstatus)
                INNER JOIN tb_carts c USING(idcart)
                INNER JOIN tb_user d ON d.iduser = a.iduser
                INNER JOIN tb_addresses e USING(idaddress)
                INNER JOIN tb_persons f ON f.idperson = d.idperson
                WHERE a.iduser = :iduser
            ", [
                ':iduser'=>$this->getiduser()
			]);
			
			return $results;
		}
	}
	
?>