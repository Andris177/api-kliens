<?php
 
namespace App\RestApiClient;
 
use App\Interfaces\ClientInterface;
use Exeception;
 
class Client //implements ClientInterface 
{
 
    const API_URL = 'http://localhost:8000/';
    /** 
     * The whole url including host, api uri and jql query.
     * @var string
    */
    protected $url;
 
    function __construct($url = self::API_URL)
    {
        $this->url = $url;
    }
 
    public function getUrl() {
        return $this->url;
    }
 
    function get($route, array $query = [])
    {
        $url = $this->getUrl() . $route;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $response = curl_exec($curl);
        if  (!$response) {
            trigger_error(curl_error($curl));
        }
        curl_close($curl);
 
        return json_decode($response, TRUE);
    }

    function post($url, array $data = [])
    {
    $json = json_encode($data);
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_URL, $this->url . $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($json)
    ]);
    curl_setopt($curl, CURLOPT_POST, TRUE);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $json);

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE); // HTTP státuszkód
    $curlError = curl_error($curl); // cURL hiba üzenet
    curl_close($curl);

    if ($curlError) {
        echo "cURL Error: $curlError";
        return null;
    }

    // Ellenőrizzük a HTTP válaszkódot
    if ($httpCode >= 400) {
        echo "HTTP Error: $httpCode. Response: $response";
        return null;
    }

    // Ellenőrizzük az API válasz érvényességét
    if ($response === false || $response === null) {
        echo "Invalid response or empty response received.";
        return null;
    }

    $decodedResponse = json_decode($response, true);

    if ($decodedResponse === null) {
        echo "Failed to decode JSON response. Response was: $response";
        return null;
    }

    if (isset($decodedResponse['error'])) {
        echo "API Error: " . $decodedResponse['error'];
    }

    return $decodedResponse;
    }

    function delete($url, $id)
    {
        $json = json_encode(['id' => $id]);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_URL, $this->url . $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
        
        $response = curl_exec($curl);
        if (!$response)
        {
            $error = curl_error($curl);
            if ($error)
            {
                trigger_error($error);
            }
        }
        curl_close($curl);
        return json_decode($response, True);
    }
    function put($url, array $data = [])
    {
        $json = json_encode($data);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_URL, $this->url . $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
        if ($response === FALSE) {
            $error = curl_error($curl);
            echo "cURL Error (PUT): " . $error; 
        } else {
            // Ellenőrizzük a JSON választ
            $decodedResponse = json_decode($response, true);
            if (isset($decodedResponse['error'])) {
                echo "API Error: " . $decodedResponse['error'];
            }
        }
        curl_close($curl);
        return json_decode($response, TRUE);
    }
    
    
}
