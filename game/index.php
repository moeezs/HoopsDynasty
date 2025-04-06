<?php
// Validate team parameters
$userTeamId = isset($_GET['userTeamId']) ? intval($_GET['userTeamId']) : 0;
$opponentTeamId = isset($_GET['opponentTeamId']) ? intval($_GET['opponentTeamId']) : 0;

if ($userTeamId <= 0 || $opponentTeamId <= 0) {
    header('Location: ../menu/index.php');
    exit;
}

// Create images directory structure if it doesn't exist
$directories = ['images', 'images/players'];
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Basketball Game Simulation</title>
  <link rel="stylesheet" href="css/game.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
  <div class="main-container">
    <!-- Compact Header with Scoreboard -->
    <div class="game-header">
      <div class="scoreboard">
        <span class="team-name" id="homeTeamName">Home</span>
        <span class="score" id="homeScore">0</span>
        <span class="score-divider">|</span>
        <span class="score" id="awayScore">0</span>
        <span class="team-name" id="awayTeamName">Away</span>
      </div>
      <a href="../menu/index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Menu</a>
    </div>
    
    <!-- Court Area with Players at opposite ends -->
    <div class="court-area">
      <div class="player-container home-team">
        <img src="images/players/lebron.png" alt="Home Player" class="player-img" id="homePlayerImg">
        <img src="../images/ball.png" alt="Basketball" class="ball-img" id="homeBall">
      </div>
      
      <div class="game-state-indicator" id="stateLabel">OFFENSE</div>
      
      <div class="game-info">
        <div class="possession-indicator" id="ballPossession">Player Name</div>
      </div>
      
      <div class="player-container away-team">
        <img src="images/players/lebron.png" alt="Away Player" class="player-img" id="awayPlayerImg">
        <img src="../images/ball.png" alt="Basketball" class="ball-img" id="awayBall">
      </div>
    </div>
    
    <!-- Collapsible Game Log -->
    <div class="game-log collapsed" id="gameLog">
      <button class="log-toggle" id="logToggle">
        <i class="fas fa-chevron-right log-toggle-icon" id="logToggleIcon"></i>
      </button>
      <div class="log-title">GAME LOG</div>
      <div class="log-container" id="logEntries">
        <!-- Log entries will be populated by JS -->
      </div>
    </div>
    
    <!-- Full-width Action Panel with Icons -->
    <div class="action-panel">
      <!-- Offense Options -->
      <div class="action-buttons" id="offense-options">
        <button class="action-btn" data-action="three-pointer">
          <i class="fas fa-basketball action-icon"></i>
          <span class="action-name">3-POINTER</span>
          <span class="chance">(35%)</span>
        </button>
        <button class="action-btn" data-action="layup">
          <i class="fas fa-running action-icon"></i>
          <span class="action-name">LAYUP</span>
          <span class="chance">(70%)</span>
        </button>
        <button class="action-btn" data-action="pass">
          <i class="fas fa-share action-icon"></i>
          <span class="action-name">PASS</span>
          <span class="chance">(85%)</span>
        </button>
        <button class="action-btn" data-action="dunk">
          <i class="fas fa-hand-rock action-icon"></i>
          <span class="action-name">DUNK</span>
          <span class="chance">(60%)</span>
        </button>
      </div>
      
      <!-- Defense Options (Hidden by default) -->
      <div class="action-buttons" id="defense-options" style="display: none;">
        <button class="action-btn" data-action="block">
          <i class="fas fa-hand-paper action-icon"></i>
          <span class="action-name">BLOCK</span>
          <span class="chance">(65%)</span>
        </button>
        <button class="action-btn" data-action="steal">
          <i class="fas fa-hand-rock action-icon"></i>
          <span class="action-name">STEAL</span>
          <span class="chance">(60%)</span>
        </button>
        <button class="action-btn" data-action="tackle">
          <i class="fas fa-shield-alt action-icon"></i>
          <span class="action-name">TACKLE</span>
          <span class="chance">(55%)</span>
        </button>
        <button class="action-btn" data-action="pressure">
          <i class="fas fa-compress-alt action-icon"></i>
          <span class="action-name">PRESSURE</span>
          <span class="chance">(75%)</span>
        </button>
      </div>
      
      <div class="action-result" id="resultText">Choose an action</div>
      <button id="playAgainBtn" class="play-again-btn" style="display: none;">Play Again</button>
    </div>
  </div>
  
  <script src="js/game.js"></script>
</body>
</html>
