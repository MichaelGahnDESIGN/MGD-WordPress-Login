# Pro-Templates und eigene Templates

Das Plugin ist so gebaut, dass später kostenpflichtige Templates von theme.michael-gahn.de als **Zusatz-Plugin** dazukommen. Das Hauptplugin bleibt frei.

## Wie funktioniert das?

1. Das Zusatz-Plugin meldet seine Templates über den Filter `mgd_login_templates` an.
2. Pro-Templates sind in der Galerie gesperrt, bis der Filter `mgd_login_pro_unlocked` den Wert `true` liefert (zum Beispiel nach einer gültigen Lizenz).
3. Ist ein Pro-Template gewählt, aber nicht freigeschaltet (Lizenz abgelaufen), zeigt der Login automatisch **Aurora**. Der Login geht also nie kaputt.

Die drei gesperrten Karten „Wellen 3D“, „Retro Terminal“ und „Parallax Skyline“ sind nur Platzhalter. Sobald ein Add-on ein Template mit derselben ID einträgt, verschwindet der Platzhalter.

## Beispiel

```php
add_filter( 'mgd_login_templates', function ( array $templates ): array {
    $templates['waves'] = array(
        'name' => 'Wellen 3D',
        'desc' => 'Tiefe Wellen mit Licht-Reflexen.',
        'tags' => array( 'Dunkel', 'Animiert' ),
        'tier' => 'pro',
        'css'  => plugins_url( 'templates/waves.css', __FILE__ ),
        'js'   => '', // optional
        'ver'  => '1.0.0',
        'vars' => array(
            'bg1' => '#052a4d', 'bg2' => '#0e7490', 'accent' => '#67e8f9',
            'card' => '#06213a', 'text' => '#f0fbff',
            'card_opacity' => 70, 'radius' => 18, 'blur' => 18,
            'card_width' => 400, 'overlay' => 0,
        ),
    );
    return $templates;
} );

// Nur wenn die Lizenz stimmt (Prüfung kommt vom Add-on)
add_filter( 'mgd_login_pro_unlocked', fn() => my_license_is_valid() );
```

## Was muss die CSS-Datei eines Templates können?

Sie bekommt diese Variablen vom Hauptplugin: `--mgd-bg1`, `--mgd-bg2`, `--mgd-accent`, `--mgd-card`, `--mgd-text`, `--mgd-speed`, `--mgd-radius`, `--mgd-blur`, `--mgd-card-width` und weitere. Die Basis (Formular, Buttons, Links, Fehlermeldungen) ist schon gestylt. Das Template liefert nur Hintergrund und Bewegung.

- Hintergrund-Ebene: `.mgd-login-bg` mit drei leeren `span`-Elementen zum Animieren
- Body-Klasse: `mgd-t-<id>`
- Animationsdauer immer so: `calc(20s / var(--mgd-speed))`, damit der Tempo-Regler wirkt
- Selektoren mit `body.mgd-t-<id> …` beginnen, sonst gewinnt die Basis
- Immer `prefers-reduced-motion` respektieren (macht die Basis schon für alle Animationen)

## ⚠️ Wichtig vor dem Verkauf

Die Lizenzprüfung gehört in das Add-on und auf den Server von theme.michael-gahn.de, nicht in dieses freie Plugin. Hier gibt es bewusst nur die Schalter.
