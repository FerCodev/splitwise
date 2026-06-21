<?php

namespace App\Models;

use CodeIgniter\Model;

class UiComponentPreference extends Model
{
    protected $table = 'ui_component_preferences';
    protected $primaryKey = 'id';
    protected $allowedFields = ['screen_key', 'component_key', 'variant_key'];
    protected $useTimestamps = true;

    public function getVariant(string $screenKey, string $componentKey): ?string
    {
        $row = $this->where('screen_key', $screenKey)
            ->where('component_key', $componentKey)
            ->first();

        return $row['variant_key'] ?? null;
    }

    public function setVariant(string $screenKey, string $componentKey, string $variantKey): void
    {
        $existing = $this->where('screen_key', $screenKey)
            ->where('component_key', $componentKey)
            ->first();

        $data = [
            'screen_key' => $screenKey,
            'component_key' => $componentKey,
            'variant_key' => $variantKey,
        ];

        if ($existing) {
            $this->update($existing['id'], $data);
            return;
        }

        $this->insert($data);
    }
}
