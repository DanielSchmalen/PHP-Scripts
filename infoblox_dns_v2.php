#!/usr/bin/php
<?php
	/*
		Autor:	 Daniel Schmalen
		Date:	 19.09.2015
		Version: 2.0
		Usage:	 Automatized DNS entries for Cisco devices
		include: cisco_snmp_interfaces.php
				 infoblox_wapi.php
		Comment: Those files have to be in the same folder (or you need to change the paths below)
					- cisco_snmp_interfaces.php
					- infoblox_wapi.php
	*/
require('cisco_snmp_interfaces.php');
require('infoblox_wapi.php');

$ib_api_url = "https://your.domain/wapi/v1.7.1";
$ib_user = "username";
$ib_password = 'password';
$ib_wapi = new Infoblox_wapi($ib_api_url, $ib_user, $ib_password);

getInterfaces("SNMP_COMMUNITY","Source-IP-Address-File.txt","Output-File.txt");

$hosts = file ('Output-File.txt');
foreach ($hosts as $line) {
	# $line looks like this: '10.102.0.1 > gi-0-0.ro01010140'
	# $interface is supposed to contain everything behind '>' 
	# $ipv4 contains everything prior to '>'
	$interface = substr($line, strpos($line, ' > ') + 3);
	$interface = trim($interface);
	$interface = strtolower($interface);
	$ipv4 = preg_replace("#>.*#","",$line);
	$ipv4 = trim($ipv4);
	$reg = "/lo-/";
	
	# Usually you want to register your device with the loopback address using the FQDN, but without an 'lo-1.' in front.
	# Therefore, the 'lo-1.' for example will be removed from the FQDN to get a proper DNS entry such as 'devicename.your.domain'
	if(preg_match($reg, $interface)) {
		$loopback = LoopbackAlias($interface);
		continue;
	}
	
	# At this point the script query the Infoblox for the Host reference of the given IP address
	$query = $ib_wapi->get_host_ref_ipv4($ipv4);
	if(sizeof($query) != 0) {
		$ib_ipv4 = $query[0]->ipv4addr;
		$ib_name = $query[0]->name;
	}
	
	$name_match = strpos($ib_name, $interface);
	
	# Case 1: IP and Name are the same. No action required.
	if(($ib_ipv4 === $ipv4) && $name_match !== FALSE) {
		echo "None: No changes have been made!\n";
	}
	# Case 2: The IP was found in the Infoblox, but the name retrieved from there differs to the name in the provided interface list.	
	elseif(($ib_ipv4 === $ipv4) && $name_match === FALSE) {	
		$ib_wapi->edit_host($ipv4,$interface);
		echo "Edit: Name has been changed!\n";
	}
	# Case 3: The IP is not yet registered in the Infoblox.
	elseif(($ib_ipv4 !== $ipv4)) {
		$comment = "Added by automatized Interface Script, " . date('d.m.y');
		$ib_wapi->create_host($interface,$ipv4,$comment);
		echo "Add: $interface $ipv4 \n";
	}		
	else {
		echo "$ipv4 -> $interface \n";
		echo "Error: No case matched for this IP and interface.\n";
	}	
	unset ($ib_ipv4);
	unset ($ib_name);
	unset ($ipv4);
	unset ($interface);
	unset ($name_match);
}

# This function removes the 'lo-0' (or whatever number it is) from the FQDN for the loopback interfaces.
function LoopbackAlias($interface) {
	$reg2 = "/lo-0/";
	if(preg_match($reg2, $interface))
		$loopback = str_replace("lo-0.","", $interface);
	
	$reg2 = "/lo-1/";
	if(preg_match($reg2, $interface))
		$loopback = str_replace("lo-1.","", $interface);
		
	$reg2 = "/lo-5/";
	if(preg_match($reg2, $interface))
		$loopback = str_replace("lo-5.","", $interface);
		
	$reg2 = "/lo-10/";
	if(preg_match($reg2, $interface))
		$loopback = str_replace("lo-10.","", $interface);
		
	$reg2 = "/lo-64512/";
	if(preg_match($reg2, $interface))
		$loopback = str_replace("lo-10.","", $interface);

	return $loopback;
}	
?>