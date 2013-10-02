#wp_studyoslider

Outputs a wrapped list of slides for easy triggering your chosen slider library upon. This is a first version and by no means perfect nor complete. Includes german translation.

## Usage

Post fields:
* **Title**: Image alt attribute 
* **Content**: Caption
* **Featured Image**: Slide image
* **Custom Meta Box "Attributes":**
  * **Order**: Value for manually ordering the slides (ascending order)
  * **CSS Classes**: Will be added to the class attribute of the corresponding caption for styling and positioning by predefined css styles.

Use this function inside your template:

```php
studyo_slider_output($slug, $wrap_class = "flexslider", $ul_class = "slides", $caption_class = "flex-caption" );
```

* **$slug**: Slider Category
* **$wrap_class**: Add a class to the wrapping div. Default is "flexslider"
* **$ul_class**: Add a class to the ul. Default is "slides"
* **$caption_class**: Add a class to the caption div. Default is "flex-caption"

As you can see, the default values proceed of the assumption of using [Flexslider](http://www.woothemes.com/flexslider/), however there is *no slider library included*.

## License

MIT License
