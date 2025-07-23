<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Redirecting...</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="bg-white flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md">
      <div class="bg-white rounded-3xl shadow-2xl p-8 flex flex-col items-center animate-fade-in">
        <!-- Spinner -->
        <div class="mb-6">
          <svg class="animate-spin h-12 w-12 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-80" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-emerald-500 mb-2">Redirecting…</h2>
        <div class="text-gray-600 mb-4 text-center">
          You are being redirected to the original URL.<br class="hidden md:inline">
          If you are not redirected automatically, please click the button below.
        </div>
        <div class="flex items-center mb-6">
          <span class="text-gray-400 mr-2">Redirecting in</span>
          <span id="countdown" class="text-2xl font-extrabold text-emerald-500 transition-all duration-300">{{ $countdown ?? 10 }}</span>
          <span class="text-gray-400 ml-2">seconds…</span>
        </div>
        <a href="{{ $data->long_url }}" class="inline-block px-6 py-3 rounded-full bg-emerald-500 text-white font-semibold shadow-md hover:bg-emerald-600 transition-all duration-300 text-lg">
          Let's go now
        </a>
      </div>
    </div>
    <!-- Animate fade-in (optional) -->
    <style>
      @keyframes fade-in {
        from { opacity: 0; transform: translateY(24px);}
        to { opacity: 1; transform: translateY(0);}
      }
      .animate-fade-in {
        animation: fade-in 0.8s cubic-bezier(.4,0,.2,1) both;
      }
    </style>
    <script>
      let seconds = '{{ $countdown ?? 10 }}'
          seconds = parseInt(seconds);

      const countdownEl = document.getElementById('countdown')
      const targetUrl   = '{{ $data->long_url }}'

      const interval = setInterval(function() {
        seconds--;
        if (seconds >= 0) countdownEl.textContent = seconds;
        if (seconds === 0) {
          clearInterval(interval);
          window.location.href = targetUrl;
        }
      }, 1000);
    </script>
  </body>
</html>