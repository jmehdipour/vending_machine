<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machine extends Model
{
    use HasFactory;
    protected $table = 'machines';
    protected $fillable = ['name', 'location', 'status'];
    public $timestamps = true;

    public function machineProducts(): HasMany
    {
        return $this->hasMany(MachineProduct::class);
    }
}
