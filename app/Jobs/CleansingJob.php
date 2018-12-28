<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

class CleansingJob extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($limit = null)
    {
        if (!empty($limit)) {
            $this->limit = $limit;
        }
        //
    }

    public function handle()
    {
        // $limit = $this->limit;
        $results = DB::select("SELECT * FROM `enderecos` WHERE `C_HERE_ID` IS NULL limit 100");

        foreach ($results as $key => $result) {
            $location = $this->getApiResult($result->CLEANED_ADDRESS);

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
                ->update($update_data);

        }

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

    private function getApiResult($endereco = "")
    {
        $client = new Client(); //GuzzleHttp\Client

        if (empty($endereco)) {
            return false;
        }

        $result = $client->get('https://geocoder.api.here.com/6.2/geocode.json', [
            'query' => [
                'app_id' => env('HERE_APP_ID'),
                'app_code' => env('HERE_APP_CODE'),
                'searchtext' => $endereco,
            ],
        ])->getBody()->getContents();

        $result_json = json_decode($result);

        return $this->validateResponse($result_json);

    }

    private function validateResponse($result)
    {
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

    public function getApiInfo()
    {
        return [
            'app_id' => 'aqyRtDyoNJMewbgpngW8',
            'app_code' => '64KKq04PuzCnZTpZns-GdQ',
            'url' => 'https://geocoder.api.here.com/6.2/geocode.json',
        ];
    }

}
