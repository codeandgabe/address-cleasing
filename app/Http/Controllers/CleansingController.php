<?php

namespace App\Http\Controllers;

use App\Jobs\GeocoderBatchJob;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CleansingController extends Controller {
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {
		//
	}

	public function geocoder() {
		// $results = DB::select("SELECT * FROM `enderecos` WHERE `C_WITH_HOUSENUMBER` IS NULL limit 200");
		// $job = new \App\Jobs\GeocoderBatchJob();
		// $job->handle();

	}

	public function geoBatch($threshold = 0) {
		$geocode = new \App\Jobs\GeocoderBatchJob();

		$threshold = 49999;
		for ($i = 1; $i <= 2; $i++) {
			$limit = $threshold * ($i - 1);
			$offset = $limit + $threshold;
			$limit_offset = ($limit + 1) . ",$offset";
			$geocode->handle($limit_offset);
			usleep(1);
		}

		exit();
	}

	public function index() {

		// return $this->geoBatch();
		// $job = new \App\Jobs\CleansingJob();
		// $job->handle();
		// $this->testJob();

		return view('cleansing.upload');
	}

	public function records() {
		$enderecos = DB::table('enderecos')->paginate(10);

		return view('cleansing.records', [
			'enderecos' => $enderecos,
		]);
	}

	public function upload(Request $request) {

		$planilha = $request->file('planilha');
		if ($planilha->isValid()) {

			$result = $this->readCSV($planilha->getRealPath(), [
				'delimiter' => ',',
			]);

			return $this->processData($result);

		}

	}

	private function readCSV($csvFile, $array) {
		$file_handle = fopen($csvFile, 'r');
		while (!feof($file_handle)) {
			$line_of_text[] = fgetcsv($file_handle, 0, $array['delimiter']);
		}
		fclose($file_handle);
		return $line_of_text;
	}

	public function processData($result = array()) {
		set_time_limit(0);
		$enderecos = array();
		// $results = DB::table("enderecos")->truncate();
		foreach ($result as $key => $endereco) {
			if ($key == 0) {
				continue;
			}
			$end = $endereco[0];
			$end_explode = explode(';', $end);

			try {
				if (count($end_explode) < 31) {
					continue;
				}
				$enderecos[] = [
					'NOM_BAIRRO' => $end_explode[0],
					'NOM_ABREV_BAIRRO' => $end_explode[1],
					'NOM_PT_CX_POSTAL_COMUNIT' => $end_explode[2],
					'END_PT_CX_POSTAL_COMUNIT' => $end_explode[3],
					'NUM_INI_CX_POSTAL_COMUNIT' => $end_explode[4],
					'NUM_FIN_CX_POSTAL_COMUNIT' => $end_explode[5],
					'AREA_ABRAN_CX_POSTAL_COMUNIT' => $end_explode[6],
					'NUM_CEP' => $end_explode[7],
					'INAREARURAL' => $end_explode[8],
					'DSC_LOCALIDADE' => $end_explode[9],
					'DSC_ABREV_LOCALIDADE' => $end_explode[10],
					'NUM_COD_NAC_LOCALIDADE' => $end_explode[11],
					'SGL_COD_NAC_LOCALIDADE' => $end_explode[12],
					'NUM_COD_IBGE' => $end_explode[13],
					'COD_LOCALIDADE_PRINC' => $end_explode[14],
					'AREA_LOCAL' => $end_explode[15],
					'LATITUDE' => $end_explode[16],
					'LONGITUDE' => $end_explode[17],
					'INAREACONCESSAO' => $end_explode[18],
					'NOM_LOGRADOURO' => $end_explode[19],
					'IND_NUMERACAO_ENDERECO' => $end_explode[20],
					'NOM_ABREV_LOGRADOURO' => $end_explode[21],
					'DSC_ABREV_TIPO_LOGRAD' => $end_explode[22],
					'DSC_TIPO_LOGRAD' => $end_explode[23],
					'DSC_ABREV_TITULO_LOGRAD' => $end_explode[24],
					'DSC_TITULO_LOGRAD' => $end_explode[25],
					'SGL_UF' => $end_explode[26],
					'NOM_UF' => $end_explode[27],
					'NUM_INIC' => $end_explode[28],
					'NUM_FINAL' => $end_explode[29],
					'IND_LADO_NUMERACAO' => $end_explode[30],
					'INAREARISCO' => $end_explode[31],
					'COD_REL_CEP_LOGRAD_BAIRRO' => $end_explode[32],
					'CLEANED_ADDRESS' => $this->cleanFullAddress($end_explode),
				];
			} catch (Exception $e) {
				continue;
			}

		}

		foreach (array_chunk($enderecos, 1500) as $t) {
			DB::table('enderecos')->insert($t);
			usleep(10);
		}

	}

	public function cleanFullAddress($endereco) {
		$cleanAddress = "";

		$logradouro = $endereco[19];
		$tipo_logradouro = $endereco[23];
		$uf = $endereco[27];
		$bairro = $endereco[0];
		$cidade = $endereco[9];
		$cep = $endereco[7];

		$cleanAddress = "$tipo_logradouro $logradouro, $bairro, $cidade, $uf, $cep";

		return $cleanAddress;
	}

	public function getApiInfo() {
		return [
			'app_id' => 'aqyRtDyoNJMewbgpngW8',
			'app_code' => '64KKq04PuzCnZTpZns-GdQ',
			'url' => 'https://geocoder.api.here.com/6.2/geocode.json',
		];
	}

	private function getApiResult($endereco = "") {
		$client = new Client(); //GuzzleHttp\Client

		if (empty($endereco)) {
			return false;
		}

		$result = $client->get('https://geocoder.api.here.com/6.2/geocode.json', [
			'timeout' => 5, // Response timeout
			'query' => [
				'app_id' => env('HERE_APP_ID'),
				'app_code' => env('HERE_APP_CODE'),
				'searchtext' => $endereco,
			],
		])->getBody()->getContents();

		$result_json = json_decode($result);
		$result_json = $result_json->Response->View[0];

		if (!empty($result_json->Result[0])) {
			return $result_json->Result[0];
		} else {
			return false;
		}

	}

	public function batch() {
		$jobs = DB::table('batch_jobs')->paginate(10);

		return view('cleansing.batch', [
			'jobs' => $jobs,
		]);
	}

	/*
		@route upload/batchjob
	*/
	public function batchjob(Request $request) {
		$txt = $request->file('txt');
		if ($txt->isValid()) {

			$contents = fopen($txt->getRealPath(), 'r');

			if ($contents) {
				$i = 0;
				while (($line = fgets($contents)) !== false) {
					if ($i == 0) {
						// continue;
					} else {
						$record = explode('|', $line);

						$fields = [
							// 'recId' => $record[0],
							'h_SeqNumber' => $record[1],
							'h_seqLength' => $record[2],
							'h_recId' => $record[3],
							'h_displayLatitude' => $record[4],
							'h_displayLongitude' => $record[5],
							'h_relevance' => $record[6],
							'h_matchQualityStreet' => $record[7],
							'h_locationLabel' => $record[8],
							'h_houseNumber' => $record[9],
							'h_street' => $record[10],
							'h_district' => $record[11],
							'h_city' => $record[12],
							'h_postalCode' => $record[13],
							'h_state' => $record[15],
						];

						if (empty($fields['h_matchQualityStreet'])) {
							$fields['here_status'] = 'inativo';
						}

						DB::table('enderecos')
							->where('id', $record[0])
							->update($fields);

					}
					$i++;
				}

				fclose($contents);
			} else {
				// error opening the file.
			}

		}
	}

	//
}
