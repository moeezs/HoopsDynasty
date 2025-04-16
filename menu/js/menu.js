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
          return "../../images/players/lebron.png"; // fallback image
        }
      } catch (err) {
        console.error("Photo fetch error for", name, err);
        return "../../images/players/lebron.png";
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