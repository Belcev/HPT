# Mini E-shop API

PHP 8.4 REST API pro správu košíku a objednávek malého e-shopu.

## Technologie

- PHP 8.4
- Composer
- Slim Framework 4
- PHP-DI 7
- Doctrine DBAL 4
- Guzzle 7
- Nominatim (OpenStreetMap)
- PHPUnit 13
- Docker

---

## Spuštění

### Požadavky

- Docker + Docker Compose

### 1. Spustit kontejnery

```bash
docker compose up -d
```

MySQL při prvním spuštění automaticky provede `docker/mysql/init.sql` — vytvoří tabulky a naplní katalog produktů.

### 2. Nainstalovat závislosti

```bash
docker compose exec app composer install
```

API je dostupné na **http://localhost**.

### Zastavení

```bash
docker compose down
```
---

## Produkty v katalogu

| SKU | Název | Cena |
|---|---|---|
| `LAPTOP-01` | Laptop Pro 15 | 45 999 Kč |
| `MOUSE-01` | Wireless Mouse | 499 Kč |
| `KB-01` | Mechanical Keyboard | 1 299 Kč |
| `MONITOR-01` | 27" 4K Monitor | 8 999 Kč |
| `HEADPH-01` | Noise Cancelling Headphones | 2 499 Kč |

---

## Architektura

```
app/
├── Domain/           # čistá doménová vrstva bez závislostí na frameworku
│   ├── Cart/         # Cart (entita), CartItem (value object), CartRepositoryInterface
│   ├── Order/        # Order (readonly entita), OrderItem, OrderRepositoryInterface
│   ├── Product/      # Product (readonly VO), ProductRepositoryInterface
│   └── Exception/    # CartNotFoundException, ProductNotFoundException, ...
├── Application/
│   └── Service/      # CartService, OrderService — orchestrace doménové logiky
├── Infrastructure/
│   ├── ExternalApi/  # GeocoderInterface + NominatimGeocoder (Nominatim OSM)
│   └── Persistence/  # implementace repository přes Doctrine DBAL (MySQL)
└── Http/
    └── Controller/   # CartController, OrderController

bootstrap/container.php  # definice PHP-DI kontejneru
public/index.php          # vstupní bod, routing (Slim 4)

legacy/
├── legacy.php            # původní kód zadání (refaktoring)
└── refactored/           # refaktorovaná verze s DI, readonly modely, správné typy
```

## Co bych dělal dál, kdybych měl víc času

- **Opakované vytvoření objednávky** — aplikace při vytvoření objednávky nevyprázdní košík, takže pokud klient po vytvoření objednávky znovu zavolá stejný endpoint s tím samým `cart_id`, vytvoří se další objednávka se stejnými položkami. V reálném projektu bych to ošetřil buď vyprázdněním košíku po vytvoření objednávky, nebo přidáním stavu k objednávce a kontrole před vytvořením nové.
- **Testy** — unit testy pokrývají aplikační vrstvu, chybí testy, které projdou celým stackem.
- **Autentizace** — pro ochranu endpointů;
- **Endpoiny pro Produkty** — momentálně je katalog statický (seed v `init.sql`);
- **CI/CD pipeline** — GitHub Actions
- **GIT** - v reálném projektu bych do GIT commitů psal jesnější zprávy

## Na co jsem se zaměřil a proč

PHPStan běží na `level: max` bez baseline, takže každý `array` má shape annotation, každý nullable typ je explicitní.
Jsem si vědom toho, že v praxi je to zbytečné zdržení co místy kód trochu znepřehledňuje a refaktoring je pak náročnější, ale proč ne. :)

## Poznámky

Ceny jsou interně uloženy jako **celá čísla v haléřích** (`int`). V API odpovědích jsou převedeny na `float` v korunách.
Důvodem je, že `float` v PHP může způsobovat problémy s přesností při ukládání peněžních hodnot
