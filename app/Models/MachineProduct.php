<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MachineProduct extends Model
{
    use HasFactory;
    protected $table = 'machine_products';
    protected $fillable = ['machine_id', 'product_id', 'stock'];
    public $timestamps = true;

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
