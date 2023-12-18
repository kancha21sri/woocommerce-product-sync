<?php

namespace App\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use Illuminate\Support\Facades\Log;

trait APIConnection
{
    /**
     * API Prefix
     *
     * @var string
     */
    private  $apiPreFix = 'wp-json/wc/v3';

    /**
     * Connect the Woocommerce API
     *
     * @param  string $request_type
     * @param string $end_point
     * @param array $parameters
     * @param  string $body_type
     * @return false|\Psr\Http\Message\ResponseInterface
     */
    public function connectWoocommerceApi($request_type,$end_point,$parameters = [],$body_type = 'query')
    {
        try{
            $client = new Client([
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode(config('woocommerce_api_config.consumer_key').':'.config('woocommerce_api_config.consumer_secret') )
                ]
            ]);

            $response = $client->request($request_type,  config('woocommerce_api_config.shop_url').$this->apiPreFix.$end_point, [$body_type => $parameters]);

            if ($response->getStatusCode() >= 300) {
                Log::channel('woocommerce_api')->error('This is not exception related issue and response code is '.$response->getStatusCode());
                $response = false;
            }

        }catch (TooManyRedirectsException|\Exception $e) {
            Log::channel('woocommerce_api')->error("Too Many Redirection related error ".$e->getMessage());
            $response = false;

        } catch (GuzzleException $e) {
            Log::channel('woocommerce_api')->error("Guzzle related error " .$e->getMessage());
            $response = false;
        }
        return $response;
    }

    /**
     * Connect to products end point and  pull the product data
     *
     * @return false|mixed
     */
    public function connectProductsEndPoint(){
        $params = [
            'page' => 1,
            'per_page' => 15
        ];

        $response = $this->connectWoocommerceApi('GET','/products',$params);
        if($response ){
            return json_decode($response->getBody()->getContents(), true);

        } else {
            return false;
        }
    }

}
