.PHONY: up down logs db app serve

up:
	docker-compose up -d --build

down:
	docker-compose down

logs:
	docker-compose logs -f

db:
	docker exec -it sqms-db mysql -uroot -p"xVtED0s^7@hh82bCv6" sqms_db

app:
	docker exec -it sqms-app bash

serve:
	php -S localhost:8000 router.php

