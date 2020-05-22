<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$id = $_GET['id'];

//$id="3696069000004364011";

	// if(isset($_GET["rec_id"]) && !empty($_GET["rec_id"]))
	// {
		$url = "https://creator.zoho.com/api/json/order-management2/view/PHP_Design_BOM_Report?zc_ownername=aorborctechnologiescpt&authtoken=a92351abb9b379565f324987eec90c84&scope=creatorapi&raw=true&criteria=(ID==".$id.")"; 
        // print_r($url);
    // }
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		$output = curl_exec($ch);
		//print_r($output);
		curl_close($ch);
                                        

        $zc_resp = json_decode($output)->PHP_Design_BOM[0];
        //echo $zc_resp;
        $fileName = $zc_resp->File_upload;
		// $file_name =$zc_resp->Name;
		// // echo $file_name;
		$split_url = substr($fileName , strrpos($fileName , '/') + 1);
		//print_r($split_url);
		$split_final_value = explode("&", $split_url, 2)[0];;
		// print_r($split_final_value);
		$file_url = 'https://creator.zohopublic.com/file/aorborctechnologiescpt/order-management2/PHP_Design_BOM_Report/'.$id.'/File_upload/download/rp4z8qsHWm2xSyVJvakRdh0EUNf0JmNnvbd5VUpYm61e496CMXJVJJjXBWAdxRzH039Mh1h4byGnAO46XGKy5dv38ftx1aa7GnQF?filepath=/'.$split_final_value.'';
		$import_url = 'https://creator.zoho.com/api/aorborctechnologiescpt/json/order-management2/form/PHP_Design_BOM_SF/record/add';
		$authtoken = "a92351abb9b379565f324987eec90c84";
		// echo $file_name . ' : ' . $file_url ;
	    import_file_from_zc($file_url,$split_final_value,$import_url,$authtoken,$id);

    function import_file_from_zc($file_url,$file_name,$import_url,$authtoken,$id){
		$destination = "csvs/" . $file_name;
		$file = fopen($destination, "a+");
		set_time_limit(0); // unlimited max execution time
		$ch = curl_init();
		$options = array(
			CURLOPT_FILE    => $file,
			CURLOPT_TIMEOUT =>  28800, // set this to 8 hours so we dont timeout on big files
			CURLOPT_URL     => $file_url,
		);
		curl_setopt_array($ch, $options);
		$data = curl_exec ($ch);
		curl_close ($ch);
		fclose($file);
		parse_csv($file_url,$file_name,$destination,$import_url,$authtoken,$id);
		unlink($destination);
	}
	function parse_csv($file_url,$file_name,$destination,$import_url,$authtoken,$id){
		$first_line = true;	
		//$creator_log = array();
		if (($handle = fopen($destination, 'r')) !== FALSE) {
			while (($line = fgetcsv($handle)) !== FALSE) {
			  	//$line is an array of the csv elements
				if($first_line == true){
				  $first_line = false;
				}else{
					//print_r($line);
					//log_php($file_name, implode($line, " ") );
					insert_record($line,$authtoken,$import_url,$file_name,$id);
					//array_push($creator_log, $zc_resp);
				}
			}
		}
		//var_dump($creator_log);
		fclose($handle);
	}
	function insert_record($line,$authtoken,$import_url,$file_name,$id){
		$post_params = array();
		$post_params['authtoken'] = $authtoken;
		$post_params['scope'] = 'creatorapi';
		$post_params['Sub_Order_Name'] = $line[0];
		$post_params['Material_Grade'] = $line[1];
		$post_params['Materials_Name1'] = $line[2];
		$post_params['Materiel_Specification'] = $line[3];
		$post_params['Weight'] = $line[4];
		$post_params['Length_field'] = $line[5];
		$post_params['Qty'] = $line[6];
		$post_params['Total_Length'] = $line[7];
		$post_params['Scrap_mm'] = $line[8];
		$post_params['Total_Weight'] = $line[9];
		$post_params['Design_BOM'] = $id;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $import_url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
		$result = curl_exec($ch);
		curl_close($ch);
		$result = json_decode($result) ;
		var_dump($result);
	}
    
?>
