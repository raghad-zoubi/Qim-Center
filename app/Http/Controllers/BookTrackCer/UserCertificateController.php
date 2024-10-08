<?php

namespace App\Http\Controllers\BookTrackCer;


use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Center;
use App\Models\Certificate;
use App\Models\Option;
use App\Models\Question;
use App\Models\UserCertificate;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use App\Services\ImageProcessingService;


class UserCertificateController extends Controller
{
    protected $imageProcessingService;

    public function __construct(ImageProcessingService $imageProcessingService)
    {
        $this->middleware(["auth:sanctum"]);

        $this->imageProcessingService = $imageProcessingService;
    }

    public function show($id)
    {
        // Retrieve image from database
        $image = Certificate::findOrFail($id);

        // Return the image as a response
        return response($image->image_data)->header('Content-Type', 'image/jpeg');
    }

    public function index()
    {
        // Retrieve image from database
        //$responseData = UserCertificate::query()->with('book2')->get();

        $responseData = DB::table('user_certificate')
            ->select(
                'user_certificate.number',
                'user_certificate.certificate',
                'user_certificate.id',
                'profiles.name as profile_name',
                'profiles.lastName as profile_lastname',
                'courses.name as course_name',
                DB::raw('CASE WHEN online_centers.id_center IS NULL THEN "أون لاين" ELSE "مركز" END AS type')
            , DB::raw('DATE(user_certificate.created_at) as created_at') )
          //  ->join('booking', 'booking.id', '=', 'user_certificate.id_booking')
          ->join('booking', function($join) {
              $join->on('booking.id_user', '=', 'user_certificate.id_user')
                  ->on('booking.id_online_center', '=', 'user_certificate.id_online_center'); // Added semicolon
          })
            ->join('online_centers', 'online_centers.id', '=', 'booking.id_online_center')
            ->join('courses', 'courses.id', '=', 'online_centers.id_course')
            ->join('profiles', 'profiles.id_user', '=', 'booking.id_user')
            ->get();

        return response()->json($responseData);

    }
   public function myCertificate()

    {



        $responseData = DB::table('user_certificate')
            ->select(
                'courses.name',
                'user_certificate.certificate',
                'user_certificate.id',
                DB::raw('CASE WHEN online_centers.id_center IS NULL THEN "أون لاين" ELSE "مركز" END AS type')
                          ,DB::raw('DATE(user_certificate.created_at) as created_at')
  )
            ->join('booking', function($join) {
                $join->on('booking.id_user', '=', 'user_certificate.id_user')
                    ->on('booking.id_online_center', '=', 'user_certificate.id_online_center'); // Added semicolon
            })
            ->join('online_centers', 'online_centers.id', '=', 'booking.id_online_center')
            ->join('courses', 'courses.id', '=', 'online_centers.id_course')
            ->where('booking.id_user', Auth::id())
            ->get();



        return response()->json(['data'=>$responseData]);

    }

}

