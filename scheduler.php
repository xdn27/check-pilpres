<?php require_once __DIR__.'/vendor/autoload.php';

use GO\Scheduler;


class MyDB {

    use \App\Traits\DB;

    public function getProv() {
        $this->dbinit();

        $wilayah = $this->db->table('provinsi');
        $wilayah_arr = $wilayah->select()->get();

        return $wilayah_arr;
    }
}

$scheduler = new Scheduler();

// $prov = (new MyDB())->getProv();

// foreach ($prov as $key => $value) {
    
//     $scheduler->raw('./minicli kpu getdatatps p="'.$value['nama'].'"')->onlyOne();
// }

$scheduler->raw('./minicli kpu getdatatps p="JAWA BARAT, JAWA TENGAH, JAWA TIMUR, DKI JAKARTA"')->onlyOne();

$scheduler->run();
