# De Gouden Draak – Modernisering

Laravel 11 + Vue.js 3 + Inertia.js + Tailwind CSS

---

## Installatie

```bash
# 1. Kloon het project
git clone <repo-url> gouden-draak
cd gouden-draak

# 2. PHP dependencies
composer install

# 3. Omgevingsvariabelen
cp .env.example .env
php artisan key:generate

# 4. Database aanmaken (in .env: DB_DATABASE=goudendraak)
php artisan migrate
php artisan db:seed

# 5. Node dependencies + build
npm install
npm run build

# 6. Storage link (voor afbeeldingen)
php artisan storage:link

# 7. Development server starten
php artisan serve
npm run dev
```

---

## Inloggegevens (na seeding)

| Rol   | E-mail                       | Wachtwoord        |
|-------|------------------------------|-------------------|
| Admin | admin@goudendraak.nl         | goudendraak2025   |
| Kassa | kassa@goudendraak.nl         | kassa2025         |

---

## URL's

| Pagina             | URL                           |
|--------------------|-------------------------------|
| Website (publiek)  | http://localhost:8000/        |
| Menu               | http://localhost:8000/menu    |
| Menu PDF download  | http://localhost:8000/menu/pdf |
| Afhaal bestellen   | http://localhost:8000/bestellen |
| Tablet tafel 1     | http://localhost:8000/tablet/1 |
| Kassasysteem       | http://localhost:8000/kassa   |
| Admin panel        | http://localhost:8000/admin   |

---

## UC-19 Scheduler activeren

Voeg dit toe aan de crontab van de server:

```
* * * * * php /pad/naar/project/artisan schedule:run >> /dev/null 2>&1
```

De rapportage wordt dan elke dag om 01:00 automatisch gegenereerd.

---

## Geïmplementeerde features

| User Story | Beschrijving                                  | SP  |
|-----------|-----------------------------------------------|-----|
| KO        | Frontend + Backend/DB + DevDoc                | 25  |
| UC-20     | Admin menu CRUD (a/b/c nummering)             | 6   |
| UC-13     | Localisatie NL/EN (vue-i18n + SetLocale)      | 8   |
| UC-14     | PDF menu download (DomPDF)                    | 4   |
| UC-15     | Favorieten via cookie (sortering)             | 4   |
| UC-16     | Afhaal bestelling + QR code                   | 4   |
| Responsive| Website op 3 schermgroottes (Tailwind)        | 7   |
| US-9      | Kassa zoekfunctie (naam + nummer + categorie) | 4   |
| US-10     | Kassa opmerking per gerecht                   | 3   |
| US-1      | Tablet bestelling (5 rondes, 10 min cooldown) | 5   |
| US-5      | Tablet hulp inschakelen + backoffice afmelden | 4   |
| UC-19     | Dagelijkse Excel rapportage (Scheduler)       | 8   |
| **Totaal**|                                               | **82** |

**Verwacht cijfer: ~8,2**
