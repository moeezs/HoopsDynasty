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
  $cmd = "SELECT id, team_name, creator, center, power_forward, small_forward, point_guard, shooting_guard FROM hoopsdynastyteams WHERE creator=?";
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

  // If no teams found, add demo teams for testing
  if (empty($teams)) {
    // Demo teams for new users
    $demoTeams = [
      ["name" => "Lakers", "rating" => 90, "id" => "demo1"],
      ["name" => "Warriors", "rating" => 92, "id" => "demo2"],
      ["name" => "Celtics", "rating" => 88, "id" => "demo3"]
    ];

    foreach ($demoTeams as $demo) {
      $teams[$demo["id"]] = [
        "name" => $demo["name"],
        "rating" => $demo["rating"],
        "demo" => true,
        "players" => [
          "C" => "Default Center",
          "PF" => "Default Forward",
          "SF" => "Default Small Forward",
          "PG" => "Default Point Guard",
          "SG" => "Default Shooting Guard"
        ]
      ];
    }
  }
} catch (Exception $e) {
  // Fallback to demo teams if database query fails
  $teams = [
    "demo1" => [
      "name" => "Lakers",
      "rating" => 90,
      "demo" => true,
      "players" => [
        "C" => "Default Center",
        "PF" => "Default Forward",
        "SF" => "Default Small Forward",
        "PG" => "Default Point Guard",
        "SG" => "Default Shooting Guard"
      ]
    ],
    "demo2" => [
      "name" => "Warriors",
      "rating" => 92,
      "demo" => true,
      "players" => [
        "C" => "Default Center",
        "PF" => "Default Forward",
        "SF" => "Default Small Forward",
        "PG" => "Default Point Guard",
        "SG" => "Default Shooting Guard"
      ]
    ],
    "demo3" => [
      "name" => "Celtics",
      "rating" => 88,
      "demo" => true,
      "players" => [
        "C" => "Default Center",
        "PF" => "Default Forward",
        "SF" => "Default Small Forward",
        "PG" => "Default Point Guard",
        "SG" => "Default Shooting Guard"
      ]
    ]
  ];
}

// Get all opponents (all teams in the system)
$allTeams = [];
try {
  $cmd = "SELECT id, team_name, creator, center, power_forward, small_forward, point_guard, shooting_guard
          FROM hoopsdynastyteams";
  $stmt = $dbh->prepare($cmd);
  $stmt->execute();

  while ($row = $stmt->fetch()) {
    if ($row["creator"] === $_SESSION["userid"] || $row["creator"] === "dmgg") {
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
  <title>NBA Basketball Simulator - Main Menu</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

    <script>
      document.addEventListener('DOMContentLoaded', () => {
        // DOM Elements
        const teamSelect = document.getElementById('teamSelect');
        const playGameLink = document.getElementById('playGameLink');
        const opponentModal = document.getElementById('opponentModal');
        const modalClose = document.querySelector('.modal-close');
        const playerSpots = document.querySelectorAll('.player-spot');
        const startGameBtn = document.getElementById('startGameBtn');
        const userTeamIdInput = document.getElementById('userTeamId');
        const opponentTeamIdInput = document.getElementById('opponentTeamId');

        // Function to update player spots with selected team
        async function updatePlayerSpots(teamId) {
          if (!teamId) return;

          const selectedOption = teamSelect.options[teamSelect.selectedIndex];
          if (!selectedOption) return;

          const team = {
            name: selectedOption.text,
            players: JSON.parse(selectedOption.getAttribute('data-players'))
          };

          // Update team info card
          document.querySelector('.team-card-name').textContent = team.name;

          // Update player spots
          let positionCount = 0;
          for (const [position, playerName] of Object.entries(team.players)) {
            const spot = document.querySelector(`.player-spot[data-position="${position}"]`);
            if (spot) {
              spot.querySelector('.player-name').textContent = playerName;

              switch (positionCount) {
                case 0: {
                  document.getElementById("C").src = await getPhoto(playerName);
                  document.getElementById("Cd").src = await getPhoto(playerName);
                  break;
                }
                case 1: {
                  document.getElementById("PF").src = await getPhoto(playerName);
                  document.getElementById("PFd").src = await getPhoto(playerName);
                  break;
                }
                case 2: {
                  document.getElementById("SF").src = await getPhoto(playerName);
                  document.getElementById("SFd").src = await getPhoto(playerName);
                  break;
                }
                case 3: {
                  document.getElementById("PG").src = await getPhoto(playerName);
                  document.getElementById("PGd").src = await getPhoto(playerName);
                  break;
                }
                case 4: {
                  document.getElementById("SG").src = await getPhoto(playerName);
                  document.getElementById("SGd").src = await getPhoto(playerName);
                  break;
                }
              }

              positionCount++;
            }
          }

          // Enable play button
          playGameLink.classList.remove('disabled');
          userTeamIdInput.value = teamId;

          // Save selected team to localStorage
          localStorage.setItem('selectedTeam', teamId);
        }

        async function getPhoto(name) {
          let params = "name=" + encodeURIComponent(name);

          let config = {
            method: 'POST',
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            body: params
          };

          try {
            const response = await fetch("../menu/queries/getspecifiedplayer.php", config);
            const data = await response.json();

            if (data && data.photo_base64) {
              return "data:image/jpeg;base64," + data.photo_base64;
            } else {
              return "../images/default_player.jpg"; // fallback image
            }
          } catch (err) {
            console.error("Photo fetch error for", name, err);
            return "../images/default_player.jpg";
          }
        }


        // Event Listeners
        teamSelect.addEventListener('change', () => {
          updatePlayerSpots(teamSelect.value);
        });

        playGameLink.addEventListener('click', (e) => {
          if (playGameLink.classList.contains('disabled')) {
            e.preventDefault();
            alert('Please select a team first');
          } else {
            e.preventDefault();
            opponentModal.classList.add('active');
          }
        });

        modalClose.addEventListener('click', () => {
          opponentModal.classList.remove('active');
        });

        // Close modal if clicking outside of content
        opponentModal.addEventListener('click', (e) => {
          if (e.target === opponentModal) {
            opponentModal.classList.remove('active');
          }
        });

        // Team cards in modal are clickable
        document.querySelectorAll('.team-list .team-card').forEach(card => {
          card.addEventListener('click', () => {
            // Remove selected class from all cards
            document.querySelectorAll('.team-list .team-card').forEach(c => {
              c.classList.remove('selected');
            });

            // Add selected class to clicked card
            card.classList.add('selected');

            // Set opponent team id
            opponentTeamIdInput.value = card.getAttribute('data-team-id');

            // Enable start game button
            startGameBtn.disabled = false;
          });
        });

        // Add hover effect to player spots
        playerSpots.forEach(spot => {
          spot.addEventListener('mouseenter', () => {
            spot.style.transform = 'scale(1.1)';
          });

          spot.addEventListener('mouseleave', () => {
            spot.style.transform = 'scale(1)';
          });
        });

        // Initialize with default team if available
        if (localStorage.getItem('selectedTeam')) {
          const savedTeam = localStorage.getItem('selectedTeam');
          teamSelect.value = savedTeam;
          if (teamSelect.value === savedTeam) { // Only if the team still exists
            updatePlayerSpots(savedTeam);
          }
        }

        // Validate game form submission
        document.getElementById('gameForm').addEventListener('submit', (e) => {
          if (!userTeamIdInput.value || !opponentTeamIdInput.value) {
            e.preventDefault();
            alert('Please select both your team and an opponent');
          }
        });
      });
    </script>
  <?php } else { ?>
    <div class="login-error">
      <h1>Login Error! Access denied.</h1>
      <a href="../accounts/signin.php" class="menu-btn primary">Try again</a>
    </div>
  <?php } ?>
</body>

</html>