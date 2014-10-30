<?php

            $content = preg_replace_callback(
                                 '/<#\s*include\s*("|\')(.*?)("|\')\s*[\.;]{0,1}\s*#>/is',
                                 function($matches) use ($content){
                                 
                                 $temp_url_array = get_option("ease_replace_urls");
                                 $espx_page = str_replace(".espx","",$matches[2]);
                                 $post_id = $temp_url_array[$espx_page];
                                 $post_object = get_post($post_id);
                                 return $post_object->post_content;
                                 },
                                 $content
                                 );
            
            // If there is an email includes in here, parse them out and run them
           $content = preg_replace_callback(
                                 '/bodypage\s*=\s*"(.*?)"\s*;/is',
                                 function($matches) use ($content){
                                    $url = parse_url($matches[0]);

                                    //$url = str_replace("bodypage = \"","",$url['path']);
                                    $url = preg_replace('/(bodypage\s*=\s*")/',"", $url['path']); 
                                    $replace_urls1 = get_option( "ease_replace_urls");

                                    $post = get_post($replace_urls1[$url]);
                                    $ease_core1 = ease_load_core();
                                    return "body=\"" .replace_ease_urls($ease_core1->process_ease($post->post_content,true)) . "\";";
                                 },
                                 $content
                                 );
           
           ?>