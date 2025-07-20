import React, { useEffect, useMemo, useState } from 'react';
import { useParams } from 'react-router-dom';
import useAxios from '@/hooks/useAxios';
import PrepareRoutesTreeDraggable from '../Includes/PrepareRoutesTreeDraggable';
import RoleHeader from '../Includes/RoleHeader';
import { useRoleRoutePermissionsAndMenuContext } from '@/contexts/RoleRoutePermissionsAndMenuContext';
import { publish } from '@/utils/events';
import { PermissionInterface, RoleInterface, RouteCollectionInterface } from '@/interfaces/RolePermissionsInterfaces';

type Props = {
    role: RoleInterface | undefined;
}

const Permissions: React.FC<Props> = ({ role }) => {

    const { id } = useParams<{ id: string }>();

    const [savedFolders, setSavedFolders] = useState<string[]>([])
    const [saving, setSaving] = useState<boolean>(false)
    const { roleAndPermissions, roleRoutePermissions, roleMenu } = useRoleRoutePermissionsAndMenuContext();

    const { post: saveData } = useAxios();
    const { get: getRoutes, loading: loadingRoutes, loaded: loadedRoutes } = useAxios();
    const { get: getAllPermissions, loading: loadingAllPermissions, loaded: loadedAllPermissions } = useAxios<PermissionInterface[]>();

    const roleUri = `dashboard/settings/role-permissions/roles/view/${id}`;
    const allPermissionsUri = `dashboard/settings/role-permissions/permissions/get-role-permissions/all`;
    const routesUri = 'dashboard/settings/role-permissions/permissions/routes';

    const [routes, setRoutes] = useState<RouteCollectionInterface[]>([])
    const [allPermissions, setAllPermissions] = useState<PermissionInterface[]>([])

    useEffect(() => {
        getRoutes(routesUri, { uri: 1 }).then((resp) => {
            if (resp.results) {
                setRoutes(resp.results)
            }
        });
        getAllPermissions(allPermissionsUri, { uri: 1 }).then((resp) => {
            if (resp.results) {
                setAllPermissions(resp.results)
            }
        });

    }, []);

    useEffect(() => {

        const curr = roleAndPermissions.currentRole
        if (savedFolders.length > 0 && id && curr && String(curr.id) == id) {

            roleRoutePermissions.reload()
            roleMenu.reload()
        }

    }, [saving, id]);


    const { get: getPermissions, loading: loadingPermissions } = useAxios<PermissionInterface[]>();
    const [rolePermissions, setRolePermissions] = useState<PermissionInterface[]>([])

    useEffect(() => {
        if (role) {
            doGetPermissions()
        }
    }, [role])

    function doGetPermissions() {
        getPermissions(`dashboard/settings/role-permissions/permissions/get-role-permissions/${id}?perms=1`, { uri: 1 }).then((resp) => {
            setRolePermissions(resp.results)
        });
    }

    async function handleSubmit(checked: any) {
        setSavedFolders([]);
        setSaving(true);

        if (!role) return

        const { folderPermissions, menu, allFolders } = checked;

        const savePromises = folderPermissions.map(async (current_folder: any) => {
            const { folder } = current_folder;

            return saveData(`${roleUri}/save-permissions?folder=` + folder, {
                role_id: role.id,
                current_folder,
            });
        });

        let updatedSavedFolders = [];

        try {
            const responses = await Promise.all(savePromises);

            updatedSavedFolders = folderPermissions
                .filter((_: any, index: any) => responses[index]) // Keep only successful responses
                .map((current_folder: any) => current_folder.folder); // Extract folders from successful responses

            if (updatedSavedFolders.length === folderPermissions.length) {
                await saveData(`${roleUri}/save-menu-and-clean-permissions`, {
                    role_id: role.id,
                    menu,
                    saved_folders: updatedSavedFolders,
                    all_folders: allFolders,
                });

                publish('notification', { message: 'Successfully saved role menu', type: 'success' });
                doGetPermissions();
            }
        } catch (error) {
            publish('notification', { message: 'An error occurred: ' + error, type: 'error' });
        } finally {
            setSaving(false);
            setSavedFolders(updatedSavedFolders);
        }
    }

    // Memoize the component so it doesn't re-render unless `role`, `routes`, or `permissions` changes
    const memoizedComponent = useMemo(() => {

        return (
            <div>
                <div className='d-flex justify-content-between mt-2'>
                    <h4>Role Description</h4>
                </div>
                <RoleHeader role={role} permissions={rolePermissions} loadingPermissions={loadingPermissions} />
                <div className="row">
                    <div className='col-sm-12'>
                        <div className='card mt-2'>
                            <div className="card-header">
                                <h4>Permissions</h4>
                            </div>
                            <div className='card-body overflow-auto'>
                                {/* let us wait 4 roles, routes & permissions */}
                                {
                                    routes?.length && allPermissions?.length ?
                                        <PrepareRoutesTreeDraggable routes={routes} allPermissions={allPermissions} rolePermissions={rolePermissions} handleSubmit={handleSubmit} saving={saving} savedFolders={savedFolders} />
                                        :
                                        <div className='mt-2 p-2'>
                                            {loadingRoutes || loadingAllPermissions ?
                                                <div className="d-flex align-items-center justify-content-center gap-3">
                                                    <span className="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                    Loading...
                                                </div>
                                                :
                                                <>
                                                    {
                                                        loadedRoutes && loadedAllPermissions ?
                                                            <>
                                                                No routes defined.
                                                            </>
                                                            :
                                                            <>Data Error</>
                                                    }
                                                </>
                                            }
                                        </div>
                                }
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        )

    }, [loadingRoutes, loadedRoutes, loadingAllPermissions, loadedAllPermissions, loadingPermissions, saving, savedFolders]);

    return memoizedComponent;
}

export default Permissions