<?php
	require_once 'class.mellat.php';
    require_once 'config.php';

    $melat  = new Mellat($terminal_id, $username, $password, $callbackURL);
    $client = $melat->setConnection();
    $refId  = $melat->bpPayRequest($Orderid, $debit, 'پرداخت وجه', $userId);

    $ref_code = $refId[0];
    $ref_id   = $refId[1];

    $sql = "Update accounts Set ref_id = '$ref_id', ref_id_status='$ref_code' Where id = $rid";
    updateQuery($sql);

    if($refId[0] == 0){
        $redirect = $melat->redirectToBank($ref_id);
        echo $twig->render('credit-add.twig', 
                            array('session' => $_SESSION, 
                                    'type' => 2,
                                    'redirect' => $redirect,
                                    )
                            );
        return;
    }else{
        echo $twig->render('credit-add.twig', 
                            array('session' => $_SESSION, 
                                    'type' => -1,
                                    'error' => $melat->getErrors($ref_code),
                                    )
                            );
        return;
    }
