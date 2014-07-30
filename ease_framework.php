<?php
    /**
     * Plugin Name: EASE Framework
     * Plugin URI: http://www.cloudward.com
     * Description: A plugin that makes it easy to generate forms and lists using EASE syntax and Google Spreadsheets
     * Version: 0.1.0
     * Author: Cloudward
     * Author URI: http://www.cloudward.com
     * License: GPLv2 or later
     */
     
    $replace_urls = array();
    $no_show_urls = array();
    $temp_url_array = array();
    global $plugin_updates;
    global $theme_updates;
    
    global $run_ease_filter;
    
    define( 'EASE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
    set_include_path(plugin_dir_path( __FILE__ ) . ":" . plugin_dir_path( __FILE__ ) . "ease" . DIRECTORY_SEPARATOR . "lib");
    
    $plugin_dir = plugin_dir_path( __FILE__ );
    $request_path_parts = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    if(!is_admin()){
        global $post;
        
        // this tries to replace curly quotes with the quote HTML, but it breaks things in EASE
        remove_filter( "the_content", "wptexturize");
        // removes auto addition of <p> and <br> tags to content which was causing issues w/EASE parsing
        remove_filter( 'the_content', 'wpautop' );
        
        add_filter( 'wp_page_menu_args', 'ease_page_menu_args' );
        // run the form/oauth actions before anything else
        add_action('init', 'ease_form_process');
        
        // run the content through the parser
        
        add_filter( 'the_content', 'ease_filter' );
        
        // readd the removed filters
        add_filter( 'the_content', 'wpautop' );
        
        add_filter( "the_content", "wptexturize");
    }else{
        $pagename = $_REQUEST['page'];
        $request_path_parts = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        add_action('admin_menu', 'ease_plugin_menu');
        add_action( 'admin_init', 'register_ease_setting' );
        add_action( 'admin_init', 'ease_admin_load_js_scripts' );
        add_filter('admin_footer_text', 'ease_remove_footer_admin');
        
        add_action( 'admin_footer', 'ease_disable_html_editor_wps' );
        
        add_filter( 'wp_default_editor', create_function('', 'return "html";') );
        
        
        add_filter('media_buttons_context', 'ease_script_helper');
        
        if(get_option('ease_gapp_client_id')){
          add_action( 'add_meta_boxes', 'adding_custom_ease_meta_box_pullfromdrive', 10, 2 );
        }
    }
    
    //

    /**
     * Queues javascripts
     * 
     * @author  Lucas Simmons 
     *
     * @since 0.1
     *
    */
    function ease_admin_load_js_scripts(){
        wp_enqueue_script('ease_my_script', plugins_url( 'script_helper.js' , __FILE__ ));
    }
    
    /**
     * Handles form actions
     * 
     * @author  Lucas Simmons 
     *
     * @since 0.1
     *
    */
    function ease_form_process(){ 
        ob_start();
        
        $service_endpoint = $_REQUEST['endpoint'];
        if($service_endpoint == "oauth2callback") {
            session_start();
            $ease_core = ease_load_core();
            $ease_core->handle_google_oauth2callback();
            exit;
        }elseif($_POST['ease_form_id'] && $service_endpoint == "ease_form"){
            session_start();
            $ease_core = ease_load_core();
            $ease_core->handle_form();
            
            if(strpos($_SERVER['HTTP_REFERER'],'/wp-admin/admin.php?')){
                exit;
            }
            
            // This causes the page to redirect
            exit;
        }
    }
    
    /**
     * Excludes endpoint url from being displayed in the menu
     * 
     * @author  Lucas Simmons 
     *
     * @since 0.1
     *
    */
    function ease_page_menu_args( $args ) {
        $args['exclude'] = get_option('ease_service_endpoint_page');
        
        return $args;
    }
    
    /**
     * Generates the content that shows under a post's edit page
     *
     * @author  Lucas Simmons 
     *
     * @since 0.1
     *
    */
     function ease_disable_html_editor_wps() {
        echo '<script type="text/javascript">jQuery(document).ready(function($) {';
        
        echo '
            if(jQuery("#content").length > 0){
                var editor_content = jQuery("#content").html();
                
                if(editor_content.indexOf("&lt;#") >=0){
                    jQuery("#content-tmce").remove();
                }
            }
        });
        
        loadScripts();
        
        
        jQuery( "#content-html" ).click(function() {
          jQuery("#script_helper").show();
        });
        
        jQuery( "#content-tmce" ).click(function() {
          jQuery("#script_helper").hide();
        }); 
        </script>';
        add_thickbox();
        echo '<span style="display:none;">
        <a href="#TB_inline?width=75%&height=75%&inlineId=script-helper-div" title="EASE Script Helper" id="script-helper-modal-link" class="thickbox"></a>
        </span>
        <div id="script-helper-div" style="display:none;">
            <i>Note: Once you add an EASE Script, the visual editor in Wordpress for the page will automatically be disabled</i><BR><BR><link rel="stylesheet" href="//code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css">
                  <script src="//code.jquery.com/jquery-1.10.2.js"></script>
                  <script src="//code.jquery.com/ui/1.11.0/jquery-ui.js"></script><script type="text/javascript">
          jQuery(function() {
          //  jQuery( "#accordion" ).accordion();
          });
    
          function toogleScriptHelper(helper_type){
              jQuery(".content-" + helper_type + "-one").slideToggle();
          }
           jQuery.fn.extend({
            insertAtCaret: function(myValue){
              return this.each(function(i) {
                if (document.selection) {
                  this.focus();
                  sel = document.selection.createRange();
                  sel.text = myValue;
                  this.focus();
                }
                else if (this.selectionStart || this.selectionStart == "0") {
                  var startPos = this.selectionStart;
                  var endPos = this.selectionEnd;
                  var scrollTop = this.scrollTop;
                  this.value = this.value.substring(0, startPos)+myValue+this.value.substring(endPos,this.value.length);
                  this.focus();
                  this.selectionStart = startPos + myValue.length;
                  this.selectionEnd = startPos + myValue.length;
                  this.scrollTop = scrollTop;
                } else {
                  this.value += myValue;
                  this.focus();
                }
              })
            }
            });
            
            function insertHelper(){
              insertHelperScript(jQuery("#modal_script_helper_textarea").val());
            }
            
            function loadScriptHelperText(script_name) {
                    if (script_name) {
                      jQuery("#modal_script_helper_textarea").val(script_object[script_name]);
                    }
            }</script>
        <style>
              /* Use the following CSS code if you want to have a class per icon */
              li { cursor: pointer; }
          </style>
          <div class="siteformsection" style=""> <a href="#TB_inline?width=600&height=550&inlineId=script-helper-div" title="EASE Script Helper" id="script-helper-modal-link" class="thickbox"></a><div class="expand-one" style="border-top-left-radius: 5px;border-top-right-radius: 5px;padding:10px 15px;border-width:1px;border-style:solid;border-color: rgb(221, 221, 221);background-color:#f5f5f5;margin:10px;padding-left:10px;margin-bottom:0px;"><a style="color:#000000;font-size:16px;cursor:pointer;"  onclick="toogleScriptHelper(1);">Surveys</a></div><div class="content-1-one" style="cursor: pointer;padding:10px 15px;border-width:1px;border-style:solid;border-color: rgb(221, 221, 221);display:none;margin-left:10px;margin-top:0px;padding-left:10px;margin-right:10px;border-top:0px;"><a style="cursor: pointer;" onclick="loadScriptHelperText(&quot;survey_helper&quot;);">Survey</a><BR></div><div class="expand-one" style="border-top-left-radius: 5px;border-top-right-radius: 5px;padding:10px 15px;border-width:1px;border-style:solid;border-color: rgb(221, 221, 221);background-color:#f5f5f5;margin:10px;padding-left:10px;margin-bottom:0px;"><a style="color:#000000;font-size:16px;cursor:pointer;"  onclick="toogleScriptHelper(4);">Files</a></div><div class="content-4-one" style="cursor: pointer;padding:10px 15px;border-width:1px;border-style:solid;border-color: rgb(221, 221, 221);display:none;margin-left:10px;margin-top:0px;padding-left:10px;margin-right:10px;border-top:0px;"><a style="cursor: pointer;" onclick="loadScriptHelperText(&quot;file_upload_helper&quot;);">Form to upload a file</a><BR><a style="cursor: pointer;" onclick="loadScriptHelperText(&quot;file_list_helper&quot;);">List Files Uploaded to Drive</a><BR></div><div class="expand-one" style="border-top-left-radius: 5px;border-top-right-radius: 5px;padding:10px 15px;border-width:1px;border-style:solid;border-color: rgb(221, 221, 221);background-color:#f5f5f5;margin:10px;padding-left:10px;margin-bottom:0px;"><a style="color:#000000;font-size:16px;cursor:pointer;"  onclick="toogleScriptHelper(5);">List Scripts</a></div><div class="content-5-one" style="cursor: pointer;padding:10px 15px;border-width:1px;border-style:solid;border-color: rgb(221, 221, 221);display:none;margin-left:10px;margin-top:0px;padding-left:10px;margin-right:10px;border-top:0px;"><a style="cursor: pointer;" onclick="loadScriptHelperText(&quot;sheet_list_helper&quot;);">SImple List from Sheet</a><BR><a style="cursor: pointer;" onclick="loadScriptHelperText(&quot;db_list_helper&quot;);">Simple List from DB</a><BR></div><div class="expand-one" style="border-top-left-radius: 5px;border-top-right-radius: 5px;padding:10px 15px;border-width:1px;border-style:solid;border-color: rgb(221, 221, 221);background-color:#f5f5f5;margin:10px;padding-left:10px;margin-bottom:0px;"><a style="color:#000000;font-size:16px;cursor:pointer;"  onclick="toogleScriptHelper(6);">Form Scripts</a></div><div class="content-6-one" style="cursor: pointer;padding:10px 15px;border-width:1px;border-style:solid;border-color: rgb(221, 221, 221);display:none;margin-left:10px;margin-top:0px;padding-left:10px;margin-right:10px;border-top:0px;"><a style="cursor: pointer;" onclick="loadScriptHelperText(&quot;form_helper&quot;);">Simple Form to Sheet</a><BR><a style="cursor: pointer;" onclick="loadScriptHelperText(&quot;dbform_helper&quot;);">Simple form to database</a><BR></div>
    <div class="expand-one" style="border-top-left-radius: 5px;border-top-right-radius: 5px;padding:10px 15px;;margin:10px;padding-left:10px;margin-bottom:0px;">
    Script Preview:<br><textarea id="modal_script_helper_textarea" style="width:100%" name="modal_script_helper_textarea" class="form-control" rows="25"></textarea>
    </div>
    </div><span class="pull-right">
            <a class="button button-primary button-large" onclick="insertHelper();">Insert</a>
            <a class="button button-default button-large" onclick="closeHelperScriptModal();">Nevermind</a>
            </span>    
        </div>';     
    }
    
    /**
     * Runs the passed content through the EASE parser and echoes the result
     *
     * @author  Lucas Simmons 
     *
     * @since 0.1
     *
     * @param string    $content  The content you want to run through the EASE parser
    */
    function ease_filter( $content) {
        session_start();
        
        // Only run the plugin if there is an ease tag.  No need to add this extra processing where it does not exist
        if((strpos($content,"<#") !== false) || (strpos($content,"&lt;#") !== false)){
            $ease_core = ease_load_core();
            // Sometimes and symbols are converted to their html equivalent
            $content = str_replace("&#038;",'&',$content);
            
            // If they used the visual text editor, then their EASE tags will be converted into the html equivalent - replacing these values should work most of the time
            $content = str_replace("&lt;#","<#",$content);
            $content = str_replace("#&gt;","#>",$content);
            
            $this_content = $ease_core->process_ease($content,true);
            echo $this_content;
        }else{
            echo $content;
        }
        if($_POST['ease_form_id']){   
            ob_flush();
        }
    }
    
    
    /**
     * Removes the footer the the ease plugin settings page
     *
     * @author  Lucas Simmons 
     *
     * @since 0.1
     *
     * @param string    $text The footer text
     *
    */
    function ease_remove_footer_admin ($text) {
        if($_REQUEST['page'] != "ease_plugin_settings"){
            echo $text;
        }
        
    }
    
    /**
     * The admin plugin settings page
     *
     * @author Lucas Simmons
     *
     * @since 0.1
     */
    function ease_plugin_settings(){
        $endpoint_page_id = get_option('ease_service_endpoint_page');
        if($endpoint_page_id === false || !$endpoint_page_id){             
            ease_load_core();
            $endpoint_page_id = get_option('ease_service_endpoint_page');
        }
        

        ?>
        <script type="text/javascript">
            function loadProjectSetup() {
                var project_id = jQuery("#ease_project_id").val();
                jQuery("#drive_api_setup").attr("href", "https://cloud.google.com/console/project/apps~" + project_id + "/apiui/api?show=all");
                jQuery("#client_id_setup").attr("href", "https://cloud.google.com/console/project/apps~" + project_id + "/apiui/credential");
                jQuery("#project_setup").show();
            }

            function toogleGoogleSettings(){
                jQuery('.content-one').slideToggle('slow');
            }
            
            function toogleDbSettings(){
                jQuery('.content-db-one').slideToggle('slow');
                jQuery('')
            }
            
            function toogleAwsSettings(){
                jQuery('.content-aws-one').slideToggle('slow');
            }
 
            function toogleBucketSettings(){
                jQuery('.content-bucket-one').slideToggle('slow');
            }
            
            function toogleUploadDirSettings() {
                jQuery('.content-upload-dir-one').slideToggle('slow');
            }
            
            function easeUploadSelectCheck(upload_type){
                if (upload_type == "amazon") {
                    jQuery('#ease_local_upload_active').prop('checked', false);
                }else{
                    jQuery('#ease_s3_active').prop('checked', false);
                }
            }
        </script>
        <style>
            #wpfooter{
                display:none;
            }
           .plugin-settings{
            padding-left:15px;
            background-color:#FFFFFA;
           }
           
            blockquote {
              border-left: 40px solid #FFFFFA;
              margin: 0em;
            }
        </style>
        <div class="wrap">
            <h2>EASE Plugin Settings</h2>
            <form method="post" action="options.php">
            <?php settings_fields( 'easeoption-group' );
            do_settings_sections( 'easeoption-group' );
            
            if(!get_post($endpoint_page_id)){
                echo '<B><a href="admin.php?page=create_ease_endpoint_page">Your EASE handler page is missing, click here to regenerate it</a></B>';
            }
            ?>
            <h4>Settings to get your EASE Plugin just the way you imagined it</h4>
            
            <?php
                $php_version = phpversion();
                
                if(version_compare($php_version,"5.3.0","<")){
                    echo "<B>You need to be running a version of PHP greater than 5.3.0 for this plugin to work</b>";
                }
    
                $ease_disable_db_access = "";
                
                if(get_option('ease_disable_db_access')){
                    $ease_disable_db_access = "checked=checked";
                }
            ?>
            <div class="sitedbsection">
                            <p class="expand-one"><a onclick="toogleDbSettings();"><h3>Plugin Settings >></h3></a></p>
                            <div class="content-db-one plugin-settings" style="display:none">
                            <table class="form-table">
                            <tr valign="top">
                            <th scope="row">Disable Database Access</th>
                            <td><input type="checkbox" name="ease_disable_db_access" id="ease_disable_db_access" <?php echo $ease_disable_db_access; ?> /></td>
                            </tr>
                            <tr valign="top">
                            <th scope="row">Database Name (if you do not want to use the default Wordpress database)</th>
                            <td><input type="text" name="ease_db_name" value="<?php echo get_option('ease_db_name'); ?>" /></td>
                            </tr>
                            <tr valign="top">
                            <th scope="row">Recreate EASE Handler Page (if you accidentally deleted it)</th>
                            <td><a href="admin.php?page=create_ease_endpoint_page">Regenerate</a></td>
                            </tr>
                            <tr valign="top">
                            <th scope="row" colspan="2" style="margin:0px;padding:0px"><i><h5>The handler pages processes all your EASE forms, uploads, etc.  Without it, EASE won't work!</h5></i></th>
                            </tr>
                            <!--  <tr valign="top">
                            <th scope="row" colspan="2">External Database Settings (optional: if you do not want to use the database on this server)</th>
                            </tr>
                            <tr valign="top">
                            <th scope="row">Database Host</th>
                            <td><input type="text" name="ease_external_db_hostname" value="<?php echo get_option('ease_external_db_hostname'); ?>" /></td>
                            </tr>
                            <tr valign="top">
                            <th scope="row">Username</th>
                            <td><input type="text" name="ease_external_db_user" value="<?php echo get_option('ease_external_db_user'); ?>" /></td>
                            </tr>
                            <tr valign="top">
                            <th scope="row">Password</th>
                            <td><input type="password" name="ease_external_db_password" value="<?php echo get_option('ease_external_db_password'); ?>" /></td>
                            </tr>-->
                            </table>
                            </div>
            </div>
            <div class="sitesection">
                <?php
                $endpoint_url = get_permalink( $endpoint_page_id );
                
                $endpoint_url_ssl = str_replace("http://","https://",$endpoint_url);
            
                $drive_active = "";
                
                if(get_option('ease_google_drive_active')){
                    $drive_active = "checked=checked";
                }
                ?>
                            <p class="expand-one"><h3><input type="checkbox" id="ease_google_drive_active" name="ease_google_drive_active" <?php echo $drive_active; ?>><a onclick="toogleGoogleSettings();">Activate Google Settings >></a></h3>(To activate connectivity to Google Drive, Sheets and Docs)</p>
                            <div class="content-one plugin-settings" style="display:none">
                                <table class="form-table">
                                                <tr valign="top">
                                                    <th colspan="2">To enable access to Google Drive, you need to configure your Google Drive security settings:
                                                        <B><BR></B>
                                                        <p class="mt20">
                                                        <ol><B>Step 1 of 3 - Create a Google Project to enable the API</B><BR><BR>
                                                        <blockquote><li>Login to your Google account</li>
                                                        <li>Go to <a target="_blank" href="http://cloud.google.com/console">http://cloud.google.com/console</a> - if the "New Project" window doesn't show, then click New Project</li>
                                                        <li>Enter a project name and ID for your project. The project is required by Google for access to Google. </li>
                                                        <li>Enter the name of your project below
                                                        <p><input type="text" id="ease_project_id" name="ease_project_id" value="<?php echo get_option('ease_project_id'); ?>" /></p></li>
                                                        </blockquote>
                                                        <p>Click <a onclick="loadProjectSetup();">Next >></a></p>
                                                        <div id="project_setup" name="project_setup" style="display: none">
                                                            <B>Step 2 of 3 - Enable the API<BR><BR></B>
                                                            <blockquote><li>Now <a id="drive_api_setup" target="_blank">Click here</a> and set the Drive API to "ON"</li></blockquote>
                                                            <B><BR>Step 3 of 3 - Create your Client ID<BR><i>The Client ID is used to authenticate your Wordpress site to Google Drive</i><BR><BR></B>
                                                            <blockquote><li><a id="client_id_setup" target="_blank">Click here</a> and select the option CREATE CLIENT ID</li>
                                                            <p>Fill out the form as follows<BR><BR>
                                                            Application type: Web Application <BR><BR>In the FIRST, Authorized Javascript Origins� box, put these values in:<BR><BR>
                                                            <textarea id="javascript_origins" rows=5 cols=60><?php echo "http://" . $_SERVER['HTTP_HOST']; ?>&#13;&#10;<?php echo "https://" . $_SERVER['HTTP_HOST']; ?></textarea>
                                                            </p>
                                                            <p>In the SECOND box, Authorized Redirect URI, put these values in:<BR><BR>
                                                            <textarea id="redirect_uris" rows=5 cols=60><?php echo $endpoint_url . check_for_params($endpoint_url) . "endpoint=oauth2callback"; ?>&#13;&#10;<?php echo $endpoint_url_ssl . check_for_params($endpoint_url_ssl) . "endpoint=oauth2callback"; ?></textarea>
                                                            </p>
                                                            </li>
                                                            <li>Now, click the CREATE CLIENT ID button and enter the CLIENT ID and CLIENT SECRET from the "CLIENT ID for web application section" below<BR><BR></li>
                                                            </blockquote>
                                                        </div>
                                                        </ol>
                                                        </p>
                                                    </th>
                                                </tr>
                                                <tr valign="top">
                                                <th scope="row">Google App Client ID</th>
                                                <td><input type="text" name="ease_gapp_client_id" value="<?php echo get_option('ease_gapp_client_id'); ?>" /></td>
                                                </tr>
                                                 
                                                <tr valign="top">
                                                <th scope="row">Google App Client Secret</th>
                                                <td><input type="text" name="ease_gapp_client_secret" value="<?php echo get_option('ease_gapp_client_secret'); ?>" /></td>
                                                </tr>

                                                <tr valign="top" style="padding:0px">
                                                    <th colspan="2" style="padding:0px"><h4>Google Drive Upload Code Examples</h4></th>
                                                </tr>
                                                <tr valign="top">
                                                    <th colspan="2">
                                                    Upload a file to your Google Drive by putting this code on a page:<BR>
                                                    <pre style="background-color:#FFFFFF;padding:10px">
&lt;# set var.uploads_folder_id to public folder id by name "EASE-BAT File Uploads"; #&gt;
&lt;# start form for files &lt;#[url.edit]#&gt;;#&gt;
Upload to Drive: &lt;input type="file" &lt;# upload file to googledrive "/&lt;#[var.uploads_folder_id]#&gt;" for files.file #&gt; /&gt;&lt;br /&gt;
&lt;input type="button" &lt;# Create button #&gt; /&gt;
&lt;# end form #&gt;</pre><BR>
List your public files in Google Drive like so:

<pre style="background-color:#FFFFFF;padding:10px">&lt;# start list for files; #&gt;

&lt;# start header #&gt;
&lt;table border='1' cellpadding='2' cellspacing='0'&gt;
&lt;tr&gt;
&lt;th&gt;File&lt;/th&gt;
&lt;/tr&gt;
&lt;# end header #&gt;

&lt;# start row #&gt;
&lt;tr&gt;
&lt;td&gt;&lt;img src="&lt;# file_drive_web_url #&gt;" /&gt;&lt;/td&gt;
&lt;/tr&gt;
&lt;# end row #&gt;

&lt;# start footer #&gt;
&lt;/table&gt;
&lt;# end footer #&gt;

&lt;# no results #&gt;
&lt;hr /&gt;No Results
&lt;# end no results #&gt;

&lt;# end list #&gt;</pre>
                                                    </th>
                                                </tr>
                                </table>
                            </div>
            </div>

            <?php
            
                $s3_upload_active = "";
                
                if(get_option('ease_s3_active')){
                    $s3_upload_active = "checked=checked";
                }
                ?>
            <div class="siteawssection">
                            <p class="expand-one"><h3><input type="checkbox" onclick="easeUploadSelectCheck('amazon');" id="ease_s3_active" name="ease_s3_active" <?php echo $s3_upload_active; ?>><a onclick="toogleAwsSettings();">Activate Amazon Settings >></a></h3> (Activate Upload/Downloads to Amazon S3 rather than your hosting site's folder)</p>
                            <div class="content-aws-one plugin-settings" style="display:none">
                            <table class="form-table">
                                <tr valign="top" style="padding:0px">
                                    <th colspan="2" style="padding:0px"><h3>Optional - Amazon Upload Settings</h3></th>
                                </tr>
                                <tr valign="top">
                                    <th colspan="2">To upload files to Amazon S3 instead or in addition to uploading to Google Drive, you can follow these instructions to create your S3 Access keys to be used by EASE:
                                    <ol>
                                        <li>Create an account at <a href="http://aws.amazon.com" target="_blank">aws.amazon.com</a></li>
                                    <li><p><a onclick="toogleBucketSettings();">Create upload buckets (optional) >></a></p>
                                    <span class="content-bucket-one" style="display:none">
                                        <p>You can specify buckets to upload to, but if you don't we will create the buckets automatically</p>
                                        <p>Go to <a href="https://console.aws.amazon.com/s3" target="_blank">https://console.aws.amazon.com/s3</a> and click Create Bucket</p>
                                        <p>Type the public bucket name here:<BR>
                                            <input type="text" name="ease_s3_bucket_public" value="<?php echo get_option('ease_s3_bucket_public'); ?>" />
                                        </p>
                                        <p>After you create that bucket, do it again and type private bucket name here:<BR>
                                            <input type="text" name="ease_s3_bucket_private" value="<?php echo get_option('ease_s3_bucket_private'); ?>" />
                                        </p>
                                    </span></li>
                                    <li>Go to <a href="https://console.aws.amazon.com/iam/home?#users" target="_blank">https://console.aws.amazon.com/iam/home?#users</a> and click Create New Users</li>
                                    <li>Type in a random username</li>
                                    <li>Click Create</li>
                                    <li>Click Show Security Credentials</li>
                                    <li>Enter the credentials displayed in the previous step below
                                    <p>Amazon S3 Access Key ID<BR>
                                    <input type="text" name="ease_s3_access_key" value="<?php echo get_option('ease_s3_access_key'); ?>" />
                                    </p>
                                    <p>Amazon S3 Secret Access Key<BR>
                                    <input type="text" name="ease_s3_secret_access_key" value="<?php echo get_option('ease_s3_secret_access_key'); ?>" />
                                    </p>
                                    </li>
                                    <li>For the created user, click on that user in IAM</li>

                                    <p>
                                        Once the user is created, click on that user
                                    </p>
                                    <li>At the bottom, click permissions</li>
                                    <li>Click Attach User Policy
                                    <p>Select Custom Policy, and click Select</p></li>
                                    <li>Type in any name for the policy name</li>
                                    <li>In the Policy Document, copy and paste the following text:<BR>
                                    <textarea  rows=5 cols=60>{
                                    "Version": "2012-10-17",
                                    "Statement": [
                                      {
                                        "Sid": "Stmt1404711211000",
                                        "Effect": "Allow",
                                        "Action": [
                                          "s3:*"
                                        ],
                                        "Resource": [
                                          "*"
                                        ]
                                      }
                                    ]
                                  }</textarea>
                                    </p>
                                    <p>Click Apply Policy</p>
                                    <p>You can now access your S3 bucket through EASE!</p>
                                    </li>
                                    </ol>
                                    </th>
                                </tr>
                                <tr valign="top" style="padding:0px">
                                    <th colspan="2" style="padding:0px"><h4>Amazon Code Examples</h4></th>
                                </tr>
                                <tr valign="top">
                                    <th colspan="2">
                                    Upload a public file to Amazon S3 by putting this code on a page:<BR>
                                    <pre style="background-color:#FFFFFF;padding:10px">&lt;# start form for public_files &lt;#[url.edit]#&gt;;#&gt;
#&gt;
File to Upload: &lt;input type="file" &lt;# file #&gt; /&gt;&lt;br /&gt;
&lt;input type="button" &lt;# Create button #&gt; /&gt;
&lt;# end form #&gt;</pre><BR>
                                    List your public files in Amazon S3 like so:<BR>
                                                                        <pre style="background-color:#FFFFFF;padding:10px">&lt;# start list for public_files; #&gt;

&lt;# start header #&gt;
&lt;table border='1' cellpadding='2' cellspacing='0'&gt;
&lt;tr&gt;
&lt;th&gt;File Name&lt;/th&gt;
&lt;th&gt;Web URL as HREF in IMG tag&lt;/th&gt;
&lt;/tr&gt;
&lt;# end header #&gt;

&lt;# start row #&gt;
&lt;tr&gt;
&lt;td&gt;&lt;# file as html #&gt;&lt;/td&gt;
&lt;td&gt;&lt;img src="&lt;# file_web_url #&gt;" /&gt;&lt;/td&gt;
&lt;/tr&gt;
&lt;# end row #&gt;

&lt;# start footer #&gt;
&lt;/table&gt;
&lt;# end footer #&gt;

&lt;# end list #&gt;</pre>
                                    </th>
                                </tr>
                            </table>
                    </div>
    </div>
                        <div class="siteuploadsection">
                <?php
                
                $plugin_dir = plugin_dir_path( __FILE__ );
                $public_dir = get_option('ease_public_folder_upload_directory');
                $private_dir = get_option('ease_private_folder_upload_directory');
                
                $upload_dir = wp_upload_dir();
                $upload_directory = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . "ease_framework";
                
                if(!is_dir($upload_directory)){
                    @mkdir($upload_directory);
                }
                    
                if(!$public_dir){
                    $public_dir = $upload_directory . DIRECTORY_SEPARATOR . "public";
                    
                    if(!is_dir($public_dir)){
                        @mkdir($public_dir);
                    }
                    update_option('ease_public_folder_upload_directory',$public_dir);
                }
                
                if(!$private_dir){
                    $private_dir = dirname($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "ease_framework_uploads_private";
                    if(!is_dir($private_dir)){
                        @mkdir($private_dir);
                    }
                    update_option('ease_private_folder_upload_directory',$private_dir);
                }
                
                $local_upload = "";
                
                if(get_option('ease_local_upload_active')){
                    $local_upload = "checked=checked";
                }
                ?>
                            <p class="expand-one"><h3><input type="checkbox" onclick="easeUploadSelectCheck('local');" id="ease_local_upload_active" name="ease_local_upload_active" <?php echo $local_upload; ?>><a onclick="toogleUploadDirSettings();">Activate Upload Folders >></a></h3>(Activate uploads/downloads to your hosted sites folder)</p>
                            <div class="content-upload-dir-one plugin-settings" style="display:none">
                            <table class="form-table">
                            <tr valign="top">
                            <th scope="row">Public Folder<?php if(!is_dir($public_dir)){
                                echo "<BR>Could not create/locate public directory, you may want to use Amazon or Google Drive for Uploads<BR>";   
                            } ?></th>
                            <td><input type="text" name="ease_public_folder_upload_directory" value="<?php echo $public_dir ?>" /></td>
                            </tr>
                            <tr valign="top">
                            <th scope="row">Private Folder
                            </th>
                            <td><?php if(!is_dir($private_dir)){
                                echo "<BR>Could not create/locate private directory, you may want to use Amazon or Google Drive for Uploads<BR>";   
                            } ?><BR><input type="text" name="ease_private_folder_upload_directory" value="<?php echo $private_dir ?>" /></td>
                            </tr>
                                <tr valign="top" style="padding:0px">
                                    <th colspan="2" style="padding:0px"><h4>Code Examples</h4></th>
                                </tr>
                                <tr valign="top">
                                    <th colspan="2">
                                    Upload a file to your server by putting this code on a page:<BR>
                                    <pre style="background-color:#FFFFFF;padding:10px">&lt;# start form for public_files &lt;#[url.edit]#&gt;;#&gt;
File to Upload: &lt;input type="file" &lt;# file #&gt; /&gt;&lt;br /&gt;
&lt;input type="button" &lt;# Create button #&gt; /&gt;
&lt;# end form #&gt;</pre><BR>
                                    List your public files on your server like so:<BR>
                                                                        <pre style="background-color:#FFFFFF;padding:10px">&lt;# start list for public_files; #&gt;

&lt;# start header #&gt;
&lt;table border='1' cellpadding='2' cellspacing='0'&gt;
&lt;tr&gt;
&lt;th&gt;File Name&lt;/th&gt;
&lt;th&gt;Web URL as HREF in IMG tag&lt;/th&gt;
&lt;/tr&gt;
&lt;# end header #&gt;

&lt;# start row #&gt;
&lt;tr&gt;
&lt;td&gt;&lt;# file as html #&gt;&lt;/td&gt;
&lt;td&gt;&lt;img src="&lt;# file_web_url #&gt;" /&gt;&lt;/td&gt;
&lt;/tr&gt;
&lt;# end row #&gt;

&lt;# start footer #&gt;
&lt;/table&gt;
&lt;# end footer #&gt;

&lt;# end list #&gt;</pre>
                                    </th>
                                </tr>
                            </table>
                            </div>
            </div>
            <?php submit_button(); ?>
            </form>
            </div>
        </div>
        <?php
    }
 
     /**
     *  The admin welcome page
     *
     *  @author Lucas Simmons
     *
     *  @since 0.1
     *  
     */   
    function ease_landing_page(){
        echo "<script type='text/javascript'>
        function closeModal(){
            tb_show('', '#TB_inline?height=300&width=300&inlineId=confirmDiv&modal=false');
            jQuery('#TB_window').css({'border':'0px', 'background':'none'});
            jQuery('#TB_window').hide();
            jQuery('#TB_overlay').css({'background':'transparent'});
        }
        function loadWelcomeWindow(){
            tb_show('Welcome to WordPress with EASE','#TB_inline?width=1000&amp;height=500&amp;inlineId=welcome-modal-div');
            jQuery('#TB_closeAjaxWindow').html('<a onclick=\'closeModal();\'><div class=\'tb-close-icon\'></div></a>');
        }
        </script>
        <div class='wrap'><h3>Welcome to Cloudward EASE and WordPress</h3></div>";
        add_thickbox();
        echo '<span>
        <a onclick="loadWelcomeWindow();" title="Welcome" id="welcome-modal-link" name="welcome-modal-link" class="thickbox">Welcome</a>
        </span>
        <div id="welcome-modal-div" style="display:none;">
        <p>    
				    
				    
				    
				<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Welcome to Cloudward with EASE - Cloudward</title>
	<!-- favicon -->
	<link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
	<link rel="icon" href="favicon.ico">
	
	<link rel="stylesheet" type="text/css" href="http://www.cloudward.com/static/css/style.css" />
	
	<!-- fonts -->
	<link href="http://fonts.googleapis.com/css?family=Lato:400,700,900,400italic" rel="stylesheet" type="text/css">
    <meta name="description" content="Welcome to Cloudward with EASE. Build your business with the skills you have and the tools you know">
</head>
<body>
	<!-- 
	=>	NOTE: 
		Remove class on body tag class="temporary-cloudward-wp-popUp-bg"
		when applying content into iFrame, this class is temporary and is used as simulation for wordpress background.

	=>	IMPORTANT:
		All styles are pulled from file style.css that is linked into head of this document.
		Font family is pulled from fonts.googleapis.com
	-->
	<div class="cloudward-wp-popUp">
		<div id="plugin-information-content">
			<div class="cloudward-wp-popUp-content clearfix">
				<div class="cloudward-wp-popUp-heading">
					<h1>Welcome to <strong class="blue-light">Cloudward</strong> with <strong class="blue-light">EASE</strong></h1>
					<h2>Build your business with the skills you have and the tools you know</h2>
				</div>
				<!-- description -->
				<div class="cloudward-wp-popUp-description">
					<div class="text-formating">
						<ul>
							<li>Create and edit your WordPress site from Google Docs</li>
							<li>Save customer contact forms to a Google Sheet</li>
							<li>Deliver protected member content from Google Cloud</li>
							<li>eCommerce Store setup is as easy as setting up a spreadsheet</li>
							<li>Customize with Cloudward’s EASE framework</li>
						</ul>
						<p class="text-big">Get Support: <strong><a href="http://www.cloudward.com/support" target="_blank">http://www.cloudward.com/support</a></strong></p>
					</div>
				</div>
				<!-- video -->
				<div>
						
						<center><iframe id="startplayer" style="visibility: visible; display: block;" frameborder="0" allowfullscreen="1" title="YouTube video player" width="264" height="180" src="https://www.youtube.com/embed/UNHevzZf7dM?autoplay=0&enablejsapi=1&amp;controls=2&amp;rel=0&amp;modestbranding=1&amp;showinfo=0&amp;hd=1&amp;autohide=1&amp;enablejsapi=1&amp;origin=http%3A%2F%2Fwww.cloudward.com"></iframe></center>
				</div>
			</div>
		</div>
	</div>
	
	<script src=”//code.jquery.com/jquery-1.7.2.js”></script>
	<script type=”text/javascript” src=”http://www.cloudward.com/js/lunametrics-youtube-v6.js ”></script>
	
	<span id="welcome_do_not_show" >
                <a class="button button-primary button-large" href="admin.php?page=ease_landing_page&ease_do_not_show_welcome=y">Do not show this again</a>
    </span>
    <span id="welcome_do_show">
                <a class="button button-primary button-large" href="admin.php?page=ease_landing_page&ease_do_not_show_welcome=n">Show this again</a>
    </span>
	
</body>
</html>			    
			  			    
			  			    
			  			    
			  </p>
       </div>';
       
        if($_GET['ease_do_not_show_welcome'] == "y"){
         update_option('ease_has_shown_install_popup',true);
        }elseif($_GET['ease_do_not_show_welcome'] == "n"){
         update_option('ease_has_shown_install_popup',"");
        }
       
        if(!get_option('ease_has_shown_install_popup')){
            add_thickbox();
           
           echo '<script type="text/javascript">jQuery(window).load(function() {
                loadWelcomeWindow();
                jQuery("#welcome_do_show").hide();
           });
           </script>
           ';
        }else{
            echo '<script type="text/javascript">jQuery(window).load(function() {
                jQuery("#welcome_do_not_show").hide();
           });
           </script>
           ';           
        }
    }
    
     /**
     *  Initializing EASE Settings
     *
     *  @author Lucas Simmons
     *
     *  @since 0.1
     *  
     */   
    function register_ease_setting() {
            register_setting( 'easeoption-group', 'ease_db_name' );
            register_setting( 'easeoption-group', 'ease_project_id' );
            register_setting( 'easeoption-group', 'ease_google_drive_active' );
            register_setting( 'easeoption-group', 'ease_gapp_client_id' );
            register_setting( 'easeoption-group', 'ease_gapp_client_secret' );
            register_setting( 'easeoption-group', 'ease_s3_active' );
            register_setting( 'easeoption-group', 'ease_s3_bucket_public' );
            register_setting( 'easeoption-group', 'ease_s3_bucket_private' );
            register_setting( 'easeoption-group', 'ease_s3_access_key' );
            register_setting( 'easeoption-group', 'ease_s3_secret_access_key' );
            register_setting( 'easeoption-group', 'ease_has_shown_install_popup' );
            register_setting( 'easeoption-group', 'ease_local_upload_active' );
            register_setting( 'easeoption-group', 'ease_public_folder_upload_directory' );
            register_setting( 'easeoption-group', 'ease_private_folder_upload_directory' );
            register_setting( 'easeoption-group', 'ease_external_db_hostname' );
            register_setting( 'easeoption-group', 'ease_external_db_user' );
            register_setting( 'easeoption-group', 'ease_external_db_password' );
            register_setting( 'easeoption-group', 'ease_disable_db_access' );
            //ease_disable_db_access
    //        register_setting( 'easeoption-group', 'ease_service_endpoint_page' );
    }
    
     /**
     *  Function to add the admin side plugin menu
     *
     *  @author Lucas Simmons
     *
     *  @since 0.1
     *  
     */   
    function ease_plugin_menu(){
        add_menu_page('Welcome','EASE','manage_options','ease_landing_page','ease_landing_page');
        add_submenu_page('ease_landing_page','Settings','Settings','manage_options','ease_plugin_settings','ease_plugin_settings');
        add_submenu_page(null,'Create EASE Endpoint page','Create EASE Endpoint page','manage_options','create_ease_endpoint_page','create_ease_endpoint_page');
    }
    

    include_once plugin_dir_path( __FILE__ ) . 'plugin_function_includes.php';
?>
