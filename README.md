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

### Classes


#### IvoPetkov\HTMLServerComponent
Used to create the $component object that is passed to the corresponding file

##### Properties

`public array $attributes`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Component tag attributes

`public string $innerHTML`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Component tag innerHTML

##### Methods

```php
public string|null getAttribute ( string $name [, string|null $defaultValue ] )
```

Returns value of an attribute

_Parameters_

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`$name`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The name of the attribute

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`$defaultValue`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The default value of the attribute (if missing)

_Returns_

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The value of the attribute or the defaultValue specified

```php
public void setAttribute ( string $name , string $value )
```

Sets new value to the attribute specified

_Parameters_

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`$name`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The name of the attribute

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`$value`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The new value of the attribute

_Returns_

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;No value is returned

```php
public void removeAttribute ( string $name )
```

Removes attribute

_Parameters_

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`$name`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The name of the attribute

_Returns_

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;No value is returned

```php
public string|null __get ( string $name )
```

Provides acccess to the component attributes via properties

_Parameters_

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`$name`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The name of the attribute

_Returns_

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The value of the attribute or null if missing

```php
public void __set ( string $name , string $value )
```

Provides acccess to the component attributes via properties

_Parameters_

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`$name`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The name of the attribute

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`$value`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The new value of the attribute

_Returns_

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;No value is returned

```php
public boolean __isset ( string $name )
```

Provides acccess to the component attributes via properties

_Parameters_

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`$name`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The name of the attribute

_Returns_

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;TRUE if the attribute exists, FALSE otherwise

```php
public void __unset ( string $name )
```

Provides acccess to the component attributes via properties

_Parameters_

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`$name`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The name of the attribute

_Returns_

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;No value is returned

#### IvoPetkov\HTMLServerComponentsCompiler
HTML Server Components compiler. Converts components code into HTML code.

##### Constants

`const string VERSION`

##### Methods

```php
public void addAlias ( string $alias , string $original )
```

Adds an alias

_Parameters_

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`$alias`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The alias

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`$original`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The original source name

_Returns_

&nbsp;&nbsp;&nbsp;&nbsp;&nbsnbsp;&nbsp;No value is returned

```php
public string process ( string $content [, array $options = [] ] )
```

Converts components code (if any) into HTML code

_Parameters_

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`$content`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The content to be processed

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`$options`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Compiler options

_Returns_

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The result HTML code

```php
public string processData ( string $data [, array $options = [] ] )
```

Creates a component from the data specified and processes the content

_Parameters_

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`$data`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The data to be used as component content. Currently only base64 encoded data is allowed.

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`$options`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Compiler options

_Returns_

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The result HTML code

```php
public string processFile ( string $file [, array $attributes = [] ]  [, string $innerHTML = '' ]  [, array $variables = [] ]  [, array $options = [] ] )
```

Creates a component from the file specified and processes the content

_Parameters_

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`$file`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The file to be run as component

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`$attributes`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Component object attributes

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`$innerHTML`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Component object innerHTML

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`$variables`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;List of variables that will be passes to the file. They will be available in the file scope.

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`$options`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Compiler options

_Returns_

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The result HTML code

```php
protected \IvoPetkov\HTMLServerComponent constructComponent ( [ array $attributes = [] ]  [, string $innerHTML = '' ] )
```

Constructs a component object

_Parameters_

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`$attributes`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The attributes of the component object

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`$innerHTML`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The innerHTML of the component object

_Returns_

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;A component object

```php
protected string getComponentFileContent ( string $file , array $variables )
```

Includes a component file and returns its content

_Parameters_

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`$file`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The filename

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`$variables`

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;List of variables that will be passes to the file. They will be available in the file scope.

_Returns_

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The content of the file



## Demo

A demo is available at [http://ivopetkov.github.io/demos/html-server-components/](http://ivopetkov.github.io/demos/html-server-components/). The source is also [there](https://github.com/ivopetkov/ivopetkov.github.io/tree/master/demos/html-server-components/).

## License
HTML Server Components compiler is open-sourced software. It's free to use under the MIT license. See the [license file](https://github.com/ivopetkov/html-server-components-compiler/blob/master/LICENSE) for more information.

## Author
This library is created by Ivo Petkov. Feel free to contact me at [@IvoPetkovCom](https://twitter.com/IvoPetkovCom) or [ivopetkov.com](https://ivopetkov.com).