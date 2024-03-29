<?php

namespace App\Http\Controllers\Papers;

use App\Http\Controllers\Controller;
use App\Models\d1;
use App\Models\d3;
use App\Models\d6;
use App\Models\Group;
use App\Models\OptionPaper;
use App\Models\Paper;
use App\Models\QuestionPaper;
use App\MyApplication\MyApp;
use App\MyApplication\Services\PaperRuleValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaperController extends Controller
{

    public function __construct()
    {
      //  $this->middleware(["auth:sanctum"]);
   //     $this->rules = new PaperRuleValidation();
    }

    public function index($type)
    {

        try {

            DB::beginTransaction();
            $paperGet = Paper::query()->where ('type',$type)->get();
            DB::commit();
            return MyApp::Json()->dataHandle($paperGet, "paper");
        } catch (\Exception $e) {

            DB::rollBack();
            throw new \Exception($e->getMessage());
        }


        return MyApp::Json()->errorHandle("paper", "لقد حدث خطا ما اعد المحاولة لاحقا");//,$prof->getErrorMessage);

    }
    public function show($id)
    {

        try {
            DB::beginTransaction();
                $environments = QuestionPaper::query()->with('optionpaper')
                    ->where('id_paper',$id)->get();
                DB::commit();
                return MyApp::Json()->dataHandle($environments, "paper");
        } catch (\Exception $e) {

            DB::rollBack();
            throw new \Exception($e->getMessage());
        }


        return MyApp::Json()->errorHandle("paper", "لقد حدث خطا ما اعد المحاولة لاحقا");//,$prof->getErrorMessage);

    }

    public function Create(Request $request)
    {
        //      return MyApp::Json()->dataHandle($request['body'][0]['question'], "poll");


        //protected $fillable = ['type', 'id_poll_form', 'question', 'kind'];

        //  $request->validate($this->rules->onlyKey(["name","address"],true));
        try {
            DB::beginTransaction();
            $Added = Paper::create([
                "title" => strtolower($request['title']),
                "description" => strtolower($request['description']),
                "type" => strtolower($request['type']),
            ]);

            foreach ($request['body'] as $inner) {


                $AddedQ = QuestionPaper::create([
                    "select" => strtolower($inner['select']),
                    "question" => strtolower($inner['question']),
                    "required" => strtolower($inner['required']),
                    "id_paper" => $Added->id,
                ]);
//dd($AddedQ->id);
                foreach ($inner['options'] as $item) {
                    // ['id_question_poll_form', 'answer'];
                    $Addedop = OptionPaper::create([
                        "value" => strtolower($item['value']),
                        "id_question_paper" => $AddedQ->id,
                    ]);

                }

            }

            DB::commit();
            return MyApp::Json()->dataHandle($Added, "paper");
        } catch (\Exception $e) {

            DB::rollBack();
            throw new \Exception($e->getMessage());
        }


        return MyApp::Json()->errorHandle("paper", "error");//,$prof->getErrorMessage);

    }


    public function delete($id)
    {
        if (Paper::query()->where("id", $id)->exists()) {
            try {

                DB::beginTransaction();
                Paper::where("id", $id)->delete();
                DB::commit();
                return MyApp::Json()->dataHandle("success", "paper");
            } catch (\Exception $e) {

                DB::rollBack();
                throw new \Exception($e->getMessage());
            }

        } else

            return MyApp::Json()->errorHandle("paper", "حدث خطا ما في الحذف ");//,$prof->getErrorMessage);


    }
}
/*
 * country
 * hasmany city
 * hasmanythrough shop  city  country->with('shops)
 *                                     whithcount
 *         city
 *               shop
 * belongstocity
 *                    employee
 *  * */
