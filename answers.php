<?php 
session_start(); // Add this line
include('connection.php');

// Set UTF-8 encoding for database connection
mysqli_set_charset($conn, "utf8");


if(!$_SESSION['userID']) {
    header("Location: welcome.php");
    exit();
}
if(!isset($_SESSION['activ'])){
    header("Location: welcome.php");
    exit();
}

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
    
  return 2;
}

function initialise_questionnaire() {
    global $conn;
    $questionnaire = new Questionnaire(get_random_questionnaire());
    
    if ($questionnaire->id === null) {
        return null; // Return if invalid questionnaire
    }
    
    for($i = 0; $i < 10; $i++) {
        $question_id = rand(4, 136);
        
        $query = "SELECT description FROM questions WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $question_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
          $question = new Question($question_id);
          $question->description = $row['description'];
          
        }


        $query = "SELECT description, is_correct FROM question_answers WHERE question_id = ? ORDER BY question_id LIMIT 3";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $question_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $answers = [];
        while ($row = mysqli_fetch_assoc($result)) {
          $answers[] = $row;
        }

        if (count($answers) === 3) {
          $question->answers1 = $answers[0]['description'];
          $question->answers2 = $answers[1]['description']; 
          $question->answers3 = $answers[2]['description'];
          $question->answers1_correct = (bool)$answers[0]['is_correct'];
          $question->answers2_correct = (bool)$answers[1]['is_correct'];
          $question->answers3_correct = (bool)$answers[2]['is_correct'];
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

$userID = $_SESSION['userID'] ?? null;



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

function write($USER_ID, $answeared_correct, $answeared_incorrect, $start){

  // scriem in  user_questionnaires   ( id este AUTO_INCREMENT, nu trebuie sa il setam si created_at este CURRENT_TIMESTAMP )
  $end = date('Y-m-d H:i:s');
  global $conn;
  $sql = "INSERT INTO user_questionnaires (user_id, answered_correct, answered_incorrect, started_at, finished_at) 
    VALUES ($USER_ID, $answeared_correct, $answeared_incorrect, '$start', '$end')";
  mysqli_query($conn, $sql);



}






?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Rezultate Chestionar</title>
  <link rel="icon" type="image/png" href="logo_robest.png">
  <link rel="shortcut icon" type="image/png" href="logo_robest.png">
  <link rel="apple-touch-icon" href="logo_robest.png">
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
    <h1>Rezultate Chestionar</h1>
    
    <?php for ($i = 0; $i < count($chestionar->questions); $i++): ?>
      <?php 
        $question = $chestionar->questions[$i];
        $question_num = $i + 1;
      ?>
      
      <div class="question">
        <p><?php echo $question_num; ?>. <?php echo htmlspecialchars($question->description); ?></p>
        
        <!-- Raspuns 1 -->
        <div class="answer-option <?php echo check($raspuns[$i]->answer1, $question->answers1_correct) ?>">
          <?php echo htmlspecialchars($question->answers1); ?>
        </div>
        
        <!-- Raspuns 2 -->
        <div class="answer-option <?php echo check($raspuns[$i]->answer2, $question->answers2_correct) ?>">
          <?php echo htmlspecialchars($question->answers2); ?>
        </div>
        
        <!-- Raspuns 3 -->
        <div class="answer-option <?php echo check($raspuns[$i]->answer3, $question->answers3_correct) ?>">
          <?php echo htmlspecialchars($question->answers3); ?>
        </div>

        <?php  $count = $count + valid($raspuns[$i]->answer1,$raspuns[$i]->answer2,$raspuns[$i]->answer3,$question->answers1_correct,$question->answers2_correct,$question->answers3_correct)?>
      </div>
      
    <?php endfor; ?>
    <h2>Scorul final: <?php echo $count ?> / 10</h2>
    <h3><?php if($count == 10) echo "Felicitari!Ai rezolvat perfect acest chestionar! ☺️"; ?> </h3>

    <div class="navigation-buttons">
      <a href="reset2.php" class="btn secondary">Încearcă din nou</a>
      <a href="reset.php" class="btn">Înapoi la Dashboard</a>
    </div>
  </div>

  <?php if (!isset($_SESSION['results_written'])) {
    write($userID, $count, 10 - $count, $_SESSION['quiz_start_time']);
    $_SESSION['results_written'] = true;
                                                  }   ?>  <!-- rulat ultimul dupa ce e calculat tot si rulam o singura data -->
  
</body>
</html>