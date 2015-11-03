<?php

namespace Pie\Modules\Http;

class HttpRequest{

    public
            $url         = '',
            $query       = [],
            $body        = '',
            $post        = [],
            $options     = [],
            $contentType = '';

    /**
     * Sets the url for the request
     * @param string $url   The url to the website the query string is optional
     * @return HttpRequest
     */
    public function setUrl($url){
        $pu = parse_url($url);

        $this->url = $pu['scheme'] . '://' . $pu['host'];

        if(isset($pu['query'])){
            $query       = [];
            parse_str($pu['query'], $query);
            $this->query = array_merge($this->query, $query);
        }

        return $this;
    }

    /**
     * Sets the query string for the request
     * @param array $query  An array of key values for the query string
     * @return HttpRequest
     */
    public function setQuery(array $query){
        $this->query = $query;
        return $this;
    }

    /**
     * Sets the post data for the request
     * @param array $post   An array of key values for the post data
     * @return HttpRequest
     */
    public function setPost(array $post){
        $this->post = $post;
        return $this;
    }

    /**
     * Sets the body for the request
     * @param mixed $body           Anything that will be passed in the body of the request
     * @param string $contentType   Sets the "Content-Type" header
     * @return HttpRequest
     */
    public function setBody($body, $contentType = 'Content-Type: text/html'){
        $this->body        = $body;
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * Sets the body for the request as a json string
     * @param mixed $data           Data that will be converted to a json string for the body of the request
     * @param string $contentType   Sets the "Content-Type" header
     * @return HttpRequest
     */
    public function setJsonBody($data, $contentType = 'Content-Type: application/json'){
        $this->body        = json_encode($data);
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * Sets the options for curl_setopt_array()
     * @param array $options    An array of extra options for curl_setopt_array()
     * @return HttpRequest
     */
    public function setOptions(array $options){
        $this->options = $options;
        return $this;
    }

}
