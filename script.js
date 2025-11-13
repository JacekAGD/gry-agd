const WIDTH = 10;
const HEIGHT = 20;
const DROP_INTERVAL_START = 900;
const DROP_INTERVAL_MIN = 150;
const DROP_ACCELERATION = 35;

const tetrominoes = [
  {
    type: "pralka",
    label: "Pralka bębnowa",
    icon: "🧺",
    rotations: [
      [
        [0, 1, 0, 0],
        [0, 1, 0, 0],
        [0, 1, 1, 0],
        [0, 0, 0, 0],
      ],
      [
        [0, 0, 1, 0],
        [1, 1, 1, 0],
        [0, 0, 0, 0],
        [0, 0, 0, 0],
      ],
    ],
  },
  {
    type: "zmywarka",
    label: "Zmywarka do naczyń",
    icon: "🧽",
    rotations: [
      [
        [0, 0, 0, 0],
        [1, 1, 0, 0],
        [0, 1, 1, 0],
        [0, 0, 0, 0],
      ],
      [
        [0, 1, 0, 0],
        [1, 1, 0, 0],
        [1, 0, 0, 0],
        [0, 0, 0, 0],
      ],
    ],
  },
  {
    type: "lodowka",
    label: "Lodówka side-by-side",
    icon: "🧊",
    rotations: [
      [
        [0, 0, 0, 0],
        [1, 1, 1, 1],
        [0, 0, 0, 0],
        [0, 0, 0, 0],
      ],
      [
        [0, 1, 0, 0],
        [0, 1, 0, 0],
        [0, 1, 0, 0],
        [0, 1, 0, 0],
      ],
    ],
  },
  {
    type: "odkurzacz",
    label: "Odkurzacz pionowy",
    icon: "🧹",
    rotations: [
      [
        [0, 0, 0, 0],
        [1, 1, 1, 0],
        [0, 1, 0, 0],
        [0, 0, 0, 0],
      ],
      [
        [0, 1, 0, 0],
        [1, 1, 0, 0],
        [0, 1, 0, 0],
        [0, 0, 0, 0],
      ],
      [
        [0, 1, 0, 0],
        [1, 1, 1, 0],
        [0, 0, 0, 0],
        [0, 0, 0, 0],
      ],
      [
        [0, 1, 0, 0],
        [0, 1, 1, 0],
        [0, 1, 0, 0],
        [0, 0, 0, 0],
      ],
    ],
  },
  {
    type: "toster",
    label: "Toster retro",
    icon: "🍞",
    rotations: [
      [
        [0, 0, 0, 0],
        [1, 1, 0, 0],
        [1, 1, 0, 0],
        [0, 0, 0, 0],
      ],
    ],
  },
  {
    type: "mikrofalowka",
    label: "Mikrofalówka z grillem",
    icon: "📡",
    rotations: [
      [
        [0, 0, 0, 0],
        [0, 1, 1, 0],
        [1, 1, 0, 0],
        [0, 0, 0, 0],
      ],
      [
        [1, 0, 0, 0],
        [1, 1, 0, 0],
        [0, 1, 0, 0],
        [0, 0, 0, 0],
      ],
    ],
  },
  {
    type: "ekspres",
    label: "Ekspres do kawy",
    icon: "☕",
    rotations: [
      [
        [0, 0, 0, 0],
        [1, 1, 1, 0],
        [1, 0, 0, 0],
        [0, 0, 0, 0],
      ],
      [
        [1, 1, 0, 0],
        [0, 1, 0, 0],
        [0, 1, 0, 0],
        [0, 0, 0, 0],
      ],
      [
        [0, 0, 1, 0],
        [1, 1, 1, 0],
        [0, 0, 0, 0],
        [0, 0, 0, 0],
      ],
      [
        [0, 1, 0, 0],
        [0, 1, 0, 0],
        [0, 1, 1, 0],
        [0, 0, 0, 0],
      ],
    ],
  },
];

const boardElement = document.querySelector("#board");
const scoreElement = document.querySelector("#score");
const linesElement = document.querySelector("#lines");
const previewElement = document.querySelector("#preview");
const overlayElement = document.querySelector("#overlay");
const overlayMessageElement = document.querySelector("#overlayMessage");
const overlayContent = document.querySelector(".overlay__content");
const pauseButton = document.querySelector("#pauseButton");

const grid = Array.from({ length: HEIGHT }, () =>
  Array.from({ length: WIDTH }, () => null)
);

const cells = [];

let dropInterval = DROP_INTERVAL_START;
let dropTimer = 0;
let lastTime = 0;
let animationFrame = null;
let score = 0;
let linesCleared = 0;

let gameState = "idle";

let activePiece = null;
let nextPiece = createPiece();

function initBoard() {
  for (let y = 0; y < HEIGHT; y += 1) {
    for (let x = 0; x < WIDTH; x += 1) {
      const cell = document.createElement("div");
      cell.className = "cell";
      cell.dataset.filled = "false";
      boardElement.appendChild(cell);
      cells.push(cell);
    }
  }
}

function initPreview() {
  previewElement.innerHTML = "";
  for (let y = 0; y < 4; y += 1) {
    for (let x = 0; x < 4; x += 1) {
      const cell = document.createElement("div");
      cell.className = "cell";
      cell.dataset.filled = "false";
      previewElement.appendChild(cell);
    }
  }
}

function resetGrid() {
  grid.forEach((row) => row.fill(null));
  activePiece = null;
}

function showOverlay(message) {
  overlayMessageElement.textContent = message;
  overlayElement.hidden = false;
  const focusTarget = () => {
    if (overlayContent) {
      overlayContent.focus({ preventScroll: true });
    }
  };
  if (typeof queueMicrotask === "function") {
    queueMicrotask(focusTarget);
  } else {
    setTimeout(focusTarget, 0);
  }
}

function hideOverlay() {
  overlayElement.hidden = true;
}

function prepareNewGame() {
  resetGrid();
  score = 0;
  linesCleared = 0;
  dropInterval = DROP_INTERVAL_START;
  dropTimer = 0;
  lastTime = 0;
  updateStats();
  drawBoard();
}

function startGame() {
  prepareNewGame();
  spawnPiece(true);
  drawBoard();
  drawActivePiece();
  gameState = "running";
  hideOverlay();
  pauseButton.disabled = false;
  pauseButton.textContent = "Pauza";
  animationFrame = requestAnimationFrame(update);
}

function pauseGame() {
  if (gameState !== "running") return;
  gameState = "paused";
  if (animationFrame) {
    cancelAnimationFrame(animationFrame);
    animationFrame = null;
  }
  pauseButton.textContent = "Wznów";
  showOverlay(
    "Przerwa na kawę! Kliknij komunikat lub naciśnij Enter, aby wrócić do układania sprzętów."
  );
}

function resumeGame() {
  if (gameState !== "paused") return;
  gameState = "running";
  hideOverlay();
  pauseButton.textContent = "Pauza";
  lastTime = 0;
  drawBoard();
  drawActivePiece();
  animationFrame = requestAnimationFrame(update);
}

function togglePause() {
  if (gameState === "running") {
    pauseGame();
  } else if (gameState === "paused") {
    resumeGame();
  }
}

function formatLinesCount(value) {
  if (value === 1) return "1 linię";
  const rest = value % 100;
  if (rest >= 12 && rest <= 14) return `${value} linii`;
  const lastDigit = value % 10;
  if (lastDigit >= 2 && lastDigit <= 4) return `${value} linie`;
  return `${value} linii`;
}

function endGame() {
  gameState = "over";
  if (animationFrame) {
    cancelAnimationFrame(animationFrame);
    animationFrame = null;
  }
  activePiece = null;
  drawBoard();
  const message =
    linesCleared === 0
      ? `Koniec gry! Zdobyłeś ${score} pkt. Kliknij komunikat lub naciśnij Enter, aby zagrać ponownie.`
      : `Koniec gry! Zdobyłeś ${score} pkt i usunąłeś ${formatLinesCount(
          linesCleared
        )}. Kliknij komunikat lub naciśnij Enter, aby zagrać ponownie.`;
  showOverlay(message);
  pauseButton.textContent = "Pauza";
  pauseButton.disabled = true;
}

function createPiece() {
  const template = tetrominoes[Math.floor(Math.random() * tetrominoes.length)];
  return {
    ...template,
    rotation: 0,
    x: 3,
    y: -1,
  };
}

function drawBoard() {
  for (let y = 0; y < HEIGHT; y += 1) {
    for (let x = 0; x < WIDTH; x += 1) {
      const cellIndex = y * WIDTH + x;
      const cell = cells[cellIndex];
      const value = grid[y][x];
      if (value) {
        cell.dataset.filled = "true";
        cell.dataset.type = value.type;
        cell.textContent = value.icon;
      } else {
        cell.dataset.filled = "false";
        cell.dataset.type = "";
        cell.textContent = "";
      }
    }
  }
}

function drawActivePiece() {
  if (!activePiece) return;
  eachCell(activePiece, (x, y) => {
    if (y < 0) return;
    const idx = y * WIDTH + x;
    const cell = cells[idx];
    cell.dataset.filled = "true";
    cell.dataset.type = activePiece.type;
    cell.textContent = activePiece.icon;
  });
}

function drawPreview() {
  const previewCells = previewElement.querySelectorAll(".cell");
  previewCells.forEach((cell) => {
    cell.dataset.filled = "false";
    cell.dataset.type = "";
    cell.textContent = "";
  });

  eachCell({ ...nextPiece, x: 0, y: 0 }, (x, y) => {
    const idx = y * 4 + x;
    const cell = previewCells[idx];
    if (!cell) return;
    cell.dataset.filled = "true";
    cell.dataset.type = nextPiece.type;
    cell.textContent = nextPiece.icon;
  });
}

function eachCell(piece, callback) {
  const matrix = piece.rotations[piece.rotation];
  for (let y = 0; y < matrix.length; y += 1) {
    for (let x = 0; x < matrix[y].length; x += 1) {
      if (matrix[y][x]) {
        callback(piece.x + x, piece.y + y);
      }
    }
  }
}

function collide(piece, offsetX = 0, offsetY = 0, rotation = piece.rotation) {
  const matrix = piece.rotations[rotation];
  for (let y = 0; y < matrix.length; y += 1) {
    for (let x = 0; x < matrix[y].length; x += 1) {
      if (!matrix[y][x]) continue;
      const newX = piece.x + x + offsetX;
      const newY = piece.y + y + offsetY;

      if (newX < 0 || newX >= WIDTH) return true;
      if (newY >= HEIGHT) return true;
      if (newY >= 0 && grid[newY][newX]) return true;
    }
  }
  return false;
}

function mergePiece() {
  eachCell(activePiece, (x, y) => {
    if (y < 0) return;
    grid[y][x] = {
      type: activePiece.type,
      icon: activePiece.icon,
    };
  });
}

function rotatePiece() {
  if (gameState !== "running" || !activePiece) return;
  const newRotation = (activePiece.rotation + 1) % activePiece.rotations.length;
  if (!collide(activePiece, 0, 0, newRotation)) {
    activePiece.rotation = newRotation;
    return;
  }

  if (!collide(activePiece, 1, 0, newRotation)) {
    activePiece.x += 1;
    activePiece.rotation = newRotation;
    return;
  }

  if (!collide(activePiece, -1, 0, newRotation)) {
    activePiece.x -= 1;
    activePiece.rotation = newRotation;
  }
}

function clearLines() {
  let lines = 0;
  for (let y = HEIGHT - 1; y >= 0; y -= 1) {
    if (grid[y].every((cell) => cell !== null)) {
      grid.splice(y, 1);
      grid.unshift(Array.from({ length: WIDTH }, () => null));
      lines += 1;
      y += 1;
    }
  }

  if (lines > 0) {
    linesCleared += lines;
    score += Math.floor((lines * 100) * (1 + lines / 4));
    dropInterval = Math.max(
      DROP_INTERVAL_MIN,
      dropInterval - DROP_ACCELERATION * lines
    );
    updateStats();
  }
}

function updateStats() {
  scoreElement.textContent = score.toString();
  linesElement.textContent = linesCleared.toString();
}

function spawnPiece(force = false) {
  activePiece = nextPiece;
  nextPiece = createPiece();
  activePiece.x = 3;
  activePiece.y = -1;
  activePiece.rotation = 0;
  drawPreview();
  if (!force && collide(activePiece)) {
    endGame();
    return;
  }
}

function hardDrop() {
  if (gameState !== "running" || !activePiece) return;
  while (!collide(activePiece, 0, 1)) {
    activePiece.y += 1;
  }
  lockPiece();
}

function lockPiece() {
  mergePiece();
  clearLines();
  spawnPiece();
}

function movePiece(offsetX, offsetY) {
  if (gameState !== "running" || !activePiece) return false;
  if (!collide(activePiece, offsetX, offsetY)) {
    activePiece.x += offsetX;
    activePiece.y += offsetY;
    return true;
  }
  if (offsetY === 1) {
    lockPiece();
  }
  return false;
}

function update(time = 0) {
  if (gameState !== "running") return;
  if (!lastTime) {
    lastTime = time;
  }
  const delta = time - lastTime;
  lastTime = time;
  dropTimer += delta;

  drawBoard();
  drawActivePiece();

  if (dropTimer > dropInterval) {
    movePiece(0, 1);
    dropTimer = 0;
  }

  if (gameState !== "running") {
    return;
  }

  animationFrame = requestAnimationFrame(update);
}

function handleKeydown(event) {
  if (event.key === "p" || event.key === "P" || event.key === "Pause" || event.key === "Escape") {
    event.preventDefault();
    togglePause();
    return;
  }

  if (event.key === "Enter") {
    event.preventDefault();
    if (gameState === "idle" || gameState === "over") {
      startGame();
    } else if (gameState === "paused") {
      resumeGame();
    }
    return;
  }

  if (gameState !== "running" || !activePiece) return;

  switch (event.key) {
    case "ArrowLeft":
      event.preventDefault();
      movePiece(-1, 0);
      break;
    case "ArrowRight":
      event.preventDefault();
      movePiece(1, 0);
      break;
    case "ArrowDown":
      event.preventDefault();
      if (movePiece(0, 1)) {
        score += 1;
        updateStats();
      }
      break;
    case "ArrowUp":
      event.preventDefault();
      rotatePiece();
      break;
    case " ":
      event.preventDefault();
      hardDrop();
      break;
    case "Spacebar":
      event.preventDefault();
      hardDrop();
      break;
    default:
      break;
  }
}

document.addEventListener("keydown", handleKeydown);
overlayElement.addEventListener("click", () => {
  if (gameState === "idle" || gameState === "over") {
    startGame();
  } else if (gameState === "paused") {
    resumeGame();
  }
});

pauseButton.addEventListener("click", () => {
  togglePause();
});

initBoard();
initPreview();
pauseButton.disabled = true;
startGame();
