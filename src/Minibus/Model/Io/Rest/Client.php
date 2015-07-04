<?php
namespace Minibus\Model\Io\Rest;

use Zend\Http\Client as HttpClient;
use Zend\Http\Request;
use Zend\Stdlib\Parameters;
use Zend\Validator\Uri;
use Zend\Http\Response;

class Client
{

    /**
     *
     * @var \Zend\Http\Client
     */
    protected $httpClient;

    /**
     *
     * @var \Zend\Http\Response
     */
    protected $lastResponse;

    /**
     *
     * @var string
     */
    private $baseUrl;

    /**
     *
     * @var string
     */
    private $key;

    /**
     *
     * @param HttpClient $httpClient            
     * @param string $enableRestClientSslVerification            
     * @param string $baseUrl            
     */
    public function __construct(HttpClient $httpClient, $enableRestClientSslVerification = true, $baseUrl = null)
    {
        $this->httpClient = $httpClient;
        $this->httpClient->getAdapter()->setOptions(array(
            'curloptions' => array(
                CURLOPT_SSL_VERIFYPEER => $enableRestClientSslVerification,
                CURLOPT_SSL_VERIFYHOST => $enableRestClientSslVerification,
                CURLOPT_TIMEOUT => 30000
            )
        ));
        if (! is_null($baseUrl))
            $this->setBaseUrl($baseUrl);
    }

    public function setBaseUrl($baseUrl)
    {
        $validator = new Uri();
        
        if ($validator->isValid($baseUrl)) {
            $this->baseUrl = $baseUrl;
        } else {
            $reasons = '';
            foreach ($validator->getMessages() as $message) {
                $reasons .= "$message\n";
            }
            throw new \Exception($reasons);
        }
    }

    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    private function getCompleteUrl($path)
    {
        if (empty($this->baseUrl))
            throw new \Exception("L'url du client Rest n'a pas été fournie.");
        if (! empty($path))
            if (substr($path, 0, 1) != '/')
                $path = '/' . $path;
        return $this->baseUrl . $path;
    }

    public function get($path, $data = null)
    {
        $url = $this->getCompleteUrl($path);
        return $this->dispatchRequestAndDecodeResponse($url, "GET", $data);
    }

    public function post($path, $data)
    {
        $url = $this->getCompleteUrl($path);
        return $this->dispatchRequestAndDecodeResponse($url, "POST", $data);
    }

    public function put($path, $data)
    {
        $url = $this->getCompleteUrl($path);
        return $this->dispatchRequestAndDecodeResponse($url, "PUT", $data);
    }

    public function delete($path)
    {
        $url = $this->getCompleteUrl($path);
        return $this->dispatchRequestAndDecodeResponse($url, "DELETE");
    }

    protected function dispatchRequestAndDecodeResponse($url, $method, $data = null)
    {
        $request = new Request();
        $this->lastResponse = null;
        $request->getHeaders()->addHeaders(array(
            'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
            'Accept' => 'application/json',
            'User-Agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:37.0) Gecko/20100101 Firefox/37.0'
        ));
        $request->setUri($url);
        $request->setMethod($method);
        if (is_null($data))
            $data = array();
        if (isset($this->key))
            $data["auth"] = $this->key;
        if ($method == "POST" || $method == "PUT") {
            $request->setPost(new Parameters($data));
            if (isset($this->key))
                $request->setQuery(new Parameters(array(
                    'auth' => $this->key
                )));
        } else {
            $request->setQuery(new Parameters($data));
        }
        
        $this->lastResponse = $this->httpClient->send($request);
        
        if ($this->lastResponse->isSuccess())
            return json_decode($this->lastResponse->getBody(), true);
        else
            return array(
                'error' => true,
                'headers' => array(
                    "code" => $this->lastResponse->getStatusCode(),
                    "reasons" => $this->lastResponse->getReasonPhrase()
                ),
                'body' => json_decode($this->lastResponse->getBody(), true)
            );
    }

    /**
     *
     * @return \Zend\Http\Response
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }
}
