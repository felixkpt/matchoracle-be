type Props = {
    pluralName: string
    singularName: string
    componentId: string
}

const AutoPageHeader = ({
    pluralName,
    singularName,
    componentId,

}: Props) => {
    return (
        <div className="d-flex justify-content-between">
            <h3>{pluralName} List</h3>
            <div className='d-flex justify-content-end'>
                <button type="button" className="btn btn-info text-white" data-bs-toggle="modal" data-bs-target={`#${componentId}Modal`}>Create {singularName}</button>
            </div>
        </div>
    )
}

export default AutoPageHeader