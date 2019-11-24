<?PHP

    use \Hcode\PageAdmin;
    use \Hcode\Mode\User;
    use \Hcode\Mode\Order;
    use \Hcode\Mode\OrderStatus;

    $app->get("/admin/orders/:idorder/status", function($idorder){

        User::verifyLogin();

        $order = new Oreder();

        $order->get((int)$idorder);

        $page = new PageAdmin();

        $page->setTpl("order-satus", [
            'order'=>$order->getValues(),
            'status'=>OrderStatus::listAll(),
            'msgSucess'=>Order::getSucess(),
            'msgError'=>Order::getError()
        ]);
    });

    $app->post('admin/orders/:idorder/status', function($idorder){

        User::verifyLogin();

        if(isset($_POST['idstatus']) || !(int)$_POST['idstatus'] > 0){
            Order::setError("Informe o status atual.");
            header("Location: /admin/orders/".$idstatus."/status");
            exit;
        }

        $order = new Oreder();

        $order->get((int)$idorder);

        $order->setidstatus((int)$_POST['idstatus']);

        $order->save();

        Order::setSucess("Status atualizado.");

        header("Location: /admin/orders/".$idstatus."/status");
        exit;
    });

    $app->get("/admin/orders/:idorder/delete", function($idorder){

        User::verifyLogin();

        $order = new Oreder();

        $order->get((int)$idorder);

        $cart = $order->getCart();

        $order->delete();

        header("Location: /admin/orders");
        exit;
    });

    $app->get("/admin/orders/:idorder", function($idorder){

        User::verifyLogin();

        $order = new Order();

        $order->get((int)$idorder);

        $page = new PageAdmin();

        $page->setTpl("order", [
            'order'=>$order->getValues(),
            'cart'=>$cart->getValues(),
            'products'=>$cart->getProducts()
        ]);
    });

    $app->get("/admin/orders", function(){

        User::verifyLogin();

        $page = new PageAdmin();

        $page->setTpl("orders", [
            "orders"=>Order::listAll()
        ]);
    });

?>