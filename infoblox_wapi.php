<?php

/*
* author: Daniel Schmalen
* date: 13.04.2017
* description: PHP API middleware utilizing the Infoblox WAPI by using the CURL functionality. 
*/

	class Infoblox_wapi {

		private $base_url;
		private $username;
		private $password;
		private $curl;
		public $curl_response;
		public $curl_info_http_code;
		
		function __construct($base_url, $username, $password) {
			$this->base_url = $base_url;
			$this->username = $username;
			$this->password = $password;
		}	

		private function connect() {
			$this->curl = curl_init();
			curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($this->curl, CURLOPT_HEADER, false);
			curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($this->curl, CURLOPT_USERPWD, $this->username . ':' . $this->password);
		}
		
		private function disconnect() {
			curl_close($this->curl);
		}
			
		#####################################################################################################
		#																									#
		#									General CURL Methods											#
		#																									#
		#####################################################################################################
		
		private function curl_get($query,$query_params,$params = "") {
			curl_setopt($this->curl, CURLOPT_URL, $this->base_url . $query . $query_params . $params);	
			$return = json_decode(curl_exec($this->curl));
			$this->set_curl_response(curl_getinfo($this->curl, CURLINFO_HTTP_CODE));
			return $return;
		}
		
		private function curl_post($query,$postfields) {
			curl_setopt($this->curl, CURLOPT_POST, true);
			curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($this->curl, CURLOPT_URL, $this->base_url . $query);
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($postfields));		
			$this->set_curl_response = curl_exec($this->curl);
			$this->set_curl_info_http_code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
		}
		
		private function curl_put($query,$postfields) {
			curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($this->curl, CURLOPT_URL, $this->base_url . $query);
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($postfields));
			$this->set_curl_response = curl_exec($this->curl);
			$this->set_curl_info_http_code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
		}
		
		private function curl_delete($query,$query_params) {		
			curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "DELETE");
			curl_setopt($this->curl, CURLOPT_URL, $this->base_url . $query . $query_params);	
			$this->set_curl_response = curl_exec($this->curl);
			$this->set_curl_info_http_code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
		}
			
		#####################################################################################################
		#																									#
		#										GET Methods													#
		#																									#
		#####################################################################################################
		
		public function get_curl_response() {
			return $this->curl_response;
		}
		
		public function get_curl_info_http_code() {
			return $this->curl_info_http_code;
		}
		
		#####################################################################################################
		#																									#
		#										SET Methods													#
		#																									#
		#####################################################################################################
		
		private function set_curl_response($curl_response) {
			$this->curl_response = $curl_response;
		}
		
		private function set_curl_info_http_code($curl_info_http_code) {
			$this->curl_info_http_code = $curl_info_http_code;
		}	

		#####################################################################################################
		#																									#
		#									GET Referenz Methods											#
		#																									#
		#####################################################################################################
		
		function get_host_ref($fqdn,$params = "") {
			$this->connect();
			$query = '/record:host';
			$query_params = '?name=' . $fqdn;			
			return $this->curl_get($query,$query_params,$params);		
			$this->disconnect();
		}
				
		function get_host_ref_ipv4($ipv4) {
			$this->connect();
			$query = '/record:host';
			$query_params = '?ipv4addr=' . $ipv4;
			return $this->curl_get($query,$query_params);	
			$this->disconnect();
		}
		
		function get_arecord_ref($fqdn) {
			$this->connect();
			$query = '/record:a';
			$query_params = '?name=' . $fqdn;
			return $this->curl_get($query,$query_params);
			$this->disconnect();
		}
					
		function get_arecord_ref_ipv4($ipv4) {
			$this->connect();
			$query = '/record:a';
			$query_params = '?ipv4addr=' . $ipv4;
			return $this->curl_get($query,$query_params);
			$this->disconnect();
		}
		
		function get_cname_ref($fqdn) {
			$this->connect();
			$query = '/record:cname';
			$query_params = '?name=' . $fqdn;
			return $this->curl_get($query,$query_params);
			$this->disconnect();
		}
		
		function get_alias_ref($fqdn) {
			$this->connect();
			$query = '/record:host';
			$query_params = '?alias=' . $fqdn . '&_return_fields=aliases';
			return $this->curl_get($query,$query_params);;
			$this->disconnect();
		}
		
		function get_roaminghost_ref($name) {
			$this->connect();
			$query = '/roaminghost';
			$query_params = '?name=' . $name;
			return $this->curl_get($query,$query_params);
			$this->disconnect();
		}
		
		function get_fixedaddress_ref($address) {
			$this->connect();
			$query = '/fixedaddress';
			$query_params = '?ipv4addr=' . $address;
			return $this->curl_get($query,$query_params);
			$this->disconnect();
		}
				
		#####################################################################################################
		#																									#
		#										CREATE Methods												#
		#																									#
		#####################################################################################################
		
		function create_host($fqdn, $ipaddress, $comment = '') {
			$this->connect();
			$host_ref = $this->get_host_ref($fqdn);
			if (count($host_ref) == 0) {
				$query = '/record:host';
				$postfields = array("name" => $fqdn, "ipv4addrs" => array(array("ipv4addr" => $ipaddress)), "comment" => $comment);
				$this->curl_post($query,$postfields);
			}
			$this->disconnect();
		}
				
		function create_host_next_available_ip($network,$exclude) {
			$this->connect();
			$query = '/network';
			$postfields = array("_object_function" => "next_available_ip",
								"_parameters" => array("exclude" => $exclude),
								"_result_field" => "ips", 
								"_object" => "network",
								"_object_parameters" => array("network" => $network));
			$this->curl_get($query,$query_params);
			$this->disconnect();
		}
		
		function create_arecord($fqdn,$ipaddress,$comment = "") {
			$this->connect();
			$arecord_ref = $this->get_arecord_ref($fqdn);
			if (count($arecord_ref) == 0) {
				$query = '/record:a';
				$postfields = array("name" => $fqdn, "ipv4addr" => $ipaddress, "comment" => $comment);
				$this->curl_post($query,$postfields);
			}
			$this->disconnect();
		}
		
		function create_cname($fqdn,$cname,$comment = "") {
			$this->connect();
			$cname_ref = $this->get_cname_ref($fqdn);
			if(count($cname_ref) == 0) {
				$query = '/record:cname';
				$postfields = array("name" => $fqdn, "canonical" => $cname, "comment" => $comment);
				$this->curl_post($query,$postfields);
			}
			else
				echo "CNAME bereits vorhanden!";
			
			$this->disconnect();
		}
		
		function create_alias($alias, $fqdn) {
			$this->connect();
			$alias_ref = $this->get_alias_ref($alias);
			if(count($alias_ref) == 0) {
				$host_ref = $this->get_host_ref($fqdn,"&_return_fields=aliases");			
				if(array_key_exists("aliases",$host_ref[0]))
					$alias_array = $host_ref[0]->aliases;
				
				if(is_array($alias)) 
					$alias_array = array_merge($alias_array,$alias);
				else
					$alias_array[] = $alias;
				
				preg_match('/^record:host\/(\S+):/', $host_ref[0]->_ref, $host_ref_found);
				$query = '/record:host/' . $host_ref_found[1];			
				$postfields = array("aliases" => $alias_array);
				$this->curl_put($query,$postfields);
			}
			else
				echo "Alias ist bereits vorhanden!";
			
			$this->disconnect();
		}
		
		function create_roaminghost($name,$mac,$comment = "",$domain) {
			#,array("name" => "domain-name", "num" => "15", "use_option" => "domain-name", "value" => $domain)
			$this->connect();
			$roaminghost_ref = $this->get_roaminghost_ref($name);			
			if(count($roaminghost_ref) == 0) {
				$query = '/roaminghost';
				$postfields = array("name" => $name, 
									"mac" => $mac, 
									"match_client" => "MAC_ADDRESS", 
									"address_type" => "IPV4",								
									"use_options" => TRUE,
									"options" => array(array("name" => "host-name", "num" => 12,"value" => $name),
													   array("name" => "domain-name", "num" => 15,"value" => $domain)),
									"comment" => $comment);
				$this->curl_post($query,$postfields);
			}
			else
				echo "Roaminghost bereits vorhanden!";
				
			$this->disconnect();
		}
		
		function create_fixedaddress($address,$mac,$name,$comment = "") {
			$this->connect();
			$fixedaddress_ref = $this->get_fixedaddress_ref($address);
			if(count($fixedaddress_ref) == 0) {
				$query = '/fixedaddress';
				$postfields = array("ipv4addr" => $address, "mac" => $mac, "name" => $name, "comment" => $comment);
				$this->curl_post($query,$postfields);
			}
			else
				echo "Fixed Address bereits vorhanden!";
				
			$this->disconnect();
		}
		#####################################################################################################
		#																									#
		#										DELETE Methods												#
		#																									#
		#####################################################################################################
		
		function delete_host($fqdn) {
			$this->connect();
			$host_ref = $this->get_host_ref($fqdn);
			if (count($host_ref) == 1) {
				preg_match('/^record:host\/(\S+):/', $host_ref[0]->_ref, $host_ref_found);		
				$query = '/record:host';
				$query_params = '/' . $host_ref_found[1];
				$this->curl_delete($query,$query_params);
			}
			else
				echo "Host nicht gefunden!";
			$this->disconnect();
		}
		
		function delete_arecord($fqdn) {
			$this->connect();
			$arecord_ref = $this->get_arecord_ref($fqdn);
			if (count($arecord_ref) == 1) {
				preg_match('/^record:a\/(\S+):/', $arecord_ref[0]->_ref, $arecord_ref_found);		
				$query = '/record:a';
				$query_params = '/' . $arecord_ref_found[1];
				$this->curl_delete($query,$query_params);
			}
			else
				echo "A RECORD nicht gefunden!";
			
			$this->disconnect();
		}
		
		function delete_cname($fqdn) {
			$this->connect();
			$cname_ref = $this->get_cname_ref($fqdn);
			if(count($cname_ref) == 1) {
				preg_match('/^record:cname\/(\S+):/', $cname_ref[0]->_ref, $cname_ref_found);
				$query = '/record:cname';
				$query_params = '/' . $cname_ref_found[1];
				$this->curl_delete($query,$query_params);
			}
			else
				echo "CNAME nicht gefunden!";
				
			$this->disconnect();
		}
		
		function delete_alias($alias,$fqdn) {
			$this->connect();
			$alias_ref = $this->get_alias_ref($alias);
			if(count($alias_ref) == 1) {
				$alias_array = $alias_ref[0]->aliases;
				
				if(($key = array_search($alias, $alias_array)) !== false)
					unset($alias_array[$key]);
				
				$alias_array = array_merge($alias_array);

				preg_match('/^record:host\/(\S+):/', $alias_ref[0]->_ref, $host_ref_found);
				$query = '/record:host/' . $host_ref_found[1];			
				$postfields = array("aliases" => $alias_array);
				$this->curl_put($query,$postfields);
			}
			else
				echo "Alias nicht vorhanden!";
				
			$this->disconnect();
		}
		
		function delete_alias_all($fqdn) {
			$this->connect();
			$host_ref = $this->get_host_ref($fqdn);			
			preg_match('/^record:host\/(\S+):/', $host_ref[0]->_ref, $host_ref_found);
			$query = '/record:host/' . $host_ref_found[1];			
			$postfields = array("aliases" => array());
			$this->curl_put($query,$postfields);			
			$this->disconnect();
		}
		
		function delete_roaminghost($name) {
			$this->connect();
			$roaminghost_ref = $this->get_roaminghost_ref($name);
			if(count($roaminghost_ref) == 1) {
				preg_match('/^roaminghost\/(\S+):/', $roaminghost_ref[0]->_ref, $roaminghost_ref_found);
				$query = '/roaminghost';
				$query_params = '/' . $roaminghost_ref_found[1];
				$this->curl_delete($query,$query_params);
			}
			else
				echo "Roaminghost nicht gefunden!";
			
			$this->disconnect();
		}
		
		function delete_fixedaddress($address) {
			$this->connect();
			$fixedaddress_ref = $this->get_fixedaddress_ref($address);
			if(count($fixedaddress_ref) == 1) {
				preg_match('/^fixedaddress\/(\S+):/', $fixedaddress_ref[0]->_ref, $fixedaddress_ref_found);	
				$query = '/fixedaddress';
				$query_params = '/' . $fixedaddress_ref_found[1];
				$this->curl_delete($query,$query_params);
			}
			else
				echo "Fixed Address nicht gefunden!";	
				
			$this->disconnect();
		}
		
		#####################################################################################################
		#																									#
		#										EDIT Methods												#
		#																									#
		#####################################################################################################
		
		function edit_host($ipv4,$fqdn) {
			$this->connect();
			$host_ref = $this->get_host_ref_ipv4($ipv4);
			if (count($host_ref) == 1) {
				preg_match('/^record:host\/(\S+):/', $host_ref[0]->_ref, $host_ref_found);
				$query = '/record:host/' . $host_ref_found[1];			
				$postfields = array("name" => $fqdn);
				$this->curl_put($query,$postfields);
			}
			$this->disconnect();
		}
		
		function edit_host_fqdn($fqdn_old,$fqdn_new) {
			$this->connect();
			$host_ref = $this->get_host_ref($fqdn_old);
			if (count($host_ref) == 1) {
				preg_match('/^record:host\/(\S+):/', $host_ref[0]->_ref, $host_ref_found);
				$query = '/record:host/' . $host_ref_found[1];			
				$postfields = array("name" => $fqdn_new);
				$this->curl_put($query,$postfields);
			}
			$this->disconnect();
		}
		
		#####################################################################################################
		#																									#
		#										SEARCH Methods												#
		#																									#
		#####################################################################################################
		
		function search_network($network) {
			$this->connect();
			$query = '/network';
			$query_params = '?comment~:=' . $network;
			return $this->curl_get($query,$query_params);
			$this->disconnect();
		}
		
		function search_host($host) {
			$this->connect();
			$query = '/record:host';
			$query_params = '?name~=' . $host;
			return $this->curl_get($query,$query_params);
			$this->disconnect();				
		}
		
		function search_roaminghost_by_domain($domain) {
			$this->connect();
			$query = '/roaminghost';
			$query_params = '?ddns_domainname=' . $domain;
			$this->curl_get($query,$query_params);
			$this->disconnect();
		}
		
    }
?>