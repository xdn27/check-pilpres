<?php

declare(strict_types=1);

namespace App\Command\Kpu;

use Minicli\Command\CommandController;
use Minicli\Output\Filter\ColorOutputFilter;
use Minicli\Output\Helper\TableHelper;

use ClanCats\Hydrahon\Query\Expression as Ex;

use GuzzleHttp\Client;

class GetWilayahController extends CommandController
{

    use \App\Traits\DB;

    private $tingkat_recursive_param = [];
    private $tingkat_base_uri = 'https://sirekap-obj-data.kpu.go.id/wilayah/pemilu/ppwp/';

    public function handle(): void
    {
        $this->dbinit();

        $tingkat = "1";
        if ($this->hasParam('tingkat')) {
            $tingkat = $this->getParam('tingkat');
        } 
        
        $this->display('Get Wilayah '.$tingkat);

        $this->{'getTingkat'.$tingkat}();
        // $this->getTingkatRecursive();
    }

    public function getTingkatRecursive($param = 0)
    {
        // https://sirekap-obj-data.kpu.go.id/wilayah/pemilu/ppwp/11/1105/110507/1105072002.json
        // https://sirekap-obj-data.kpu.go.id/pemilu/hhcw/ppwp/11/1105/110507/1105072002/1105072002001.json

        if(!empty($param)){
            $path = implode('/', $this->tingkat_recursive_param);
        }else{
            $path = $param;
        }

        $client = new Client([
            'base_uri' => $this->tingkat_base_uri
        ]);

        $response = $client->request('GET', $path.'.json', ['http_errors' => false]);

        $body = $response->getBody()->getContents();
        $json = json_decode($body, true);

        $this->newline();
        $this->out('Request: '. $path);

        foreach($json as $k => $j) {

            sleep(2);

            $this->newline();
            $this->out($j['nama']);

            if(isset($j['tingkat']) && $j['tingkat'] >= 5){
                $this->tingkat_base_uri = 'https://sirekap-obj-data.kpu.go.id/pemilu/hhcw/ppwp/';
            }else{
                $this->tingkat_base_uri = 'https://sirekap-obj-data.kpu.go.id/wilayah/pemilu/ppwp/';
                
                $this->tingkat_recursive_param[$param] = $j['kode'];
                $this->getTingkatRecursive($j['kode']);
            }

        }
        
    }

    public function getTingkat1()
    {
        $client = new Client();
        $response = $client->request('GET', 'https://sirekap-obj-data.kpu.go.id/wilayah/pemilu/ppwp/0.json');

        $status_code = $response->getStatusCode();
        $body = $response->getBody()->getContents();

        $this->display('Save Tingkat 1');

        $wilayah = $this->db->table('wilayah');

        $wilayah_kpu = json_decode($body, true);
        
        foreach ($wilayah_kpu as $kw => $w) {
            $this->newline();
            $this->out($w['nama']);

            $wilayah->insert($w)->execute();
        }

        return $wilayah_kpu;
    }

    public function getTingkat2()
    {
        $wilayah = $this->db->table('wilayah');
        $wilayah_arr = $wilayah->select()->get();
        
        $client = new Client();
        
        foreach($wilayah_arr as $w1){
            
            $response = $client->request('GET', 'https://sirekap-obj-data.kpu.go.id/wilayah/pemilu/ppwp/'.$w1['kode'].'.json');

            $status_code = $response->getStatusCode();
            $body = $response->getBody()->getContents();

            $this->display('Save Tingkat 2');

            $wilayah_kpu = json_decode($body, true);
            
            foreach ($wilayah_kpu as $kw => $w2) {
                $this->newline();
                $this->out($w1['nama'].' => '.$w2['nama']);

                $w2['parent'] = $w1['kode'];

                $wilayah->insert($w2)->execute();
            }

        }
    }

    public function getTingkat3()
    {
        $wilayah = $this->db->table('wilayah');
        $wilayah_arr = $wilayah->select([
            'id',
            'kode',
            'nama',
            'tingkat',
            'parent'
        ])
        ->addField(new Ex('CONCAT_WS("/", parent, kode)'), 'path')
        ->where('tingkat', 2)->get();
        
        $client = new Client();
        
        foreach($wilayah_arr as $w1){
            
            $response = $client->request('GET', 'https://sirekap-obj-data.kpu.go.id/wilayah/pemilu/ppwp/'.$w1['path'].'.json');

            $status_code = $response->getStatusCode();
            $body = $response->getBody()->getContents();

            $this->display('Save Tingkat 3');

            $wilayah_kpu = json_decode($body, true);
            
            foreach ($wilayah_kpu as $kw => $w2) {
                $this->newline();
                $this->out($w1['nama'].' => '.$w2['nama']);

                $w2['parent'] = $w1['kode'];

                $wilayah->insert($w2)->execute();
            }

        }
    }

    public function getTingkat4()
    {
        $kelurahan = $this->db->table('kelurahan');
        $wilayah = $this->db->table('kecamatan as k');
        $wilayah_arr = $wilayah->select([
            'k.id',
            'k.kode',
            'k.nama',
            'k.tingkat',
            'k.parent'
        ])
        ->addField(new Ex('CONCAT_WS("/", p.kode, kk.kode, k.kode)'), 'path')
        ->join('kab_kota as kk', 'kk.kode', '=', 'k.parent')
        ->join('provinsi as p', 'p.kode', '=', 'kk.parent')
        ->get();
        
        $client = new Client();
        
        foreach($wilayah_arr as $w1){
            
            $response = $client->request('GET', 'https://sirekap-obj-data.kpu.go.id/wilayah/pemilu/ppwp/'.$w1['path'].'.json');

            $status_code = $response->getStatusCode();
            $body = $response->getBody()->getContents();

            $this->display('Save Tingkat 4');

            $wilayah_kpu = json_decode($body, true);
            
            foreach ($wilayah_kpu as $kw => $w2) {
                $this->newline();
                $this->out($w1['nama'].' => '.$w2['nama']);

                $w2['parent'] = $w1['kode'];

                $kelurahan->insert($w2)->execute();
            }

        }
    }

    public function getTingkat5()
    {
        $tps = $this->db->table('tps');
        $wilayah = $this->db->table('kelurahan as k');
        $wilayah_arr = $wilayah->select([
            'k.id',
            'k.kode',
            'k.nama',
            'k.tingkat',
            'k.parent'
        ])
        ->addField(new Ex('CONCAT_WS("/", p.kode, kk.kode, kec.kode, k.kode)'), 'path')
        ->join('kecamatan as kec', 'kec.kode', '=', 'k.parent')
        ->join('kab_kota as kk', 'kk.kode', '=', 'kec.parent')
        ->join('provinsi as p', 'p.kode', '=', 'kk.parent')
        ->get();
        
        $client = new Client();
        
        foreach($wilayah_arr as $w1){
            
            $response = $client->request('GET', 'https://sirekap-obj-data.kpu.go.id/wilayah/pemilu/ppwp/'.$w1['path'].'.json');

            $status_code = $response->getStatusCode();
            $body = $response->getBody()->getContents();

            $this->display('Save Tingkat 5');

            $wilayah_kpu = json_decode($body, true);
            
            foreach ($wilayah_kpu as $kw => $w2) {
                $this->newline();
                $this->out($w1['nama'].' => '.$w2['nama']);

                $w2['parent'] = $w1['kode'];

                $tps->insert($w2)->execute();
            }

        }
    }
}
