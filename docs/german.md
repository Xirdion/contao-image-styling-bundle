# Beschreibung

Bei der Verwendung der Lazy-Loading-Technik für Bilder kann es zur sogenannten [kumulativen Layout-Verschiebung](https://web.dev/cls/) kommen.
Dabei wird bereits der Inhalt einer Seite angezeigt, während im Hintergrund die Bilder erst nachgeladen werden.
Sind die Bilder nun geladen und werden angezeigt, kann sich der Inhalt der Webseite verschieben.
Dieses Phänomen verhindert das Bundle.

Dies wird ermöglicht, indem für jedes Bild bzw. dem dazugehörigen Image-Container (`figure`) ein CSS-Snippet hinzugefügt wird.
Das Styling enthält ein `::before` Element mit der richtigen **Proportion** als `padding-top`, welches für jeden konfigurierten Media Query der Bildgrößen aus der Breite und der Höhe errechnet wird.

```css
@media only screen and (min-width:768px) {
    .image_container--1::before {
        padding-top: 74.29%
    }
    .image_container--1 {
        width: 385px
    }
}
@media only screen and (max-width:767px) {
    .image_container--1::before {
        padding-top: 74.24%
    }
    .image_container--1 {
        width: 295px
    }
}
```

Das Bild-Element selber kann nun `absolute` innerhalb des Image-Containers platziert werden. Dadurch wird der benötigte Platz bereits von Anfang an für das Bild reserviert.

---

# Kompatibilität mit Contao 5.0 und 4.13

Mit Version 2.0 ist die Erweiterung sowohl kompatibel mit Contao 4.13 und auch 5.0.
Da ab Version 5.0 viele Inhaltselemente auf Twig-Templates umgestellt wurden, wurde eine **Twig-Extension** geschrieben, welche über das Figure-Objekt (`Contao\CoreBundle\Image\Studio\Figure`) die Berechnung der Styles durchführt.
Das `_figure-Template` wird dabei so erweitert, dass zum einen die neue **Twig-Funktion** aufgerufen wird und zum anderen die zusätzliche CSS-Klasse zu den `figure-Attributen` hinzugefügt wird.

---

# CSS Styling

Folgendes CSS-Styling muss manuell integriert werden.

Das Bundle kümmert sich nur um das Hinzufügen der richtigen Proportionen. Für die richtige Ausgabe muss das eigene Styling angepasst werden.

```css
figure {
	display: block;
	position: relative;
	max-width: 100%;
}

figure::before {
	content:  "";
	display: block;
}

figure img {
	width: 100%;
	height: auto;
	display: block;
	position: absolute;
	top: 0;
	left: 0;
}
```

---

# Lazy Loading

Das Nachladen der Bilder kann via HTML Attribut `loading="lazy"` oder aber über eine JS-Library umgesetzt werden. Hierzu ein [Blogpost von Google](https://web.dev/lazy-loading-images/ "Hierzu ein Blogpost von Google").

---

# Bildgrößen / Responsive Images

Diese Funktionalität bietet Contao bereits seit langer Zeit und wurde auch stetig erweitert und verbessert.

Von @ausi wurde hierzu ein sehr guter Artikel geschrieben:
[Responsive Images und das Picture Element in Contao verwenden](https://rocksolidthemes.com/de/contao/blog/responsive-images-picture-contao "Responsive Images und das Picture Element in Contao verwenden")

Seit [Conato 4.8.0](https://contao.org/de/news/contao_4-8-0.html "Conato 4.8.0") können Bildgrößen auch über eine .yaml Datei konfiguriert werden.
[Contao Dokumentation - image-sizes](https://docs.contao.org/dev/framework/image-processing/image-sizes/ "Contao Dokumentation")

---

# Verwendung mit RockSolid Custom Element

Code-Snippet um die Ausgabe von Bildern über die Contao Templates laufen zu lassen und somit die Funktionen des Bundles zu verwenden.

```php
// Prepare some data for the image creation
$imgSrc = $this->singleSRC;
$imgSizes = $this->size;
$imgAttributes = [
    'fullsize' => true,
    'alt' => 'alternative text'
];

// Generate an image object with the getImageObject function provided by the bundle
$image = $this->getImageObject($imgSrc, $imgSizes, null, null, $imgAttributes);
if (null !== $image) {
    // Extending the image object with some additional information
    $image->type = 'element_type';
    
    // Converting the \stdClass object to an associative array
    try {
        $imgData = json_decode(json_encode($image, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR)
    } catch (\JsonException $exception) {
        $imgData = null;
    }
    
    // Inserting the image into the template
    if (null !== $imgData) {
        $this->insert('image', $imgData);
    }
}
```
