<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Mas API Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-50 via-white to-blue-50 min-h-screen flex items-center justify-center">
  <div class=" p-8 max-w-2xl w-full">
    <div class="text-center mb-6">
      <h1 class="text-3xl font-extrabold text-blue-600 mb-2">🚀 Mas API Server Dashboard</h1>
      <p class="text-gray-500">Welcome to the Mas API server monitoring panel.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Server Status Card -->
      <div class="bg-green-100 border-l-4 border-green-500 p-5 rounded shadow">
        <div class="flex items-center">
          <div class="text-green-600 mr-3">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M5 13l4 4L19 7"/>
            </svg>
          </div>
          <div>
            <h2 class="text-xl font-bold">Server Status</h2>
            <p class="text-green-700">Running successfully ✅</p>
          </div>
        </div>
      </div>

      <!-- Database Info Card -->
      <div class="bg-blue-100 border-l-4 border-blue-500 p-5 rounded shadow">
        <div class="flex items-center">
          <div class="text-blue-600 mr-3">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 10h18M3 6h18M3 14h18M3 18h18"/>
            </svg>
          </div>
          <div>
            <h2 class="text-xl font-bold">Database</h2>
            <p class="text-blue-700">Connected to MySQL</p>
          </div>
        </div>
      </div>

      <!-- Uptime Card -->
      <div class="bg-yellow-100 border-l-4 border-yellow-500 p-5 rounded shadow col-span-1 md:col-span-2">
        <div class="flex justify-between items-center">
          <div>
            <h2 class="text-xl font-bold text-yellow-700">Uptime</h2>
            <p class="text-yellow-800" id="uptime">Calculating...</p>
          </div>
          <div class="text-sm text-gray-500 text-right">
            <p>Server Time:</p>
            <p id="server-time"></p>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-8 text-center text-gray-400 text-sm">
      &copy; 2025 Mas API. All rights reserved.
    </div>
  </div>

  <script>
    const serverStart = new Date();

    const updateTime = () => {
      const now = new Date();
      document.getElementById('server-time').textContent = now.toLocaleString();

      const uptimeMs = now - serverStart;
      const seconds = Math.floor((uptimeMs / 1000) % 60);
      const minutes = Math.floor((uptimeMs / (1000 * 60)) % 60);
      const hours = Math.floor((uptimeMs / (1000 * 60 * 60)) % 24);
      const days = Math.floor(uptimeMs / (1000 * 60 * 60 * 24));

      document.getElementById('uptime').textContent =
        `${days}d ${hours}h ${minutes}m ${seconds}s`;
    };

    updateTime();
    setInterval(updateTime, 1000);
  </script>
</body>
</html>
