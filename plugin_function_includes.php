<?php
     /**
     *  Loads the EASE Core Framework
     *
     *  @author Lucas Simmons
     *
     *  @since 0.1
     *
     *  return object the EASE Core object
     */   
    function ease_load_core(){
            $plugin_directory = getcwd() . DIRECTORY_SEPARATOR . "wp-content" . DIRECTORY_SEPARATOR . "plugins" . DIRECTORY_SEPARATOR . "ease";
            $plugin_directory = str_replace("wp-admin" . DIRECTORY_SEPARATOR,"",$plugin_directory);
            require_once('ease/core.class.php');
            
            $db_name = get_option('ease_db_name');
            $db_hostname = get_option('ease_external_db_hostname');
            $db_user = get_option('ease_external_db_user');
            $db_password = get_option('ease_external_db_password');
            
            if(!$db_name){
                $db_name = DB_NAME;
            }
  
            if(!$db_hostname){
                $db_hostname = DB_HOST;
            }
            
            if(!$db_user){
                $db_user = DB_USER;
            }
            
            if(!$db_password){
                $db_password = DB_PASSWORD;
            }
            
            $params = array();
            
            if(isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Google App Engine')!==false) {
                $params['application_root'] = $plugin_directory;
            }else{
                
                $endpoint_page_id = get_option('ease_service_endpoint_page');
                if((get_option('ease_service_endpoint_page') === false) || (!get_option('ease_service_endpoint_page'))){
                    create_ease_endpoint();
                }
                
                $endpoint_url = get_permalink($endpoint_page_id);
                
                $endpoint_url = str_replace("http://" . $_SERVER['HTTP_HOST'],"",$endpoint_url);
                $endpoint_url = str_replace("https://" . $_SERVER['HTTP_HOST'],"",$endpoint_url);
                if(get_option('ease_disable_db_access') != "on"){
                    $params['database_host'] = "mysql:dbname=" . $db_name . ";host=" . $db_hostname . ";charset=utf8";
                    $params['database_username'] = $db_user;
                    $params['database_password'] = $db_password;
                }
                $params['suppress_503_headers'] = true;
                $params['web_basedir'] = "auto";
                $params['google_oauth2callback_service_endpoint'] = $endpoint_url . check_for_params($endpoint_url) . "endpoint=oauth2callback";
                $params['form_service_endpoint'] = $endpoint_url . check_for_params($endpoint_url) .  "endpoint=ease_form";
            }
            
            $ease_core = new ease_core($params);
            
            if(get_option('ease_google_drive_active') != ""){
                $ease_core->set_system_config_var('gapp_client_id',get_option('ease_gapp_client_id'));
                $ease_core->set_system_config_var('gapp_client_secret',get_option('ease_gapp_client_secret'));
            }
            
            if(isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Google App Engine')!==false) {

            }else{
                $s3_public = get_option('ease_s3_bucket_public');
                $s3_private = get_option('ease_s3_bucket_private');
                $s3_access_key = get_option('ease_s3_access_key');
                $s3_secret_access_key = get_option('ease_s3_secret_access_key');
                
                if($s3_access_key && $s3_secret_access_key && (get_option('ease_s3_active') != "")){
                    $ease_core->set_system_config_var('s3_access_key_id',$s3_access_key);
                    $ease_core->set_system_config_var('s3_secret_key',$s3_secret_access_key);
                    
                    if($s3_public){
                        $ease_core->set_system_config_var('s3_bucket_public',$s3_public);
                    }
                    
                    if($s3_private){
                        $ease_core->set_system_config_var('s3_bucket_private',$s3_private);
                    }
                }else{
                    if(get_option('ease_local_upload_active') != ""){
                        $public_folder = get_option('ease_public_folder_upload_directory');
                        $private_folder = get_option('ease_private_folder_upload_directory');
                        
                        if($private_folder){
                            if(is_dir($private_folder)){
                                $ease_core->set_system_config_var('private_upload_dir',$private_folder);
                            }
                        }
                        
                        if($public_folder){
                            if(is_dir($public_folder)){
                                $ease_core->set_system_config_var('public_upload_dir',$public_folder);
                            }
                        }
                    }
                }
            }
            return $ease_core;
    }
    
     /**
     *  Loads the EASE Script Helper, that allows the user to insert pre-built scripts into their Wordpress pages
     *
     *  @author Lucas Simmons
     *
     *  @since 0.1
     *
     *
     */ 
    function ease_script_helper() {	
            echo '<a onclick="loadScriptHelper(this.value)" class="button">EASE Script Helper</a>';
    }
    
     /**
     *  Loads the Drive importer script
     *
     *  @author Lucas Simmons
     *
     *  @since 0.1
     *
     */ 
    function adding_custom_ease_meta_box_pullfromdrive( $post_type, $post ) {
        $screens = array( 'post', 'page' );
        
        foreach ( $screens as $screen ) {
            add_meta_box( 
                'my-meta-box-pullfromdrive',
                __( 'EASE - Pull Content from a Google Document' ),
                'render_my_ease_meta_box_pullfromdrive',
                $screen,
                'side',
                'default'
            );
        }
    }
    
     /**
     *  Checks to see if query has a question mark in it already or not
     *
     *  @author Lucas Simmons
     *
     *  @since 0.1
     *
     */ 
    function check_for_params($url){
       $path_query = parse_url($url);
        
        if(isset($path_query['query'])){
            return "&";
        }else{
            return "?";
        }
    }
     /**
     *  Drive importer script
     *
     *  @author Lucas Simmons
     *
     *  @since 0.1
     *
     *
     */ 
    function render_my_ease_meta_box_pullfromdrive($post){
            $ease_core = ease_load_core();
        
            $client_id = get_option('ease_gapp_client_id');
            
            if(!$client_id){
                $client_id = $ease_core->load_system_config_var('gapp_client_id');
            }
            
        echo '
            
        <script type="text/javascript">
              var CLIENT_ID = "' . $client_id. '";
              var SCOPES = "https://www.googleapis.com/auth/drive";
        
              /**
               * Called when the client library is loaded to start the auth flow.
               */
              function handleClientLoad() {
                window.setTimeout(checkAuth, 1);
              }
        
              /**
               * Check if the current user has authorized the application.
               */
              function checkAuth() {
                gapi.auth.authorize(
                    {"client_id": CLIENT_ID, "scope": SCOPES, "immediate": true},
                    handleAuthResult);
              }
        
              /**
               * Called when authorization server replies.
               *
               * @param {Object} authResult Authorization result.
               */
              function handleAuthResult(authResult) {
                var authButton = document.getElementById("authorizeButton");
                authButton.style.display = "none";
                if (authResult && !authResult.error) {
                } else {
                  // No access token could be retrieved, show the button to start the authorization flow.
                  authButton.style.display = "block";
                  authButton.onclick = function() {
                      gapi.auth.authorize(
                          {"client_id": CLIENT_ID, "scope": SCOPES, "immediate": false},
                          handleAuthResult);
                  };
                }
              }
        
              /**
               * Start the file upload.
               *
               * @param {Object} evt Arguments from the file selector.
               */
              function uploadFile(evt) {
                gapi.client.load("drive", "v2", function() {
                  var file = evt.target.files[0];
                  insertFile(file);
                });
              }
        
              /**
               * Insert new file.
               *
               * @param {File} fileData File object to read data from.
               * @param {Function} callback Function to call when the request is complete.
               */
              function insertFile(fileData, callback) {
                const boundary = "-------314159265358979323846";
                const delimiter = "\r\n--" + boundary + "\r\n";
                const close_delim = "\r\n--" + boundary + "--";
        
                var reader = new FileReader();
                reader.readAsBinaryString(fileData);
                reader.onload = function(e) {
                  var contentType = fileData.type || "application/octet-stream";
                  var metadata = {
                    "title": fileData.name,
                    "mimeType": contentType
                  };
        
                  var base64Data = btoa(reader.result);
                  var multipartRequestBody =
                      delimiter +
                      "Content-Type: application/json\r\n\r\n" +
                      JSON.stringify(metadata) +
                      delimiter +
                      "Content-Type: " + contentType + "\r\n" +
                      "Content-Transfer-Encoding: base64\r\n" +
                      "\r\n" +
                      base64Data +
                      close_delim;
        
                  var request = gapi.client.request({
                      "path": "/upload/drive/v2/files",
                      "method": "POST",
                      "params": {"uploadType": "multipart"},
                      "headers": {
                        "Content-Type": \'multipart/mixed; boundary="\' + boundary + \'"\'
                      },
                      "body": multipartRequestBody});
                  if (!callback) {
                    callback = function(file) {
                      console.log(file)
                    };
                  }
                  request.execute(callback);
                }
              }
              
                function makeApiCall() {
                        //downloadFile();
                        gapi.client.load("drive", "v2", downloadFile);   
                }
        
                function makeRequest()
                {
                                var googledocid = document.getElementById("google_doc_id").value;
                                alert(googledocid);
                var statusDiv = document.getElementById("status");
                statusDiv.style.display = "";
                statusDiv.innerHTML = "Retrieving Document Information";
                        var request = gapi.client.request({
                               path : "/drive/v2/files/" + googledocid,
                               method : "GET",
                               params : {
                                    projection: "FULL"
                               }
                          });
        
                        request.execute(function(resp) { 
                         //console.log(resp);
                 
                var test = downloadFile(resp, output);
                        
                        });    
                }
                
                /**
                 * Download a files content.
                 *
                 * @param {File} file Drive File instance.
                 * @param {Function} callback Function to call when the request is complete.
                 */
                function downloadFile() {
                        jQuery("#import_doc_status").html("<br>Importing...");
                        file_id = document.getElementById("google_doc_id").value;
                var statusDiv = document.getElementById("status");
                            statusDiv.innerHTML = "Downloading document...";
                    var accessToken = gapi.auth.getToken().access_token;
                            
                            var export_url;
                           // console.log(file_id);
         var request = gapi.client.drive.files.get({
              "fileId": file_id
          });
          request.execute(function(resp) {
          //console.log(resp);
            if (!resp.error) {
            export_url = resp.exportLinks["text/html"];
              jQuery("#title").val(resp.title);
              
          if (export_url) {
            var accessToken = gapi.auth.getToken().access_token;
              export_url = export_url + "&access_token=" + accessToken;
          //console.log(export_url);
           jQuery.get( export_url, function( data ) {
          jQuery("#content").val(data);
          jQuery("#import_doc_status").html("");
          //alert( "Load was performed." );
        });
            } else {
            jQuery("#import_doc_status").html("<br>Error: " + resp.error.message);
            //  console.log("Error code: " + resp.error.code);
            //  console.log("Error message: " + resp.error.message);
              // More error information can be retrieved with resp.error.errors.
            }
            }
          });
	}
	
	function output(oText){
               // console.log(oText);
	// document.getElementById("content").innerHTML = oText;
	}
    </script>

    <script type="text/javascript" src="https://apis.google.com/js/client.js?onload=handleClientLoad"></script>
  
    <!--Add a file picker for the user to start the upload process -->
    <div id="status" style="display:none"></div>
    Enter Google Doc ID: <br>
    <input type="text" id="google_doc_id" name="google_doc_id">
    <input type="button" onclick="makeApiCall();" value="Import doc">
    <input type="button" id="authorizeButton" style="" value="Authorize" />
    <span id="import_doc_status"></span>
    ';
    
    }
    
     /**
     *  Calls the function that creates the endpoint page that handles all EASE calls and displays a message
     *
     *  @author Lucas Simmons
     *
     *  @since 0.1
     *
     */ 
    function create_ease_endpoint_page(){
        create_ease_endpoint();
        echo "<h3>Success</h3>Your EASE endpoint page has been created.  You will need to change your settings in your Google Project to match the updated urls";
    }
    
     /**
     *  Creates the endpoint page that handles all EASE calls
     *
     *  @author Lucas Simmons
     *
     *  @since 0.1
     *
     */ 
    function create_ease_endpoint(){
        $_p['post_content'] = " ";
        $_p['post_status'] = 'publish';
        $_p['post_type'] = 'page';
        $_p['post_title'] = 'EASE Endpoint Handler';
        $_p['comment_status'] = 'closed';
        $_p['ping_status'] = 'closed';
        $_p['post_category'] = array(1); // the default 'Uncatrgorised'
        $the_page_id = wp_insert_post( $_p );
        update_option( 'ease_service_endpoint_page', $the_page_id);
    }
?>