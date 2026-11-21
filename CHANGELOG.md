# Änderungen

## 1.7.1

- Neue Ansicht **Weitere Plugins** auf der Seite **MGD Login → Info und Updates**: große Karten mit Bildern, Kategorien, Suche, Filter und ein Fenster mit Details
- Mit dem Knopf „Installieren“ kannst Du ein anderes MGD-Plugin direkt installieren. Es wird dabei nicht eingeschaltet
- Die alte Seite „Weitere Plugins“ ist jetzt ganz entfernt (Dateien gelöscht)
- Keine Änderung an der Login-Seite. Deine Einstellungen bleiben erhalten

## 1.7.0

- Umstellung auf den gemeinsamen MGD-Baukasten: Updates von theme.michael-gahn.de mit Prüfsumme, Anmeldung der Installation, Liste „Weitere Plugins“
- Neue Seite **MGD Login → Info und Updates** mit Version, Update-Stand, weiteren Plugins und dem Schalter „Installation melden“
- Der Schalter „Installation melden“ ist von der Einstellungsseite auf die Seite „Info und Updates“ umgezogen
- Beim Löschen des Plugins meldet es sich bei theme.michael-gahn.de ab
- Alte Update-Prüfung und alte Seite „Weitere Plugins“ sind abgeschaltet. Alte Zwischenspeicher werden einmal aufgeräumt
- Keine Änderung am Aussehen der Login-Seite. Deine Einstellungen bleiben erhalten

## 1.6.0

- 8 Layouts, alle überarbeitet und deutlich unterschiedlicher: Nordlicht (neu gestaltet), Milchglas, Neon Grid, Minimal Clean, Split Screen, Partikel-Netz, Sunset und neu **Kosmos**
- Neue Effekte, frei wählbar: Partikel (Sterne, Schnee, Glühwürmchen, Seifenblasen, Punktenetz, Sternenflug, Konfetti), Menge der Partikel, Lichtkegel folgt der Maus, Karten-Effekt (3D-Neigung, kreisender Leuchtrand, Glanz-Streifen), Position der Karte (Mitte, links, rechts)
- Minimal Clean und Kosmos zeigen auf großen Bildschirmen einen großen Titel neben der Karte
- Alle Effekte laufen lokal, ohne fremde Dateien. Bei „Bewegung reduzieren“ oder ausgeschalteter Animation steht alles still

## 1.5.0

- Neu: Das Plugin meldet sich höchstens einmal am Tag bei theme.michael-gahn.de. Gesendet werden nur Plugin-Name, Version, Shopsystem, Domain und eine zufällige Installations-ID
- Der Schalter ist ab Werk an und jederzeit abschaltbar unter **MGD Login → Installation melden**. Beim Abschalten wird der Eintrag auf theme.michael-gahn.de gelöscht
- Das Plugin funktioniert immer voll weiter, auch ohne die Plattform oder mit ausgeschaltetem Schalter
- Neue Datei `readme.txt` mit Kurzbeschreibung
- Neue Tests (`php tests/install.php`)

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
