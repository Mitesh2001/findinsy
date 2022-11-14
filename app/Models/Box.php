<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Category;


class Box extends Model
{
    use HasFactory;

    /**
     * The users that belong to the Box
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */

    public $fillable = ['name','description','category_id','user_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class,'box_user');
    }

}
