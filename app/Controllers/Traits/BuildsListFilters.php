<?php

namespace App\Controllers\Traits;

trait BuildsListFilters
{
    private function getFilters(array $extraIntegerKeys = []): array
    {
        $filters = [];

        foreach (array_merge(['grupo_id', 'pagador_id'], $extraIntegerKeys) as $key) {
            if ($this->request->getGet($key)) {
                $filters[$key] = (int) $this->request->getGet($key);
            }
        }

        foreach (['fecha_desde', 'fecha_hasta', 'descripcion'] as $key) {
            if ($this->request->getGet($key)) {
                $filters[$key] = $this->request->getGet($key);
            }
        }

        $filters['sort'] = $this->request->getGet('sort') ?: 'fecha';
        $filters['order'] = $this->request->getGet('order') ?: 'DESC';

        return $filters;
    }
}
