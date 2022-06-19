<?php

namespace Artificers\Supports;

use ReflectionNamedType;
use ReflectionParameter;

class Reflector {
    /**
     *Return class name of a given parameter.
     *
     * @param ReflectionParameter $param
     * @return string|null
     */
    public static function getParamClassName(ReflectionParameter $param): string|null {
        //check parameter type is built in or not. If it is then return null. Because it is primitive type, and we have to handle it separately.
        $type = $param->getType();

        if(!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
            return null;
        }

        $name = $type->getName();
        //check type name. If it is self type or parent type hinted
        if(!is_null($class = $param->getDeclaringClass())) {
            if($name === "self") return $class->getName();
            if($name === "parent" && $parent = $class->getParentClass()) return $parent->getName();
        }

        return $name;
    }
}