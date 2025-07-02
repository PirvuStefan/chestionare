<?php 
session_start(); // Add this line
include('connection.php');

// Include the same classes and functions from question.php
class Question {
    public int $id;
    public string $description;
    public string $answers1;
    public string $answers2;   
    public string $answers3;
    public bool $answers1_correct;
    public bool $answers2_correct;
    public bool $answers3_correct;

    public function __construct(int $id) {
        $this->id = $id;
        // Initialize with empty values - will be populated later
        $this->description = "";
        $this->answers1 = "";
        $this->answers2 = "";
        $this->answers3 = "";
        $this->answers1_correct = false;
        $this->answers2_correct = false;
        $this->answers3_correct = false;
    }
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

class Answers {

    public bool $answer1, $answer2, $answer3;
    public function __construct(bool $answer1, bool $answer2, bool $answer3) {
        $this->answer1 = $answer1;
        $this->answer2 = $answer2;
        $this->answer3 = $answer3;
    }
}

function get_random_questionnaire() {
    global $conn;
    
    $count_query = "SELECT COUNT(DISTINCT user_questionnaire_id) as total FROM user_questionnaire_questions";
    $count_result = mysqli_query($conn, $count_query);
    
    if (!$count_result) {
        return null;
    }
    
    $total = mysqli_fetch_assoc($count_result)['total'];
    
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

function initialise_questionnaire(){
    $questionnaire = new Questionnaire(get_random_questionnaire());
    
    if ($questionnaire->id === null) {
        return null;
    }
    
    global $conn;
    $query = "SELECT question_id FROM user_questionnaire_questions WHERE user_questionnaire_id = {$questionnaire->id} LIMIT 10";
    $result = mysqli_query($conn, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $question = new Question($row['question_id']);
            
            $description_query = "SELECT description FROM questions WHERE id = {$question->id}";
            $description_result = mysqli_query($conn, $description_query);
            if ($description_result && $desc_row = mysqli_fetch_assoc($description_result)) {
                $question->description = $desc_row['description'];
            }

            // Get answers and their correctness for the current question
            $answers_query = "SELECT description, is_correct FROM questions_answers WHERE question_id = {$question->id} ORDER BY id LIMIT 3";
            $answers_result = mysqli_query($conn, $answers_query);
            if ($answers_result) {
                $i = 1;
                while ($answer_row = mysqli_fetch_assoc($answers_result)) {
                    $answer_field = "answers" . $i;
                    $correct_field = "answers" . $i . "_correct";
                    $question->$answer_field = $answer_row['description'];
                    $question->$correct_field = (bool)$answer_row['is_correct'];
                    $i++;
                }
            }

            $questionnaire->add_question($question);
        }
    }
    
    return $questionnaire;
}

// Try to get questionnaire from session first, otherwise create new one
$chestionar = $_SESSION['chestionar'] ?? null;

$raspuns = $_SESSION['raspunsuri'] ?? []; // Retrieve user answers from session

if ($chestionar === null) {
    // Initialize the questionnaire if not in session
    $chestionar = initialise_questionnaire();
    
    // Store in session for future use
    $_SESSION['chestionar'] = $chestionar;
}

// Handle the case where no questionnaire is available
if ($chestionar === null) {
    echo "<h1>No questionnaire available</h1>";
    exit();
}

function test(){
for($i = 0; $i < 10; $i++){

  echo $raspuns[$i]->answer1  ;
  echo $raspuns[$i]->answer2  ;
  echo $raspuns[$i]->answer3  ;
  
  echo "\
  <br>";
}
}

function check($check, $rasp){
  // check este ceea ce a fost selectat de utilizator, rasp este daca este corect sau nu
  if($check == 1 and $rasp == 1)
  return 'correct';
  if($check == 1 and $rasp == 0)
  return 'incorrect';
  if($check == 0 and $rasp == 0)
  return 'neutral';
  return 'should';
}

function valid($a,$b,$c,$x,$y,$z){
  // a,b,c sunt raspunsurile date de utilizator, x,y,z sunt raspunsurile corecte
  if($a == $x and $b == $y and $c == $z)
  return 1;
  return 0;
}

$count = 0;




?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Rezultate Chestionar</title>
  <style>
    :root {
      --glass-bg: rgba(0, 123, 255, 0.15);
      --glass-border: rgba(0, 123, 255, 0.3);
      --glass-shadow: rgba(0, 123, 255, 0.4);
      --text-color: #fff;
      --correct-color: #28a745;
      --incorrect-color: #dc3545;
      --neutral-color: rgba(255, 255, 255, 0.1);
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to bottom right, #0f2027, #203a43, #2c5364);
      margin: 0;
      padding: 2rem;
      color: var(--text-color);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }

    .container {
      width: 100%;
      max-width: 700px;
      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      border-radius: 20px;
      box-shadow: 0 8px 32px 0 var(--glass-shadow);
      backdrop-filter: blur(12px);
      padding: 2rem;
    }

    h1 {
      text-align: center;
      font-size: 2rem;
      margin-bottom: 1.5rem;
      user-select: none;
    }

    .question {
      margin-bottom: 1.5rem;
    }

    .question p {
      margin-bottom: 0.5rem;
      font-weight: bold;
      user-select: none;
    }

    .answer-option {
      display: block;
      user-select: none;
      padding: 0.5rem;
      margin: 0.3rem 0;
      border-radius: 12px;
      border: 2px solid transparent;
      transition: all 0.3s ease;
      position: relative;
    }

    .answer-option.correct {
      background: rgba(40, 167, 69, 0.3);
      border-color: var(--correct-color);
    }

    .answer-option.incorrect {
      background: rgba(220, 53, 69, 0.3);
      border-color: var(--incorrect-color);
    }

    .answer-option.neutral {
      background: var(--neutral-color);
      border-color: transparent;
      opacity: 0.6;
    }

    .answer-option::after {
      content: '';
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      width: 20px;
      height: 20px;
      border-radius: 50%;
      font-weight: bold;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
    }

    .answer-option.correct::after {
      content: '✓';
      background: var(--correct-color);
      color: white;
    }

    .answer-option.incorrect::after {
      content: '✗';
      background: var(--incorrect-color);
      color: white;
    }

    

    .navigation-buttons {
      display: flex;
      gap: 1rem;
      margin-top: 2rem;
    }

    .btn {
      background: #007bff;
      color: white;
      border: none;
      padding: 0.8rem 1.5rem;
      border-radius: 12px;
      cursor: pointer;
      font-size: 1rem;
      text-decoration: none;
      text-align: center;
      transition: background 0.3s;
      flex: 1;
    }

    .btn:hover {
      background: rgb(24, 204, 0);
    }

    .btn.secondary {
      background: #6c757d;
    }

    .btn.secondary:hover {
      background: #5a6268;
    }

    .answer-option.should {
  background: rgba(255, 193, 7, 0.3);  /* Yellow background */
  border-color: #ffc107;               /* Yellow border */
  }

.answer-option.should::after {
  content: '!';
  background: #ffc107;
  color: white;
  }
  </style>
</head>
<body>
  <div class="container">
    <h1>Rezultate Chestionar #<?php  echo " "; echo $count  ?></h1>
    
    <?php for ($i = 0; $i < count($chestionar->questions); $i++): ?>
      <?php 
        $question = $chestionar->questions[$i];
        $question_num = $i + 1;
      ?>
      
      <div class="question">
        <p><?php echo $question_num; ?>. <?php echo htmlspecialchars($question->description); ?></p>
        
        <!-- Answer 1 -->
        <div class="answer-option <?php echo check($raspuns[$i]->answer1, $question->answers1_correct) ?>">
          <?php echo htmlspecialchars($question->answers1); ?>
        </div>
        
        <!-- Answer 2 -->
        <div class="answer-option <?php echo check($raspuns[$i]->answer2, $question->answers2_correct) ?>">
          <?php echo htmlspecialchars($question->answers2); ?>
        </div>
        
        <!-- Answer 3 -->
        <div class="answer-option <?php echo check($raspuns[$i]->answer3, $question->answers3_correct) ?>">
          <?php echo htmlspecialchars($question->answers3); ?>
        </div>

        <?php  $count = $count + valid($raspuns[$i]->answer1,$raspuns[$i]->answer2,$raspuns[$i]->answer3,$question->answers1_correct,$question->answers2_correct,$question->answers3_correct)?>
      </div>
      
    <?php endfor; ?>
    <h2>Scorul final: <?php echo $count ?> / 10</h2>
    <h3><?php if($count == 10) echo "Felicitari!Ai rezolvat perfect acest chestionar! ☺️"; ?> </h3>

    <div class="navigation-buttons">
      <a href="question.php" class="btn secondary">Încearcă din nou</a>
      <a href="welcome.php" class="btn">Înapoi la Dashboard</a>
    </div>
  </div>
  
</body>
</html>