<?php
	/*
		author:	 		Daniel Schmalen
		date:	 		04.12.2013 // Revised 13.04.2017
		version: 		1.1
		usage:	 		This script is querying Cisco devices with SNMP in order to retrieve the interfaces and the respective IP Address.
						Originally this script was used to generate DNS entries automatically. Therefore, the script is being called by the function getInterfaces();
		requirements:	A .txt file with IP addresses for the Cisco devices to check (single IP address per line)
		Comment:		The file is not optimized as you can easily see by the implementation at the end of the file. I might release a version 1.2 with a better file handling and reducing the operations.
	*/

function getInterfaces($community,$router_filename,$output_filename) {
	#OID's for SNMP Walk
	$OID_InterfaceIndex =  '1.3.6.1.2.1.2.2.1.1';	
	$OID_InterfaceDescription =  '1.3.6.1.2.1.2.2.1.2';	
	$OID_IPAdressIndex =  '1.3.6.1.2.1.4.20.1.2';	
	$OID_IPAdress =  '1.3.6.1.2.1.4.20.1.1';
	$OID_HostName =  '1.3.6.1.4.1.9.2.1.3';

	#List of Loopback addresses of the Cisco routers and the SNMP Community Name
	$hosts = file ($router_filename);
	$SNMP_COMMUNITY = $community;

	#Variable used for the output
	$i=0;

	foreach ($hosts as $HOST) {

		#SNMP Walks for the given OIDs at one single host per time
		$InterfaceIndex =  snmp2_real_walk($HOST, $SNMP_COMMUNITY, $OID_InterfaceIndex);
		$InterfaceDescription =  snmp2_real_walk($HOST, $SNMP_COMMUNITY, $OID_InterfaceDescription);	
		$IPAdressIndex =  snmp2_real_walk($HOST, $SNMP_COMMUNITY, $OID_IPAdressIndex);
		$IPAdress =  snmp2_real_walk($HOST, $SNMP_COMMUNITY, $OID_IPAdress);
		$HostName =  snmp2_real_walk($HOST, $SNMP_COMMUNITY, $OID_HostName);

		#The output of the SNMP Walk contains 
		$InterfaceIndex = str_replace("INTEGER: ", "", $InterfaceIndex);
		$InterfaceDescription = str_replace("STRING: ", "", $InterfaceDescription);
		$IPAdressIndex = str_replace("INTEGER: ", "", $IPAdressIndex);
		$IPAdress = str_replace("IpAddress: ", "", $IPAdress);
		$HostName = str_replace("STRING: ","", $HostName);
		$HostName = str_replace("\"","", $HostName);

		#Connects the Interface Index with the Interface Name
		$interfaceTemp = array_combine($InterfaceIndex, $InterfaceDescription);
		
		#Connects the IP address Index with the actual IP address
		$ipadressTemp = array_combine($IPAdressIndex, $IPAdress);
		
		#The arrays are being resorted based on the key (ascending)
		ksort($interfaceTemp);
		ksort($ipadressTemp);
		
		#Checks both array on their key elements. Every not matching object will be removed.
		$interface = array_intersect_key($interfaceTemp, $ipadressTemp);
		$ipadress = array_intersect_key($ipadressTemp, $interfaceTemp);
		
		#Resets the keys of the arrays, starting with 0 ascending
		$interface = array_merge($interface);
		$ipadress = array_merge($ipadress);
		
		#Changes the string index to a numerical index
		$HostName = array_values($HostName);

		#Pattern for renaming the interface names to their abbreviations
		$pattern[0] = '/GigabitEthernet/';
		$pattern[1] = '/FastEthernet/';
		$pattern[2] = '/Loopback/';
		$pattern[3] = '/Vlan/';
		$pattern[4] = '/Dialer/';

		#The corresponding replacement of the patterns
		$replace[0] = 'gi-';
		$replace[1] = 'fa-';
		$replace[2] = 'lo-';
		$replace[3] = 'vl-';
		$replace[4] = 'di';

		#Replaces every found pattern with the corresponding replacement
		$interface = preg_replace($pattern, $replace, $interface);

		#Changes the characters to DNS conform characters
		$interface = str_replace("/","-",$interface);
		$interface = str_replace(".","-",$interface);
		
		$j = 0;
		
		#Appends the Hostname, Domain and IP Address to the Interface String
		foreach($interface as $int)
		{
			$interface[$j] = $ipadress[$j] . ' > ' . $int . "." . $HostName[0] . "\n";
			$j++;
		}
		
		#The output variable will be pushed into the file
		$output[$i] = $interface;
		$i++;
		
		#Saves the output variable to the given file
		if($output != false)
		{
			$file = fopen($output_filename, 'w+');
			foreach($output as $values) {
				foreach($values as $val) fputs($file, $val);
			}	
			fclose($file);
		}	
	}

	#Removes entries from Dialer interfaces and other unwanted unterfaces
	$dat_ar = file($output_filename);
	foreach ($dat_ar as $key => $value) {
		$reg = "/di/";
		if(preg_match($reg, $value))
			unset($dat_ar[$key]);
			
		$reg = "/EOBC0/";
		if(preg_match($reg, $value))
			unset($dat_ar[$key]);
	}

	$file = fopen($output_filename, 'w+');
	foreach($dat_ar as $values) {
		fputs($file, $values);
		}	
	fclose($file);
}
?>