<?php

namespace App\Models;

use CodeIgniter\Model;

class CatalogDesignCuration extends Model
{
    protected $table = 'catalog_design_curations';
    protected $primaryKey = 'id';
    protected $allowedFields = ['design_id', 'design_name', 'design_group', 'status', 'redesign_note'];
    protected $useTimestamps = true;

    public const STATUS_SELECTED = 'selected';
    public const STATUS_DISCARDED = 'discarded';

    public function allByDesignId(): array
    {
        $rows = $this->findAll();
        $state = [];

        foreach ($rows as $row) {
            $state[$row['design_id']] = [
                'status' => $row['status'] ?? '',
                'redesignNote' => $row['redesign_note'] ?? '',
            ];
        }

        return $state;
    }

    public function saveState(string $designId, string $designName, string $designGroup, string $status, string $redesignNote): void
    {
        $status = in_array($status, [self::STATUS_SELECTED, self::STATUS_DISCARDED], true) ? $status : '';
        $redesignNote = trim($redesignNote);
        $existing = $this->where('design_id', $designId)->first();

        if ($status === '' && $redesignNote === '') {
            if ($existing) {
                $this->delete($existing['id']);
            }
            return;
        }

        $data = [
            'design_id' => $designId,
            'design_name' => $designName,
            'design_group' => $designGroup,
            'status' => $status !== '' ? $status : null,
            'redesign_note' => $redesignNote !== '' ? $redesignNote : null,
        ];

        if ($existing) {
            $this->update($existing['id'], $data);
            return;
        }

        $this->insert($data);
    }

    public function clearAll(): void
    {
        $this->db->table($this->table)->truncate();
    }
}