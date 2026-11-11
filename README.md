# MGD WordPress Login

Ein richtig schöner Login für WordPress. Du wählst ein Template, passt Farben, Logo und Hintergrund an und siehst das Ergebnis sofort live. Ohne externe Dienste, ohne Schriften-Download, ohne Tracking.

> Stand: Version 0.1.4 (Vorabversion). Getestet mit WordPress 6.x und PHP 8.5.

## Was kann das Plugin?

- 7 kostenlose Templates mit Animation
- Eigene Farben, eigenes Logo, eigenes Hintergrundbild
- Live-Vorschau im Backend (Desktop, Tablet, Handy)
- Eigene Texte (Überschrift, Text darunter, Fußzeile)
- Animationen ein- und ausschaltbar, Tempo einstellbar
- Wer in seinem Gerät „Bewegung reduzieren“ aktiviert hat, sieht keine Animation
- Kontrast-Warnung, wenn Text schwer lesbar wäre
- Später: kostenpflichtige Pro-Templates von [theme.michael-gahn.de](https://theme.michael-gahn.de)

## Die kostenlosen Templates

| Template | Stil |
|---|---|
| Aurora | dunkel, leuchtende Farbwolken, Milchglas-Karte |
| Milchglas | hell, bunte Kugeln hinter Glasscheibe |
| Neon Grid | dunkel, laufendes Raster, pulsierende Neon-Karte |
| Minimal Clean | hell, ruhig, weiches Licht |
| Split Screen | links Marke mit Bewegung, rechts Formular |
| Partikel-Netz | dunkel, Punktenetz reagiert auf die Maus |
| Sunset | warmer Farbverlauf mit atmender Sonne |

## Installation (Schritt für Schritt)

1. ZIP-Datei `mgd-wordpress-login-0.1.4.zip` aus den [Releases](https://github.com/MichaelGahnDESIGN/MGD-WordPress-Login/releases) laden.
2. In WordPress: **Plugins → Installieren → Plugin hochladen**.
3. ZIP auswählen, **Installieren**, dann **Aktivieren**.
4. Links im Menü erscheint **MGD Login**. Dort geht es los.

## So benutzt du es

1. **Template wählen**: Klick auf eine Karte. Rechts siehst du sofort die Vorschau.
2. **Farben**: Leer lassen heißt „Farbe des Templates“. Nur ändern, was du willst.
3. **Logo und Hintergrund**: Bild aus der Mediathek wählen. Ohne Logo steht der Name deiner Website da.
4. **Karte und Schrift**: Rundung, Deckkraft, Glas-Unschärfe, Breite.
5. **Texte**: Zum Beispiel „Willkommen zurück“.
6. **Speichern** nicht vergessen. Erst dann sehen es alle auf der echten Login-Seite.

Mit **Login-Seite öffnen** (oben) prüfst du das Ergebnis auf `wp-login.php`.

Wenn etwas komisch aussieht: Button **Farben und Maße auf Template-Standard** drücken und speichern.

## Wo wirkt das Design?

Auf allen Standard-Seiten von WordPress: Anmelden, Passwort vergessen, Passwort zurücksetzen, Registrieren. Es ändert nur das Aussehen. Die Anmeldung selbst bleibt die von WordPress.

## Updates

So bekommst du neue Versionen:

1. WordPress fragt alle paar Stunden bei theme.michael-gahn.de nach, ob es eine neue Version gibt.
2. Gibt es eine, steht sie wie gewohnt unter **Dashboard → Aktualisierungen** und bei **Plugins**.
3. Du klickst selbst auf **Jetzt aktualisieren**. Automatisch passiert nichts.
4. Vor dem Einspielen prüft das Plugin die Datei mit einer Prüfsumme (einem digitalen Fingerabdruck). Passt sie nicht, wird **nichts** installiert.

Gut zu wissen:

- Die ZIP-Datei kommt von GitHub, der Fingerabdruck von theme.michael-gahn.de. Beides muss zusammenpassen.
- Ist theme.michael-gahn.de mal nicht erreichbar, siehst du einfach kein Update. Deine Seite und der Login laufen normal weiter.
- Sofort nachsehen: Button **MGD-Updates jetzt prüfen** unter Dashboard → Aktualisierungen.
- Ein ZIP selbst hochladen (Plugins → Installieren → Plugin hochladen) geht weiterhin.
- Ein Download-Link zu einer älteren Version auf GitHub wird bei aktivem Plugin abgelehnt. Ältere Versionen spielst du über **Plugin hochladen** ein.
- Von Version 0.1.1 auf 0.1.2 läuft das Update noch über den alten Weg. Ab 0.1.2 gilt der neue Weg.

## Datenschutz

- Keine externen Schriften, keine CDN-Skripte, keine Tracker
- Alles läuft lokal von deinem Server
- Nur für Updates fragt WordPress bei theme.michael-gahn.de und GitHub an. Dabei sehen die Server die Adresse (IP) deines Servers.
- Beim Löschen des Plugins bleiben Einstellungen erhalten, außer du hakst „Einstellungen beim Löschen entfernen“ an

## Eigene Templates oder Pro-Templates (für Entwickler)

Siehe [docs/PRO-TEMPLATES.md](docs/PRO-TEMPLATES.md). Kurz: Ein Zusatz-Plugin trägt Templates per Filter `mgd_login_templates` ein und schaltet sie per `mgd_login_pro_unlocked` frei.

## Entwickeln

```bash
php tests/settings.php           # schneller Test ohne WordPress
php tests/updater.php            # Test Update-Weg und Prüfsumme
python3 scripts/build-package.py # ZIP bauen nach artifacts/
```

## Lizenz

GPL-2.0-or-later. Von Michael Gahn DESIGN.
