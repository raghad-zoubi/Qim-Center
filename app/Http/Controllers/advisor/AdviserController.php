<?php

namespace App\Http\Controllers\advisor;

use App\Http\Controllers\Controller;
use App\Http\Resources\DetailsCenterCourses;
use App\Http\Resources\IndexTypeAdvisor;
use App\Models\Adviser;
use App\Models\Course;
use App\Models\d3;
use App\Models\User;
use App\MyApplication\MyApp;
use App\MyApplication\Services\AdviserRuleValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


// Now $adviserGet contains the Adviser data along with the associated User and Profile data
class AdviserController extends Controller
{
    public function __construct()
    {
//        $this->middleware(["auth:sanctum"]);
//        $this->rules = new AdviserRuleValidation();
    }
    public function indexAll()
    {

        try {

            DB::beginTransaction();
            $adviserGet = Adviser::query()->
          //  with('user')->
            get();
            DB::commit();

         //   return response()->json([
                //   '$adviserGet' =>DetailsCenterCourses::collection($courses),
           //     '$adviserGet' =>($adviserGet),
            // ]);

           return MyApp::Json()->dataHandle($adviserGet, "adviser");
        } catch (\Exception $e) {

            DB::rollBack();
            throw new \Exception($e->getMessage());
        }


        return MyApp::Json()->errorHandle("adviser", "حدث خطا ما في عرض  لديك ");//,$prof->getErrorMessage);

    }

    public function create(Request $request)
    {
     //   $request->validate($this->rules->onlyKey([ "type", "about", "id_user"], true));
        try {
            $file = $request->file("photo");
            if ($file->isValid()) {

                    DB::beginTransaction();
                    $path = MyApp::uploadFile()->upload($file);

            $adviserAdded = Adviser::create([
                "about" => strtolower($request->about),
                "type" => strtolower($request->type),
                "name" => strtolower($request->name),
                "photo" => strtolower($path),
            //    "id_user" => $request->id_user
            ]);
            DB::commit();
            return MyApp::Json()->dataHandle($adviserAdded, "Adviser");
        }} catch (\Exception $e) {

            DB::rollBack();
            throw new \Exception($e->getMessage());
        }


        return MyApp::Json()->errorHandle("adviser", "حدث خطا ما في الاضافة  لديك ");//,$prof->getErrorMessage);

    }

    public function show($id_user)
    {
        if (Adviser::query()->where("id_user", $id_user)->exists()) {

            try {

                DB::beginTransaction();
                $AdviserGet = Adviser::where("id_user", $id_user)->
                    with('date')->
                get();
                DB::commit();
                return MyApp::Json()->dataHandle($AdviserGet, "Adviser");
            } catch (\Exception $e) {

                DB::rollBack();
                throw new \Exception($e->getMessage());
            }

        } else

            return MyApp::Json()->errorHandle("Adviser", "لقد حدث خطا ما اعد المحاولة لاحقا");//,$prof->getErrorMessage);

    }

    public function update(Request $request)
    {
     //   $request->validate($this->rules->onlyKey([ "type", "about", "id_user"], true));


        if (Adviser::query()->where("id", $request->id)->exists()) {
            $ad = Adviser::where("id", $request->id)->first();
            $oldPath = $ad->photo;
            $newFile = $request->file("photo");
            //  dd($newFile);
            if ($newFile->isValid()) {
                try {
                    DB::beginTransaction();
                    $newPath = MyApp::uploadFile()->upload($newFile);

                    if ($ad) {
                        $ad->about = strtolower($request->about);
                        $ad->name = strtolower($request->name);
                        $ad->type = strtolower($request->type);
                        $ad->id_user = ($request->id_user);
                        $ad->photo = ($newPath);

                        $ad->save();
                    }
                    MyApp::uploadFile()->deleteFile($oldPath);

                    DB::commit();
                    return MyApp::Json()->dataHandle("edit successfully", "adviser");
                } catch (\Exception $e) {

                    DB::rollBack();
                    throw new \Exception($e->getMessage());
                }

            }

        }
                else

            return MyApp::Json()->errorHandle("adviser", "حدث خطا ما في تعديل  لديك ");//,$prof->getErrorMessage);


    }

    public function delete($id)
    {
        if (Adviser::query()->where("id", $id)->exists()) {
            try {

                DB::beginTransaction();
                $ad = Adviser::where("id", $id)->first();
                $oldPath = $ad->photo;
                MyApp::uploadFile()->deleteFile($oldPath);
                Adviser::where("id", $id)->delete();
                DB::commit();
                return MyApp::Json()->dataHandle("success deleted", "adviser");
            } catch (\Exception $e) {

                DB::rollBack();
                throw new \Exception($e->getMessage());
            }

        } else

            return MyApp::Json()->errorHandle("adviser", "حدث خطا ما في الحذف  لديك ");//,$prof->getErrorMessage);


    }
    //user
    public function index($type)
    {

        try {

            DB::beginTransaction();
            $adviserGet = Adviser::query()->
         //   with('user')->
            where('type','=',$type)->
            get();
            DB::commit();
//
            return response()->json([
               //    'course' =>DetailsCenterCourses::collection($courses),
               'adviserGet' =>($adviserGet),
            ]);

       //     return MyApp::Json()->dataHandle(IndexTypeAdvisor::Collection($adviserGet), "adviser");
        } catch (\Exception $e) {

            DB::rollBack();
            throw new \Exception($e->getMessage());
        }


        return MyApp::Json()->errorHandle("adviser", "حدث خطا ما في عرض  لديك ");//,$prof->getErrorMessage);

    }
}