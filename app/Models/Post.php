<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'status',
        'image'
    ];

    public function getCreatedAtHumanAttribute() {
        return Carbon::parse($this->updated_at)->format('d M, Y h:i A');
    }
}
