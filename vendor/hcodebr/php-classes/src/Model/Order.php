<?php
    namespace Hcode\Model;
    
    use \Hcode\DB;
	use \Hcode\Model;

    class Order Extends Model{

        public function save(){

            $sql = new Sql();

            $results = $sql->select("CALL sp_order_save(:idorder, :idcart, :iduser, :idstatus, :idaddress, :vltotal)", [
                ':idorder'=>$this->getidorder(),
                ':idcart'=>$this->getidcart(),
                ':iduser'=>$this->getiduser(),
                ':idstatus'=>$this->getidstatus(),
                ':idaddress'=>$this->getidaddress(),
                ':vltotal'=>$this->getvltotal()
            ]);

            if(count($results) > 0){

                $this->setData($results[0]);
            }
        }

        public function get($idorder){

            $sql = new Sql();

            $results = $sql->select("SELECT * FROM tb_orders a 
                INNER JOIN tb_ordersstatus b USING(idstatus)
                INNER JOIN tb_carts c USING(idcart)
                INNER JOIN tb_user d ON d.iduser = a.iduser
                INNER JOIN tb_addresses e USING(idaddress)
                INNER JOIN tb_persons f ON f.idperson = d.idperson
                WHERE a.idoerder = :idorder
            ", [
                ':idorder'=>$idorder
            ]);

            if(count($results) > 0){

                $this->setData(results[0]);
            }
        }
    }
?>