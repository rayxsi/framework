<?php

namespace Artificers\Routing;

class RouteGroup {

    /**
     * @param array $newProps
     * @param array $oldProps
     * @param bool $prependWithExistingPrefix
     * @return array
     */
    public static function mergeProps(array $newProps, array $oldProps, bool $prependWithExistingPrefix = true): array {
        if(isset($newProps['action'])) {
            unset($oldProps['action']);
        }

        return array_merge(static::formatName($newProps, $oldProps), [
            'prefix'=>static::formatPrefix($newProps, $oldProps, $prependWithExistingPrefix),
            'where'=>static::formatWhereClause($newProps, $oldProps),
            'middleware'=>static::formatMiddleware($newProps, $oldProps)
        ]);
    }

    /**
     * @param $newProps
     * @param $oldProps
     * @return array
     */
    private static function formatName($newProps, $oldProps): array {
        if(isset($oldProps['name'])) {
            $newProps['name'] = trim($oldProps['name'].'@'.($newProps['name'] ?? ''));
        }

        return $newProps;
    }

    /**
     * @param array $newProps
     * @param array $oldProps
     * @param $prependWithExistingPrefix
     * @return string
     */
    private static function formatPrefix(array $newProps, array $oldProps, $prependWithExistingPrefix): string {
        $oldPrefix = $oldProps['prefix'] ?? '';

        if($prependWithExistingPrefix) {
            return isset($newProps['prefix']) ? trim($oldPrefix, '/').'/'.trim($newProps['prefix']) : trim($oldPrefix);
        }

        return isset($newProps['prefix']) ? trim($newProps['prefix']).'/'.trim($oldPrefix, '/') : trim($oldPrefix);
    }

    /**
     * @param array $newProps
     * @param array $oldProps
     * @return array
     */
    private static function formatWhereClause(array $newProps, array $oldProps): array {
        return array_merge($newProps['where'] ?? [], $oldProps['where'] ?? []);
    }

    private static function formatMiddleware(array $newProps, array $oldProps): array {
        return array_merge((array)($newProps['middleware'] ?? []), (array)($oldProps['middleware'] ?? []));
    }
}