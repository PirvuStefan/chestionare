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

function initialise_questionnaire(){
  
  $questionnaire = new Questionnaire(get_random_questionnaire());
  
  if ($questionnaire->id === null) {
      return null; // gol
  }
  global $conn;
  // Get all question IDs for the selected questionnaire
  $query = "SELECT question_id FROM user_questionnaire_questions WHERE user_questionnaire_id = {$questionnaire->id} LIMIT 10";
  $result = mysqli_query($conn, $query);



  if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
      $question = new Question($row['question_id']);
      
      $description_query = "SELECT description FROM questions WHERE id = {$question->id}";
      $description_result = mysqli_query($conn, $description_query);
      if ($description_result && $row = mysqli_fetch_assoc($description_result)) {
        $question->description = $row['description'];
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

$chestionar = initialise_questionnaire();

$_SESSION['chestionar'] = $chestionar;

?>




<!DOCTYPE html>
<html lang="en">
<head>
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
  </style>
</head>
<body>
  <div class="container">
    <h1>Chestionar #<?php echo $chestionar->id ?></h1>
    <div class="progress-bar">
      <div class="progress-bar-inner" id="progress"></div>
    </div>
    <form id="quizForm">
      <!-- Example of one question block -->
      <div class="question">
        <p>1. <?php echo $chestionar->questions[0]->description; ?></p>
        <input type="checkbox" id="q1a" name="q1" value="Red" hidden>
        <label for="q1a"><?php echo $chestionar->questions[0]->answers1 ?></label>
        <input type="checkbox" id="q1b" name="q1" value="Blue" hidden>
        <label for="q1b"><?php echo $chestionar->questions[0]->answers2 ?></label>
        <input type="checkbox" id="q1c" name="q1" value="Green" hidden>
        <label for="q1c"><?php echo $chestionar->questions[0]->answers3 ?></label>
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
        <label for="q4c"><?php echo $chestionar->questions[3]->answers2 ?></label>
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

      <button type="submit">Submit</button>
    </form>
  </div>

  <script>
    const form = document.getElementById('quizForm');
    const progress = document.getElementById('progress');
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

    form.addEventListener('change', updateProgress);

    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const answers = {};
      for (let i = 1; i <= totalQuestions; i++) {
        const selected = Array.from(form.querySelectorAll(`input[name="q${i}"]:checked`)).map(cb => cb.value);
        if (selected.length === 0) {
          alert(`Please answer question ${i}.`);
          return;
        }
        answers[`q${i}`] = selected;
      }
      localStorage.setItem('multiChoiceAnswers', JSON.stringify(answers));
      alert("Your answers were saved successfully!");
      form.reset();
      updateProgress();
    });
  </script>
</body>
</html>
