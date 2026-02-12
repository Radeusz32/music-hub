# Laravel Sail – Uruchomienie projektu lokalnie

Projekt oparty o Laravel uruchamiany lokalnie przy użyciu Docker + Laravel Sail.
Całe środowisko (PHP, MySQL, Node) działa w kontenerach.

============================================================

KROK 0 – WymAGANIA

Na komputerze muszą być zainstalowane:
- Docker
- Docker Compose (plugin)
- Git

Sprawdzenie:
docker --version
docker compose version
git --version

============================================================

KROK 1 – Klonowanie projektu

git clone <URL_REPOZYTORIUM>
cd <NAZWA_PROJEKTU>

============================================================

KROK 2 – Konfiguracja pliku .env

Skopiuj plik środowiskowy:

cp .env.example .env

Upewnij się, że plik .env zawiera co najmniej:

APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

APP_PORT=80

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password

WWWUSER=1000
WWWGROUP=1000

============================================================

KROK 3 – Uruchomienie kontenerów Docker piewszy raz 

docker run --rm \
  -u "$(id -u):$(id -g)" \
  -v "$(pwd):/var/www/html" \
  -w /var/www/html \
  laravelsail/php83-composer \
  composer install --ignore-platform-reqs

KROK 4 – Uruchomienie kontenerów  
./vendor/bin/sail up -d


KROK 5 – Wygenerowanie klucza aplikacji

./vendor/bin/sail artisan key:generate

============================================================

KROK 6 – Migracje bazy danych i seederow

./vendor/bin/sail artisan migrate:fresh --seed


============================================================

KROK 7 – Instalacja zależności frontendowych

./vendor/bin/sail npm install

============================================================

KROK 8 – Uruchomienie Vite (frontend)

./vendor/bin/sail npm run dev


============================================================

KROK 9 – Dostęp do aplikacji

Aplikacja:
http://localhost

PhpMyAdmin
http://localhost:8080
============================================================

PRZYDATNE KOMENDY

./vendor/bin/sail up -d
./vendor/bin/sail down
./vendor/bin/sail artisan
./vendor/bin/sail tinker
./vendor/bin/sail composer
./vendor/bin/sail npm


ZATRZYMANIE ŚRODOWISKA

./vendor/bin/sail down

Usunięcie kontenerów razem z bazą danych:

./vendor/bin/sail down -v

============================================================

UWAGI KOŃCOWE

- Projekt nie wymaga lokalnej instalacji PHP ani MySQL
- Całość działa w Dockerze
- DB_HOST musi być ustawione na "mysql"
- Każdy krok wykonuj po kolei

============================================================

LICENCJA

MIT
