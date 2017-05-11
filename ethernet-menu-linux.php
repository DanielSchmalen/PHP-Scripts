<?php
/*
author: Daniel Schmalen
class: CECS561 - Final Project
use: Implementation of a menu-based network script
*/

#	Define the global var to check when to exit the program
$exit = FALSE;

#	In history every command that have been executed on the shell will be logged
$history = array();

#	i is a continous variable to order the history log
$i=0;

echo "#################################### \n";
echo "CECS561 - Final Project - User Logic \n";
echo "#################################### \n";

#	As long as exit is not set TRUE, which can be done by choosing option 5, the program will run
while($exit === FALSE) {
	
	#	The input var contains the choosen option by the user in the following menu
	$input;
	
	#	The parameter var contains the user-specific choosen value to run the choosen command
	$parameter;
	
	#	The output var contains the shell output and will be echo'ed to the user
	$output;

	echo "(1) - Ping \n";
	echo "(2) - Telnet \n";
	echo "(3) - DNS Lookup \n";
	echo "(4) - Read Command History \n";
	echo "(5) - Exit \n";

	#	readline waits for input by the user and saves, only the input, to the given var
	$input = readline("Command: ");
	
	#	The readline module requires to put all readlines into a history
	readline_add_history($input);

	#	Depending on the input value the user put, it executes the correct command
	switch($input) {
		case 1:
			$parameter = readline("Destination IP: ");
			
			#	filter_var with the option FILTER_VALIDATE_IP checks the input on typical IP address properties
			if(!filter_var($parameter, FILTER_VALIDATE_IP) === FALSE) {
				$output = shell_exec('ping -c 5 ' . $parameter);
				$history[$i] = 'ping -c 5 ' . $parameter;
				echo $output;
			}
			else {
			#	If the filter_var check fails and the input is not an IP address, the program will output the following.
				$history[$i] = 'ping: Invalid IP address - ' . $parameter;
				echo "Invalid IP address! \n";
			}
			break;
		case 2:
			$parameter = readline("Destination IP and Port: ");
			
			#	explode will divide the entered IP address and Port into several values
			$parameter = explode(" ",$parameter);
			
			#	In addition to the filter_var check, the program also checks if the given port is a numeric value.
			if((!filter_var($parameter[0], FILTER_VALIDATE_IP) === FALSE) && (is_numeric($parameter[1]))) {
				$output = shell_exec('telnet ' . $parameter[0] . ' ' . $parameter[1]);
				$history[$i] = 'telnet ' . $parameter;
				echo $output;
			}
			else {
			#	If the filter_var check fails or the given port is not a numeric value, the program will output the following.
				$history[$i] = 'telnet: Invalid IP address or port - ' . $parameter[0] . ' ' . $parameter[1];
				echo "Invalid IP address or port! \n";
			}
			break;
		case 3:
			$parameter = readline("DNS Name: ");
			
			#	The user input has to be either a valid IP address or a valid DNS name. 
			#	is_valid_domain_name is a self-written function at the end of the program to check the properties.
			if((!filter_var($parameter, FILTER_VALIDATE_IP) === FALSE) || (is_valid_domain_name($parameter) == TRUE)) {
				$output = shell_exec('nslookup ' . $parameter);
				$history[$i] = 'nslookup ' . $parameter;
				echo $output;
			}
			else {
			#	If the filter_var check fails or the DNS name is not valid, the program will output the following.
				$history[$i] = 'nslookup: Invalid IP address or DNS name - ' . $parameter;
				echo "Invalid IP address or DNS name! \n";
			}
			break;
		case 4:
			foreach ( $history as $key=>$val ){
				echo "$key:$val\n";
			}
			$history[$i] = 'history';
			break;
		case 5:
			echo "Exit";
			$exit = TRUE;
			break;	
		default:
			echo "You have to enter a value between 1-5 \n";
	}
	echo "\n";
	
	#	Clearing the values of the following vars
	unset($input);
	unset($output);
	unset($parameter);
	
	#	Increase the counter for the history log
	$i++;
}

if($exit === TRUE) {
	echo "######################## \n";
	echo "Closing the application. \n";
	echo "######################## \n";
}	
else
	echo "Oops, something went wrong! \n";

function is_valid_domain_name($domain_name) {
    return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name) //valid chars check
            && preg_match("/^.{1,253}$/", $domain_name) 									 //overall length check
            && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name)   ); 			 //length of each label
}
?>