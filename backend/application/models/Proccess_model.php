<?php

class Proccess_model extends CI_Model
{
    public $time;
    public $name;
    public $company;
    public $city;
    public $email;
    public $q1;
    public $q2;
    public $q3;
    public $q4;
    public $q5;
    public $q6;
    public $q7;
    public $q8;
    public $final_result;
    public $right_answers;
    public $wrong_answers;

    public function insert_answer($user, $questions, $stats)
    {
        $this->time = date('Y-m-d H:i:s');
        $this->name = $user->name;
        $this->company = $user->company_name;
        $this->city = $user->city;
        $this->email = $user->email;

        foreach ($questions as $key => $question ) {
            $key = $key + 1;
            $question_number = 'q'. $key;

            $this->$question_number = ( $question->correct_answer == $question->selected_answer ? 'R' : 'W' );
        }

        $this->final_result = $stats['final_result'];
        $this->right_answers = $stats['right_answers'];
        $this->wrong_answers = $stats['wrong_answers'];

        $this->db->insert('quiz_data', $this);
    }
}