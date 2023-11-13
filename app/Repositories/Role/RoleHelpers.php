<?php

namespace App\Repositories\Role;

use App\Models\Permission;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

trait RoleHelpers
{

    private function extractAndSavePermissions($parent_folder, $nestedRoute, $guard_name)
    {

        $folder = $nestedRoute['folder'];
        $title = $nestedRoute['title'];
        $icon = $nestedRoute['icon'];
        $hidden = $nestedRoute['hidden'];
        $position = $nestedRoute['position'] ?? 999999;
        $children = $nestedRoute['children'];
        $routes = $nestedRoute['routes'];

        $uri = $folder;
        $slug = Str::slug(Str::replace('/', ' ', $uri), '.');

        $this->checked_permissions[] = Permission::updateOrCreate(
            ['name' => $slug],
            [
                'name' => $slug,
                'uri' => $uri,
                'title' => $title,
                'icon' => $icon,
                'hidden' => $hidden,
                'parent_folder' => $parent_folder,
                'position' => $position,
                'guard_name' => $guard_name,
                'user_id' => auth()->id() ?? 0,
                'updated_at' => Carbon::now(),
            ]
        )->id;

        if (count($routes) > 0) {
            array_push($this->checked_permissions, ...$this->saveRoutesASPermissions($parent_folder, $routes, $guard_name));
        }

        if (count($children) > 0) {

            foreach ($children as $nestedRoute) {
                $this->extractAndSavePermissions($parent_folder, $nestedRoute, $guard_name);
            }
        }
    }

    private function saveRoutesASPermissions($parent_folder, $routes, $guard_name)
    {
        $permissions = [];
        foreach ($routes as $route) {

            $uri = $route['uri'];
            $title = $route['title'];
            $icon = $route['icon'];
            $slug = Str::slug(Str::replace('/', ' ', $uri), '.');

            $permissions[] = Permission::updateOrCreate(
                ['name' => $slug],
                [
                    'name' => $slug,
                    'parent_folder' => $parent_folder,
                    'uri' => $uri,
                    'title' => $title,
                    'icon' => $icon,
                    'guard_name' => $guard_name,
                    'user_id' => auth()->id() ?? 0,
                    'updated_at' => Carbon::now(),
                ]
            )->id;
        }

        return $permissions;
    }

    private function saveJson($role, $json)
    {

        $filePath = storage_path('/app/system/roles/' . Str::slug($role->name) . '_menu.json');
        $jsonString = json_encode($json, JSON_PRETTY_PRINT);

        // Create the directory if it does not exist
        $filesystem = new Filesystem();
        $filesystem->makeDirectory(dirname($filePath), 0755, true, true);

        // Save the JSON data to the file
        $filesystem->put($filePath, $jsonString);
    }
}
