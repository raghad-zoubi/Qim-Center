<?php


namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllCourses;
use App\Http\Resources\ExameUserContent;
use App\Models\AnswerPaper;
use App\Models\Booking;
use App\Models\Center;
use App\Models\Certificate;
use App\Models\Content;
use App\Models\Course;
use App\Models\CourseExame;
use App\Models\CoursePaper;
use App\Models\Exame;
use App\Models\Information;
use App\Models\Online;
use App\Models\Online_Center;
use App\Models\Option;
use App\Models\Paper;
use App\Models\Profile;
use App\Models\Question;
use App\Models\Reserve;
use App\Models\Track;
use App\Models\UserCertificate;
use App\Models\Video;
use App\MyApplication\MyApp;
use App\Services\FFmpegService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


use Illuminate\Support\Facades\Response;
use App\Services\ImageProcessingService;




class CourseExameController extends Controller
{
    protected $imageProcessingService;

    public function __construct(ImageProcessingService $imageProcessingService)
    {
        $this->middleware(["auth:sanctum"]);

        $this->imageProcessingService = $imageProcessingService;

    }

    public function showExamCourse($idOnlineCenter, $id_online)
    {
        // Fetch the online center along with its related contents, videos, and exams
        $onlineCenter = Online_Center::with(['content.contentExam.videoExam.exam.questionexamwith'])
            ->find($idOnlineCenter);
        $online = Online::where('id', $id_online)->first();

        if (!$onlineCenter) {
            return response()->json(['error' => 'Online center not found'], 404);
        }

        $selectedQuestions = collect();

        $numberQuestionsNeeded = $online->numberQuestion;
        $timeExam = $online->durationExam;

        if ($onlineCenter->content) {
            foreach ($onlineCenter->content as $content) {
                if ($content->contentExam) {
                    foreach ($content->contentExam as $video) {
                        if ($video->videoExam) {
                            foreach ($video->videoExam as $videoExam) {
                                $exam = $videoExam->exam;

                                if ($exam && $exam->questionexamwith->isNotEmpty()) {
                                    $questions = $exam->questionexamwith->shuffle();
                                    $questionsToAdd = $questions->take($numberQuestionsNeeded);

                                    $selectedQuestions = $selectedQuestions->merge($questionsToAdd);
                                }
                            }
                        }
                    }
                }
            }
        }

        // If not enough questions were found, return an error
        if ($selectedQuestions->count() < $numberQuestionsNeeded) {
            return response()->json(['error' => 'Not enough questions found'], 400);
        }

        // Ensure we don't exceed the required number of questions
        if ($selectedQuestions->count() > $numberQuestionsNeeded) {
            $selectedQuestions = $selectedQuestions->shuffle()->take($numberQuestionsNeeded);
        }

        // Prepare the response data
        $responseData = [
            'numberQuestions' => $numberQuestionsNeeded,
            'timeExam' => $timeExam,
            'id_online' => $id_online,
            'idOnlineCenter' => $idOnlineCenter,
            'selectedQuestions' => $selectedQuestions->values()
        ];

        return response()->json($responseData);
    }

    public function showExamContent($id_content)
    {
        // Fetch the content along with its videos and exams
        $content = Content::with(['contentExam.videoExam.exam.questionexamwith'])->find($id_content);

        if (!$content) {
            return response()->json(['error' => 'Content not found'], 404);
        }

        $totalQuestionsNeeded = $content->numberQuestion;
        $timeExam = $content->durationExam;
        $id_content = $content->id;
        $id_online_center = $content->id_online_center;

        $allQuestions = collect();

        foreach ($content->contentExam as $contentExam) {
            foreach ($contentExam->videoExam as $videoExam) {
                $exam = $videoExam->exam;

                if ($exam && $exam->questionexamwith->isNotEmpty()) {
                    $questions = $exam->questionexamwith;
                    $allQuestions = $allQuestions->merge($questions);
                }
            }
        }

        // Shuffle all collected questions
        $allQuestions = $allQuestions->shuffle();

        // If not enough questions were found, return an error
        if ($allQuestions->count() < $totalQuestionsNeeded) {
            return response()->json(['error' => 'Not enough questions found'], 400);
        }

        // Take the required number of questions
        $selectedQuestions = $allQuestions->take($totalQuestionsNeeded);

        // Prepare the response data
        $responseData = [
            'numberQuestions' => $totalQuestionsNeeded,
            'timeExam' => $timeExam,
            'id_content' => $id_content,
            'id_online_center' => $id_online_center,
            'selectedQuestions' => $selectedQuestions->values()
        ];

        return response()->json($responseData);
    }

    public function answerExamContent(Request $request)
    {
        // Validate the input data
        $validatedData = $request->validate([
            'id_online_center' => 'required|integer|exists:online_centers,id',
            'id_content' => 'required|integer|exists:contents,id',
            'options' => 'required|array',
            'options.*.id_question' => 'required|integer|exists:questions,id',
            'options.*.id_option' => 'required|integer|exists:options,id',
        ]);

        $id_online_center = $validatedData['id_online_center'];
        $id_content = $validatedData['id_content'];
        $options = $validatedData['options'];

        // Fetch the content along with its questions and correct options
        $content = Content::with(['contentExam.videoExam.exam.questionexamwith.option'])->find($id_content);

        if (!$content) {
            return response()->json(['error' => 'Content not found'], 404);
        }

        $totalQuestions = 0;
        $correctAnswers = 0;
        $uncorrectAnswers = 0;
//
//        foreach ($content->contentExam as $contentExam) {
//            foreach ($contentExam->videoExam as $videoExam) {
//                $exam = $videoExam->exam;
//      return response()->json(["ff"=>$exam,"dd"=>$exam->questionexamwith]);
//
////
////                if ($exam && $exam->questionexamwith->isNotEmpty()) {
////                    foreach ($exam->questionexamwith as $question) {
////                      $totalQuestions++;
////                     // $h=$question;
////                        $correctOption = $question->option->firstWhere('correct', '1'); // Find the correct option
////                        if ($correctOption) {
////                            foreach ($options as $option) {
////                                if ($option['id_question'] == $question->id && $option['id_option'] == $correctOption->id) {
////                                    $correctAnswers++;
////                                }
////                            }
////                        }
////                    }
////                }
//            }
//        }
//


        foreach ($options as $option) {
            if (Option::query()->where('id', $option['id_option'])
                ->where('id_question', $option['id_question'])
                ->where('correct', '1')->exists()) {
                $totalQuestions++;
                $correctAnswers++;
            } else {
                $uncorrectAnswers++;
                $totalQuestions++;
            }
        }


        $responseData = [
            'totalQuestions' => $totalQuestions,
            'correctAnswers' => $correctAnswers,
            'score' => $correctAnswers . '/' . $totalQuestions
        ];

        return response()->json($responseData);
    }


    public function answerExamCourse(Request $request)
    {
        // Validate the input data
        $validatedData = $request->validate([
            'id_online_center' => 'required|integer|exists:online_centers,id',
            'id_online' => 'required|integer|exists:onlines,id',
            'options' => 'required|array',
            'options.*.id_question' => 'required|integer|exists:questions,id',
            'options.*.id_option' => 'required|integer|exists:options,id',
        ]);

        // Extract validated data
        $id_online_center = $validatedData['id_online_center'];
        $id_online = $validatedData['id_online'];
        $options = $validatedData['options'];

        try {
            DB::beginTransaction();

            $course_onlineCenter = Online_Center::where('id', $id_online_center)
                ->where('id_online', $id_online)
                ->first();

            if (!$course_onlineCenter) {
                return response()->json(['error' => 'Invalid online center or online ID'], 404);
            }

            $course = Course::find($course_onlineCenter->id_course);
            $online = Online::find($course_onlineCenter->id_online);

            if (!$course || !$online) {
                return response()->json(['error' => 'Course or Online not found'], 404);
            }

            $booking = Booking::where('id_online_center', $id_online_center)
                ->where('id_user', auth()->id())
                ->where('done', '0')
                ->where('can', '1')
                ->where('status', '1')
                ->first();

            if (!$booking) {
                return response()->json(['error' => 'You cannot proceed with the exam'], 403);
            }

            if ($online->exam == '1') {
                $onlineCenter = Online_Center::with(['content.contentExam.videoExam.exam.questionexamwith'])
                    ->find($id_online_center);

                if (!$onlineCenter) {
                    return response()->json(['error' => 'Error occurred'], 404);
                }

                $correctAnswers = 0;
                $uncorrectAnswers = 0;
                $totalQuestions = count($options);

                foreach ($options as $option) {
                    if (Option::query()->where('id', $option['id_option'])
                        ->where('id_question', $option['id_question'])
                        ->where('correct', '1')->exists()) {
                        $correctAnswers++;
                    } else {
                        $uncorrectAnswers++;
                    }
                }

                $responseData = [
                    'totalQuestions' => $totalQuestions,
                    'correctAnswers' => $correctAnswers,
                    'score' => $correctAnswers . '/' . $totalQuestions,
                ];

                if ($correctAnswers * 2 >= $totalQuestions) {
                    $booking->done = '1';
                    $booking->count = $booking->count + 1;
                    $booking->mark = $correctAnswers . '/' . $totalQuestions;
                    $booking->save();
                } else {
                    $booking->done = '0';
                    $booking->count = $booking->count + 1;
                    $booking->can = '0';
                    $booking->mark = $correctAnswers . '/' . $totalQuestions;
                    $booking->save();
                    $responseData['message'] = 'يرجى اعادة طلب تقديم امتحان';
                }

                DB::commit();

                return response()->json($responseData);
            } else {
                $booking->done = '1';
                $booking->can = '0';
                $booking->save();
                DB::commit();

                return response()->json(['message' => 'لايوجد امتحان لهذا الكورس']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function showPollCourse($id_online_center)
    {


        $booking = Booking::where('id_online_center', $id_online_center)
            ->where('id_user', auth()->id())
            ->where('done', '1')
            ->where('can', '0')
            ->where('status', '1')
            ->first();

        if ($booking) {// "certificate", "id_booking"
           $userCertificate= UserCertificate::query()->
           where('id_booking',$booking)->first();
           if(!$userCertificate){
               $coursePaper=  CoursePaper::where('id_online_center', $id_online_center)->first();
               if ($coursePaper != null) {
                   $poll = Paper::
                   with('questionpaperwith'
                   )->whereHas('coursepaper', function ($query) use ($id_online_center) {
                       $query->where('id_online_center', $id_online_center);
                   })->where("type", "استبيان")->
                   get();
//            $responseData['id_booking'] = $booking->id;
                   $responseData['massage'] = 'success';
                   $responseData['poll'] = $poll;

                   return response()->json($responseData);

               }
               else{
                   $user=$this->generateCertificate($id_online_center, $booking);
                   $responseData['massage'] = 'success';
                   return response()->json($responseData);
               }

           }



        }

        else  {
            return response()->json(['error' => 'حدث خطا ما '], 404);
        }



    }


    public function answerPollCourse(Request $request,$id_online_center)
    {




        $booking = Booking::where('id_online_center', $id_online_center)
            ->where('id_user', auth()->id())
            ->where('done', '1')
            ->where('can', '0')
            ->where('status', '1')
            ->first();

        if ($booking) {
            $userCertificate= UserCertificate::query()->
            where('id_booking',$booking->id)->first();
            if(!$userCertificate){
            if ($ispapper = CoursePaper::query()
                ->where('id_online_center', $id_online_center)
                ->exists()) {
                foreach ($request->options as $item) {
                    if ($item != null) {
                        DB::beginTransaction();

                        foreach ($request->options as $item) {
                            AnswerPaper::create([
                                "id_user" => auth()->id(),
                                "answer" => $item['answer'],
                                "id_question_paper" => $item['id_question_paper'],
                                "id_option_paper" => $item['id_option_paper']
                            ]);
                        }
                        $user = $this->generateCertificate($id_online_center, $booking);
                        DB::commit();
                        return response()->json([
                            "message" => "done",
                            "status" => "success",
                        ]);

                    }
                    else {
                        return response()->json([
                            "message" => 'يرجى تعبئة الاستمارة',
                            "status" => "success",
                        ]);
                    }

                }
            }}
            else{
                return response()->json(['error' => 'انت تملك شهادة لهذه الدورة  '], 404);

            }


        }
        else  {
            return response()->json(['error' => 'حدث خطا ما '], 404);
        }
    }



//
//    public function generateCertificate1($course, $online, $booking)
//    {
//        $date = $booking->updated_at;
//        $coursename = $course->name;
//        $text = $course->text;
//        $teacher = $course->teacher;
//        $nameuser = Profile::where('id_user', auth()->id())->first();
//        $director = Information::query()->get();
//
//        $image = Certificate::findOrFail(6)->first();
//        $imagePath = public_path('Uploads/' . $image->photo);
//        $texts = [
//            ['text' => $date, 'x' => 680, 'y' => 250, 'size' => 24, 'color' => '#000000', 'font' => 'UrdType.ttf'],
//            ['text' => 'تم منح شهادةإتمام مساق الى السيد/ السيدة:', 'x' => 470, 'y' => 280, 'size' => 24, 'color' => '#000000', 'font' => 'UrdType.ttf'],
//            ['text' => $nameuser->name, 'x' => 650, 'y' => 320, 'size' => 28, 'color' => '#000000', 'font' => 'UrdType.ttf'],
//            ['text' => 'وهو مساق مدته ثمانية أشهر من 01 كانون الثاني لغاية أيلول ', 'x' => 450, 'y' => 360, 'size' => 18, 'color' => '#000000', 'font' => 'UrdType.ttf'],
//            ['text' => 'وهو مساق مدته ثمانية أشهر من 01 كانون الثاني لغاية أيلول ', 'x' => 450, 'y' => 380, 'size' => 18, 'color' => '#000000', 'font' => 'UrdType.ttf'],
//            ['text' => '____________________________________________', 'x' => 350, 'y' => 420, 'size' => 18, 'color' => '#000000', 'font' => 'UrdType.ttf'],
//            ['text' => 'مدير المركز', 'x' => 400, 'y' => 450, 'size' => 18, 'color' => '#000000', 'font' => 'UrdType.ttf'],
//            ['text' => 'الدكتور محمد علي الملة', 'x' => 330, 'y' => 490, 'size' => 22, 'color' => '#000000', 'font' => 'UrdType.ttf'],
//            ['text' => 'مدير الدبلوم', 'x' => 700, 'y' => 450, 'size' => 18, 'color' => '#000000', 'font' => 'UrdType.ttf'],
//            ['text' => 'الاختصاصية رهف قرة حمود ', 'x' => 650, 'y' => 490, 'size' => 18, 'color' => '#000000', 'font' => 'UrdType.ttf'],
//            ['text' => '_________________________________', 'x' => 480, 'y' => 550, 'size' => 18, 'color' => '#000000', 'font' => 'UrdType.ttf'],
//            ['text' => '231', 'x' => 50, 'y' => 580, 'size' => 18, 'color' => '#000000', 'font' => 'UrdType.ttf'],
//        ];
//
//        // Process the image to add text
//
//$processedImage = $this->imageProcessingService->addTextToImage($imagePath, $texts);
//
//        // Save the processed image to disk
//        $outputPath = public_path('Uploads/' . $nameuser->id_user . '.jpg');
//        file_put_contents($outputPath, $processedImage);
//
//        // Update the database with the path of the saved image
//        $userCertificate = new UserCertificate();
//        $userCertificate->id_booking = $booking->id;
//        $userCertificate->certificate = 'file/' . $nameuser->id_user . '.jpg';
//        $userCertificate->number = '2ssx1';
//        $userCertificate->save();
//
//        // Return the processed image path as a response
//        return $outputPath;
//    }
    public function generateCertificate($id_online_center, $booking)
    {$number=$this->generateNextSerialNumber();
        $date = '$booking->updated_at';
        $coursename = '$course->name';
        $text = '$course->text';
        $teacher =' $course->teacher';
        $nameuser = Profile::where('id_user', auth()->id())->first();
        $director = Information::query()->get();

        $image = Certificate::findOrFail(6)->first();
        $imagePath = public_path('Uploads/' . $image->photo);
        $texts = [
            ['text' => $date, 'x' => 680, 'y' => 250, 'size' => 24, 'color' => '#000000', 'font' => 'UrdType.ttf'],
            ['text' => 'تم منح شهادةإتمام مساق الى السيد/ السيدة:', 'x' => 470, 'y' => 280, 'size' => 24, 'color' => '#000000', 'font' => 'UrdType.ttf'],
            ['text' => $nameuser->name, 'x' => 650, 'y' => 320, 'size' => 28, 'color' => '#000000', 'font' => 'UrdType.ttf'],
            ['text' => 'وهو مساق مدته ثمانية أشهر من 01 كانون الثاني لغاية أيلول ', 'x' => 450, 'y' => 360, 'size' => 18, 'color' => '#000000', 'font' => 'UrdType.ttf'],
            ['text' => 'وهو مساق مدته ثمانية أشهر من 01 كانون الثاني لغاية أيلول ', 'x' => 450, 'y' => 380, 'size' => 18, 'color' => '#000000', 'font' => 'UrdType.ttf'],
            ['text' => '____________________________________________', 'x' => 350, 'y' => 420, 'size' => 18, 'color' => '#000000', 'font' => 'UrdType.ttf'],
            ['text' => 'مدير المركز', 'x' => 400, 'y' => 450, 'size' => 18, 'color' => '#000000', 'font' => 'UrdType.ttf'],
            ['text' => 'الدكتور محمد علي الملة', 'x' => 330, 'y' => 490, 'size' => 22, 'color' => '#000000', 'font' => 'UrdType.ttf'],
            ['text' => 'مدير الدبلوم', 'x' => 700, 'y' => 450, 'size' => 18, 'color' => '#000000', 'font' => 'UrdType.ttf'],
            ['text' => 'الاختصاصية رهف قرة حمود ', 'x' => 650, 'y' => 490, 'size' => 18, 'color' => '#000000', 'font' => 'UrdType.ttf'],
            ['text' => '_________________________________', 'x' => 480, 'y' => 550, 'size' => 18, 'color' => '#000000', 'font' => 'UrdType.ttf'],
            ['text' => '231', 'x' => 50, 'y' => 580, 'size' => 18, 'color' => '#000000', 'font' => 'UrdType.ttf'],
        ];

        // Process the image to add text
        $processedImage = $this->imageProcessingService->addTextToImage($imagePath, $texts);

        // Save the processed image to disk
        $outputPath = public_path('Uploads/' . $number . '.jpg');
        file_put_contents($outputPath, $processedImage);

        // Update the database with the path of the saved image
        $userCertificate = new UserCertificate();
        $userCertificate->id_booking =$booking->id;
        $userCertificate->certificate = 'file/' .$number . '.jpg';
        $userCertificate->number = $number;
        $userCertificate->save();

        // Return the processed image path as a response
        return $outputPath;
    }






    function generateNextSerialNumber()
    {
        $prefixes = range('A', 'Z');
        $numberLength = 4; // Default length for numeric part

        // Helper function to generate serial number based on prefix and number
//        function createSerial($prefix, $number, $length) {
//            return $prefix . str_pad($number, $length, '0', STR_PAD_LEFT);
//        }

        // Get the latest serial number from the table
        $latestCertificate = DB::table('user_certificate')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($latestCertificate) {
            $lastSerial = $latestCertificate->number;
            preg_match('/([A-Z])(\d+)$/', $lastSerial, $matches);
            $lastPrefix = $matches[1];
            $lastNumber = (int) $matches[2];

            // Define the alphabet range for prefixes
            $prefixes = range('A', 'Z');
            $currentPrefixIndex = array_search($lastPrefix, $prefixes);
            $newNumber = $lastNumber + 1;

            // Handle number overflow and prefix transition
            if ($newNumber > str_repeat('9', $numberLength)) {
                $newNumber = 0; // Reset number part to 0000
                if ($currentPrefixIndex < count($prefixes) - 1) {
                    $newPrefix = $prefixes[$currentPrefixIndex + 1];
                } else {
                    $newPrefix = 'A'; // Reset prefix after Z
                    $numberLength++; // Increase the length of the number part
                }
            } else {
                $newPrefix = $lastPrefix;
            }
        } else {
            $newPrefix = 'A';
            $newNumber = 1;
        }

        // Handle the case where we need to increment the digit length
        if (strlen($newNumber) > $numberLength) {
            $numberLength = strlen($newNumber);
            $newNumber = (int) $newNumber;
        }

        $serialNumber = $this->createSerial($newPrefix, $newNumber, $numberLength);

        // Check if the generated serial number already exists
//        $exists = DB::table('user_certificate')->where('number', $serialNumber)->exists();
//        if ($exists) {
//            // If it exists, recursively call the function to generate the next unique number
//            return $this->generateNextSerialNumber();
//        }
        return $serialNumber;
    }


    private function createSerial($lettersPart, $numberPart, $numberLength) {
        return $lettersPart . str_pad($numberPart, $numberLength, '0', STR_PAD_LEFT);
    }

}
