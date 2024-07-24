<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Question;
use App\Models\Answer;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use App\Models\School;
use App\Models\User;

class FileUploadController extends Controller
{

    public function showUploadForm()
    {
        return view('UploadExcelFiles');
    }

    public function uploadQuestions(Request $request)
    {
        $request->validate([
            'questionsFile' => 'required|file|mimes:xlsx,xls',
        ]);

        $file = $request->file('questionsFile');
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        foreach ($rows as $row) {
            if ($row[0] !== 'id') { // Skip header row
                Question::create([
                    'question_text' => $row[1],
                    'marks' => $row[2],
                ]);
            }
        }

        return back()->with('success', 'Questions uploaded successfully!');
    }

    public function uploadAnswers(Request $request)
    {
        $request->validate([
            'answersFile' => 'required|file|mimes:xlsx,xls',
        ]);

        $file = $request->file('answersFile');
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        foreach ($rows as $row) {
            if ($row[0] !== 'id') { // Skip header row
                // Validate and cast question_id
                $question_id = filter_var($row[0], FILTER_VALIDATE_INT);
                if ($question_id === false) {
                    // Handle invalid question_id, e.g., skip this row or log an error
                    continue;
                }

                Answer::create([
                    'question_id' => $question_id,
                    'answer_text' => $row[1],
                    'is_correct' => isset($row[2]) ? (bool) $row[2] : false, // Assuming `is_correct` is a boolean
                ]);
            }
        }

        return back()->with('success', 'Answers uploaded successfully!');
    }


    public function uploadSchools(Request $request)
    {
        $request->validate([
            'schoolsFile' => 'required|file|mimes:xlsx,xls',
        ]);

        $file = $request->file('schoolsFile');
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                if ($row[0] !== 'id') { // Skip header row
                    // Create the user entry first
                    $user = User::create([
                        'username' => $row[6],
                        'firstname' => $row[6],
                        'lastname' => '6', // Add appropriate data if available
                        'email' => $row[4],
                        'role' => 'representative',
                        'date_of_birth' => now(), // Add appropriate data if available
                        'school_reg_no' => $row[3],
                        'password' => Hash::make($row[3]),
                    ]);

                    // Create the school entry referencing the user
                    School::create([
                        'id' => $row[0],
                        'name' => $row[1],
                        'district' => $row[2],
                        'registration_number' => $row[3],
                        'email_of_representative' => $user->email,
                        'email' => $row[5],
                        'representative_name' => $row[6],
                        'validated' => (bool) $row[7],
                    ]);
                }
            }
        });

        return back()->with('success', 'Schools and user accounts uploaded successfully!');
    }


}
