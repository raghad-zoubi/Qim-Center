<?php

namespace App\Http\Controllers\Papers;

use App\Http\Controllers\Controller;
use App\Http\Resources\DisplayPaperUser;
use App\Models\AnswerPaper;
use App\Models\CoursePaper;
use App\Models\OptionPaper;
use App\Models\Paper;
use App\Models\QuestionPaper;
use App\MyApplication\MyApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// user
class CoursePaperController extends Controller
{

    public function displayPaperUser($id_user, $id_online_center)
    {
        try {
            DB::beginTransaction();


            $data = DB::table('question_papers')
                ->select(
                    'papers.title',
                    'papers.description',
                    'papers.type',
                    'profiles.name',
                    'profiles.lastName',
                    'profiles.mobilePhone',
                    'question_papers.select',
                    'question_papers.question',
                    // 'papers.', // 'question_papers.',//'answer_papers.', // 'course_papers.', // 'option_papers.*',
                  DB::raw('COALESCE(answer_papers.answer, option_papers.value) as answer'),
                    'question_papers.id as id_question',
                    'answer_papers.id as id_answer',
                    'option_papers.id as id_option',
                    'profiles.id_user'

                )
                ->join('papers', 'papers.id', '=', 'question_papers.id_paper')
                ->leftJoin('option_papers', 'option_papers.id_question_paper', '=', 'question_papers.id')
                ->leftJoin('answer_papers', 'answer_papers.id_question_paper', '=', 'question_papers.id')
                ->join('course_papers', 'course_papers.id_paper', '=', 'papers.id')
                ->join('online_centers', 'online_centers.id', '=', 'course_papers.id_online_center')
                ->join('users', 'users.id', '=', 'answer_papers.id_user')
                ->join('profiles', 'profiles.id_user', '=', 'users.id')
                ->where('online_centers.id', $id_online_center)
                ->where('answer_papers.id_user', $id_user)
                ->get();

          //  $uniqueData = collect($data)->unique('id_question');
            DB::commit();
dd('data');
        return  $data;
            //DisplayPaperUser::collection($uniqueData);

        } catch (\Exception $e) {

            DB::rollBack();
            throw new \Exception($e->getMessage());
        }


        return MyApp::Json()->errorHandle("paper", "لقد حدث خطا ما اعد المحاولة لاحقا");//,$prof->getErrorMessage);

        // AnswerPaper
    }
    public function displayPaperCourse( $id_online_center)
    {
        try {
            DB::beginTransaction();


//            $answers = DB::table('answer_papers')
//                ->join('question_papers', 'answer_papers.id_question_paper', '=', 'question_papers.id')
//                ->join('papers', 'question_papers.id_paper', '=', 'papers.id')
//                ->join('course_papers', 'papers.id', '=', 'course_papers.id_paper')
//                ->where('course_papers.id_online_center', $id_online_center)
//                ->where('papers.type', 'استبيان')
//                ->select('question_papers.id as question_id', 'question_papers.question', 'papers.title', 'answer_papers.*')
//                ->get()
//                ->groupBy('question_id');

  $w='o';
//////////////////////
//            $answers = DB::table('answer_papers')
//                ->join('question_papers', 'answer_papers.id_question_paper', '=', 'question_papers.id')
//                ->join('papers', 'question_papers.id_paper', '=', 'papers.id')
//                ->join('course_papers', 'papers.id', '=', 'course_papers.id_paper')
//                ->leftJoin('option_papers', 'answer_papers.id_option_paper', '=', 'option_papers.id')
//                ->where('course_papers.id_online_center', 4)
//                ->where('papers.type', 'استبيان')
//                ->select(
//                    'question_papers.id as question_id',
//                    'question_papers.question',
//                    'question_papers.select',
//                    'papers.title',
//                    'answer_papers.answer',
//                    'option_papers.value as option'
//                )
//                ->get()
//                ->groupBy('question_id');
//
//            $formattedAnswers = [];
//
//            foreach ($answers as $questionId => $group) {
//                $answersArray = $group->map(function($item) {
//                    return $item->answer ?? $item->option;
//                })->all();
//
//                $formattedAnswers[$questionId] = [
//                    'question_id' => $group[0]->question_id,
//                    'question' => $group[0]->question,
//                    'select' => $group[0]->select,
//                    'answer' => $answersArray
//                ];
//            }
//
//            $title = $answers->first()[0]->title;
//
//            $response = [
//                'title' => $title
//            ];
//
//            $response = array_merge($response, $formattedAnswers);
////////
//            return response()->json($response);
            $j='o';
//            $answers = DB::table('answer_papers')
//                ->join('question_papers', 'answer_papers.id_question_paper', '=', 'question_papers.id')
//                ->join('papers', 'question_papers.id_paper', '=', 'papers.id')
//                ->join('course_papers', 'papers.id', '=', 'course_papers.id_paper')
//                ->leftJoin('option_papers', 'answer_papers.id_option_paper', '=', 'option_papers.id')
//                ->where('course_papers.id_online_center', 4)
//                ->where('papers.type', 'استبيان')
//                ->select(
//                    'question_papers.id as question_id',
//                    'question_papers.question',
//                    'question_papers.select',
//                    'papers.title',
//                    'answer_papers.answer',
//                    'option_papers.value as option'
//                )
//                ->get()
//                ->groupBy('question_id');
//
//            $formattedAnswers = [];
//
//            foreach ($answers as $questionId => $group) {
//                $answersArray = $group->map(function($item) {
//                    return $item->answer ?? $item->option;
//                })->all();
//
//                $answerCounts = array_count_values($answersArray);
//                $answersList = [];
//
//                foreach ($answerCounts as $answer => $count) {
//                    if ($count > 1 && in_array($group[0]->select, ['قائمة منسدلة', 'خيار متعدد', 'مربعات اختيار'])) {
//                        $answersList[] = $answer;
//                        $answersList[] = $count;
//                    } else {
//                        $answersList[] = $answer;
//                    }
//                }
//
//                $formattedAnswers[$questionId] = [
//                    'question_id' => $group[0]->question_id,
//                    'question' => $group[0]->question,
//                    'select' => $group[0]->select,
//                    'answer' => $answersList
//                ];
//            }
//
//            $title = $answers->first()[0]->title;
//
//            $response = [
//                'title' => $title
//            ];
//
//            $response = array_merge($response, $formattedAnswers);
//
//            return response()->json($response);
//


            $answers = DB::table('answer_papers')
                ->join('question_papers', 'answer_papers.id_question_paper', '=', 'question_papers.id')
                ->join('papers', 'question_papers.id_paper', '=', 'papers.id')
                ->join('course_papers', 'papers.id', '=', 'course_papers.id_paper')
                ->leftJoin('option_papers', 'answer_papers.id_option_paper', '=', 'option_papers.id')
                ->where('course_papers.id_online_center', 4)
                ->where('papers.type', 'استبيان')
                ->select(
                    'question_papers.id as question_id',
                    'question_papers.question',
                    'question_papers.select',
                    'papers.title',
                    'answer_papers.answer',
                    'option_papers.value as option'
                )
                ->get()
                ->groupBy('question_id');

            $formattedAnswers = [];

            foreach ($answers as $questionId => $group) {
                $answersArray = $group->map(function($item) {
                    return $item->answer ?? $item->option;
                })->all();

                $answerCounts = array_count_values($answersArray);
                $answersList = [];

                foreach ($answerCounts as $answer => $count) {
                    if ($count > 1 && in_array($group[0]->select, ['قائمة منسدلة', 'خيار متعدد', 'مربعات اختيار'])) {
                        $answersList[] = [
                            'value' => $answer,
                            'num' => $count
                        ];
                    } else {
                        $answersList[] = ['value' => $answer];
                    }
                }

                $formattedAnswers[$questionId] = [
                    'question_id' => $group[0]->question_id,
                    'question' => $group[0]->question,
                    'select' => $group[0]->select,
                    'answer' => $answersList
                ];
            }

            $title = $answers->first()[0]->title;

            $response = [
                'title' => $title
            ];

            $response = array_merge($response, $formattedAnswers);

            return response()->json($response);


        } catch (\Exception $e) {

            DB::rollBack();
            throw new \Exception($e->getMessage());
        }


        return MyApp::Json()->errorHandle("paper", "لقد حدث خطا ما اعد المحاولة لاحقا");//,$prof->getErrorMessage);

        // AnswerPaper
    }

    public function show($id)
    {

        try {
            DB::beginTransaction();
            $questionsWithOptions = Paper::

            with('questionpaperwith'
            )->whereHas('coursepaper', function ($query) use ($id) {
                $query->where('id_online_center', $id);
            })->
            get();


            DB::commit();

            return MyApp::Json()->dataHandle($questionsWithOptions, "paper");
        } catch (\Exception $e) {

            DB::rollBack();
            throw new \Exception($e->getMessage());
        }


        return MyApp::Json()->errorHandle("paper", "لقد حدث خطا ما اعد المحاولة لاحقا");//,$prof->getErrorMessage);

    }

    public function answer(Request $request)
    {

        try {
            DB::beginTransaction();

            foreach ($request->options as $item) {
                AnswerPaper::create([
                    "id_user" => auth()->id(),
                    "answer" => $item['answer'],
                    "id_question_paper" => $item['id_question_paper'],
                    "id_option_paper" => $item['id_option_paper']
                ]);
            }
            DB::commit();

            return MyApp::Json()->dataHandle('correct', "data");
        } catch (\Exception $e) {

            DB::rollBack();
            throw new \Exception($e->getMessage());
        }


        return MyApp::Json()->errorHandle("paper", "لقد حدث خطا ما اعد المحاولة لاحقا");//,$prof->getErrorMessage);

    }



}
