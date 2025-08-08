<?php 
/* Template Name: IPN Listener */
//wp_mail('gauden@essetra.com', 'IPN elemental connection','Reaching the file');


$req = 'cmd=_notify-validate';
foreach($_POST as $key => $value) :
  $value = urlencode(stripslashes($value));
  $req .= "&$key=$value";
endforeach;

$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Host: www.sandbox.paypal.com\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

$fp = fsockopen ('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);
 
if(!$fp) :
// HTTP ERROR  
	//wp_mail('gauden@essetra.com', 'HTTP ERROR','HTTP ERROR');

else :
	//wp_mail('gauden@essetra.com', 'NOT HTTP ERROR','Should send this');
    fputs ($fp, $header . $req);
    while(!feof($fp)) :

        $res = fgets ($fp, 1024);
         
        $fh = fopen('result.txt', 'w');
		fwrite($fh, $res);
		fclose($fh);
         
        if (strcmp ($res, "VERIFIED") == 0) :
         
            // Make sure we have access to WP functions namely WPDB
            //include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
             
            // You should validate against these values.
			$payment_status = $_POST['payment_status']; 
            $firstName      = $_POST['first_name'];
            $lastName       = $_POST['last_name'];
            $payerEmail     = $_POST['payer_email'];
            $addressStreet  = $_POST['address_street'];
            $addressZip     = $_POST['address_zip'];
            $addressCity    = $_POST['address_city'];
            $txnID          = $_POST['txn_id'];
            $br_userID      = $_POST['custom'];
			$item_name      = $_POST['item_name'];
			$item_number      = $_POST['item_number'];
			$payment_date   = $_POST['payment_date'];
			$payer_amount   = $_POST['mc_gross']; 
			if(!$br_userID){
				$br_userID = 16;
			}
			$payment_content = '';
			foreach($_POST as $key => $value) : 
			  $payment_content .= '<p><strong>Key: </strong>'.$key.'<strong>Value: </strong>'.$value.'</p>';
			endforeach;

			
			if($payment_status == 'Completed'){
			$the_nonce = wp_create_nonce('br_new_transaction_nonce');
			$new_post = array(
				'post_title'    =>   'Transaction '.$payment_status.': '.$txnID,
				'post_status'   =>   'pending', 
				'post_content' => $payment_content,
				'post_author'=> $br_userID,
				'post_type' =>   'payment'
			);
			if (wp_verify_nonce($the_nonce, 'br_new_transaction_nonce')) {
				$pid = wp_insert_post($new_post);
			}
			
			update_post_meta($pid, 'the_payer_first_name', $firstName); 
			update_post_meta($pid, 'the_payer_last_name', $lastName); 
			update_post_meta($pid, 'the_payer_email', $payerEmail); 
			update_post_meta($pid, 'the_payer_item_name', $item_name);
			update_post_meta($pid, 'the_payer_item_number', $item_number);
			update_post_meta($pid, 'the_payer_txn_id', $txnID);
			update_post_meta($pid, 'the_payer_txn_amount', $payer_amount);
			update_post_meta($pid, 'the_payer_payment_status', $payment_status);
			update_post_meta($pid, 'the_payer_payment_date', $payment_date);
				if($br_userID != 1){
					$args = array( 'ID' => $br_userID, 'role' => 'br_big_rabbit');
					wp_update_user( $args );
					update_user_meta($br_userID, 'show_subscribe_button', 'false');
				};
			}else{					
				if($br_userID != 1){
					$args = array( 'ID' => $br_userID, 'role' => 'br_rabbit');
					wp_update_user( $args );
					update_user_meta($br_userID, 'show_subscribe_button', 'true');
				};
			};
			
			$receipt_message ='<table width="90%" border="0" cellspacing="0" cellpadding="5">
				<tbody>
					<tr>
						<td><center><img src="http://app.bluerabbit.io/email-images/thankyou.png" alt=""/></center></td>
					</tr>
					<tr>
						<td><center><h4>Thanks for choosing BLUErabbit! Below you will find your receipt. Any comments please let us know at support@bluerabbit.io</h4></center></td>
					</tr>
				</tbody>
			</table>
			<table width="90%">
				<tbody>
					<tr>
						<td>Name</td>
						<td>'.$_POST['first_name'].' '.$_POST['last_name'].'</td>
					</tr>
					<tr>
						<td>Email</td>
						<td>'.$_POST['payer_email'].'</td>
					</tr>
					<tr>
						<td>Subscription Date</td>
						<td>'.$_POST['subscr_date'].'</td>
					</tr>
					<tr>
						<td>Subscription Type</td>
						<td>'.$_POST['item_name'].' '.$_POST['item_number'].'</td>
					</tr>
					<tr>
						<td>Amount Payed</td>
						<td>$'.$_POST['mc_gross'].$_POST['mc_currency'].'</td>
					</tr>
					<tr>
						<td>Transaction ID</td>
						<td>'.$_POST['txn_id'].'</td>
					</tr>
				</tbody>
			</table>
			<center>
				<h3>Please contact us for any questions or comments</h3>
			</center>';
			
			
			wp_mail($payerEmail, 'BLUErabbit Receipt',$receipt_message);
			wp_mail('receipts@bluerabbit.io', 'BLUErabbit Receipt '.$_POST['txn_id'],$receipt_message);
        elseif(strcmp ($res, "INVALID") == 0) :

			// wp_mail('gauden@essetra.com', 'INVALID RESPONSE FROM THE SERVER',$res);
            // You may prefer to store the transaction even if failed for further investigation.
        else :
			//wp_mail('gauden@essetra.com', 'NOT Verified, nor Invalid',$res);
            //You may prefer to store the transaction even if failed for further investigation.
        endif;
         
    endwhile;
fclose ($fp);
endif;
