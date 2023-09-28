
type Props = {
    permissions: PermissionData[]
    loadingPermission: boolean
    role: RoleData
}

const Header = ({ permissions, loadingPermission, role }: Props) => {

    return (
        <div>
            {role && (
                <div className="mb-3">
                    <table className="table table-bordered">
                        <tbody>
                            <tr>
                                <td>Name</td>
                                <td>{role.name}</td>
                            </tr>
                            <tr>
                                <td>Guard Name</td>
                                <td>{role.guard_name}</td>
                            </tr>
                            <tr>
                                <td>Permissions counts</td>
                                <td>{loadingPermission ? 'Counting...' : permissions?.length || 0}</td>
                            </tr>
                            <tr>
                                <td>Status</td>
                                <td>{role.status === 0 ? 'Inactive' : 'Active'}</td>
                            </tr>
                            <tr>
                                <td>Created At</td>
                                <td>{new Date(role.created_at).toLocaleString()}</td>
                            </tr>
                            <tr>
                                <td>Updated At</td>
                                <td>{new Date(role.updated_at).toLocaleString()}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            )}
        </div>
    )
}

export default Header