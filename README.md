# Mellat Payment Class
-----
Open `config.php` file and edit below variables:

	<?php
		$terminal_id = 1234567;
		$username    = 'bankUserName';
		$password    = 'bankPassword';
		$callbackURL = 'http://your-domain/callback.php';
  	?>

Pay Method:

	require_once 'class.mellat.php';
    require_once 'config.php';

	$melat  = new Mellat($terminal_id, $username, $password, $callbackURL);
    $client = $melat->setConnection();
    $refId  = $melat->bpPayRequest($Orderid, $price, 'پرداخت وجه', $userId);

    $ref_code = $refId[0];
    $ref_id   = $refId[1];

    //Update your database table column: $ref_id and $ref_code Where $order_id
    
    if($refId[0] == 0){
    	$redirect = $melat->redirectToBank($ref_id);
        //echo $redirect for redirecting to mellat page
    }else{
    	//error
    }
