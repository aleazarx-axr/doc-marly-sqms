<?php
// Main display page - No refresh here!
// Simple API to get latest videos without refreshing the page
if (isset($_GET['api']) && $_GET['api'] === 'videos') {
    $videoDir = __DIR__ . '/assets/videos';
    $videos = [];
    if (is_dir($videoDir)) {
        $files = scandir($videoDir);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'mp4') {
                $mtime = filemtime($videoDir . '/' . $file);
                $videos[] = '/assets/videos/' . $file . '?v=' . $mtime;
            }
        }
    }
    if (empty($videos)) {
        $videos[] = 'https://www.w3schools.com/html/mov_bbb.mp4';
    }
    header('Content-Type: application/json');
    echo json_encode($videos);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Queue Display</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: sans-serif;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
            background-color: #fff;
        }
        .header {
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ccc;
        }
        .header h1 { margin: 0; }
        .main-content {
            display: flex;
            flex: 1;
            padding: 20px;
            gap: 20px;
        }
        .now-serving-container {
            flex: 1; /* takes up remaining width */
            height: 100%;
        }
        .now-serving-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        .video-container {
            width: 65%; /* 65% of screen width for a large video */
            aspect-ratio: 16 / 9;
            border: 1px solid #000;
            background-color: #000;
            flex-shrink: 0;
            align-self: center; /* Center it vertically if there is extra space */
        }
        .video-container video {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .marquee {
            background-color: #eee;
            padding: 10px;
            font-size: 18px;
            white-space: nowrap;
            overflow: hidden;
            border-top: 1px solid #ccc;
        }
        .marquee span {
            display: inline-block;
            padding-left: 100%;
            animation: marquee 15s linear infinite;
        }
        @keyframes marquee {
            0% { transform: translate(0, 0); }
            100% { transform: translate(-100%, 0); }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Live Queue Status</h1>
        <div class="datetime" id="datetime"></div>
    </div>

    <div class="main-content">
        <div class="now-serving-container">
            <!-- Iframe handles auto-refreshing the queue data every 5s -->
            <iframe src="/display_queue.php"></iframe>
        </div>

        <div class="video-container">
            <video id="promoVideo" autoplay playsinline></video>
        </div>
    </div>

    <div class="marquee">
        <span>Welcome to Doc Marly SQMS! Please wait for your number to be called on the screen. Prepare your requirements while waiting. Thank you!</span>
    </div>

    <?php
        // Get all MP4 files in assets/videos
        $videoDir = __DIR__ . '/assets/videos';
        $videos = [];
        if (is_dir($videoDir)) {
            $files = scandir($videoDir);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'mp4') {
                    // Append cache buster to URL based on modification time
                    $mtime = filemtime($videoDir . '/' . $file);
                    $videos[] = '/assets/videos/' . $file . '?v=' . $mtime;
                }
            }
        }
        
        // If no videos, fallback
        if (empty($videos)) {
            $videos[] = 'https://www.w3schools.com/html/mov_bbb.mp4';
        }
    ?>

    <script>
        // Clock
        setInterval(() => {
            document.getElementById('datetime').textContent = new Date().toLocaleString();
        }, 1000);
        document.getElementById('datetime').textContent = new Date().toLocaleString();

        // Video Looper
        const videoElement = document.getElementById('promoVideo');
        
        let videos = [];
        let currentIndex = 0;

        async function fetchVideos() {
            try {
                const response = await fetch('/display.php?api=videos');
                if (response.ok) {
                    videos = await response.json();
                }
            } catch (error) {
                console.error("Failed to fetch videos:", error);
            }
        }

        async function playNextVideo() {
            await fetchVideos(); // Always get the latest list before playing next

            if (videos.length === 0) return;

            // If the current index is somehow out of bounds because videos were deleted
            if (currentIndex >= videos.length) {
                currentIndex = 0;
            }

            videoElement.loop = (videos.length === 1); // Native loop if only 1 video
            
            videoElement.src = videos[currentIndex];
            videoElement.currentTime = 0; // Force rewind just in case
            videoElement.play().catch(e => console.log('Autoplay blocked:', e));
            
            currentIndex++;
            if (currentIndex >= videos.length) {
                currentIndex = 0; // Loop back to start
            }
        }

        // When current video ends, play the next one
        videoElement.addEventListener('ended', playNextVideo);
        
        // Also skip to next if there is an error playing (e.g. unsupported format)
        videoElement.addEventListener('error', () => {
            console.error("Error playing video:", videoElement.src);
            setTimeout(playNextVideo, 2000); // Try next after 2s
        });
        
        // Start playing the first video
        playNextVideo();

        let globalAudioCtx = null;
        
        // Add click listener to start video if autoplay was blocked, and init audio
        document.body.addEventListener('click', () => {
            if (videoElement.paused) {
                videoElement.play().catch(e => console.log('Manual play blocked:', e));
            }
            
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (AudioContext && !globalAudioCtx) {
                globalAudioCtx = new AudioContext();
            }
            if (globalAudioCtx && globalAudioCtx.state === 'suspended') {
                globalAudioCtx.resume();
            }
        });

        // Listen for new tickets from the queue iframe
        window.addEventListener('message', function(event) {
            if (event.data && event.data.type === 'speak') {
                const text = event.data.text;
                
                // Lower video volume
                const originalVolume = videoElement.volume;
                videoElement.volume = 0.1;
                
                const restoreVolume = () => {
                    videoElement.volume = originalVolume;
                };

                // Function to play a synthesized airport-style chime
                function playChime() {
                    return new Promise((resolve) => {
                        if (!globalAudioCtx) {
                            resolve(); // Skip if user hasn't interacted yet
                            return;
                        }
                        const ctx = globalAudioCtx;
                        if (ctx.state === 'suspended') {
                            ctx.resume();
                        }
                        
                        // E5 Note (Ding)
                        const osc1 = ctx.createOscillator();
                        const gain1 = ctx.createGain();
                        osc1.type = 'sine';
                        osc1.frequency.setValueAtTime(783.99, ctx.currentTime); // G5
                        gain1.gain.setValueAtTime(0, ctx.currentTime);
                        gain1.gain.linearRampToValueAtTime(0.4, ctx.currentTime + 0.05);
                        gain1.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.8);
                        osc1.connect(gain1);
                        gain1.connect(ctx.destination);
                        osc1.start(ctx.currentTime);
                        osc1.stop(ctx.currentTime + 0.8);
                        
                        // C5 Note (Dong)
                        const osc2 = ctx.createOscillator();
                        const gain2 = ctx.createGain();
                        osc2.type = 'sine';
                        osc2.frequency.setValueAtTime(523.25, ctx.currentTime + 0.4); // C5
                        gain2.gain.setValueAtTime(0, ctx.currentTime + 0.4);
                        gain2.gain.linearRampToValueAtTime(0.4, ctx.currentTime + 0.45);
                        gain2.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 1.5);
                        osc2.connect(gain2);
                        gain2.connect(ctx.destination);
                        osc2.start(ctx.currentTime + 0.4);
                        osc2.stop(ctx.currentTime + 1.5);
                        
                        setTimeout(resolve, 1500); // Wait for chime to finish
                    });
                }

                playChime().then(() => {
                    if ('speechSynthesis' in window) {
                        const utterance = new SpeechSynthesisUtterance(text);
                        // Optional: adjust rate or pitch
                        utterance.rate = 0.85; 
                        utterance.pitch = 1;
                        utterance.onend = restoreVolume;
                        utterance.onerror = restoreVolume;
                        window.speechSynthesis.speak(utterance);
                    } else {
                        restoreVolume();
                    }
                }).catch(e => {
                    console.log('Chime failed', e);
                    restoreVolume();
                });
            }
        });
    </script>
</body>
</html>
