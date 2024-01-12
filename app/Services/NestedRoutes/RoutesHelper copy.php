<?php

namespace App\Services\NestedRoutes;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class RoutesHelper
{
    protected $nested_routes_folder;
    protected $prefix_from;

    function __construct($prefix_from = null)
    {
        $this->nested_routes_folder = config('nested_routes.admin_folder');

        $this->prefix_from = $prefix_from ?? 'admin';
    }
    /**
     * Get nested routes from a specific folder.
     *
     * @param string $folder The folder name.
     * @return array An array containing information about the nested routes.
     */
    public function getRoutes($leftTrim)
    {

        $routes_path = base_path('routes/' . $this->nested_routes_folder);

        if (file_exists($routes_path)) {

            $folder = $routes_path;
            $routes = $this->getRoutesReal($folder, $routes_path, $leftTrim);

            $foldermin = $this->getFolderAfterNested($folder);
            
            $item = [
                'folder' => $foldermin,
                'children' => [],
                'routes' => $routes,
                'hidden' => $this->getHidden($foldermin),
                'icon' => $this->getIcon($foldermin),
                'position' => $this->getPosition($foldermin),
            ];

            $items = $this->iterateFolders($folder, $routes_path, $leftTrim);

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

    private function iterateFolders($folder, $routes_path, $leftTrim)
    {

        $items = [];
        $folders = File::directories($folder);

        foreach ($folders as $folder) {

            $routes = $this->getRoutesReal($folder, $routes_path, $leftTrim);

            $foldermin = $this->getFolderAfterNested($folder);

            $item = [
                'folder' => $foldermin,
                'children' => $this->iterateFolders($folder, $routes_path, $leftTrim),
                'routes' => $routes,
                'hidden' => $this->getHidden($foldermin),
                'icon' => $this->getIcon($foldermin),
                'position' => $this->getPosition($foldermin),
            ];

            $items[] = $item;
        }

        return $items;
    }

    function getHidden($folder)
    {
        return Permission::where('name', $folder)->first()->hidden ?? null;
    }

    function getIcon($folder)
    {
        return Permission::where('name', $folder)->first()->icon ?? null;
    }

    function getPosition($folder)
    {
        return Permission::where('name', $folder)->first()->position ?? 999999;
    }

    function getRoutesReal($folder, $routes_path, $leftTrim)
    {
        $items = [];
        // Filter out the driver.php files and process each route file
        $route_files = collect(File::files($folder))->filter(fn ($file) => !Str::is($file->getFileName(), 'driver.php') && Str::endsWith($file->getFileName(), '.route.php'));

        foreach ($route_files as $file) {

            // Handle the route file and extract relevant information
            $res = $this->handle($file, $routes_path, $leftTrim);

            $prefix = $res['prefix'];
            $file_path = $res['file_path'];
            $folder_after_nested = $res['folder_after_nested'];

            // Get the existing routes before adding new ones
            $existingRoutes = collect(Route::getRoutes())->pluck('uri');

            Route::group(['prefix' => $prefix], function () use ($file_path, $existingRoutes, $folder_after_nested, &$items) {

                require $file_path;

                $filename = Str::title(Str::before(basename($file_path), '.route.php'));

                // Get the newly added routes and their corresponding folders
                $routes = collect(Route::getRoutes())->filter(function ($route) use ($existingRoutes) {
                    return !in_array($route->uri, $existingRoutes->toArray());
                })->map(function ($route) use ($folder_after_nested, $filename) {

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
                        $parts = preg_replace('#\.#', ' ', $route->getName());
                        $title = Str::title($parts);
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
                        'checked' => $route->everyoneRoute(),
                        'filename' => $filename,
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
     * @param string $this->nested_routes_folder The folder name after 'nested-routes'.
     * @param string $routes_path The base path to the routes folder.
     * @param bool $get_folder_after_nested Whether to get the folder name after the 'nested-routes' folder.
     * @return array The processed route information as an associative array.
     */
    function handle($file, $routes_path = null, $get_folder_after = null)
    {
        $routes_path = $routes_path ?? $this->nested_routes_folder;

        if (!$get_folder_after) {
            $get_folder_after = base_path('routes/' . $this->nested_routes_folder);
        }

        $path = $file->getPath();

        $get_folder_after = true;

        $folder_after_nested = null;
        if ($get_folder_after)
            $folder_after_nested = $this->getFolderAfterNested($path);

        $file_name = $file->getFileName();
        $prefix = $file_name;

        $prefix = $this->getPrefix($file, $routes_path);


        $isAtRoot = false;
        // Check if the current file is at the root of $routes_path
        if ($file->getPathname() === $routes_path) {
            $isAtRoot = true;
        } else {
            $main_index_file = Str::afterLast($file->getPathname(), '/') . 'route.php';
            if ($file->getBasename() != $main_index_file || $file->getBasename() !== 'index.route.php') {
                $prefix = $prefix . '/' . Str::before($file->getBasename(), '.route.php');
            }
        }

        $file_path = $file->getPathName();
        $res = [
            'prefix' => $prefix,
            'file_path' => $file_path,
            'folder_after_nested' => $folder_after_nested,
        ];

        return $res;
    }

    function getPrefix($file, $routes_path)
    {

        $prefix = '';
        if ($file->getPathname() !== $routes_path) {
            $sub = Str::replace('\\', '/', dirname($file->getPathname()));
            $sub = Str::afterLast($sub, $this->nested_routes_folder);
            $prefix = Str::after($sub, $this->prefix_from);
        }

        return $prefix;

        // api/admin/settings/role-permissions/roles/roles/get-user-roles-and-direct-permissions

        $prefix = str_replace($file_name, '', $path);
        $prefix = str_replace($routes_path, '', $prefix);
        $main_file = Str::afterLast($prefix, '/');

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
     * @param string $this->nested_routes_folder The folder name after 'nested-routes'.
     * @return string|null The folder name after 'nested-routes', or null if not found.
     */
    function getFolderAfterNested($path)
    {
        $parts = explode('/', $this->nested_routes_folder);
        $folder_after_nested = null;

        $this->nested_routes_folder = trim($this->nested_routes_folder, '/');

        $start_position = strpos($path, $this->nested_routes_folder);

        if ($start_position !== false) {
            $start_position += strlen($this->nested_routes_folder) + 1; // Adding 1 to skip the slash after the folder name.
            $folder_after_nested = substr($path, $start_position);
        }

        // Loop through all parts of $this->nested_routes_folder and handle empty parts
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
