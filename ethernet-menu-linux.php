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
	echo "(2) - Traceroute \n";
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
			$output = shell_exec('ping -c 5 ' . $parameter);
			$history[$i] = 'ping -c 5 ' . $parameter;
			echo $output;
			break;
		case 2:
			$parameter = readline("Destination IP: ");
			$output = shell_exec('traceroute ' . $parameter);
			$history[$i] = 'traceroute ' . $parameter;
			echo $output;
			break;
		case 3:
			$parameter = readline("DNS Name: ");
			$output = shell_exec('nslookup ' . $parameter);
			$history[$i] = 'nslookup ' . $parameter;
			echo $output;
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
?>