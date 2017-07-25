<?php

$additionalIndents = '###';

require __DIR__ . '/vendor/autoload.php';

if (!is_file('composer.json')) {
    echo 'composer.json not found!';
    exit;
}

$composerData = json_decode(file_get_contents('composer.json'), true);
if (!is_array($composerData)) {
    echo 'composer.json file content is not valid json!';
    exit;
}

$classNames = [];
if (isset($composerData['autoload'])) {
    foreach ($composerData['autoload'] as $autoloadTypeKey => $autoloadTypeData) {
        if ($autoloadTypeKey === 'psr-4') {
            foreach ($autoloadTypeData as $namespacePrefix => $filesDir) {
                $files = getFiles($filesDir);
                foreach ($files as $file) {
                    $className = str_replace(['.php', '/'], ['', '\\'], substr($file, strlen($filesDir)));
                    if (strpos($className, '\\Internal\\') !== false) {
                        continue;
                    }
                    $classNames[] = $namespacePrefix . $className;
                }
            }
        }
    }
}
sort($classNames);

$getValue = function($value) {
    if (is_string($value)) {
        return '\'' . str_replace('\'', '\\\'', $value) . '\'';
    }
    return json_encode($value);
};

$getType = function($type) {
//        if ($type !== 'void' && $type !== 'string' && $type !== 'int' && $type !== 'boolean' && $type !== 'array') {
//            return '<a href="xxxxxxxxxx">' . $type . '</a>';
//        }
    return $type;
};

$getMethod = function($method) use($getValue, $getType) {
    $result = '';
    $keywords = [];
    if ($method['isStatic']) {
        $keywords[] = 'static';
    }
    if ($method['isPublic']) {
        $keywords[] = 'public';
    }
    if ($method['isProtected']) {
        $keywords[] = 'protected';
    }
    if ($method['isPrivate']) {
        $keywords[] = 'private';
    }
    if ($method['isAbstract']) {
        $keywords[] = 'abstract';
    }
    if ($method['isFinal']) {
        $keywords[] = 'final';
    }

    if (empty($method['parameters'])) {
        $parameters = 'void';
    } else {
        $parameters = '';
        foreach ($method['parameters'] as $parameter) {
            if ($parameter['isOptional']) {
                $parameters .= ' [, ';
            } else {
                $parameters .= ' , ';
            }
            $parameters .= $getType($parameter['type']) . ' $' . $parameter['name'] . ($parameter['value'] !== null ? ' = ' . $getValue($parameter['value']) : '');
            if ($parameter['isOptional']) {
                $parameters .= ' ] ';
            }
        }
        $parameters = trim($parameters, ' ,');
        if (substr($parameters, 0, 2) === '[,') {
            $parameters = '[' . substr($parameters, 2);
        }
    }
    $returnType = isset($method['comment']['return']['type']) ? $method['comment']['return']['type'] : 'void';
    $result .= implode(' ', $keywords) . ($method['isConstructor'] || $method['isDestructor'] ? '' : ' ' . $getType($returnType)) . ' ' . $method['name'] . ' ( ' . $parameters . ' )' . "\n";
    return $result;
};

foreach ($classNames as $className) {
    $data = PHPClassParser::parse($className);

    $result = '';

    $result .= $additionalIndents . '# ' . $data['name'] . "\n";
    if (!empty($data['comment']['description'])) {
        $result .= $data['comment']['description'] . "\n\n";
    }

    //$result .= $data['name'] . " {\n\n";

    if (!empty($data['constants'])) {
        $result .= $additionalIndents . '## Constants' . "\n\n";
        foreach ($data['constants'] as $constant) {
            $result .= "`" . 'const ' . $constant['type'] . ' ' . $constant['name'] . "`"; // . ' = ' . $getValue($constant['value'])
            if (!empty($constant['comment']['description'])) {
                $result .= "\n\n&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $constant['comment']['description'];
            }
            $result .= "\n\n";
        }
    }

    $propertiesResult = '';
    if (!empty($data['properties'])) {
        foreach ($data['properties'] as $property) {
            if ($property['isPrivate']) {
                continue;
            }
            $keywords = [];
            if ($property['isStatic']) {
                $keywords[] = 'static';
            }
            if ($property['isPublic']) {
                $keywords[] = 'public';
            }
            if ($property['isProtected']) {
                $keywords[] = 'protected';
            }
            if ($property['isPrivate']) {
                $keywords[] = 'private';
            }
            $propertiesResult .= '`' . implode(' ', $keywords) . ' ' . $getType($property['type']) . ' $' . $property['name'] . "`"; // . ($property['value'] === null ? '' : ' = ' . $getValue($property['value']))
            if (!empty($property['comment']['description'])) {
                $propertiesResult .= "\n\n&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $property['comment']['description'];
            }
            $propertiesResult .= "\n\n";
        }
    }

    if (!empty($data['comment']['properties'])) {
        foreach ($data['comment']['properties'] as $property) {
            $propertiesResult .= '`public ' . $getType($property['type']) . ' $' . $property['name'] . "`";
            if (!empty($property['description'])) {
                $propertiesResult .= "\n\n&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $property['description'];
            }
            $propertiesResult .= "\n\n";
        }
    }
    if (!empty($propertiesResult)) {
        $result .= $additionalIndents . '## Properties' . "\n\n";
        $result .= $propertiesResult;
    }

    if (!empty($data['methods'])) {
        $methodsResult = '';
        foreach ($data['methods'] as $method) {
            if ($method['isPrivate']) {
                continue;
            }
            $methodsResult .= "```php\n" . trim($getMethod($method)) . "\n```";
            $methodsResult .= "\n\n";
            if (!empty($method['comment']['description'])) {
                $methodsResult .= $method['comment']['description'] . "\n\n";
            }

            if (!empty($method['parameters'])) {
                $methodsResult .= '_Parameters_' . "\n\n";
                foreach ($method['parameters'] as $i => $parameter) {
                    $methodsResult .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`$' . $parameter['name'] . '`';
                    if (!empty($method['comment']['parameters'][$i]['description'])) {
                        $methodsResult .= "\n\n&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $method['comment']['parameters'][$i]['description'];
                    }
                    $methodsResult .= "\n\n";
                }
            }
            $methodsResult .= '_Returns_' . "\n\n";
            $methodsResult .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            if (is_array($method['comment']['return'])) {
                $methodsResult .= $method['comment']['return']['description'];
            } else {
                $methodsResult .= 'No value is returned.';
            }

            $methodsResult .= "\n\n";
        }
        if (!empty($methodsResult)) {
            $result .= $additionalIndents . '## Methods' . "\n\n";
            $result .= $methodsResult;
        }
    }
    echo $result;
}

function getFiles($dirname)
{
    $files = scandir($dirname);
    $result = array();
    foreach ($files as $file) {
        if ($file === '.' || $file === '..' || $file === '.git' || substr($file, 0, 1) === '_') {
            continue;
        }
        if (is_dir($dirname . $file)) {
            $result = array_merge($result, getFiles($dirname . $file . '/'));
        } else {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $result[] = $dirname . $file;
            }
        }
    }
    return $result;
}

class PHPClassParser
{

    static function parse($class)
    {
        $result = [];
        $reflectionClass = new ReflectionClass($class);

        $result['name'] = $reflectionClass->name;
        $result['namespace'] = $reflectionClass->getNamespaceName();
        $result['comment'] = self::parseDocComment($reflectionClass->getDocComment());

        $result['constants'] = [];
        $constants = $reflectionClass->getConstants();
        foreach ($constants as $name => $value) {
            $result['constants'][] = [
                'name' => $name,
                'value' => $value,
                'type' => gettype($value)
            ];
        }

        $result['properties'] = [];
        $properties = $reflectionClass->getProperties();
        $defaultProperties = $reflectionClass->getDefaultProperties();
        foreach ($properties as $property) {
            $value = isset($defaultProperties[$property->name]) ? $defaultProperties[$property->name] : null;
            $comment = self::parseDocComment($property->getDocComment());
            $result['properties'][] = [
                'name' => $property->name,
                'value' => $value,
                'type' => $comment['type'] !== null ? $comment['type'] : gettype($value),
                'comment' => $comment,
                'isPrivate' => $property->isPrivate(),
                'isProtected' => $property->isProtected(),
                'isPublic' => $property->isPublic(),
                'isStatic' => $property->isStatic()
            ];
        }

        $result['methods'] = [];
        $methods = $reflectionClass->getMethods();
        foreach ($methods as $method) {
            $parameters = $method->getParameters();
            $resultParameters = [];
            $comment = self::parseDocComment($method->getDocComment());
            foreach ($parameters as $i => $parameter) {
                $value = null;
                $type = null;
                if (isset($parameter->hasType) && $parameter->hasType()) {
                    $type = (string) $parameter->getType();
                }
                if ($parameter->isOptional()) {
                    if ($parameter->isDefaultValueAvailable()) {
                        $value = $parameter->getDefaultValue();
                    }
                    if ($type === null) {
                        $type = gettype($value);
                    }
                }
                if (isset($comment['parameters'][$i]) && $comment['parameters'][$i]['name'] === $parameter->name) {
                    $type = $comment['parameters'][$i]['type'];
                }
                $resultParameters[] = [
                    'name' => $parameter->name,
                    'value' => $value,
                    'type' => $type,
                    'isOptional' => $parameter->isOptional(),
                ];
            }
            $result['methods'][] = [
                'name' => $method->name,
                'parameters' => $resultParameters,
                'comment' => $comment,
                'isPrivate' => $method->isPrivate(),
                'isProtected' => $method->isProtected(),
                'isPublic' => $method->isPublic(),
                'isStatic' => $method->isStatic(),
                'isAbstract' => $method->isAbstract(),
                'isFinal' => $method->isFinal(),
                'isConstructor' => $method->isConstructor(),
                'isDestructor' => $method->isDestructor()
            ];
        }

        return $result;
    }

    static function parseDocComment($comment)
    {
        $comment = trim($comment, "/* \n\r\t");
        $lines = explode("\n", $comment);
        $temp = [];
        foreach ($lines as $line) {
            $line = trim($line, " *");
            if (isset($line{0})) {
                $temp[] = $line;
            }
        }
        $lines = $temp;
        $result = [];
        $result['description'] = '';
        $result['type'] = null;
        $result['parameters'] = [];
        $result['return'] = null;
        $result['exceptions'] = [];
        $result['properties'] = [];
        if (isset($lines[0])) {
            $result['description'] = $lines[0][0] === '@' ? '' : $lines[0];
            foreach ($lines as $line) {
                if ($line[0] === '@') {
                    $lineParts = explode(' ', $line, 2);
                    $tag = trim($lineParts[0]);
                    $value = trim($lineParts[1]);
                    if ($tag === '@param') {
                        $valueParts = explode(' ', $value, 3);
                        $result['parameters'][] = [
                            'name' => isset($valueParts[1]) ? trim($valueParts[1], ' $') : null,
                            'type' => isset($valueParts[0]) ? trim($valueParts[0]) : null,
                            'description' => isset($valueParts[2]) ? trim($valueParts[2]) : null,
                        ];
                    } elseif ($tag === '@return') {
                        $valueParts = explode(' ', $value, 2);
                        $result['return'] = [
                            'type' => isset($valueParts[0]) ? trim($valueParts[0]) : null,
                            'description' => isset($valueParts[1]) ? trim($valueParts[1]) : null,
                        ];
                    } elseif ($tag === '@throws') {
                        $result['exceptions'][] = $value;
                    } elseif ($tag === '@var') {
                        $result['type'] = $value;
                    } elseif ($tag === '@property' || $tag === '@property-read' || $tag === '@property-write') {
                        $valueParts = explode(' ', $value, 3);
                        $result['properties'][] = [
                            'name' => isset($valueParts[1]) ? trim($valueParts[1], ' $') : null,
                            'type' => isset($valueParts[0]) ? trim($valueParts[0]) : null,
                            'description' => isset($valueParts[2]) ? trim($valueParts[2]) : null,
                        ];
                    }
                }
            }
            $result['exceptions'] = array_unique($result['exceptions']);
        }
        return $result;
    }

}
