<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

class GeocoderBatchJob extends Job {
	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($limit_offset = null) {
		if (!empty($limit_offset)) {
			$this->limit_offset = $limit_offset;
		}
		//
	}

	public function handle($limit_offset = null) {
        // $limit_offset = $this->limit_offset;
        $results = DB::select("SELECT * FROM `enderecos` WHERE `C_HERE_ID` IS NULL LIMIT {$limit_offset}");
		// $results = DB::select("SELECT * FROM `enderecos` WHERE `C_HERE_ID` IS NULL LIMIT 0,1");

        // dd($results);
		$responseBody = "";

		// dd($results);
		$body_request = 'recId|searchText|prox' . PHP_EOL;
		// $body_request = 'recId|prox' . PHP_EOL;
		foreach ($results as $key => $result) {
			// dd($result);
			if (count($results) > 0) {
				if (empty($result->LATITUDE) || empty($result->LONGITUDE)) {
					continue;
				} else {
					$body_request .= $result->id . '|';
					$body_request .= '"'.$result->CLEANED_ADDRESS . '"|';
					$body_request .= $result->LATITUDE . ',';
					$body_request .= $result->LONGITUDE . ',0' . PHP_EOL;
				}
			}else{
				continue;
			}


			/*$location = $this->getApiResult($result->CLEANED_ADDRESS);

		if ($location) {
		$addr_info = $location;
		$location = $addr_info->Location;
		$latitude = $location->NavigationPosition[0]->Latitude;
		$longitude = $location->NavigationPosition[0]->Longitude;

		// dd($addr_info);
		$update_data = [
		'C_LATITUDE' => $latitude,
		'C_LONGITUDE' => $longitude,
		'C_ENDERECO' => $location->Address->Label,
		'C_HERE_ID' => $location->LocationId,
		'C_STATUS' => 'ATIVO',
		// 'C_MATCHLEVEL' => $location->MatchLevel,
		'C_REL' => $addr_info->Relevance,
		];

		} else {
		$update_data = [
		'C_STATUS' => 'INATIVO',
		];
		}

		DB::table('enderecos')
		->where('id', $result->id)
		->update($update_data);*/

		}

		// try {
        // dd($body_request);
		$response = $this->getApiResult($body_request);
        return $this->saveJobsHash($response, $limit_offset);
		// } catch (ClientErrorResponseException $exception) {
		// $responseBody = $exception->getResponse()->getBody(true);
		// }
		// header('Content-Type: text/plain', true);
		// print($body_request);exit();

	}

	/*private function getApiResult($endereco)
	{
	$client = new Client(); //GuzzleHttp\Client
	$result = $client->get('https://geocoder.api.here.com/6.2/geocode.json', [
	'query' => [
	'app_id' => $api_info['app_id'],
	'app_code' => $api_info['app_code'],
	'searchtext' => $endereco
	]
	]);

	return $result;
	 */

    public function saveJobsHash($responseXml, $limit_offset)
    {
        $response = $responseXml;
        DB::table('batch_jobs')->insert([
            'key' => $response->Response->MetaInfo->RequestId,
            'status' => $response->Response->Status,
            'limitoffset' => $limit_offset
        ]);
        // dd($req_id);
    }


	private function getApiResult($request = "") {
		$client = new Client(); //GuzzleHttp\Client

		$url = 'https://batch.geocoder.api.here.com/6.2/jobs?app_id=aqyRtDyoNJMewbgpngW8&app_code=64KKq04PuzCnZTpZns-GdQ&action=run&mailto=rgabriel182%40gmail.com&&outcols=recId%2CdisplayLatitude%2CdisplayLongitude%2Crelevance%2CmatchQualityStreet%2ClocationLabel%2ChouseNumber%2Cstreet%2Cdistrict%2Ccity%2CpostalCode%2Ccounty%2Cstate&mode=retrieveAddresses&language=pt-BR&indelim=%7C&outdelim=%7C&outputCombined=false&outcols=displayLatitude,displayLongitude,locationLabel,houseNumber,street,district,city,postalCode,county,state,country&outputcompressed=false&maxresults=1';
		$query = [
			'app_id' => env('HERE_APP_ID'),
			'app_code' => env('HERE_APP_CODE'),
			'action' => 'run',
			'mailto' => 'rgabriel182@gmail.com',
			'outCols' => "recId%2Clatitude%2Clongitude%2ClocationLabel",
			'mode' => 'retrieveAddresses',
			'language' => 'pt-BR',
			'indelim' => '%7C',
			'outdelim' => '%7C',
			'outputCombined' => false,
			'outcols' => "displayLatitude%2CdisplayLongitude%2ClocationLabel%2ChouseNumber%2Cstreet%2Cdistrict%2Ccity%2CpostalCode%2Ccounty%2Cstate%2Ccountry",
		];

		$options = [
			'body' => $request,
			// 'debug' => TRUE,
			// 'headers' => [
				// "Content-Type" => "text/plain",
			// ],
			// 'query' => $query,
		];

		$response = $client->post($url, $options)->getBody()->getContents();
        $responseXml = simplexml_load_string($response);
        // dd($responseXml);
        // dd($result);
		// return $this->validateResponse($result_json);
		return $responseXml;

	}

	private function validateResponse($result) {
		// $result = false;
		// dd($result->Response->View);

		if (empty($result->Response->View->Result)) {
			$result = array_first($result->Response->View);
			if (!empty($result)) {
				$result = array_first($result->Result);
			} else {
				$result = false;
			}
		} else {
			$result = false;
		}

		return $result;
	}

	public function getApiInfo() {
		return [
			'app_id' => 'aqyRtDyoNJMewbgpngW8',
			'app_code' => '64KKq04PuzCnZTpZns-GdQ',
			'url' => 'https://geocoder.api.here.com/6.2/geocode.json',
		];
	}

}
