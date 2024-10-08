<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 */
class Serial extends Model
{
    use HasFactory;

    protected $table="serials";
    protected $fillable = [
        'id_course',
        'id_online_center',
        'id',
    ];

    protected $hidden = [

    ];

}
