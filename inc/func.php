<?php
function track_user_quiz_attempt($user_id, $quiz_id, $grade)
{

    $course_id = get_the_ID();
    // if (empty($course_id)) {
    //     error_log("Course ID is empty for Quiz ID: $quiz_id");
    // }
    $course_title = get_the_title($course_id);


    $submission = Sensei()->quiz_submission_repository->get($quiz_id, $user_id);
    $quiz_grade = $submission ? $submission->get_final_grade() : 0;
    $quiz_passmark = Sensei_Utils::as_absolute_rounded_number(get_post_meta($quiz_id, '_quiz_passmark', true), 2);
    $passed_quiz = ($quiz_passmark <= intval($quiz_grade)) ? $quiz_passmark <= intval($quiz_grade) : 0;
    // error_log("Pass quiz: $passed_quiz");

    $incorrect_questions = [];
    $quiz_questions = Sensei()->quiz->get_questions($quiz_id);


    $quizform_ans = isset($_POST['sensei_question']) ? $_POST['sensei_question'] : [];

    foreach ($quiz_questions as $question) {
        $question_id = $question->ID;
        $correct_answer = get_post_meta($question_id, '_question_right_answer', true);

        // Check if the answer is incorrect or missing
        if ( get_post_status($question_id) === 'publish' && (!isset($quizform_ans[$question_id]) || empty($quizform_ans[$question_id]) || $quizform_ans[$question_id] !== $correct_answer) ) {
            // Store the question ID and the user's answer (if provided) for incorrect questions
            $incorrect_questions[] = [
                'question_id' => $question_id,
                'user_answer' => isset($quizform_ans[$question_id]) ? $quizform_ans[$question_id] : ' - '
            ];
        }
    }



    $attempt_records = get_field('attempt_records', 'user_' . $user_id) ?: [];
    $attempt_number = [
        'course_id' => $course_id,
        'num' => 1,  // Default value
        'datetime' => date('Y-m-d H:i:s')
    ];
    
    foreach ($attempt_records as $record) { // Use a reference to modify the array directly
        if ($record['attempt_number']['course_id'] == $course_id) {
            $attempt_number['num'] = (int) $record['attempt_number']['num'] + 1;
            // $attempt_number['attempt_number'] = $record['attempt_number'];
            // error_log("Added Record ID:". $record['course_id'] . "CourseID: $course_id Record Num:" . $record['attempt_number']);
            // break;
        }
        // else{
        //     // error_log("Something went worng with course ID Record ID:". $record['course_id'] . "CourseID: $course_id ". $record['attempt_number']);
        // }
    }
    unset($record); 
    

    $attempt_record = [
        'course_title' => $course_title,
        'attempt_number' => $attempt_number,
        'score' => $grade,
        'incorrect_questions' => [],
    ];

    foreach ($incorrect_questions as $question) {
        $question_title = get_the_title($question['question_id']);
        $attempt_record['incorrect_questions'][] = [
            'ques' => $question_title,
            'ans' => $question['user_answer']
        ];
    }
    add_row('attempt_records', $attempt_record, 'user_' . $user_id);

}
add_action('sensei_user_quiz_grade', 'track_user_quiz_attempt', 10, 3);


function export_csv()
{

    $users = get_users(['fields' => ['ID', 'user_login', 'user_email']]);

    $filename = "quiz_attempts_" . date('Y-m-d_H-i-s') . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    fputcsv($output, ['Name', 'Email', 'Course Title', 'Attempt Number', 'Score', 'Incorrect Questions']);

 
    foreach ($users as $user) {
        $attempt_records = get_field('attempt_records', 'user_' . $user->ID);

        if ($attempt_records) {
            // Loop through the attempt records for the user
            foreach ($attempt_records as $record) {
                $incorrect_questions = [];

                if (!empty($record['incorrect_questions'])) {
                    foreach ($record['incorrect_questions'] as $incorrect) {
                        if (isset($incorrect['ques'], $incorrect['ans'])) {
                            $ques = $incorrect['ques'] ? $incorrect['ques'] : '';
                            $ans = is_array($incorrect['ans']) ? $incorrect['ans'][0] : $incorrect['ans'];
                            $incorrect_questions[] = 'Quiz:' . $ques . '- Ans:' . $ans;
                        }
                    }
                }

                fputcsv($output, [
                    $user->user_login ?? 'Unknown',
                    $user->user_email ?? 'Unknown',
                    $record['course_title'] ?? 'No Title',
                    $record['attempt_number']['num'] ?? 'N/A',
                    ($record['score'] ?? '0') . '%',
                    implode(", ", $incorrect_questions) ?: 'None'
                ]);

                
            }
        } else {
            fputcsv($output, [
                $user->user_login,
                $user->user_email,
                '-',
                '-',
                '-',
                '-'
            ]);
        }
    }

    fclose($output);
    exit();
}