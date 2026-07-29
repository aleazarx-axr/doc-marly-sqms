<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/Ticket.php';
require_once __DIR__ . '/../../includes/models/Counter.php';

Session::requireLogin();
$userId = Session::get('user_id');
$role = Session::get('role');

$db = new Database();
$conn = $db->getConnection();
$ticketModel = new Ticket($conn);
$counterModel = new Counter($conn);

if (!$conn) {
    die("Database connection failed. Please check your .env settings and ensure the database is running.");
}

$serviceIds = [];
$waitingList = [];
$currentTicket = null;
$currentCounter = null;

if ($role !== 'admin') {
    header("Location: /");
    exit();
}

// Fetch counts for Admin Dashboard
$stmt = $conn->query("SELECT COUNT(*) as count FROM services WHERE is_archived = 0");
$servicesCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $conn->query("SELECT COUNT(*) as count FROM counters WHERE is_archived = 0");
$countersCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $conn->query("SELECT COUNT(*) as count FROM users");
$usersCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $conn->query("SELECT COUNT(*) as count FROM tickets");
$recordsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Fetch recent tickets for Queue Monitoring
$stmt = $conn->query("SELECT t.ticket_number, s.name as service_name, t.status FROM tickets t LEFT JOIN services s ON t.service_id = s.id ORDER BY t.issued_at DESC LIMIT 5");
$recentTickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Admin Dashboard - Doc Marly SQMS';
$activeMenu = 'dashboard';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar_admin.php';
?>

<link rel="stylesheet" href="/assets/css/admin_ui.css">
<link rel="stylesheet" href="/assets/css/clockwidget_ui.css">

<div class="main-content">
    <div class="header-section">
        <div>
            <h1><?php echo ($role === 'admin') ? 'Admin Portal' : 'Staff Portal'; ?></h1>
            <h2>Welcome back, <?php echo htmlspecialchars(Session::get('name') ?: Session::get('username') ?: 'User'); ?></h2>

        <!-- Header info removed - clock is now floating -->
        </div>
    </div>
        <!-- Top Cards for Admin -->
        <div class="dashboard-grid">
            <a href="/modules/admin/service_management/services.php" class="card">
                <div class="card-content-wrapper">
                    <div class="card-left">
                        <h3>Services Active</h3>
                        <span class="card-link-hint">Manage Services &rarr;</span>
                    </div>
                    <div class="card-right">
                        <div class="value-large"><?= htmlspecialchars($servicesCount) ?></div>
                    </div>
                </div>
            </a>
            <a href="/modules/admin/service_management/counters.php" class="card">
                <div class="card-content-wrapper">
                    <div class="card-left">
                        <h3>Active Counters</h3>
                        <span class="card-link-hint">Manage Counters &rarr;</span>
                    </div>
                    <div class="card-right">
                        <div class="value-large"><?= htmlspecialchars($countersCount) ?></div>
                    </div>
                </div>
            </a>
            <a href="/modules/admin/user_management/index.php" class="card">
                <div class="card-content-wrapper">
                    <div class="card-left">
                        <h3>Total Users</h3>
                        <span class="card-link-hint">Manage Users &rarr;</span>
                    </div>
                    <div class="card-right">
                        <div class="value-large"><?= htmlspecialchars($usersCount) ?></div>
                    </div>
                </div>
            </a>
            <a href="/modules/admin/records/index.php" class="card">
                <div class="card-content-wrapper">
                    <div class="card-left">
                        <h3>Ticket Records</h3>
                        <span class="card-link-hint">View Records &rarr;</span>
                    </div>
                    <div class="card-right">
                        <div class="value-large"><?= htmlspecialchars($recordsCount) ?></div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Bottom Section: Queue Monitoring -->
        <div class="card card-staff">
            <h3 class="mb-4">Recent Queue Activity</h3>

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Ticket Number</th>
                            <th>Service</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentTickets)): ?>
                            <?php foreach ($recentTickets as $ticket): ?>
                                <?php
                                $statusClass = 'badge-default';
                                if ($ticket['status'] == 'called') $statusClass = 'badge-called';
                                elseif ($ticket['status'] == 'serving') $statusClass = 'badge-serving';
                                elseif ($ticket['status'] == 'done') $statusClass = 'badge-done';
                                ?>
                                <tr>
                                    <td><strong class="text-primary"><?= htmlspecialchars($ticket['ticket_number']) ?></strong></td>
                                    <td><?= htmlspecialchars($ticket['service_name']) ?></td>
                                    <td><span class="nexus-badge <?= $statusClass ?>"><?= ucfirst(htmlspecialchars($ticket['status'])) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-5">No recent queue activity recorded.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            </div>
        </div>
</div>

<!-- ==========================================
     FLOATING CLOCK WIDGET CONTAINER
     ========================================== -->
<div id="floating-clock-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;">
    <!-- Minimize Toggle Button -->
    <button id="clock-toggle-btn" title="Toggle clock visibility">−</button>

    <!-- Clock Widget -->
    <div class="clock-widget" id="weather-widget">
        <!-- Loading Overlay -->
        <div class="loading-overlay" id="loading-overlay">
            <span>Loading weather...</span>
        </div>

        <section class="info-section">
            <!-- Background design (circles) -->
            <div class="bg-design">
                <div class="bg-circle1"></div>
                <div class="bg-circle2"></div>
                <div class="bg-circle3"></div>
            </div>

            <!-- left side -->
            <div class="left-side">
                <!-- weather row -->
                <div class="weather-row">
                    <div class="weather-icon" id="weather-icon">
                        <svg stroke="#ffffff" fill="#ffffff" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024">
                            <g stroke-width="0" id="SVGRepo_bgCarrier"></g>
                            <g stroke-linejoin="round" stroke-linecap="round" id="SVGRepo_tracerCarrier"></g>
                            <g id="SVGRepo_iconCarrier">
                                <path d="M512 704a192 192 0 1 0 0-384 192 192 0 0 0 0 384zm0 64a256 256 0 1 1 0-512 256 256 0 0 1 0 512zm0-704a32 32 0 0 1 32 32v64a32 32 0 0 1-64 0V96a32 32 0 0 1 32-32zm0 768a32 32 0 0 1 32 32v64a32 32 0 1 1-64 0v-64a32 32 0 0 1 32-32zM195.2 195.2a32 32 0 0 1 45.248 0l45.248 45.248a32 32 0 1 1-45.248 45.248L195.2 240.448a32 32 0 0 1 0-45.248zm543.104 543.104a32 32 0 0 1 45.248 0l45.248 45.248a32 32 0 0 1-45.248 45.248l-45.248-45.248a32 32 0 0 1 0-45.248zM64 512a32 32 0 0 1 32-32h64a32 32 0 0 1 0 64H96a32 32 0 0 1-32-32zm768 0a32 32 0 0 1 32-32h64a32 32 0 1 1 0 64h-64a32 32 0 0 1-32-32zM195.2 828.8a32 32 0 0 1 0-45.248l45.248-45.248a32 32 0 0 1 45.248 45.248L240.448 828.8a32 32 0 0 1-45.248 0zm543.104-543.104a32 32 0 0 1 0-45.248l45.248-45.248a32 32 0 0 1 45.248 45.248l-45.248 45.248a32 32 0 0 1-45.248 0z" fill="#ffffff"></path>
                            </g>
                        </svg>
                    </div>
                    <div id="weather-condition">Sunny</div>
                </div>

                <!-- temperature -->
                <div class="temperature" id="weather-temp">--°</div>
                <!-- range (feels like) -->
                <div class="temp-range" id="weather-range">--°/--°</div>
            </div>

            <!-- right side -->
            <div class="right-side">
                <div style="display:flex; flex-direction:column; align-items:flex-end;">
                    <div class="time-display" id="clock-time">
                        <span class="time-hours-minutes">
                            <span id="clock-hours">--</span>
                            <span class="time-separator">:</span>
                            <span id="clock-minutes">--</span>
                        </span>
                        <span class="time-separator">:</span>
                        <span class="seconds-display" id="clock-seconds">--</span>
                        <span class="ampm-display" id="clock-ampm">AM</span>
                    </div>
                    <div class="date-display" id="clock-date">--- --</div>
                </div>
                <div class="location" id="weather-location">Zamboanga Sibugay</div>
            </div>
        </section>

        <!-- DAYS SECTION -->
        <section class="days-section">
            <button class="day-btn" onmouseout="this.style.transform='scale(1)'; this.style.borderRadius='0';">
                <span>TUE</span>
                <span class="day-icon">
                    <svg stroke="#ffffff" fill="#ffffff" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024">
                        <path d="M512 704a192 192 0 1 0 0-384 192 192 0 0 0 0 384zm0 64a256 256 0 1 1 0-512 256 256 0 0 1 0 512zm0-704a32 32 0 0 1 32 32v64a32 32 0 0 1-64 0V96a32 32 0 0 1 32-32zm0 768a32 32 0 0 1 32 32v64a32 32 0 1 1-64 0v-64a32 32 0 0 1 32-32zM195.2 195.2a32 32 0 0 1 45.248 0l45.248 45.248a32 32 0 1 1-45.248 45.248L195.2 240.448a32 32 0 0 1 0-45.248zm543.104 543.104a32 32 0 0 1 45.248 0l45.248 45.248a32 32 0 0 1-45.248 45.248l-45.248-45.248a32 32 0 0 1 0-45.248zM64 512a32 32 0 0 1 32-32h64a32 32 0 0 1 0 64H96a32 32 0 0 1-32-32zm768 0a32 32 0 0 1 32-32h64a32 32 0 1 1 0 64h-64a32 32 0 0 1-32-32zM195.2 828.8a32 32 0 0 1 0-45.248l45.248-45.248a32 32 0 0 1 45.248 45.248L240.448 828.8a32 32 0 0 1-45.248 0zm543.104-543.104a32 32 0 0 1 0-45.248l45.248-45.248a32 32 0 0 1 45.248 45.248l-45.248 45.248a32 32 0 0 1-45.248 0z" fill="#ffffff" />
                    </svg>
                </span>
            </button>
            <button class="day-btn" onmouseout="this.style.transform='scale(1)'; this.style.borderRadius='0';">
                <span>WED</span>
                <span class="day-icon">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff">
                        <path d="M16 18.5L15 21M8 18.5L7 21M12 18.5L11 21M7 15C4.23858 15 2 12.7614 2 10C2 7.23858 4.23858 5 7 5C7.03315 5 7.06622 5.00032 7.09922 5.00097C8.0094 3.2196 9.86227 2 12 2C14.5192 2 16.6429 3.69375 17.2943 6.00462C17.3625 6.00155 17.4311 6 17.5 6C19.9853 6 22 8.01472 22 10.5C22 12.9853 19.9853 15 17.5 15C13.7434 15 11.2352 15 7 15Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
            </button>
            <button class="day-btn" onmouseout="this.style.transform='scale(1)'; this.style.borderRadius='0';">
                <span>THU</span>
                <span class="day-icon">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff">
                        <path d="M16 18.5L15 21M8 18.5L7 21M12 18.5L11 21M7 15C4.23858 15 2 12.7614 2 10C2 7.23858 4.23858 5 7 5C7.03315 5 7.06622 5.00032 7.09922 5.00097C8.0094 3.2196 9.86227 2 12 2C14.5192 2 16.6429 3.69375 17.2943 6.00462C17.3625 6.00155 17.4311 6 17.5 6C19.9853 6 22 8.01472 22 10.5C22 12.9853 19.9853 15 17.5 15C13.7434 15 11.2352 15 7 15Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
            </button>
            <button class="day-btn" onmouseout="this.style.transform='scale(1)'; this.style.borderRadius='0';">
                <span>FRI</span>
                <span class="day-icon">
                    <svg stroke="#ffffff" fill="#ffffff" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024">
                        <path d="M512 704a192 192 0 1 0 0-384 192 192 0 0 0 0 384zm0 64a256 256 0 1 1 0-512 256 256 0 0 1 0 512zm0-704a32 32 0 0 1 32 32v64a32 32 0 0 1-64 0V96a32 32 0 0 1 32-32zm0 768a32 32 0 0 1 32 32v64a32 32 0 1 1-64 0v-64a32 32 0 0 1 32-32zM195.2 195.2a32 32 0 0 1 45.248 0l45.248 45.248a32 32 0 1 1-45.248 45.248L195.2 240.448a32 32 0 0 1 0-45.248zm543.104 543.104a32 32 0 0 1 45.248 0l45.248 45.248a32 32 0 0 1-45.248 45.248l-45.248-45.248a32 32 0 0 1 0-45.248zM64 512a32 32 0 0 1 32-32h64a32 32 0 0 1 0 64H96a32 32 0 0 1-32-32zm768 0a32 32 0 0 1 32-32h64a32 32 0 1 1 0 64h-64a32 32 0 0 1-32-32zM195.2 828.8a32 32 0 0 1 0-45.248l45.248-45.248a32 32 0 0 1 45.248 45.248L240.448 828.8a32 32 0 0 1-45.248 0zm543.104-543.104a32 32 0 0 1 0-45.248l45.248-45.248a32 32 0 0 1 45.248 45.248l-45.248 45.248a32 32 0 0 1-45.248 0z" fill="#ffffff" />
                    </svg>
                </span>
            </button>
        </section>
    </div>
</div>

<!-- Pure JavaScript Weather & Location - Zamboanga Sibugay Specific + Draggable + 12-Hour Format -->
<script>
    // ============================================
    // 1. CLOCK FUNCTION WITH 12-HOUR FORMAT & AM/PM
    // ============================================
    function updateClock() {
        const now = new Date();
        let hours = now.getHours();
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');

        // Determine AM/PM
        const ampm = hours >= 12 ? 'PM' : 'AM';

        // Convert to 12-hour format
        hours = hours % 12;
        hours = hours ? hours : 12; // 12 instead of 0
        const hours12 = String(hours).padStart(2, '0');

        document.getElementById('clock-hours').textContent = hours12;
        document.getElementById('clock-minutes').textContent = minutes;
        document.getElementById('clock-seconds').textContent = seconds;
        document.getElementById('clock-ampm').textContent = ampm;

        const days = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
        const dayName = days[now.getDay()];
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        document.getElementById('clock-date').textContent = dayName + ' ' + month + '-' + day;
    }

    // ============================================
    // 2. WEATHER ICON MAPPER
    // ============================================
    function getWeatherIcon(condition) {
        const icons = {
            'clear': `<svg stroke="#ffffff" fill="#ffffff" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024">
                        <path d="M512 704a192 192 0 1 0 0-384 192 192 0 0 0 0 384zm0 64a256 256 0 1 1 0-512 256 256 0 0 1 0 512zm0-704a32 32 0 0 1 32 32v64a32 32 0 0 1-64 0V96a32 32 0 0 1 32-32zm0 768a32 32 0 0 1 32 32v64a32 32 0 1 1-64 0v-64a32 32 0 0 1 32-32zM195.2 195.2a32 32 0 0 1 45.248 0l45.248 45.248a32 32 0 1 1-45.248 45.248L195.2 240.448a32 32 0 0 1 0-45.248zm543.104 543.104a32 32 0 0 1 45.248 0l45.248 45.248a32 32 0 0 1-45.248 45.248l-45.248-45.248a32 32 0 0 1 0-45.248zM64 512a32 32 0 0 1 32-32h64a32 32 0 0 1 0 64H96a32 32 0 0 1-32-32zm768 0a32 32 0 0 1 32-32h64a32 32 0 1 1 0 64h-64a32 32 0 0 1-32-32zM195.2 828.8a32 32 0 0 1 0-45.248l45.248-45.248a32 32 0 0 1 45.248 45.248L240.448 828.8a32 32 0 0 1-45.248 0zm543.104-543.104a32 32 0 0 1 0-45.248l45.248-45.248a32 32 0 0 1 45.248 45.248l-45.248 45.248a32 32 0 0 1-45.248 0z" fill="#ffffff"/>
                    </svg>`,
            'clouds': `<svg stroke="#ffffff" fill="#ffffff" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M16 18.5L15 21M8 18.5L7 21M12 18.5L11 21M7 15C4.23858 15 2 12.7614 2 10C2 7.23858 4.23858 5 7 5C7.03315 5 7.06622 5.00032 7.09922 5.00097C8.0094 3.2196 9.86227 2 12 2C14.5192 2 16.6429 3.69375 17.2943 6.00462C17.3625 6.00155 17.4311 6 17.5 6C19.9853 6 22 8.01472 22 10.5C22 12.9853 19.9853 15 17.5 15C13.7434 15 11.2352 15 7 15Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>`,
            'rain': `<svg stroke="#ffffff" fill="#ffffff" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M16 18.5L15 21M8 18.5L7 21M12 18.5L11 21M7 15C4.23858 15 2 12.7614 2 10C2 7.23858 4.23858 5 7 5C7.03315 5 7.06622 5.00032 7.09922 5.00097C8.0094 3.2196 9.86227 2 12 2C14.5192 2 16.6429 3.69375 17.2943 6.00462C17.3625 6.00155 17.4311 6 17.5 6C19.9853 6 22 8.01472 22 10.5C22 12.9853 19.9853 15 17.5 15C13.7434 15 11.2352 15 7 15Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8 19L6 21M12 19L10 21M16 19L14 21" stroke="#ffffff" stroke-width="2" stroke-linecap="round"/>
                    </svg>`
        };
        return icons[condition] || icons['clear'];
    }

    // ============================================
    // 3. WEATHER CONDITION MAPPER
    // ============================================
    function getWeatherClass(condition) {
        const map = {
            'clear': 'weather-sunny',
            'clouds': 'weather-cloudy',
            'rain': 'weather-rainy',
            'drizzle': 'weather-rainy',
            'thunderstorm': 'weather-stormy',
            'snow': 'weather-snowy',
            'mist': 'weather-cloudy',
            'fog': 'weather-cloudy'
        };
        return map[condition] || 'weather-sunny';
    }

    // ============================================
    // 4. GET WEATHER FOR ZAMBOANGA SIBUGAY
    // Coordinates: 7.8000° N, 122.6667° E
    // ============================================
    function getWeather() {
        const loadingOverlay = document.getElementById('loading-overlay');
        const widget = document.getElementById('weather-widget');

        // Zamboanga Sibugay coordinates
        const lat = 7.8000;
        const lon = 122.6667;

        // Set location
        document.getElementById('weather-location').textContent = 'Zamboanga Sibugay';

        // Get weather using Open-Meteo (FREE, no API key)
        const weatherUrl = `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current_weather=true&timezone=Asia/Manila`;

        fetch(weatherUrl)
            .then(response => {
                if (!response.ok) throw new Error('Weather API failed');
                return response.json();
            })
            .then(weatherData => {
                const current = weatherData.current_weather;
                if (!current) throw new Error('No weather data');

                // Update temperature
                const temp = Math.round(current.temperature);
                document.getElementById('weather-temp').textContent = temp + '°';

                // Update feels like (approximate)
                document.getElementById('weather-range').textContent =
                    (temp - 2) + '°/' + (temp + 2) + '°';

                // Determine weather condition from weather code
                const codes = {
                    0: 'clear',
                    1: 'clear',
                    2: 'clouds',
                    3: 'clouds',
                    45: 'fog',
                    48: 'fog',
                    51: 'rain',
                    53: 'rain',
                    55: 'rain',
                    56: 'rain',
                    57: 'rain',
                    61: 'rain',
                    63: 'rain',
                    65: 'rain',
                    66: 'rain',
                    67: 'rain',
                    71: 'snow',
                    73: 'snow',
                    75: 'snow',
                    77: 'snow',
                    80: 'rain',
                    81: 'rain',
                    82: 'rain',
                    85: 'snow',
                    86: 'snow',
                    95: 'thunderstorm',
                    96: 'thunderstorm',
                    99: 'thunderstorm'
                };

                const weatherCode = current.weathercode;
                const condition = codes[weatherCode] || 'clear';

                // Map condition to display name
                const displayNames = {
                    'clear': 'Sunny',
                    'clouds': 'Cloudy',
                    'rain': 'Rainy',
                    'snow': 'Snowy',
                    'fog': 'Foggy',
                    'thunderstorm': 'Stormy'
                };

                document.getElementById('weather-condition').textContent =
                    displayNames[condition] || 'Sunny';

                // Update icon
                const iconMap = {
                    'clear': 'clear',
                    'clouds': 'clouds',
                    'rain': 'rain',
                    'snow': 'rain',
                    'fog': 'clouds',
                    'thunderstorm': 'rain'
                };
                document.getElementById('weather-icon').innerHTML =
                    getWeatherIcon(iconMap[condition] || 'clear');

                // Update widget class for background colors
                widget.className = 'clock-widget ' + getWeatherClass(condition);

                // Hide loading
                loadingOverlay.classList.add('hidden');
            })
            .catch(error => {
                console.error('Weather error:', error);
                loadingOverlay.classList.add('hidden');
                // Set fallback data for Zamboanga Sibugay
                document.getElementById('weather-temp').textContent = '28°';
                document.getElementById('weather-range').textContent = '26°/30°';
                document.getElementById('weather-condition').textContent = 'Sunny';
                document.getElementById('weather-icon').innerHTML = getWeatherIcon('clear');
                widget.className = 'clock-widget weather-sunny';
            });
    }

    // ============================================
    // 5. DRAGGABLE FUNCTIONALITY
    // ============================================
    (function() {
        const container = document.getElementById('floating-clock-container');
        let isDragging = false;
        let startX, startY, offsetX, offsetY;

        function onStart(e) {
            // Don't start drag if clicking on toggle button
            if (e.target.closest('#clock-toggle-btn')) return;

            isDragging = true;
            const touch = e.touches ? e.touches[0] : e;
            startX = touch.clientX;
            startY = touch.clientY;

            const rect = container.getBoundingClientRect();
            offsetX = startX - rect.left;
            offsetY = startY - rect.top;

            container.style.cursor = 'grabbing';
            container.style.transition = 'none';

            e.preventDefault();
        }

        function onMove(e) {
            if (!isDragging) return;

            const touch = e.touches ? e.touches[0] : e;
            const x = touch.clientX - offsetX;
            const y = touch.clientY - offsetY;

            // Keep within viewport
            const maxX = window.innerWidth - container.offsetWidth;
            const maxY = window.innerHeight - container.offsetHeight;
            const newX = Math.max(0, Math.min(x, maxX));
            const newY = Math.max(0, Math.min(y, maxY));

            container.style.left = newX + 'px';
            container.style.top = newY + 'px';
            container.style.right = 'auto';
            container.style.bottom = 'auto';

            e.preventDefault();
        }

        function onEnd() {
            if (isDragging) {
                isDragging = false;
                container.style.cursor = 'grab';
                container.style.transition = 'opacity 0.3s ease';
            }
        }

        // Mouse events
        container.addEventListener('mousedown', onStart);
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onEnd);

        // Touch events
        container.addEventListener('touchstart', onStart, {
            passive: false
        });
        document.addEventListener('touchmove', onMove, {
            passive: false
        });
        document.addEventListener('touchend', onEnd);
    })();

    // ============================================
    // 6. MINIMIZE/MAXIMIZE TOGGLE
    // ============================================
    (function() {
        const container = document.getElementById('floating-clock-container');
        const toggleBtn = document.getElementById('clock-toggle-btn');
        let isMinimized = false;

        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            isMinimized = !isMinimized;
            container.classList.toggle('minimized', isMinimized);
            toggleBtn.textContent = isMinimized ? '+' : '−';
            toggleBtn.title = isMinimized ? 'Expand clock' : 'Minimize clock';
        });
    })();

    // ============================================
    // 7. INITIALIZE EVERYTHING
    // ============================================
    updateClock();
    setInterval(updateClock, 1000);

    // Wait for DOM to load then get weather
    document.addEventListener('DOMContentLoaded', function() {
        getWeather();
    });
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
