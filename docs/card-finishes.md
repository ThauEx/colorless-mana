# Magic-Karten: Finishes ("Foil") richtig modellieren

Referenz für alles rund um Foil-Arten, Sonderfälle und die Zuordnung beim Scannen/Importieren.
Die Zahlen stammen aus einer realen Datenbank mit 100.224 Karten (MTGJSON-Stand 2026-07).

## 1. Das Grundproblem

**"Foil" ist kein Boolean.** Es ist ein *Finish*, und davon gibt es viele physisch
unterschiedliche. In der Praxis kommen aktuell **37 verschiedene Finish-Werte** vor.

Eine Karte hat außerdem nicht *ein* Finish, sondern eine **Liste der verfügbaren**
Finishes — denselben Print gibt es z. B. als `nonfoil` *und* `surgefoil`.

Reale Verteilung (Anzahl Karten je Finish):

| Finish | Karten | Finish | Karten | Finish | Karten |
|---|---|---|---|---|---|
| nonfoil | 87.215 | doublerainbow | 289 | manafoil | 60 |
| foil | 56.093 | rainbowfoil | 190 | fracturefoil | 60 |
| surgefoil | 2.329 | halofoil | 159 | gilded | 48 |
| signed | 1.238 | firstplacefoil | 137 | raisedfoil | 42 |
| etched | 1.218 | embossed | 99 | confettifoil | 38 |
| galaxyfoil | 376 | textured | 98 | oilslick | 25 |
| silverfoil | 369 | stepandcompleat | 76 | chocobotrackfoil | 25 |
| ripplefoil | 349 | invisibleink | 14 | glossy | 7 |

Dazu der lange Schwanz: `dazzlefoil`, `neoninkblue/green/red/yellow/purple/pink`,
`dragonscalefoil`, `cosmicfoil`, `metal`, `facetfoil`, `singularityfoil` — teilweise
nur 1–2 Karten weltweit.

## 2. Woher die Information kommt

Zwei Felder, die man **kombinieren** muss:

- **`finishes`** — MTGJSON/Scryfall liefern hier meist nur `["nonfoil", "foil"]`,
  manchmal zusätzlich `etched` oder `signed`.
- **`promoTypes`** — hier steht die *eigentliche* Foil-Art (`surgefoil`, `galaxyfoil`,
  `oilslick`, …), aber **vermischt mit völlig unbezogenen Tags** wie `prerelease`,
  `datestamped`, `promopack`.

**Kernregel:** Ein Foil-Treatment aus `promoTypes` **ersetzt** den generischen
`foil`-Eintrag — es kommt nicht zusätzlich dazu.

```
finishes:   ["nonfoil", "foil"]
promoTypes: ["surgefoil"]
--------------------------------
ergibt:     ["nonfoil", "surgefoil"]      # nicht drei Einträge!
```

## 3. Foil-Treatments erkennen (regelbasiert, nicht per Whitelist)

Eine feste Liste veraltet sofort — praktisch jedes neue Set bringt ein neues Foil mit.
Deshalb per Regel. Ein `promoType` ist ein Foil-Treatment, wenn **eine** davon zutrifft:

1. Er **endet auf** `foil`
   → `surgefoil`, `galaxyfoil`, `ripplefoil`, `halofoil`, `manafoil`, `confettifoil`,
     `dragonscalefoil`, `chocobotrackfoil`, `firstplacefoil`, …
2. Er **beginnt mit** `neonink`
   → Kamigawa-Neon-Ink-Varianten
3. Er steht in einer kleinen **Ausnahmeliste**, weil der Name der Konvention nicht folgt:
   ```
   doublerainbow, embossed, gilded, glossy, invisibleink,
   metal, oilslick, stepandcompleat, textured
   ```

Alles andere (`prerelease`, `datestamped`, `promopack`, …) wird ignoriert.

> ⚠️ **Stolperfalle:** Der MTGJSON-Key heißt **`textured`**, nicht `texturedfoil`.
> Deshalb muss er in die Ausnahmeliste, sonst fällt "Textured Foil" durchs Raster.

## 4. Die Ausnahmen, die wehtun

### 4.1 `raisedfoil` ist keine eigene Foil-Art
*Phyrexia: All Will Be One* liefert `["oilslick", "raisedfoil"]` für das, was physisch
**ein** Finish ist (Oil Slick Raised Foil). `raisedfoil` beschreibt nur die Textur eines
anderen Treatments.

**Regel:** Kommen mehrere Treatments zusammen, fliegt `raisedfoil` raus.
Steht es **allein**, ist es ein echtes Finish (42 Karten).

### 4.2 `neonink` begleitet immer seine Farbvariante
`["neonink", "neoninkyellow"]` → das generische `neonink` fliegt raus,
`neoninkyellow` bleibt. Gleiche Regel wie bei `raisedfoil`.

### 4.3 Echte Mehrfach-Kombinationen
Bleiben nach dem Aufräumen immer noch mehrere übrig: sortieren und mit `+` verbinden.
Betrifft weltweit fast nichts (genau 2 Karten:
`neoninkmulticolor+neoninkrainbow+neoninkthreecolor`). Wichtig ist nur, **nicht daran zu
crashen** und keinen der Werte still zu verlieren.

### 4.4 `signed` und `etched` laufen an der Logik vorbei
Beide stehen direkt in `finishes`, **nicht** in `promoTypes` — die promoType-Regeln
greifen dort also gar nicht.

### 4.5 Pseudocode der kompletten Ableitung

```
effectiveFinishes(card):
    finishes   = card.finishes ?? []
    treatments = card.promoTypes.filter(isFoilTreatment)

    if treatments is empty:
        return finishes                      # nichts zu tun

    if treatments.count > 1:
        treatments -= ["raisedfoil", "neonink"]     # reine Modifikatoren

    if treatments.count > 1:
        treatments = [ sort(treatments).join("+") ] # unauflösbare Kombi

    finishes -= ["foil"]                     # Treatment ersetzt generisches foil
    return finishes + treatments
```

## 5. Die ★-Konvention (für Scan-Apps der wichtigste Punkt)

Manche Karten existieren als Print **gar nicht in nonfoil**. Die Foil-Version ist dann ein
**eigener Datensatz mit eigener Sammlernummer**, gekennzeichnet durch ein **★ am Ende der
Nummer** — z. B. `9ed 213★`. In der Referenz-DB sind das **1.960 Karten**, praktisch alle
mit `finishes: ["foil"]`.

Konsequenzen:

- Wer eine Foil-Karte scannt und stumpf auf "Set + Nummer" matcht, landet auf der
  **Nonfoil-Zeile** und bucht die Foil-Menge auf den falschen Datensatz.
- Die Nummernsuche sollte deshalb **immer beide Varianten** matchen (`213` *und* `213★`),
  damit niemand das Sternchen tippen muss.
- **Führende Nullen vorher abschneiden** — Scanner und CSV-Exporte liefern gern `012`.
- Beim Import sollte eine Foil-Menge automatisch auf die ★-Zeile wandern, wenn die
  Basiskarte selbst kein Foil kennt.

**Beide Extreme sind Alltag, keine Randfälle:**

| Fall | Karten |
|---|---|
| Prints ganz **ohne** nonfoil | 13.009 |
| Prints ganz **ohne** foil | 37.128 |
| Prints mit mehr als 2 Finishes | 245 |
| Karten mit ★-Nummer | 1.960 |

## 6. Datenmodell-Empfehlung

**Nicht** `nonFoilQuantity` / `foilQuantity` als zwei Spalten — das skaliert nicht auf
37 Finishes und war der ursprüngliche Konstruktionsfehler.

Stattdessen **eine Zeile pro Kombination**:

```
(user, card, language, finish) → quantity        ← unique constraint
```

- `finish` ist ein String mit Default `nonfoil`.
- Auf der Karte selbst wird die **Liste der verfügbaren** Finishes gespeichert
  (JSON-Spalte), damit die UI nur anbietet, was es wirklich gibt.
- Der **Sprach-Anteil ist bei internationalen Karten essenziell**: Dieselbe Karte in
  DE-nonfoil und JP-surgefoil sind zwei völlig eigenständige Einträge.

## 7. Zuordnung beim Scannen

> **Der Knackpunkt:** Ein Scanner erkennt physisch bestenfalls "glänzt / glänzt nicht" —
> **niemals** "surgefoil vs. galaxyfoil". Die konkrete Foil-Art ergibt sich **nicht aus dem
> Bild**, sondern aus **Set + Nummer**.

Ablauf:

1. Karte identifizieren → **Set + Nummer + Sprache**
2. Nachschlagen, **welche Finishes es für diesen Print überhaupt gibt**
3. Erst dann zuordnen:

**Nicht-foil gescannt:**
- Enthält die Liste `nonfoil`? → `nonfoil` nehmen.
- Hat der Print **genau ein** Finish (z. B. reine Foil-Promo)? → die Karte kann physisch
  nur dieses sein, also dieses nehmen — auch wenn es ein Foil ist.

**Foil gescannt:**
- Alle Nicht-`nonfoil`-Finishes betrachten.
- Bleibt **genau eines** übrig → eindeutig zuordnen. *So wird aus "foil" automatisch
  korrekt `surgefoil`.*
- Bleiben **mehrere** oder ist das generische `foil` dabei → `foil` als Default,
  hier muss der Nutzer entscheiden.
- Bleibt **keines** übrig (Print kennt kein Foil) → auf die **★-Variante** ausweichen.

**Der große Gewinn:** In der überwiegenden Mehrheit der Fälle ist die Zuordnung
**eindeutig ableitbar**, ohne den Nutzer zu fragen. Nur bei echten Mehrdeutigkeiten
(meist Secret Lair und Collector Boosters) braucht es eine Rückfrage.

Genau diese Unterscheidung machen bestehende Tools nicht — dort fallen alle Spezial-Foils
auf ein pauschales "foil" zusammen, und die Sprache wird oft ganz ignoriert.
