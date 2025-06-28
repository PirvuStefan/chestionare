<?php


session_start();
include('connection.php');


function get_random_questionnaire() {
    global $conn;
    
   
    $count_query = "SELECT COUNT(DISTINCT user_questionnaire_id) as total FROM user_questionnaire_questions";
    $count_result = mysqli_query($conn, $count_query);
    
    if (!$count_result) {
        return null;
    }
    
    $count_row = mysqli_fetch_assoc($count_result);
    $total = $count_row['total'];
    
    if ($total == 0) {
        return null;
    }
    
  
    $random_offset = rand(0, $total - 1);
    
    
    $query = "SELECT DISTINCT user_questionnaire_id FROM user_questionnaire_questions LIMIT 1 OFFSET $random_offset";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['user_questionnaire_id'];
    }
    
    return null;
}



class Questionnaire {
    public $id;
    public array $questions = []; 

    public function __construct($id) {
        $this->id = $id;
    }

    public function add_question(Question $question) {
        $this->questions[] = $question;
    }
}

class Question {
    
    public int $id;
        public string $description;
    public string $answers1;
    public string $answers2;   
    public string $answers3;
    public string $answers1_correct;
    public string $answers2_correct;
    public string $answers3_correct;

    public function __construct(
        int $id,
        string $description,
        string $answers1,
        string $answers2,
        string $answers3,
        string $answers1_correct,
        string $answers2_correct,
        string $answers3_correct
    ) {
        $this->id = $id;
        $this->description = $description;
        $this->answers1 = $answers1;
        $this->answers2 = $answers2;
        $this->answers3 = $answers3;
        $this->answers1_correct = $answers1_correct;
        $this->answers2_correct = $answers2_correct;
        $this->answers3_correct = $answers3_correct;
    }
}// firecare intrebare are un id, o descriere si 3 raspunsuri, fiecare cu un camp de corectitudine

?>