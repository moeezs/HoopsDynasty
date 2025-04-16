document.addEventListener('DOMContentLoaded', () => {
  // DOM Elements
  const positionItems = document.querySelectorAll('.position-item');
  const playersList = document.getElementById('players-list');
  const saveTeamBtn = document.getElementById('saveTeam');
  const saveTeamModal = document.getElementById('saveTeamModal');
  const modalClose = document.querySelector('.modal-close');
  const searchNameInput = document.getElementById("NameSearch");
  const searchTeamInput = document.getElementById("TeamSearch");
  const teamSelect = document.getElementById('team-select');
  const confirmSaveTeam = document.getElementById('confirmSaveTeam');
  const teamNameInput = document.getElementById('teamName');
  const deleteTeamBtn = document.getElementById('delete-team');
  const statusMessage = document.getElementById('statusMessage');
  const saveErrorMessage = document.getElementById('saveErrorMessage');

  // State variables
  let selectedPosition = 'PG'; // Default selected position
  let selectedPlayer = null;
  let players = []; // Will be populated from API
  let teamPlayers = {
    PG: null,
    SG: null,
    SF: null,
    PF: null,
    C: null
  };

  // Function to show a status message
  function showStatus(message, isError = false) {
    statusMessage.textContent = message;
    statusMessage.classList.add('active');

    if (isError) {
      statusMessage.classList.add('error');
    } else {
      statusMessage.classList.remove('error');
    }

    setTimeout(() => {
      statusMessage.classList.remove('active');
    }, 3000);
  }

  // Function to load all available players from database
  async function loadPlayers() {
    try {
      const response = await fetch('queries/getplayers.php');
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }

      const data = await response.json();

      if (data.status === "NONE") {
        playersList.innerHTML = '<div class="no-results">No players found</div>';
        players = [];
        return;
      }

      // Map player data from database
      players = data.map(player => ({
        id: player.player_id,
        name: player.player_name,
        team: player.player_team,
        image: player.photo || '../images/players/lebron.png',
        // Stats from database - handle as floats
        threePointPct: parseFloat(player.three_point_percentage) || 0,
        twoPointPct: parseFloat(player.two_point_percentage) || 0,
        freeThrowPct: parseFloat(player.free_throw_percentage) || 0,
        blocksPerGame: parseFloat(player.blocks_per_game) || 0,
        stealsPerGame: parseFloat(player.steals_per_game) || 0,
        foulsPerGame: parseFloat(player.personal_fouls_per_game) || 0
      }));

      renderPlayersList();
    } catch (error) {
      playersList.innerHTML = '<div class="error">Failed to load players</div>';
    }
  }

  // Function to render the players list
  function renderPlayersList() {
    playersList.innerHTML = '';

    if (players.length === 0) {
      playersList.innerHTML = '<div class="no-results">No players found</div>';
      return;
    }

    players.forEach(player => {
      const playerItem = document.createElement('div');
      playerItem.className = 'player-item';
      playerItem.setAttribute('data-id', player.id);

      playerItem.innerHTML = `
        <div class="player-avatar">
          <img src="${player.image}" alt="${player.name}" onerror="this.src='../images/players/lebron.png'">
        </div>
        <div class="player-details">
          <div class="player-name-list">${player.name}</div>
          <div class="player-position">${player.team || 'Free Agent'}</div>
        </div>
      `;

      playerItem.addEventListener('click', () => {
        // Remove selected class from all player items
        playersList.querySelectorAll('.player-item').forEach(p => p.classList.remove('selected'));

        // Add selected class to clicked item
        playerItem.classList.add('selected');

        showPlayerPreview(player.id);
      });

      playersList.appendChild(playerItem);
    });
  }

  // Function to load user's existing teams
  async function loadUserTeams() {
    try {
      const response = await fetch('queries/getteams.php');
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }

      const data = await response.json();

      // Clear existing options except the first one
      while (teamSelect.options.length > 1) {
        teamSelect.remove(1);
      }

      if (data.length === 0) {
        return;
      }

      data.forEach(team => {
        const option = document.createElement('option');
        option.value = team.team_name;
        option.textContent = team.team_name;
        teamSelect.appendChild(option);
      });
    } catch (error) {
      console.error('Error loading teams:', error);
      showStatus('Failed to load your teams', true);
    }
  }

  // Function to load a specific team
  async function loadTeam(teamName) {
    if (!teamName) {
      // If no team name, reset to empty team
      resetTeam();
      return;
    }

    try {
      const params = "team_name=" + encodeURIComponent(teamName);

      const response = await fetch("queries/getteamplayers.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        body: params,
        cache: "no-store",
        credentials: "same-origin"
      });

      if (!response.ok) {
        throw new Error('Team is Missing');
      }

      const data = await response.json();

      if (data.status === 'error') {
        showStatus(data.message, true);
        return;
      }

      resetTeam();

      const teamData = data.players;
      if (teamData.length === 0) {
        return;
      }

      // Assign players to positions
      teamData.forEach(player => {
        const playerObj = {
          id: player.player_id,
          name: player.player_name,
          team: player.player_team,
          image: player.photo || '../images/players/lebron.png',
          // Stats from database
          threePointPct: parseFloat(player.three_point_percentage) || 0,
          twoPointPct: parseFloat(player.two_point_percentage) || 0,
          freeThrowPct: parseFloat(player.free_throw_percentage) || 0,
          blocksPerGame: parseFloat(player.blocks_per_game) || 0,
          stealsPerGame: parseFloat(player.steals_per_game) || 0,
          foulsPerGame: parseFloat(player.personal_fouls_per_game) || 0
        };

        // Assign to position based on the saved position
        const position = player.position;
        if (position && Object.keys(teamPlayers).includes(position)) {
          teamPlayers[position] = playerObj;
        }
      });

      updatePositionSelection();
      updateSaveButtonState();
      showStatus(`Team "${teamName}" loaded successfully`);
    } catch (error) {
      console.error('Error loading team players:', error);
      showStatus('Failed to load team', true);
    }
  }

  // Function to reset team to empty
  function resetTeam() {
    teamPlayers = {
      PG: null,
      SG: null,
      SF: null,
      PF: null,
      C: null
    };
    updatePositionSelection();
    updateSaveButtonState();
  }

  // Function to delete a team
  async function deleteTeam(teamName) {
    if (!teamName) {
      return;
    }

    try {
      const formData = new FormData();
      formData.append('team_name', teamName);

      const response = await fetch('queries/deleteteam.php', {
        method: 'POST',
        body: formData
      });

      if (!response.ok) {
        throw new Error('Network response was not ok');
      }

      const data = await response.json();

      if (data.status === 'error') {
        showStatus(data.message, true);
        return;
      }

      // Remove from dropdown and reset
      const index = Array.from(teamSelect.options).findIndex(option => option.value === teamName);
      if (index > 0) {
        teamSelect.remove(index);
      }

      teamSelect.value = '';
      resetTeam();
      showStatus(`Team "${teamName}" deleted successfully`);
    } catch (error) {
      console.error('Error deleting team:', error);
      showStatus('Failed to delete team', true);
    }
  }

  // Function to save the current team
  async function saveTeam(teamName) {
    saveErrorMessage.textContent = '';

    // Validate team has all positions filled
    if (!isTeamComplete()) {
      saveErrorMessage.textContent = 'Please fill all positions before saving.';
      return;
    }

    // Validate team has different players in each position
    if (!hasUniquePlayersInPositions()) {
      saveErrorMessage.textContent = 'Cannot use the same player in multiple positions.';
      return;
    }

    // Validate team name
    if (!teamName || teamName.trim() === '') {
      saveErrorMessage.textContent = 'Please enter a team name.';
      return;
    }

    // Collect player names instead of IDs
    const playerNames = [];
    const positions = [];

    Object.entries(teamPlayers).forEach(([position, player]) => {
      if (player) {
        playerNames.push(player.name);
        positions.push(position);
      }
    });

    console.log("Sending player names:", playerNames);
    console.log("Sending positions:", positions);

    try {
      const formData = new FormData();
      formData.append('team_name', teamName);
      formData.append('player_names', JSON.stringify(playerNames));
      formData.append('positions', JSON.stringify(positions));

      const response = await fetch('queries/saveteam.php', {
        method: 'POST',
        body: formData
      });

      const text = await response.text();
      console.log("Raw response:", text);

      let data;
      try {
        data = JSON.parse(text);
      } catch (e) {
        console.error("Failed to parse JSON response:", e);
        saveErrorMessage.textContent = 'Invalid response from server. Check console for details.';
        return;
      }

      console.log("Save team response:", data);

      if (data.status === 'error') {
        saveErrorMessage.textContent = data.message;
        return;
      }

      // Close modal
      saveTeamModal.classList.remove('active');

      // Add to dropdown if not already there
      const exists = Array.from(teamSelect.options).some(option => option.value === teamName);

      if (!exists) {
        const option = document.createElement('option');
        option.value = teamName;
        option.textContent = teamName;
        teamSelect.appendChild(option);
      }

      teamSelect.value = teamName;
      showStatus(`Team "${teamName}" saved successfully`);
    } catch (error) {
      console.error('Error saving team:', error);
      saveErrorMessage.textContent = 'Failed to save team. Please try again.';
    }
  }

  // Function to update the position selection UI
  function updatePositionSelection() {
    positionItems.forEach(item => {
      const pos = item.getAttribute('data-pos');

      // Update active state
      if (pos === selectedPosition) {
        item.classList.add('active');
      } else {
        item.classList.remove('active');
      }

      // Show player in position if assigned
      const playerSlot = item.querySelector('.position-player');
      playerSlot.innerHTML = '';

      if (teamPlayers[pos]) {
        const img = document.createElement('img');
        img.src = teamPlayers[pos].image;
        img.alt = teamPlayers[pos].name;
        // Add error handling for images
        img.onerror = function () {
          this.src = '../images/players/lebron.png';
        };
        playerSlot.appendChild(img);
      }
    });

    updateSaveButtonState();
  }

  // Function to check if all positions are filled with unique players
  function hasUniquePlayersInPositions() {
    const usedPlayerIds = new Set();

    for (const player of Object.values(teamPlayers)) {
      if (player) {
        if (usedPlayerIds.has(player.id)) {
          return false; // Duplicate player found
        }
        usedPlayerIds.add(player.id);
      }
    }

    return true;
  }

  // Function to update save button state
  function updateSaveButtonState() {
    if (isTeamComplete() && hasUniquePlayersInPositions()) {
      saveTeamBtn.classList.add('pulse-animation');
      saveTeamBtn.disabled = false;
    } else {
      saveTeamBtn.classList.remove('pulse-animation');
      saveTeamBtn.disabled = true;
    }
  }

  // Function to populate player preview
  function showPlayerPreview(playerId) {
    const player = players.find(p => p.id == playerId);

    if (!player) {
      document.getElementById('playerPreview').innerHTML = '<div class="no-player-message">Select a player</div>';
      return;
    }

    selectedPlayer = player;

    // Format percentages and stats for display
    const threePoint = (player.threePointPct).toFixed(1) + '%';
    const twoPoint = (player.twoPointPct).toFixed(1) + '%';
    const freeThrow = (player.freeThrowPct).toFixed(1) + '%';
    const blocks = player.blocksPerGame.toFixed(1);
    const steals = player.stealsPerGame.toFixed(1);
    const fouls = player.foulsPerGame.toFixed(1);

    // Create preview HTML
    const previewHTML = `
      <img src="${player.image}" alt="${player.name}" class="preview-img" onerror="this.src='../images/players/lebron.png'">
      <h3 class="preview-name">${player.name}</h3>
      <div class="preview-team">${player.team || 'Free Agent'}</div>
      
      <div class="preview-stats">
        <div class="stat-item">
          <div class="stat-value">${threePoint}</div>
          <div class="stat-label">3PT%</div>
        </div>
        <div class="stat-item">
          <div class="stat-value">${twoPoint}</div>
          <div class="stat-label">2PT%</div>
        </div>
        <div class="stat-item">
          <div class="stat-value">${freeThrow}</div>
          <div class="stat-label">FT%</div>
        </div>
        <div class="stat-item">
          <div class="stat-value">${blocks}</div>
          <div class="stat-label">BLOCKS/G</div>
        </div>
        <div class="stat-item">
          <div class="stat-value">${steals}</div>
          <div class="stat-label">STEALS/G</div>
        </div>
        <div class="stat-item">
          <div class="stat-value">${fouls}</div>
          <div class="stat-label">FOULS/G</div>
        </div>
      </div>
      
      <button id="addToTeam" class="add-player-btn">
        <i class="fas fa-plus-circle"></i> Add to Position
      </button>
    `;

    document.getElementById('playerPreview').innerHTML = previewHTML;

    // Re-attach event listener to the new button
    const newAddToTeamBtn = document.getElementById('addToTeam');
    if (newAddToTeamBtn) {
      newAddToTeamBtn.addEventListener('click', () => {
        if (selectedPlayer && selectedPosition) {
          teamPlayers[selectedPosition] = selectedPlayer;
          updatePositionSelection();
          updateSaveButtonState();
        }
      });
    }
  }

  // Helper to get full position name
  function getPositionFullName(posCode) {
    const positions = {
      PG: 'Point Guard',
      SG: 'Shooting Guard',
      SF: 'Small Forward',
      PF: 'Power Forward',
      C: 'Center'
    };
    return positions[posCode] || posCode;
  }

  // Function to check if team is complete
  function isTeamComplete() {
    return Object.values(teamPlayers).every(player => player !== null);
  }

  // Function to filter players
  function filterPlayers(name, team) {
    let queriedPlayers = [];
    let params = "";

    if (name !== "") {
      params += "name=" + encodeURIComponent(name);
    }
    if (team !== "") {
      if (params !== "") params += "&";
      params += "team=" + encodeURIComponent(team);
    }

    let config = {
      method: 'POST',
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: params
    };

    fetch("../teambuilder/queries/getplayers.php", config)
      .then(response => response.json())
      .then(data => {
        players = data.map(player => ({
          id: player.player_id,
          name: player.player_name,
          team: player.player_team,
          image: player.photo || '../images/players/lebron.png',
          threePointPct: parseFloat(player.three_point_percentage) || 0,
          twoPointPct: parseFloat(player.two_point_percentage) || 0,
          freeThrowPct: parseFloat(player.free_throw_percentage) || 0,
          blocksPerGame: parseFloat(player.blocks_per_game) || 0,
          stealsPerGame: parseFloat(player.steals_per_game) || 0,
          foulsPerGame: parseFloat(player.personal_fouls_per_game) || 0
        }));

        if (players.length === 0 || players[0] === "NONE") {
          playersList.innerHTML = '<div class="no-results">No matching players found</div>';
          return;
        }

        renderPlayersList();
      })
  }

  // Event Listeners
  positionItems.forEach(item => {
    item.addEventListener('click', () => {
      selectedPosition = item.getAttribute('data-pos');
      updatePositionSelection();
    });
  });

  // Team select dropdown change
  teamSelect.addEventListener('change', (e) => {
    e.preventDefault();
    loadTeam(teamSelect.value);
  });


  // Delete team button
  deleteTeamBtn.addEventListener('click', () => {
    deleteTeam(teamSelect.value);
  });

  // Save team button
  saveTeamBtn.addEventListener('click', () => {
    // Check if team is complete and has unique players
    if (isTeamComplete() && hasUniquePlayersInPositions()) {
      teamNameInput.value = teamSelect.value;
      saveTeamModal.classList.add('active');
    } else if (!isTeamComplete()) {
      showStatus('Please fill all positions before saving.', true);
    } else {
      showStatus('Cannot use the same player in multiple positions.', true);
    }
  });

  // Confirm save in modal
  confirmSaveTeam.addEventListener('click', () => {
    saveTeam(teamNameInput.value).then(() => {
      // Redirect to main menu after saving
      window.location.href = '../menu/index.php';
    });
  });

  modalClose.addEventListener('click', () => {
    saveTeamModal.classList.remove('active');
  });

  // Close modal if clicking outside of content
  saveTeamModal.addEventListener('click', (e) => {
    if (e.target === saveTeamModal) {
      saveTeamModal.classList.remove('active');
    }
  });

  // Search functionality
  searchNameInput.addEventListener('input', () => {
    filterPlayers(searchNameInput.value, searchTeamInput.value);
  });

  searchTeamInput.addEventListener('input', () => {
    filterPlayers(searchNameInput.value, searchTeamInput.value);
  });

  // Initialize
  loadPlayers();
  loadUserTeams();
  updatePositionSelection();
  // Disable save button by default until team is complete
  saveTeamBtn.disabled = true;

  function updateStatCaptions() {
    document.querySelectorAll('.stat-caption').forEach(caption => {
      const statType = caption.getAttribute('data-stat');
      switch (statType) {
        case 'pointsPerGame':
          caption.textContent = 'Points Per Game';
          break;
        case 'assistsPerGame':
          caption.textContent = 'Assists Per Game';
          break;
        case 'reboundsPerGame':
          caption.textContent = 'Rebounds Per Game';
          break;
        case 'stealsPerGame':
          caption.textContent = 'Steals Per Game';
          break;
        case 'blocksPerGame':
          caption.textContent = 'Blocks Per Game';
          break;
        default:
          caption.textContent = 'Unknown Stat';
      }
    });
  }

  // Call updateStatCaptions on page load
  updateStatCaptions();
});
