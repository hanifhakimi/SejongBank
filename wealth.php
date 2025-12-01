<?php
// wealth.php - SejongBank Wealth Dashboard
session_start();

// Mock user data - replace with actual session data
$user_name = $_SESSION['user_name'] ?? 'Abu Bakar';

// API endpoints
$crypto_apis = [
    'bitcoin' => 'https://api.coingecko.com/api/v3/coins/bitcoin/market_chart?vs_currency=myr&days=365',
    'ethereum' => 'https://api.coingecko.com/api/v3/coins/ethereum/market_chart?vs_currency=myr&days=365',
    'ripple' => 'https://api.coingecko.com/api/v3/coins/ripple/market_chart?vs_currency=myr&days=365',
];

$gold_api = 'https://api.metalpriceapi.com/v1/latest?api_key=YOUR_API_KEY&base=MYR&currencies=XAU';

// Function to fetch data with caching
function fetchData($url, $cache_file, $cache_time = 300) {
    if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_time)) {
        return json_decode(file_get_contents($cache_file), true);
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    
    if ($result) {
        file_put_contents($cache_file, $result);
        return json_decode($result, true);
    }
    
    return null;
}

// Process time range filter
$time_range = $_GET['range'] ?? '1y';
$days_map = [
    '1d' => 1,
    '1w' => 7,
    '1m' => 30,
    '6m' => 180,
    '1y' => 365,
    'all' => 'max'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wealth - SejongBank</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #ff8a80 0%, #ff80ab 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header h1 {
            color: #1976d2;
            font-size: 24px;
        }

        .header-nav {
            display: flex;
            gap: 20px;
        }

        .header-nav a {
            color: #333;
            text-decoration: none;
            font-weight: 500;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .wealth-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .asset-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            cursor: pointer;
        }

        .asset-card:hover {
            transform: translateY(-5px);
        }

        .asset-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .asset-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .asset-icon.bitcoin {
            background: #f7931a;
            color: white;
        }

        .asset-icon.ethereum {
            background: #627eea;
            color: white;
        }

        .asset-icon.ripple {
            background: #23292f;
            color: white;
        }

        .asset-info h3 {
            font-size: 16px;
            color: #666;
            margin-bottom: 5px;
        }

        .asset-price {
            font-size: 32px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .asset-change {
            font-size: 16px;
            font-weight: 600;
        }

        .asset-change.positive {
            color: #4caf50;
        }

        .asset-change.negative {
            color: #f44336;
        }

        .chart-container {
            position: relative;
            height: 200px;
            margin-top: 20px;
        }

        .time-range-selector {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            justify-content: center;
        }

        .time-btn {
            padding: 8px 16px;
            border: none;
            background: #f5f5f5;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .time-btn:hover {
            background: #e0e0e0;
        }

        .time-btn.active {
            background: #1976d2;
            color: white;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
        }

        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 20px;
            width: 90%;
            max-width: 900px;
            max-height: 80vh;
            overflow-y: auto;
            animation: slideUp 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .close {
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #999;
        }

        .close:hover {
            color: #333;
        }

        .modal-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .modal-chart {
            height: 400px;
            margin-bottom: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .stat-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
        }

        .stat-box h4 {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .stat-box p {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }

        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }

        @media (max-width: 768px) {
            .wealth-grid {
                grid-template-columns: 1fr;
            }
            
            .modal-content {
                width: 95%;
                margin: 10% auto;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>SejongBank | Wealth</h1>
            <div class="header-nav">
                <a href="accounts.php">ACCOUNTS</a>
                <a href="cards.php">CARDS</a>
                <a href="deposit.php">DEPOSIT</a>
                <a href="withdraw.php">WITHDRAW</a>
                <a href="wealth.php" style="color: #1976d2;">WEALTH</a>
            </div>
        </div>

        <div class="wealth-grid">
            <!-- Bitcoin Card -->
            <div class="asset-card" onclick="openModal('bitcoin', 'Bitcoin', 'BTC')">
                <div class="asset-header">
                    <div class="asset-icon bitcoin">₿</div>
                    <div class="asset-info">
                        <h3>Bitcoin</h3>
                        <small>BTC</small>
                    </div>
                </div>
                <div class="asset-price" id="btc-price">
                    <div class="loading">Loading...</div>
                </div>
                <div class="asset-change" id="btc-change"></div>
                <div class="chart-container">
                    <canvas id="btc-chart"></canvas>
                </div>
            </div>

            <!-- Ethereum Card -->
            <div class="asset-card" onclick="openModal('ethereum', 'Ethereum', 'ETH')">
                <div class="asset-header">
                    <div class="asset-icon ethereum">Ξ</div>
                    <div class="asset-info">
                        <h3>Ethereum</h3>
                        <small>ETH</small>
                    </div>
                </div>
                <div class="asset-price" id="eth-price">
                    <div class="loading">Loading...</div>
                </div>
                <div class="asset-change" id="eth-change"></div>
                <div class="chart-container">
                    <canvas id="eth-chart"></canvas>
                </div>
            </div>

            <!-- Ripple Card -->
            <div class="asset-card" onclick="openModal('ripple', 'Ripple', 'XRP')">
                <div class="asset-header">
                    <div class="asset-icon ripple">✕</div>
                    <div class="asset-info">
                        <h3>Ripple</h3>
                        <small>XRP</small>
                    </div>
                </div>
                <div class="asset-price" id="xrp-price">
                    <div class="loading">Loading...</div>
                </div>
                <div class="asset-change" id="xrp-change"></div>
                <div class="chart-container">
                    <canvas id="xrp-chart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for detailed view -->
    <div id="assetModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div class="modal-header">
                <div class="asset-icon" id="modal-icon"></div>
                <div>
                    <h2 id="modal-title"></h2>
                    <p id="modal-symbol" style="color: #666;"></p>
                </div>
            </div>
            <div class="asset-price" id="modal-price"></div>
            <div class="asset-change" id="modal-change"></div>
            
            <div class="time-range-selector">
                <button class="time-btn" onclick="updateTimeRange('1d')">1D</button>
                <button class="time-btn" onclick="updateTimeRange('1w')">1W</button>
                <button class="time-btn" onclick="updateTimeRange('1m')">1M</button>
                <button class="time-btn" onclick="updateTimeRange('6m')">6M</button>
                <button class="time-btn active" onclick="updateTimeRange('1y')">1Y</button>
                <button class="time-btn" onclick="updateTimeRange('all')">All</button>
            </div>

            <div class="modal-chart">
                <canvas id="modal-chart"></canvas>
            </div>

            <div class="stats-grid">
                <div class="stat-box">
                    <h4>24h High</h4>
                    <p id="stat-high">-</p>
                </div>
                <div class="stat-box">
                    <h4>24h Low</h4>
                    <p id="stat-low">-</p>
                </div>
                <div class="stat-box">
                    <h4>Market Cap</h4>
                    <p id="stat-mcap">-</p>
                </div>
                <div class="stat-box">
                    <h4>Volume</h4>
                    <p id="stat-volume">-</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentAsset = '';
        let currentTimeRange = '1y';
        let modalChart = null;
        let assetCharts = {};
        let assetData = {};

        // Fetch cryptocurrency data with retry
        async function fetchCryptoData(crypto, days = 365) {
            try {
                // Add a small delay to avoid rate limiting
                await new Promise(resolve => setTimeout(resolve, 100));
                
                const response = await fetch(`https://api.coingecko.com/api/v3/coins/${crypto}/market_chart?vs_currency=myr&days=${days}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log(`Fetched ${crypto} data:`, data);
                return data;
            } catch (error) {
                console.error(`Error fetching ${crypto} data:`, error);
                // Return mock data as fallback
                return generateMockData(days);
            }
        }

        // Generate mock data as fallback
        function generateMockData(days) {
            const now = Date.now();
            const prices = [];
            let basePrice = 100000 + Math.random() * 50000;
            
            for (let i = days; i >= 0; i--) {
                const timestamp = now - (i * 24 * 60 * 60 * 1000);
                basePrice += (Math.random() - 0.5) * 5000;
                prices.push([timestamp, Math.max(basePrice, 1000)]);
            }
            
            return { prices };
        }

        // Fetch current price data
        async function fetchCurrentPrice(crypto) {
            try {
                const response = await fetch(`https://api.coingecko.com/api/v3/simple/price?ids=${crypto}&vs_currencies=myr&include_24hr_change=true&include_24hr_vol=true&include_market_cap=true`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log(`Fetched ${crypto} price:`, data);
                return data[crypto];
            } catch (error) {
                console.error(`Error fetching ${crypto} price:`, error);
                // Return mock price data
                return {
                    myr: 100000 + Math.random() * 50000,
                    myr_24h_change: (Math.random() - 0.5) * 10,
                    myr_24h_vol: 1000000000,
                    myr_market_cap: 100000000000
                };
            }
        }

        // Format number to currency
        function formatMYR(num) {
            return 'RM ' + num.toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        // Format large numbers
        function formatLarge(num) {
            if (num >= 1e9) return 'RM ' + (num / 1e9).toFixed(2) + 'B';
            if (num >= 1e6) return 'RM ' + (num / 1e6).toFixed(2) + 'M';
            return formatMYR(num);
        }

        // Create mini chart
        function createMiniChart(canvasId, data) {
            const ctx = document.getElementById(canvasId);
            if (!ctx) {
                console.error(`Canvas ${canvasId} not found`);
                return;
            }
            
            const context = ctx.getContext('2d');
            
            if (assetCharts[canvasId]) {
                assetCharts[canvasId].destroy();
            }

            if (!data || !data.prices || data.prices.length === 0) {
                console.error(`No price data for ${canvasId}`);
                return;
            }

            const dataPoints = Math.min(30, data.prices.length);
            const prices = data.prices.slice(-dataPoints).map(p => p[1]);
            const labels = data.prices.slice(-dataPoints).map(p => new Date(p[0]).toLocaleDateString());

            const isPositive = prices[0] < prices[prices.length - 1];

            assetCharts[canvasId] = new Chart(context, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        data: prices,
                        borderColor: isPositive ? '#4caf50' : '#f44336',
                        backgroundColor: isPositive ? 'rgba(76, 175, 80, 0.1)' : 'rgba(244, 67, 54, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false }
                    },
                    scales: {
                        x: { display: false },
                        y: { display: false }
                    },
                    animation: {
                        duration: 750
                    }
                }
            });
            
            console.log(`Chart created for ${canvasId}`);
        }

        // Initialize asset
        async function initAsset(crypto, symbol, priceId, changeId, chartId) {
            console.log(`Initializing ${crypto}...`);
            
            try {
                const data = await fetchCryptoData(crypto);
                const priceData = await fetchCurrentPrice(crypto);
                
                if (!data || !priceData) {
                    console.error(`Failed to fetch data for ${crypto}`);
                    document.getElementById(priceId).innerHTML = '<div class="loading">Unable to load</div>';
                    return;
                }
                
                assetData[crypto] = { data, priceData };
                
                // Update price
                const priceElement = document.getElementById(priceId);
                if (priceElement) {
                    priceElement.textContent = formatMYR(priceData.myr);
                }
                
                // Update change
                const change = priceData.myr_24h_change || 0;
                const changeAmount = Math.abs(change * priceData.myr / 100);
                const changeEl = document.getElementById(changeId);
                if (changeEl) {
                    changeEl.textContent = `${change > 0 ? '+' : ''}RM ${changeAmount.toFixed(2)} (${change.toFixed(2)}%) over 24h`;
                    changeEl.className = 'asset-change ' + (change >= 0 ? 'positive' : 'negative');
                }
                
                // Create chart
                createMiniChart(chartId, data);
                
                console.log(`${crypto} initialized successfully`);
            } catch (error) {
                console.error(`Error initializing ${crypto}:`, error);
                document.getElementById(priceId).innerHTML = '<div class="loading">Error loading data</div>';
            }
        }

        // Open modal
        function openModal(asset, title, symbol) {
            console.log(`Opening modal for ${asset}`);
            
            currentAsset = asset;
            currentTimeRange = '1y';
            
            // Check if asset data exists
            if (!assetData[asset]) {
                console.error(`No data available for ${asset}`);
                alert('Data not yet loaded. Please wait a moment and try again.');
                return;
            }
            
            const modal = document.getElementById('assetModal');
            modal.style.display = 'block';
            
            // Set modal header
            document.getElementById('modal-title').textContent = title;
            document.getElementById('modal-symbol').textContent = symbol;
            
            // Set icon
            const iconEl = document.getElementById('modal-icon');
            const iconMap = {
                'bitcoin': { class: 'bitcoin', text: '₿' },
                'ethereum': { class: 'ethereum', text: 'Ξ' },
                'ripple': { class: 'ripple', text: '✕' },
                'gold': { class: 'gold', text: '⚜' }
            };
            
            if (iconMap[asset]) {
                iconEl.className = 'asset-icon ' + iconMap[asset].class;
                iconEl.textContent = iconMap[asset].text;
            }
            
            // Reset time range buttons
            document.querySelectorAll('.time-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelectorAll('.time-btn')[4].classList.add('active'); // 1Y button
            
            // Load data
            updateModalData();
        }

        // Close modal
        function closeModal() {
            document.getElementById('assetModal').style.display = 'none';
        }

        // Update time range
        function updateTimeRange(range) {
            console.log(`Changing time range to ${range}`);
            currentTimeRange = range;
            
            // Update active button
            document.querySelectorAll('.time-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Reload chart with new time range
            updateModalData();
        }

        // Update modal data
        async function updateModalData() {
            console.log(`Updating modal for ${currentAsset} with range ${currentTimeRange}`);
            
            const daysMap = { '1d': 1, '1w': 7, '1m': 30, '6m': 180, '1y': 365, 'all': 'max' };
            const days = daysMap[currentTimeRange];
            
            // Show loading state
            const chartContainer = document.querySelector('.modal-chart');
            if (chartContainer) {
                chartContainer.style.opacity = '0.5';
            }
            
            const data = await fetchCryptoData(currentAsset, days);
            
            if (!assetData[currentAsset]) {
                console.error(`No data found for ${currentAsset}`);
                return;
            }
            
            const priceData = assetData[currentAsset].priceData;
            
            if (data && priceData) {
                // Update price
                document.getElementById('modal-price').textContent = formatMYR(priceData.myr);
                
                // Update change
                const change = priceData.myr_24h_change || 0;
                const changeAmount = Math.abs(change * priceData.myr / 100);
                const changeEl = document.getElementById('modal-change');
                changeEl.textContent = `${change > 0 ? '+' : ''}RM ${changeAmount.toFixed(2)} (${change.toFixed(2)}%) over 24h`;
                changeEl.className = 'asset-change ' + (change >= 0 ? 'positive' : 'negative');
                
                // Update stats - calculate from chart data
                const prices = data.prices.map(p => p[1]);
                const high24h = Math.max(...prices.slice(-24));
                const low24h = Math.min(...prices.slice(-24));
                
                document.getElementById('stat-high').textContent = formatMYR(high24h);
                document.getElementById('stat-low').textContent = formatMYR(low24h);
                document.getElementById('stat-mcap').textContent = formatLarge(priceData.myr_market_cap || 0);
                document.getElementById('stat-volume').textContent = formatLarge(priceData.myr_24h_vol || 0);
                
                // Create chart
                createModalChart(data);
                
                // Remove loading state
                if (chartContainer) {
                    chartContainer.style.opacity = '1';
                }
                
                console.log('Modal updated successfully');
            } else {
                console.error('Failed to fetch data for modal');
            }
        }

        // Create modal chart
        function createModalChart(data) {
            const canvas = document.getElementById('modal-chart');
            if (!canvas) {
                console.error('Modal chart canvas not found');
                return;
            }
            
            const ctx = canvas.getContext('2d');
            
            if (modalChart) {
                modalChart.destroy();
                modalChart = null;
            }

            if (!data || !data.prices || data.prices.length === 0) {
                console.error('No price data for modal chart');
                return;
            }

            const prices = data.prices.map(p => p[1]);
            const timestamps = data.prices.map(p => p[0]);

            const isPositive = prices[0] < prices[prices.length - 1];

            modalChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: timestamps,
                    datasets: [{
                        label: 'Price (MYR)',
                        data: prices,
                        borderColor: isPositive ? '#4caf50' : '#f44336',
                        backgroundColor: isPositive ? 'rgba(76, 175, 80, 0.1)' : 'rgba(244, 67, 54, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 6,
                        pointHoverBackgroundColor: isPositive ? '#4caf50' : '#f44336',
                        pointHoverBorderColor: '#fff',
                        pointHoverBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            enabled: true,
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            padding: 12,
                            displayColors: false,
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 16
                            },
                            callbacks: {
                                title: function(context) {
                                    const date = new Date(parseInt(context[0].label));
                                    return date.toLocaleDateString('en-MY', {
                                        year: 'numeric',
                                        month: 'short',
                                        day: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    });
                                },
                                label: function(context) {
                                    return 'RM ' + context.parsed.y.toLocaleString('en-MY', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            type: 'time',
                            time: {
                                unit: currentTimeRange === '1d' ? 'hour' : 
                                      currentTimeRange === '1w' ? 'day' :
                                      currentTimeRange === '1m' ? 'day' : 'month',
                                displayFormats: {
                                    hour: 'HH:mm',
                                    day: 'MMM d',
                                    month: 'MMM yyyy'
                                }
                            },
                            grid: {
                                display: false
                            },
                            ticks: {
                                maxRotation: 0,
                                autoSkipPadding: 20
                            }
                        },
                        y: {
                            ticks: {
                                callback: function(value) {
                                    return 'RM ' + value.toLocaleString('en-MY', {
                                        minimumFractionDigits: 0,
                                        maximumFractionDigits: 0
                                    });
                                }
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            }
                        }
                    }
                }
            });
            
            console.log('Modal chart created successfully');
        }

        // Initialize all assets with staggered loading
        async function initializeAll() {
            console.log('Starting initialization...');
            
            // Load assets one by one to avoid rate limiting
            await initAsset('bitcoin', 'BTC', 'btc-price', 'btc-change', 'btc-chart');
            await new Promise(resolve => setTimeout(resolve, 500));
            
            await initAsset('ethereum', 'ETH', 'eth-price', 'eth-change', 'eth-chart');
            await new Promise(resolve => setTimeout(resolve, 500));
            
            await initAsset('ripple', 'XRP', 'xrp-price', 'xrp-change', 'xrp-chart');
            
            // Initialize gold with mock data
            initGold();
            
            console.log('All assets initialized');
        }

        // Initialize gold with mock/placeholder data
        function initGold() {
            document.getElementById('gold-price').textContent = 'RM 9,245.00';
            const changeEl = document.getElementById('gold-change');
            changeEl.textContent = '+RM 125.50 (+1.38%) over 24h';
            changeEl.className = 'asset-change positive';
            
            // Create mock gold chart
            const mockGoldData = generateMockData(30);
            createMiniChart('gold-chart', mockGoldData);
        }

        // Start initialization when page loads
        window.addEventListener('DOMContentLoaded', () => {
            console.log('DOM loaded, initializing assets...');
            initializeAll();
        });

        // Close modal on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('assetModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>