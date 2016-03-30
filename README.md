# HTML Server Components compiler (in PHP)

**A brand new way to develop websites**

<p align="center">
<img src="http://ivopetkov.github.io/demos/html-server-components/poster.jpg" style="max-width:100%;">
</p>

This is the home of the compiler that demonstrates the power of HTML Server Components.

The details article is available at [http://ivopetkov.com/b/html-server-components/](http://ivopetkov.com/b/html-server-components/).

You can find a demo at [http://ivopetkov.github.io/demos/html-server-components/](http://ivopetkov.github.io/demos/html-server-components/). The source is also [there](https://github.com/ivopetkov/ivopetkov.github.io/tree/master/demos/html-server-components/).

## Download and install

* Install via Composer
```
php composer.phar require ivopetkov/html-server-components-compiler
```

* Download the zip file

Download the [latest release](https://github.com/ivopetkov/html-server-components-compiler/releases) from our GitHub page and include the following files.
```
include 'path/to/HTMLServerComponentsCompiler.php';
include 'path/to/HTMLServerComponent.php';
```
## Usage

* Construct the compiler
```
$compiler = new HTMLServerComponentsCompiler();
```

* Call the process method
```
// over file
echo $compiler->processFile('my-component.php')
// over HTML Code
echo $compiler->process('<component src="file:my-component.php"/>')
```

## License
Free to use under the [MIT license](http://opensource.org/licenses/MIT).

## Got questions?
You can find me at [@IvoPetkovCom](https://twitter.com/IvoPetkovCom) and [ivopetkov.com](http://ivopetkov.com)