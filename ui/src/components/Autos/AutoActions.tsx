import usePermissions from "@/hooks/rba/usePermissions"

type Props = {
    key?: string, row: any, moduleUri: string
}

const AutoAction = ({ row, moduleUri }: Props) => {
    const { userCan } = usePermissions()

    const showView = userCan(moduleUri + '/view/:id', 'get')
    const showEdit = userCan(moduleUri + '/view/:id', 'put')
    const showUpdateStatus = userCan(moduleUri + '/view/:id/update-status', 'patch')

    return (
        <div className="dropdown">
            <button className="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i className="icon icon-list2 font-20"></i>
            </button>
            <ul className="dropdown-menu">
                {showView && <li><a className="dropdown-item autotable-view" data-id={row.id} href={`${moduleUri}view/${row.id}`}>View</a></li>}
                {showEdit && <li><a className="dropdown-item autotable-edit" data-id={row.id} href={`${moduleUri}view/${row.id}`}>Edit</a></li>}
                {showUpdateStatus && <li><a className="dropdown-item autotable-update-status" data-id={row.id} href={`${moduleUri}view/${row.id}/update-status`}>Update Status</a></li>}
            </ul>
        </div>
    );

}

export default AutoAction