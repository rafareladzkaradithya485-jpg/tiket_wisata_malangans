<?php
/**
 * API Testing Tool
 * Memudahkan testing semua endpoint API
 */

session_start();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🧪 API Testing Tool</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .header h1 {
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .test-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .test-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .test-card h3 {
            color: #667eea;
            margin-bottom: 15px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        
        .endpoint-list {
            list-style: none;
        }
        
        .endpoint-list li {
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .endpoint-list li:last-child {
            border-bottom: none;
        }
        
        .endpoint-list button {
            background: #667eea;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            width: 100%;
            text-align: left;
            transition: background 0.3s ease;
        }
        
        .endpoint-list button:hover {
            background: #764ba2;
        }
        
        .response-box {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            border-left: 4px solid #667eea;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            max-height: 300px;
            overflow-y: auto;
            display: none;
        }
        
        .response-box.show {
            display: block;
        }
        
        .response-box.success {
            border-left-color: #4caf50;
            background: #f1f8f4;
        }
        
        .response-box.error {
            border-left-color: #f44336;
            background: #ffebee;
        }
        
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            margin-left: 10px;
        }
        
        .status-badge.get {
            background: #2196f3;
            color: white;
        }
        
        .status-badge.post {
            background: #4caf50;
            color: white;
        }
        
        .status-badge.put {
            background: #ff9800;
            color: white;
        }
        
        .status-badge.delete {
            background: #f44336;
            color: white;
        }
        
        .method-label {
            display: inline-block;
            width: 40px;
            font-size: 11px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 API Testing Tool</h1>
            <p>Test semua endpoint aplikasi Tiket Wisata Malang</p>
        </div>
        
        <div class="test-grid">
            <!-- Wisata API -->
            <div class="test-card">
                <h3>🏖️ Wisata API</h3>
                <ul class="endpoint-list">
                    <li>
                        <button onclick="testAPI('wisata_info.php?action=all')">
                            <span class="method-label"><span class="status-badge get">GET</span></span>
                            Get All Wisata
                        </button>
                    </li>
                    <li>
                        <button onclick="testAPI('wisata_info.php?action=detail&id=1')">
                            <span class="method-label"><span class="status-badge get">GET</span></span>
                            Get Wisata Detail (ID=1)
                        </button>
                    </li>
                    <li>
                        <button onclick="testAPI('wisata_info.php?action=search&keyword=jatim')">
                            <span class="method-label"><span class="status-badge get">GET</span></span>
                            Search Wisata
                        </button>
                    </li>
                    <li>
                        <button onclick="testAPI('wisata_info.php?action=filter&category=alam')">
                            <span class="method-label"><span class="status-badge get">GET</span></span>
                            Filter by Category
                        </button>
                    </li>
                </ul>
            </div>
            
            <!-- Analytics API -->
            <div class="test-card">
                <h3>📊 Analytics API</h3>
                <ul class="endpoint-list">
                    <li>
                        <button onclick="testAPI('analytics_ai.php?action=stats')">
                            <span class="method-label"><span class="status-badge get">GET</span></span>
                            Get Statistics
                        </button>
                    </li>
                    <li>
                        <button onclick="testAPI('analytics_ai.php?action=generate')">
                            <span class="method-label"><span class="status-badge post">POST</span></span>
                            Generate Daily Analytics
                        </button>
                    </li>
                    <li>
                        <button onclick="testAPI('analytics_ai.php?action=forecast')">
                            <span class="method-label"><span class="status-badge get">GET</span></span>
                            Revenue Forecast
                        </button>
                    </li>
                    <li>
                        <button onclick="testAPI('analytics_ai.php?action=segmentation')">
                            <span class="method-label"><span class="status-badge get">GET</span></span>
                            Customer Segmentation
                        </button>
                    </li>
                    <li>
                        <button onclick="testAPI('analytics_ai.php?action=churn')">
                            <span class="method-label"><span class="status-badge get">GET</span></span>
                            Churn Prediction
                        </button>
                    </li>
                </ul>
            </div>
            
            <!-- Payment API -->
            <div class="test-card">
                <h3>💳 Payment API</h3>
                <ul class="endpoint-list">
                    <li>
                        <button onclick="testAPI('payment_gateway.php?action=all')">
                            <span class="method-label"><span class="status-badge get">GET</span></span>
                            Get All Payments
                        </button>
                    </li>
                    <li>
                        <button onclick="testAPI('payment_gateway.php?action=history')">
                            <span class="method-label"><span class="status-badge get">GET</span></span>
                            Payment History
                        </button>
                    </li>
                    <li>
                        <button onclick="testAPI('payment_gateway.php?action=pending')">
                            <span class="method-label"><span class="status-badge get">GET</span></span>
                            Pending Payments
                        </button>
                    </li>
                </ul>
            </div>
            
            <!-- System Test -->
            <div class="test-card">
                <h3>⚙️ System Test</h3>
                <ul class="endpoint-list">
                    <li>
                        <button onclick="testAPI('config.php')">
                            <span class="method-label"><span class="status-badge get">GET</span></span>
                            Config File Access
                        </button>
                    </li>
                    <li>
                        <button onclick="testAPI('diagnose.php')">
                            <span class="method-label"><span class="status-badge get">GET</span></span>
                            System Diagnostics
                        </button>
                    </li>
                    <li>
                        <button onclick="testAPI('index.php')">
                            <span class="method-label"><span class="status-badge get">GET</span></span>
                            Homepage
                        </button>
                    </li>
                    <li>
                        <button onclick="testAPI('home.php')">
                            <span class="method-label"><span class="status-badge get">GET</span></span>
                            Home Page
                        </button>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Response Display -->
        <div id="responseContainer" class="response-box">
            <div id="response"></div>
        </div>
    </div>
    
    <script>
        function testAPI(endpoint) {
            const container = document.getElementById('responseContainer');
            const response = document.getElementById('response');
            
            container.classList.remove('show', 'success', 'error');
            response.textContent = 'Loading...';
            
            fetch(endpoint)
                .then(res => res.text())
                .then(data => {
                    // Try to parse as JSON
                    try {
                        const json = JSON.parse(data);
                        response.textContent = JSON.stringify(json, null, 2);
                        container.classList.add('show', 'success');
                    } catch {
                        // If not JSON, display as HTML/text
                        response.textContent = data.substring(0, 1000) + (data.length > 1000 ? '\n... (truncated)' : '');
                        container.classList.add('show', 'success');
                    }
                })
                .catch(error => {
                    response.textContent = 'ERROR: ' + error.message;
                    container.classList.add('show', 'error');
                });
        }
    </script>
</body>
</html>
