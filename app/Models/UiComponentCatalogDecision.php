<?php

namespace App\Models;

use CodeIgniter\Model;

class UiComponentCatalogDecision extends Model
{
    public const DECISION_IMPLEMENT = 'implement';
    public const DECISION_DISCARD = 'discard';
    public const DECISION_REDESIGN = 'redesign';

    protected $table = 'ui_component_catalog_decisions';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'catalog_key',
        'section_key',
        'group_key',
        'item_key',
        'item_name',
        'item_hint',
        'source_label',
        'decision',
        'redesign_notes',
        'created_by',
    ];
    protected $useTimestamps = true;

    public static function allowedDecisions(): array
    {
        return [
            self::DECISION_IMPLEMENT,
            self::DECISION_DISCARD,
            self::DECISION_REDESIGN,
        ];
    }

    public static function mapKey(string $catalogKey, string $sectionKey, ?string $groupKey, string $itemKey): string
    {
        return implode('|', [$catalogKey, $sectionKey, $groupKey ?: '', $itemKey]);
    }

    public function decisionMap(): array
    {
        $map = [];

        foreach ($this->findAll() as $row) {
            $map[self::mapKey($row['catalog_key'], $row['section_key'], $row['group_key'] ?? '', $row['item_key'])] = $row;
        }

        return $map;
    }

    public function setDecision(array $data): void
    {
        $existing = $this->where('catalog_key', $data['catalog_key'])
            ->where('section_key', $data['section_key'])
            ->where('group_key', $data['group_key'] ?? '')
            ->where('item_key', $data['item_key'])
            ->first();

        if ($existing) {
            $this->update($existing['id'], $data);
            return;
        }

        $this->insert($data);
    }

    public function clearDecision(string $catalogKey, string $sectionKey, string $groupKey, string $itemKey): void
    {
        $this->where('catalog_key', $catalogKey)
            ->where('section_key', $sectionKey)
            ->where('group_key', $groupKey)
            ->where('item_key', $itemKey)
            ->delete();
    }
}
