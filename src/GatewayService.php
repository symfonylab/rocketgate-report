<?php

namespace SymfonyLab\RocketGate;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use SymfonyLab\RocketGate\Request\RequestInterface;

class GatewayService implements GatewayServiceInterface
{
    /**
     * @var string
     */
    private $devLink = 'https://dev-my.rocketgate.com/com/rocketgate/gateway/xml/';
    /**
     * @var string
     */
    private $prodLink = 'https://my.rocketgate.com/com/rocketgate/gateway/xml/';
    /**
     * @var string
     */
    private $baseLink = 'https://my.rocketgate.com';
    /**
     * @var Client
     */
    private $http;
    /**
     * @var string
     */
    private $rgLogin;
    /**
     * @var string
     */
    private $rgPassword;

    public function __construct(string $rgLogin = null, string $rgPassword = null)
    {
        $this->http = $client = new Client([
            'base_uri' => $this->prodLink
        ]);
        $this->rgLogin = $rgLogin;
        $this->rgPassword = $rgPassword;
    }

    public function request(RequestInterface $request)
    {
        $data = null;
        try {
            $response = $this->http->get($request->getLink(), [
                RequestOptions::QUERY => $request->getParams()
            ]);
            if (200 === $response->getStatusCode()) {
                $data = $response->getBody()->getContents();
            }
            if ($data) {
                $xmlCheck = substr($data, 0, 1);
                if ($xmlCheck === '<') {
                    $xml = simplexml_load_string($data);
                    if ($xml !== false) {
                        if (isset($xml->code) && $xml->code === 401) {
                            try {
                                $this->clearAPIBlocking($request->getMerchant());
                            } catch (\Exception $e) {
                            } finally {
                                sleep(1);
                                return $this->request($request);
                            }
                        } else {
                            $data = $request->handleResponse($data);
                        }
                    }
                } else {
                    $data = $request->handleResponse($data);
                }
            }
        } catch (GuzzleException $e) {
            sleep(1);
            return $this->request($request);
        }

        return $data;
    }

    private function clearAPIBlocking(MerchantInterface $merchant)
    {
        $merchId = $merchant->getId();
        $client = new Client([
            'base_uri' => $this->baseLink,
            'http_errors' => false,
            'cookies' => true
        ]);
        if (!$this->rgLogin || !$this->rgPassword){
            return;
        }
        $response = $client->post('/mc/secure/index.cfm', [
            'form_params' => [
                'j_username' => $this->rgLogin,
                'j_password' => $this->rgPassword,
                'tmz' => 'GMT',
                'mg_id' => $merchId
            ],
        ]);
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('RocketGate Clear API request: Failed to login to mission control.');
        }

        // Get page to fetch _cf_clientid
        $body = $client->get('/mc/secure/users/gw_access_logs/index.cfm')->getBody()->getContents();
        preg_match_all("/_cf_clientid='([^']+)';/", $body, $matches);
        if (!empty($matches[1][0])) {
            $CFClientID = $matches[1][0];
        } else {
            throw new \Exception('RocketGate Clear API request: failed to fetch _cf_clientid.');
        }

        $from = (new \DateTimeImmutable('-1 month'))->format('m/d/Y');
        $to = (new \DateTimeImmutable('+1 day'))->format('m/d/Y');

        $response = $client->get('/mc/secure/users/gw_access_logs/clear_logs.cfm', [
            'query' => [
                'merch_id' => $merchId,
                'fromDate' => $from,
                'toDate' => $to,
                '_cf_containerId' => 'details',
                '_cf_nodebug' => true,
                '_cf_clientid' => $CFClientID,
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('RocketGate Clear API request: ' . $response->getBody()->getContents());
        }

        $data = $response->getBody()->getContents();
        if (strpos($data, 'Record Updated') === false) {
            throw new \Exception('RocketGate Clear API request: failed to clear api.');
        }
    }
}
