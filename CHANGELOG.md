# Änderungen

## 1.4.0

- Versionsnummer an die anderen MGD Themes und Plugins angeglichen (fertige Version, keine Vorabversion)
- Keine Änderung an Funktionen oder Aussehen

## 0.1.5 (Vorabversion)

- Abstand oben: Auf kleineren Bildschirmen ist die Seite beim Öffnen nach unten gesprungen (WordPress setzt den Cursor ins Namensfeld). Jetzt bleibt die Seite oben und das Logo ist sichtbar. Der Cursor steht trotzdem im Feld

## 0.1.4 (Vorabversion)

- Logo: „Logo-Höhe“ wirkt jetzt auch bei breiten Logos. Das Logo darf dafür breiter als die Karte sein (höchstens 480 Pixel, auf dem Handy so breit wie der Bildschirm)
- Logo: SVG-Logos (Vektorgrafiken) werden richtig eingepasst
- Logo-Auswahl im Backend: Graues Karo-Muster statt weißem, damit auch weiße Logos sichtbar sind
- Neu: dezente Logo-Animation (leichtes Schweben und ab und zu ein Glanz). Aus, wenn Animationen aus sind oder im Gerät „Bewegung reduzieren“ an ist
- Abstand oben: Die Login-Box rutscht nicht mehr über den oberen Rand
- Hinweis im Backend, wenn ein anderes Login-Design-Plugin aktiv ist (zum Beispiel Login Designer)

## 0.1.3 (Vorabversion)

- Repariert: Die Dateien in der ZIP-Datei haben jetzt die richtigen Leserechte. Vorher konnte der Webserver nach dem Entpacken von Hand (zum Beispiel per SSH) die Design-Dateien nicht ausliefern
- Installation über „Plugin hochladen“ in WordPress war nicht betroffen
- Keine Änderung an Funktionen oder Aussehen

## 0.1.2 (Vorabversion)

- Neuer Update-Weg wie bei den anderen MGD Plugins: Die neue Version und ihr Fingerabdruck (SHA-256) kommen von theme.michael-gahn.de, die ZIP-Datei aus dem GitHub-Release
- Vor dem Einspielen wird die ZIP-Datei geprüft. Passt der Fingerabdruck nicht, wird nichts installiert
- Downloads nur über HTTPS und nur von GitHub-Adressen
- Keine automatischen Updates, du klickst selbst
- Ist theme.michael-gahn.de nicht erreichbar, wird einfach kein Update angezeigt
- Die alte Update-Prüfung direkt über GitHub ist für dieses Plugin abgeschaltet
- Neue Tests für den Update-Weg (`php tests/updater.php`)

## 0.1.1 (Vorabversion)

- Aufgeräumt: zwei übrig gebliebene Hilfsdateien vom Netzlaufwerk aus dem Projekt entfernt
- Keine Änderung an Funktionen oder Aussehen

## 0.1.0 (Vorabversion)

- Erste Version
- 7 kostenlose Templates: Aurora, Milchglas, Neon Grid, Minimal Clean, Split Screen, Partikel-Netz, Sunset
- Einstellungsseite mit Template-Galerie und Live-Vorschau
- Farben, Logo, Hintergrundbild, Schrift, Texte, Animationen, eigenes CSS
- Kontrast-Warnung und Rücksicht auf „Bewegung reduzieren“
- Schalter für spätere Pro-Templates (`mgd_login_templates`, `mgd_login_pro_unlocked`)
- MGD Update-Prüfung und Plugin-Kollektion wie bei den anderen MGD Plugins
