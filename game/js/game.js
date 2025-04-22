/*
    Author: Daryl John
    Student Number: 400583895
    Date: 20-03-2025
    Description: This file contains all of the JS code needed to
    handle the game logic and UI interactions for the game
*/ 

document.addEventListener('DOMContentLoaded', () => {
	let gameData = {
		teamPlayers: [],
		opponentPlayers: [],
		teamName: 'Home',
		opponentName: 'Away',
		homeScore: 0,
		awayScore: 0,
		eventCount: 0,
		maxEvents: Math.floor(Math.random() * 6) + 20, // Randomly chooses between 20-25 events
		currentPlayer: null,
		opponentPlayer: null,
		offense: true // Game starts on offense
	};

	const stateLabel = document.getElementById('stateLabel');
	const offenseOptions = document.getElementById('offense-options');
	const defenseOptions = document.getElementById('defense-options');
	const resultText = document.getElementById('resultText');
	const ballPossession = document.getElementById('ballPossession');
	const homeScore = document.getElementById('homeScore');
	const awayScore = document.getElementById('awayScore');
	const homeTeamName = document.getElementById('homeTeamName');
	const awayTeamName = document.getElementById('awayTeamName');
	const gameLogContainer = document.getElementById('logEntries');
	const playAgainBtn = document.getElementById('playAgainBtn');

	// Add game log toggle functionality
	const gameLog = document.getElementById('gameLog');
	const logToggle = document.getElementById('logToggle');
	const logToggleIcon = document.getElementById('logToggleIcon');

	if (logToggle) {
		logToggle.addEventListener('click', () => {
			gameLog.classList.toggle('collapsed');
			if (gameLog.classList.contains('collapsed')) {
				logToggleIcon.classList.remove('fa-chevron-left');
				logToggleIcon.classList.add('fa-chevron-right');
			} else {
				logToggleIcon.classList.remove('fa-chevron-right');
				logToggleIcon.classList.add('fa-chevron-left');
			}
		});
	}

	// Player images elements
	const homePlayerImg = document.getElementById('homePlayerImg');
	const awayPlayerImg = document.getElementById('awayPlayerImg');
	const homeBall = document.getElementById('homeBall');
	const awayBall = document.getElementById('awayBall');

	// Ensure ball images have the correct path
	if (homeBall) homeBall.src = '/~ruefferg/Team45/HoopsDynasty/images/ball.png';
	if (awayBall) awayBall.src = '/~ruefferg/Team45/HoopsDynasty/images/ball.png';

	// Set initial ball visibility
	if (homeBall) homeBall.style.display = 'block';
	if (awayBall) awayBall.style.display = 'none';

	initGame();

	/**
	 * Initialize the game by loading team data from the server
	 */
	function initGame() {
		fetch('init-game.php' + window.location.search)
			.then(response => {
				if (!response.ok) {
					throw new Error('Network response was not ok');
				}
				return response.json();
			})
			.then(data => {
				if (data.status === 'error') {
					showError(data.message);
					return;
				}

				// Store game data
				gameData.teamPlayers = data.team;
				gameData.opponentPlayers = data.opponent;
				gameData.teamName = data.teamName;
				gameData.opponentName = data.opponentName;

				// Update UI
				homeTeamName.textContent = gameData.teamName;
				awayTeamName.textContent = gameData.opponentName;

				function convertPlayerStats(players) {
					players.forEach(player => {
							player.three_point_percentage = parseFloat(player.three_point_percentage) || 0;
							player.two_point_percentage = parseFloat(player.two_point_percentage) || 0;
							player.free_throw_percentage = parseFloat(player.free_throw_percentage) || 0;
							player.blocks_per_game = parseFloat(player.blocks_per_game) || 0;
							player.steals_per_game = parseFloat(player.steals_per_game) || 0;
							player.personal_fouls_per_game = parseFloat(player.personal_fouls_per_game) || 0;
					});
				}

				convertPlayerStats(gameData.teamPlayers);
				convertPlayerStats(gameData.opponentPlayers);

				selectRandomPlayers();

				updateUI();

				addButtonListeners();

				addToGameLog(`Game between ${gameData.teamName} and ${gameData.opponentName} has started!`, true);
			})
			.catch(error => {
				showError('Failed to load game data: ' + error.message);
				console.error("Game initialization error:", error);
			});
	}

	/**
	 * Add event listeners to action buttons
	 */
	function addButtonListeners() {
		document.querySelectorAll('.action-btn').forEach(button => {
			button.addEventListener('click', () => {
				const action = button.getAttribute('data-action');
				handlePlayerAction(action);
			});
		});

		playAgainBtn.addEventListener('click', () => {
			location.reload();
		});
	}

	/**
	 * Handle player actions
	 * @param {string} action - The action performed
	 */
	function handlePlayerAction(action) {
		// Prevents actions if game is over
		if (gameData.eventCount >= gameData.maxEvents) {
			endGame();
			return;
		}

		let success = false;
		let message = '';
		let points = 0;

		if (gameData.offense) {
			// Offensive actions
			switch (action) {
				case 'three-pointer':
					success = Math.random() * 100 < gameData.currentPlayer.three_point_percentage;
					message = success
						? `${gameData.currentPlayer.player_name} shoots a three-pointer and scores!`
						: `${gameData.currentPlayer.player_name} attempts a three-pointer but misses.`;
					points = success ? 3 : 0;
					break;

				case 'layup':
					success = Math.random() * 100 < gameData.currentPlayer.two_point_percentage;
					message = success
						? `${gameData.currentPlayer.player_name} drives in for a layup and scores!`
						: `${gameData.currentPlayer.player_name} misses the layup.`;
					points = success ? 2 : 0;
					break;

				case 'pass':
					const teammate = getRandomTeammate();
					success = Math.random() * 100 < (gameData.currentPlayer.two_point_percentage + 10); // Higher chance to score after pass
					message = success
						? `${gameData.currentPlayer.player_name} passes to ${teammate.player_name} who shoots and scores!`
						: `${gameData.currentPlayer.player_name} passes to ${teammate.player_name} who misses the shot.`;
					points = success ? 2 : 0;
					break;

				case 'dunk':
					success = Math.random() * 100 < (gameData.currentPlayer.two_point_percentage - 10); // Lower chance to score dunks
					message = success
						? `${gameData.currentPlayer.player_name} goes for a dunk and slams it home!`
						: `${gameData.currentPlayer.player_name} attempts to dunk but is blocked.`;
					points = success ? 2 : 0;
					break;
			}

			// Update score if successful
			if (success) {
				gameData.homeScore += points;
				homeScore.textContent = gameData.homeScore;
			}
		} else {
			// Defensive actions
			const opponentOffenseChance = getOpponentOffenseChance();

			const blocksPerGame = Number(gameData.currentPlayer.blocks_per_game);
			const stealsPerGame = Number(gameData.currentPlayer.steals_per_game);

			const blockChance = 18 + Math.min(80, blocksPerGame * 25);
			const stealChance = 20 + Math.min(70, stealsPerGame * 15);
			const tackleChance = 15 + Math.min(70, stealsPerGame * 0.8 * 15);
			const pressureChance = 40 + Math.min(70, stealsPerGame * 15);

			switch (action) {
				case 'block':
					success = Math.random() * 100 < blockChance;
					message = success
						? `${gameData.currentPlayer.player_name} jumps to block ${gameData.opponentPlayer.player_name}'s shot!`
						: `${gameData.currentPlayer.player_name} tries to jump and block, but ${gameData.opponentPlayer.player_name} scores.`;
					break;

				case 'steal':
					success = Math.random() * 100 < stealChance;
					message = success
						? `${gameData.currentPlayer.player_name} reaches for a steal from ${gameData.opponentPlayer.player_name}!`
						: `${gameData.currentPlayer.player_name} fails to reach for the steal and ${gameData.opponentPlayer.player_name} scores.`;
					break;

				case 'tackle':
					success = Math.random() * 100 < tackleChance;
					message = success
						? `${gameData.currentPlayer.player_name} cuts off the pass and stops ${gameData.opponentPlayer.player_name}!`
						: `${gameData.currentPlayer.player_name} fails to cut off the pass and ${gameData.opponentPlayer.player_name} scores.`;
					break;

				case 'pressure':
					success = Math.random() * 100 < pressureChance;
					message = success
						? `${gameData.currentPlayer.player_name} applies pressure and forces ${gameData.opponentPlayer.player_name} to miss!`
						: `Despite pressure from ${gameData.currentPlayer.player_name}, ${gameData.opponentPlayer.player_name} scores.`;
					break;
			}

			// If defense fails, opponent scores
			if (!success) {
				// Determines if it's a 2 or 3 pointer
				const isThreePointer = Math.random() < 0.4; // 40% chance of three-pointer
				points = isThreePointer ? 3 : 2;
				gameData.awayScore += points;
				awayScore.textContent = gameData.awayScore;
			}
		}

		resultText.textContent = message;
		addToGameLog(message, false, success); // Pass success or failure to the log

		gameData.eventCount++;

		// Check for special events
		if (Math.random() < 0.3 && gameData.eventCount < gameData.maxEvents) { // 30% chance of special event
			handleSpecialEvent();
		} else {
			// Switch between offense and defense
			gameData.offense = !gameData.offense;

			selectRandomPlayers();

			updateUI();
		}
	}

	/**
	 * Handle special events like fouls, fast breaks, etc.
	 */
	function handleSpecialEvent() {
		const eventType = Math.floor(Math.random() * 5); // 5 types of special events
		let message = '';

		switch (eventType) {
			case 0: // Foul
				const foulingPlayer = gameData.offense ? getRandomPlayer(gameData.opponentPlayers) : gameData.currentPlayer;
				const fouledPlayer = gameData.offense ? gameData.currentPlayer : gameData.opponentPlayer;
				message = `FOUL: ${foulingPlayer.player_name} fouls ${fouledPlayer.player_name}. Free throw awarded.`;
				addToGameLog(message, true);
				handleFreeThrow(fouledPlayer);
				break;

			case 1: // Fast break
				const fastBreakPlayer = gameData.offense ? gameData.currentPlayer : gameData.opponentPlayer;
				const fastBreakTeam = gameData.offense ? gameData.teamName : gameData.opponentName;
				message = `FAST BREAK: ${fastBreakPlayer.player_name} leads a fast break for ${fastBreakTeam}!`;
				addToGameLog(message, true);

				// Higher chance of scoring on fast break
				const success = Math.random() < 0.7; // 70% chance
				if (success) {
					const points = Math.random() < 0.3 ? 3 : 2; // 30% chance of three-pointer
					if (gameData.offense) {
						gameData.homeScore += points;
						homeScore.textContent = gameData.homeScore;
						message = `${fastBreakPlayer.player_name} scores ${points} points on the fast break!`;
					} else {
						gameData.awayScore += points;
						awayScore.textContent = gameData.awayScore;
						message = `${fastBreakPlayer.player_name} scores ${points} points on the fast break!`;
					}
				} else {
					message = `${fastBreakPlayer.player_name} misses on the fast break!`;
				}

				resultText.textContent = message;
				addToGameLog(message);

				gameData.eventCount++;

				// Switch between offense and defense
				gameData.offense = !gameData.offense;
				selectRandomPlayers();
				updateUI();

				break;

			case 2: // Temporary stat boost
				const boostedPlayer = gameData.offense ? gameData.currentPlayer : getRandomPlayer(gameData.teamPlayers);
				message = `MOMENTUM BOOST: ${boostedPlayer.player_name} is feeling confident!`;
				addToGameLog(message, true);

				// Temporary boost to shooting percentages
				boostedPlayer.three_point_percentage += 10;
				boostedPlayer.two_point_percentage += 10;

				setTimeout(() => {
					// Reverts the boost after a few seconds
					boostedPlayer.three_point_percentage -= 10;
					boostedPlayer.two_point_percentage -= 10;
					addToGameLog(`${boostedPlayer.player_name}'s confidence boost has worn off.`);
				}, 10000); // Resets after 10 seconds

				gameData.offense = !gameData.offense;
				selectRandomPlayers();
				updateUI();
				break;

			case 3: // Shot clock violation
				const violationTeam = gameData.offense ? gameData.teamName : gameData.opponentName;
				message = `SHOT CLOCK VIOLATION: ${violationTeam} took too long to shoot!`;
				addToGameLog(message, true);

				// Switchs possession
				gameData.offense = !gameData.offense;
				selectRandomPlayers();
				updateUI();
				break;

			case 4: // Technical foul
				const technicalPlayer = gameData.offense ? getRandomPlayer(gameData.opponentPlayers) : gameData.currentPlayer;
				message = `TECHNICAL FOUL: ${technicalPlayer.player_name} receives a technical foul!`;
				addToGameLog(message, true);

				// Free throw for the other team
				const freeThrowShooter = gameData.offense ? gameData.currentPlayer : gameData.opponentPlayer;
				handleFreeThrow(freeThrowShooter);
				break;
		}
	}

	/**
	 * Handle free throws
	 * @param {Object} player - The player taking the free throw
	 */
	function handleFreeThrow(player) {
		const isHomeTeam = gameData.teamPlayers.some(p => p.player_ID === player.player_ID);
		const freeThrowSuccess = Math.random() * 100 < player.free_throw_percentage;

		let message = `${player.player_name} `;

		if (freeThrowSuccess) {
			message += `makes the free throw!`;
			if (isHomeTeam) {
				gameData.homeScore += 1;
				homeScore.textContent = gameData.homeScore;
			} else {
				gameData.awayScore += 1;
				awayScore.textContent = gameData.awayScore;
			}
		} else {
			message += `misses the free throw.`;
		}

		resultText.textContent = message;
		addToGameLog(message);

		gameData.eventCount++;

		// Switch between offense and defense
		gameData.offense = !gameData.offense;
		selectRandomPlayers();
		updateUI();

	}

	/**
	 * Calculate opponent's offense chance
	 * @returns {number} - Chance of successful offense
	 */
	function getOpponentOffenseChance() {
		// Changes the base probability on player position
		let chance;

		if (gameData.opponentPlayer.player_position === 'point guard' ||
			gameData.opponentPlayer.player_position === 'shooting guard') {
			chance = gameData.opponentPlayer.three_point_percentage * 0.6 +
				gameData.opponentPlayer.two_point_percentage * 0.4;
		} else {
			chance = gameData.opponentPlayer.two_point_percentage * 0.8 +
				gameData.opponentPlayer.three_point_percentage * 0.2;
		}

		return chance;
	}

	/**
	 * Select random players for the current play
	 */
	function selectRandomPlayers() {
		gameData.currentPlayer = getRandomPlayer(gameData.teamPlayers);
		gameData.opponentPlayer = getRandomPlayer(gameData.opponentPlayers);

		// Update player images when players change
		updatePlayerImages();
	}

	/**
	 * Get a random player from the given array
	 * @param {Array} players - Array of player objects
	 * @returns {Object} - A random player
	 */
	function getRandomPlayer(players) {
		return players[Math.floor(Math.random() * players.length)];
	}

	/**
	 * Get a random teammate different from the current player
	 * @returns {Object} - A random teammate
	 */
	function getRandomTeammate() {
		let teammates = gameData.teamPlayers.filter(player =>
			player.player_ID !== gameData.currentPlayer.player_ID
		);
		return teammates[Math.floor(Math.random() * teammates.length)];
	}

	/**
	 * Add a message to the game log
	 * @param {string} message - The message to add
	 * @param {boolean} isSpecial - Whether this is a special event
	 * @param {boolean|null} isSuccess - Whether the action was successful
	 */
	function addToGameLog(message, isSpecial = false, isSuccess = null) {
		const logEntry = document.createElement('div');
		logEntry.classList.add('log-entry');
		if (isSpecial) {
			logEntry.classList.add('special-event');
		}
		if (isSuccess === true) {
			logEntry.classList.add('success');
		} else if (isSuccess === false) {
			logEntry.classList.add('failure');
		}
		logEntry.textContent = message;
		gameLogContainer.appendChild(logEntry);
		gameLogContainer.scrollTop = gameLogContainer.scrollHeight;
	}

	/**
 * Update UI based on game state
 */
function updateUI() {
    if (gameData.eventCount >= gameData.maxEvents) {
        endGame();
        return;
    }

    if (gameData.offense) {
        stateLabel.textContent = 'Offense';
        offenseOptions.style.display = 'flex';
        defenseOptions.style.display = 'none';
        ballPossession.textContent = `${gameData.currentPlayer.player_name} (${gameData.teamName})`;
        
        // Show home ball, hide away ball during offense
        if (homeBall) homeBall.style.display = 'block';
        if (awayBall) awayBall.style.display = 'none';

        // Update chance percentages for offense buttons
        document.querySelector('button[data-action="three-pointer"] .chance').textContent =
            `(${gameData.currentPlayer.three_point_percentage.toFixed(1)}%)`;
        document.querySelector('button[data-action="layup"] .chance').textContent =
            `(${gameData.currentPlayer.two_point_percentage.toFixed(1)}%)`;
        document.querySelector('button[data-action="pass"] .chance').textContent =
            `(${(gameData.currentPlayer.two_point_percentage + 10).toFixed(1)}%)`;
        document.querySelector('button[data-action="dunk"] .chance').textContent =
            `(${(gameData.currentPlayer.two_point_percentage - 10).toFixed(1)}%)`;
    } else {
        stateLabel.textContent = 'Defense';
        offenseOptions.style.display = 'none';
        defenseOptions.style.display = 'flex';
        ballPossession.textContent = `${gameData.opponentPlayer.player_name} (${gameData.opponentName})`;
        
        // Show away ball, hide home ball during defense
        if (homeBall) homeBall.style.display = 'none';
        if (awayBall) awayBall.style.display = 'block';

        const blocksPerGame = Number(gameData.currentPlayer.blocks_per_game);
        const stealsPerGame = Number(gameData.currentPlayer.steals_per_game);

        // Convert blocks and steals per game to a reasonable percentage
        const blockChance = Math.min(80, blocksPerGame * 25);
        const stealChance = Math.min(70, stealsPerGame * 15);

        // Update chance percentages for defense buttons
        document.querySelector('button[data-action="block"] .chance').textContent =
            `(${isNaN(blockChance) ? '18.0' : (18 + blockChance).toFixed(1)}%)`;
        document.querySelector('button[data-action="steal"] .chance').textContent =
            `(${isNaN(stealChance) ? '20.0' : (20 + stealChance).toFixed(1)}%)`;
        document.querySelector('button[data-action="tackle"] .chance').textContent =
            `(${isNaN(stealChance) ? '15.0' : (15 + (stealChance * 0.8)).toFixed(1)}%)`;
        document.querySelector('button[data-action="pressure"] .chance').textContent =
            `(${isNaN(stealChance) ? '40.0' : (40 + stealChance).toFixed(1)}%)`;
    }

    resultText.textContent = 'Choose an action';

    playAgainBtn.style.display = 'none';

    updatePlayerImages();
}

	/**
	 * Update player images based on current players
	 */
	function updatePlayerImages() {
		if (homePlayerImg && gameData.currentPlayer) {
			homePlayerImg.src = gameData.currentPlayer.photo || "/~ruefferg/Team45/HoopsDynasty/images/players/lebron.png";
			homePlayerImg.alt = gameData.currentPlayer.player_name;
			// Add error handling for player images
			homePlayerImg.onerror = function () {
				this.src = "/~ruefferg/Team45/HoopsDynasty/images/players/lebron.png";
				console.log("Error loading player image, using default");
			};
		}

		if (awayPlayerImg && gameData.opponentPlayer) {
			awayPlayerImg.src = gameData.opponentPlayer.photo || "/~ruefferg/Team45/HoopsDynasty/images/players/lebron.png";
			awayPlayerImg.alt = gameData.opponentPlayer.player_name;
			// Add error handling for player images
			awayPlayerImg.onerror = function () {
				this.src = "/~ruefferg/Team45/HoopsDynasty/images/players/lebron.png";
				console.log("Error loading player image, using default");
			};
		}
	}

	/**
	 * Ends the game when max events are reached
	 */
	function endGame() {
		stateLabel.textContent = 'Game Over';
		offenseOptions.style.display = 'none';
		defenseOptions.style.display = 'none';

		// Determine winner
		let finalMessage;
		if (gameData.homeScore > gameData.awayScore) {
			finalMessage = `${gameData.teamName} wins the game ${gameData.homeScore}-${gameData.awayScore}!`;
		} else if (gameData.homeScore < gameData.awayScore) {
			finalMessage = `${gameData.opponentName} wins the game ${gameData.awayScore}-${gameData.homeScore}!`;
		} else {
			finalMessage = `The game ends in a tie, ${gameData.homeScore}-${gameData.awayScore}!`;
		}

		resultText.textContent = finalMessage;
		addToGameLog(finalMessage, true);

		playAgainBtn.style.display = 'block';
	}

	/**
	 * Show error message
	 * @param {string} message - Error message
	 */
	function showError(message) {
		console.error("Game error:", message);
		resultText.textContent = 'Error: ' + message;
		resultText.style.color = 'red';
	}
});