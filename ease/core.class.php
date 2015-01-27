<?php
/*
* Copyright 2014 Cloudward, Inc.
*
* This project is licensed under the terms of either the GNU General Public
* License Version 2 with Classpath Exception or the Common Development and
* Distribution License Version 1.0 (the "License").  See the LICENSE.txt
* file for details.
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*/

/**
 * EASE Framework Core
 *
 * @author Mike <mike@cloudward.com>
 */
class ease_core
{
	public $config = array();
	public $globals = array();
	public $environment;
	public $namespace = '';
	public $db;
	public $memcache;
	public $application_root;
	public $web_basedir;
	public $request_espx_path;
	public $request_espx_path_dir;
	public $ease_block_start = '<#';
	public $ease_block_end = '#>';
	public $global_reference_start = '[';
	public $global_reference_end = ']';
	public $service_endpoints = array('form'=>'/ease/form', 'google_oauth2callback'=>'/oauth2callback', 'logout'=>'/logout');
	public $reserved_buckets = array('system', 'session', 'cookie', 'url', 'uri', 'cache', 'config', 'request', 'post', 'form', 'ease_forms');
	public $reserved_sql_tables = array('ease_config', 'ease_google_spreadsheets', 'ease_forms');
	public $reserved_sql_columns = array('instance_id', 'id', 'uuid', 'created_on', 'updated_on');
	public $include_from_sql = null;
	public $google_api_scopes = 'https://spreadsheets.google.com/feeds https://www.googleapis.com/auth/drive.file https://www.googleapis.com/auth/drive.readonly https://www.googleapis.com/auth/gmail.compose';
	public $google_spreadsheets = array();
	public $google_spreadsheets_by_name = array();
	public $catch_cookies = false;
	public $cookies = null;
	public $catch_redirect = false;
	public $redirect = null;
	public $db_disabled = false;
	public $php_disabled = false;
	public $include_disabled = false;
	public $inject_config_disabled = false;
	public $deliver_disabled = false;
	public $file_upload_disabled = false;
	public $parse_errors = null;
	public $google_api_errors = null;

	function __construct($params=array()) {
		// use UTF-8 character encoding when parsing or generating text
		ini_set('default_charset', 'UTF-8');
		// configure application root directory
		if(isset($params['application_root'])) {
			$this->application_root = $params['application_root'];
		} else {
			// default the app root directory to the directory above the 'ease' directory containing this file
			$this->application_root = preg_replace('@' . preg_quote(DIRECTORY_SEPARATOR, '@') . 'ease$@', '', dirname(__FILE__));
		}
		// add the application root directory, and "/ease/lib/" to the list of include paths
		set_include_path($this->application_root . PATH_SEPARATOR . get_include_path() . PATH_SEPARATOR . $this->application_root . DIRECTORY_SEPARATOR . 'ease' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR);
		// configure environment (e.g. DEV, TEST, INT, QA, UAT, PROD)
		if(isset($params['environment'])) {
			// an environment setting was provided with the initialization parameters
			$this->environment = $params['environment'];
		} else {
			// default the environment to DEV if the current server is 'localhost', PROD otherwise
			if($_SERVER['SERVER_NAME']=='localhost') {
				$this->environment = 'DEV';
			} else {
				$this->environment = 'PROD';
			}
		}
		// process the EASE configuration file in the application root in a file named easeConfig.json
		if(file_exists($this->application_root . DIRECTORY_SEPARATOR . 'easeConfig.json')) {
			$this->config = json_decode(file_get_contents($this->application_root . DIRECTORY_SEPARATOR . 'easeConfig.json'), true);
		} else {
			// an EASE configuration file was not found.  use the values in the PHP superglobal $_SERVER as the configuration
			// this method is used for AWS, and is the preferred configuration method as it saves loading and parsing a JSON file for every request
			$this->config = $_SERVER;
		}
		// check if there are environment specific configurations for the current host
		if(isset($this->config[$_SERVER['SERVER_NAME']])) {
			$environment_config = $this->config[$_SERVER['SERVER_NAME']];
		}
		// initialize global configuration
		if(isset($this->config['configuration'])) {
			$this->config = $this->config['configuration'];
		}
		// apply any environment specific configurations, overwriting global configuration
		if(isset($environment_config)) {
			foreach($environment_config as $key=>$value) {
				$this->config[$key] = $value;
			}
		}
		// apply any parameter provided configurations, overwriting global and environment specific configurations
		if(isset($params['google_token_message'])) {
			$this->config['google_token_message'] = $params['google_token_message'];
		}
		if(isset($params['database_host'])) {
			$this->config['database_host'] = $params['database_host'];
		}
		if(isset($params['database_username'])) {
			$this->config['database_username'] = $params['database_username'];
		}
		if(isset($params['database_password'])) {
			$this->config['database_password'] = $params['database_password'];
		}
		if(isset($params['web_basedir'])) {
			$this->config['web_basedir'] = $params['web_basedir'];
		}
		if(isset($params['suppress_503_headers'])) {
			$this->config['suppress_503_headers'] = $params['suppress_503_headers'];
		}
		foreach($this->service_endpoints as $service=>$endpoint) {
			if(isset($params[$service . '_service_endpoint'])) {
				$this->config[$service . '_service_endpoint'] = $params[$service . '_service_endpoint'];
			}
		}
		// apply any web_basedir configuration for use in EASE service URLs
		// example: post forms to /subdirectory/ease/form instead of /ease/form
		if(isset($this->config['web_basedir']) && strtolower($this->config['web_basedir'])=='auto'
		  && isset($_SERVER['DOCUMENT_ROOT']) && $this->application_root!=$_SERVER['DOCUMENT_ROOT']
		  && preg_match('@^' . preg_quote($_SERVER['DOCUMENT_ROOT'], '@') . '(.*)$@i', $this->application_root, $matches)) {
			// configuration was set to "auto" and the EASE application root is in a subdirectory of the web server document root.
			// set the web_basedir to the difference of the web server document root and the EASE application root
			$this->web_basedir = str_replace(DIRECTORY_SEPARATOR, '/', $matches[1]);
		} elseif(isset($this->config['web_basedir'])) {
			$this->web_basedir = $this->config['web_basedir'];
		} else {
			$this->web_basedir = '';
		}
		// apply any EASE service endpoint configurations
		foreach($this->service_endpoints as $service=>$endpoint) {
			if(isset($this->config[$service . '_service_endpoint']) && trim($this->config[$service . '_service_endpoint'])!='') {
				// a custom service endpoint was configured
				$this->service_endpoints[$service] = $this->config[$service . '_service_endpoint'];
			} elseif(trim($this->web_basedir)!='') {
				// a custom service endpoint was not configured, but a web basedir was, prepend it to the default endpoint
				$this->service_endpoints[$service] = $this->web_basedir . $endpoint;
			}
		}
		// initialize SQL database connection
		if(((!isset($this->config['database_host'])) || trim($this->config['database_host'])=='') && isset($_SERVER['RDS_HOSTNAME']) && trim($_SERVER['RDS_HOSTNAME'])!='') {
			// a database was not configured, but Amazon RDS DB connection params were set for the environment
			$this->config['database_host'] = 'mysql:host=' . $_SERVER['RDS_HOSTNAME'] . ';port=' . $_SERVER['RDS_PORT'] . ';dbname=' . $_SERVER['RDS_DB_NAME'];
			$this->config['database_username'] = $_SERVER['RDS_USERNAME'];
			$this->config['database_password'] = $_SERVER['RDS_PASSWORD'];
		}
		if(@$this->config['database_host'] && @$this->config['database_username']) {
			try {
				$this->db = new PDO($this->config['database_host'], $this->config['database_username'], $this->config['database_password']);
			} catch(PDOException $e) {
				// there was an error attempting to connect to the database... check for invalid database name error
				if($e->getCode()=='1049') {
					// invalid database name... remove the database name from the PDO host connection string, then attempt to create the database
					preg_match('/(.*?)(;*dbname=)([^;]*)(.*)/is', $this->config['database_host'], $matches);
					$pdo_host_without_dbname = $matches[1] . $matches[4];
					try {
						$matches[3] = preg_replace('/[^a-z0-9-]+/is', '_', $matches[3]);
						$this->db = new PDO($pdo_host_without_dbname, $this->config['database_username'], $this->config['database_password']);
						$this->db->exec("CREATE DATABASE IF NOT EXISTS `$matches[3]`;");
					} catch(PDOException $e) {
						// could not connect to database, or create the database
						if(!@$this->config['suppress_503_headers']) {
							header('HTTP/1.1 503 Service Unavailable');
						}
						echo 'Could not connect to database.';
						exit;
					}
				} else {
					// connection error... apply exponential backoff and retry 5 times
					$try_count = 0;
					while($this->db===null && $try_count<=5) {
						if($try_count > 0) {
							// this isn't the first try
							sleep((2 ^ ($try_count - 1)) + (mt_rand(1, 1000) / 1000));
						}
						$try_count++;
						try {
							$this->db = new PDO($this->config['database_host'], $this->config['database_username'], $this->config['database_password']);
						} catch(PDOException $e) {
							// error... try again
							continue;
						}
					}
					if($try_count > 5) {
						// could not connect to database
						if(!@$this->config['suppress_503_headers']) {
							header('HTTP/1.1 503 Service Unavailable');
						}
						echo 'Could not connect to database.';
						exit;
					}
				}
			}
		}
		// initialize memcache connection
		if(class_exists('Memcached')) {
			$this->memcache = new Memcached;
			$this->memcache->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
			if(isset($this->config['elasticache_config_endpoint']) && trim($this->config['elasticache_config_endpoint'])!='') {
				// an ElastiCache configuration endpoint was configured for auto-discovery of nodes in a memcache cluster
				$this->memcache->setOption(Memcached::OPT_CLIENT_MODE, Memcached::DYNAMIC_CLIENT_MODE);
				$this->memcache->addServer($this->config['elasticache_config_endpoint'], (isset($this->config['elasticache_config_port']) && trim($this->config['elasticache_config_port'])!='') ? intval($this->config['elasticache_config_port']) : 11211);
			} elseif(isset($this->config['memcache_host']) && trim($this->config['memcache_host'])!='' && isset($this->config['memcache_port']) && trim($this->config['memcache_port'])!='') {
				// an external memcache host was configured
				$this->memcache->addServer($this->config['memcache_host'], $this->config['memcache_port']);
			}
		}
	}

	function __destruct() {
		// explicitly disconnect the database and memcache
		$this->db = null;
		$this->memcache = null;
	}

	function handle_request() {
		// initialize the session, loading current session data into the global $_SESSION variable
		session_start();
		// determine the requested filepath
		$request_path_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
		$request_path = $request_path_parts[0];
		if(preg_match('@^' . preg_quote($this->web_basedir, '@') . '(.*)$@i', $request_path, $matches)) {
			$request_path = $matches[1];
		}
		$request_file_path = str_replace('/', DIRECTORY_SEPARATOR, $request_path);
		// check if the request is for a core EASE service endpoint
		if($request_path=='/ease/form') {
			$this->handle_form();
		} elseif($request_path=='/logout') {
			$this->handle_logout();
		} elseif($request_path=='/oauth2callback') {
			$this->handle_google_oauth2callback();
		} elseif($request_path=='/flush_spreadsheet_cache') {
			$this->handle_flush_spreadsheet_cache();
		} else {
			// the request was not for a core EASE service endpoint
			// check if the requested path translates to an existing ESPX file
			$request_path_parts = pathinfo($request_file_path);
			if(isset($_REQUEST['page'])) {
				$requested_page_path = str_replace('/', DIRECTORY_SEPARATOR, $_REQUEST['page']);
			}
			if($request_path=='/' && isset($requested_page_path) && file_exists($this->application_root . DIRECTORY_SEPARATOR . $requested_page_path . '.espx')) {
				// the root path was requsted, and the "page" variable in the URL points to an existing ESPX file;  flag the "page" ESPX file for processing
				$espx_filepath = $this->application_root . DIRECTORY_SEPARATOR . $requested_page_path . '.espx';
				$this->globals['system.page'] = $_REQUEST['page'];
				$this->globals['system.page'] = preg_replace('@/index$@is', '/', $this->globals['system.page']);
			} elseif($request_path=='/' && isset($requested_page_path) && substr($_REQUEST['page'], -1, 1)=='/' && file_exists($this->application_root . DIRECTORY_SEPARATOR . $requested_page_path . 'index.espx')) {
				// the requested path did not point to an existing ESPX file, even when assuming .espx extension or looking for index.espx in the directory
				// the "page" variable in the URL points to a directory that includes an index.espx file;  flag that index.espx file for processing
				$espx_filepath = $this->application_root . DIRECTORY_SEPARATOR . $requested_page_path . 'index.espx';
				$this->globals['system.page'] = ltrim($_REQUEST['page'], '/');
			} elseif(substr($request_path, -1, 1)=='/' && file_exists($this->application_root . $request_file_path . 'index.espx')) {
				// the requested path was a directory that includes an index.espx file;  flag the index.espx file for processing
				$espx_filepath = $this->application_root . $request_file_path . 'index.espx';
				$this->globals['system.page'] = ltrim($request_path, '/');
			} elseif(isset($request_path_parts['extension']) && strtolower($request_path_parts['extension'])=='espx' && file_exists($this->application_root . $request_file_path)) {
				// the requested path was an existing ESPX file;  flag the requested ESPX file for processing
				$espx_filepath = $this->application_root . $request_file_path;
				$this->globals['system.page'] = ltrim($request_path, '/');
				$this->globals['system.page'] = preg_replace('@/index\.espx$@is', '/', $this->globals['system.page']);
			} elseif(file_exists($this->application_root . $request_file_path . '.espx')) {
				// the requested translates to an ESPX file if you assume an .espx extension; assume .espx and flag the ESPX file for processing
				// * if both of these exist: /path/to/dir/name.espx & /path/to/dir/name/index.espx - then a request to /dir/name would go to /dir/name.espx
				$espx_filepath = $this->application_root . $request_file_path . '.espx';
				$this->globals['system.page'] = ltrim($request_path, '/');
				$this->globals['system.page'] = preg_replace('@/index$@is', '/', $this->globals['system.page']);
			} elseif(is_dir($request_file_path) && file_exists($this->application_root . $request_file_path . DIRECTORY_SEPARATOR . 'index.espx')) {
				// the requested path lacked a trailing directory separator, and an assumed .espx extension did not point to an existing ESPX file...
				// the requested path was for directory that includes an index.espx file;  flag that index.espx file for processing
				$espx_filepath = $this->application_root . $request_file_path . DIRECTORY_SEPARATOR . 'index.espx';
				$this->globals['system.page'] = ltrim($request_path . '/', '/');
			} elseif(isset($requested_page_path) && file_exists($this->application_root . DIRECTORY_SEPARATOR . $requested_page_path . '.espx')) {
				// the requested path did not point to an existing ESPX file, even when assuming .espx extension or looking for index.espx in the directory
				// the "page" variable in the URL points to an existing ESPX file;  flag the "page" ESPX file for processing
				$espx_filepath = $this->application_root . DIRECTORY_SEPARATOR . $requested_page_path . '.espx';
				$this->globals['system.page'] = ltrim($_REQUEST['page'], '/');
				$this->globals['system.page'] = preg_replace('@/index$@is', '/', $this->globals['system.page']);
			}
			// if the $espx_file variable is set, the request translated to an existing ESPX file;  process that ESPX file
			if(isset($espx_filepath) && trim($espx_filepath)!='') {
				// ESPX file flagged for processing... get path information about the file
				$espx_filepath_parts = pathinfo($espx_filepath);
				$this->request_espx_path = $espx_filepath;
				$this->request_espx_path_dir = $espx_filepath_parts['dirname'];
				// don't allow direct requests to header or footer ESPX files
				if((strtolower($espx_filepath_parts['basename'])=='header.espx') || (strtolower($espx_filepath_parts['basename'])=='footer.espx')) {
					header('HTTP/1.1 403 Forbidden');
					echo 'Direct requests for header and footer ESPX files are forbidden.';
					exit;
				}
				// add the ESPX filepath directory to the PHP include path list
				set_include_path(get_include_path() . PATH_SEPARATOR . $this->application_root . $espx_filepath_parts['dirname']);
				// load the EASE body of the ESPX file
				$espx_body = file_get_contents($espx_filepath);
				// apply any header or footer content to the EASE body
				$header_filepath = $espx_filepath_parts['dirname'] . DIRECTORY_SEPARATOR . 'header.espx';
				$footer_filepath = $espx_filepath_parts['dirname'] . DIRECTORY_SEPARATOR . 'footer.espx';
				if(file_exists($header_filepath)) {
					$espx_body = file_get_contents($header_filepath) . $espx_body;
				}
				if(file_exists($footer_filepath)) {
					$espx_body .= file_get_contents($footer_filepath);
				}
				// process the ESPX body as EASE
				$this->process_ease($espx_body);
			} else {
				// the request did not translate to an existing ESPX file
				header('HTTP/1.1 404 Not Found');
				echo "<html><head><title>EASE File Not Found</title></head><body style='background-color:#DDDDDD;'>\n";
				echo "<div style='width:100%; margin:auto; margin-top:120px; font-style:italic; font-size:297px; text-align:center; color:#CFCFCF; margin-bottom:0px; padding-bottom:0px; line-height:360px; padding:0px;'>EASE</div>\n";
				echo "<div style='width:100%; margin:auto; text-align:center; font-size:38px; color:#BBBBBB; vertical-align:top; line-height:38px; padding:0px; margin-top:-32px;'>File Not Found</div>\n";
				echo "</body></html>";
				exit;
			}
		}
	}

	function process_ease($content, $return_output_buffer=false) {
		// parse the provided content string, and immediately process any EASE or PHP
		$this->set_global_system_vars();
		require_once 'ease/parser.class.php';
		$ease_parser = new ease_parser($this);
		$process_result = $ease_parser->process($content, $return_output_buffer);
		if(count($ease_parser->errors) > 0) {
			$this->parse_errors[] = $ease_parser->errors;
		}
		$ease_parser = null;
		return $process_result;
	}

	function handle_form() {
		// the request was for the EASE Form handling service
		$this->set_global_system_vars();
		require_once 'ease/form_handler.class.php';
		$form_handler = new ease_form_handler($this);
		$form_handler->process();
	}

	// process OAuth 2.0 callbacks for Google API Access Tokens
	function handle_google_oauth2callback() {
		// load Google API Client info
		$this->set_global_system_vars();
		$this->load_system_config_var('gapp_client_id');
		$this->load_system_config_var('gapp_client_secret');
		$this->load_system_config_var('gapp_scopes');
		if((!isset($this->config['gapp_scopes'])) || (trim($this->config['gapp_scopes'])=='')) {
			// Google API Scopes were not configured... 
			// this is likely an app that has upgraded from an earlier version of the EASE Framework that didn't store the scopes
			// set the scopes to the full 
			$this->set_system_config_var('gapp_scopes', $this->google_api_scopes);
		}
		// initialize a Google API Client
		require_once 'ease/lib/Google/Client.php';
		$client = new Google_Client();
		$client->setClientId($this->config['gapp_client_id']);
		$client->setClientSecret($this->config['gapp_client_secret']);
		$client->setRedirectUri((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $this->service_endpoints['google_oauth2callback']);
		$client->setScopes($this->config['gapp_scopes']);
		$client->setAccessType('offline');
		$client->setApprovalPrompt('force');
		if(isset($this->config['elasticache_config_endpoint']) && trim($this->config['elasticache_config_endpoint'])!='') {
			// the Google APIs default use of memcache is not supported while using the AWS ElastiCache Cluster Client
			require_once 'ease/lib/Google/Cache/Null.php';
			$cache = new Google_Cache_Null($client);
			$client->setCache($cache);
		} elseif(isset($this->config['memcache_host']) && trim($this->config['memcache_host'])!='') {
			// an external memcache host was configured, pass on that configuration to the Google API Client
			$client->setClassConfig('Google_Cache_Memcache', 'host', $this->config['memcache_host']);
			if(isset($this->config['memcache_port']) && trim($this->config['memcache_port'])!='') {
				$client->setClassConfig('Google_Cache_Memcache', 'port', $this->config['memcache_port']);
			} else {
				$client->setClassConfig('Google_Cache_Memcache', 'port', 11211);
			}
			require_once 'ease/lib/Google/Cache/Memcache.php';
			$cache = new Google_Cache_Memcache($client);
			$client->setCache($cache);
		}
		try {
			$client->authenticate($_GET['code']);
		} catch(Google_Auth_Exception $e) {
			if(preg_match('/^(.*?), message: \'(.*?)\'$/is', $e->getMessage(), $matches)) {
				echo "<h1>$matches[1]</h1><p>\n";
				$message = json_decode($matches[2]);
				foreach($message as $key=>$value) {
					echo "<b>$key</b>: $value<br />\n";
					if($key=='error') {
						if($value=='invalid_client') {
							// the client is invalid, so wipe the settings in the memcache and database... revert to settings in easeConfig.json
							if($this->memcache) {
								$this->memcache->delete('system.gapp_client_id');
								$this->memcache->delete('system.gapp_client_secret');
							}
							if($this->db) {
								$query = $this->db->prepare("DELETE FROM ease_config WHERE name=:name;");
								$query->execute(array(':name'=>'gapp_client_id'));
								$query->execute(array(':name'=>'gapp_client_secret'));
							}
						} elseif($value=='unauthorized_client' || $value=='invalid_grant') {
							// the Google API Access Tokens were not created for this client, so wipe them as a new client has been configured
							// the user will be prompted to grant permissions again to link this app to a google account
							$this->flush_google_access_tokens();
						}
					}
				}
			} else {
				echo $e->getMessage();
			}
			exit;
		}
		$gapp_access_token_json = $client->getAccessToken();
		$access_token = json_decode($gapp_access_token_json);
		if($access_token==null) {
			echo 'Invalid Access Token';
			exit;
		}
		// store the new Google API Access Token in the memcache and database
		$gapp_access_token = $access_token->access_token;
		$gapp_expire_time = $_SERVER['REQUEST_TIME'] + $access_token->expires_in;
		$gapp_refresh_token = $access_token->refresh_token;
		$this->set_system_config_var('gapp_access_token_json', $gapp_access_token_json);
		$this->set_system_config_var('gapp_access_token', $gapp_access_token);
		$this->set_system_config_var('gapp_expire_time', $gapp_expire_time);
		$this->set_system_config_var('gapp_refresh_token', $gapp_refresh_token);
		// store any values in the DB that may have just come from an easeConfig.json file
		$this->set_system_config_var('gapp_client_id', $this->config['gapp_client_id']);
		$this->set_system_config_var('gapp_client_secret', $this->config['gapp_client_secret']);
		// redirect back to the page that initiated the OAuth 2.0 consent process
		if(isset($_REQUEST['state']) && trim($_REQUEST['state'])!='') {
			header('Location: ' . urldecode($_REQUEST['state']));
		} else {
			// a landing page wasn't set... default to the homepage
			header('Location: /');
		}
	}

	function handle_logout() {
		// EASE logout service; destroy the session and redirect to a landing page or the homepage
		session_destroy();
		if(isset($_REQUEST['landing']) && trim($_REQUEST['landing'])!='') {
			header("Location: {$_REQUEST['landing']}");
		} else {
			header('Location: /');
		}
	}

	function handle_flush_spreadsheet_cache() {
		if($this->db) {
			if($this->memcache) {
				if($result = $this->db->query('SELECT id, name, worksheet, namespace FROM ease_google_spreadsheets;')) {
					while($row = $result->fetch(PDO::FETCH_ASSOC)) {
						$this->memcache->delete("system.google_spreadsheets_by_id.{$row['id']}.{$row['worksheet']}");
						if($row['namespace']!='') {
							$this->memcache->delete("{$row['namespace']}.system.google_spreadsheets_by_name.{$row['name']}.{$row['worksheet']}");
						} else {
							$this->memcache->delete("system.google_spreadsheets_by_name.{$row['name']}.{$row['worksheet']}");
						}
					}
				}
			}
			$this->db->exec('DROP TABLE IF EXISTS ease_google_spreadsheets;');
		}
		echo 'Successfully flushed the Google Sheet Cache';
	}

	function flush_meta_data_for_google_spreadsheet_by_id($google_spreadsheet_id, $google_spreadsheet_sheet_name='') {
		$google_spreadsheet_id = preg_replace('/[^\w-]+/', '', $google_spreadsheet_id);
		$google_spreadsheet_sheet_name = trim($google_spreadsheet_sheet_name);
		if($google_spreadsheet_id=='') {
			return false;
		}
		if($this->db) {
			$query = $this->db->prepare("SELECT name, worksheet, namespace FROM ease_google_spreadsheets WHERE id=:id;");
			if($query->execute(array(':id'=>$google_spreadsheet_id))) {
				while($row = $query->fetch(PDO::FETCH_ASSOC)) {
					if($google_spreadsheet_sheet_name==$row['worksheet']) {
						unset($this->google_spreadsheets_by_name[$row['name']][$row['worksheet']]);
						if($this->memcache) {
							if($row['namespace']!='') {
								$this->memcache->delete("{$row['namespace']}.system.google_spreadsheets_by_name.{$row['name']}.{$row['worksheet']}");
							} else {
								$this->memcache->delete("system.google_spreadsheets_by_name.{$row['name']}.{$row['worksheet']}");
							}
						}
					} elseif($google_spreadsheet_sheet_name=='') {
						unset($this->google_spreadsheets_by_name[$row['name']]);
						if($this->memcache) {
							if($row['namespace']!='') {
								$this->memcache->delete("{$row['namespace']}.system.google_spreadsheets_by_name.{$row['name']}.");
								$this->memcache->delete("{$row['namespace']}.system.google_spreadsheets_by_name.{$row['name']}.{$row['worksheet']}");
							} else {
								$this->memcache->delete("system.google_spreadsheets_by_name.{$row['name']}.");
								$this->memcache->delete("system.google_spreadsheets_by_name.{$row['name']}.{$row['worksheet']}");
							}
							$this->memcache->delete("system.google_spreadsheets_by_id.$google_spreadsheet_id.{$row['worksheet']}");
						}
					}
				}
			}
		}
		if($google_spreadsheet_sheet_name!='') {
			unset($this->google_spreadsheets_by_id[$google_spreadsheet_id][$google_spreadsheet_sheet_name]);
			if($this->memcache) {
				$this->memcache->delete("system.google_spreadsheets_by_id.$google_spreadsheet_id.$google_spreadsheet_sheet_name");
			}
			if($this->db) {
				$query = $this->db->prepare("DELETE FROM ease_google_spreadsheets WHERE id=:id AND worksheet=:worksheet;");
				$query->execute(array(':id'=>$google_spreadsheet_id, ':worksheet'=>$google_spreadsheet_sheet_name));
			}
		} else {
			unset($this->google_spreadsheets_by_id[$google_spreadsheet_id]);
			if($this->memcache) {
				$this->memcache->delete("system.google_spreadsheets_by_id.$google_spreadsheet_id.");
			}
			if($this->db) {
				$query = $this->db->prepare("DELETE FROM ease_google_spreadsheets WHERE id=:id;");
				$query->execute(array(':id'=>$google_spreadsheet_id));
			}
		}
	}

	function load_meta_data_for_google_spreadsheet_by_id($google_spreadsheet_id, $google_spreadsheet_sheet_name='') {
		$google_spreadsheet_id = preg_replace('/[^\w-]+/', '', $google_spreadsheet_id);
		if($google_spreadsheet_id=='') {
			return false;
		}
		$google_spreadsheet_sheet_name = trim($google_spreadsheet_sheet_name);
		if(isset($this->google_spreadsheets_by_id[$google_spreadsheet_id][$google_spreadsheet_sheet_name])) {
			return $this->google_spreadsheets_by_id[$google_spreadsheet_id][$google_spreadsheet_sheet_name];
		}
		if($this->memcache) {
			$meta_data = $this->memcache->get("system.google_spreadsheets_by_id.$google_spreadsheet_id.$google_spreadsheet_sheet_name");
		} else {
			$meta_data = false;
		}
		if($meta_data!==false) {
			// a value was found in the memcache, use that value and return it
			$meta_data = (array)$meta_data;
			foreach($meta_data as $key=>$value) {
				if(is_object($value)) {
					$meta_data[$key] = (array)$value;
				}
			}
			return $this->google_spreadsheets[$google_spreadsheet_id][$google_spreadsheet_sheet_name] = $meta_data;
		} else {
			// the Google Spreadsheet ID was not found in the memcache; check for an available database
			if($this->db) {
				// a database is available, check if the EASE config in the database has a value set for the requested name
				$query = $this->db->prepare("SELECT meta_data_json FROM ease_google_spreadsheets WHERE id=:id AND worksheet=:worksheet;");
				if($query->execute(array(':id'=>$google_spreadsheet_id, ':worksheet'=>$google_spreadsheet_sheet_name))) {
					if($row = $query->fetch(PDO::FETCH_ASSOC)) {
						// a value was found in the database, use that value and set the value in the memcache and return it
						$meta_data = json_decode($row['meta_data_json']);
						$meta_data = (array)$meta_data;
						foreach($meta_data as $key=>$value) {
							if(is_object($value)) {
								$meta_data[$key] = (array)$value;
							}
						}
						if($this->memcache) {
							$this->memcache->set("system.google_spreadsheets_by_id.$google_spreadsheet_id.$google_spreadsheet_sheet_name", $meta_data);
						}
						return $this->google_spreadsheets[$google_spreadsheet_id][$google_spreadsheet_sheet_name] = $meta_data;
					}
				}
			}
			// a value wasn't found in an available database, attempt to load the spreadsheet from google
			$this->validate_google_access_token();
			// initialize a google drive API client for spreadsheets
			require_once 'ease/lib/Spreadsheet/Autoloader.php';
			$request = new Google\Spreadsheet\Request($this->config['gapp_access_token']);
			$serviceRequest = new Google\Spreadsheet\DefaultServiceRequest($request);
			Google\Spreadsheet\ServiceRequestFactory::setInstance($serviceRequest);
			$spreadsheetService = new Google\Spreadsheet\SpreadsheetService($request);
			@$spreadSheet = $spreadsheetService->getSpreadsheetById($google_spreadsheet_id);
			if($spreadSheet===null) {
				// unable to load spreadsheet
				return false;
			}
			$google_spreadsheet_name = $spreadSheet->getTitle();
			$worksheetFeed = $spreadSheet->getWorksheets();
			if($google_spreadsheet_sheet_name!='') {
				$worksheet = $worksheetFeed->getByTitle($google_spreadsheet_sheet_name);
			} else {
				$worksheet = $worksheetFeed->getFirstSheet();
			}
			if($worksheet===null) {
				// unable to load spreadsheet
				return false;
			}
			// request all cell data from the worksheet
			$cellFeed = $worksheet->getCellFeed();
			$cell_entries = $cellFeed->getEntries();
			$cells_by_row_by_column_letter = array();
			foreach($cell_entries as $cell_entry) {
				$cell_title = $cell_entry->getTitle();
				preg_match('/([A-Z]+)([0-9]+)/', $cell_title, $matches);
				$cells_by_row_by_column_letter[$matches[2]][$matches[1]] = $cell_entry->getContent();
			}
			foreach($cells_by_row_by_column_letter[1] as $column_letter=>$column_name) {
				$meta_data['column_letter_by_name'][preg_replace('/(^[0-9]+|[^a-z0-9]+)/', '', strtolower($column_name))] = $column_letter;
				$meta_data['column_name_by_letter'][$column_letter] = preg_replace('/(^[0-9]+|[^a-z0-9]+)/', '', strtolower($column_name));
			}
			$meta_data['id'] = $google_spreadsheet_id;
			$meta_data['name'] = $google_spreadsheet_name;
			$meta_data['worksheet'] = $google_spreadsheet_sheet_name;
			// store the meta data in the memcache and any available database, then return the meta data
			if($this->memcache) {
				$this->memcache->set("system.google_spreadsheets_by_id.$google_spreadsheet_id.$google_spreadsheet_sheet_name", $meta_data);
			}
			if($this->db) {
				$query = $this->db->prepare("	REPLACE INTO ease_google_spreadsheets
													(id, name, worksheet, meta_data_json, namespace)
												VALUES
													(:id, :name, :worksheet, :meta_data_json, :namespace)	");
				$params = array(
					':id'=>$google_spreadsheet_id,
					':name'=>$google_spreadsheet_name,
					':worksheet'=>$google_spreadsheet_sheet_name,
					':meta_data_json'=>json_encode($meta_data),
					':namespace'=>$this->namespace
				);
				$result = $query->execute($params);
				if(!$result) {
					// the query failed... attempt to create the ease_google_spreadsheets table, then try again
					$this->db->exec("	DROP TABLE IF EXISTS ease_google_spreadsheets;
										CREATE TABLE ease_google_spreadsheets (
											id VARCHAR(255) NOT NULL PRIMARY KEY,
											name TEXT NOT NULL DEFAULT '',
											worksheet TEXT NOT NULL DEFAULT '',
											meta_data_json TEXT NOT NULL DEFAULT '',
											namespace TEXT NOT NULL DEFAULT ''
										);	");
					$result = $query->execute($params);
				}
			}
			return $this->google_spreadsheets[$google_spreadsheet_id][$google_spreadsheet_sheet_name] = $meta_data;
		}
	}

	function load_meta_data_for_google_spreadsheet_by_name($google_spreadsheet_name, $google_spreadsheet_sheet_name='') {
		// cleanse the requested spreadsheet name
		$google_spreadsheet_name = trim($google_spreadsheet_name);
		$google_spreadsheet_sheet_name = trim($google_spreadsheet_sheet_name);
		if($google_spreadsheet_name=='') {
			// a spreadsheet name was not provided
			return false;
		}
		$google_spreadsheet_name_memcache_key = preg_replace('/\s+/s', '', $google_spreadsheet_name);
		$google_spreadsheet_sheet_name_memcache_key = preg_replace('/\s+/s', '', $google_spreadsheet_sheet_name);
		// check if the meta data for the spreadsheet is already loaded
		if(isset($this->google_spreadsheets_by_name[$google_spreadsheet_name][$google_spreadsheet_sheet_name])) {
			// meta data for the spreadsheet was found already loaded
			return $this->google_spreadsheets_by_name[$google_spreadsheet_name][$google_spreadsheet_sheet_name];
		}
		// meta data was not already loaded... check for it in the memcache
		if($this->memcache) {
			if($this->namespace!='') {
				$meta_data = $this->memcache->get("{$this->namespace}.system.google_spreadsheets_by_name.{$google_spreadsheet_name}.{$google_spreadsheet_sheet_name}");
			} else {
				$meta_data = $this->memcache->get("system.google_spreadsheets_by_name.$google_spreadsheet_name.$google_spreadsheet_sheet_name");
			}
		} else {
			$meta_data = false;
		}
		if($meta_data!==false) {
			// meta data for the spreadsheet was found in the memcache... cornvert the memcache object to an array and return it
			$meta_data = (array)$meta_data;
			foreach($meta_data as $key=>$value) {
				if(is_object($value)) {
					$meta_data[$key] = (array)$value;
				}
			}
			return $this->google_spreadsheets_by_name[$google_spreadsheet_name][$google_spreadsheet_sheet_name] = $meta_data;
		} else {
			// meta data for the spreadsheet was not found in the memcache... check for it in any available databases
			if($this->db) {
				// a database is available, check for meta data for the spreadsheet
				$query = $this->db->prepare("	SELECT meta_data_json FROM ease_google_spreadsheets 
												WHERE name=:name
													AND worksheet=:worksheet
													AND namespace=:namespace;	");
				if($query->execute(array(':name'=>$google_spreadsheet_name, ':worksheet'=>$google_spreadsheet_sheet_name, ':namespace'=>$this->namespace))) {
					if($row = $query->fetch(PDO::FETCH_ASSOC)) {
						// meta data for the spreadsheet was found in the database, cornvert the JSON to an array and return it
						$meta_data = json_decode($row['meta_data_json']);
						$meta_data = (array)$meta_data;
						foreach($meta_data as $key=>$value) {
							if(is_object($value)) {
								$meta_data[$key] = (array)$value;
							}
						}
						if($this->memcache) {
							if($this->namespace!='') {
								$this->memcache->set("{$this->namespace}.system.google_spreadsheets_by_name.$google_spreadsheet_name.$google_spreadsheet_sheet_name", $meta_data);
							} else {
								$this->memcache->set("system.google_spreadsheets_by_name.$google_spreadsheet_name.$google_spreadsheet_sheet_name", $meta_data);
							}
						}
						return $this->google_spreadsheets_by_name[$google_spreadsheet_name][$google_spreadsheet_sheet_name] = $meta_data;
					}
				}
			}
			// meta data for the spreadsheet was not found in any available databases, send a ship to planet google...
			// initialize a google drive API client for spreadsheets
			require_once 'ease/lib/Spreadsheet/Autoloader.php';
			$this->validate_google_access_token();
			$request = new Google\Spreadsheet\Request($this->config['gapp_access_token']);
			$serviceRequest = new Google\Spreadsheet\DefaultServiceRequest($request);
			Google\Spreadsheet\ServiceRequestFactory::setInstance($serviceRequest);
			$spreadsheetService = new Google\Spreadsheet\SpreadsheetService($request);
			$spreadsheetFeed = $spreadsheetService->getSpreadsheets();
			$google_spreadsheet_id = $spreadsheetFeed->getIdByTitle($google_spreadsheet_name);
			$spreadSheet = $spreadsheetFeed->getByTitle($google_spreadsheet_name);
			if($spreadSheet===null) {
				// unable to load spreadsheet
				return false;
			}
			$worksheetFeed = $spreadSheet->getWorksheets();
			if($google_spreadsheet_sheet_name!='') {
				$worksheet = $worksheetFeed->getByTitle($google_spreadsheet_sheet_name);
			} else {
				$worksheet = $worksheetFeed->getFirstSheet();
			}
			if($worksheet===null) {
				// unable to load worksheet
				return false;
			}
			// request all cell data from the worksheet
			$cellFeed = $worksheet->getCellFeed();
			$cell_entries = $cellFeed->getEntries();
			$cells_by_row_by_column_letter = array();
			foreach($cell_entries as $cell_entry) {
				$cell_title = $cell_entry->getTitle();
				preg_match('/([A-Z]+)([0-9]+)/', $cell_title, $matches);
				$cells_by_row_by_column_letter[$matches[2]][$matches[1]] = $cell_entry->getContent();
			}
			if(is_array($cells_by_row_by_column_letter) && count($cells_by_row_by_column_letter)>0) {
				foreach($cells_by_row_by_column_letter[1] as $column_letter=>$column_name) {
					$meta_data['column_letter_by_name'][preg_replace('/(^[0-9]+|[^a-z0-9]+)/', '', strtolower($column_name))] = $column_letter;
					$meta_data['column_name_by_letter'][$column_letter] = preg_replace('/(^[0-9]+|[^a-z0-9]+)/', '', strtolower($column_name));
				}
			} else {
				// TODO!! the spreadsheet is empty... add the header row and set the EASE Row ID
			}
			$meta_data['id'] = $google_spreadsheet_id;
			$meta_data['name'] = $google_spreadsheet_name;
			$meta_data['worksheet'] = $google_spreadsheet_sheet_name;
			// store the meta data in the memcache and any available database, then return the meta data
			if($this->memcache) {
				if($this->namespace!='') {
					$this->memcache->set("{$this->namespace}.system.google_spreadsheets_by_name.$google_spreadsheet_name", $meta_data);
				} else {
					$this->memcache->set("system.google_spreadsheets_by_name.$google_spreadsheet_name", $meta_data);
				}
			}
			if($this->db) {
				$query = $this->db->prepare("	REPLACE INTO ease_google_spreadsheets
													(id, name, worksheet, meta_data_json, namespace)
												VALUES
													(:id, :name, :worksheet, :meta_data_json, :namespace);	");
				$params = array(
					':id'=>$google_spreadsheet_id,
					':name'=>$google_spreadsheet_name,
					':worksheet'=>$google_spreadsheet_sheet_name,
					':meta_data_json'=>json_encode($meta_data),
					':namespace'=>$this->namespace
				);
				$result = $query->execute($params);
				if(!$result) {
					// the query failed... attempt to create the ease_google_spreadsheets table, then try again
					$this->db->exec("	DROP TABLE IF EXISTS ease_google_spreadsheets;
										CREATE TABLE ease_google_spreadsheets (
											id VARCHAR(255) NOT NULL PRIMARY KEY,
											name TEXT NOT NULL DEFAULT '',
											worksheet TEXT NOT NULL DEFAULT '',
											meta_data_json TEXT NOT NULL DEFAULT '',
											namespace TEXT NOT NULL DEFAULT ''
										);	");
					$result = $query->execute($params);
				}
			}
			return $this->google_spreadsheets_by_name[$google_spreadsheet_name][$google_spreadsheet_sheet_name] = $meta_data;
		}
	}

	function load_system_config_var($ease_config_var) {
		// this function attempts to load a system configuration value from the memcache.
		// if a value isn't found in the memcache, the database and EASE configuration will be checked
		$ease_config_var = strtolower(preg_replace('/\W+/', '', $ease_config_var));
		if($this->memcache) {
			$cache_value = $this->memcache->get("system.$ease_config_var");
		} else {
			$cache_value = false;
		}
		if($cache_value!==false) {
			// a value was found in the memcache, use that value and return it
			return $this->config[$ease_config_var] = $cache_value;
		} else {
			// a value wasn't found in the memcache, check if there is an available database
			if($this->db) {
				// a database is available, check if the EASE Config in the database has the requested value set
				$query = $this->db->prepare("SELECT value FROM ease_config WHERE name=:name AND namespace=:namespace;");
				$params = array(':name'=>$ease_config_var, ':namespace'=>$this->namespace);
				$result = $query->execute($params);
				if(!$result) {
					// the query failed... attempt to add the namespace column then try again
					$this->db->exec("ALTER TABLE ease_config ADD COLUMN namespace TEXT NOT NULL DEFAULT '';");
					$result = $query->execute($params);
				}
				if($result && $row = $query->fetch(PDO::FETCH_ASSOC)) {
					// a value was found in the database, use that value and set it in the memcache and return it
					if($this->memcache) {
						$this->memcache->set("system.$ease_config_var", $row['value']);
					}
					return $this->config[$ease_config_var] = $row['value'];
				}
			}
			// a value wasn't found in any database, check if the value was set in the EASE config file
			if(isset($this->config[$ease_config_var]) && trim($this->config[$ease_config_var])!='') {
				// a value was found the EASE config file, use that value and set the value in the memcache, and in the database, then return it
				$this->set_system_config_var($ease_config_var, $this->config[$ease_config_var]);
				return $this->config[$ease_config_var];
			}
			// a value could not be found
			return false;
		}
	}

	function set_system_config_var($ease_config_var, $value) {
		$ease_config_var = strtolower(preg_replace('/\W+/', '', $ease_config_var));
		if($this->memcache) {
			$this->memcache->set('system.' . $ease_config_var, $value);
		}
		if($this->db) {
			$query = $this->db->prepare("REPLACE INTO ease_config (name, value, namespace) VALUES (:name, :value, :namespace);");
			$params = array(':name'=>$ease_config_var, ':value'=>$value, ':namespace'=>$this->namespace);
			if(!$query->execute($params)) {
				// the query failed... attempt to add the namespace column then try again
				$this->db->exec("ALTER TABLE ease_config ADD COLUMN namespace TEXT NOT NULL DEFAULT '';");
				if(!$query->execute($params)) {
					// the query failed again.. attempt to create the ease_config table, then try again
					$this->db->exec("	CREATE TABLE ease_config (
											name VARCHAR(64) NOT NULL PRIMARY KEY,
											value TEXT NOT NULL DEFAULT '',
											namespace TEXT NOT NULL DEFAULT ''
										);	");
					$query->execute($params);
				}
			}
		}
		$this->config[$ease_config_var] = $value;
	}

	function validate_google_access_token($send_full_state=false) {
		// load connection parameters for the Google API Client
		$this->load_system_config_var('gapp_client_id');
		$this->load_system_config_var('gapp_client_secret');
		$this->load_system_config_var('gapp_access_token_json');
		$this->load_system_config_var('gapp_access_token');
		$this->load_system_config_var('gapp_expire_time');
		$this->load_system_config_var('gapp_refresh_token');
		$this->load_system_config_var('gapp_scopes');
		if((!isset($this->config['gapp_scopes'])) || (trim($this->config['gapp_scopes'])=='')) {
			// Google API Scopes are not currently configured...
			// check if an Access Token was already configured
			if(isset($this->config['gapp_access_token_json']) && (trim($this->config['gapp_access_token_json'])!='')) {
				// a Google API Access Token was already configured...
				// this is likely an app that has upgraded from an earlier version of EASE that didn't store the Google API Scopes
				// set the Google API Scopes to the initial set used by the EASE Framework
				$this->set_system_config_var('gapp_scopes', 'https://spreadsheets.google.com/feeds https://www.googleapis.com/auth/drive.file https://www.googleapis.com/auth/drive.readonly');
			} else {
				// a Google API Access Token was NOT already configured...
				// default to the latest set of Google API Scopes used by the EASE Framework
				$this->set_system_config_var('gapp_scopes', $this->google_api_scopes);
			}
		}

		// load the class definition for the Google API Client
		require_once 'ease/lib/Google/Client.php';
		// check for existing Google API Access Tokens
		if((!@$this->config['gapp_access_token'] && !@$this->config['gapp_refresh_token']) || !@$this->config['gapp_refresh_token']) {
			// Google API Access Tokens are NOT available, offer a button to generate them through an OAuth 2.0 consent process
			$client = new Google_Client();
			$client->setClientId($this->config['gapp_client_id']);
			$client->setClientSecret($this->config['gapp_client_secret']);
			$client->setRedirectUri((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $this->service_endpoints['google_oauth2callback']);
			// update the Google API Scopes to the latest set used by the EASE Framework
			$this->set_system_config_var('gapp_scopes', $this->google_api_scopes);
			$client->setScopes($this->config['gapp_scopes']);
			if($send_full_state) {
				$client->setState(urlencode($_SERVER['REQUEST_URI']));
			} else {
				// determine the requested filepath
				$request_path_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
				$request_path = $request_path_parts[0];
				//$client->setState((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $request_path);
				// URL encode the state variable as google doesn't encode it when adding it to the URL they redirect back to
				$client->setState(urlencode($request_path));
			}
			$client->setAccessType('offline');
			$client->setApprovalPrompt('force');
			if(isset($this->config['elasticache_config_endpoint']) && trim($this->config['elasticache_config_endpoint'])!='') {
				// the Google APIs default use of memcache is not supported while using the AWS ElastiCache Cluster Client...
				require_once 'ease/lib/Google/Cache/Null.php';
				$cache = new Google_Cache_Null($client);
				$client->setCache($cache);
			} elseif(isset($this->config['memcache_host']) && trim($this->config['memcache_host'])!='') {
				// an external memcache host was configured, pass on that configuration to the Google API Client
				$client->setClassConfig('Google_Cache_Memcache', 'host', $this->config['memcache_host']);
				if(isset($this->config['memcache_port']) && trim($this->config['memcache_port'])!='') {
					$client->setClassConfig('Google_Cache_Memcache', 'port', $this->config['memcache_port']);
				} else {
					$client->setClassConfig('Google_Cache_Memcache', 'port', 11211);
				}
				require_once 'ease/lib/Google/Cache/Memcache.php';
				$cache = new Google_Cache_Memcache($client);
				$client->setCache($cache);
			}
			try {
				$auth_url = $client->createAuthUrl();
			} catch(Google_Auth_Exception $e) {
				if(preg_match('/^(.*?), message: \'(.*?)\'$/is', $e->getMessage(), $matches)) {
					echo "<h1>$matches[1]</h1><p>\n";
					$message = json_decode($matches[2]);
					foreach($message as $key=>$value) {
						echo "<b>$key</b>: $value<br />\n";
						if($key=='error') {
							if($value=='invalid_client') {
								// the client was invalid, so wipe the settings in the memcache and database as they are stale.
								// settings will default to those set in easeConfig.json
								if($this->memcache) {
									$this->memcache->delete('system.gapp_client_id');
									$this->memcache->delete('system.gapp_client_secret');
								}
								if($this->db) {
									$query = $this->db->prepare("DELETE FROM ease_config WHERE name=:name;");
									$query->execute(array(':name'=>'gapp_client_id'));
									$query->execute(array(':name'=>'gapp_client_secret'));
								}
							} elseif($value=='unauthorized_client' || $value=='invalid_grant') {
								// the Google API Access Tokens were not for this client, so wipe the settings in the memcache and database
								// the user will be prompted to grant permissions again to link the app to a google account
								$this->flush_google_access_tokens();
							}
						}
					}
				} else {
					echo $e->getMessage();
				}
				exit;
			}
			if(isset($this->config['google_token_message']) && trim($this->config['google_token_message'])!='') {
				echo $this->config['google_token_message'];
				if(isset($this->config['google_token_button_template']) 
				  && trim($this->config['google_token_button_template'])!=''
				  && stripos($this->config['google_token_button_template'], '[google_oauth_url]')!==false) {
					echo str_ireplace('[google_oauth_url]', $auth_url, $this->config['google_token_button_template']);
				} else {
					echo "<button onclick='window.location=\"$auth_url\"' style='margin:0px; padding:3px 6px 4px 6px; vertical-align:bottom;'>Next&nbsp;&nbsp;&gt;&gt;</button>";
				}
				if(isset($this->config['google_token_footer'])) {
					echo $this->config['google_token_footer'];
				}
			} else {
				echo "<html><body><div align='center' style='font-family:Arial, Helvetica, sans-serif;'>";
				echo "<h1 style='margin:0px; padding:0px; margin-bottom:12px;'>Google Setup</h1>";
				echo "<div style='margin:0px; padding:0px; margin-bottom:10px;'>To use Google Services in this EASE Web App, you need to grant permissions.</div>";
				echo "<div style='margin:0px; padding:0px;'>Click on the next button to redirect to Google to continue. ";
				echo "<button onclick='window.location=\"$auth_url\"' style='margin:0px; padding:3px 6px 4px 6px; vertical-align:bottom;'>Next&nbsp;&nbsp;&gt;&gt;</button>";
				echo "</div>";
				echo "</div></body></html>";
			}
			exit;
		}
		// check for an expired Google API Access Token with a Refresh Token
		if(@$this->config['gapp_refresh_token'] && ((!@$this->config['gapp_access_token_json']) || $_SERVER['REQUEST_TIME']>=@$this->config['gapp_expire_time'])) {
			// Google API Access Token is expired... refresh it
			$client = new Google_Client();
			$client->setClientId($this->config['gapp_client_id']);
			$client->setClientSecret($this->config['gapp_client_secret']);
			$client->setRedirectUri((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $this->service_endpoints['google_oauth2callback']);
			$client->setScopes($this->config['gapp_scopes']);
			$client->setAccessType('offline');
			$client->setApprovalPrompt('force');
			if(isset($this->config['elasticache_config_endpoint']) && trim($this->config['elasticache_config_endpoint'])!='') {
				// the Google APIs default use of memcache is not supported while using the AWS ElastiCache Cluster Client
				require_once 'ease/lib/Google/Cache/Null.php';
				$cache = new Google_Cache_Null($client);
				$client->setCache($cache);
			} elseif(isset($this->config['memcache_host']) && trim($this->config['memcache_host'])!='') {
				// an external memcache host was configured, pass on that configuration to the Google API Client
				$client->setClassConfig('Google_Cache_Memcache', 'host', $this->config['memcache_host']);
				if(isset($this->config['memcache_port']) && trim($this->config['memcache_port'])!='') {
					$client->setClassConfig('Google_Cache_Memcache', 'port', $this->config['memcache_port']);
				} else {
					$client->setClassConfig('Google_Cache_Memcache', 'port', 11211);
				}
				require_once 'ease/lib/Google/Cache/Memcache.php';
				$cache = new Google_Cache_Memcache($client);
				$client->setCache($cache);
			}
			try {
				$client->refreshToken($this->config['gapp_refresh_token']);
			} catch(Google_Auth_Exception $e) {
				// there was an error refreshing the Google API Access Token
				if(preg_match('/^(.*?), message: \'(.*?)\'$/is', $e->getMessage(), $matches)) {
					echo "<h1>$matches[1]</h1><p>\n";
					$message = json_decode($matches[2]);
					foreach($message as $key=>$value) {
						echo "<b>$key</b>: $value<br />\n";
						if($key=='error') {
							if($value=='invalid_client') {
								// the client was invalid, so wipe the settings in the memcache and database
								// settings will default to those set in easeConfig.json
								if($this->memcache) {
									$this->memcache->delete('system.gapp_client_id');
									$this->memcache->delete('system.gapp_client_secret');
								}
								if($this->db) {
									$query = $this->db->prepare("DELETE FROM ease_config WHERE name=:name;");
									$query->execute(array(':name'=>'gapp_client_id'));
									$query->execute(array(':name'=>'gapp_client_secret'));
								}
							} elseif($value=='unauthorized_client' || $value=='invalid_grant') {
								// the Google API Access Tokens were not for this client, so wipe the settings in the memcache and database
								// the user will be prompted to grant permissions again to link a google account
								$this->flush_google_access_tokens();
							}
						}
					}
				} else {
					echo $e->getMessage();
				}
				exit;
			}
			$gapp_access_token_json = $client->getAccessToken();
			$access_token = json_decode($gapp_access_token_json);
			if($access_token==null) {
				echo 'Invalid Access Token received while refreshing';
				exit;
			}
			// store the refreshed Google API Access Token in the memcache and database
			$gapp_access_token = $access_token->access_token;
			$gapp_expire_time = $_SERVER['REQUEST_TIME'] + $access_token->expires_in;
			$this->set_system_config_var('gapp_access_token_json', $gapp_access_token_json);
			$this->set_system_config_var('gapp_access_token', $gapp_access_token);
			$this->set_system_config_var('gapp_expire_time', $gapp_expire_time);
		}
	}

	function set_gapp_client_id_and_secret($gapp_client_id, $gapp_client_secret, $flush_google_access_tokens=false) {
		$this->set_system_config_var('gapp_client_id', $gapp_client_id);
		$this->set_system_config_var('gapp_client_secret', $gapp_client_secret);
		if($flush_google_access_tokens) {
			$this->flush_google_access_tokens();
		}
	}

	function flush_google_access_tokens() {
		if($this->memcache) {
			$this->memcache->delete('system.gapp_access_token_json');
			$this->memcache->delete('system.gapp_access_token');
			$this->memcache->delete('system.gapp_expire_time');
			$this->memcache->delete('system.gapp_refresh_token');
			$this->memcache->delete('system.gapp_scopes');
		}
		if($this->db) {
			$query = $this->db->prepare("DELETE FROM ease_config WHERE name=:name AND namespace=:namespace;");
			if(!$query->execute(array(':name'=>'gapp_access_token_json', ':namespace'=>$this->namespace))) {
				// the query failed... attempt to add the namespace column then try again
				$this->db->exec("ALTER TABLE ease_config ADD COLUMN namespace TEXT NOT NULL DEFAULT '';");
				$query->execute(array(':name'=>'gapp_access_token_json', ':namespace'=>$this->namespace));
			}
			$query->execute(array(':name'=>'gapp_access_token', ':namespace'=>$this->namespace));
			$query->execute(array(':name'=>'gapp_expire_time', ':namespace'=>$this->namespace));
			$query->execute(array(':name'=>'gapp_refresh_token', ':namespace'=>$this->namespace));
			$query->execute(array(':name'=>'gapp_scopes', ':namespace'=>$this->namespace));
		}
	}

	function set_global_system_vars() {
		$this->globals['system.core'] = 'PHP';
		$this->globals['system.session_id'] = session_id();
		$this->globals['system.domain'] = &$_SERVER['SERVER_NAME'];
		$this->globals['system.host'] = &$_SERVER['HTTP_HOST'];
		$this->globals['system.http_host'] = 'http' . ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS'])=='on') ? 's' : '') . '://' . $_SERVER['HTTP_HOST'];
		$this->globals['system.https_host'] = 'http' . (($_SERVER['SERVER_NAME']!='localhost') ? 's' : '') . '://' . $_SERVER['HTTP_HOST'];
		$this->globals['system.request'] = &$_SERVER['REQUEST_URI'];
		// set referring page variables
		if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']!='') {
			$referrer_parts = parse_url($_SERVER['HTTP_REFERER']);
			if(strpos($_SERVER['HTTP_REFERER'], '?')!==false) {
				// the referring URL includes a "?" character separator before the query string... no need to add one
				$this->globals['system.referrer'] = &$_SERVER['HTTP_REFERER'];
			} else {
				// the referring URL did not include a "?" character
				// append a "?" character to the end of the URL so adding variables can always be done using a "&" character
				$this->globals['system.referrer'] = $_SERVER['HTTP_REFERER'] . '?';
			}
			if(isset($referrer_parts['query'])) {
				$this->globals['system.referring_query'] = $referrer_parts['query'];
			} else {
				$this->globals['system.referring_query'] = '';
			}
			$this->globals['system.referring_scheme'] = strtolower($referrer_parts['scheme']);
			$this->globals['system.referring_host'] = $referrer_parts['host'] . ((isset($referrer_parts['port']) && $referrer_parts['port']!='') ? ':' . $referrer_parts['port'] : '');
			$this->globals['system.referring_host_url'] = $referrer_parts['scheme'] . '://' . $this->globals['system.referring_host'];
			$this->globals['system.referring_page_url'] = $referrer_parts['scheme'] . '://' . $this->globals['system.referring_host'] . $referrer_parts['path'];
			$this->globals['system.https_referring_host'] = 'http' . (($referrer_parts['host']!='localhost') ? 's' : '') . '://' . $this->globals['system.referring_host'];
			$this->globals['system.https_referring_page'] = 'http' . (($referrer_parts['host']!='localhost') ? 's' : '') . '://' . $this->globals['system.referring_host'] . $referrer_parts['path'];
		} else {
			$this->globals['system.referrer'] = '';
			$this->globals['system.referring_query'] = '';
			$this->globals['system.referring_scheme'] = '';
			$this->globals['system.referring_host'] = '';
			$this->globals['system.referring_host_url'] = '';
			$this->globals['system.referring_page_url'] = '';
			$this->globals['system.https_referring_host'] = '';
			$this->globals['system.https_referring_page'] = '';
		}
		// set current time variables
		$this->globals['system.timestamp'] = $_SERVER['REQUEST_TIME'];
		if(isset($_SERVER['REQUEST_TIME_FLOAT'])) {
			$this->globals['system.timestamp_float'] = $_SERVER['REQUEST_TIME_FLOAT'];
		} else {
			// a floating point version of the current timestamp was not available
			// alias the integer timestamp as the float value
			$this->globals['system.timestamp_float'] = &$this->globals['system.timestamp'];
		}
		$this->globals['system.time'] = date('g:i:s A T', $_SERVER['REQUEST_TIME']);
		$this->globals['system.time_short'] = date('H:i:s', $_SERVER['REQUEST_TIME']);
		$this->globals['system.date'] = date('Y-m-d', $_SERVER['REQUEST_TIME']);
		$this->globals['system.date_time'] = date('Y-m-d g:i:s A T', $_SERVER['REQUEST_TIME']);
		$this->globals['system.date_time_short'] = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
		$this->globals['system.day'] = date('d', $_SERVER['REQUEST_TIME']);
		$this->globals['system.month'] = date('m', $_SERVER['REQUEST_TIME']);
		$this->globals['system.month_name'] = date('F', $_SERVER['REQUEST_TIME']);
		$this->globals['system.month_name_short'] = date('M', $_SERVER['REQUEST_TIME']);
		$this->globals['system.day_name'] = date('l', $_SERVER['REQUEST_TIME']);
		$this->globals['system.day_name_short'] = date('D', $_SERVER['REQUEST_TIME']);
		$this->globals['system.year'] = date('Y', $_SERVER['REQUEST_TIME']);
		// set aliases for legacy support
		$this->globals['system.name'] = &$this->globals['system.domain'];
		$this->globals['system.host_url'] = &$this->globals['system.http_host'];
		$this->globals['system.secure_host_url'] = &$this->globals['system.https_host'];
		$this->globals['system.day_short'] = &$this->globals['system.day_name_short'];
		$this->globals['system.month_short'] = &$this->globals['system.month_name_short'];
		$this->globals['system.timestamp_long'] = &$this->globals['system.timestamp_float'];
		$this->globals['system.microtime'] = &$this->globals['system.timestamp_float'];
		$this->globals['system.http_referring_host'] = &$this->globals['system.referring_host_url'];
		$this->globals['system.http_referring_page'] = &$this->globals['system.referring_page_url'];
		$this->globals['system.secure_referring_host_url'] = &$this->globals['system.https_referring_host'];
		$this->globals['system.secure_referring_page_url'] = &$this->globals['system.https_referring_page'];
	}

	function file_get_contents_utf8($filepath) {
		$content = file_get_contents($filepath);
		return mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
	}

	function new_uuid() {
		return bin2hex(openssl_random_pseudo_bytes(16));
	}

	function html_dump($object, $label=null) {
		if(is_array($object) || is_object($object)) {
			$object = print_r($object, true);
		}
		echo '<pre style="tab-size:4;">';
		if($label!==null) {
			echo "<b>$label:</b> ";
		}
		echo htmlspecialchars($object);
		echo '</pre>';
	}

	function remove_duplicate_url_params(&$url) {
		$url_parts = parse_url($url);
		if(isset($url_parts['query']) && trim($url_parts['query'])!='') {
			// there was a query string, cleanse all duplicate URL params
			$url_query_parts = array();
			parse_str($url_parts['query'], $url_query_parts);
			$url_parts['query'] = http_build_query($url_query_parts);
			$url = isset($url_parts['scheme']) ? $url_parts['scheme'] . '://' : '';
			if(isset($url_parts['user']) || isset($url_parts['pass'])) {
				$url .= isset($url_parts['user']) ? $url_parts['user'] : '';
				$url .= isset($url_parts['pass']) ? ':' . $url_parts['pass'] : '';
				$url .= '@';
			}
			$url .= isset($url_parts['host']) ? $url_parts['host'] : '';
			$url .= isset($url_parts['port']) ? ':' . $url_parts['port'] : '';
			$url .= isset($url_parts['path']) ? $url_parts['path'] : '';
			$url .= isset($url_parts['query']) ? '?' . $url_parts['query'] : '';
			$url .= isset($url_parts['fragment']) ? '#' . $url_parts['fragment'] : '';
		}
	}

	function send_email($mail_options) {
		if(isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Google App Engine')!==false) {
			// this app is hosted on google app engine, use the Google Mail API to deliver the message
			try {
				// load the Google Mail Service API and attempt to deliver the message
				require_once 'google/appengine/api/mail/Message.php';
				$message = new google\appengine\api\mail\Message($mail_options);
				return $message->send();
			} catch(InvalidArgumentException $e) {
				// error... probably invalid recipient address
				// TODO!! log this error
				return false;
			} catch(google\appengine\runtime\OverQuotaError $e) {
				// over Google Mail Service quota, email the site admins and include a copy of the original message and recipients
				// TODO!! log this error
				$mail_options['subject'] = 'EMAIL QUOTA ALERT! ' . $mail_options['subject'];
				if(isset($mail_options['htmlBody'])) {
					$mail_options['htmlBody'] = "<br />============= ORIGINAL MESSAGE =============<br /><br />" . $mail_options['htmlBody'];
					if(@$mail_options['bcc']) {
						$mail_options['htmlBody'] = 'BCC: ' . $mail_options['bcc'] . "<br />" . $mail_options['htmlBody'];
						unset($mail_options['bcc']);
					}
					if(@$mail_options['cc']) {
						$mail_options['htmlBody'] = 'CC: ' . $mail_options['cc'] . "<br />" . $mail_options['htmlBody'];
						unset($mail_options['cc']);
					}
					if(@$mail_options['to']) {
						$mail_options['htmlBody'] = 'To: ' . $mail_options['to'] . "<br />" . $mail_options['htmlBody'];
						unset($mail_options['to']);
					}
					$mail_options['htmlBody'] = "Your Google App Engine Instance at {$_SERVER['HTTP_HOST']} has reached its email quota.<p>Click on the Application in <a href='https://appengine.google.com/'>the App Engine console</a>, then enable billing to increase your email quota.<p>Please forward this email to the intended recipients:<br />" . $mail_options['htmlBody'];
				} elseif(isset($mail_options['textBody'])) {
					$mail_options['textBody'] = "\n============= ORIGINAL MESSAGE =============\n\n" . $mail_options['textBody'];
					if(@$mail_options['bcc']) {
						$mail_options['textBody'] = 'BCC: ' . $mail_options['bcc'] . "\n" . $mail_options['textBody'];
						unset($mail_options['bcc']);
					}
					if(@$mail_options['cc']) {
						$mail_options['textBody'] = 'CC: ' . $mail_options['cc'] . "\n" . $mail_options['textBody'];
						unset($mail_options['cc']);
					}
					if(@$mail_options['to']) {
						$mail_options['textBody'] = 'To: ' . $mail_options['to'] . "\n" . $mail_options['textBody'];
						unset($mail_options['to']);
					}
					$mail_options['textBody'] = "Your Google App Engine Instance at {$_SERVER['HTTP_HOST']} has reached the email quota.\n\nClick on the Application in the App Engine console at https://appengine.google.com, then enable billing to increase your email quota.\n\nPlease forward this email to the intended recipients:\n" . $mail_options['textBody'];
				}
				require_once 'google/appengine/api/mail/AdminMessage.php';
				try {
					$message = new google\appengine\api\mail\AdminMessage($mail_options);
					return $message->send();
				} catch(Exception $e) {
					// error... over quota AND over admin quota
					// TODO!! log this error
					return false;
				}
			}
		} elseif(isset($this->config['ses_smtp_host']) && isset($this->config['ses_smtp_username']) && isset($this->config['ses_smtp_password'])
		  && trim($this->config['ses_smtp_host'])!='' && trim($this->config['ses_smtp_username'])!='' && trim($this->config['ses_smtp_password'])!='') {
			// AWS Simple Email Service was configured, use it to deliver the message
			// the PEAR Mail package is required, which depends on the PEAR Net_SMTP package
			require_once 'Mail.php';
			$headers = array();
			if(isset($mail_options['from']) && trim($mail_options['from'])!='') {
				$headers['From'] = $mail_options['from'];
			} elseif(isset($mail_options['sender']) && trim($mail_options['sender'])!='' && isset($this->config['email_from']) && trim($this->config['email_from'])!='') {
				$headers['From'] = '"' . $mail_options['sender'] . '" <' . $this->config['email_from'] . '>';
			} elseif(isset($mail_options['sender']) && trim($mail_options['sender'])!='') {
				$headers['From'] = $mail_options['sender'];
			} elseif(isset($this->config['email_from']) && trim($this->config['email_from'])!='') {
				$headers['From'] = $this->config['email_from'];
			}
			$headers['Subject'] = $mail_options['subject'];
			if(isset($mail_options['cc']) && trim($mail_options['cc'])!='') {
				$headers['Cc'] = $mail_options['cc'];
			}
			if(isset($mail_options['bcc']) && trim($mail_options['bcc'])!='') {
				$headers['Bcc'] = $mail_options['bcc'];
			}
			if(isset($mail_options['htmlBody']) && trim($mail_options['htmlBody'])!='') {
				$body = $mail_options['htmlBody'];
				$headers['Content-Type'] = 'text/html; charset="UTF-8"';
				$headers['Content-Transfer-Encoding'] = '8bit';
			} elseif(isset($mail_options['textBody']) && trim($mail_options['textBody'])!='') {
				$body = $mail_options['textBody'];
				$headers['Content-Type'] = 'text/plain; charset="UTF-8"';
				$headers['Content-Transfer-Encoding'] = '8bit';
			} else {
				$body = '';
			}
			$smtp = Mail::factory('smtp', array(
				'host'=>$this->config['ses_smtp_host'],
				'port'=>(isset($this->config['ses_smtp_port']) && trim($this->config['ses_smtp_port'])!='') ? $this->config['ses_smtp_port'] : 25,
				'auth'=>true,
				'username'=>$this->config['ses_smtp_username'],
				'password'=>$this->config['ses_smtp_password']
			));
			$mail = $smtp->send($mail_options['to'], $headers, $body);
			if(PEAR::isError($mail)) {
				// echo '<p>' . htmlspecialchars($mail->getMessage()) . '</p>';
				// TODO!! log this error
				return false;
			} else {
				return $mail;
			}
		} else {
			// no email service was configured... use the PHP mail function to deliver the mail.
			$headers = array();
			if(isset($mail_options['from']) && trim($mail_options['from'])!='') {
				$headers['From'] = $mail_options['from'];
			} elseif(isset($mail_options['sender']) && trim($mail_options['sender'])!='' && isset($this->config['email_from']) && trim($this->config['email_from'])!='') {
				$headers['From'] = '"' . $mail_options['sender'] . '" <' . $this->config['email_from'] . '>';
			} elseif(isset($mail_options['sender']) && trim($mail_options['sender'])!='') {
				$headers['From'] = $mail_options['sender'];
			} elseif(isset($this->config['email_from']) && trim($this->config['email_from'])!='') {
				$headers['From'] = $this->config['email_from'];
			}
			if(isset($mail_options['cc']) && trim($mail_options['cc'])!='') {
				$headers['Cc'] = $mail_options['cc'];
			}
			if(isset($mail_options['bcc']) && trim($mail_options['bcc'])!='') {
				$headers['Bcc'] = $mail_options['bcc'];
			}
			if(isset($mail_options['htmlBody']) && trim($mail_options['htmlBody'])!='') {
				$body = $mail_options['htmlBody'];
				$headers['Content-Type'] = 'text/html; charset="UTF-8"';
				$headers['Content-Transfer-Encoding'] = '8bit';
			} elseif(isset($mail_options['textBody']) && trim($mail_options['textBody'])!='') {
				$body = $mail_options['textBody'];
				$headers['Content-Type'] = 'text/plain; charset="UTF-8"';
				$headers['Content-Transfer-Encoding'] = '8bit';
			} else {
				$body = '';
			}
			$headers_string = '';
			foreach($headers as $key=>$value) {
				if($headers_string!='') {
					$headers_string .= "\r\n";
				}
				$headers_string .= "$key: $value";
			}
			return mail($mail_options['to'], $mail_options['subject'], $body, $headers_string);
		}
	}

	function send_gmail($params) {
		// initialize a Google API client
		$this->validate_google_access_token();
		require_once 'ease/lib/Google/Client.php';
		$client = new Google_Client();
		$client->setClientId($this->config['gapp_client_id']);
		$client->setClientSecret($this->config['gapp_client_secret']);
		$client->setScopes($this->config['gapp_scopes']);
		$client->setAccessToken($this->config['gapp_access_token_json']);
		if(isset($this->config['elasticache_config_endpoint']) && trim($this->config['elasticache_config_endpoint'])!='') {
			// the Google APIs default use of memcache is not supported while using the AWS ElastiCache Cluster Client
			require_once 'ease/lib/Google/Cache/Null.php';
			$cache = new Google_Cache_Null($client);
			$client->setCache($cache);
		} elseif(isset($this->config['memcache_host']) && trim($this->config['memcache_host'])!='') {
			// an external memcache host was configured, pass on that configuration to the Google API Client
			$client->setClassConfig('Google_Cache_Memcache', 'host', $this->config['memcache_host']);
			if(isset($this->config['memcache_port']) && trim($this->config['memcache_port'])!='') {
				$client->setClassConfig('Google_Cache_Memcache', 'port', $this->config['memcache_port']);
			} else {
				$client->setClassConfig('Google_Cache_Memcache', 'port', 11211);
			}
			require_once 'ease/lib/Google/Cache/Memcache.php';
			$cache = new Google_Cache_Memcache($client);
			$client->setCache($cache);
		}
		require_once 'ease/lib/Google/Service/Gmail.php';
		$service = new Google_Service_Gmail($client);
		$message = new Google_Service_Gmail_Message();
		// build MIME email message
		$mime_email = "To: {$params['to']}\r\n";
		if(isset($params['cc']) && trim($params['cc'])!='') {
			$mime_email .= "Cc: {$params['cc']}\r\n";
		}
		if(isset($params['bcc']) && trim($params['bcc'])!='') {
			$mime_email .= "Bcc: {$params['bcc']}\r\n";
		}
		if(isset($params['subject']) && trim($params['subject'])!='') {
			$mime_email .= "Subject: {$params['subject']}\r\n";
		}
		$mime_email .= "MIME-Version: 1.0\r\n";
		if(isset($params['htmlBody']) && trim($params['htmlBody'])!='') {
			$mime_email .= "Content-Type: text/html; charset=UTF-8\r\n";
			$mime_email .= "\r\n{$params['htmlBody']}";
		} elseif(isset($params['textBody']) && trim($params['textBody'])!='') {
			$mime_email .= "Content-Type: text/plain; charset=UTF-8\r\n";
			$mime_email .= "\r\n{$params['textBody']}";
		} else {
			// no message body
			$mime_email .= "\r\n\r\n";
		}
        $base64_mime_email = rtrim(strtr(base64_encode($mime_email), '+/', '-_'), '=');
		$message->setRaw($base64_mime_email);
		try {
			return $service->users_messages->send('me', $message);
		} catch(Exception $e) {
			// an error occurred
			echo $e->getMessage();
		}
	}

}
