<?php

namespace Object69\Modules\Http69;

use Exception;
use Object69\Core\Library\Arrays\ArrayList;
use Object69\Modules\Http69\Result;

class Http{

    protected
            $handles = [];

    public function create(HttpRequest $info){
        if(!filter_var($info->url, FILTER_VALIDATE_URL)){
            throw new Exception('Invalid Http URL "' . $info->url . '"');
        }
        $query = count($info->query) > 0 ? '?' . http_build_query($info->query) : '';
        $ch    = curl_init($info->url . $query);
        // Boolean settings
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // String settings
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_ALL);
        // Set the content-type
        if(strlen($info->contentType) > 0){
            curl_setopt($ch, CURLOPT_HTTPHEADER, [$info->contentType]);
        }
        // Set any extra options
        if(count($info->options) > 0){
            curl_setopt_array($ch, $info->options);
        }

        // Set post data if post data is present
        if(count($info->post) > 0){
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($info->post));
        }

        if(strlen($info->body) > 0){
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($info->body));
        }

        $this->handles[] = $ch;
        return $this;
    }

    /**
     * Execute all the created handles
     * @return ArrayList
     */
    public function exec(){
        $responses = new ArrayList(Result::class);
        if($this->handles > 0){
            $mh = curl_multi_init();
            foreach($this->handles as $ch){
                curl_multi_add_handle($mh, $ch);
            }
            $running = null;
            do{
                curl_multi_exec($mh, $running);
                curl_multi_select($mh);
            }while($running > 0);

//            exit('here');
//            while($active && $mrc == CURLM_OK){
//                if(curl_multi_select($mh) != -1){
//                    do{
//                        $mrc = curl_multi_exec($mh, $active);
//                    }while($mrc == CURLM_CALL_MULTI_PERFORM);
//                }
//            }
            foreach($this->handles as $ch){
                $body = curl_multi_getcontent($ch);
                $info = curl_getinfo($ch);

                $result                        = new Result();
                $result->body                  = $body;
                $result->url                   = isset($info['url']) ? $info['url'] : '';
                $result->contentType           = isset($info['content_type']) ? $info['content_type'] : '';
                $result->httpCode              = isset($info['http_code']) ? (int)$info['http_code'] : 0;
                $result->headerSize            = isset($info['header_size']) ? $info['header_size'] : '';
                $result->requestSize           = isset($info['request_size']) ? $info['request_size'] : '';
                $result->filetime              = isset($info['filetime']) ? $info['filetime'] : '';
                $result->sslVerifyResult       = isset($info['ssl_verify_result']) ? $info['ssl_verify_result'] : '';
                $result->redirectCount         = isset($info['redirect_count']) ? $info['redirect_count'] : '';
                $result->totalTime             = isset($info['total_time']) ? $info['total_time'] : '';
                $result->namelookupTime        = isset($info['namelookup_time']) ? $info['namelookup_time'] : '';
                $result->connectTime           = isset($info['connect_time']) ? $info['connect_time'] : '';
                $result->pretransferTime       = isset($info['pretransfer_time']) ? $info['pretransfer_time'] : '';
                $result->sizeUpload            = isset($info['size_upload']) ? $info['size_upload'] : '';
                $result->sizeDownload          = isset($info['size_download']) ? $info['size_download'] : '';
                $result->speedDownload         = isset($info['speed_download']) ? $info['speed_download'] : '';
                $result->speedUpload           = isset($info['speed_upload']) ? $info['speed_upload'] : '';
                $result->downloadContentLength = isset($info['download_content_length']) ? $info['download_content_length'] : '';
                $result->uploadContentLength   = isset($info['upload_content_length']) ? $info['upload_content_length'] : '';
                $result->starttransferTime     = isset($info['starttransfer_time']) ? $info['starttransfer_time'] : '';
                $result->redirectTime          = isset($info['redirect_time']) ? $info['redirect_time'] : '';
                $result->certinfo              = isset($info['certinfo']) ? $info['certinfo'] : '';
                $result->primaryIp             = isset($info['primary_ip']) ? $info['primary_ip'] : '';
                $result->primaryPort           = isset($info['primary_port']) ? $info['primary_port'] : '';
                $result->localIp               = isset($info['local_ip']) ? $info['local_ip'] : '';
                $result->localPort             = isset($info['local_port']) ? $info['local_port'] : '';
                $result->redirectUrl           = isset($info['redirect_url']) ? $info['redirect_url'] : '';
                $result->requestHeader         = isset($info['request_header']) ? $info['request_header'] : '';

                $responses->add($result);

                curl_multi_remove_handle($mh, $ch);
            }
            curl_multi_close($mh);
        }
        return $responses;
    }

    public function reset(){
        $this->handles = [];
    }

}
