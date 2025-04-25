<!--
    Authors: Grady Rueffer and Abdul Moeez Shaikh
    Student Numbers: 400579449, 
    Date: 15-03-2025
    Description: This file contains functionality and DOM elements for the team
    building aspect of Hoops Dynasty
    Links to: menu/index.php (On Main Menu)
--> 
<?php
// Start the session and connect to the database
session_start();
require_once("../connect.php");

// Check if user is logged in
if (!isset($_SESSION["userid"])) {
    // Redirect to login page if not logged in
    header("Location: ../accounts/signin.php");
    exit;
}

// Get username for display
$userId = $_SESSION["userid"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoops Dynasty Basketball Team Builder</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="./js/teambuilder.js" defer></script>
</head>
<body>
    <div class="builder-container">
        <!-- Header with Title and Navigation -->
        <div class="builder-header">
            <h1 class="builder-title">Team Builder</h1>
            <a href="../menu/index.php" class="menu-btn primary">
                <i class="fas fa-home"></i> Main Menu
            </a>
        </div>
        
        <!-- Positions Panel -->
        <div class="positions-panel">
            <h2 class="positions-title">Positions</h2>
            
            <div class="positions-list">
                <div class="position-item active" data-pos="PG">
                    <div class="position-icon">PG</div>
                    <div class="position-info">
                        <div class="position-name">Point Guard</div>
                        <div class="position-abbr">Playmaker</div>
                    </div>
                    <div class="position-player"></div>
                </div>
                
                <div class="position-item" data-pos="SG">
                    <div class="position-icon">SG</div>
                    <div class="position-info">
                        <div class="position-name">Shooting Guard</div>
                        <div class="position-abbr">Scorer</div>
                    </div>
                    <div class="position-player"></div>
                </div>
                
                <div class="position-item" data-pos="SF">
                    <div class="position-icon">SF</div>
                    <div class="position-info">
                        <div class="position-name">Small Forward</div>
                        <div class="position-abbr">All-Around</div>
                    </div>
                    <div class="position-player"></div>
                </div>
                
                <div class="position-item" data-pos="PF">
                    <div class="position-icon">PF</div>
                    <div class="position-info">
                        <div class="position-name">Power Forward</div>
                        <div class="position-abbr">Inside Scorer</div>
                    </div>
                    <div class="position-player"></div>
                </div>
                
                <div class="position-item" data-pos="C">
                    <div class="position-icon">C</div>
                    <div class="position-info">
                        <div class="position-name">Center</div>
                        <div class="position-abbr">Rim Protector</div>
                    </div>
                    <div class="position-player"></div>
                </div>
            </div>

            <!-- Team Management Section -->
            <div class="team-management">
                <h3>Your Teams</h3>
                <select id="team-select" class="team-select">
                    <option value="">New Team</option>
                    <!-- Teams will be loaded here via JavaScript -->
                </select>
                <button id="delete-team" class="menu-btn accent">
                    <i class="fas fa-trash"></i> Delete Team
                </button>
            </div>
        </div>
        
        <!-- Preview Panel -->
        <div class="preview-panel">
            <div class="preview-court"></div>
            
            <div class="player-preview" id="playerPreview">
                <!-- Player preview will be populated by JavaScript -->
                <div class="no-player-message">Select a player to preview</div>
            </div>
        </div>
        
        <!-- Players Panel -->
        <div class="players-panel">
            <h2 class="players-title">Available Players</h2>
            
            <div class="players-search">
                <i class="fas fa-search search-icon"></i>
                <input id="NameSearch" type="text" class="search-input" placeholder="Search players...">
            </div>
            <div class="players-search">
                <i class="fas fa-search search-icon"></i>
                <input id="TeamSearch" type="text" class="search-input" placeholder="Search teams...">
            </div>
            
            <div class="players-list" id="players-list">
                <!-- Players will be loaded here via JavaScript -->
                <div class="loading">Loading players...</div>
            </div>
            
            <button id="saveTeam" class="save-team-btn">
                <i class="fas fa-save"></i> Save Team
            </button>
        </div>
    </div>
    
    <!-- Save Team Modal -->
    <div id="saveTeamModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Save Your Team</h3>
                <button class="modal-close">&times;</button>
            </div>
            
            <div class="save-form">
                <div class="form-group">
                    <label for="teamName">Team Name</label>
                    <input type="text" id="teamName" class="search-input" placeholder="Enter team name...">
                </div>
                <div id="saveErrorMessage" class="error-message"></div>
            </div>
            
            <button id="confirmSaveTeam" class="menu-btn accent">
                <i class="fas fa-save"></i> Save Team
            </button>
        </div>
    </div>

    <!-- Status Message -->
    <div id="statusMessage" class="status-message"></div>
</body>
</html>