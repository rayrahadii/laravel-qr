<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class curlAPI extends Model
{
    public $URL_API_GO;
    use HasFactory;

    public function __construct() {
      $this->URL_API_GO = env('URL_API_GO');
    }

    public function getDataFromAPIWithBodyNoAuth($url, $formBody)
  {
    $curl = curl_init();

    set_time_limit(0);
    curl_setopt_array($curl, array(
      CURLOPT_URL => url($this->URL_API_GO.$url),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_CONNECTTIMEOUT => 0,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_POSTFIELDS => $formBody,
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
      ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
  }

  public function getDataFromAPIWithBodyWithAuth($url, $formBody, $userToken)
  {
    $curl = curl_init();

    set_time_limit(0);
    curl_setopt_array($curl, array(
      CURLOPT_URL => url($this->URL_API_GO.$url),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_CONNECTTIMEOUT => 0,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_POSTFIELDS => $formBody,
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Authorization: Bearer '. $userToken
      ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
  }
}
