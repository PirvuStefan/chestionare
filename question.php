<?php

// Add these lines FIRST, before any other code
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('connection.php');

// Set UTF-8 encoding for database connection
mysqli_set_charset($conn, "utf8");

function initialise_user($cookie_token){
    global $conn;
    $sql = "SELECT user_id FROM remember_tokens_web WHERE token = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $cookie_token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Check if token is expired (30 days from created_at)
    $sql_check = "SELECT created_at FROM remember_tokens_web WHERE token = ?";
    $stmt_check = mysqli_prepare($conn, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "s", $cookie_token);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    if ($row_check = mysqli_fetch_assoc($result_check)) {
        $created_at = strtotime($row_check['created_at']);
        $expiry = strtotime('+30 days', $created_at);
        if (time() > $expiry) {
            // Token expired - delete from database and unset cookie
            $sql_delete = "DELETE FROM remember_tokens_web WHERE token = ?";
            $stmt_delete = mysqli_prepare($conn, $sql_delete);
            mysqli_stmt_bind_param($stmt_delete, "s", $cookie_token);
            mysqli_stmt_execute($stmt_delete);
            setcookie('remember_token', '', time() - 3600, '/');
            return null;
        }
    }

    if ($row = mysqli_fetch_assoc($result)) {
        return $row['user_id'];
    }
    return null;
}

// Check session first, then fall back to cookie
$userID = $_SESSION['userID'] ?? null;

if (!$userID) {
    $cookie_token = $_COOKIE['remember_token'] ?? null;
    if (!$cookie_token) {
        header("Location: index.php");
        exit();
    }

    $userID = initialise_user($cookie_token);
    if (!$userID) {
        header("Location: index.php");
        exit();
    }
    
    $_SESSION['userID'] = $userID;
}




function get_random_questionnaire() {
    global $conn;

    return 2;
    
   
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
    public bool $answers1_correct;
    public bool $answers2_correct;
    public bool $answers3_correct;

    public function __construct(int $id){
      $this->id = $id;
    }
}// firecare intrebare are un id, o descriere si 3 raspunsuri, fiecare cu un camp de corectitudine


class Answers {

    public bool $answer1, $answer2, $answer3;
    public function __construct(bool $answer1, bool $answer2, bool $answer3) {
        $this->answer1 = $answer1;
        $this->answer2 = $answer2;
        $this->answer3 = $answer3;
    }
}

$raspunsuri = [];

// Process submitted answers
if (isset($_POST['user_answers'])) {
    $user_answers = json_decode($_POST['user_answers'], true);
    
    // Initialize $raspunsuri with 10 Answers objects
    for ($i = 0; $i < 10; $i++) {
        $question_num = $i + 1;
        $user_question_answers = $user_answers["q$question_num"] ?? [];
        
        // Check if each answer was selected
        $answer1_selected = in_array("q{$question_num}a", $user_question_answers);
        $answer2_selected = in_array("q{$question_num}b", $user_question_answers);
        $answer3_selected = in_array("q{$question_num}c", $user_question_answers);
        
        // Create Answers object with true/false values
        $raspunsuri[$i] = new Answers($answer1_selected, $answer2_selected, $answer3_selected);
    }
    
    // Store in session for use in other pages
    $_SESSION['raspunsuri'] = $raspunsuri;
    $_SESSION['activ'] = true;
    
    // Redirect to answers page
    header('Location: answers.php');
    exit();
} else {
    // Initialize empty $raspunsuri for first load
    for ($i = 0; $i < 10; $i++) {
        $raspunsuri[$i] = new Answers(false, false, false);
    }
}

$chestionar = initialise_questionnaire();
$_SESSION['chestionar'] = $chestionar;

// Ensure no debug output or answer visibility during quiz
if (!isset($_POST['user_answers'])) {
    // Clear any previous session data that might show answers
    unset($_SESSION['raspunsuri']);
    unset($_SESSION['activ']);
}

?>




<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="icon" type="image/png" href="logo_robest.png">
  <link rel="shortcut icon" type="image/png" href="logo_robest.png">
  <link rel="apple-touch-icon" href="logo_robest.png">
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Chestionar</title>
  <style>
    :root {
      --glass-bg: rgba(0, 123, 255, 0.15);
      --glass-border: rgba(0, 123, 255, 0.3);
      --glass-shadow: rgba(0, 123, 255, 0.4);
      --text-color: #fff;
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
    }

    .question {
      margin-bottom: 1.5rem;
    }

    .question p {
      margin-bottom: 0.5rem;
      font-weight: bold;
    }

    .question label {
      display: block;
      user-select : none;
      padding: 0.5rem;
      margin: 0.3rem 0;
      border-radius: 12px;
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid transparent;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .question input[type="checkbox"]:checked + label {
      background: rgba(0, 123, 255, 0.4);
      border-color: #00aaff;
    }

    .progress-bar {
      height: 20px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 10px;
      overflow: hidden;
      margin-bottom: 1.5rem;
    }

    .progress-bar-inner {
      height: 100%;
      width: 0%;
      background: linear-gradient(90deg, #00c6ff, #0072ff);
      transition: width 0.5s ease-in-out;
    }

    button {
      background: #007bff;
      user-select: none;
      color: white;
      border: none;
      padding: 0.8rem 1.5rem;
      border-radius: 12px;
      cursor: pointer;
      font-size: 1rem;
      width: 100%;
      transition: background 0.3s;
    }

    button:hover {
      background:rgb(24, 204, 0);
    }
    
    /* Hide any potential debug or answer information */
    .debug-info, .correct-answer, .answer-key {
      display: none !important;
    }
    
    /* Prevent text selection of question/answer content to avoid cheating */
    .question p, .question label {
      -webkit-user-select: none;
      -moz-user-select: none;
      -ms-user-select: none;
      user-select: none;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Chestionar</h1>
    <div class="progress-bar">
      <div class="progress-bar-inner" id="progress"></div>
    </div>
    <form id="quizForm">
      <!-- Example of one question block -->
      <div class="question">
        <p>1. <?php echo htmlspecialchars($chestionar->questions[0]->description); ?></p>
        <input type="checkbox" id="q1a" name="q1" value="Red" hidden>
        <label for="q1a"><?php echo htmlspecialchars($chestionar->questions[0]->answers1); ?></label>
        <input type="checkbox" id="q1b" name="q1" value="Blue" hidden>
        <label for="q1b"><?php echo htmlspecialchars($chestionar->questions[0]->answers2); ?></label>
        <input type="checkbox" id="q1c" name="q1" value="Green" hidden>
        <label for="q1c"><?php echo htmlspecialchars($chestionar->questions[0]->answers3); ?></label>
      </div>

      <div class="question">
        <p>2. <?php echo $chestionar->questions[1]->description ?></p>
        <input type="checkbox" id="q2a" name="q2" value="Spring" hidden>
        <label for="q2a"><?php echo $chestionar->questions[1]->answers1 ?></label>
        <input type="checkbox" id="q2b" name="q2" value="Summer" hidden>
        <label for="q2b"><?php echo $chestionar->questions[1]->answers2 ?></label>
        <input type="checkbox" id="q2c" name="q2" value="Winter" hidden>
        <label for="q2c"><?php echo $chestionar->questions[1]->answers3 ?></label>
      </div>

      <div class="question">
        <p>3. <?php echo $chestionar->questions[2]->description ?></p>
        <input type="checkbox" id="q3a" name="q3" value="Dog" hidden>
        <label for="q3a"><?php echo $chestionar->questions[2]->answers1 ?></label>
        <input type="checkbox" id="q3b" name="q3" value="Cat" hidden>
        <label for="q3b"><?php echo $chestionar->questions[2]->answers2 ?></label>
        <input type="checkbox" id="q3c" name="q3" value="Parrot" hidden>
        <label for="q3c"><?php echo $chestionar->questions[2]->answers3 ?></label>
      </div>

      <div class="question">
        <p>4. <?php echo $chestionar->questions[3]->description ?></p>
        <input type="checkbox" id="q4a" name="q4" value="Pop" hidden>
        <label for="q4a"><?php echo $chestionar->questions[3]->answers1 ?></label>
        <input type="checkbox" id="q4b" name="q4" value="Rock" hidden>
        <label for="q4b"><?php echo $chestionar->questions[3]->answers2 ?></label>
        <input type="checkbox" id="q4c" name="q4" value="Jazz" hidden>
        <label for="q4c"><?php echo $chestionar->questions[3]->answers3 ?></label>
      </div>

      <div class="question">
        <p>5. <?php echo $chestionar->questions[4]->description ?></p>
        <input type="checkbox" id="q5a" name="q5" value="Morning" hidden>
        <label for="q5a"><?php echo $chestionar->questions[4]->answers1 ?></label>
        <input type="checkbox" id="q5b" name="q5" value="Evening" hidden>
        <label for="q5b"><?php echo $chestionar->questions[4]->answers2 ?></label>
        <input type="checkbox" id="q5c" name="q5" value="Night" hidden>
        <label for="q5c"><?php echo $chestionar->questions[4]->answers3 ?></label>
      </div>

      <div class="question">
        <p>6. <?php echo $chestionar->questions[5]->description ?></p>
        <input type="checkbox" id="q6a" name="q6" value="Tea" hidden>
        <label for="q6a"><?php echo $chestionar->questions[5]->answers1 ?></label>
        <input type="checkbox" id="q6b" name="q6" value="Coffee" hidden>
        <label for="q6b"><?php echo $chestionar->questions[5]->answers2 ?></label>
        <input type="checkbox" id="q6c" name="q6" value="Juice" hidden>
        <label for="q6c"><?php echo $chestionar->questions[5]->answers3 ?></label>
      </div>

      <div class="question">
        <p>7. <?php echo $chestionar->questions[6]->description ?></p>
        <input type="checkbox" id="q7a" name="q7" value="Car" hidden>
        <label for="q7a"><?php echo $chestionar->questions[6]->answers1 ?></label>
        <input type="checkbox" id="q7b" name="q7" value="Plane" hidden>
        <label for="q7b"><?php echo $chestionar->questions[6]->answers2 ?></label>
        <input type="checkbox" id="q7c" name="q7" value="Train" hidden>
        <label for="q7c"><?php echo $chestionar->questions[6]->answers3 ?></label>
      </div>

      <div class="question">
        <p>8. <?php echo $chestionar->questions[7]->description ?></p>
        <input type="checkbox" id="q8a" name="q8" value="Phone" hidden>
        <label for="q8a"><?php echo $chestionar->questions[7]->answers1 ?></label>
        <input type="checkbox" id="q8b" name="q8" value="Tablet" hidden>
        <label for="q8b"><?php echo $chestionar->questions[7]->answers2 ?></label>
        <input type="checkbox" id="q8c" name="q8" value="Laptop" hidden>
        <label for="q8c"><?php echo $chestionar->questions[7]->answers3 ?></label>
      </div>

      <div class="question">
        <p>9. <?php echo $chestionar->questions[8]->description ?></p>
        <input type="checkbox" id="q9a" name="q9" value="Italian" hidden>
        <label for="q9a"><?php echo $chestionar->questions[8]->answers1 ?></label>
        <input type="checkbox" id="q9b" name="q9" value="Chinese" hidden>
        <label for="q9b"><?php echo $chestionar->questions[8]->answers2 ?></label>
        <input type="checkbox" id="q9c" name="q9" value="Indian" hidden>
        <label for="q9c"><?php echo $chestionar->questions[8]->answers3 ?></label>
      </div>

      <div class="question">
        <p>10. <?php echo $chestionar->questions[9]->description ?></p>
        <input type="checkbox" id="q10a" name="q10" value="English" hidden>
        <label for="q10a"><?php echo $chestionar->questions[9]->answers1 ?></label>
        <input type="checkbox" id="q10b" name="q10" value="Spanish" hidden>
        <label for="q10b"><?php echo $chestionar->questions[9]->answers2 ?></label>
        <input type="checkbox" id="q10c" name="q10" value="Japanese" hidden>
        <label for="q10c"><?php echo $chestionar->questions[9]->answers3 ?></label>
      </div>

      <div id="error-message" style="display: none; color: #ff4757; background: rgba(255, 71, 87, 0.1); border: 1px solid #ff4757; border-radius: 8px; padding: 10px; margin: 10px 0; text-align: center; font-weight: bold;"></div>
      <button type="submit">Submit</button>
    </form>
  </div>

  <script>
  const form = document.getElementById('quizForm');
  const progress = document.getElementById('progress');
  const errorMessage = document.getElementById('error-message');
  const totalQuestions = 10;

  function updateProgress() {
    let answered = 0;
    for (let i = 1; i <= totalQuestions; i++) {
      const checkboxes = form.querySelectorAll(`input[name="q${i}"]`);
      const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
      if (anyChecked) answered++;
    }
    progress.style.width = (answered / totalQuestions * 100) + '%';
  }

  function showError(message) {
    errorMessage.textContent = message;
    errorMessage.style.display = 'block';
    errorMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  function hideError() {
    errorMessage.style.display = 'none';
  }

  form.addEventListener('change', function() {
    updateProgress();
    hideError(); // Hide error when user starts answering
  });

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    hideError(); // Clear any previous errors
    
    const answers = {};
    
    for (let i = 1; i <= totalQuestions; i++) {
      const selected = Array.from(form.querySelectorAll(`input[name="q${i}"]:checked`)).map(cb => cb.id);
      if (selected.length === 0) {
        showError(`Te rog raspunde la intrebarea ${i}.`);
        return;
      }
      answers[`q${i}`] = selected;
    }
    
    // Create a hidden form to submit answers to the same page
    const hiddenForm = document.createElement('form');
    hiddenForm.method = 'POST';
    hiddenForm.action = 'question.php';
    
    const answersInput = document.createElement('input');
    answersInput.type = 'hidden';
    answersInput.name = 'user_answers';
    answersInput.value = JSON.stringify(answers);
    
    hiddenForm.appendChild(answersInput);
    document.body.appendChild(hiddenForm);
    hiddenForm.submit();
  });
 
  // Disable right-click context menu to prevent inspect element
  document.addEventListener('contextmenu', function(e) {
    e.preventDefault();
  });

  // Disable F12, Ctrl+Shift+I, Ctrl+U
  document.addEventListener('keydown', function(e) {
    if (e.key === 'F12' || 
        (e.ctrlKey && e.shiftKey && e.key === 'I') ||
        (e.ctrlKey && e.key === 'u')) {
      e.preventDefault();
    }
  });
</script>

</body>
</html>
