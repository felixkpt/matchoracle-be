type Props = {
    message?: string
    justify?: 'start' | 'center' | 'end'
}

const AlertMessage = (props: Props) => {
    return (
        <div className="w-100 p-1">
            <div className={`d-flex align-items-center justify-content-${props.justify || 'center'} gap-3`}>
                <div className="alert alert-light">{props.message || 'There\'s nothing here'}</div>
            </div>
        </div>
    )
}

export default AlertMessage