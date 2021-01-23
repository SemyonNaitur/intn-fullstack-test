<?php

namespace App\Models\Assignments\NbmCrawler;

use System\Core\Model;

/*
CREATE TABLE `nbm_links` (
 `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
 `site_url` varchar(100) NOT NULL,
 `link_path` varchar(100) NOT NULL,
 `updated_at` timestamp NULL DEFAULT NULL,
 `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4
*/

class Link extends Model
{
    protected $table = 'nbm_links';
    protected $primary_key = 'id';
    protected $fields = [
        'id' => [
            'type' => 'integer',
        ],
        'siteUrl' => [
            'column' => 'site_url',
            'type' => 'string',
            'required' => true,
        ],
        'linkPath' => [
            'column' => 'link_path',
            'type' => 'string',
            'required' => true,
        ],
        'updatedAt' => [
            'column' => 'updated_at',
            'type' => 'date',
        ],
        'createdAt' => [
            'column' => 'created_at',
            'type' => 'date',
        ],
    ];

    public function create(array $record): array
    {
        try {
            $pdo = $this->db->getPdo();
            $tbl = $this->table;
            $record = $this->filterFields($record, ['siteUrl', 'linkPath']);

            $sql = "INSERT INTO $tbl (site_url,link_path) VALUES (:siteUrl,:linkPath)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($record);
            $record['id'] = $pdo->lastInsertId();
            return $record;
        } catch (\PDOException $e) {
            $this->db->exception($e);
        }
    }

    public function getSites(): array
    {
        try {
            $pdo = $this->db->getPdo();
            $tbl = $this->table;

            $sql = "SELECT DISTINCT site_url FROM $tbl";
            return $pdo->query($sql)->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\PDOException $e) {
            $this->db->exception($e);
        }
    }

    public function getBySite(string $site_url): array
    {
        try {
            $pdo = $this->db->getPdo();
            $tbl = $this->table;

            $sql = "SELECT link_path FROM $tbl WHERE site_url=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$site_url]);
            return $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\PDOException $e) {
            $this->db->exception($e);
        }
    }

    public function deleteBySite(string $site_url): int
    {
        try {
            $pdo = $this->db->getPdo();
            $tbl = $this->table;

            $sql = "DELETE FROM $tbl WHERE site_url=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$site_url]);
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            $this->db->exception($e);
        }
    }
}
