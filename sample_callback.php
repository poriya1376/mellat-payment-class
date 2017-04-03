<?php
	require_once 'class.mellat.php';
	require_once 'config.php';

	$RefId           = $_POST['RefId'];
	$ResCode         = $_POST['ResCode'];
	$saleOrderId     = $_POST['SaleOrderId'];
	$SaleReferenceId = $_POST['SaleReferenceId'];
	$CardHolderInfo  = $_POST['CardHolderInfo'];
	$CardHolderPan   = $_POST['CardHolderPan'];
	$errMessage  = '';

	$sql = "update accounts Set CardHolderInfo='{$CardHolderInfo}', CardHolderPan='{$CardHolderPan}', res_code = '{$ResCode}', sale_reference_id='{$SaleReferenceId}' Where ref_id = '{$RefId}' and id = {$saleOrderId}";
	updateQuery($sql);
	// echo mysql_error();
	// echo '<hr>B<br>';

	$melat   = new Mellat($terminal_id, $username, $password);
	$client  = $melat->setConnection();

	$verify_res = -1;
	$settle_res = -1;

	if($ResCode == 0){
		$resCode = $melat->bpVerifyRequest($saleOrderId, $SaleReferenceId);
		$verify_res = $resCode[0];
		// var_dump($resCode);

		$sql = "update accounts Set verify_res = '$verify_res' Where ref_id = '$RefId' and id = $saleOrderId";
		updateQuery($sql);
		// echo mysql_error();
		// echo '<hr>C<br>';

		if($verify_res == 0){
			// echo '<hr>D<br>';
			$resCode = $melat->bpVerifyRequest($saleOrderId, $SaleReferenceId, 'settle');
			$settle_res = $resCode[0];
			// echo $settle_res;
			// echo '<hr>/D<br>';

			// echo '<hr>Settle<br>';
			$sql = "update accounts Set checked = 1, settle_ress = '$settle_res' Where ref_id = '$RefId' and id = $saleOrderId";
			updateQuery($sql);
			// echo mysql_error();

			if($settle_res == 45 || $settle_res == 0){
				// echo '<hr>E<br>';
				$sql = "insert into accounts (user_id, order_id, type_id, debit, credit, ref_id, ip, browser, checked)
					           Select 	user_id, 
					           			id,
					           			2,
					           			0 debit,
					           			price credit,
					           			'{$RefId}' ref_id,
					           			'{$ip}' ip,
					           			'{$browser}' browser,
					           			1 checked
					             From `order`
					                Where user_id in (Select user_id from accounts where ref_id = '{$RefId}' and id = {$saleOrderId})
					                	  and confirm_dt is not null
					                	  and status_id	 is null";

				updateQuery($sql);
				// echo mysql_error();
				// echo '<hr>F<br>';

				$sql = "Update `order` set status_id = 1 where confirm_dt is not null and status_id is null Where ...";
				updateQuery($sql);
				// echo mysql_error();
				// echo '<hr>G<br>';

				$_SESSION['flash'] = 'پرداخت با موفقیت انجام پذیرفت<br><strong>شناسه پیگیری:</strong>'.$RefId;
				header('location: index.php');

				// echo '<hr>/Settle<br>';
				return;
			}
		}
	}else{
		$errMessage .= $melat->getErrors($ResCode) . '';
	}

	$errMessage .= $melat->getErrors($verify_res) . '';
	$errMessage .= $melat->getErrors($settle_res) . '';

	$_SESSION['flash'] = $errMessage;
	echo $twig->render('credit-add.twig', 
						array('session' => $_SESSION, 
							  'type' => -1,
							  'error' => $errMessage,
							  )
					  );