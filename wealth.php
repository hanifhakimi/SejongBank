<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wealth</title>
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

        .asset-price {
            font-size: 32px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .api-error-msg {
            text-align: center;
            color: #c62828;
            font-size: 14px;
            padding: 20px 0;
        }

        .back-home-wrapper {
            margin-top: 30px;
        }

        .back-home-btn {
            display: inline-block;
            padding: 10px 24px;
            background: #0056b3;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }

        .back-home-btn:hover {
            background: #004494;
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

        <div class="wealth-grid">
            <div class="asset-card" onclick="openModal('bitcoin', 'Bitcoin', 'BTC')">
                <div class="asset-header">
                    <div class="asset-icon bitcoin">₿</div>
                    <div class="asset-info">
                        <h3>Bitcoin</h3>
                        <small>BTC</small>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="btc-chart"></canvas>
                </div>
            </div>

            <div class="asset-card" onclick="openModal('ethereum', 'Ethereum', 'ETH')">
                <div class="asset-header">
                    <div class="asset-icon ethereum">Ξ</div>
                    <div class="asset-info">
                        <h3>Ethereum</h3>
                        <small>ETH</small>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="eth-chart"></canvas>
                </div>
            </div>

            <div class="asset-card" onclick="openModal('ripple', 'Ripple', 'XRP')">
                <div class="asset-header">
                    <div class="asset-icon ripple">✕</div>
                    <div class="asset-info">
                        <h3>Ripple</h3>
                        <small>XRP</small>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="xrp-chart"></canvas>
                </div>
            </div>
        </div>

        <div class="back-home-wrapper">
            <a href="home.php" class="back-home-btn">← Back to Home</a>
        </div>
    </div>

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
        </div>
    </div>

    <script>
        let currentAsset = '';
        let currentTimeRange = '1y';
        let modalChart = null;
        let assetCharts = {};
        let assetData = {};

        async function fetchCryptoData(crypto, days = 365) {
            try {
                await new Promise(resolve => setTimeout(resolve, 100));
                
                const response = await fetch(
                    `https://api.coingecko.com/api/v3/coins/${crypto}/market_chart?vs_currency=myr&days=${days}`,
                    {
                        method: 'GET',
                        headers: { 'Accept': 'application/json' }
                    }
                );
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                return data;
            } catch (error) {
                console.error(`Error fetching ${crypto} data:`, error);
                return null;
            }
        }

        function formatMYR(num) {
            return 'RM ' + num.toLocaleString('en-MY', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function createMiniChart(canvasId, data) {
            const ctx = document.getElementById(canvasId);
            if (!ctx) return;
            
            const context = ctx.getContext('2d');
            
            if (assetCharts[canvasId]) {
                assetCharts[canvasId].destroy();
            }

            if (!data || !data.prices || data.prices.length === 0) return;

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
                    }
                }
            });
        }

        async function initAsset(crypto, chartId) {
            try {
                const data = await fetchCryptoData(crypto);
                if (!data || !data.prices || data.prices.length === 0) {
                    const canvas = document.getElementById(chartId);
                    if (canvas && canvas.parentElement) {
                        canvas.parentElement.innerHTML =
                            '<p class="api-error-msg">Unable to load data (API error)</p>';
                    }
                    return;
                }

                assetData[crypto] = true;
                createMiniChart(chartId, data);
            } catch (error) {
                console.error(`Error initializing ${crypto}:`, error);
            }
        }

        function openModal(asset, title, symbol) {
            currentAsset = asset;
            currentTimeRange = '1y';
            
            if (!assetData[asset]) {
                alert('Data not yet loaded or API error. Please try again later.');
                return;
            }
            
            const modal = document.getElementById('assetModal');
            modal.style.display = 'block';
            
            document.getElementById('modal-title').textContent = title;
            document.getElementById('modal-symbol').textContent = symbol;
            
            const iconEl = document.getElementById('modal-icon');
            const iconMap = {
                'bitcoin': { class: 'bitcoin', text: '₿' },
                'ethereum': { class: 'ethereum', text: 'Ξ' },
                'ripple': { class: 'ripple', text: '✕' }
            };
            
            if (iconMap[asset]) {
                iconEl.className = 'asset-icon ' + iconMap[asset].class;
                iconEl.textContent = iconMap[asset].text;
            }
            
            document.querySelectorAll('.time-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelectorAll('.time-btn')[4].classList.add('active'); // 1Y
            
            updateModalData();
        }

        function closeModal() {
            document.getElementById('assetModal').style.display = 'none';
        }

        function updateTimeRange(range) {
            currentTimeRange = range;
            
            document.querySelectorAll('.time-btn').forEach(btn => { btn.classList.remove('active');});
            event.target.classList.add('active');
            
            updateModalData();
        }

        async function updateModalData() {
            const daysMap = { '1d': 1, '1w': 7, '1m': 30, '6m': 180, '1y': 365, 'all': 'max' };
            const days = daysMap[currentTimeRange];
            
            const chartContainer = document.querySelector('.modal-chart');
            if (chartContainer) chartContainer.style.opacity = '0.5';
            
            const data = await fetchCryptoData(currentAsset, days);
            if (!data || !data.prices || data.prices.length === 0) {
                document.getElementById('modal-price').textContent =
                    'Data unavailable (API error)';
                if (modalChart) {
                    modalChart.destroy();
                    modalChart = null;
                }
                if (chartContainer) chartContainer.style.opacity = '1';
                return;
            }

            const lastPrice = data.prices[data.prices.length - 1][1];
            document.getElementById('modal-price').textContent = formatMYR(lastPrice);

            createModalChart(data);
            if (chartContainer) chartContainer.style.opacity = '1';
        }

        function createModalChart(data) {
            const canvas = document.getElementById('modal-chart');
            if (!canvas) return;
            
            const ctx = canvas.getContext('2d');
            
            if (modalChart) {
                modalChart.destroy();
                modalChart = null;
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
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 16 },
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
                            grid: { display: false },
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
                            grid: { color: 'rgba(0,0,0,0.05)' }
                        }
                    }
                }
            });
        }

        async function initializeAll() {
            await initAsset('bitcoin', 'btc-chart');
            await new Promise(resolve => setTimeout(resolve, 500));
            
            await initAsset('ethereum', 'eth-chart');
            await new Promise(resolve => setTimeout(resolve, 500));
            
            await initAsset('ripple', 'xrp-chart');
        }

        window.addEventListener('DOMContentLoaded', () => {
            initializeAll();
        });

        window.onclick = function(event) {
            const modal = document.getElementById('assetModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
