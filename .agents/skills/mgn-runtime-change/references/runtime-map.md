# Runtime map

## Compose topology

`docker-compose.yml` defines local services; `docker-compose.prod.yml` uses published application images.

- `db`: PostgreSQL 17, container port 5432, persistent `db_data` volume.
- `redis`: Redis 7 Alpine, container port 6379, persistent `redisdata` volume.
- `nginx`: listens on container port 80 and proxies PHP requests to `laravel:9001`.
- `laravel`: builds the `api` target locally, runs PHP-FPM, and listens on 9001 inside the application network.
- `horizon`: builds the `worker` target locally and runs Supervisor.

The application and worker share mounted `storage`, `bootstrap/cache`, `public`, and `.env` state. Changes to those paths can affect both processes.

## Images and processes

`deployment/docker/Dockerfile.php` provides a shared PHP 8.2 Alpine template with PostgreSQL, GD, Imagick, sockets, pcntl, and other Laravel extensions.

- The `api` target runs `/entrypoint.sh`, then PHP-FPM with `deployment/config/fpm/custom-php-fpm.conf` at port 9001.
- The `worker` target installs Supervisor and runs `deployment/config/supervisor/supervisord.conf`.
- Supervisor starts `php artisan schedule:work` and `php artisan horizon` as `www-data`.
- The entrypoint applies umask `0002`, creates Laravel writable directories, and only changes ownership when running as root.

`deployment/config/nginx/default.conf` permits request bodies up to 500 MB and uses extended FastCGI timeouts for large uploads. Keep it aligned with `deployment/config/php/php.ini` upload and execution limits.

## Application operations

- `app/Console/Kernel.php` prunes Telescope daily and runs `calculate:mogou-chapters` monthly.
- `config/queue.php`, `config/horizon.php`, and Redis settings define queue behavior.
- Tests set `QUEUE_CONNECTION=sync`; a passing test does not by itself prove Horizon process configuration.
- Laravel configuration is optimized during the API image build. Environment/config changes may require clearing and rebuilding cached configuration in the target environment.

## Safe validation

Prefer read-only/static checks first:

```bash
docker compose config
docker compose -f docker-compose.prod.yml config
```

Do not run `docker compose down`, remove volumes, publish images, or deploy as an incidental validation step.

