<?php

session_start();

if (!isset($_SESSION['selected_currencies'])) {
    $_SESSION['selected_currencies'] = ['USD', 'KRW', 'JPY'];
}

if (isset($_POST['action'])) {
    if ($_POST['action'] === 'add' && isset($_POST['currency'])) {
        $new_currency = $_POST['currency'];

        if (!in_array($new_currency, $_SESSION['selected_currencies'])) {
            $_SESSION['selected_currencies'][] = $new_currency;
        }
    } elseif ($_POST['action'] === 'remove' && isset($_POST['currency'])) {
        $remove_currency = $_POST['currency'];
        $_SESSION['selected_currencies'] = array_values(array_diff($_SESSION['selected_currencies'], [$remove_currency]));
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$all_currencies = [
    'MYR' => ['name' => 'Malaysian Ringgit', 'symbol' => 'RM', 'flag' => 'flags/MYR.png'],
    'USD' => ['name' => 'US Dollar', 'symbol' => '$', 'flag' => 'flags/USD.png'],
    'EUR' => ['name' => 'Euro', 'symbol' => '€', 'flag' => 'flags/EU.png'],
    'GBP' => ['name' => 'British Pound', 'symbol' => '£', 'flag' => 'flags/GB.png'],
    'JPY' => ['name' => 'Japanese Yen', 'symbol' => '¥', 'flag' => 'flags/JPY.png'],
    'KRW' => ['name' => 'South Korean Won', 'symbol' => '₩', 'flag' => 'flags/KRW.png'],
    'CNY' => ['name' => 'Chinese Yuan', 'symbol' => '¥', 'flag' => 'flags/CN.png'],
    'AUD' => ['name' => 'Australian Dollar', 'symbol' => 'A$', 'flag' => 'flags/AUD.png'],
    'CAD' => ['name' => 'Canadian Dollar', 'symbol' => 'C$', 'flag' => 'flags/CAD.png'],
    'CHF' => ['name' => 'Swiss Franc', 'symbol' => 'CHF', 'flag' => 'flags/CHF.png'],
    'HKD' => ['name' => 'Hong Kong Dollar', 'symbol' => 'HK$', 'flag' => 'flags/HKD.png']
];

$mock_rates = [
    'MYR' => 1.0,
    'USD' => 0.2415,
    'EUR' => 0.2201,
    'GBP' => 0.1895,
    'JPY' => 37.8632,
    'KRW' => 356.7085,
    'CNY' => 1.7234,
    'SGD' => 0.3214,
    'AUD' => 0.3654,
    'CAD' => 0.3287,
    'CHF' => 0.2134,
    'HKD' => 1.8765
];

$base_currency = 'MYR';
$base_amount   = 0;
$exchange_rates = $mock_rates;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Currency Converter</title>
    <style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Roboto', sans-serif;
    background: linear-gradient(90deg, rgba(255,94,98,1) 0%, rgba(255,180,178,1) 100%);
    padding: 20px;
    color: #000000ff;
}

.container {
    max-width: 600px;
    margin: 0 auto;
}

.top-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
}

.home-button {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 24px;
    border-radius: 6px;
    background: #0056a6;
    color: #ffffff;
    font-size: 16px;
    font-weight: 500;
    text-decoration: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.15);
    transition: background 0.2s ease;
}
.home-button:hover {
    background: #003f7f;
}

.base-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(33, 150, 243, 0.2);
    border: 2px solid #2196F3;
}

.currency-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.flag-icon img {
    width: 42px;
    height: 28px;
    object-fit: cover;
    border-radius: 6px;
    border: 2px solid #000000ff;
    background: #fff;
    display: block;
}

.currency-code {
    font-size: 24px;
    font-weight: 500;
    color: #333;
}

.amount-input {
    width: 100%;
    border: none;
    font-size: 42px;
    font-weight: 700;
    color: #333;
    text-align: right;
    outline: none;
    background: transparent;
}

.converted-card {
    background: white;
    border-radius: 12px;
    padding: 20px 24px;
    margin-bottom: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 15px;
    position: relative;
}

.conversion-info {
    flex: 1;
    text-align: right;
}

.converted-amount {
    font-size: 32px;
    font-weight: 700;
    color: #333;
    margin-bottom: 4px;
    border: none;
    background: transparent;
    width: 100%;
    text-align: right;
    outline: none;
}

.exchange-rate {
    font-size: 13px;
    color: #999;
}

.delete-form {
    margin-left: 8px;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s ease;
}

.converted-card:hover .delete-form {
    opacity: 1;
    pointer-events: auto;
}

.delete-btn {
    font-size: 20px;
    color: #888;
    background: none;
    border: none;
    cursor: pointer;
    padding: 5px;
    line-height: 1;
    transition: color 0.2s ease, transform 0.2s ease;
}

.delete-btn:hover {
    color: #e63946;
    transform: scale(1.15);
}

.add-currency-btn {
    background: white;
    border: 2px dashed #2196F3;
    border-radius: 12px;
    padding: 20px;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    font-size: 16px;
    font-weight: 500;
    color: #2196F3;
    cursor: pointer;
    transition: all 0.3s;
    margin-bottom: 20px;
}

.add-currency-btn:hover {
    background: #e3f2fd;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 12px;
    padding: 24px;
    max-width: 400px;
    width: 100%;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.close-btn {
    font-size: 28px;
    cursor: pointer;
    color: #999;
    line-height: 1;
}

.currency-option {
    padding: 15px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 12px;
}

.currency-option:hover {
    background: #f5f5f5;
}

.currency-option .flag-icon {
    width: 36px;
    height: 36px;
}

.currency-option-text {
    flex: 1;
}

.currency-option-code {
    font-weight: 700;
    font-size: 16px;
}

.currency-option-name {
    font-size: 13px;
    color: #999;
}

    </style>
</head>
<body>
    <div class="container">

        <div class="base-card">
            <div class="currency-header">
                <div class="flag-icon">
                    <img src="<?php echo htmlspecialchars($all_currencies[$base_currency]['flag']); ?>"
                         alt="<?php echo $base_currency; ?> flag">
                </div>
                <div class="currency-code"><?php echo $base_currency; ?></div>
            </div>

            <input 
                type="text" 
                class="amount-input currency-input" 
                id="baseAmount"
                data-currency="<?php echo $base_currency; ?>"
                data-rate="<?php echo $exchange_rates[$base_currency]; ?>"
                value=""
                placeholder="0.00"
                oninput="handleCurrencyInput(this)"
            >
        </div>

        <?php foreach ($_SESSION['selected_currencies'] as $currency): ?>
            <?php if ($currency !== $base_currency && isset($all_currencies[$currency]) && isset($exchange_rates[$currency])): ?>
                <?php 
                    $rate = $exchange_rates[$currency];
                    $converted = $base_amount * $rate;
                ?>
                <div class="converted-card">

                    <div class="flag-icon">
                        <img src="<?php echo htmlspecialchars($all_currencies[$currency]['flag']); ?>" 
                            alt="<?php echo $currency; ?> flag">
                    </div>

                    <div class="currency-code"><?php echo $currency; ?></div>

                    <div class="conversion-info">
                        <input 
                            type="text"
                            class="converted-amount currency-input"
                            data-currency="<?php echo $currency; ?>" 
                            data-rate="<?php echo $rate; ?>"
                            value="<?php echo number_format($converted, 2); ?>"
                            oninput="handleCurrencyInput(this)"
                        >
                        <div class="exchange-rate">
                            1 <?php echo $base_currency; ?> = <?php echo number_format($rate, 4); ?> <?php echo $currency; ?>
                        </div>
                    </div>

                    <form method="POST" class="delete-form">
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="currency" value="<?php echo $currency; ?>">
                        <button type="submit" class="delete-btn">✖</button>
                    </form>

                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <button class="add-currency-btn" onclick="openModal()">
            <span style="font-size: 24px;">+</span>
            <span>Add currency</span>
        </button>

        <a href="home.php" class="home-button">← Back to Home</a>

    </div>

    <div class="modal" id="currencyModal">
        <div class="modal-content">
            <div class="modal-header">
                <span>Select Currency</span>
                <span class="close-btn" onclick="closeModal()">×</span>
            </div>

            <?php foreach ($all_currencies as $code => $data): ?>
                <?php if ($code !== $base_currency && !in_array($code, $_SESSION['selected_currencies'])): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="currency" value="<?php echo $code; ?>">

                        <button type="submit" class="currency-option" style="border:none;background:none;width:100%;text-align:left;">
                            <div class="flag-icon">
                                <img src="<?php echo htmlspecialchars($data['flag']); ?>" 
                                    alt="<?php echo $code; ?> flag">
                            </div>

                            <div class="currency-option-text">
                                <div class="currency-option-code"><?php echo $code; ?></div>
                                <div class="currency-option-name"><?php echo $data['name']; ?></div>
                            </div>
                        </button>
                    </form>
                <?php endif; ?>
            <?php endforeach; ?>

        </div>
    </div>

    <script>
        const rates = <?php echo json_encode($exchange_rates); ?>;

        function parseNumber(value) {
            if (!value) return 0;
            value = value.replace(/,/g, '');
            const num = parseFloat(value);
            return isNaN(num) ? 0 : num;
        }

        function handleCurrencyInput(changedInput) {
            const currency = changedInput.dataset.currency;
            const rate     = parseFloat(changedInput.dataset.rate);
            const rawValue = parseNumber(changedInput.value);

            if (!currency) return;

            let myrAmount;

            if (currency === 'MYR') {
                myrAmount = rawValue;
            } else {
                if (!rate || isNaN(rate) || rate === 0) return;
                myrAmount = rawValue / rate;
            }

            document.querySelectorAll('.currency-input').forEach(input => {
                const curr = input.dataset.currency;
                const r    = parseFloat(input.dataset.rate);

                let amount;
                if (curr === 'MYR') {
                    amount = myrAmount;
                } else {
                    amount = myrAmount * r;
                }

                if (input !== changedInput) {
                    input.value = amount.toFixed(2);
                }
            });
        }

        function openModal() {
            document.getElementById('currencyModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('currencyModal').classList.remove('active');
        }

        document.getElementById('currencyModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });

        window.onload = function() {
            const base = document.getElementById('baseAmount');
            if (base) handleCurrencyInput(base);
        };
    </script>
</body>
</html>
