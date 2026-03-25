<?php
session_start();

  
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

  
$allowed_levels = ['easy', 'intermediate', 'hard'];
if (!isset($_GET['level']) || !in_array($_GET['level'], $allowed_levels)) {
      
    header("Location: dashboard.php");
    exit();
}

$current_level = $_GET['level'];
  
if ($current_level === 'intermediate' && $_SESSION['progress_easy'] < 3) {
    header("Location: dashboard.php");
    exit();
}
if ($current_level === 'hard' && $_SESSION['progress_intermediate'] < 3) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game - Hindi Crossword</title>
    <link rel="stylesheet" href="styleReal.css">
</head>
<body>
    <div id="crossword-game" class="game-container crossword-game" style="display:block;">
        <div class="game-header">
            <h1>Hindi Crossword</h1>
            <div class="game-controls">
                <div class="game-info">
                    <h3 id="currentLevel"><?= ucfirst($current_level) ?> Level</h3>
                    <p id="currentCrossword">Crossword <?= (int)$_SESSION['progress_' . $current_level] + 1 ?>/3</p>
                </div>
                <button class="btn-secondary" onclick="backToDashboard()">Back to Dashboard</button>
                <button class="btn-success" onclick="checkAnswers()">Check Answers</button>
                <button class="btn-secondary" onclick="nextCrossword()" id="nextBtn" style="display: none;">Next Crossword</button>
            </div>
        </div>

        <div id="error-container"></div>

        <div class="main-game-container">
            <div class="clues-section">
                <h3>Horizontal (आड़े)</h3>
                <div id="horizontal-clues"></div>
                <h3>Vertical (खड़े)</h3>
                <div id="vertical-clues"></div>
            </div>

            <div class="crossword-section">
                <div id="crossword-container"></div>
                <div class="input-area">
                    <h3>Complete the Word</h3>
                    <div id="selected-word-info"></div>
                    <div class="cluster-input" id="cluster-input"></div>
                    <div class="available-clusters">
                        <h4>Available Clusters</h4>
                        <div class="clusters-grid" id="clusters-grid"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="completion-modal" class="completion-modal" style="display: none;">
        <div class="modal-content">
            <h2 id="modal-title">Congratulations!</h2>
            <p id="modal-message">You have completed this crossword!</p>
            <button class="btn-primary" onclick="closeModal()">Continue</button>
        </div>
    </div>

    <script>
          
        let currentUser = {
            id: <?= json_encode($_SESSION['user_id']) ?>,
            name: <?= json_encode($_SESSION['user_name']) ?>,
            progress: { 
                easy: <?= (int)$_SESSION['progress_easy'] ?>, 
                intermediate: <?= (int)$_SESSION['progress_intermediate'] ?>, 
                hard: <?= (int)$_SESSION['progress_hard'] ?> 
            }
        };
        
        let currentLevel = '<?= htmlspecialchars($current_level) ?>';
        let currentCrosswordIndex = <?= (int)$_SESSION['progress_' . $current_level] ?>;
        let currentGrid = [];
        let placedWordsInfo = [];
        let selectedWordIndex = -1;
        let userAnswers = {};
        let availableClusters = [];
        let wordsData = [];
        let selectedClusterPositionInWord = -1;
        let currentlyUsedClusters = new Set();

          
        document.addEventListener('DOMContentLoaded', function() {
            generateCrossword();
        });

        async function loadWords() {
            if (wordsData.length > 0) return wordsData; 
            try {
                const res = await fetch('words.json');
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                wordsData = await res.json();
                return wordsData;
            } catch (e) {
                console.error("Failed to load words.json:", e);
                showError("Failed to load game data. Please try again later.");
                return [];
            }
        }

        function getMeaning(word) {
            const wordDataEntry = wordsData.find(item => item.word === word);
            return wordDataEntry ? wordDataEntry.meaning : 'अर्थ उपलब्ध नहीं';
        }

        function logout() {
            window.location.href = 'logout.php';
        }

        function backToDashboard() {
            window.location.href = 'dashboard.php';
        }

        function extractAKSHARS(word) {
            const chars = [...word];
            const clusters = [];
            const matras = 'ािीुूृेैोौ';
            const marks = 'ॉॉंँः़';
            let i = 0;
            
            while (i < chars.length) {
                let cluster = chars[i++];
                while (i < chars.length && (matras.includes(chars[i]) || marks.includes(chars[i]) || chars[i] === '्')) {
                    cluster += chars[i++];
                    if (chars[i - 1] === '्' && i < chars.length) {
                        cluster += chars[i++];
                    }
                }
                clusters.push(cluster);
            }
            return clusters;
        }

        async function generateCrossword() {
            await loadWords();

            const gridSize = getGridSize();
            const wordCount = getWordCount();
            
            currentGrid = Array.from({ length: gridSize }, () => Array(gridSize).fill(''));
            placedWordsInfo = [];
            userAnswers = {};
            selectedWordIndex = -1;
            
            const shuffledWords = [...wordsData].sort(() => Math.random() - 0.5);
            const maxAttempts = 200;
            let placedCount = 0;
            
            for (let attempt = 0; attempt < maxAttempts && placedCount < wordCount; attempt++) {
                const wordObj = shuffledWords[attempt % shuffledWords.length];

                if (placedWordsInfo.find(info => info.word === wordObj.word)) continue;

                if (placeWordRandomly(wordObj.word)) {
                    placedWordsInfo[placedWordsInfo.length - 1].meaning = wordObj.meaning;
                    placedCount++;
                }
            }

            if (placedCount < wordCount) {
                console.warn(`Could only place ${placedCount} out of ${wordCount} words. Retrying crossword generation.`);
            }
            
            fillRemainingCells();
            assignNumbers();
            initializeUserAnswers();
            displayCrossword();
            displayClues();
            generateAvailableClusters();
            showError(''); 
            document.getElementById('nextBtn').style.display = 'none';
        }

        function getGridSize() {
            switch (currentLevel) {
                case 'easy': return 8;
                case 'intermediate': return 10;
                case 'hard': return 12;
                default: return 8;
            }
        }

        function getWordCount() {
            switch (currentLevel) {
                case 'easy': return 10;
                case 'intermediate': return 12;
                case 'hard': return 18;
                default: return 6;
            }
        }

        function placeWordRandomly(word) {
            const clusters = extractAKSHARS(word);
            if (tryPlaceWithIntersection(clusters, word)) {
                return true;
            }
            if (tryPlaceAtRandom(clusters, word)) {
                return true;
            }
            return false;
        }

        function tryPlaceWithIntersection(clusters, word) {
            const size = currentGrid.length;
            for (let i = 0; i < size; i++) {
                for (let j = 0; j < size; j++) {
                    if (currentGrid[i][j] && currentGrid[i][j] !== '#') {
                        for (let k = 0; k < clusters.length; k++) {
                            if (currentGrid[i][j] === clusters[k]) {
                                if (canPlace(clusters, i, j - k, true)) {
                                    place(clusters, i, j - k, true);
                                    placedWordsInfo.push({ word, row: i, col: j - k, horizontal: true });
                                    return true;
                                }
                                if (canPlace(clusters, i - k, j, false)) {
                                    place(clusters, i - k, j, false);
                                    placedWordsInfo.push({ word, row: i - k, col: j, horizontal: false });
                                    return true;
                                }
                            }
                        }
                    }
                }
            }
            return false;
        }

        function tryPlaceAtRandom(clusters, word) {
            const size = currentGrid.length;
            const horizontal = Math.random() < 0.5;
            const maxAttempts = 50; 
            for (let i = 0; i < maxAttempts; i++) {
                const row = Math.floor(Math.random() * size);
                const col = Math.floor(Math.random() * size);
                
                if (canPlace(clusters, row, col, horizontal)) {
                    place(clusters, row, col, horizontal);
                    placedWordsInfo.push({ word, row, col, horizontal });
                    return true;
                }
            }
            return false;
        }

        function canPlace(clusters, row, col, horizontal) {
            const size = currentGrid.length;
            const len = clusters.length;
            
            if (horizontal) {
                if (col < 0 || col + len > size) return false; 

                if ((col > 0 && currentGrid[row][col - 1] && currentGrid[row][col - 1] !== '#') || 
                    (col + len < size && currentGrid[row][col + len] && currentGrid[row][col + len] !== '#')) { 
                    return false;
                }
                    
                for (let i = 0; i < len; i++) {
                    const currentCell = currentGrid[row][col + i];
                    const targetCluster = clusters[i];

                    if (currentCell && currentCell !== '#') { 
                        if (currentCell !== targetCluster) return false; 
                    }

                    if (currentCell === '' || currentCell === '#') {
                        if ((row > 0 && currentGrid[row - 1][col + i] && currentGrid[row - 1][col + i] !== '#') ||
                            (row < size - 1 && currentGrid[row + 1][col + i] && currentGrid[row + 1][col + i] !== '#')) {
                            return false; 
                        }
                    }
                }
            } else { 
                if (row < 0 || row + len > size) return false;

                if ((row > 0 && currentGrid[row - 1][col] && currentGrid[row - 1][col] !== '#') ||
                    (row + len < size && currentGrid[row + len][col] && currentGrid[row + len][col] !== '#')) {
                    return false;
                }
                    
                for (let i = 0; i < len; i++) {
                    const currentCell = currentGrid[row + i][col];
                    const targetCluster = clusters[i];

                    if (currentCell && currentCell !== '#') { 
                        if (currentCell !== targetCluster) return false; 
                    }

                    if (currentCell === '' || currentCell === '#') { 
                        if ((col > 0 && currentGrid[row + i][col - 1] && currentGrid[row + i][col - 1] !== '#') ||
                            (col < size - 1 && currentGrid[row + i][col + 1] && currentGrid[row + i][col + 1] !== '#')) {
                            return false;
                        }
                    }
                }
            }
            return true;
        }

        function place(clusters, row, col, horizontal) {
            clusters.forEach((c, i) => {
                if (horizontal) currentGrid[row][col + i] = c;
                else currentGrid[row + i][col] = c;
            });
            return true;
        }

        function fillRemainingCells() {
            currentGrid.forEach(row => row.forEach((cell, i) => {
                if (!cell) row[i] = '#';
            }));
        }

        function assignNumbers() {
            placedWordsInfo.sort((a, b) => {
                if (a.row !== b.row) return a.row - b.row;
                return a.col - b.col;
            });
            
            placedWordsInfo.forEach((wordInfo, index) => {
                wordInfo.number = index + 1;
            });
        }

        function initializeUserAnswers() {
            userAnswers = {};

            placedWordsInfo.forEach((wordInfo, index) => {
                const clusters = extractAKSHARS(wordInfo.word);
                userAnswers[index] = {
                    clusters: [clusters[0]].concat(new Array(clusters.length - 1).fill('')),
                    firstCluster: clusters[0],
                };
            });

            renderAvailableClusters();
            updateCrosswordDisplay();  
        }

        function renderAvailableClusters() {
            const grid = document.getElementById('clusters-grid');
            grid.innerHTML = '';

            availableClusters.forEach(cluster => {
                const option = document.createElement('div');
                option.className = 'cluster-option';
                option.textContent = cluster;
                option.dataset.clusterValue = normalizeInput(cluster);

                option.onclick = () => {
                    document.querySelectorAll('.cluster-option.selected').forEach(opt => {
                        opt.classList.remove('selected');
                    });
                    option.classList.add('selected');
                    selectCluster(cluster);  
                };

                grid.appendChild(option);
            });
        }

        async function generateAvailableClusters() {
            await loadWords(); 

            availableClusters = [];
            placedWordsInfo.forEach(wordInfo => {
                const clusters = extractAKSHARS(wordInfo.word);
                availableClusters.push(...clusters.slice(1)); 
            });
            
            const randomHindiClusters = ['क', 'ई', 'र', 'म', 'त', 'ल', 'न', 'च', 'प', 'स', 'ह', 'ा', 'ी', 'ो'];
            let numRandomClusters = 0;
            if (currentLevel === 'easy') numRandomClusters = 5;
            else if (currentLevel === 'intermediate') numRandomClusters = 10;
            else if (currentLevel === 'hard') numRandomClusters = 15;

            for (let i = 0; i < numRandomClusters; i++) {
                availableClusters.push(randomHindiClusters[Math.floor(Math.random() * randomHindiClusters.length)]);
            }

            availableClusters = availableClusters.sort(() => Math.random() - 0.5);
            updateClustersGrid(); 
        }

        function displayCrossword() {
            const container = document.getElementById('crossword-container');
            container.innerHTML = '';
            
            const gridDiv = document.createElement('div');
            gridDiv.className = 'crossword-grid';

            currentGrid.forEach((row, rowIndex) => {
                const rowDiv = document.createElement('div');
                rowDiv.className = 'crossword-row';
                
                row.forEach((cell, colIndex) => {
                    const cellDiv = document.createElement('div');
                    cellDiv.className = 'crossword-cell ' + (cell === '#' ? 'blocked' : 'blank');
                    
                    if (cell !== '#') {
                        const wordInfo = placedWordsInfo.find(info => 
                            info.row === rowIndex && info.col === colIndex
                        );
                        
                        if (wordInfo) {
                            const wordIndex = placedWordsInfo.indexOf(wordInfo);
                            cellDiv.textContent = userAnswers[wordIndex].firstCluster; 
                            cellDiv.className += ' filled';
                            
                            const numberDiv = document.createElement('div');
                            numberDiv.className = 'cell-number';
                            numberDiv.textContent = wordInfo.number;
                            cellDiv.appendChild(numberDiv);
                        } else {
                            const belongsToWord = placedWordsInfo.some(info => {
                                const clusters = extractAKSHARS(info.word);
                                if (info.horizontal) {
                                    return rowIndex === info.row && 
                                           colIndex > info.col && 
                                           colIndex < info.col + clusters.length;
                                } else {
                                    return colIndex === info.col && 
                                           rowIndex > info.row && 
                                           rowIndex < info.row + clusters.length;
                                }
                            });
                            
                            if (belongsToWord) {
                                cellDiv.className += ' user-input';
                                cellDiv.onclick = () => selectCell(rowIndex, colIndex);
                            }
                        }
                    }
                    rowDiv.appendChild(cellDiv);
                });
                gridDiv.appendChild(rowDiv);
            });
            container.appendChild(gridDiv);
            updateCrosswordDisplay(); 
        }

        function selectCell(row, col) {
            document.querySelectorAll('.crossword-cell.correct, .crossword-cell.incorrect').forEach(cell => {
                cell.classList.remove('correct', 'incorrect');
            });
            
            document.querySelectorAll('.crossword-cell.selected-input').forEach(cell => {
                cell.classList.remove('selected-input');
            });

            document.querySelectorAll('.cluster-option.selected').forEach(option => {
                option.classList.remove('selected');
            });

            let foundWordIndex = -1;
            let foundClusterPosition = -1;

            placedWordsInfo.forEach((wordInfo, wordIndex) => {
                const clusters = extractAKSHARS(wordInfo.word);
                if (wordInfo.horizontal) {
                    if (row === wordInfo.row && col >= wordInfo.col && col < wordInfo.col + clusters.length) {
                        foundWordIndex = wordIndex;
                        foundClusterPosition = col - wordInfo.col;
                    }
                } else {  
                    if (col === wordInfo.col && row >= wordInfo.row && row < wordInfo.row + clusters.length) {
                        foundWordIndex = wordIndex;
                        foundClusterPosition = row - wordInfo.row;
                    }
                }
            });

            const clickedCell = document.querySelector(
                `.crossword-row:nth-child(${row + 1}) .crossword-cell:nth-child(${col + 1})`
            );

            if (foundWordIndex !== -1 && foundClusterPosition > 0 && clickedCell && clickedCell.classList.contains('user-input')) {
                selectedWordIndex = foundWordIndex;
                selectedClusterPositionInWord = foundClusterPosition;
                clickedCell.classList.add('selected-input');  
                updateInputArea();  
                showError('');  
            } else {
                selectedWordIndex = -1;
                selectedClusterPositionInWord = -1;
                updateInputArea();  
            }
        }

        function updateInputArea() {
            if (selectedWordIndex === -1 || selectedClusterPositionInWord === -1) {
                document.getElementById('selected-word-info').innerHTML = '';
                document.getElementById('cluster-input').innerHTML = '';
                return;
            }

            const wordInfo = placedWordsInfo[selectedWordIndex];
            const answer = userAnswers[selectedWordIndex];
            const correctClusters = extractAKSHARS(wordInfo.word);

            document.getElementById('selected-word-info').innerHTML =
                `<p><strong>Word ${wordInfo.number}:</strong> ${wordInfo.meaning}</p>`;

            const clusterInput = document.getElementById('cluster-input');
            clusterInput.innerHTML = '';

            correctClusters.forEach((cluster, index) => {
                const box = document.createElement('div');
                box.className = 'cluster-box';
                if (index === 0) {
                    box.classList.add('used');  
                    box.textContent = answer.clusters[0];
                    box.style.cursor = 'default';
                } else {
                    box.textContent = answer.clusters[index] || '';
                    box.onclick = () => {
                        document.querySelectorAll('#cluster-input .cluster-box.selected').forEach(b => {
                            b.classList.remove('selected');
                        });
                        selectedClusterPositionInWord = index;
                        updateInputArea();  
                        showError('');
                    };
                    if (index === selectedClusterPositionInWord) {
                        box.classList.add('selected');  
                    }
                }
                clusterInput.appendChild(box);
            });

            renderAvailableClusters();  
        }

        function updateClustersGrid() {
            const grid = document.getElementById('clusters-grid');
            grid.innerHTML = '';
            
            availableClusters.forEach(cluster => {
                const option = document.createElement('div');
                option.className = 'cluster-option';
                option.textContent = cluster;
                option.onclick = () => selectCluster(cluster);
                grid.appendChild(option);
            });
        }

        function selectCluster(cluster) {
            if (selectedWordIndex === -1 || selectedClusterPositionInWord <= 0) {
                showError('Please select an empty box in the "Complete the Word" section first.');
                document.querySelectorAll('.cluster-option.selected').forEach(option => {
                    option.classList.remove('selected');
                });
                return;
            }

            const answer = userAnswers[selectedWordIndex];
            answer.clusters[selectedClusterPositionInWord] = cluster;

            document.querySelectorAll('.cluster-option.selected').forEach(option => {
                option.classList.remove('selected');
            });

            const clusterInputBoxes = document.querySelectorAll('#cluster-input .cluster-box');
            if (clusterInputBoxes[selectedClusterPositionInWord]) {
                clusterInputBoxes[selectedClusterPositionInWord].classList.remove('selected');
            }

            updateInputArea();  
            updateCrosswordDisplay();  
            renderAvailableClusters();  
            showError('');  
        }

        function updateCrosswordDisplay() {
            placedWordsInfo.forEach((wordInfo, wordIndex) => {
                const userClusters = userAnswers[wordIndex].clusters;

                for (let i = 0; i < userClusters.length; i++) {
                    let cellRow, cellCol;
                    if (wordInfo.horizontal) {
                        cellRow = wordInfo.row;
                        cellCol = wordInfo.col + i;
                    } else {
                        cellRow = wordInfo.row + i;
                        cellCol = wordInfo.col;
                    }

                    const cell = document.querySelector(
                        `.crossword-row:nth-child(${cellRow + 1}) .crossword-cell:nth-child(${cellCol + 1})`
                    );

                    if (cell && !cell.classList.contains('blocked')) {
                        const clusterInCell = userClusters[i] || '';
                        cell.textContent = clusterInCell;
                        cell.classList.remove('correct', 'incorrect', 'selected-input', 'filled', 'blank');

                        if (clusterInCell) {
                            cell.classList.add('filled');
                        } else {
                            cell.classList.add('blank');
                        }

                        if (wordIndex === selectedWordIndex) {
                            let currentCellIsSelectedInput = false;
                            if (wordInfo.horizontal) {
                                if (cellCol === wordInfo.col + selectedClusterPositionInWord) {
                                    currentCellIsSelectedInput = true;
                                }
                            } else {  
                                if (cellRow === wordInfo.row + selectedClusterPositionInWord) {
                                    currentCellIsSelectedInput = true;
                                }
                            }

                            if (currentCellIsSelectedInput && selectedClusterPositionInWord > 0) {
                                cell.classList.add('selected-input');
                            }
                        }
                    }
                }
            });
        }

        function displayClues() {
            const horizontalClues = document.getElementById('horizontal-clues');
            const verticalClues = document.getElementById('vertical-clues');
            
            const horizontal = placedWordsInfo.filter(info => info.horizontal);
            const vertical = placedWordsInfo.filter(info => !info.horizontal);
            
            horizontalClues.innerHTML = horizontal.map(info => 
                `<div class="clue-item">
                    <span class="clue-number">${info.number}.</span>
                    <span class="clue-meaning">${info.meaning}</span>
                </div>`
            ).join('');
            
            verticalClues.innerHTML = vertical.map(info => 
                `<div class="clue-item">
                    <span class="clue-number">${info.number}.</span>
                    <span class="clue-meaning">${info.meaning}</span>
                </div>`
            ).join('');
        }

        function normalizeInput(word) {
            return word.trim().replace(/\s+/g, '').normalize("NFC");
        }

        function checkAnswers() {
            let correctWords = 0;
            const totalWords = placedWordsInfo.length;

            if (totalWords === 0) {
                showError("No words generated for this crossword yet.");
                return;
            }

            document.querySelectorAll('.crossword-cell.selected-input').forEach(cell => {
                cell.classList.remove('selected-input');
            });
            if (selectedWordIndex !== -1) {
                selectedWordIndex = -1;
                updateInputArea();  
            }

            placedWordsInfo.forEach((wordInfo) => {
                const correctClusters = extractAKSHARS(wordInfo.word);
                let userEnteredClusters = [];

                for (let i = 0; i < correctClusters.length; i++) {
                    let row, col;
                    if (wordInfo.horizontal) {
                        row = wordInfo.row;
                        col = wordInfo.col + i;
                    } else {
                        row = wordInfo.row + i;
                        col = wordInfo.col;
                    }

                    const cell = document.querySelector(
                        `.crossword-row:nth-child(${row + 1}) .crossword-cell:nth-child(${col + 1})`
                    );

                    let cellContent = '';
                    if (cell) {
                        const clonedCell = cell.cloneNode(true);
                        const numberElement = clonedCell.querySelector('.cell-number');
                        if (numberElement) {
                            numberElement.remove();
                        }
                        cellContent = clonedCell.textContent.trim();
                    }
                    userEnteredClusters.push(cellContent);
                }

                const isWordCorrect = normalizeInput(userEnteredClusters.join('')) === normalizeInput(wordInfo.word);

                if (isWordCorrect) {
                    correctWords++;
                    markWordInGrid(wordInfo, 'correct');
                } else {
                    markWordInGrid(wordInfo, 'incorrect');
                }
            });

            if (correctWords === totalWords) {
                showCompletionModal();
            } else {
                showError(`${correctWords}/${totalWords} words are correct. Try correcting the ones marked in red!`);
            }
        }

        function markWordInGrid(wordInfo, status) {
            const clusters = extractAKSHARS(wordInfo.word);

            for (let i = 0; i < clusters.length; i++) {
                let cellRow, cellCol;
                if (wordInfo.horizontal) {
                    cellRow = wordInfo.row;
                    cellCol = wordInfo.col + i;
                } else {
                    cellRow = wordInfo.row + i;
                    cellCol = wordInfo.col;
                }

                const cell = document.querySelector(
                    `.crossword-row:nth-child(${cellRow + 1}) .crossword-cell:nth-child(${cellCol + 1})`
                );

                if (cell && !cell.classList.contains('blocked')) {
                    cell.classList.remove('correct', 'incorrect', 'selected-input');  

                    if (status === 'correct') {
                        cell.classList.add('correct');
                        cell.textContent = clusters[i];  
                        cell.classList.add('filled');
                        cell.classList.remove('blank');
                    } else if (status === 'incorrect' && i > 0) {  
                        cell.classList.add('incorrect');
                    }
                }
            }
        }

        async function showCompletionModal() {
            const modal = document.getElementById('completion-modal');
            const title = document.getElementById('modal-title');
            const message = document.getElementById('modal-message');
            
            if (currentUser && currentUser.progress) {
                currentUser.progress[currentLevel]++;
                console.log("User progress updated:", currentUser.progress);

                try {
                    await fetch('save_progress.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                    level: currentLevel,
                    progress: currentUser.progress[currentLevel]
                })
            });
        } catch (error) {
            console.error("Failed to save progress:", error);
              
            showError("Could not save your progress. Please check your connection.");
        }
          
    }
    
    if (currentUser && currentUser.progress[currentLevel] >= 3) {
        title.textContent = 'Level Completed!';
        message.textContent = `Congratulations! You have completed the ${currentLevel} level!`;
    } else {
        title.textContent = 'Crossword Completed!';
        message.textContent = `Great job! You have completed crossword ${currentUser ? currentUser.progress[currentLevel] : 'X'}/3 in ${currentLevel} level.`;
    }
    
    modal.style.display = 'flex';
    document.getElementById('nextBtn').style.display = 'inline-block';
}

        function closeModal() {
            document.getElementById('completion-modal').style.display = 'none';
        }

        function nextCrossword() {
            closeModal(); 
            if (currentUser && currentUser.progress[currentLevel] >= 3) {
                backToDashboard();
            } else {
                currentCrosswordIndex = currentUser ? currentUser.progress[currentLevel] : 0;
                document.getElementById('currentCrossword').textContent = `Crossword ${currentCrosswordIndex + 1}/3`;
                document.getElementById('nextBtn').style.display = 'none';
                generateCrossword();
            }
        }

        function showError(message) {
            const errorContainer = document.getElementById('error-container');
            if (message) {
                errorContainer.innerHTML = `<div class="error-message">${message}</div>`;
                setTimeout(() => {
                    errorContainer.innerHTML = '';
                }, 5000);  
            } else {
                errorContainer.innerHTML = '';  
            }
        }

        </script>
        </body>
        </html>
