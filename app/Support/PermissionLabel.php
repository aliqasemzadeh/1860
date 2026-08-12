<?php

namespace App\Support;

class PermissionLabel
{
    public static function for(string $name): string
    {
        $groups = __('permissions');

        if (! is_array($groups)) {
            return $name;
        }

        foreach (array_keys($groups) as $group) {
            $key = "permissions.{$group}.{$name}";
            $label = __($key);

            if ($label !== $key) {
                return $label;
            }
        }

        return $name;
    }

    public static function role(string $name): string
    {
        $key = "roles.{$name}";
        $label = __($key);

        return $label !== $key ? $label : $name;
    }
}
