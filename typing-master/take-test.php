<?php
require_once 'includes/auth.php';
require_once '../database/config.php';

$test_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$test_content = "The quick brown fox jumps over the lazy dog. Practice makes perfect."; // Default fallback
$duration_minutes = 1;
$test_title = "Quick Practice";

if ($test_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM typing_practice_tests WHERE id = :id");
        $stmt->execute([':id' => $test_id]);
        $test = $stmt->fetch();
        if ($test) {
            $test_content = strip_tags($test['test_content']); // Remove HTML tags
            $test_content = preg_replace('/\s+/', ' ', trim($test_content)); // Clean whitespace
            $duration_minutes = $test['duration_minutes'];
            $test_title = $test['test_title'];
        }
    } catch (PDOException $e) { }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Take Test - <?php echo htmlspecialchars($test_title); ?></title>
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
            <i class="fas fa-keyboard me-2 text-info"></i>
            <span class="fw-bold"><?php echo htmlspecialchars($test_title); ?></span>
        </div>
        <div class="d-flex align-items-center gap-4">
            <div class="stat-badge"><i class="fas fa-tachometer-alt me-2"></i>WPM: <span id="wpm-display">0</span></div>
            <div class="stat-badge"><i class="fas fa-bullseye me-2"></i>Acc: <span id="acc-display">100%</span></div>
            <div class="timer-box text-warning" id="timer">--:--</div>
            <button class="btn btn-danger btn-sm fw-bold px-3 d-none" id="submit-btn" onclick="finishTest()">SUBMIT</button>
        </div>
    </div>

    <div class="main-container">
        <!-- Typing Area -->
        <div class="typing-area">
            <div class="text-display-box" id="text-display">
                <!-- Text will be injected via JS -->
            </div>
            
            <div class="input-area">
                <textarea id="typing-input" placeholder="Click 'Start Test' button to begin..." spellcheck="false" onpaste="return false;" disabled></textarea>
            </div>
            
            <div class="text-center text-muted small mt-2">
                <i class="fas fa-info-circle me-1"></i> Press Space to move to next word. Backspace behavior depends on settings.
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
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="backspace_opt" id="bs_one" value="one">
                    <label class="form-check-label small" for="bs_one">One Word Backspace</label>
                </div>
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
               <button class="btn btn-success w-100 fw-bold mb-2 p-2" id="start-btn" onclick="startTestLogic()"><i class="fas fa-play me-2"></i> Start Test</button>
               <a href="dashboard.php" class="btn btn-outline-secondary w-100 btn-sm" id="exit-btn" style="display: none;">Exit Test</a>
               <a href="dashboard.php" class="btn btn-outline-secondary w-100 btn-sm" id="back-btn">Back to Dashboard</a>
            </div>
        </div>
    </div>

    <!-- Hidden Data -->
    <input type="hidden" id="raw-text" value="<?php echo htmlspecialchars($test_content); ?>">
    <input type="hidden" id="duration-min" value="<?php echo $duration_minutes; ?>">
    <input type="hidden" id="test-id" value="<?php echo $test_id; ?>">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // --- Core Logic ---
        const rawText = document.getElementById('raw-text').value;
        const words = rawText.split(' ').filter(w => w.length > 0);
        const textDisplay = document.getElementById('text-display');
        const inputField = document.getElementById('typing-input');
        const timerDisplay = document.getElementById('timer');
        
        let currentWordIndex = 0;
        let startTime = null;
        let timerInterval = null;
        let isRunning = false;
        let timeRemaining = parseInt(document.getElementById('duration-min').value) * 60;
        let totalTime = timeRemaining;
        
        let correctKeystrokes = 0;
        let totalKeystrokes = 0;
        let errorCount = 0;
        
        // Track the starting index of the *current word* in the textarea to lock previous content
        let currentInputStartIndex = 0;
        
        // --- Initialization ---
        function init() {
            // Render words
            textDisplay.innerHTML = words.map((word, index) => 
                `<span class="word ${index === 0 ? 'current' : ''}" id="w-${index}">${word}</span>`
            ).join(' ');
            
            updateTimerDisplay();
        }
        
        // --- Event Listeners ---
        inputField.addEventListener('input', (e) => {
            if (!isRunning) return;
            
            const fullText = inputField.value;
            const currentTyped = fullText.substring(currentInputStartIndex);
            
            const currentWordSpan = document.getElementById(`w-${currentWordIndex}`);
            const targetWord = words[currentWordIndex];
            
            // Handle Spacebar (Word Submission)
            if (e.data === ' ' || currentTyped.endsWith(' ')) {
                const typedWord = currentTyped.trim(); // The word they just typed
                
                if (typedWord.length > 0) {
                    // Check correctness
                    if (typedWord === targetWord) {
                        currentWordSpan.classList.add('correct');
                        currentWordSpan.classList.remove('current', 'incorrect');
                        correctKeystrokes += targetWord.length + 1; // +1 for space
                    } else {
                        currentWordSpan.classList.add('incorrect');
                        currentWordSpan.classList.remove('current');
                        errorCount++;
                    }
                    
                    totalKeystrokes += typedWord.length + 1;
                    
                    // Move to next word
                    currentWordIndex++;
                    
                    // Lock current progress
                    currentInputStartIndex = inputField.value.length;
                    
                    if (currentWordIndex < words.length) {
                        const nextSpan = document.getElementById(`w-${currentWordIndex}`);
                        nextSpan.classList.add('current');
                        
                        // Auto Scroll (Reference Text)
                        if (document.getElementById('auto_scroll').checked) {
                            const containerRect = textDisplay.getBoundingClientRect();
                            const spanRect = nextSpan.getBoundingClientRect();
                            if (spanRect.bottom > containerRect.bottom - 50) {
                                nextSpan.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        }
                        
                         // Auto Scroll (Input Textarea)
                        if (document.getElementById('auto_scroll').checked) {
                            inputField.scrollTop = inputField.scrollHeight;
                        }

                    } else {
                        finishTest(); // All words typed
                    }
                } else {
                    // Prevent double spaces or space at start of word if strict? 
                    // For now, allow it but don't advance if empty. 
                    // To keep things clean, we might reset the input value to strip the trailing space
                    // if it was just a space. But that fights user input. 
                    // Let's just update start index to skip the extra space
                    currentInputStartIndex = inputField.value.length;
                }
            } else {
                // Real-time highlighting
                 if (!targetWord.startsWith(currentTyped)) {
                    currentWordSpan.classList.add('incorrect');
                } else {
                    currentWordSpan.classList.remove('incorrect');
                }
            }
            
            updateStats();
        });
        
        // --- Backspace & Locking Logic ---
        inputField.addEventListener('keydown', (e) => {
            if (!isRunning) {
                e.preventDefault();
                return;
            }

            // Prevent editing previous words (History Lock)
            if (inputField.selectionStart < currentInputStartIndex) {
                // Allow only navigating forward or valid keys? 
                // Mostly just block modification
                if(e.key.length === 1 || e.key === 'Backspace' || e.key === 'Delete') {
                    e.preventDefault();
                }
            }
            
            // Backspace Options
            if (e.key === 'Backspace') {
                // Also prevent backspacing into locked area (redundant check but safe)
                if (inputField.selectionStart === currentInputStartIndex) {
                    e.preventDefault();
                    return;
                }
                
                const opt = document.querySelector('input[name="backspace_opt"]:checked').value;
                if (opt === 'off') {
                    e.preventDefault();
                } else if (opt === 'one') {
                     // One word backspace is allowed implicitly because we only allow editing current word
                     // So no special logic needed here as History Lock handles previous words
                }
            }
        });

        // Prevent clicking/selecting previous text to cheat history
        inputField.addEventListener('mousedown', (e) => {
            // We can't easily block click position but we can correct focus or generic validation
        });
        
        // Stop pasting
        inputField.addEventListener('paste', e => e.preventDefault());

        function startTestLogic() {
            isRunning = true;
            startTime = new Date();
            
            // UI Updates
            document.getElementById('start-btn').style.display = 'none';
            document.getElementById('back-btn').style.display = 'none';
            document.getElementById('exit-btn').style.display = 'block';
            document.getElementById('submit-btn').classList.remove('d-none');
            
            inputField.disabled = false;
            inputField.value = ''; // Ensure clean start
            inputField.focus();
            currentInputStartIndex = 0;
            
            timerInterval = setInterval(() => {
                timeRemaining--;
                updateTimerDisplay();
                updateStats();
                
                if (timeRemaining <= 0) {
                    finishTest();
                }
            }, 1000);
        }
        
        function updateTimerDisplay() {
            const m = Math.floor(timeRemaining / 60);
            const s = timeRemaining % 60;
            timerDisplay.textContent = `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
        }
        
        function updateStats() {
            if (!startTime) return;
            const elapsedMin = (totalTime - timeRemaining) / 60;
            if (elapsedMin <= 0) return;
            
            const wpm = Math.round(((totalKeystrokes / 5) / elapsedMin));
            
            let acc = 100;
            if (currentWordIndex > 0) {
                acc = Math.round(((currentWordIndex - errorCount) / currentWordIndex) * 100);
            }
            
            document.getElementById('wpm-display').textContent = wpm || 0;
            document.getElementById('acc-display').textContent = acc + '%';
        }
        
        window.finishTest = function() {
            clearInterval(timerInterval);
            isRunning = false;
            inputField.disabled = true;
            
            const timeTaken = totalTime - timeRemaining;
            const elapsedMin = Math.max(timeTaken / 60, 0.1); 
            
            const wpm = Math.round(((totalKeystrokes / 5) / elapsedMin));
            let acc = 0;
            if (currentWordIndex > 0) {
                acc = ((currentWordIndex - errorCount) / currentWordIndex) * 100;
            }
            
            // Submit Data
            $.ajax({
                url: 'submit-test.php',
                type: 'POST',
                data: {
                    test_id: document.getElementById('test-id').value,
                    wpm: wpm,
                    accuracy: acc.toFixed(2),
                    errors: errorCount,
                    duration_seconds: timeTaken,
                    total_words: words.length,
                    typed_content: inputField.value // Send full typed content
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
        
        // Handle Highlight Toggle
        document.querySelectorAll('input[name="highlight_opt"]').forEach(el => {
            el.addEventListener('change', (e) => {
                if(e.target.value === 'off') {
                   textDisplay.classList.add('no-highlight');
                } else {
                   textDisplay.classList.remove('no-highlight');
                }
            });
        });
        
        // Start
        init();
    </script>
</body>
</html>
