.DEFAULT_GOAL := dc-down

install:
	git config core.fileMode false
	sudo chmod -R 777 ./
	sudo grep -qxF '10.222.33.1   tmbnailer.local' /etc/hosts || echo '10.222.33.1    tmbnailer.local' >> /etc/hosts
	sudo grep -qxF '10.222.33.6   pgadmin.tmbnailer.local'  /etc/hosts || echo '10.222.33.6    pgadmin.tmbnailer.local' >> /etc/hosts
	make dc-up
	sleep 20
	docker exec -i thumbnailer-fpm php artisan migrate
	docker exec -i thumbnailer-fpm php artisan db:seed

dc-up:
	docker-compose -f develop/docker-compose.yml -p tmbnailer up -d

dc-up-build:
	docker-compose -f develop/docker-compose.yml -p tmbnailer up --build -d

dc-down:
	docker-compose -f develop/docker-compose.yml -p tmbnailer down

dc-reload: dc-down dc-up del-rabbit-dir

migrate:
	docker exec -i thumbnailer-fpm php artisan migrate
	docker exec -i thumbnailer-fpm php artisan cache:clear

seed:
	docker exec -i thumbnailer-fpm php artisan db:seed
	docker exec -i thumbnailer-fpm php artisan cache:clear

rerun:
	docker restart php-worker
	docker restart php-worker2
	docker restart php-worker3
	docker exec -i thumbnailer-fpm php artisan cache:clear

restart:
	docker exec -i thumbnailer-fpm php artisan queue:restart
	docker exec -i thumbnailer-fpm php artisan cache:clear

remove-images:
	docker exec -i thumbnailer-fpm rm -rf public/images/*
	docker exec -i thumbnailer-fpm rm -rf public/original-images/*

chmod-777:
	docker exec -i thumbnailer-fpm chmod -R 777 .

del-rabbit-dir:
	docker exec -i thumbnailer-fpm rm -r _rabbit

test-build:
	php vendor/bin/codecept build

test-func:
	php vendor/bin/codecept run tests/Functional

test-accept:
	php vendor/bin/codecept run tests/Acceptance

test-all-steps:
	docker exec -i thumbnailer-fpm php vendor/bin/codecept run --steps
