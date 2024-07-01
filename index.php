<?php
session_start();

// Inicializa a sessão do jogo
if (!isset($_SESSION['snake'])) {
    initializeGame();
}

function initializeGame() {
    $_SESSION['snake'] = [['x' => 5, 'y' => 5]];
    $_SESSION['food'] = ['x' => rand(0, 19), 'y' => rand(0, 19)];
    $_SESSION['direction'] = ['x' => 0, 'y' => 1];
    $_SESSION['score'] = 0;
}

// Movimenta a cobra
function moveSnake() {
    $head = $_SESSION['snake'][0];
    $direction = $_SESSION['direction'];
    $newHead = ['x' => $head['x'] + $direction['x'], 'y' => $head['y'] + $direction['y']];
    
    // Verifica colisões
    if ($newHead['x'] < 0 || $newHead['x'] >= 20 || $newHead['y'] < 0 || $newHead['y'] >= 20) {
        resetGame();
        return false;
    }
    
    // Verifica se a cobra comeu a comida
    if ($newHead['x'] == $_SESSION['food']['x'] && $newHead['y'] == $_SESSION['food']['y']) {
        $_SESSION['score'] += 10;
        $_SESSION['food'] = ['x' => rand(0, 19), 'y' => rand(0, 19)];
    } else {
        array_pop($_SESSION['snake']);
    }

    array_unshift($_SESSION['snake'], $newHead);

    // Verifica se a cobra colidiu consigo mesma
    for ($i = 1; $i < count($_SESSION['snake']); $i++) {
        if ($_SESSION['snake'][$i]['x'] == $newHead['x'] && $_SESSION['snake'][$i]['y'] == $newHead['y']) {
            resetGame();
            return false;
        }
    }

    return true;
}

// Reseta o jogo
function resetGame() {
    $_SESSION['snake'] = [['x' => 5, 'y' => 5]];
    $_SESSION['food'] = ['x' => rand(0, 19), 'y' => rand(0, 19)];
    $_SESSION['direction'] = ['x' => 0, 'y' => 1];
    $_SESSION['score'] = 0;
}

// Manipula a direção
if (isset($_GET['direction'])) {
    $direction = $_GET['direction'];
    switch ($direction) {
        case 'up':
            if ($_SESSION['direction']['y'] != 1) $_SESSION['direction'] = ['x' => 0, 'y' => -1];
            break;
        case 'down':
            if ($_SESSION['direction']['y'] != -1) $_SESSION['direction'] = ['x' => 0, 'y' => 1];
            break;
        case 'left':
            if ($_SESSION['direction']['x'] != 1) $_SESSION['direction'] = ['x' => -1, 'y' => 0];
            break;
        case 'right':
            if ($_SESSION['direction']['x'] != -1) $_SESSION['direction'] = ['x' => 1, 'y' => 0];
            break;
    }
    moveSnake();
    echo json_encode([
        'snake' => $_SESSION['snake'],
        'food' => $_SESSION['food'],
        'score' => $_SESSION['score']
    ]);
    exit();
}

if (!moveSnake()) {
    initializeGame();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jogo da Cobrinha</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Jogo da Cobrinha</h1>
    <canvas id="gameCanvas" width="400" height="400"></canvas>
    <p>Sua Pontuação: <span id="score"><?php echo $_SESSION['score']; ?></span></p>
    <script>
        const canvas = document.getElementById('gameCanvas');
        const ctx = canvas.getContext('2d');

        let snake = <?php echo json_encode($_SESSION['snake']); ?>;
        let food = <?php echo json_encode($_SESSION['food']); ?>;
        let score = <?php echo $_SESSION['score']; ?>;

        function drawGame() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            snake.forEach(part => {
                ctx.fillStyle = 'green';
                ctx.fillRect(part['x'] * 20, part['y'] * 20, 20, 20);
            });
            ctx.fillStyle = 'red';
            ctx.fillRect(food['x'] * 20, food['y'] * 20, 20, 20);
            document.getElementById('score').textContent = score;
        }

        function updateGame(direction) {
            fetch(`index.php?direction=${direction}`)
                .then(response => response.json())
                .then(data => {
                    snake = data.snake;
                    food = data.food;
                    score = data.score;
                    drawGame();
                });
        }

        document.addEventListener('keydown', event => {
            switch (event.keyCode) {
                case 37:
                    updateGame('left');
                    break;
                case 38:
                    updateGame('up');
                    break;
                case 39:
                    updateGame('right');
                    break;
                case 40:
                    updateGame('down');
                    break;
            }
        });

        drawGame();
    </script>
</body>
</html>
