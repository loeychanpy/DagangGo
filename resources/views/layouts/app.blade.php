<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'DagangGo') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-surface text-on-surface">
        <!-- Page loading indicator -->
        <div id="page-loader" class="fixed top-0 left-0 h-1 bg-primary z-[9999] pointer-events-none" style="width:0;transition:none"></div>
        <script>
            (function(){
                var loader = document.getElementById('page-loader');
                var timer;
                var active = false;

                function startLoader(){
                    if(active) return;
                    active = true;
                    clearTimeout(timer);
                    // Immediately jump to 20% — instant visual feedback
                    loader.style.transition = 'none';
                    loader.style.width = '20%';
                    // Then slowly creep to 85%
                    requestAnimationFrame(function(){
                        requestAnimationFrame(function(){
                            loader.style.transition = 'width 6s cubic-bezier(0.05,0.5,0.1,1)';
                            loader.style.width = '85%';
                        });
                    });
                }

                function finishLoader(){
                    active = false;
                    clearTimeout(timer);
                    loader.style.transition = 'width 0.15s ease';
                    loader.style.width = '100%';
                    timer = setTimeout(function(){
                        loader.style.transition = 'none';
                        loader.style.width = '0';
                    }, 200);
                }

                document.addEventListener('click', function(e){
                    var link = e.target.closest('a[href]');
                    if(!link) return;
                    if(link.target === '_blank') return;
                    if(e.ctrlKey || e.metaKey || e.shiftKey) return;
                    var href = link.getAttribute('href');
                    if(!href || href.startsWith('#') || href.startsWith('javascript')) return;
                    if(link.hostname && link.hostname !== location.hostname) return;
                    startLoader();
                });

                document.addEventListener('submit', function(){ startLoader(); });
                window.addEventListener('load', finishLoader);
            })();
        </script>
        <div class="min-h-screen bg-surface">
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-white border-b border-outline-variant shadow-sm">
                    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
