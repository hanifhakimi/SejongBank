<?php
// currency.php - Multi-Currency Converter with Multi-Input Support

// Get selected currencies from session or use defaults
session_start();
if (!isset($_SESSION['selected_currencies'])) {
    $_SESSION['selected_currencies'] = ['USD', 'KRW', 'JPY'];
}

// Handle add/remove currency
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

// List of all available currencies
$all_currencies = [
    'MYR' => ['name' => 'Malaysian Ringgit', 'symbol' => 'RM', 'flag' => 'flags/MYR.png'],
    'USD' => ['name' => 'US Dollar', 'symbol' => '$', 'flag' => 'flags/USD.png'],
    'EUR' => ['name' => 'Euro', 'symbol' => '€', 'flag' => 'flags/EU.png'],
    'GBP' => ['name' => 'British Pound', 'symbol' => '£', 'flag' => 'flags/GB.png'],
    'JPY' => ['name' => 'Japanese Yen', 'symbol' => '¥', 'flag' => 'flags/JPY.png'],
    'KRW' => ['name' => 'South Korean Won', 'symbol' => '₩', 'flag' => 'flags/KRW.png'],
    'CNY' => ['name' => 'Chinese Yuan', 'symbol' => '¥', 'flag' => 'flags/CN.png'],
    'SGD' => ['name' => 'Singapore Dollar', 'symbol' => 'S$', 'flag' => '🇸🇬'],
    'AUD' => ['name' => 'Australian Dollar', 'symbol' => 'A$', 'flag' => '🇦🇺'],
    'CAD' => ['name' => 'Canadian Dollar', 'symbol' => 'C$', 'flag' => '🇨🇦'],
    'INR' => ['name' => 'Indian Rupee', 'symbol' => '₹', 'flag' => '🇮🇳'],
    'THB' => ['name' => 'Thai Baht', 'symbol' => '฿', 'flag' => '🇹🇭'],
    'CHF' => ['name' => 'Swiss Franc', 'symbol' => 'CHF', 'flag' => '🇨🇭'],
    'HKD' => ['name' => 'Hong Kong Dollar', 'symbol' => 'HK$', 'flag' => '🇭🇰'],
    'NZD' => ['name' => 'New Zealand Dollar', 'symbol' => 'NZ$', 'flag' => '🇳🇿'],
    'SEK' => ['name' => 'Swedish Krona', 'symbol' => 'kr', 'flag' => '🇸🇪'],
    'NOK' => ['name' => 'Norwegian Krone', 'symbol' => 'kr', 'flag' => '🇳🇴'],
    'MXN' => ['name' => 'Mexican Peso', 'symbol' => 'MX$', 'flag' => '🇲🇽'],
    'BRL' => ['name' => 'Brazilian Real', 'symbol' => 'R$', 'flag' => '🇧🇷'],
    'ZAR' => ['name' => 'South African Rand', 'symbol' => 'R', 'flag' => '🇿🇦']
];

// Mock exchange rates (replace with API later)
// All rates are per 1 MYR
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
    'INR' => 19.8765,
    'THB' => 8.4321,
    'CHF' => 0.2134,
    'HKD' => 1.8765,
    'NZD' => 0.3921,
    'SEK' => 2.5432,
    'NOK' => 2.6543,
    'MXN' => 4.8765,
    'BRL' => 1.2345,
    'ZAR' => 4.3210
];

$base_currency = 'MYR';
$base_amount = 0;

// Use timestamp and let JS format it in user's local time
$last_updated_ts = time();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Currency Converter</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(90deg, rgba(255, 94, 98, 1) 0%, rgba(255, 180, 178, 1) 100%);
            padding: 20px;
            color: #000000ff;
        }


        .container {
            max-width: 600px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .last-updated {
            font-size: 14px;
            color: #666;
        }

        /* Base Currency Card */
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
            /* Smooth rectangle corners */
            border: 2px solid #000000ff;
            /* Soft border for visibility */
            background: #fff;
            /* Protect visibility for flags with white edges */
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

        /* Converted Currency Cards */
        .converted-card {
            background: white;
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 15px;
            position: relative;
        }

        .converted-card .flag-icon {
            font-size: 36px;
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

        /* Delete button (style D: hidden until hover) */
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
            /* grey by default */
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            line-height: 1;
            transition: color 0.2s ease, transform 0.2s ease;
        }

        .delete-btn:hover {
            color: #e63946;
            /* red on hover */
            transform: scale(1.15);
        }

        /* Add Currency Button */
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

        .info-icon {
            width: 20px;
            height: 20px;
            border: 2px solid #999;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
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
            font-size: 26px;
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

        .back-home {
            display: inline-block;
            margin-top: 25px;
            background: #004b87;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .back-home:hover {
            background: #00345d;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="last-updated" id="lastUpdated" data-ts="<?php echo $last_updated_ts; ?>">
                <!-- Filled by JS -->
            </div>
        </div>

        <!-- Base Currency Card -->
        <div class="base-card">
            <div class="currency-header">
                <div class="flag-icon">
                    <img src="<?php echo htmlspecialchars($all_currencies[$base_currency]['flag']); ?>"
                        alt="<?php echo $base_currency; ?> flag">
                </div>
                <div class="currency-code"><?php echo $base_currency; ?></div>
            </div>
            <input type="text" class="amount-input currency-input" id="baseAmount"
                data-currency="<?php echo $base_currency; ?>" data-rate="<?php echo $mock_rates[$base_currency]; ?>"
                value="" placeholder="0.00" oninput="handleCurrencyInput(this)">
        </div>

        <!-- Converted Currency Cards -->
        <?php foreach ($_SESSION['selected_currencies'] as $currency): ?>
            <?php if ($currency !== $base_currency && isset($all_currencies[$currency]) && isset($mock_rates[$currency])): ?>
                <?php
                $rate = $mock_rates[$currency];
                $converted = $base_amount * $rate;
                ?>
                <div class="converted-card">
                    <div class="flag-icon">
                        <img src="<?php echo htmlspecialchars($all_currencies[$currency]['flag']); ?>"
                            alt="<?php echo $currency; ?> flag">
                    </div>
                    <div class="currency-code"><?php echo $currency; ?></div>
                    <div class="conversion-info">
                        <input type="text" class="converted-amount currency-input" data-currency="<?php echo $currency; ?>"
                            data-rate="<?php echo $rate; ?>" value="<?php echo number_format($converted, 2); ?>"
                            oninput="handleCurrencyInput(this)">
                        <div class="exchange-rate">
                            1 <?php echo $base_currency; ?> = <?php echo number_format($rate, 4); ?>         <?php echo $currency; ?>
                        </div>
                    </div>

                    <!-- Delete button (hidden until card hover) -->
                    <form method="POST" class="delete-form">
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="currency" value="<?php echo $currency; ?>">
                        <button type="submit" class="delete-btn">✖</button>
                    </form>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <!-- Add Currency Button -->
        <button class="add-currency-btn" onclick="openModal()">
            <span style="font-size: 24px;">+</span>
            <span>Add currency</span>
        </button>

        <a href="home.php" class="back-home">← Back to Home</a>


    </div>

    <!-- Add Currency Modal -->
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
                        <button type="submit" class="currency-option"
                            style="border: none; background: none; width: 100%; text-align: left;">
                            <div class="flag-icon">
                                <img src="<?php echo htmlspecialchars($data['flag']); ?>" alt="<?php echo $code; ?> flag">
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
        // Exchange rates from PHP (1 MYR = rate[currency])
        const rates = <?php echo json_encode($mock_rates); ?>;

        function parseNumber(value) {
            if (!value) return 0;
            value = value.replace(/,/g, '');
            const num = parseFloat(value);
            return isNaN(num) ? 0 : num;
        }

        function handleCurrencyInput(changedInput) {
            const currency = changedInput.dataset.currency;
            const rate = parseFloat(changedInput.dataset.rate);
            const rawValue = parseNumber(changedInput.value);

            if (!currency) return;

            // Step 1: convert edited currency to MYR
            let myrAmount;

            if (currency === 'MYR') {
                myrAmount = rawValue;
            } else {
                if (!rate || isNaN(rate) || rate === 0) return;
                // 1 MYR = rate[currency] -> amount_currency = myrAmount * rate
                // => myrAmount = amount_currency / rate
                myrAmount = rawValue / rate;
            }

            // Step 2: update all currency inputs based on MYR amount
            document.querySelectorAll('.currency-input').forEach(input => {
                const curr = input.dataset.currency;
                const r = parseFloat(input.dataset.rate);

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

        // Close modal when clicking outside
        document.getElementById('currencyModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Format "Last updated" using user's local time
        function formatLastUpdated() {
            const el = document.getElementById('lastUpdated');
            if (!el) return;
            const ts = parseInt(el.dataset.ts, 10);
            if (!ts) return;

            const date = new Date(ts * 1000);

            const options = {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            };

            const formatted = date.toLocaleString(undefined, options);
            el.textContent = 'Last updated: ' + formatted;
        }

        // Initialize values once on load
        window.addEventListener('load', function () {
            const base = document.getElementById('baseAmount');
            if (base) {
                handleCurrencyInput(base);
            }
            formatLastUpdated();
        });
    </script>
</body>

</html>