# MergLaMunte.ro - API v3 cu Web Components

## Structura Proiectului

```
v3-components/
├── api/                          # Backend PHP
│   ├── analiza-v3.php           # Endpoint principal
│   ├── config/                   # Configurații
│   │   ├── masive.php           # Date masive montane
│   │   ├── matrice-risc.php     # Matricea de decizie
│   │   ├── praguri-meteo.php    # Praguri pentru factori
│   │   ├── salvamont.php        # Contacte Salvamont
│   │   └── ...
│   ├── services/                 # Servicii
│   │   ├── meteo-service.php    # Fetch meteo ANM/Meteoblue
│   │   ├── avalansa-service.php # Risc avalanșă
│   │   ├── evaluare-meteo.php   # Evaluare 12 factori
│   │   ├── matrice-service.php  # Aplicare matrice risc
│   │   └── determinare-context.php
│   └── utils/                    # Utilitare
│       ├── helpers.php
│       ├── validators.php
│       ├── calcule.php
│       └── date-helpers.php
│
├── components/                   # Web Components (Frontend)
│   ├── verdict-card.js          # Card decizie GO/CAUTION/NO-GO
│   ├── factor-list.js           # Lista factori meteo
│   ├── meteo-details.js         # Detalii meteo
│   ├── equipment-checklist.js   # Lista echipament
│   ├── salvamont-contact.js     # Contact Salvamont
│   ├── context-card.js          # Info traseu
│   └── predictability-banner.js # Banner predictibilitate
│
├── form.html                     # Formular input
├── results.html                  # Pagina rezultate (folosește components)
└── README.md                     # Acest fișier
```

## Instalare

1. Copiază tot folderul pe server
2. Asigură-te că PHP 7.4+ este disponibil
3. Configurează calea API în `results.html`:
   ```javascript
   const API_URL = '/calea-ta/api/analiza-v3.php';
   ```

## Folosire Web Components

Componentele sunt self-contained și pot fi folosite independent:

```html
<!-- Import component -->
<script src="components/verdict-card.js"></script>

<!-- Folosire -->
<verdict-card 
    status="VERDE" 
    mesaj="Condiții bune pentru drumeție"
    nivel="mediu"
    meteo-status="VERDE"
    dificultate="3"
    altitudine="2000">
</verdict-card>
```

### Componente disponibile:

1. **`<verdict-card>`** - Afișează decizia finală
   - Atribute: `status`, `mesaj`, `nivel`, `meteo-status`, `dificultate`, `altitudine`

2. **`<factor-list>`** - Lista factorilor meteo
   - Proprietate JS: `factors` (object)

3. **`<meteo-details>`** - Detalii meteo
   - Proprietate JS: `data` (object cu meteo)

4. **`<equipment-checklist>`** - Checklist echipament
   - Proprietate JS: `items` (array de strings)

5. **`<salvamont-contact>`** - Contact Salvamont
   - Proprietate JS: `data` (object cu nume, telefon, mobil)

6. **`<context-card>`** - Info traseu (doar pentru CAUTION/NO-GO)
   - Proprietăți JS: `data` (context object), `show` (boolean)

7. **`<predictability-banner>`** - Banner predictibilitate prognoză
   - Proprietate JS: `data` (full API response)

## API Endpoint

```
GET /api/analiza-v3.php?masiv=fagaras&data=2026-01-16&nivel_experienta=mediu&altitudine_tinta=2000
```

### Parametri:
- `masiv` - slug masiv (fagaras, bucegi, retezat, etc.)
- `data` - YYYY-MM-DD (azi sau următoarele 7 zile)
- `nivel_experienta` - incepator | mediu | experimentat
- `altitudine_tinta` - 500-2700 (metri)

### Răspuns:
```json
{
    "success": true,
    "masiv": "Fagaras",
    "data": "2026-01-16",
    "input": { ... },
    "meteo": { ... },
    "context": { ... },
    "evaluare": { "factori": { ... }, "no_go_count": 0, "caution_count": 2 },
    "decizie": { "status": "ROSU", "mesaj": "...", "cod": "ROSU" },
    "echipament": [ ... ],
    "salvamont": { ... }
}
```

## Avantaje Structură Components

1. **Modularitate** - Fiecare component e independent
2. **Reutilizare** - Poți folosi componentele în alte pagini
3. **Testare** - Fiecare component poate fi testat separat
4. **Mentenanță** - Modifici într-un singur loc
5. **Encapsulare** - Shadow DOM izolează stilurile
