<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(array $array)
 * @method static create(array $array)
 * @method static whereDoesntHave(string $string, \Closure $param)
 * @method static whereHas(string $string, \Closure $param)
 * @method static selectRaw(string $string)
 */
class Booking extends Model
{
    use HasFactory;

    protected $table ='booking';
    protected $fillable = [   "id_online_center",
        "can", "count", "id_user" ,"id","mark","status",'done',
     'created_at','updated_at'
    ];

    protected $hidden = [];


    public function booking(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Online_Center::class,"id_online_center","id")->withDefault();
    }
    public function bookingindex(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Online_Center::class,"id_online_center","id")->
        with(['coursepaper','course']);
    }
    public function booking2(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Online_Center::class,"id_online_center","id")->
        with('course')->select();
    }
    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class,"id_user","id")
            ->with('profile')->select();
    }

}

