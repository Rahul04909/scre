<?php
require_once 'includes/auth.php';
require_once '../database/config.php';

$lesson_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$lesson_content = "F J F J F J F J"; // Default fallback
$lesson_title = "Lesson 1: Home Row";
$duration_minutes = 5; // Default max time, though lessons might be completion based

if ($lesson_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM typing_lessons WHERE id = :id");
        $stmt->execute([':id' => $lesson_id]);
        $lesson = $stmt->fetch();
        if ($lesson) {
            $lesson_content = strip_tags($lesson['lesson_content']); 
            $lesson_content = preg_replace('/\s+/', ' ', trim($lesson_content)); 
            $lesson_title = $lesson['lesson_title'];
            // $duration_minutes = $lesson['duration_minutes'] ?? 10; // If lessons have duration
        }
    } catch (PDOException $e) { }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Practice Lesson - <?php echo htmlspecialchars($lesson_title); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;500;700&family=Plus+Jakarta+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { background-color: #e5e7eb; font-family: 'Plus Jakarta Sans', sans-serif; height: 100vh; overflow: hidden; }
        
        .top-bar {
            background: #111827; color: white; height: 60px; display: flex; align-items: center; justify-content: space-between; padding: 0 2rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        
        .main-container {
            display: flex; height: calc(100vh - 60px);
        }
        
        .typing-area {
            flex: 1; padding: 1.5rem; display: flex; flex-direction: column; gap: 0.8rem;
            max-width: 1000px; margin: 0 auto;
        }
        
        .settings-panel {
            width: 300px; background: #f3f4f6; border-left: 1px solid #d1d5db; padding: 1.5rem;
            overflow-y: auto;
        }
        
        .text-display-box {
            background: white; border: 1px solid #d1d5db; border-radius: 8px; padding: 1rem;
            font-family: 'Roboto Mono', monospace; font-size: 1.1rem; line-height: 1.6;
            height: 250px; overflow-y: auto; user-select: none; color: #6b7280;
            position: relative; white-space: pre-wrap;
        }
        
        .word { padding: 0 2px; border-radius: 3px; }
        .word.current { background-color: #e5e7eb; border-bottom: 2px solid #0f766e; }
        .word.correct { color: #059669; }
        .word.incorrect { color: #dc2626; background-color: #fecaca; }

        /* No Highlight Overrides */
        .no-highlight .word.correct { color: inherit; background-color: transparent; }
        .no-highlight .word.incorrect { color: inherit; background-color: transparent; border-bottom: 2px solid red; }
        .no-highlight .word.current { background-color: #e5e7eb; border-bottom: 2px solid #0f766e; }
        
        .input-area textarea {
            width: 100%; height: 200px; padding: 1rem; font-family: 'Roboto Mono', monospace; font-size: 1.1rem;
            border: 2px solid #0f766e; border-radius: 8px; resize: none; outline: none;
        }
        .input-area textarea:disabled { background-color: #f9fafb; border-color: #d1d5db; cursor: not-allowed; }

        .timer-box { font-size: 1.5rem; font-weight: bold; font-family: 'Roboto Mono', monospace; }
        .stat-badge { background: rgba(255,255,255,0.1); padding: 5px 15px; border-radius: 20px; font-size: 0.9rem; }
        
        /* Settings Styles */
        .option-group { margin-bottom: 1.5rem; background: white; padding: 1rem; border-radius: 8px; border: 1px solid #e5e7eb; }
        .option-title { font-weight: 600; color: #374151; margin-bottom: 0.8rem; font-size: 0.9rem; border-bottom: 1px solid #f3f4f6; padding-bottom: 0.5rem; }
        .form-check-input:checked { background-color: #0f766e; border-color: #0f766e; }
    </style>
</head>
<body>

    <!-- Top Header -->
    <div class="top-bar">
        <div class="d-flex align-items-center">
            <i class="fas fa-book-open me-2 text-info"></i>
            <span class="fw-bold"><?php echo htmlspecialchars($lesson_title); ?></span>
        </div>
        <div class="d-flex align-items-center gap-4">
            <div class="stat-badge"><i class="fas fa-tachometer-alt me-2"></i>WPM: <span id="wpm-display">0</span></div>
            <div class="stat-badge"><i class="fas fa-bullseye me-2"></i>Acc: <span id="acc-display">100%</span></div>
            <div class="timer-box text-warning" id="timer">00:00</div>
            <button class="btn btn-danger btn-sm fw-bold px-3 d-none" id="submit-btn" onclick="finishLesson()">FINISH</button>
        </div>
    </div>

    <div class="main-container">
        <!-- Typing Area -->
        <div class="typing-area">
            <div class="text-display-box" id="text-display">
                <!-- Text will be injected via JS -->
            </div>
            
            <div class="input-area">
                <textarea id="typing-input" placeholder="Click 'Start Lesson' button to begin..." spellcheck="false" onpaste="return false;" disabled></textarea>
            </div>
            
            <div class="text-center text-muted small mt-2">
                <i class="fas fa-info-circle me-1"></i> Type the text exactly as shown.
            </div>
        </div>

        <!-- Right Settings Panel -->
        <div class="settings-panel shadow-sm">
            <h5 class="fw-bold mb-3 text-dark"><i class="fas fa-cog me-2"></i>Settings</h5>
            
            <div class="option-group">
                <div class="option-title">Backspace Options</div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="backspace_opt" id="bs_full" value="full" checked>
                    <label class="form-check-label small" for="bs_full">Full Backspace</label>
                </div>
                <!-- Simpler options for lessons -->
                <div class="form-check">
                     <input class="form-check-input" type="radio" name="backspace_opt" id="bs_off" value="off">
                     <label class="form-check-label small" for="bs_off">Deactivate Backspace</label>
                </div>
            </div>

            <div class="option-group">
                <div class="option-title">Highlight Options</div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="highlight_opt" id="hl_word" value="word" checked>
                    <label class="form-check-label small" for="hl_word">Word Highlight</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="highlight_opt" id="hl_off" value="off">
                    <label class="form-check-label small" for="hl_off">No Highlight</label>
                </div>
            </div>

            <div class="option-group">
                <div class="option-title">Scroll Options</div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="auto_scroll" checked>
                    <label class="form-check-label small" for="auto_scroll">Auto Scroll</label>
                </div>
            </div>
            
            <div class="mt-4">
               <button class="btn btn-success w-100 fw-bold mb-2 p-2" id="start-btn" onclick="startLessonLogic()"><i class="fas fa-play me-2"></i> Start Lesson</button>
               <a href="lessons.php" class="btn btn-outline-secondary w-100 btn-sm" id="exit-btn" style="display: none;">Exit Lesson</a>
               <a href="lessons.php" class="btn btn-outline-secondary w-100 btn-sm" id="back-btn">Back to Lessons</a>
            </div>
        </div>
    </div>

    <!-- Hidden Data -->
    <input type="hidden" id="raw-text" value="<?php echo htmlspecialchars($lesson_content); ?>">
    <input type="hidden" id="lesson-id" value="<?php echo $lesson_id; ?>">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // --- Core Logic (Lessons often count UP time, not down) ---
        const rawText = document.getElementById('raw-text').value;
        const words = rawText.split(' ').filter(w => w.length > 0);
        const textDisplay = document.getElementById('text-display');
        const inputField = document.getElementById('typing-input');
        const timerDisplay = document.getElementById('timer');
        
        let currentWordIndex = 0;
        let startTime = null;
        let timerInterval = null;
        let isRunning = false;
        let timeElapsed = 0;
        
        let correctKeystrokes = 0;
        let totalKeystrokes = 0;
        let errorCount = 0;
        
        let currentInputStartIndex = 0;
        
        function init() {
            textDisplay.innerHTML = words.map((word, index) => 
                `<span class="word ${index === 0 ? 'current' : ''}" id="w-${index}">${word}</span>`
            ).join(' ');
        }
        
        inputField.addEventListener('input', (e) => {
            if (!isRunning) return;
            
            const fullText = inputField.value;
            const currentTyped = fullText.substring(currentInputStartIndex);
            const currentWordSpan = document.getElementById(`w-${currentWordIndex}`);
            const targetWord = words[currentWordIndex];
            
            if (e.data === ' ' || currentTyped.endsWith(' ')) {
                const typedWord = currentTyped.trim();
                
                if (typedWord.length > 0) {
                    if (typedWord === targetWord) {
                        currentWordSpan.classList.add('correct');
                        currentWordSpan.classList.remove('current', 'incorrect');
                        correctKeystrokes += targetWord.length + 1;
                    } else {
                        currentWordSpan.classList.add('incorrect');
                        currentWordSpan.classList.remove('current');
                        errorCount++;
                    }
                    
                    totalKeystrokes += typedWord.length + 1;
                    currentWordIndex++;
                    currentInputStartIndex = inputField.value.length;
                    
                    if (currentWordIndex < words.length) {
                        const nextSpan = document.getElementById(`w-${currentWordIndex}`);
                        nextSpan.classList.add('current');
                        
                        if (document.getElementById('auto_scroll').checked) {
                            const containerRect = textDisplay.getBoundingClientRect();
                            const spanRect = nextSpan.getBoundingClientRect();
                            if (spanRect.bottom > containerRect.bottom - 50) {
                                nextSpan.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                            inputField.scrollTop = inputField.scrollHeight;
                        }
                    } else {
                        finishLesson();
                    }
                } else {
                     currentInputStartIndex = inputField.value.length;
                }
            } else {
                 if (!targetWord.startsWith(currentTyped)) {
                    currentWordSpan.classList.add('incorrect');
                } else {
                    currentWordSpan.classList.remove('incorrect');
                }
            }
            updateStats();
        });
        
        inputField.addEventListener('keydown', (e) => {
            if (!isRunning) { e.preventDefault(); return; }
            if (inputField.selectionStart < currentInputStartIndex) {
                if(e.key.length === 1 || e.key === 'Backspace' || e.key === 'Delete') {
                    e.preventDefault();
                }
            }
            if (e.key === 'Backspace') {
                 if (inputField.selectionStart === currentInputStartIndex) {
                    e.preventDefault();
                    return;
                }
                const opt = document.querySelector('input[name="backspace_opt"]:checked').value;
                if (opt === 'off') e.preventDefault();
            }
        });
        
        inputField.addEventListener('paste', e => e.preventDefault());

        function startLessonLogic() {
            isRunning = true;
            startTime = new Date();
            
            document.getElementById('start-btn').style.display = 'none';
            document.getElementById('back-btn').style.display = 'none';
            document.getElementById('exit-btn').style.display = 'block';
            document.getElementById('submit-btn').classList.remove('d-none');
            
            inputField.disabled = false;
            inputField.value = '';
            inputField.focus();
            currentInputStartIndex = 0;
            
            timerInterval = setInterval(() => {
                timeElapsed++;
                updateTimerDisplay();
                updateStats();
            }, 1000);
        }
        
        function updateTimerDisplay() {
            const m = Math.floor(timeElapsed / 60);
            const s = timeElapsed % 60;
            timerDisplay.textContent = `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
        }
        
        function updateStats() {
            if (!startTime) return;
            const elapsedMin = Math.max(timeElapsed / 60, 0.01);
            const wpm = Math.round(((totalKeystrokes / 5) / elapsedMin));
            let acc = 100;
            if (currentWordIndex > 0) {
                acc = Math.round(((currentWordIndex - errorCount) / currentWordIndex) * 100);
            }
            document.getElementById('wpm-display').textContent = wpm || 0;
            document.getElementById('acc-display').textContent = acc + '%';
        }
        
        window.finishLesson = function() {
            clearInterval(timerInterval);
            isRunning = false;
            inputField.disabled = true;
            
            const elapsedMin = Math.max(timeElapsed / 60, 0.01);
            const wpm = Math.round(((totalKeystrokes / 5) / elapsedMin));
            let acc = 0;
            if (currentWordIndex > 0) {
                acc = ((currentWordIndex - errorCount) / currentWordIndex) * 100;
            }
            
            $.ajax({
                url: 'submit-lesson.php',
                type: 'POST',
                data: {
                    lesson_id: document.getElementById('lesson-id').value,
                    wpm: wpm,
                    accuracy: acc.toFixed(2),
                    errors: errorCount,
                    duration_seconds: timeElapsed,
                    total_words: words.length,
                    typed_content: inputField.value
                },
                success: function(res) {
                    if(res.success) {
                        window.location.href = res.redirect;
                    } else {
                        alert('Error submitting result: ' + res.message);
                    }
                }
            });
        };
        
        document.querySelectorAll('input[name="highlight_opt"]').forEach(el => {
            el.addEventListener('change', (e) => {
                if(e.target.value === 'off') {
                   textDisplay.classList.add('no-highlight');
                } else {
                   textDisplay.classList.remove('no-highlight');
                }
            });
        });
        
        init();
    </script>
</body>
</html>
