<?php

namespace App\Scope;

use Illuminate\Database\Eloquent\Builder;

trait FilterScope
{
    public function scopeSetFilterAllowKeys($builder, ...$allowKeys)
    {
        $allowKeys = implode(',', $allowKeys);
        if (!$allowKeys) return $builder;
        $request = request();
        $request->validate([
            'filter.*.key' => "required|in:{$allowKeys}",
            'filter.*.condition' => 'required|in:in,is,not,like,lt,gt',
            'filter.*.value' => 'required'
        ]);
        $filters = $request->input('filter');
        if ($filters) {
            foreach ($filters as $k => $filter) {
                if ($filter['condition'] === 'in') {
                    $builder->whereIn($filter['key'], $filter['value']);
                    continue;
                }
                if ($filter['condition'] === 'is') {
                    $builder->where($filter['key'], $filter['value']);
                    continue;
                }
                if ($filter['condition'] === 'not') {
                    $builder->where($filter['key'], '!=', $filter['value']);
                    continue;
                }
                if ($filter['condition'] === 'gt') {
                    $builder->where($filter['key'], '>', $filter['value']);
                    continue;
                }
                if ($filter['condition'] === 'lt') {
                    $builder->where($filter['key'], '<', $filter['value']);
                    continue;
                }
                if ($filter['condition'] === 'like') {
                    $builder->where($filter['key'], 'like', "%{$filter['value']}%");
                    continue;
                }
            }
        }
        return $builder;
    }
}