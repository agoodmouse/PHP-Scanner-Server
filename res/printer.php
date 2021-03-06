<?php
# This file is used to handle all the print commands
# This file runs all the print commands, ../download.php and inc/printer.php call this page via include
# If your printer needs some special options used you want to edit lines 15 and 27, also note lines 12 and 24
# You will want to read up on the CUPS command line printing documentation @ http://www.cups.org/documentation.php/options.html

$lpstat='lpstat -a | awk \'{print $1}\'';// command used to find printers
if(function_exists('exe')){// internal call via inc/printer.php
	if(isset($file)){
		$_POST['quantity']=intval($_POST['quantity']);
		$q=$_POST['quantity']>0?$_POST['quantity']:1;
		$o=escapeshellarg($_POST['options']);
		Print_Message(
			$_POST['printer'],
			'Your document is being processed:<br/><pre>'.html(
				exe('lp -d '.shell($_POST['printer'])." -n $q -o $o $file",true) // Print via Printer page
			).'</pre>',
			'center'
		);
	}
	else
		$printers=array_filter(explode("\n",exe($lpstat,true)));
}
else if(isset($Printer)){ // internal call via include from ../download.php
	header('Content-type: application/json; charset=UTF-8');
	$_GET['quantity']=intval($_GET['quantity']);
	$q=$_GET['quantity']>0?$_GET['quantity']:1;
	$o=escapeshellarg($_GET['options']);
	echo json_encode((object)array(
		'printer'=>$_GET['printer'],
		'message'=>shell_exec('lp -d '.escapeshellarg($_GET['printer'])." -n $q -o $o $file") // This line makes it print using the integrated printer
	));
}
else{
	$Printer=parse_ini_file(file_exists('res')?'config.ini':'../config.ini');
	$Printer=(integer)$Printer['Printer'];
	if(!function_exists('ext2mime')){// external call via browser
		if($Printer==0){// Check if printer service  is enabled
			header('Content-type: application/json; charset=UTF-8');
			echo '{"error":"Printer service is disabled"}';
		}
		else if(isset($_GET['list'])){// Return list of printers
			header('Content-type: plain/txt; charset=UTF-8');
			echo str_replace("\n",",",substr(shell_exec($lpstat),0,-1));
		}
		else
			echo "Todo: Don't reload printer page, use AJAX";
	}
}
?>
