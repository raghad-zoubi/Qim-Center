<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 * @method static where(string $string, $id)
 */
class Online extends Model
{
    use HasFactory;
    protected $table="onlines";
    protected $fillable = [
        'exam',
        'isopen',
        'price',
        'durationExam',
        'serial',
        'id_course',
        'numberQuestion',
        'numberHours',
        'numberVideos',
        'id',
    ];




    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['pivot'];

//
//    public function forms()
//    {
//        return $this->hasMany(Form::class);
//    }

    public function forms()
    {
        return $this->belongsTo(d4::class,"id_form","id");//->select(["users.id","users.name"])->withDefault();
    } public function polls()
    {
        return $this->belongsTo(d3::class,"id_poll","id");//->select(["users.id","users.name"])->withDefault();
    }
    public function courses()
{
    return $this->belongsTo(Course::class, "id_course", "id")
        ->select(["id", "photo", "name"])->withDefault();
}
    public function onlineCenters()
    {
        return $this->hasMany(Online_Center::class, 'id_online', 'id');
    }



}