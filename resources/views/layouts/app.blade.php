<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="sanctum-token" content="{{ auth()->user() ? auth()->user()->createToken('web')->plainTextToken : '' }}">
    <title>MediSync AI - @yield('title')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon-3.ico') }}">
    <style>
        :root {
            --primary: #1e88e5;
            --primary-dark: #1565c0;
            --accent: #26a69a;
            --accent-dark: #00897b;
            --background: #f5f8fb;
            --card-bg: #ffffff;
            --text: #263238;
            --text-light: #546e7a;
            --error: #ef5350;
            --success: #4caf50;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: var(--background);
            color: var(--text);
            line-height: 1.6;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header img.logo {
            width: 120px;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 2rem;
            color: var(--text);
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--text-light);
            margin-bottom: 8px;
        }
        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #cfd8dc;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.1);
        }
        button {
            background: var(--primary);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
            position: relative;
            overflow: hidden;
        }
        button:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        button:disabled {
            background: #b0bec5;
            cursor: not-allowed;
        }
        .confirm-btn {
            background: var(--accent);
        }
        .confirm-btn:hover {
            background: var(--accent-dark);
        }
        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 0.95rem;
            opacity: 0;
            transform: translateY(-10px);
            animation: slideIn 0.3s forwards;
        }
        .error {
            background: var(--error);
            color: white;
        }
        .success {
            background: var(--success);
            color: white;
        }
        @keyframes slideIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .medicine-details {
            margin-top: 30px;
            padding: 20px;
            border: 1px solid #eceff1;
            border-radius: 12px;
            background: var(--card-bg);
            display: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .medicine-details h2 {
            font-size: 1.5rem;
            color: var(--text);
            margin-bottom: 15px;
        }
        .medicine-details p {
            margin-bottom: 10px;
        }
        .medicine-details ul {
            list-style: none;
            padding-left: 10px;
        }
        .medicine-details li {
            margin-bottom: 8px;
            position: relative;
            padding-left: 15px;
        }
        .medicine-details li::before {
            content: '•';
            color: var(--primary);
            position: absolute;
            left: 0;
        }
        .medicine-details strong {
            color: var(--text);
            font-weight: 600;
        }
        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            border: 2px solid white;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            transform: translate(-50%, -50%);
        }
        @keyframes spin {
            to { transform: translate(-50%, -50%) rotate(360deg); }
        }
        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }
            .header h1 {
                font-size: 1.5rem;
            }
            button {
                width: 100%;
                padding: 12px;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                <img src="{{ asset('logo.jpg') }}" alt="MediSin AI Logo" class="logo">
                <span style="font-size: 1.5rem; font-weight: 600; color: var(--primary); letter-spacing: 1px;">MediSin.ai</span>
            </div>
            <h1>@yield('title')</h1>
        </div>

        <div id="success" class="message success"></div>
        <div id="error" class="message error"></div>

        @yield('content')

    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const sanctumToken = document.querySelector('meta[name="sanctum-token"]')?.content;

        // Reset UI
        function resetUI() {
            const successMsg = document.getElementById('success');
            const errorMsg = document.getElementById('error');
            successMsg.style.display = 'none';
            errorMsg.style.display = 'none';
            successMsg.textContent = '';
            errorMsg.textContent = '';
        }

        // Show message
        function showMessage(type, message) {
            const msgElement = type === 'success' ? document.getElementById('success') : document.getElementById('error');
            msgElement.textContent = message;
            msgElement.style.display = 'block';
            setTimeout(() => {
                msgElement.style.display = 'none';
                msgElement.textContent = '';
            }, 5000);
        }

        </script>
        @yield('scripts')
</body>
</html>