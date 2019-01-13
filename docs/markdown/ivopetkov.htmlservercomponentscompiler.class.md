# IvoPetkov\HTMLServerComponentsCompiler

HTML Server Components compiler. Converts components code into HTML code.

## Methods

##### public void [addAlias](ivopetkov.htmlservercomponentscompiler.addalias.method.md) ( string $alias , string $original )

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Adds an alias.

##### public void [addTag](ivopetkov.htmlservercomponentscompiler.addtag.method.md) ( string $tagName , string $src )

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Defines a new tag.

##### public [IvoPetkov\HTMLServerComponent](ivopetkov.htmlservercomponent.class.md) [makeComponent](ivopetkov.htmlservercomponentscompiler.makecomponent.method.md) ( [ array $attributes = [] [, string $innerHTML = '' [, string $tagName = 'component' ]]] )

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Constructs a component object.

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Returns: A component object.

##### public string [process](ivopetkov.htmlservercomponentscompiler.process.method.md) ( string|[IvoPetkov\HTMLServerComponent](ivopetkov.htmlservercomponent.class.md) $content [, array $options = [] ] )

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Converts components code (if any) into HTML code.

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Returns: The result HTML code.

## Details

File: /src/HTMLServerComponentsCompiler.php

---

[back to index](index.md)

