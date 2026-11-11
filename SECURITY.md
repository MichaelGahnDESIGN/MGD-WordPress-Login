# Sicherheit

Einstellungen speichern und die Live-Vorschau brauchen das Recht `manage_options`. Die Vorschau prüft zusätzlich einen Nonce. Alle Werte werden beim Speichern bereinigt (Farben, Zahlen, Bild-IDs, Texte, eigenes CSS ohne Tags und @import).

## Updates

- Neue Versionen werden nur angeboten, wenn die Prüfdatei von `https://theme.michael-gahn.de/plugin-updates/mgd-wordpress-login.json` gültig ist. Produkt, Version, Dateiname und Download-Adresse müssen genau passen.
- Die ZIP-Datei wird nur von `github.com` geladen. Weiterleitungen sind nur auf `objects.githubusercontent.com` und `release-assets.githubusercontent.com` erlaubt, immer per HTTPS.
- Vor der Installation werden Größe und SHA-256 geprüft. Passt etwas nicht, wird die Datei gelöscht und nichts installiert.
- Ohne erreichbare Prüfdatei gibt es kein Update und keinen Rückfall auf ungeprüfte Downloads.
- Keine automatischen Updates.

Sicherheitslücken bitte nicht öffentlich melden, sondern per Mail an michaelgahndesign@gmail.com. Bitte keine Zugangsdaten in Issues veröffentlichen.
