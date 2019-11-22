<?php
    namespace Hcode\Model;
    
    use \Hcode\DB;
	use \Hcode\Model;

    class OrderStatus Extends Model{

       const EM_ABERTO = 1;
       const AGUARDANDO_PAGAMENTO = 2;
       const PAGO= 3;
       const ENTREGE = 4;
    }
?>