import React, { useEffect, useMemo, useState } from 'react';
import { useParams } from 'react-router-dom';
import useAxios from '@/hooks/useAxios';
import PrepareRoutesTreeDraggable from '../Includes/PrepareRoutesTreeDraggable';
import Header from '../Includes/Header';
import { useRolePermissionsContext } from '@/contexts/RolePermissionsContext';
import { publish } from '@/utils/events';

type Props = {
    role: RoleInterface
    permissions: PermissionInterface[]
    loadingPermission: boolean
    doGetPermissions: () => void
}

const Permissions: React.FC = ({ role, permissions, loadingPermission, doGetPermissions }: Props) => {

    const { id } = useParams<{ id: string }>();

    const [savedFolders, setSavedFolders] = useState<string[]>([])
    const [saving, setSaving] = useState<boolean>(false)
    const { fetchRoutePermissions } = useRolePermissionsContext();

    const { post: saveData } = useAxios();
    const { data: routes, get: getRoutes } = useAxios<RoleInterface>();
    const { data: allPermissions, get: getAllPermissions } = useAxios<PermissionInterface[]>();

    const roleUri = `admin/settings/role-permissions/roles/view/${id}`;
    const allPermissionsUri = `admin/settings/role-permissions/permissions/get-role-permissions/all`;
    const routesUri = 'admin/settings/role-permissions/permissions/routes';

    useEffect(() => {
        getRoutes(routesUri, { uri: 1 });
        getAllPermissions(allPermissionsUri, { uri: 1 });
    }, []);

    useEffect(() => {

        if (savedFolders.length > 0 && id) {
            fetchRoutePermissions(id)
        }

    }, [saving, id]);

    async function handleSubmit(checked: any) {
        setSavedFolders([]);
        setSaving(true);

        const { folderPermissions, menu, allFolders } = checked;

      var  k = folderPermissions

        const savePromises = k.map(async (current_folder: any) => {
            const { folder } = current_folder;

            return saveData(`${roleUri}/save-permissions?folder=` + folder, {
                role_id: role.id,
                current_folder,
            });
        });

        let updatedSavedFolders = []; // Declare the variable here

        try {
            const responses = await Promise.all(savePromises);

            updatedSavedFolders = folderPermissions
                .filter((_, index) => responses[index]) // Keep only successful responses
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
                <Header permissions={permissions} loadingPermission={loadingPermission} role={role} />
                <div className="row">
                    <div className='col-sm-12'>
                        <div className='card mt-2'>
                            <div className='card-body'>
                                <div className="card-header">
                                    <h4>Permissions</h4>
                                </div>
                                {/* let us wait 4 roles, routes & permissions */}
                                {
                                    routes && permissions && allPermissions ?
                                        <PrepareRoutesTreeDraggable routes={routes} permissions={permissions} allPermissions={allPermissions} handleSubmit={handleSubmit} saving={saving} savedFolders={savedFolders} />
                                        :
                                        <div className='mt-2 p-2'>
                                            {true ?
                                                <div className="d-flex align-items-center justify-content-center gap-3">
                                                    <span className="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                    Loading...
                                                </div>
                                                :
                                                `No routes defined.`
                                            }
                                        </div>
                                }
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        )

    }, [permissions, allPermissions, routes, saving, savedFolders]);

    return memoizedComponent;
}

export default Permissions