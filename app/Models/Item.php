<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $fillable = [
        'item_code', 'name', 'jenis_barang', 'kategori_barang', 'unit',
    ];

    public function opnameEntries(): HasMany
    {
        return $this->hasMany(OpnameEntry::class);
    }

    public function unitConversions(): HasMany
    {
        return $this->hasMany(ItemUnitConversion::class);
    }
}
