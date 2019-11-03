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

        $data.= '<!DOCTYPE html><html lang="fn">';
        $data.= '<head><meta charset="UTF-8"><body>';

        $data .= '<p style="font-family: Arial, Helvetica, sans-serif;">';
        $data .= "Hei,<br><br> \n\n";
        $data .= "kiitos, kun osallistuit IDO- ja Geberit-tuotteidemme online-koulutukseen. Toivottavasti sait koulutuksesta hyöydyllistä tietoa.<br><br> \n\n";
        $data .= "Jos koulutuksen aikana heräsi kysymyksiä, niin otathan yhteyttä alueesi myyntiedustajaamme. Yhteystiedot löydät myös kotisivuiltamme www.geberit.fi.<br><br> \n";

        $data .= "\n";

        $data .= "Alla näet tuloksesi.<br><br> \n\n";

        $data .= "Nimesi: <strong>$user->name</strong><br> \n";
        $data .= "Yritys: <strong>$user->company_name</strong><br> \n";
        $data .= "Kaupunki: <strong>$user->city</strong><br> \n";
        $data .= "Sähköposti: <strong>$user->email</strong><br><br> \n\n";

        foreach ($questions as $q) {
            if ( $q->selected_answer == $q->correct_answer ) {
                $correct_answers++;
            }
        }

        $correct_answers_percentage = $correct_answers * 100 / $questions_count;

        $data .= "Olet vastannut oikein {$correct_answers_percentage}%:iin kysymyksistä ($correct_answers kysymystä oikein {$questions_count}:sta)<br> \n";
        $data .= "Voit tarkistaa oikeat vastaukset alta.<br><br>\n\n";

        $data .= "Tulos<br><br> \n\n";

        foreach($questions as $q) {
            $data .= "<strong>$question_number) $q->question</strong><br> \n";
            foreach ($q->possible_answers as $pa) {
                $data .= "$pa <br>\n";
            }
            $data .= "\nVastauksesi: {$q->possible_answers[$q->selected_answer]}<br>\n";
            $data .= "Oikea vastaus: {$q->possible_answers[$q->correct_answer]}<br><br>\n\n";
            $question_number++;
        }

        $data .= "Terveisin<br> \nGeberit Oy<br><br> \n\n";
        $data .= '</p>';

        $data .= '</body></html>';

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
            'mailtype' => 'html',
        ));

        $this->email->from('koulutukset.fi@geberit.com', 'Geberit Oy');
        $this->email->to($user->email);
        $this->email->bcc('andrius@adguns.lt,koulutukset.fi@geberit.com');
        // $this->email->bcc('koulutukset.fi@geberit.com');
        $this->email->subject('Testituloksesi');
        $this->email->message($data);

        $this->email->send();

        return true;
    }

}
