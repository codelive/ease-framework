<!-- GOOGLE SETTINGS MODAL START -->
<div id="google-settings-modal" style="display:none">
<!DOCTYPE html>
    <html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Welcome to Cloudward with EASE - Cloudward</title>
	<!-- favicon -->
	<link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
	<link rel="icon" href="favicon.ico">
	<!-- css -->
        <link rel="stylesheet" type="text/css" title="cloudward_static_style" href="<?php echo plugin_dir_url(__FILE__); ?>static/css/style.css">
	<!-- fonts -->
	<link href='http://fonts.googleapis.com/css?family=Lato:400,700,900,400italic' rel='stylesheet' type='text/css'>
     <meta name="description" content="Welcome to Cloudward with EASE. Build your business with the skills you have and the tools you know">
</head>
        <script type="text/javascript">
            function loadGoogleSetup() {
                var project_id = jQuery("#ease_project_id_modal").val().trim();
                
                if (project_id.indexOf(" ") > 0) {
                    alert("Your project name cannot have spaces");
                    return false;
                }
                jQuery("#drive_api_setup").attr("href", "https://cloud.google.com/console/project/apps~" + project_id + "/apiui/api?show=all");
                jQuery(".credential_setup").attr("href", "https://cloud.google.com/console/project/apps~" + project_id + "/apiui/credential");
                jQuery(".consent_screen_setup").attr("href","https://console.developers.google.com/project/apps~" + project_id + "/apiui/consent");
                jQuery("#project_setup").show();
            }
        </script>
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
					<h1>Welcome to the <span class="blue-light">Cloudward EASE Framework</span></h1>
					<p class="text-formating">To enable access to Google Drive, you need to configure your Google Drive security settings via a Google Project. This project requires settings that this wizard will take you through and will also setup your EASE Framework Plugin to be enabled for access to your Google Drive (You will be asked later to actually logon)</p>
				</div>
				<!-- STEPS -->
				<!-- cloudward-wp-popUp-steps => short class declaration "cwps" -->
				<form method="post" id="cwFormID" onsubmit="submitEASEModalData();return false;">
					<div class="cwps clearfix tabs">
						<div class="cwps-main text-formating">
							<div class="tabs-nav">
								<ul>
									<li class="item-step item-step-01">
										<div class="item clearfix">
											<div class="cwps-main-lbl"><a href="#tab-1"><label for="projectID">Project ID</label></a></div>
											<div class="cwps-main-value"><input id="ease_project_id_modal" name="projectID" type="text" value="<?php echo get_option('ease_project_id'); ?>" oninput="loadProjectSetup();" required /></div>
										</div>
									</li>
									<li class="item-step item-step-02">
										<div class="item">
											<a href="#tab-2">Enable Google Drive</a>
										</div>
									</li>

									<li class="item-step item-step-03">
										<div class="item">
											<a href="#tab-3">Update the Consent Screen</a>
										</div>
									</li>
									<li class="item-step item-step-04">
										<div class="item">
											<a href="#tab-4">Create Client ID</a>
										</div>
									</li>
									<li class="item-step item-step-05">
										<div class="item clearfix">
											<div class="cwps-main-lbl"><a href="#tab-5">Client ID</a></div>
											<div class="cwps-main-value"><input type="text" name="ease_gapp_client_id_modal" id="ease_gapp_client_id_modal" value="<?php echo get_option('ease_gapp_client_id'); ?>" required /></div>
										</div>
										<div class="item clearfix">
											<div class="cwps-main-lbl"><a href="#tab-5">Client Secret</a></div>
											<div class="cwps-main-value"><input type="text" name="ease_gapp_client_secret_modal" id="ease_gapp_client_secret_modal" value="<?php echo get_option('ease_gapp_client_secret'); ?>" required /></div>
										</div>
									</li>
									<li class="item-step item-step-06">
										<div class="item">
											<a href="#tab-6">Finish</a>
										</div>
									</li>
								<ul>
							</div>
						</div>
						<!-- side -->
						<div class="cwps-side">
							<div class="cwps-side-content">
								<div class="tabs-content">
									<!-- 01 -->
									<div id='tab-1' class="item item-step-01">
										<h2>Project ID</h2>
										<div class="cwps-video-holder">
											<iframe width="414" height="219" src="//www.youtube.com/embed/n911chjIUHs?rel=0&amp;controls=0" frameborder="0" allowfullscreen></iframe>
										</div>
										<ol>
											<li>Login to your Google Drive account.</li>
											<li>Go to <a href="http://cloud.google.com/console" target="_blank">http://cloud.google.com/console</a> - if the <strong>“New Project”</strong> window doesn’t show, then click New Project.</li>
											<li>Enter a project name and ID for your project.</li>
											<li>Enter the Project ID  of your project to the right and continue to the next step.</li>
										</ol>
									</div>
									<!-- 02 -->
									<div id='tab-2' class="item item-step-02">
										<h2>Enable Google Drive</h2>
										<div class="cwps-video-holder">
											<iframe width="414" height="219" src="//www.youtube.com/embed/s1APUlWC6ig?rel=0&amp;controls=0" frameborder="0" allowfullscreen></iframe>
										</div>
										<p><a class="drive_api_setup" target="_blank">Click here</a> and set the <strong>Drive API to "ON"</strong></p>
									</div>
									<div id='tab-3' class="item item-step-03 text-formating">
										<h2>Update the Consent Screen</h2>
										<div class="cwps-video-holder">
											<iframe width="414" height="219" src="//www.youtube.com/embed/83mQPxjg4iA?rel=0&amp;controls=0" frameborder="0" allowfullscreen></iframe>
										</div>
										<p>Add Product Name and Email to your <a class="consent_screen_setup" target="_blank">consent screen</a>.</p>
										<p>For your app to authorize access to Google Drive it presents a consent screen. Your consent includes a required Product Name and Email address.  The consent screen will be shown to users whenever your application requests access to their private data using your client ID.</p>
									</div>
									<!-- 03 -->
									<div id='tab-4' class="item item-step-04 text-formating">
										<h2>Create Client ID</h2>
										<div class="cwps-video-holder">
											<iframe width="414" height="219" src="//www.youtube.com/embed/pJLjlRbNkWA?rel=0&amp;controls=0" frameborder="0" allowfullscreen></iframe>
										</div>
										<p>The Client ID is used to authenticate your Wordpress Apps access to Google Drive <a class="client_id_setup" target="_blank">click here</a> and select the option <strong>"CREATE CLIENT ID"</strong></p>
										<p>(Update your consent screen if prompted)</p>
										<p>Fill out the form as follows:</p>
										<p>Application type: Web Application In the <strong>"Authorized Javascript Origins"</strong> box, put these values in:</p>
										<code>
											<p><?php echo "http://" . $_SERVER['HTTP_HOST']; ?></p>
											<p><?php echo "https://" . $_SERVER['HTTP_HOST']; ?></p>
										</code>
										<p>In the SECOND box, <strong>"Authorized Redirect URI"</strong>, put these values in:</p>
										<code>
											<?php
												$endpoint_page_id = get_option('ease_service_endpoint_page');
												if($endpoint_page_id === false || !$endpoint_page_id){             
												    ease_load_core();
												    $endpoint_page_id = get_option('ease_service_endpoint_page');
												}
											        $endpoint_url = site_url() . "/?page_id=" . $endpoint_page_id . "&endpoint=oauth2callback";
                
												$endpoint_url_ssl = str_replace("http://","https://",$endpoint_url);
											?>
											<p><?php echo $endpoint_url; ?></p>
											<p><?php echo $endpoint_url_ssl; ?></p>
										</code>
									</div>
									<!-- 04 -->
									<div id='tab-5' class="item item-step-05 text-formating">
										<h2>Client ID and Client Secret</h2>
										<div class="cwps-video-holder">
											<iframe width="414" height="219" src="//www.youtube.com/embed/6X0XyTEvUwk?rel=0&amp;controls=0" frameborder="0" allowfullscreen></iframe>
										</div>
										<p>From the <a class="client_id_setup" target="_blank">Client ID</a> Created in the previous step. Copy the <strong>Client ID</strong> and <strong>Client Secret</strong> into the fields on the right.</p>
										<p>These will be set in the plugin and will allow your WordPress page to request access to Google Drive.</p>
										<p>Note: If the domain of your app changes, or the EASE Endpoint handler URL changes - you will have to update the redirect URL and Javascript origins.</p>
									</div>
									<!-- 05 -->
									<!-- 06 -->
									<div id='tab-6' class="item item-step-06 text-formating">
										<h2>Finish</h2>
										<div class="cwps-video-holder">
											<iframe width="414" height="219" src="//www.youtube.com/embed/TcxMEYRXz4Q?rel=0&amp;controls=0" frameborder="0" allowfullscreen></iframe>
										</div>
										<p>Setup is complete. Save your Changes.</p>
										<p>Note: when your app first requires access to Google Drive it will prompt you with a logon screen. At this point, your app is granted access to Google Drive.</p>
										<p class="img-holder">
											<img src="<?php echo plugin_dir_url(__FILE__); ?>static/img/wp-plugin/cwps-access-google-drive.jpg" alt="Access Google Drive" />
										</p>
										<p>Errors at this time are most likely errors during the setup process. Review your settings in this wizard and your Cloud Project</p>
									</div>
								</div>
							</div>
							<div class="cwps-side-content-nav clearfix">
								<a class="prev">Prev Step</a>
								<a class="next">Next Step</a>
							</div>
						</div>
					</div>
					<div style="margin:20px;text-align:right">
						<input type="submit" onclick="closeEaseSettingsModal();return false;" value="Cancel" class="btn btn-small" />
						<input type="submit" onclick="submitEASEModalData();return false;" value="Save" class="btn btn-small" />
					</div>
				</form>
			</div>
		</div>
	</div>
 
        </body>
        </html>
</div>
<!-- GOOGLE SETTINGS MODAL END -->
<!-- PLUGIN SETTINGS MODAL START -->
<div id="plugin-settings-modal" style="display:none">
<!DOCTYPE html>
    <html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Welcome to Cloudward with EASE - Cloudward</title>
	<!-- favicon -->
	<link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
	<link rel="icon" href="favicon.ico">
	<!-- css -->
        <link rel="stylesheet" type="text/css" title="cloudward_static_style" href="<?php echo plugin_dir_url(__FILE__); ?>static/css/style.css">
	<!-- fonts -->
	<link href='http://fonts.googleapis.com/css?family=Lato:400,700,900,400italic' rel='stylesheet' type='text/css'>
     <meta name="description" content="Welcome to Cloudward with EASE. Build your business with the skills you have and the tools you know">
</head>
        <script type="text/javascript">
            function loadGoogleSetup() {
                var project_id = jQuery("#ease_project_id_modal").val().trim();
                
                if (project_id.indexOf(" ") > 0) {
                    alert("Your project name cannot have spaces");
                    return false;
                }
                jQuery("#drive_api_setup").attr("href", "https://cloud.google.com/console/project/apps~" + project_id + "/apiui/api?show=all");
                jQuery(".credential_setup").attr("href", "https://cloud.google.com/console/project/apps~" + project_id + "/apiui/credential");
                jQuery(".consent_screen_setup").attr("href","https://console.developers.google.com/project/apps~" + project_id + "/apiui/consent");
                jQuery("#project_setup").show();
            }
        </script>
	<body>
	<!-- 
	=>	NOTE: 
		Remove class on body tag class="temporary-cloudward-wp-popUp-bg"
		when applying content into iFrame, this class is temporary and is used as simulation for wordpress background.

	=>	IMPORTANT:
		All styles are pulled from file style.css that is linked into head of this document.
		Font family is pulled from fonts.googleapis.com
	-->
	<?php
		$ease_disable_db_access = "";
                
                if(get_option('ease_disable_db_access') == "on"){
                    $ease_disable_db_access = "selected";
                }
	?>
	<div class="cloudward-wp-popUp">
		<div id="plugin-information-content">
			<div class="cloudward-wp-popUp-content clearfix">
				<div class="cloudward-wp-popUp-heading">
					<h1><span class="blue-light">EASE Plugin Properties</span></h1>
				</div>
				<!-- STEPS -->
				<!-- cloudward-wp-popUp-steps => short class declaration "cwps" -->
				<form method="post" id="cwFormID" onsubmit="submitEASEModalData();return false;">
					<div class="cwps clearfix tabs">
						<div class="cwps-main text-formating">
							<div class="tabs-nav">
								<ul>
									<li class="item-step item-step-01">
										<div class="item clearfix">
											<div class="cwps-main-lbl"><a href="#tab-7"><label for="disableDB">Disable Database Access</label></a></div>
											<div class="cwps-main-value" style="text-align:right">											<select name="ease_disable_db_access_modal" id="ease_disable_db_access_modal">
												<option value="">no</option>
												<option value="on" <?php echo $ease_disable_db_access;?>>yes</option>
											</select>
											</div>
										</div>
										<div class="item clearfix">
											<div class="cwps-main-lbl"><a href="#tab-8"><label for="dbname">Database Name</label></a></div>
											<div class="cwps-main-value"><input type="text" name="ease_db_name_modal" id="ease_db_name_modal" value="<?php echo get_option('ease_db_name_modal'); ?>" /></div>
										</div>
										<div class="item clearfix">
											<div class="cwps-main-lbl"><a href="#tab-9"><label for="dbname">Recreate EASE Handler Page (if you accidentally deleted it)</label></a> <a href="admin.php?page=create_ease_endpoint_page" target="_blank"><span style='text-decoration:underline'>Recreate</a></div>
											<div class="cwps-main-value"></div>
										</div>
									</li>
								<ul>
							</div>
						</div>
<!-- side -->
						<div class="cwps-side">
							<div class="cwps-side-content">
								<div class="tabs-content">
									<!-- 07 -->
									<div id='tab-7' class="item item-step-07">
										
										<div class="cwps-video-holder">
											<iframe width="414" height="219" src="//www.youtube.com/embed/eGsYJmlX6Q8?rel=0&amp;controls=0" frameborder="0" allowfullscreen></iframe>
										</div>
										Disable Database Access - Set to Yes if you do now want to access a local database from EASE<BR><BR>
										Database Name - if you do not want to use the default WordPress database (On the same hosting machine)<BR><BR>
										Follow <a href="admin.php?page=create_ease_endpoint_page" target="_blank"><span style='text-decoration:underline'>this link</A> to recreate the EASE Handler Page - this page is used as an end-point for EASE commands. If this page is regenerated, you may need to add this new page to your Authorized Redirect URI.
									</div>
								</div>
							</div>
						</div>
					</div>
					
					</div>
					<div style="margin:20px;text-align:right">
						<input type="submit" onclick="closeEaseSettingsModal();return false;" value="Cancel" class="btn btn-small" />
						<input type="submit" onclick="submitEASEModalData();return false;" value="Save" class="btn btn-small" />
					</div>
					
				</form>
			</div>
		</div>
	</div>

        </body>
        </html>
</div>
<!-- PLUGIN SETTINGS MODAL END -->
<!-- AMAZON SETTINGS MODAL START -->
<div id="upload-settings-modal" style="display:none">
<!DOCTYPE html>
    <html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Welcome to Cloudward with EASE - Cloudward</title>
	<!-- favicon -->
	<link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
	<link rel="icon" href="favicon.ico">
	<!-- css -->
        <link rel="stylesheet" type="text/css" title="cloudward_static_style" href="<?php echo plugin_dir_url(__FILE__); ?>static/css/style.css">
	<!-- fonts -->
	<link href='http://fonts.googleapis.com/css?family=Lato:400,700,900,400italic' rel='stylesheet' type='text/css'>
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
	<?php
                $public_dir = get_option('ease_public_folder_upload_directory');
                $private_dir = get_option('ease_private_folder_upload_directory');
	?>
	<div class="cloudward-wp-popUp">
		<div id="plugin-information-content">
			<div class="cloudward-wp-popUp-content clearfix">
				<div class="cloudward-wp-popUp-heading">
					<h1><span class="blue-light">Upload Folders</span></h1>
				</div>
				<!-- STEPS -->
				<!-- cloudward-wp-popUp-steps => short class declaration "cwps" -->
				<form method="post" id="cwFormID" onsubmit="submitEASEModalData();return false;">
					<div class="cwps clearfix tabs">
						<div class="cwps-main text-formating">
							<div class="tabs-nav">
								<ul>
									<li class="item-step item-step-10">
										<div class="item clearfix">
											<div class="cwps-main-lbl"><a href="#tab-10"><label for="disableDB">Private Folder
											
											</label></a></div>
											<div class="cwps-main-value"><input type="text" name="ease_private_folder_upload_directory_modal" id="ease_private_folder_upload_directory_modal" value="<?php echo get_option('ease_private_folder_upload_directory'); ?>" /></div>
										
											<div class="cwps-main-lbl"><a href="#tab-11"><label for="dbname">Public Folder
											
											</label></a></div>
											<div class="cwps-main-value"><input type="text" name="ease_public_folder_upload_directory_modal" id="ease_public_folder_upload_directory_modal" value="<?php echo get_option('ease_public_folder_upload_directory'); ?>" /></div>
										</div>
									</li>
								<ul>
							</div>
						</div>
<!-- side -->
						<div class="cwps-side">
							<div class="cwps-side-content">
								<div class="tabs-content">
									<!-- 07 -->
									<div id='tab-10' class="item item-step-10">
										<div class="cwps-video-holder">
											<iframe width="414" height="219" src="//www.youtube.com/embed/IaiGooq9j4g?rel=0&amp;controls=0" frameborder="0" allowfullscreen></iframe>

										</div>
										Set File Upload Folders<BR><BR>
										
										<?php if(!is_dir($public_dir)){
												echo "<BR>Could not create/locate public directory, you may want to use Amazon or Google Drive for Uploads<BR>";   
										} ?>
										<?php if(!is_dir($private_dir)){
												echo "<BR>Could not create/locate private directory, you may want to use Amazon or Google Drive for Uploads<BR>";   
										} ?>
										Set the locations in your WordPress Hosting site to be used by EASE to upload files too, or download files from. Public folders are accessible to anyone with the URL. Private folders are only accessible via EASE (See EASE Helper Scripts for examples of how to use)
									</div>
								</div>
							</div>
						</div>
					</div>
					
					</div>
					<div style="margin:20px;text-align:right">
						<input type="submit" onclick="closeEaseSettingsModal();return false;" value="Cancel" class="btn btn-small" />
						<input type="submit" onclick="submitEASEModalData();return false;" value="Save" class="btn btn-small" />
					</div>
					
				</form>
			</div>
		</div>
	</div>

        </body>
        </html>
</div>
<!-- UPLOAD SETTINGS MODAL END -->
<!-- AMAZON SETTINGS MODAL START -->
<div id="amazon-settings-modal" style="display:none">
<!DOCTYPE html>
    <html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Welcome to Cloudward with EASE - Cloudward</title>
	<!-- favicon -->
	<link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
	<link rel="icon" href="favicon.ico">
	<!-- css -->
        <link rel="stylesheet" type="text/css" title="cloudward_static_style" href="<?php echo plugin_dir_url(__FILE__); ?>static/css/style.css">
	<!-- fonts -->
	<link href='http://fonts.googleapis.com/css?family=Lato:400,700,900,400italic' rel='stylesheet' type='text/css'>
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
					<h1><span class="blue-light">Amazon Settings</span></h1>
				</div>
				<!-- STEPS -->
				<!-- cloudward-wp-popUp-steps => short class declaration "cwps" -->
				<form method="post" id="cwFormID" onsubmit="submitEASEModalData();return false;">
					<div class="cwps clearfix tabs">
						<div class="cwps-main text-formating">
							<div class="tabs-nav">
								<ul>
									<li class="item-step item-step-12">
										<div class="item clearfix">
											<div class="cwps-main-lbl"><a href="#tab-12"><label for="disableDB">Create an Amazon account</label></a></div>
										
											<div class="cwps-main-value"></div>
										</div>
									</li>

									<li class="item-step item-step-13">
										<div class="item clearfix">
											<div class="cwps-main-lbl"><a href="#tab-13"><label for="dbname">Set Access Keys</label></a></div>
											<div class="cwps-main-value"></div>
										</div>
										<div class="item clearfix">
											<div class="cwps-main-lbl"><label for="disableDB">Access Key ID</label></div>
											<div class="cwps-main-value"><input type="text" name="ease_s3_access_key_modal" id="ease_s3_access_key_modal" value="<?php echo get_option('ease_s3_access_key'); ?>" /></div>
										
											<div class="cwps-main-lbl"><label for="dbname">Secret Access Key</label></div>
											<div class="cwps-main-value"><input type="text" name="ease_s3_secret_access_key_modal" id="ease_s3_secret_access_key_modal" value="<?php echo get_option('ease_s3_secret_access_key'); ?>" /></div>

										</div>
									</li>
									<li class="item-step item-step-15">
										<div class="item clearfix">
											<div class="cwps-main-lbl"><a href="#tab-15"><label for="dbname">Set S3 Policy
											
											</label></a></div>
											<div class="cwps-main-value"></div>
										</div>
									</li>
									<li class="item-step item-step-16">
										<div class="item clearfix">
											<div class="cwps-main-lbl"><a href="#tab-16"><label for="dbname">Create upload buckets (optional)
											
											</label></a></div>
											<div class="cwps-main-value"></div>
										</div>
										<div class="item clearfix">
											<div class="cwps-main-lbl"><label for="disableDB">Public Bucket Name</label></div>
											<div class="cwps-main-value"><input type="text" name="ease_s3_bucket_public_modal" id="ease_s3_bucket_public_modal" value="<?php echo get_option('ease_s3_bucket_public'); ?>" /></div>
										
											<div class="cwps-main-lbl"><label for="dbname">Private Bucket Name</label></div>
											<div class="cwps-main-value"><input type="text" name="ease_s3_bucket_private_modal" id="ease_s3_bucket_private_modal" value="<?php echo get_option('ease_s3_bucket_private'); ?>" /></div>

										</div>
									</li>
								<ul>
							</div>
						</div>
<!-- side -->
						<div class="cwps-side">
							<div class="cwps-side-content">
								<div class="tabs-content">
									<div id='tab-12' class="item item-step-12">
										<div class="cwps-video-holder">
											<a href="#" target="_blank">
												<iframe width="414" height="219" src="//www.youtube.com/embed/U8kC-dK752s?rel=0&amp;controls=0" frameborder="0" allowfullscreen></iframe>
											</a>
										</div>
										<B><span style="text-decoration:underline">Create an Amazon account</span></B>
										<BR>
											<ol>
												<li>Create an account at <a href="http://aws.amazon.com" target="_blank">http://aws.amazon.com</a></li>
											</ol>
									</div>
									<div id='tab-13' class="item item-step-13">
										
										<div class="cwps-video-holder">
												<iframe width="414" height="219" src="//www.youtube.com/embed/cm10O8b1yYM?rel=0&amp;controls=0" frameborder="0" allowfullscreen></iframe>
										</div>
										<B><span style="text-decoration:underline">Set Access Keys</span></B>
										<BR>
											<ol>
												<li>Go to <a href="https://console.aws.amazon.com/iam/home?#users" target="_blank">https://console.aws.amazon.com/iam/home?#users and click Create New Users</a></li>
												<li>Type in a random username</li>
												<li>Click Create</li>
												<li>Click Show Security Credentials</li>
												<li>Enter the access keys displayed on the left</li>
											</ol>

									</div>
									<div id='tab-14' class="item item-step-14">
										
									</div>
									<div id='tab-15' class="item item-step-15">
										
										<div class="cwps-video-holder">
											<a href="#" target="_blank">
												<iframe width="414" height="219" src="//www.youtube.com/embed/9EGB5FVgE3U?rel=0&amp;controls=0" frameborder="0" allowfullscreen></iframe>
											</a>
										</div>
										<B><span style="text-decoration:underline">Set S3 Policy</span></B>
										<BR>
											<ol>
											
												<li>For the created user, click on that user in IAM</li>
												<li>Once the user is created, click on that user</li>
												<li>At the bottom, click permissions</li>
												<li>Click Attach User Policy</li>
												<li>Select Custom Policy, and click Select</li>
												<li>Type in any name for the policy name</li>
												<li>In the Policy Document, copy and paste the following text:</li>
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
}</textarea></li>
												<li>Click Apply Policy</li>
<BR><BR>You can now access your S3 buckets through EASE!
											</ol>
									</div>
									<div id='tab-16' class="item item-step-16">
										<B><span style="text-decoration:underline">Create Upload Buckets</span></B>
										<BR>
											<i>You can specify buckets to upload to, but if you don't we will create the buckets automatically</i>
											<ol>
												<li><a href="https://console.aws.amazon.com/s3" target="_blank">https://console.aws.amazon.com/s3</a> and click Create Bucket twice, once for private buckets and once for public</li>
												<li>Type the bucket names on the section below this one on the left</li>
											</ol>
									</div>
									<div id='tab-17' class="item item-step-17">
										<B><span style="text-decoration:underline">Enter your bucket information from the previous step</span></B>
									</div>
								</div>
							</div>
							<div class="cwps-side-content-nav clearfix">
								<a class="prev">Prev Step</a>
								<a class="next">Next Step</a>
							</div>
						</div>
					</div>
					
					</div>
					<div style="margin:20px;text-align:right">
						<input type="submit" onclick="closeEaseSettingsModal();return false;" value="Cancel" class="btn btn-small" />
						<input type="submit" onclick="submitEASEModalData();return false;" value="Save" class="btn btn-small" />
					</div>
					
				</form>
			</div>
		</div>
	</div>

        </body>
        </html>
</div>
<!-- AMAZON SETTINGS MODAL END -->
       <script type="text/javascript">
	    
            function submitEASEModalData(){
		if (jQuery("#cwFormID").valid()) {
			data = { action : 'save_ease_settings_modal_values', ease_gapp_client_id: jQuery("#ease_gapp_client_id_modal").val(), ease_gapp_client_secret: jQuery("#ease_gapp_client_secret_modal").val(), ease_project_id: jQuery("#ease_project_id_modal").val(),ease_disable_db_access: jQuery("#ease_disable_db_access_modal").val(),ease_db_name: jQuery("#ease_db_name_modal").val(),ease_public_folder_upload_directory: jQuery("#ease_public_folder_upload_directory_modal").val(),ease_private_folder_upload_directory: jQuery("#ease_private_folder_upload_directory_modal").val(),ease_s3_bucket_private: jQuery("#ease_s3_bucket_private_modal").val(),ease_s3_bucket_public:jQuery("ease_s3_bucket_public_modal").val(),ease_s3_access_key:jQuery("#ease_s3_access_key_modal").val(),ease_s3_secret_access_key:jQuery("#ease_s3_secret_access_key_modal").val()};
			jQuery.post(ajaxurl, data, function(response) {
			       
			   jQuery("#ease_gapp_client_id").html(jQuery("#ease_gapp_client_id_modal").val());
			   jQuery("#ease_gapp_client_secret").html(jQuery("#ease_gapp_client_secret_modal").val());
			   jQuery("#ease_project_id").val(jQuery("#ease_project_id_modal").val());
			   closeEaseSettingsModal();
			});
		}

            }
	    
		$(document).ready(function() {
			$("#cwFormID").validate();
		});
		
		$( ".item" ).click(function() {
		  var class_list = $( this ).closest("li").attr("class");
		  step_num = class_list.replace("item-step item-step-","");
		 // console.log(step_num);
		  hide_prev_next(step_num);
		
		});
        </script>