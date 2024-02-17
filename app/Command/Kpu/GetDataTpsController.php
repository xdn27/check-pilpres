<?php

declare(strict_types=1);

namespace App\Command\Kpu;

use Minicli\Command\CommandController;
use Minicli\Output\Filter\ColorOutputFilter;
use Minicli\Output\Helper\TableHelper;

use ClanCats\Hydrahon\Query\Expression as Ex;

use GuzzleHttp\Client;

ini_set('memory_limit', '-1');

class GetDataTpsController extends CommandController
{

    use \App\Traits\DB;

    public function handle(): void
    {
        $this->dbinit();

        $p = null;
        if ($this->hasParam('p')) {
            $p = explode(',', $this->getParam('p'));
        }
        
        $this->display('Get Data TPS');

        $perolehan = $this->db->table('perolehan');
        
        $tps = $this->db->table('tps as t');
        $query_tps = $tps->select([
            't.id',
            't.kode',
            't.nama',
            't.tingkat',
            't.parent'
        ])
        ->addField(new Ex('CONCAT_WS("/", p.kode, kk.kode, kec.kode, kel.kode, t.kode)'), 'path')
        ->addField(new Ex('CONCAT_WS("-", p.nama, kk.nama, kec.nama, kel.nama, t.nama)'), 'path_name')
        ->join('kelurahan as kel', 'kel.kode', '=', 't.parent')
        ->join('kecamatan as kec', 'kec.kode', '=', 'kel.parent')
        ->join('kab_kota as kk', 'kk.kode', '=', 'kec.parent')
        // ->join('provinsi as p', 'p.kode', '=', 'kk.parent')
        ->join('provinsi as p', function($join) {
            $join->on('p.kode', '=', 'kk.parent');
        });

        if(!empty($p)){
            $query_tps->whereIn('p.nama', $p);
        }
        
        $tps_arr = $query_tps->get();

        $time = time();
        $client = new Client();

        foreach($tps_arr as $tps) {

            $response = $client->request('GET', 'https://sirekap-obj-data.kpu.go.id/pemilu/hhcw/ppwp/'.$tps['path'].'.json', ['http_errors' => false]);

            if($response->getStatusCode() != 200){
                continue;
            }

            $status_code = $response->getStatusCode();
            $body = $response->getBody()->getContents();

            $result = json_decode($body, true);
            $a = $result['administrasi'];
            $c = $result['chart'];
            $images = json_encode($result['images']);
            
            $kode = explode('/', $tps['path']);
            
            $this->newline();
            $this->out('Save '.$tps['path_name']);

            $suara_sah = $a['suara_sah'] ?? 0;
            $pas_1 = $c['100025'] ?? 0;
            $pas_2 = $c['100026'] ?? 0;
            $pas_3 = $c['100027'] ?? 0;
            $manipulasi = ($pas_1+$pas_2+$pas_3) - $suara_sah;

            if($manipulasi > 0){
                $perolehan->insert([
                    'kode_prov' => $kode[0],
                    'kode_kab_kota' => $kode[1],
                    'kode_kec' => $kode[2],
                    'kode_kel' => $kode[3],
                    'kode_tps' => $kode[4],
                    'lokasi' => $tps['path_name'],
                    'suara_total' => $a['suara_total'] ?? 0,
                    'suara_sah' => $a['suara_sah'] ?? 0,
                    'suara_tidak_sah' => $a['suara_tidak_sah'] ?? 0,
                    'pengguna_total' => $a['pengguna_dpt_j'] ?? 0,
                    'pemilih_dpt' => $a['pemilih_dpt_j'] ?? 0,
                    'pas_1' => $c['100025'] ?? 0,
                    'pas_2' => $c['100026'] ?? 0,
                    'pas_3' => $c['100027'] ?? 0,
                    'koreksi_pas_1' => 0,
                    'koreksi_pas_2' => 0,
                    'koreksi_pas_3' => 0,
                    'manipulasi' => $manipulasi,
                    'scan_c1_urls' => $images,
                    'waktu_tarik' => $time
                ])->execute();
            }
        }
    }
}
