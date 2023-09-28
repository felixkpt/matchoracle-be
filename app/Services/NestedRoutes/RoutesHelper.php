<?php

namespace App\Services\NestedRoutes;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class RoutesHelper
{
    /**
     * Get nested routes from a specific folder.
     *
     * @param string $folder The folder name.
     * @return array An array containing information about the nested routes.
     */
    public static function getRoutes($nested_routes_folder, $leftTrim)
    {

        $routes_path = base_path('routes/' . $nested_routes_folder);

        if (file_exists($routes_path)) {

            $folder = $routes_path;
            $routes = self::getRoutesReal($folder, $nested_routes_folder, $routes_path, $leftTrim);

            $foldermin = self::getFolderAfterNested($folder, $nested_routes_folder);
            $item = [
                'folder' => $foldermin,
                'children' => [],
                'routes' => $routes,
                'hidden' => self::getHidden($foldermin),
                'icon' => self::getIcon($foldermin),
                'position' => self::getPosition($foldermin),
            ];

            $items = self::iterateFolders($folder, $nested_routes_folder, $routes_path, $leftTrim);

            array_unshift($items, $item);

            // Sort the items array based on position
            usort($items, function ($a, $b) {
                return $a['position'] - $b['position'];
            });


            // Sort the children of each folder based on position
            foreach ($items as &$item) {
                usort($item['children'], function ($a, $b) {
                    return $a['position'] - $b['position'];
                });
            }

            return $items;
        }

        return null;
    }

    private static function iterateFolders($folder, $nested_routes_folder, $routes_path, $leftTrim)
    {

        $items = [];
        $folders = File::directories($folder);

        foreach ($folders as $folder) {

            $routes = self::getRoutesReal($folder, $nested_routes_folder, $routes_path, $leftTrim);

            $foldermin = self::getFolderAfterNested($folder, $nested_routes_folder);

            $item = [
                'folder' => $foldermin,
                'children' => self::iterateFolders($folder, $nested_routes_folder, $routes_path, $leftTrim),
                'routes' => $routes,
                'hidden' => self::getHidden($foldermin),
                'icon' => self::getIcon($foldermin),
                'position' => self::getPosition($foldermin),
            ];

            $items[] = $item;
        }

        return $items;
    }

    static function getHidden($folder)
    {
        return Permission::where('name', $folder)->first()->hidden ?? null;
    }

    static function getIcon($folder)
    {
        return Permission::where('name', $folder)->first()->icon ?? null;
    }

    static function getPosition($folder)
    {
        return Permission::where('name', $folder)->first()->position ?? 999999;
    }

    static function getRoutesReal($folder, $nested_routes_folder, $routes_path, $leftTrim)
    {
        $items = [];
        // Filter out the driver.php files and process each route file
        $route_files = collect(File::files($folder))->filter(fn ($file) => !Str::is($file->getFileName(), 'driver.php') && Str::endsWith($file->getFileName(), '.route.php'));

        foreach ($route_files as $file) {

            // Handle the route file and extract relevant information
            $res = self::handle($file, $nested_routes_folder, $routes_path, $leftTrim);

            $prefix = $res['prefix'];
            $file_path = $res['file_path'];
            $folder_after_nested = $res['folder_after_nested'];

            // Get the existing routes before adding new ones
            $existingRoutes = collect(Route::getRoutes())->pluck('uri');

            Route::group(['prefix' => $prefix], function () use ($file_path, $existingRoutes, $folder_after_nested, &$items) {

                require $file_path;

                // Get the newly added routes and their corresponding folders
                $routes = collect(Route::getRoutes())->filter(function ($route) use ($existingRoutes) {
                    return !in_array($route->uri, $existingRoutes->toArray());
                })->map(function ($route) use ($folder_after_nested) {

                    $uri = $route->uri;

                    $methods = '@' . implode('|@', $route->methods());
                    $uri_methods = $uri . $methods;

                    $slug = Str::slug(Str::replace('/', '.', $uri), '.');

                    $parts = explode('/', $uri);
                    $title = end($parts);

                    if (isset($route->action['controller'])) {
                        $c = explode('@', $route->action['controller']);
                        if (count($c) === 2) {
                            $title = $c[1];
                        }
                    }

                    if ($route->getName()) {
                        $parts = explode('.', $route->getName());
                        $title = end($parts);
                    }


                    // Convert camel case to words
                    $words = preg_split('/(?=[A-Z])/', $title, -1, PREG_SPLIT_NO_EMPTY);

                    // Capitalize the first letter of each word and join them with spaces
                    $title = implode(' ', array_map(fn ($word) => ucfirst($word), $words));
                    $title = Str::title($title);

                    return [
                        'uri' => $uri,
                        'methods' => $methods,
                        'uri_methods' => $uri_methods,
                        'slug' => $slug,
                        'title' => $title,
                        'folder' => $folder_after_nested,
                        'hidden' => $route->hiddenRoute(),
                        'icon' => $route->getIcon(),
                        'checked' => $route->everyoneRoute()
                    ];
                });

                $items = array_merge($items, $routes->toArray());
            });
        }

        return $items;
    }
    /**
     * Handle the processing of a route file and extract relevant information.
     *
     * @param \SplFileInfo $file The route file.
     * @param string $nested_routes_folder The folder name after 'nested-routes'.
     * @param string $routes_path The base path to the routes folder.
     * @param bool $get_folder_after_nested Whether to get the folder name after the 'nested-routes' folder.
     * @return array The processed route information as an associative array.
     */
    static function handle($file, $nested_routes_folder, $routes_path, $get_folder_after = null)
    {
        $path = $file->getPath();

        $get_folder_after = true;

        $folder_after_nested = null;
        if ($get_folder_after)
            $folder_after_nested = self::getFolderAfterNested($path, $nested_routes_folder);

        $file_name = $file->getFileName();
        $prefix = $file_name;

        $prefix = self::getPrefix($folder_after_nested, $file_name, $routes_path, $path);

        $file_path = $file->getPathName();
        $res = [
            'prefix' => $prefix,
            'file_path' => $file_path,
            'folder_after_nested' => $folder_after_nested,
        ];

        // if ($get_folder_after)
        //     dump($res);

        return $res;
    }

    static function getPrefix($folder_after_nested, $file_name, $routes_path, $path)
    {

        $prefix = str_replace($file_name, '', $path);
        $prefix = str_replace($routes_path, '', $prefix);
        $arr = explode('/', $prefix);
        $len = count($arr);
        $main_file = $arr[$len - 1];
        $arr = array_map('ucwords', $arr);
        $arr = array_filter($arr);
        $ext_route = str_replace('user.route.php', '', $file_name);
        $ext_route = str_replace('index.route.php', '', $file_name);
        if ($main_file . '.route.php' === $ext_route)
            $ext_route = str_replace($main_file . '.', '.', $ext_route);
        $ext_route = str_replace('.route.php', '', $ext_route);
        if ($ext_route)
            $ext_route = '/' . $ext_route;
        $prefix = strtolower($prefix . $ext_route);

        return $prefix;
    }

    /**
     * Get the folder name after the nested-routes folder.
     *
     * @param string $path The full path to the route file.
     * @param string $nested_routes_folder The folder name after 'nested-routes'.
     * @return string|null The folder name after 'nested-routes', or null if not found.
     */
    static function getFolderAfterNested($path, $nested_routes_folder)
    {
        $parts = explode('/', $nested_routes_folder);
        $folder_after_nested = null;

        $nested_routes_folder = trim($nested_routes_folder, '/');

        $start_position = strpos($path, $nested_routes_folder);

        if ($start_position !== false) {
            $start_position += strlen($nested_routes_folder) + 1; // Adding 1 to skip the slash after the folder name.
            $folder_after_nested = substr($path, $start_position);
        }

        // Loop through all parts of $nested_routes_folder and handle empty parts
        foreach ($parts as $part) {
            if (!empty($part)) {
                $folder_after_nested = str_replace($part, '', $folder_after_nested, $count);
                if ($count > 0) {
                    break;
                }
            }
        }

        if (!$folder_after_nested) $folder_after_nested = $part;

        return $folder_after_nested;
    }
}
