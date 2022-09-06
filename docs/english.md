# Description

When using the lazy loading technique for images, the so-called [cumulative layout shift](https://web.dev/cls/) can occur. 
In this case, the content of a page is already displayed while the images are being loaded in the background. 
If the images are now loaded and displayed, the content of the web page can shift. 
This phenomenon is prevented by the bundle.

This is made possible by adding a CSS snippet for each image or the associated image container (`figure`). 
The styling contains a `::before` element with the correct **proportion** as `padding-top`, which is calculated for each configured media query of the image sizes from the width and the height.

```css
@media only screen and (min-width:768px) {
    .image_container_xxx_element_type::before {
        padding-top: 74.29%
    }
    .image_container_xxx_element_type {
        width: 385px
    }
}
@media only screen and (max-width:767px) {
    .image_container_xxx_element_type::before {
        padding-top: 74.24%
    }
    .image_container_xxx_element_type {
        width: 295px
    }
}
```

The image element itself can now be placed `absolutely` within the image container. This means that the required space is reserved for the image right from the start.

---

# Compatibility with Contao 5.0 and 4.13

With version 2.0, the extension is compatible with both Contao 4.13 and 5.0. 
Since many content elements have been switched to Twig templates as of version 5.0, a **Twig extension** was written, which performs the calculation of the styles via the figure object (`Contao\CoreBundle\Image\Studio\Figure`). 
The `_figure` template is extended so that the new **Twig function** is called and the additional CSS class is added to the `figure attributes`.

---

# CSS Styling

The following CSS styling must be integrated manually.

The bundle only takes care of adding the correct proportions. Custom styling must be adjusted for proper output.

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

The reloading of the images can be implemented via HTML attribute `loading="lazy"` or via a JS library. Here is a [blog post from Google](https://web.dev/lazy-loading-images/ "Hierzu ein Blogpost von Google").

---

# Responsive Images

This functionality has been offered by Contao for a long time and has been continuously extended and improved.

@ausi wrote a very good article about this:
[Responsive Images und das Picture Element in Contao verwenden](https://rocksolidthemes.com/de/contao/blog/responsive-images-picture-contao "Responsive Images und das Picture Element in Contao verwenden").

Since [Conato 4.8.0](https://contao.org/de/news/contao_4-8-0.html "Conato 4.8.0"), image sizes can also be configured via a .yml file.
[Contao documentation - image-sizes](https://docs.contao.org/dev/framework/image-processing/image-sizes/ "Contao Dokumentation")

---

# Usage with RockSolid Custom Element

Code snippet to run the output of images via the Contao templates and thus use the functions of the bundle.

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
