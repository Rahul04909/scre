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
            flex: 1; padding: 2rem; display: flex; flex-direction: column; gap: 1rem;
            max-width: 1000px; margin: 0 auto;
        }
        
        .settings-panel {
            width: 300px; background: #f3f4f6; border-left: 1px solid #d1d5db; padding: 1.5rem;
            overflow-y: auto;
        }
        
        .text-display-box {
            background: white; border: 1px solid #d1d5db; border-radius: 8px; padding: 1.5rem;
            font-family: 'Roboto Mono', monospace; font-size: 1.2rem; line-height: 1.8;
            height: 300px; overflow-y: auto; user-select: none; color: #6b7280;
            position: relative; white-space: pre-wrap;
        }
        
        .word { padding: 0 2px; border-radius: 3px; }
        .word.current { background-color: #e5e7eb; border-bottom: 2px solid #0f766e; }
        .word.correct { color: #059669; }
        .word.incorrect { color: #dc2626; background-color: #fecaca; }
        
        .input-area textarea {
            width: 100%; height: 150px; padding: 1rem; font-family: 'Roboto Mono', monospace; font-size: 1.2rem;
            border: 2px solid #0f766e; border-radius: 8px; resize: none; outline: none;
        }
        .input-area textarea:disabled { background-color: #f9fafb; border-color: #d1d5db; }

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
            <button class="btn btn-danger btn-sm fw-bold px-3" onclick="finishTest()">SUBMIT</button>
        </div>
    </div>

    <div class="main-container">
        <!-- Typing Area -->
        <div class="typing-area">
            <div class="text-display-box" id="text-display">
                <!-- Text will be injected via JS -->
            </div>
            
            <div class="input-area">
                <textarea id="typing-input" placeholder="Start typing here..." spellcheck="false" onpaste="return false;"></textarea>
            </div>
            
            <div class="text-center text-muted small mt-2">
                <i class="fas fa-info-circle me-1"></i> Timer starts when you begin typing.
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
               <a href="dashboard.php" class="btn btn-outline-secondary w-100 btn-sm">Exit Test</a>
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
            if (!isRunning) startTest();
            
            const value = inputField.value;
            const currentWordSpan = document.getElementById(`w-${currentWordIndex}`);
            const targetWord = words[currentWordIndex];
            
            // Handle Spacebar (Word Submission)
            if (e.data === ' ' || value.endsWith(' ')) {
                const typedWord = value.trim();
                
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
                    if (currentWordIndex < words.length) {
                        const nextSpan = document.getElementById(`w-${currentWordIndex}`);
                        nextSpan.classList.add('current');
                        
                        // Auto Scroll
                        if (document.getElementById('auto_scroll').checked) {
                            // Logic to check if next word is out of view
                            const containerRect = textDisplay.getBoundingClientRect();
                            const spanRect = nextSpan.getBoundingClientRect();
                            if (spanRect.bottom > containerRect.bottom - 50) {
                                nextSpan.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        }
                    } else {
                        finishTest(); // All words typed
                    }
                    
                    // Clear Input
                    inputField.value = '';
                } else {
                    // Just a space typed at start
                    inputField.value = ''; 
                }
            } else {
                // Real-time highlighting (Letter by letter - optional, keeping simple for now)
                // We could highlight the current word red if prefix doesn't match
                if (!targetWord.startsWith(value)) {
                    currentWordSpan.classList.add('incorrect');
                } else {
                    currentWordSpan.classList.remove('incorrect');
                }
            }
            
            updateStats();
        });
        
        // --- Backspace Prevention Logic ---
        inputField.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace') {
                const opt = document.querySelector('input[name="backspace_opt"]:checked').value;
                if (opt === 'off') {
                    e.preventDefault();
                } 
                // Logic for 'one' word backspace is implicitly handled as we clear input on space
                // 'Full' allows standard backspace behaviour
            }
        });

        function startTest() {
            isRunning = true;
            startTime = new Date();
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
            
            // WPM Calculation: (Gross Keystrokes / 5) / TimeInMinutes
            // Usually Gross WPM = (Total Entries / 5) / Time
            // Net WPM = Gross WPM - (Uncorrected Errors / Time)
            
            // Simplified here: Correct Words / Time ? Or standard (Chars / 5) / Time
            const wpm = Math.round(((totalKeystrokes / 5) / elapsedMin));
            
            // Accuracy: (Total - Errors) / Total * 100
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
            
            // Calculate Final Stats
            const timeTaken = totalTime - timeRemaining;
            const elapsedMin = Math.max(timeTaken / 60, 0.1); // Avoid div by zero
            
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
                    typed_content: "Encrytped" // Optional: Send actual content if needed
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
                    textDisplay.style.color = '#000';
                    // Need more css logic to hide colors, or just rely on class toggling
                   // For now, simpler implementation: Just toggle a class on body or container
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
