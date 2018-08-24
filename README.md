# HTML Server Components compiler (in PHP)

**A brand new way to build websites**

It's a simple idea:

Instead of using template engines or PHP includes in your HTML code use the &lt;component&gt; tag. It allows you to:
- Include code from other files (`<component src="file:header.php"/>`).
- Pass arguments (`<component src="file:content.php" pageID="home"/>`) that are available in the file (`$component->getAttribute('pageID')` or just `$component->pageID`).
- Automatically places the included file HTML code into the proper places (Tags in the `head` go in the parent document `head`).
- Makes easy to test each component because each component outputs full HTML code.

[![Build Status](https://travis-ci.org/ivopetkov/html-server-components-compiler.svg)](https://travis-ci.org/ivopetkov/html-server-components-compiler)
[![Latest Stable Version](https://poser.pugx.org/ivopetkov/html-server-components-compiler/v/stable)](https://packagist.org/packages/ivopetkov/html-server-components-compiler)
[![codecov.io](https://codecov.io/github/ivopetkov/html-server-components-compiler/coverage.svg?branch=master)](https://codecov.io/github/ivopetkov/html-server-components-compiler?branch=master)
[![License](https://poser.pugx.org/ivopetkov/html-server-components-compiler/license)](https://packagist.org/packages/ivopetkov/html-server-components-compiler)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/a27773a51b56467f9e1c240c2a2bd2d0)](https://www.codacy.com/app/ivo_2/html-server-components-compiler)

## Install via Composer

```shell
composer require ivopetkov/html-server-components-compiler
```

## Usage

```php
$compiler = new IvoPetkov\HTMLServerComponentsCompiler();
echo $compiler->process('
<html>
    <body>
        <component src="file:header.php"/>
        <component src="file:content.php" pageID="home"/>
        <component src="file:footer.php"/>
    </body>
</html>
')
```


## Documentation

Full [documentation](https://github.com/ivopetkov/html-server-components-compiler/blob/master/docs/markdown/index.md) is avaliable as part of this repository.

## Demo

A demo is available at [http://ivopetkov.github.io/demos/html-server-components/](http://ivopetkov.github.io/demos/html-server-components/). The source is also [there](https://github.com/ivopetkov/ivopetkov.github.io/tree/master/demos/html-server-components/).

## License
This project is licensed under the MIT License. See the [license file](https://github.com/ivopetkov/html-server-components-compiler/blob/master/LICENSE) for more information.

## Contributing
Feel free to open new issues and contribute to the project. Let's make it awesome and let's do in a positive way.

## Author
This library is created and maintained by [Ivo Petkov](https://github.com/ivopetkov/) ([ivopetkov.com](https://ivopetkov.com)).
