<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Proccess extends CI_Controller {

    public function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

        parent::__construct();

        $this->load->library('email');
        $this->load->model('proccess_model');
    }

    public function index()
    {
        return '';
    }

    public function post_submit()
    {
        $post = json_decode($this->input->raw_input_stream);
        $questions = $post->questions;
        $user = $post->user;

        $data = '';
        $question_number = 1;

        $questions_count = count($questions);
        $correct_answers = 0;

        $data .= "Hei, \n";
        $data .= "kiitos, kun osallistuit IDO- ja Geberit-tuotteidemme online-koulutukseen. Toivottavasti sait koulutuksesta hyöydyllistä tietoa. \n";
        $data .= "Jos koulutuksen aikana heräsi kysymyksiä, niin otathan yhteyttä alueesi myyntiedustajaamme. Yhteystiedot löydät myös kotisivuiltamme www.geberit.fi. \n";

        $data .= "\n";

        $data .= "Alla näet tuloksesi. \n";

        $data .= "User: $user->name \n";
        $data .= "Company: $user->company_name \n";
        $data .= "City: $user->city \n";
        $data .= "Email: $user->email \n\n";

        foreach ($questions as $q) {
            if ( $q->selected_answer == $q->correct_answer ) {
                $correct_answers++;
            }
        }

        $correct_answers_percentage = $correct_answers * 100 / $questions_count;

        $data .= "You’ve answered {$correct_answers_percentage}% ($correct_answers questions out of $questions_count) correctly \n";
        $data .= "You can review all the test results below.";

        $data .= "Questions and answers \n\n";

        foreach($questions as $q) {
            $data .= "$question_number) $q->question \n";
            foreach ($q->possible_answers as $pa) {
                $data .= "$pa \n";
            }
            $data .= "\nAnswered: {$q->possible_answers[$q->selected_answer]}\n";
            $data .= "Correct answer: {$q->possible_answers[$q->correct_answer]}\n\n";
            $question_number++;
        }

        $data .= "Terveisin Geberit Oy \n\n";

        $stats = [
            'final_result' => $correct_answers_percentage,
            'right_answers' => $correct_answers,
            'wrong_answers' => $questions_count - $correct_answers,
        ];

        $this->proccess_model->insert_answer($user, $questions, $stats);

        $this->email->initialize(array(
            'protocol' => 'SMTP',
            'smtp_host' => 'smtp.office365.com',
            'smtp_user' => 'koulutukset.fi@geberit.com',
            'smtp_pass' => 'W24geCW6G9YQztj!',
            'smtp_port' => '587',
            'smtp_crypto' => 'tls',
        ));

        $this->email->from('your@example.com', 'Geberit Oy');
        $this->email->to($user->email);
        $this->email->bcc('andrius@adguns.lt');
        $this->email->subject('Testituloksesi');
        $this->email->message($data);

        $this->email->send();

        return true;
    }

}
