<?php

declare(strict_types=1);

namespace App\Traits;

$dotenv = \Dotenv\Dotenv::createImmutable(dirname(dirname(__DIR__)));
$dotenv->load();

trait DB
{
    public $db;

    public function dbinit() {

        $connection = new \PDO('mysql:host='.$_ENV['DB_HOST'].';dbname='.$_ENV['DB_NAME'].';charset=utf8', $_ENV['DB_USER'], $_ENV['DB_PASS']);

        // create a new mysql query builder
        $this->db = new \ClanCats\Hydrahon\Builder('mysql', function($query, $queryString, $queryParameters) use($connection)
        {
            $statement = $connection->prepare($queryString);
            $statement->execute($queryParameters);

            // when the query is fetchable return all results and let hydrahon do the rest
            // (there's no results to be fetched for an update-query for example)
            if ($query instanceof \ClanCats\Hydrahon\Query\Sql\FetchableInterface)
            {
                return $statement->fetchAll(\PDO::FETCH_ASSOC);
            }
            // when the query is a instance of a insert return the last inserted id  
            elseif($query instanceof \ClanCats\Hydrahon\Query\Sql\Insert)
            {
                return $connection->lastInsertId();
            }
            // when the query is not a instance of insert or fetchable then
            // return the number os rows affected
            else 
            {
                return $statement->rowCount();
            }   
        });

    }
}
