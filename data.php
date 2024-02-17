<?php require 'vendor/autoload.php'; ?>
<?php

use Ozdemir\Datatables\Datatables;
use Ozdemir\Datatables\DB\MySQL;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


$config = [ 'host'     => $_ENV['DB_HOST'],
            'port'     => $_ENV['DB_PORT'],
            'username' => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASS'],
            'database' => $_ENV['DB_NAME'] 
        ];

$dt = new Datatables( new MySQL($config) );

$dt->query('select lokasi, suara_sah, pas_1, pas_2, pas_3, manipulasi, waktu_tarik, id, kode_prov, kode_kab_kota, kode_kec, kode_kel, kode_tps from perolehan where manipulasi > 0');

$dt->edit('id', function($data){
    // return a link.
    return "<a target='_blank' href='https://pemilu2024.kpu.go.id/pilpres/hitung-suara/".$data['kode_prov']."/".$data['kode_kab_kota']."/".$data['kode_kec']."/".$data['kode_kel']."/".$data['kode_tps']."'>Periksa</a>";
});

$dt->edit('waktu_tarik', function($data){
    // return a link.
    return date('Y-m-d H:i:s', $data['waktu_tarik']);
});

echo $dt->generate();