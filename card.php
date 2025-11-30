<?php
session_start();
require 'db_connect.php';   // must define $conn = new mysqli(...)

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get this user's card
$sql = "SELECT card_number, cardholder_name, valid_until, pin, cvc
        FROM cards
        WHERE user_id = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($card_number, $cardholder_name, $valid_until, $card_pin, $card_cvc);
$stmt->fetch();
$stmt->close();

/* Fallbacks in case no row is found */
if (!$card_number) {
    $card_number      = "0000000000000000";
    $cardholder_name  = "No card";
    $valid_until      = "--/----";
    $card_pin         = "0000";
    $card_cvc         = "000";
}

/* Format numbers for display */
$last4 = substr($card_number, -4);
$maskedCardNumber = substr($card_number, 0, 4) . ' ' .
                    substr($card_number, 4, 4) . ' **** ' .
                    $last4;

$fullCardNumberSpaced = trim(chunk_split($card_number, 4, ' '));
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SejongBank – Virtual Card</title>
  <style>
    /* PAGE BACKGROUND (same feeling as red deposit page) */
    body {
      margin: 0;
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      background: linear-gradient(135deg, #ff6a6a, #ff9e9e, #ffd4d4);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* CENTERED WHITE CARD (form container) */
    .form-card {
      background: #ffffff;
      width: 460px;
      max-width: 94vw;
      border-radius: 18px;
      box-shadow: 0 18px 40px rgba(0,0,0,0.18);
      padding: 28px 32px 30px;
      box-sizing: border-box;
    }

    /* TOP LOGO & TITLE */
    .bank-logo {
      font-size: 24px;
      font-weight: 700;
      text-align: center;
      margin-bottom: 4px;
    }
    .bank-logo span:first-child {
      color: #0056a6;   /* Sejong (blue) */
    }
    .bank-logo span:last-child {
      color: #e63946;   /* Bank (red) */
    }

    .page-title {
      text-align: center;
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 24px;
      color: #333;
    }

    /* VIRTUAL CARD ITSELF */
    .virtual-card {
      position: relative;
      width: 100%;
      height: 190px;
      border-radius: 18px;
      border: 1px solid rgba(0,0,0,0.06);
      background: linear-gradient(135deg, #28d36f, #1aa85a); /* fallback */
      background-size: cover;
      background-position: center;
      box-shadow: 0 14px 26px rgba(0,0,0,0.18);
      padding: 18px 22px;
      box-sizing: border-box;
      overflow: hidden;
      color: #f5f5f5;
    }

    .virtual-card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-weight: 600;
      font-size: 13px;
      opacity: 0.95;
    }

    .virtual-card-chip {
      width: 40px;
      height: 28px;
      border-radius: 8px;
      background: linear-gradient(135deg, #d6d6d6, #f3f3f3);
    }

    .virtual-card-number {
      margin-top: 30px;
      font-size: 20px;
      letter-spacing: 0.16em;
      font-weight: 600;
    }

    .virtual-card-footer {
      margin-top: 20px;
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      font-size: 12px;
    }

    .virtual-card-label {
      opacity: 0.83;
      font-size: 11px;
    }

    .virtual-card-value {
      font-weight: 600;
      margin-top: 4px;
    }

    .virtual-card-brand {
      font-size: 20px;
      font-weight: 700;
    }

    .status-badge {
      position: absolute;
      top: 14px;
      right: 18px;
      padding: 4px 12px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 600;
      background: rgba(0,0,0,0.2);
    }

    .status-frozen {
      background: rgba(0,0,0,0.55);
    }

    /* PIN TEXT */
    .pin-row {
      margin-top: 16px;
      font-size: 14px;
      color: #333;
    }

    /* ACTION BUTTONS ROW */
    .actions-row {
      margin-top: 18px;
      display: flex;
      justify-content: space-between;
      gap: 10px;
    }

    .btn {
      flex: 1;
      padding: 10px 10px;
      border-radius: 999px;
      border: none;
      cursor: pointer;
      font-size: 13px;
      font-weight: 600;
      transition: transform 0.08s, box-shadow 0.12s, background 0.12s;
      white-space: nowrap;
    }

    .btn:active {
      transform: scale(0.97);
      box-shadow: 0 2px 4px rgba(0,0,0,0.12) inset;
    }

    .btn-secondary {
      background: #111827;
      color: #ffffff;
    }

    .btn-danger {
      background: #f94144;
      color: #ffffff;
    }

    /* Simple modal for card details */
    .modal-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.6);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 20;
    }

    .modal {
      width: 320px;
      background: #ffffff;
      border-radius: 12px;
      padding: 16px 18px 14px;
      box-shadow: 0 14px 36px rgba(0,0,0,0.3);
      font-size: 13px;
    }

    .modal-title {
      font-size: 15px;
      font-weight: 600;
      margin-bottom: 10px;
      color: #111827;
    }

    .modal-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 6px;
    }

    .modal-label {
      color: #4b5563;
    }

    .modal-value {
      font-weight: 600;
      color: #111827;
    }

    .modal-footer {
      text-align: right;
      margin-top: 10px;
    }

    .modal-close-btn {
      border: none;
      background: #0056a6;
      color: #ffffff;
      border-radius: 999px;
      padding: 6px 14px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
    }

    .back-btn {
  display: inline-block;
  margin-top: 18px;
  padding: 10px 20px;
  background: #003b73;
  color: #ffffff;
  font-weight: 600;
  font-size: 14px;
  border-radius: 8px;
  text-decoration: none;
  transition: background 0.15s ease;
}

.back-btn:hover {
  background: #002f5c;
}


  </style>
</head>
<body>
  <form class="form-card">
    <div class="bank-logo">
      <span>Sejong</span><span>Bank</span>
    </div>
    <div class="page-title">Virtual Card</div>

    <!-- CARD -->
    <div class="virtual-card" id="virtualCard">
      <div class="virtual-card-header">
        <span>Sejong Bank</span>
        <div class="virtual-card-chip"></div>
      </div>

      <!-- CARD NUMBER FROM DB (masked) -->
      <div class="virtual-card-number" id="cardNumber">
        <?= htmlspecialchars($maskedCardNumber) ?>
      </div>

      <div class="virtual-card-footer">
        <div>
          <div class="virtual-card-label">Cardholder</div>
          <!-- NAME FROM DB -->
          <div class="virtual-card-value">
            <?= htmlspecialchars($cardholder_name) ?>
          </div>
        </div>
        <div>
          <div class="virtual-card-label">Valid thru</div>
          <!-- VALID UNTIL FROM DB -->
          <div class="virtual-card-value">
            <?= htmlspecialchars($valid_until) ?>
          </div>
        </div>
        <div class="virtual-card-brand">VISA</div>
      </div>

      <div class="status-badge" id="statusBadge">ACTIVE</div>
    </div>

    <!-- PIN text (PIN from DB) -->
    <div class="pin-row" id="pinDisplay">PIN: ••••</div>

    <div class="actions-row">
      <button type="button" class="btn btn-secondary" id="showPinBtn">Show PIN</button>
      <button type="button" class="btn btn-secondary" id="cardDetailsBtn">Card details</button>
      <button type="button" class="btn btn-danger" id="freezeBtn">Freeze card</button>
    </div>
    <a href="home.php" class="back-btn">← Back to Home</a>
  </form>

  <!-- MODAL (all from DB) -->
  <div class="modal-backdrop" id="modalBackdrop">
    <div class="modal">
      <div class="modal-title">Card details</div>

      <div class="modal-row">
        <span class="modal-label">Card number</span>
        <span class="modal-value">
          <?= htmlspecialchars($fullCardNumberSpaced) ?>
        </span>
      </div>
      <div class="modal-row">
        <span class="modal-label">Name</span>
        <span class="modal-value">
          <?= htmlspecialchars($cardholder_name) ?>
        </span>
      </div>
      <div class="modal-row">
        <span class="modal-label">Expiry</span>
        <span class="modal-value">
          <?= htmlspecialchars($valid_until) ?>
        </span>
      </div>
      <div class="modal-row">
        <span class="modal-label">CVV</span>
        <span class="modal-value">
          <?= htmlspecialchars($card_cvc) ?>
        </span>
      </div>

      <div class="modal-footer">
        <button type="button" class="modal-close-btn" id="modalCloseBtn">Close</button>
      </div>
    </div>
  </div>

  <script>
    // PIN from PHP/DB
    const PIN = "<?= htmlspecialchars($card_pin, ENT_QUOTES) ?>";

    const pinDisplay   = document.getElementById("pinDisplay");
    const showPinBtn   = document.getElementById("showPinBtn");
    const freezeBtn    = document.getElementById("freezeBtn");
    const statusBadge  = document.getElementById("statusBadge");
    const virtualCard  = document.getElementById("virtualCard");

    const modalBackdrop = document.getElementById("modalBackdrop");
    const cardDetailsBtn = document.getElementById("cardDetailsBtn");
    const modalCloseBtn  = document.getElementById("modalCloseBtn");

    let pinVisible = false;
    let frozen = false;

    // 1. Show / hide PIN
    showPinBtn.addEventListener("click", () => {
      pinVisible = !pinVisible;
      pinDisplay.textContent = pinVisible ? `PIN: ${PIN}` : "PIN: ••••";
      showPinBtn.textContent = pinVisible ? "Hide PIN" : "Show PIN";
    });

    // 2. Card details modal
    cardDetailsBtn.addEventListener("click", () => {
      modalBackdrop.style.display = "flex";
    });

    modalCloseBtn.addEventListener("click", () => {
      modalBackdrop.style.display = "none";
    });

    modalBackdrop.addEventListener("click", (e) => {
      if (e.target === modalBackdrop) {
        modalBackdrop.style.display = "none";
      }
    });

    // 3. Freeze / unfreeze card
    freezeBtn.addEventListener("click", () => {
      frozen = !frozen;

      if (frozen) {
        statusBadge.textContent = "FROZEN";
        statusBadge.classList.add("status-frozen");
        virtualCard.style.filter = "grayscale(0.3) brightness(0.8)";
        freezeBtn.textContent = "Unfreeze card";
      } else {
        statusBadge.textContent = "ACTIVE";
        statusBadge.classList.remove("status-frozen");
        virtualCard.style.filter = "none";
        freezeBtn.textContent = "Freeze card";
      }
    });
  </script>
</body>
</html>
