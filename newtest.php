<?php
include ('dbconfig.php');

function vendor_report($vendorpassvalue, $email)
{
	//Setup Email Subject
	$subject = 'Your Weekly FarmieMarket Sales Report';
	include ('dbconfig.php');
	$results_vendor_name = $con->query("SELECT * FROM wp_terms WHERE term_id = ".$vendorpassvalue."");
	while($row_vendor_name = $results_vendor_name ->fetch_object()) {
	$results = $con->query("SELECT * FROM wp_posts WHERE post_date > DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND post_type = 'shop_order' AND post_status = 'wc-completed'");

	$report = '';
	$report .= '<p>'.$header.'</p>';	
	$report .= '<table border="1">';
	$report .= '<tr>';
	$report .= '<td colspan="5">7 Days Report</td>';
	$report .= '</tr>';
	//print_r($row_vendors);
	$row_vendors_term_id = $vendorpassvalue;
	$results_vendor_name = $con->query("SELECT * FROM wp_terms WHERE term_id = ".$row_vendors_term_id."");
	while($row_vendor_name = $results_vendor_name ->fetch_object()) {
	//print_r($row_vendor_name);
	$report .= '<tr>';
	$report .= '<td colspan="3">Vendors Name</td>';
	$report .= '<td colspan="2">'.$row_vendor_name->name.'</td></tr>';
	$report .= '<tr>';
	$report .= '<td>Product Name</td>';
	$report .= '<td>Product SKU</td>';
	$report .= '<td>Product Quantity</td>';
	$report .= '<td>Product Cost Price</td>';
	$report .= '<td>Product Sold Price</td>';
	$report .= '</tr>';

	while($row = $results->fetch_object()) {
	$orderid = $row->ID; 
	$result_oii = $con->query("SELECT * FROM wp_woocommerce_order_items WHERE order_item_type = 'line_item' AND order_id = ".$orderid."");
		while($row_oii  = $result_oii ->fetch_object()) {

			$result_product_id = $con->query("SELECT * FROM wp_woocommerce_order_itemmeta WHERE meta_key = '_product_id' AND order_item_id = ".$row_oii->order_item_id."");
				while($row_product_id = $result_product_id ->fetch_object()) {
					
					$result_product_term = $con->query("SELECT * FROM wp_term_relationships WHERE object_id = ".$row_product_id->meta_value."");
					while($row_product_term = $result_product_term ->fetch_object()) {
						//print_r($row_product_term);
						
							
						if($row_vendors_term_id == $row_product_term->term_taxonomy_id)
						{

							$pname = $con->query("SELECT * FROM wp_posts WHERE ID = ".$row_product_id->meta_value."");
							while($row_pname = $pname ->fetch_object()) { 
								$pname_value = $row_pname->post_title;
							}

							$sku = $con->query("SELECT * FROM wp_postmeta WHERE post_id = ".$row_product_id->meta_value." AND meta_key = '_sku'");
							while($row_sku = $sku ->fetch_object()) { 
								$sku_value = $row_sku->meta_value;
							}

							$regular_price = $con->query("SELECT * FROM wp_postmeta WHERE post_id = ".$row_product_id->meta_value." AND meta_key = '_regular_price'");
							while($row_regular_price = $regular_price ->fetch_object()) { 
								$regular_value= $row_regular_price->meta_value;
							}

							$wc_cog_cost = $con->query("SELECT * FROM wp_postmeta WHERE post_id = ".$row_product_id->meta_value." AND meta_key = '_wc_cog_cost'");
							while($row_wc_cog_cost = $wc_cog_cost ->fetch_object()) { 
								$wc_value = $row_wc_cog_cost->meta_value;
							}

							$result_product_qnt = $con->query("SELECT * FROM wp_woocommerce_order_itemmeta WHERE meta_key = '_qty' AND order_item_id = ".$row_oii->order_item_id."");
							while($row_product_qnt = $result_product_qnt ->fetch_object()) {
								$result_product_qnt_val = $row_product_qnt->meta_value;
							}

							$report .= '<tr>';
							$report .= '<td>'.$pname_value.'</td>';
							$report .= '<td>'.$sku_value.'</td>';
							$report .= '<td>'.$result_product_qnt_val.'</td>';
							$report .= '<td>'.$wc_value.'</td>';
							$report .= '<td>'.$regular_value.'</td>';
							
						}
						
					}
				}
			}
		}
		$report .= '</tr>';
		$report .= '</tr>';
	}
	$report .= '</table>';
	$report .= '<p>'.$footer.'</p>';

	html_email($email,$subject,$report);

	$results = $row_vendor_name->name;
	return $results;
	}
	
}

//Add a message to the header of the report
$header = '<p>Hello!  Below is a report of your weekly sales on FarmieMarket.com this week.</p>';

//Add a message to the footer of the report, you can use plain text or html.
$footer = 'Please meet me at my house, insert address here, at 7:45am Thursday to drop off your weekly sales.  Thanks a bunch!!  -Sarah';

//emails array
$details = array
(
array(                                                      
		// ID must be added here for Merchant Gordon Farms
		'name' => '111',       
		// Email must be added here     
        'email' => 'GordonFarm@APC2GO.com'
    ),

    // present vendor id are 111, 115 to 123
    //add more by this way, get the ID from wp-admin of Vendor and set the email id here same as old script. Every new vendor only requires adding the same call as above but with the new ID and email address for each additional Vender/Merchant
    
    //array(
    //    'name' => '123',
     //   'email' => 'NewVender123@somewhere.com'
    //),

);

function searchForId($id, $array) {
   foreach ($array as $key => $val) {
       if ($val['name'] === $id) {
           return $val['email'];
       }
   }
   return null;
}

$results_vendors_all = $con->query("SELECT * FROM wp_term_taxonomy WHERE taxonomy = 'shop_vendor'");
 	$results = array();
	  	while($row_vendors_all = $results_vendors_all->fetch_object())
	  		{
	  			$results_vendor_name = $con->query("SELECT * FROM wp_terms WHERE term_id = ".$row_vendors_all->term_id."");
					while($row_vendor_name = $results_vendor_name ->fetch_object()) {
						//$newvendornames[] = $row_vendor_name->name;
	  					//$newtermids[] = $row_vendors_all->term_id;
	  					$results[] = array(
						    'name' => $row_vendor_name->name,
						    'term' => $row_vendors_all->term_id,
						    'email' => $id = searchForId($row_vendors_all->term_id, $details),
						    //Change email by adding 'name@domain.com', -- Keep the , at end
						  );

	  				}
	    	}

		    foreach($results as $name) { 
		    	vendor_report($name['term'], $name['email']);
			} 



			//	Generate HTML Email
function html_email($to,$subject,$msg) {
	
	if (!TEST_MODE) {
		$eol = "\r\n";
		$fromaddress = FROM_EMAIL;
		$now = time();
		$num1 = rand(0,10000);
		$num2 = rand(0,10000);
	
		$headers = "";
		$headers .= 'MIME-Version: 1.0'.$eol;
		$headers .= 'Content-type: text/html; charset="'.MY_CHARSET.'"'.$eol;
		$headers .= 'Content-Transfer-Encoding: base64'.$eol;	
		$headers .= 'From: '.FROM_NAME.'<'.$fromaddress.'>'.$eol;
		$headers .= 'Reply-To: '.FROM_NAME.'<'.$fromaddress.'>'.$eol;
		$headers .= 'Return-Path: '.FROM_NAME.'<'.$fromaddress.'>'.$eol;    // these two to set reply address
		$headers .= "Message-ID: <".$num1.$now.$num2." TheSystem@".$_SERVER['SERVER_NAME'].">".$eol;
		$headers .= "X-Mailer: PHP v".phpversion().$eol;          // These two to help avoid spam-filters	
	
		$base64contents = rtrim(chunk_split(base64_encode($msg)));
		$success = mail($to, $subject, $base64contents, $headers);	
		if($success == false) {
			echo 'The mail to '.$to.' failed to send.<br />';
		} else {
			echo 'The mail was sent successfully to '.$to.'.<br />';
		}
	} else {
		echo '<b>Report running in test mode.</b><br />Disable test mode in the config.php when you are ready for the report to go live.<br /><br />';	
		echo '<br />'.$msg.'<br />';
	}
}	
?>