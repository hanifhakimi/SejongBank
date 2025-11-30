<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Virtual Card</title>
  <style>
    body {
      margin: 0;
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      background: #050608;
      color: #f5f5f5;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }

    .card-screen {
      width: 360px;
      padding: 24px 20px 32px;
      background: #050608;
    }

    .section-title {
      font-size: 24px;
      font-weight: 700;
      margin-bottom: 16px;
    }

    .virtual-card {
      position: relative;
      width: 100%;
      height: 190px;
      border-radius: 18px;
      background: linear-gradient(135deg, #28d36f, #1aa85a);
      box-shadow: 0 18px 30px rgba(0,0,0,0.45);
      padding: 20px 22px;
      box-sizing: border-box;
      overflow: hidden;
    }

    .virtual-card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-weight: 600;
      font-size: 14px;
      opacity: 0.9;
    }

    .virtual-card-chip {
      width: 38px;
      height: 28px;
      border-radius: 6px;
      background: linear-gradient(135deg, #d6d6d6, #f2f2f2);
    }

    .virtual-card-number {
      margin-top: 32px;
      font-size: 20px;
      letter-spacing: 0.12em;
      font-weight: 600;
    }

    .virtual-card-footer {
      margin-top: 22px;
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      font-size: 12px;
    }

    .virtual-card-label {
      opacity: 0.8;
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
      top: 12px;
      right: 16px;
      padding: 4px 10px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 600;
      background: rgba(0,0,0,0.18);
      color: #f5f5f5;
    }

    .status-frozen {
      background: rgba(0,0,0,0.6);
    }

    .actions-row {
      margin-top: 24px;
      display: flex;
      justify-content: space-between;
      gap: 12px;
    }

    .action-button {
      flex: 1;
      padding: 13px 10px;
      border-radius: 999px;
      border: none;
      cursor: pointer;
      font-size: 13px;
      font-weight: 600;
      background: #26d96c;
      color: #050608;
      transition: background 0.15s, transform 0.08s;
      white-space: nowrap;
    }

    .action-button:active {
      transform: scale(0.97);
    }

    .action-secondary {
      background: #111317;
      color: #f5f5f5;
      border: 1px solid #262a33;
    }

    .action-danger {
      background: #ff4b5a;
      color: #f5f5f5;
    }

    /* Simple modal */
    .modal-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.65);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 10;
    }

    .modal {
      width: 320px;
      background: #111317;
      border-radius: 16px;
      padding: 18px 18px 16px;
      box-shadow: 0 12px 30px rgba(0,0,0,0.7);
    }

    .modal-title {
      font-size: 16px;
      font-weight: 600;
      margin-bottom: 10px;
    }

    .modal-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 6px;
      font-size: 13px;
    }

    .modal-label {
      opacity: 0.75;
    }

    .modal-value {
      font-weight: 600;
    }

    .modal-footer {
      text-align: right;
      margin-top: 12px;
    }

    .modal-close-btn {
      border: none;
      background: #26d96c;
      color: #050608;
      border-radius: 999px;
      padding: 6px 16px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
    }

    .pin-text {
      margin-top: 10px;
      font-size: 14px;
      opacity: 0.9;
    }

    .virtual-card {
      position: relative;
        width: 100%;
        height: 190px;
        border-radius: 18px;
        /* fallback gradient if no image set */
        background: linear-gradient(135deg, #28d36f, #1aa85a);
        background-size: cover;
        background-position: center;
        /* rest same as before... */
    }

  </style>
</head>
<body>
  <div class="card-screen">

    <!-- Add this above or below your card -->
    <label style="display:block;margin:12px 0 4px;font-size:13px;opacity:.8;">
        Customize card background
    </label>
    <input type="file" id="bgInput" accept="image/*" style="font-size:13px;">


    <div class="section-title">Cards</div>

    <!-- VIRTUAL CARD -->
    <div class="virtual-card" id="virtualCard">
      <div class="virtual-card-header">
        <span>Sejong Bank</span>
        <div class="virtual-card-chip"></div>
      </div>

      <div class="virtual-card-number" id="cardNumber">
        1234&nbsp;5678&nbsp;****&nbsp;5464
      </div>

      <div class="virtual-card-footer">
        <div>
          <div class="virtual-card-label">Cardholder</div>
          <div class="virtual-card-value">HANIF HAKIMI</div>
        </div>
        <div>
          <div class="virtual-card-label">Valid thru</div>
          <div class="virtual-card-value">08/28</div>
        </div>
        <div class="virtual-card-brand">VISA</div>
      </div>

      <div class="status-badge" id="statusBadge">ACTIVE</div>
    </div>

    <!-- PIN display -->
    <div class="pin-text" id="pinDisplay">PIN: ••••</div>

    <!-- ACTION BUTTONS -->
    <div class="actions-row">
      <button class="action-button action-secondary" id="showPinBtn">Show PIN</button>
      <button class="action-button action-secondary" id="cardDetailsBtn">Card details</button>
      <button class="action-button action-danger" id="freezeBtn">Freeze card</button>
    </div>
  </div>

  <!-- MODAL FOR CARD DETAILS -->
  <div class="modal-backdrop" id="modalBackdrop">
    <div class="modal">
      <div class="modal-title">Card details</div>

      <div class="modal-row">
        <span class="modal-label">Card number</span>
        <span class="modal-value">1234 5678 9012 5464</span>
      </div>
      <div class="modal-row">
        <span class="modal-label">Name</span>
        <span class="modal-value">HANIF HAKIMI</span>
      </div>
      <div class="modal-row">
        <span class="modal-label">Expiry</span>
        <span class="modal-value">08/28</span>
      </div>
      <div class="modal-row">
        <span class="modal-label">CVV</span>
        <span class="modal-value">123</span>
      </div>

      <div class="modal-footer">
        <button class="modal-close-btn" id="modalCloseBtn">Close</button>
      </div>
    </div>
  </div>

  <script>
    // Fake data (normally from backend)
    const PIN = "4829";

    const pinDisplay = document.getElementById("pinDisplay");
    const showPinBtn  = document.getElementById("showPinBtn");
    const freezeBtn   = document.getElementById("freezeBtn");
    const statusBadge = document.getElementById("statusBadge");
    const virtualCard = document.getElementById("virtualCard");

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

    // 2. Card details (simple modal)
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

    const bgInput = document.getElementById("bgInput");
const card = document.getElementById("virtualCard");

bgInput.addEventListener("change", function () {
  const file = this.files[0];
  if (!file) return;

  // Optional: basic size limit (e.g. 2 MB)
  if (file.size > 2 * 1024 * 1024) {
    alert("Image too large. Please choose a file under 2 MB.");
    this.value = "";
    return;
  }

  const reader = new FileReader();
  reader.onload = function (e) {
    card.style.backgroundImage = `url('${e.target.result}')`;
  };
  reader.readAsDataURL(file);
});

  </script>
</body>
</html>
