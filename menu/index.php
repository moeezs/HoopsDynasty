<?php

/**
 * Main menu for NBA Basketball Simulator.
 */
include "../connect.php";

session_start();

if (!isset($_SESSION["userid"])) {
  $userid = filter_input(INPUT_POST, "userid", FILTER_SANITIZE_SPECIAL_CHARS);
  $password = filter_input(INPUT_POST, "password");

  if ($userid !== null and $password !== null) {
    $cmd = "SELECT password, firstname, lastname, accesslevel FROM hoopsdynastyusers WHERE username=?";
    $stmt = $dbh->prepare($cmd);
    $stmt->execute([$userid]);

    if ($row = $stmt->fetch()) {
      if (password_verify($password, $row["password"])) {
        $_SESSION["userid"] = $userid;
        $_SESSION["firstname"] = $row["firstname"];
        $_SESSION["lastname"] = $row["lastname"];
        $_SESSION["accesslevel"] = $row["accesslevel"];
      } else {
        session_unset();
        session_destroy();
        header("Location: ../accounts/signin.php?error=invalid");
        exit;
      }
    } else {
      // bad login attempt. kick them out.
      session_unset();
      session_destroy();
      header("Location: ../accounts/signin.php?error=invalid");
      exit;
    }
  }
}

// If no user is logged in after all checks, redirect to login page
if (!isset($_SESSION["userid"])) {
  header("Location: ../accounts/signin.php");
  exit;
}

// Get user's teams
$teams = [];
$teamPlayers = [];

try {
  // Get teams from hoopsdynastyteams table
  $cmd = "SELECT id, team_name, creator, access, center, power_forward, small_forward, point_guard, shooting_guard FROM hoopsdynastyteams WHERE creator=? || access = 1 ORDER BY access";
  $stmt = $dbh->prepare($cmd);
  $stmt->execute([$_SESSION["userid"]]);

  while ($row = $stmt->fetch()) {
    $teams[$row["id"]] = [
      "name" => $row["team_name"],
      "rating" => calculateTeamRating($row),
      "players" => [
        "C" => $row["center"],
        "PF" => $row["power_forward"],
        "SF" => $row["small_forward"],
        "PG" => $row["point_guard"],
        "SG" => $row["shooting_guard"]
      ]
    ];

    // Store player names to fetch their data
    foreach (["center", "power_forward", "small_forward", "point_guard", "shooting_guard"] as $position) {
      if (!empty($row[$position])) {
        $teamPlayers[] = $row[$position];
      }
    }
  }
} catch (Exception $e) {
}

// Get all opponents (all teams in the system)
$allTeams = [];
try {
  $cmd = "SELECT id, team_name, creator, access, center, power_forward, small_forward, point_guard, shooting_guard
          FROM hoopsdynastyteams WHERE creator=? || access = 1 ORDER BY access";
  $stmt = $dbh->prepare($cmd);
  $stmt->execute([$_SESSION["userid"]]);

  while ($row = $stmt->fetch()) {
    if ($row["creator"] === $_SESSION["userid"] || (int)$row["access"] === 1) {
      $teamId = $row["id"];
      $players = [];

      // Go through each position and fetch player photo
      foreach (
        [
          "C" => "center",
          "PF" => "power_forward",
          "SF" => "small_forward",
          "PG" => "point_guard",
          "SG" => "shooting_guard"
        ] as $positionCode => $dbField
      ) {
        $playerName = $row[$dbField];

        if (!empty($playerName)) {
          $photoQuery = "SELECT TO_BASE64(photo) AS photo_base64 FROM hoopsdynastyplayers WHERE player_name = ? LIMIT 1";
          $photoStmt = $dbh->prepare($photoQuery);
          $photoStmt->execute([$playerName]);
          $photoResult = $photoStmt->fetch();

          $players[$positionCode] = [
            "name" => $playerName,
            "photo_base64" => $photoResult["photo_base64"] ?? null
          ];
        } else {
          $players[$positionCode] = [
            "name" => "Unknown",
            "photo_base64" => null
          ];
        }
      }

      // Add team to list
      $allTeams[$teamId] = [
        "name" => $row["team_name"],
        "owner" => $row["creator"],
        "players" => $players
      ];
    }
  }
} catch (Exception $e) {
  // fallback
  $allTeams = [];
}

// Calculate team rating based on player positions
function calculateTeamRating($teamData)
{
  // A more sophisticated rating algorithm could be implemented here
  // For now, just return a score between 70-95
  return rand(70, 95);
}

// Check for error message
$errorMsg = "";
if (isset($_GET['error']) && $_GET['error'] == 'noteams') {
  $errorMsg = "Please select a team before starting a game.";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hoops Dynasty Basketball Simulator - Main Menu</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <script src="./js/menu.js" defer></script>
</head>

<body>
  <?php if (isset($_SESSION["userid"])) { ?>
    <div class="main-container">
      <!-- Header with Title and User Info -->
      <div class="main-header">
        <h1 class="main-title">Hoops Dynasty</h1>
        <div class="user-info">
          <i class="fas fa-user"></i>
          <span>Welcome, <?= htmlspecialchars($_SESSION["firstname"]) ?> <?= htmlspecialchars($_SESSION["lastname"]) ?> (<?= htmlspecialchars($_SESSION["userid"]) ?>)</span>
          <a href="../accounts/logout.php" class="logout-btn" title="Logout">
            <i class="fas fa-sign-out-alt"></i>
          </a>
        </div>
      </div>

      <!-- Court Display with Team Visualization -->
      <div class="court-display">
        <div class="court-bg"></div>

        <div class="player-spots">
          <div class="player-spot" data-position="PG">
            <img id="PG" src="../images/ball.png" alt="Point Guard">
            <div class="player-name">PG</div>
          </div>
          <div class="player-spot" data-position="SG">
            <img id="SG" src="../images/ball.png" alt="Shooting Guard">
            <div class="player-name">SG</div>
          </div>
          <div class="player-spot" data-position="SF">
            <img id="SF" src="../images/ball.png" alt="Small Forward">
            <div class="player-name">SF</div>
          </div>
          <div class="player-spot" data-position="PF">
            <img id="PF" src="../images/ball.png" alt="Power Forward">
            <div class="player-name">PF</div>
          </div>
          <div class="player-spot" data-position="C">
            <img id="C" src="../images/ball.png" alt="Center">
            <div class="player-name">C</div>
          </div>
        </div>
      </div>

      <!-- Sidebar with Actions -->
      <div class="main-sidebar">
        <h2 class="sidebar-title">TEAM MANAGEMENT</h2>

        <?php if (!empty($errorMsg)): ?>
          <div class="error-message"><?= htmlspecialchars($errorMsg) ?></div>
        <?php endif; ?>

        <select id="teamSelect" class="team-select">
          <option value="">Select Your Team</option>
          <?php foreach ($teams as $id => $team): ?>
            <option value="<?= htmlspecialchars($id) ?>" data-players='<?= htmlspecialchars(json_encode($team["players"])) ?>'>
              <?= htmlspecialchars($team["name"]) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <a href="../teambuilder" class="menu-btn primary">
          <i class="fas fa-user-plus"></i> Create New Team
        </a>

        <a href="#" id="playGameLink" class="menu-btn accent disabled">
          <i class="fas fa-basketball-ball"></i> Play Game
        </a>

        <a href="../accounts/logout.php" class="menu-btn danger">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>

        <div class="team-info">
          <div class="team-card">
            <div class="team-card-header">
              <span class="team-card-name">Select a Team</span>
            </div>
            <div class="team-card-players">
              <div class="team-card-player">
                <img id="PGd" src="../images/ball.png" alt="Player">
              </div>
              <div class="team-card-player">
                <img id="SGd" src="../images/ball.png" alt="Player">
              </div>
              <div class="team-card-player">
                <img id="SFd" src="../images/ball.png" alt="Player">
              </div>
              <div class="team-card-player">
                <img id="PFd" src="../images/ball.png" alt="Player">
              </div>
              <div class="team-card-player">
                <img id="Cd" src="../images/ball.png" alt="Player">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Opponent Selection Modal -->
    <div id="opponentModal" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h3 class="modal-title">Select Opponent</h3>
          <button class="modal-close">&times;</button>
        </div>

        <div class="team-list">
          <?php if (empty($allTeams)): ?>
            <p class="no-teams-message">No opponent teams available. Try creating more teams first.</p>
          <?php else: ?>
            <?php foreach ($allTeams as $id => $team): ?>
              <div class="team-card" data-team-id="<?= htmlspecialchars($id) ?>">
                <div class="team-card-header">
                  <span class="team-card-name"><?= htmlspecialchars($team["name"]) ?></span>
                  <span class="team-card-owner">Owner: <?= htmlspecialchars($team["owner"]) ?></span>
                </div>
                <div class="team-card-players">
                  <?php foreach ($team["players"] as $pos => $player): ?>
                    <div class="team-card-player">
                      <img
                        src="<?= $player["photo_base64"]
                                ? 'data:image/jpeg;base64,' . $player["photo_base64"]
                                : '../images/ball.png' ?>"
                        alt="<?= htmlspecialchars($player["name"]) ?>"
                        title="<?= htmlspecialchars($pos . ": " . $player["name"]) ?>">
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <form action="../game/index.php" method="GET" id="gameForm">
          <input type="hidden" name="userTeamId" id="userTeamId" value="">
          <input type="hidden" name="opponentTeamId" id="opponentTeamId" value="">
          <button type="submit" class="menu-btn accent" id="startGameBtn" disabled>Start Game</button>
        </form>
      </div>
    </div>
  <?php } else { ?>
    <div class="login-error">
      <h1>Login Error! Access denied.</h1>
      <a href="../accounts/signin.php" class="menu-btn primary">Try again</a>
    </div>
  <?php } ?>
</body>

</html>