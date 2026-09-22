<?php

namespace Viewi\UI\Components\Navigation;

/**
 * Finding a node in a tree by typing, the same way everywhere (the folder panel, the folder
 * picker):
 *
 *  - a plain word matches a node whose name contains it: "q4";
 *  - a term with a slash is a PATH: "campaigns/q4" is a node matching "q4" under one matching
 *    "campaigns", gaps allowed ("campaigns/email" finds Campaigns / Q4 / Email), and a leading
 *    slash anchors the first segment to the top level ("/campaigns").
 *
 * Case-insensitive. `$path` is the node's full path with "/" between names ("Campaigns / Q4");
 * spaces around the slashes do not matter.
 */
class TreeSearch
{
    public static function matches(string $path, string $name, string $term): bool
    {
        $term = strtolower(trim($term));
        if ($term === '') {
            return true;
        }
        $name = strtolower($name);
        if (strpos($term, '/') === false) {
            return strpos($name, $term) !== false;
        }
        $anchored = strpos($term, '/') === 0;
        $segments = [];
        foreach (explode('/', $term) as $segment) {
            $segment = trim($segment);
            if ($segment !== '') {
                $segments[] = $segment;
            }
        }
        $wanted = count($segments);
        if ($wanted === 0) {
            return false;
        }
        $index = 0;
        $position = 0;
        foreach (explode('/', strtolower($path)) as $part) {
            $part = trim($part);
            if ($index === 0 && $anchored && $position > 0) {
                return false; // the first segment has to be a top-level node
            }
            if ($index < $wanted && strpos($part, $segments[$index]) !== false) {
                $index++;
            }
            $position++;
        }
        // Every segment matched, and the LAST one matched this node rather than an ancestor.
        return $index === $wanted && strpos($name, $segments[$wanted - 1]) !== false;
    }
}
