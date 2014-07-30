var script_object = {};
   script_object["form_helper"] = "<# start form for google spreadsheet \"Time Cards\";\r\n    \/\/javascript function call\r\n\twhen creating call formValidation();\r\n\t\/\/use a captcha field to validate real user\r\n\trestrict posts to \"Cloudward\" in captcha;\r\n\twhen creating set start_time to \"<# system.date_time_short #>\";\r\n    when creating set status to \"clocked in\";\r\n    when updating set end_time to \"<# system.date_time_short #>\";\r\n    when updating set hours to\r\n       <# system.timestamp #> - <# form.start_time as timestamp #> as time \"hh:mm:ss\";\r\n    when updating set status to \"clocked out\";\r\n    \/\/redirect to another page when done\r\n    when done redirect to \"\/timesheet\";\r\n#>\r\nEmployee Name: <input type=\"text\" <# employee_name #> value=\"Bob Smith\" \/><br \/>\r\nStart Time: <input type=\"text\" <# start_time #> \/><br \/>\r\nEnd Time: <input type=\"text\" <# end_time #> \/><br \/>\r\n<input type=\"button\" <# create button #> value='Clock-In' \/>\r\n<input type=\"button\" <# update button #> value='Clock-Out' \/>\r\n<# end form #>\t\t\t    \r\n\t\t\t  ";script_object["dbform_helper"] = "<# start form for tablename <#[url.edit_id]#>; \r\n    \/\/javascript function call\r\n\twhen creating call formValidation();\r\n\t\/\/use a captcha field to validate real user\r\n\trestrict posts to \"Cloudward\" in captcha;\r\n\twhen creating set tablename.start_time to \"<# system.date_time_short #>\";\r\n    when creating set tablename.status to \"clocked in\";\r\n    when updating set tablename.end_time to \"<# system.date_time_short #>\";\r\n    when updating set tablename.hours to\r\n       <# system.timestamp #> - <# form.start_time as timestamp #> as time \"hh:mm:ss\";\r\n    when updating set tablename.status to \"clocked out\";\r\n    \/\/redirect to another page when done\r\n    when done redirect to \"\/timesheet\";\r\n#>\r\nEmployee Name: <input type=\"text\" <# tablename.employee_name #> value=\"Bob Smith\" \/><br \/>\r\nStart Time: <input type=\"text\" <# tablename.start_time #> \/><br \/>\r\nEnd Time: <input type=\"text\" <# tablename.end_time #> \/><br \/>\r\n<input type=\"button\" <# create button #> value='Clock-In' \/>\r\n<input type=\"button\" <# update button #> value='Clock-Out' \/>\r\n<# end form #>\t\t\t    \r\n\t\t\t  \t\t\t    \r\n\t\t\t  \r\n\t\t\t\t\t\t\t    \r\n\t\t\t  ";script_object["file_upload_helper"] = "<# start form for webimages <#[url.edit]#>; \r\nwhen creating set webimages.created_on to \"<# system.date_time_short #>\";\r\nwhen creating redirect to \"\/?page=file_upload_list\";\r\nwhen updating redirect to \"\/?page=file_upload_list\";\r\nwhen deleting redirect to \"\/?page=file_upload_list\";\r\n#> \r\n<p>File Name (for admin use)<br>\r\n<input type=\"text\" style=\"width:400px\" <# webimages.name #> >\r\n\r\n<p>File\/Image<br>\r\n\r\n<input type=\"text\" style=\"width:400px\"  <# webimages.image1 #> \/>\r\n<!--##### you must provide a Google Drive Folder ID for the file to be uploaded into #####-->\r\n<input type=\"file\" <# upload file to googledrive \"\/<#[webstyle.uploads_folder_id]#>\" for webimages.image1 #> \/>\r\n\r\n\r\n<input type=\"button\" value=\"create\" <# create button #> >\r\n<input type=\"button\" value=\"update\" <# update button #> >\r\n<input type=\"button\" value=\"delete\" <# delete button #> >\r\n<p>\r\n<# end form #> \r\n";script_object["sheet_list_helper"] = "<# start list for google spreadsheet \"Test Spreadsheet\"; \r\n    \/\/filter the list of records with an \"include\" statement\r\n    include when payment method is \"Debit\";\r\n    \/\/Limit the list to only show 20 rows per page\r\n\tshow 20 rows per page;\r\n\t\/\/hide pager: \"top\", \"bottom\", \"both\"\r\n\thide bottom pager;\r\n\t\/\/set sorting on one row\r\n\tsort by Customer Name;\r\n#>\r\n\r\n<# start header #>\r\n<table border='1' cellpadding='2' cellspacing='0'>\r\n\t<tr style='font-size:11pt;'>\r\n\t\t<th>ID<\/th>\r\n\t\t<th>Time<\/th>\r\n\t\t<th>Type<\/th>\r\n\t\t<th>Customer Name<\/th>\r\n\t\t<th>Total<\/th>\r\n\t\t<th>Payment<br \/>Method<\/th>\r\n\t<\/tr>\r\n<# end header #>\r\n\r\n<# start row #>\r\n\t<tr style='vertical-align:top; font-size:10pt;'>\r\n\t\t<td style='font-size:8pt; font-family:monospace;'><a href='\/spreadsheet_form?edit=<# easerowid #>'><# easerowid #><\/a><\/td>\r\n\t\t<td><# Time #><\/td>\r\n\t\t<td style='padding-left:4px; padding-right:6px;'><# Type #><\/td>\r\n\t\t<td style='padding-left:4px; padding-right:6px;'><# Customer Name #><\/td>\r\n\t\t<td style='text-align:right; padding-right:6px;'><# Total #><\/td>\r\n\t\t<td style='padding-left:4px; padding-right:6px;'><# Payment Method #><\/td>\r\n\t<\/tr>\r\n<# end row #>\r\n\r\n<# start footer #>\r\n<\/table>\r\n<# end footer #>\r\n\r\n<# no results #>\r\n<hr \/>No Results\r\n<# end no results #>\r\n\r\n<# end list #>\t\t\t    \r\n\t\t\t  ";script_object["db_list_helper"] = "<# start list for articles;\r\n    \/\/filter your list with an \"include\" statement\r\n    include when sku ~ \"<#[request.regex]#>\";\r\n    \/\/set a total value from the detail items returned by the list. \r\n\tset overall_total to total of orders.total;\r\n    \/\/showing 10 rows per page\r\n\tshow 10 rows per page;\r\n\t\/\/can hide pager: \"top\", \"bottom\", \"both\"\r\n\thide bottom pager;\r\n#>\r\n\r\n<# start header #>\r\n<table border='1' cellpadding='2' cellspacing='0'>\r\n\t<tr style='font-size:12pt;'>\r\n\t\t<th>ID<span style='font-weight:normal; font-size:9pt; padding-left:10px;'>(click to edit)<\/span><\/th>\r\n\t\t<th>Created On<\/th>\r\n\t\t<th>Status<\/th>\r\n\t\t<th>Headline<\/th>\r\n\t\t<th>Descriptive Tests<\/th>\r\n\t\t<th>Immediate Tests<\/th>\r\n\t<\/tr>\r\n<# end header #>\r\n\r\n<# start row #>\r\n\t<tr style='font-size:10pt;'>\r\n\t\t<td style='font-size:8pt; font-family:monospace;'><a href='\/article?edit=<# id #>'><# id #><\/a><\/td>\r\n\t\t<td style='padding-top:3px; padding-left:5px; padding-right:5px; font-size:9pt;'><# created_on #><\/td>\r\n\t\t<td style='padding-left:4px; padding-right:7px;'><# status as html #><\/td>\r\n\t\t<td style='padding-left:4px; padding-right:7px;'><# headline as html #><\/td>\r\n\t\t<td style='padding-left:5px; padding-right:5px;'>\r\n\t\t\t<a href='\/article_apply?id=<# id #>'>Apply<\/a>&nbsp;&nbsp;\r\n\t\t\t<a href='\/article_update?id=<# id #>&index=<#[url.index as html]#>'>Status\u2192\"reviewed\"<\/a>&nbsp;&nbsp;\r\n\t\t\t<a href='\/article_delete?id=<# id #>&index=<#[url.index as html]#>' onclick=\"return confirm('Confirm Delete?');\">Delete<\/a>\r\n\t\t<\/td>\r\n\t\t<td style='padding-left:5px; padding-right:5px;'>\r\n\t\t\t<a href='\/article_update_immediate?id=<# id #>&index=<#[url.index as html]#>'>Status\u2192\"reviewed\"<\/a>&nbsp;&nbsp;\r\n\t\t\t<a href='\/article_delete_immediate?id=<# id #>&index=<#[url.index as html]#>' onclick=\"return confirm('Confirm Delete?');\">Delete<\/a>\r\n\t\t<\/td>\r\n\t<\/tr>\r\n<# end row #>\r\n\r\n<# start footer #>\r\n<\/table>\r\n<# end footer #>\r\n\r\n<# no results #>\r\n<hr \/>No Results\r\n<# end no results #>\r\n\r\n<# end list #>  \r\n\t\t\t\t\t\t\t    \r\n\t\t\t  \t\t\t    \r\n\t\t\t  ";script_object["file_list_helper"] = "<# start list for webimages; \r\ninclude when banner <> \"yes\";\r\nshow 25 rows per page;\r\n#>\r\n\r\n<# start header #>\r\n<table border='1' cellpadding='2' cellspacing='0'>\r\n\t<tr>\r\n\t\t<th>Name<\/th>\r\n\t\t<th>Created On<\/th>\r\n\t\t<th>File<\/th>\r\n\t\t<th>File Location<\/th>\r\n\t<\/tr>\r\n<# end header #>\r\n\r\n<# start row #>\r\n    \t<tr>\r\n\t\t<td><a href='\/?page=file_upload_edit&edit=<# webimages.id #>'><# webimages.name #> \r\n\t\t    <span style='font-weight:normal; font-size:9pt; padding-left:10px;'>[edit]<\/span><\/a><\/td>\r\n\t\t<td><# webimages.created_on #><\/td>\r\n\t\t<!--###### \r\n\t\t    When you upload an image, xxx_drive_web_url will be automatically added to your db table.\r\n\t\t        \"xxx\" is whatever you named your upload field on the input form. This is the url\r\n\t\t        that you would use to access the file from the web.\r\n\t\t######-->\r\n\t\t<td><a href=\"<# webimages.image1_drive_web_url #>\"  target=\"_blank\"><img src=\"<# webimages.image1_drive_web_url #>\"  style=\"width:125px;\" \/><\/a>\r\n\t\t    <br \/><# webimages.image1 #>\r\n\t\t<\/td>\r\n\t\t<td><# webimages.image1_drive_web_url #><\/td>\r\n\t<\/tr>\r\n<# end row #>\r\n\r\n<# start footer #>\r\n<\/table>\r\n<# end footer #>\r\n\r\n<# no results #>\r\n\t<h2>There are no files uploaded yet...<\/h2>\r\n<# end no results #>\r\n\r\n<# end list #>";script_object["survey_helper"] = "    \r\n\t\t\t\t<h2>Let Us Know How We Are Doing<\/h2>\r\n<hr>\r\n\r\n<# start form for googlespreadsheet \"Survey Spreadsheet\";\r\n\/\/ specifies what sheet to save results to\r\n\r\n\/\/ inserts the new record at row two and shifts the rest of the records down\r\n\r\nwhen creating redirect to \"\/\";\r\n#>\r\n<p>\t\r\n<table>\r\n\t<tr>\r\n\t\t<td><u>Question:<\/u><\/td>\r\n\t\t<td>1<\/td>\r\n\t\t<td>2<\/td>\r\n\t\t<td>3<\/td>\r\n\t\t<td>4<\/td>\r\n\t\t<td>5<\/td>\r\n\t\t\r\n\t<\/tr>\r\n\t<tr>\r\n\t\t<td width=\"300\">\r\n\t\tRate Your Overall Experience: \r\n\t\t<\/td>\r\n\t\t<td>\r\n\t\t\t<input type=\"radio\"  value=\"1\" <# row.c #> >\r\n\t\t<\/td>\r\n\t\t<td>\r\n\t\t\t<input type=\"radio\"  value=\"2\" <# row.c #> >\r\n\t\t<\/td>\r\n\t\t<td>\r\n\t\t\t<input type=\"radio\"  value=\"3\" <# row.c #> >\r\n\t\t<\/td>\r\n\t\t<td>\r\n\t\t\t<input type=\"radio\"  value=\"4\" <# row.c #> >\r\n\t\t<\/td>\r\n\t\t<td>\r\n\t\t\t<input type=\"radio\"  value=\"5\" <# row.c #> >\r\n\t\t<\/td>\r\n\t<\/tr>\r\n\t<tr>\r\n\t\t<td width=\"300\">\r\n\t\tWillingness to Recommend: \r\n\t\t<\/td>\r\n\t\t<td>\r\n\t\t\t<input type=\"radio\"  value=\"1\" <# row.d #> >\r\n\t\t<\/td>\r\n\t\t<td>\r\n\t\t\t<input type=\"radio\"  value=\"2\" <# row.d #> >\r\n\t\t<\/td>\r\n\t\t<td>\r\n\t\t\t<input type=\"radio\"  value=\"3\" <# row.d #> >\r\n\t\t<\/td>\r\n\t\t<td>\r\n\t\t\t<input type=\"radio\"  value=\"4\" <# row.d #> >\r\n\t\t<\/td>\r\n\t\t<td>\r\n\t\t\t<input type=\"radio\"  value=\"5\" <# row.d #> >\r\n\t\t<\/td>\r\n\t<\/tr>\r\n<\/table>\r\n<p>Name:<br>\r\n<input type=\"text\"  style=\"width:300px\" <# row.a #> >\r\n\r\n<p>Email:<br>\r\n<input type=\"text\"  style=\"width:300px\" <# row.b #> >\r\n\r\n<p>Comments<br>\r\n\t<textarea style=\"width:400px;height:105px\"  <# row.e #> >\r\n\t\r\n<p>\r\n<input type=\"button\" value=\"Submit\" <# create button #> >\r\n<p>\r\n\r\n<# end form #>\t\t\t    \r\n\t\t\t  ";/*Plugin definition*/
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

	function loadScriptHelper(){
		jQuery("#script-helper-modal-link").click();
		jQuery("#TB_ajaxContent").css("height","90%");
	}
	
	function loadScriptHelperText(script_name) {
		if (script_name) {
		  jQuery("#script-helper-textarea").val(script_object[script_name]);
		}
	}

      function loadScripts(){
              var myOptions = {
              form_helper:"Simple Form to Sheet",
         dbform_helper:"Simple form to database",
         file_upload_helper:"Form to upload a file",
         sheet_list_helper:"SImple List from Sheet",
         db_list_helper:"Simple List from DB",
         file_list_helper:"List Files Uploaded to Drive",
         survey_helper:"Survey",
          };
              
              var _select = jQuery("<select>");
              jQuery.each(myOptions, function(val, text) {
                  _select.append(
                          jQuery("<option></option>").val(val).html(text)
                      );
              });
              jQuery("#script_helper").append(_select.html());
      }
      
     function insertHelperScript(){
	jQuery("#content").insertAtCaret(jQuery("#modal_script_helper_textarea").val());
	jQuery("#content-tmce").remove();
	tb_remove();
      }
      
      function closeHelperScriptModal(){
	tb_remove();
      }
   