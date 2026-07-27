@echo off
setlocal

if "%1"=="up" (
    docker-compose up -d --build
    echo Docker containers started in background.
    goto end
)

if "%1"=="down" (
    docker-compose down
    echo Docker containers stopped and removed.
    goto end
)

if "%1"=="logs" (
    docker-compose logs -f
    goto end
)

if "%1"=="db" (
    docker exec -it sqms-db mysql -uroot -pxVtED0s^7@hh82bCv6 sqms_db
    goto end
)

if "%1"=="app" (
    docker exec -it sqms-app bash
    goto end
)

if "%1"=="serve" (
    echo Starting PHP built-in server on http://localhost:8000
    php -S localhost:8000 router.php
    goto end
)

echo Usage:
echo   docker.bat up    - Start the Docker containers
echo   docker.bat down  - Stop the Docker containers
echo   docker.bat logs  - View real-time Docker logs
echo   docker.bat db    - Open MySQL shell in Docker
echo   docker.bat app   - Open App container shell
echo   docker.bat serve - Run locally using PHP built-in server (php -S)

:end
endlocal
