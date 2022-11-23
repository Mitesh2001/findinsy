<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Box;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'box_id',
        'category_id',
        'user_id',
    ];

    /**
     * Get the box that owns the Item
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function box()
    {
        return $this->belongsTo(Box::class, 'box_id');
    }

}
