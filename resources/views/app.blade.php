<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />

    <!-- Prevent mobile scaling -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

   <link rel="shortcut icon" href="{{ asset('logo.png') }}">

    <title>NEST PORTAL</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer" />

    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            font-family: Arial, sans-serif;
        }

        /* Main App */
        #desktop-app {
            width: 100%;
            height: 100%;
        }

        /* Block screen for mobile/tablet */
        #device-blocker {
            display: none;
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100vh;
            background: #0f172a;
            color: white;
            z-index: 999999;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 24px;
            box-sizing: border-box;
        }

        .device-content {
            max-width: 500px;
        }

        .device-content i {
            font-size: 70px;
            margin-bottom: 20px;
            color: #60a5fa;
        }

        .device-content h1 {
            font-size: 30px;
            margin-bottom: 12px;
        }

        .device-content p {
            font-size: 16px;
            line-height: 1.7;
            color: #cbd5e1;
        }

        /* Show blocker on tablet/mobile */
        @media (max-width: 1024px) {
            #device-blocker {
                display: flex;
            }

            #desktop-app {
                display: none;
            }

            body {
                overflow: hidden;
            }
        }
    </style>
</head>

<body>

    <!-- Desktop App -->
    <div id="desktop-app">
        @inertia
    </div>

    <!-- Mobile/Tablet Blocker -->
    <div id="device-blocker">
        <div class="device-content">
            <i class="fas fa-laptop"></i>

            <h1>Desktop or Laptop Required</h1>

            <p>
                NEST PORTAL is designed for desktop and laptop screens only.
                Please access the system using a desktop or laptop for the best experience.
            </p>
        </div>
    </div>

</body>
</html>
