<?php

global $global_curl_response_header;
global $curl_result;

function curl_init($host = ""){
    if(function_exists('update_option')){
        update_option( 'ease_curl_error', true);
    }
    
    $object = new StdClass;
    if($host){
        $object->CURLOPT_URL = $host;
    }
    return $object;
}

function curl_reset($object){
    $object = new stdClass;
    return $object;
}

function curl_exec($object){
    if($object->CURLOPT_POST){
        $method = "post";
    }elseif($object->CURLOPT_PUT){
        $method = "put";
    }elseif($object->CURLOPT_CUSTOMREQUEST){
        $method = $object->CURLOPT_CUSTOMREQUEST;
    }elseif($object->CURLOPT_HTTPGET){
        $method = "get";
    }else{
        $method = "get";
    }
    
    $max_redirects = 20;
    
    if($object->CURLOPT_MAXREDIRS){
        $max_redirects = $object->CURLOPT_MAXREDIRS;
    }
    
    $follow_location = 1;
    
    if($object->CURLOPT_FOLLOWLOCATION === false || $object->CURLOPT_FOLLOWLOCATION === 0){
        $follow_location = 0;
    }
    
    if($object->CURLOPT_SSL_VERIFYPEER){
        $verify_peer = true;
    }else{
        $verify_peer = false;
    }
    
    $headers = false;
    
    if($object->CURLOPT_USERAGENT){
        $headers .= "User-Agent: " . $object->CURLOPT_USERAGENT . "\r\n";    
    }
    
    if($object->CURLOPT_HTTPHEADER){
        foreach($object->CURLOPT_HTTPHEADER as $header){
            $headers .= $header . "\r\n";
        }
    }
    
    $ignore_errors = true;
    if($object->CURLOPT_FAILONERROR){
        $ignore_errors = false;
    }
    
    if($object->CURLOPT_REFERER){
        $headers .= "Referer: " . $object->CURLOPT_REFERER . "\r\n";
    }
    
    if($object->CURLOPT_USERPWD){
        $headers .= "Authorization: Basic " . base64_encode($object->CURLOPT_USERPWD);    
    }
    
    if($object->CURLOPT_POSTFIELDS){
        $post_fields = $object->CURLOPT_POSTFIELDS;
    }else{
        $post_fields = false;
    }
    
    $timeout = 10;
    
    
    if($object->CURLOPT_TIMEOUT){
        $timeout = floatval($object->CURLOPT_TIMEOUT);
        
        if($timeout > 60){
            $timeout = 60.0;
        }
    }
    
    $ssl_check = parse_url($object->CURLOPT_URL,PHP_URL_SCHEME);
    
    if($ssl_check == "https"){
        $context = [
          'http' => [
            'method' => $method,
            'max_redirects' => $max_redirects,
            'follow_location'=>$follow_location,
            'ignore_errors'=>$ignore_errors,
            'timeout' => $timeout
          ],
          'ssl'=>[
            'verify_peer'=>$verify_peer
          ]
        ];        
    }else{
        $context = [
          'http' => [
            'method' => $method,
            'max_redirects' => $max_redirects,
            'follow_location'=>$follow_location,
            'ignore_errors'=>$ignore_errors,
            'timeout' => $timeout
          ]
        ];
    }
    
    if($headers){
        $context['http']['header'] = $headers;
    }

    if($post_fields){
        $context['http']['content'] = $post_fields;
    }
    
    if($_GET['debug_curl'] == "true"){
        syslog(LOG_WARNING, "cURL call to " . $object->CURLOPT_URL . " was performed using the following parameters: " . print_r($context,true) .  "  cURL calls are not fully supported in Google App Engine");
    }
    
    $context = stream_context_create($context);
    
    // Not sure if this should be used - refer to http://php.net/manual/en/context.ssl.php
    //if($object->CURLOPT_CAINFO){
    //    stream_context_set_option($context, 'ssl', 'local_cert', $object->CURLOPT_CAINFO);
    //}
    
    $result = file_get_contents($object->CURLOPT_URL, false, $context);

    $header_result = "";
    
    global $global_curl_response_header;
    $global_curl_response_header = $http_response_header;
    if($object->CURLOPT_HEADER){
        
        foreach($http_response_header as $header){
            $header_result .= $header . "\r\n";
        }
        
        if($header_result){
            $header_result .= "\r\n";
        }
    }
    
    $result = $header_result . $result;
    
    global $curl_result;
    
    $curl_result = $result;
    
    if($_GET['debug_curl'] == "true"){
        syslog(LOG_WARNING, "cURL result to " . $object->CURLOPT_URL . " was: " . print_r($result,true) .  "");
    }
    
    if($object->CURLOPT_RETURNTRANSFER){
        if($result){
            return $result;
        }else{
            return false;
        }
    }else{
        if($result){
            echo $result;
        }else{
            return false;
        }
    }
}

function curl_setopt($object,$key,$value){
    $object->$key = $value;
    return $object;
}

function curl_setopt_array($object,$options){
    foreach($options as $key=>$value){
        $object = curl_setopt($object,$key,$value);
    }
    
    return $object;
}

function curl_getinfo($object,$option){
    global $global_curl_response_header;
    $response_header = $http_response_header;
    $matches = array();
    
    $value = false;
    
    if($option == CURLINFO_HTTP_CODE){
        preg_match('#HTTP/\d+\.\d+ (\d+)#', $global_curl_response_header[0], $matches);
        $value = $matches[1];
    }elseif($option == CURLINFO_CONTENT_TYPE){
        preg_match('/(Content-Type: )(.*+)/', $global_curl_response_header[6], $matches);
        if($matches[2]){
            $value = $matches[2];
        }  
    }elseif($option == CURLINFO_CONTENT_LENGTH_DOWNLOAD){
        preg_match('/(Content-Length: )(\d+)/', $global_curl_response_header[4], $matches);
        if($matches[2]){
            $value = $matches[2];
        }    
    }elseif($option == CURLINFO_HEADER_SIZE){
        $value = strlen(implode("\r\n",$global_curl_response_header));
    }else{
        syslog(LOG_WARNING, "Unsupported curl_getinfo option " . $option . "");
    }
    
    return $value;
}

function curl_close($object){
    
}

function curl_error($object){
    global $curl_result;
    
    if($curl_result === false){
        // We could add recent logs to this function
        return "Could not retrieve page.  cURL is not fully supported on Google App Engine, but please check your error logs for warnings to determine the cause of this issue";
    }
    
}

function curl_copy_handle($object){
    return $object;
}

function curl_errno($object){
    return 0;
}

function curl_escape($object, $str){
    return urlencode($str);
}

function curl_unescape($object,$str){
    return urldecode($str);
    unsupported_curl_warning(__FUNCTION__,func_get_args());
}

/**
 * Unsupported functions, but at least nothing with fail now
 * 
 **/
// Should look into this one further
function curl_file_create(){
    unsupported_curl_warning(__FUNCTION__,func_get_args());
    return false;
}

function curl_pause($object){
    unsupported_curl_warning(__FUNCTION__,func_get_args());
}

function curl_share_close($object){
    unsupported_curl_warning(__FUNCTION__,func_get_args());
}

function curl_share_init(){
    unsupported_curl_warning(__FUNCTION__,func_get_args());
}

function curl_share_setopt($object,$option,$value){
    unsupported_curl_warning(__FUNCTION__,func_get_args());
}

function curl_strerror($errornum){
    unsupported_curl_warning(__FUNCTION__,func_get_args());
}


// Function is disabled as it is what is used to detect if cURL is in place in Google PHP API Client
//function curl_version($age){
//    unsupported_curl_warning(__FUNCTION__,func_get_args());
//}

function curl_multi_add_handle($object1,$object2){
    unsupported_curl_warning(__FUNCTION__,func_get_args());
}

function curl_multi_close($object1){
    unsupported_curl_warning(__FUNCTION__,func_get_args());
}

function curl_multi_exec($object1,$still_running){
    unsupported_curl_warning(__FUNCTION__,func_get_args());
}

function curl_multi_getcontent($object1){
    unsupported_curl_warning(__FUNCTION__,func_get_args());
}

function curl_multi_info_read($object1,$msgs_in_queue){
    unsupported_curl_warning(__FUNCTION__,func_get_args());
}

function curl_multi_init(){
    unsupported_curl_warning(__FUNCTION__,func_get_args());
}

function curl_multi_remove_handle($object1,$object2){
    unsupported_curl_warning(__FUNCTION__,func_get_args());
}

function curl_multi_select($object1,$timeout){
    unsupported_curl_warning(__FUNCTION__,func_get_args());
}

function curl_multi_setopt($object1,$option,$value){
    unsupported_curl_warning(__FUNCTION__,func_get_args());
}

function curl_multi_strerror($errornum){
    unsupported_curl_warning(__FUNCTION__,func_get_args());
}

function unsupported_curl_warning($function_name,$parameters){
    syslog(LOG_WARNING, "Unsupported cURL function call " . $function_name . " with params " . print_r($parameters,true));
}

?>